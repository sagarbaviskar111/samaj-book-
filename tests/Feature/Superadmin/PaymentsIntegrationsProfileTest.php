<?php

namespace Tests\Feature\Superadmin;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Interest;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Setting;
use App\Services\IntegrationSettings;
use Illuminate\Support\Facades\DB;

class PaymentsIntegrationsProfileTest extends SuperadminTestCase
{
    public function test_indian_money_format(): void
    {
        $this->assertSame('₹1,234.50', Payment::inr(123450));
        $this->assertSame('₹12,34,567', Payment::inr(123456700));
        $this->assertSame('₹0.05', Payment::inr(5));
        $this->assertSame('₹999', Payment::inr(99900));
        $this->assertSame('₹1,00,000', Payment::inr(10000000));
    }

    public function test_payments_crud_filters_csv_and_totals(): void
    {
        $sa = $this->mk('superadmin');
        $m = $this->mk('user');
        $this->actingAs($sa)->post(route('superadmin.payments.store'), [
            'user_phone' => $m->phone, 'amount' => '1500.50', 'purpose' => 'donation', 'method' => 'upi', 'note' => 'Diwali fund',
        ])->assertRedirect();
        $p = Payment::firstOrFail();
        $this->assertSame(150050, $p->amount_paise);
        $this->assertSame('paid', $p->status);
        $this->assertSame($m->id, $p->user_id);
        Payment::create(['amount_paise' => 20000, 'purpose' => 'event_fee', 'gateway' => 'razorpay', 'status' => 'pending']);
        Payment::create(['amount_paise' => 30000, 'purpose' => 'other', 'gateway' => 'razorpay', 'status' => 'refunded']);

        $this->get(route('superadmin.payments.index'))->assertOk()->assertSee('₹1,500.50')->assertSee('₹200')->assertSee('₹300');
        $this->get(route('superadmin.payments.index', ['status' => 'pending']))->assertOk()->assertDontSee('Diwali');
        $this->get(route('superadmin.payments.index', ['q' => $m->name, 'gateway' => 'manual']))->assertOk()->assertSee('₹1,500.50');

        $this->get(route('superadmin.payments.show', $p->id))->assertOk()->assertSee('Diwali fund');
        $this->post(route('superadmin.payments.refund', $p->id))->assertRedirect();
        $this->assertSame('refunded', $p->fresh()->status);
        $this->post(route('superadmin.payments.refund', $p->id))->assertStatus(422);

        $pending = Payment::where('status', 'pending')->firstOrFail();
        $this->post(route('superadmin.payments.mark-paid', $pending->id))->assertRedirect();
        $this->assertSame('paid', $pending->fresh()->status);

        $csv = $this->get(route('superadmin.payments.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('1500.50', $csv);
        $this->assertStringContainsString('id,created_at', $csv);
        $this->assertTrue(AuditLog::where('action', 'sa.payment.create')->exists());
        $this->assertTrue(AuditLog::where('action', 'sa.payment.refund')->exists());

        $this->post(route('superadmin.payments.store'), ['amount' => '0', 'purpose' => 'donation'])->assertSessionHasErrors('amount');
    }

    public function test_integration_secrets_encrypted_masked_blank_keeps_old_and_never_audited(): void
    {
        $sa = $this->mk('superadmin');
        $this->actingAs($sa)->put(route('superadmin.integrations.update', 'razorpay'), [
            'key_id' => 'rzp_test_ABCDEF123456', 'key_secret' => 'SuperSecretValue9876', 'webhook_secret' => '', 'mode' => 'test',
        ])->assertRedirect();

        $raw = DB::table('integration_secrets')->where('group', 'razorpay')->where('key', 'key_secret')->value('value');
        $this->assertNotNull($raw);
        $this->assertStringNotContainsString('SuperSecretValue9876', $raw);
        $this->assertSame('SuperSecretValue9876', IntegrationSettings::get('razorpay', 'key_secret'));
        $this->assertTrue(IntegrationSettings::has('razorpay', 'key_id'));
        $this->assertFalse(IntegrationSettings::has('razorpay', 'webhook_secret'));
        $this->assertSame('SuperSecretValue9876', IntegrationSettings::forGateway('razorpay')['key_secret']);
        $this->assertSame('••••••9876', IntegrationSettings::masked('razorpay', 'key_secret'));

        $this->get(route('superadmin.integrations.index'))->assertOk()
            ->assertSee('••••••9876', false)->assertDontSee('SuperSecretValue9876');

        // blank keeps old
        $this->put(route('superadmin.integrations.update', 'razorpay'), ['key_id' => '', 'key_secret' => '', 'mode' => ''])->assertRedirect();
        $this->assertSame('SuperSecretValue9876', IntegrationSettings::get('razorpay', 'key_secret'));
        // replacing works
        $this->put(route('superadmin.integrations.update', 'razorpay'), ['key_secret' => 'BrandNewSecret0001']);
        $this->assertSame('BrandNewSecret0001', IntegrationSettings::get('razorpay', 'key_secret'));

        // audit mentions field names only
        $notes = AuditLog::where('action', 'sa.integration.update')->pluck('note')->implode(' ');
        $this->assertStringContainsString('razorpay.key_secret', $notes);
        $this->assertStringNotContainsString('SuperSecretValue9876', $notes);
        $this->assertStringNotContainsString('BrandNewSecret0001', $notes);

        [$ok] = IntegrationSettings::formatCheck('razorpay');
        $this->assertTrue($ok);
        $this->post(route('superadmin.integrations.check', 'razorpay'))->assertRedirect();

        // custom keys
        $this->post(route('superadmin.integrations.custom.store'), ['name' => 'PAYTM_KEY', 'value' => 'paytm-secret-zzzz-1234'])->assertRedirect();
        $this->assertSame('paytm-secret-zzzz-1234', IntegrationSettings::get('custom', 'PAYTM_KEY'));
        $this->get(route('superadmin.integrations.index'))->assertSee('PAYTM_KEY')->assertDontSee('paytm-secret-zzzz-1234')->assertSee('••••••1234', false);
        $this->delete(route('superadmin.integrations.custom.destroy', 'PAYTM_KEY'))->assertRedirect();
        $this->assertFalse(IntegrationSettings::has('custom', 'PAYTM_KEY'));

        $this->put(route('superadmin.integrations.update', 'nope'), [])->assertNotFound();
    }

    public function test_payment_config_saved(): void
    {
        $this->actingAs($this->mk('superadmin'))->put(route('superadmin.integrations.payment-config'), [
            'currency' => 'inr', 'enabled' => '1', 'default_gateway' => 'phonepe', 'platform_fee_text' => '2% fee',
        ])->assertRedirect();
        $this->assertSame('INR', Setting::get('payments.currency'));
        $this->assertSame('1', (string) Setting::get('payments.enabled'));
        $this->assertSame('phonepe', Setting::get('payments.default_gateway'));
    }

    public function test_full_profile_page_renders_with_data(): void
    {
        $sa = $this->mk('superadmin');
        $u = $this->mk('user');
        $other = $this->mk('user');
        $b = $this->bio($u, ['contact_phone' => '9876501234', 'income_range' => '5-10L', 'name' => 'Sita Hidden']);
        $b2 = $this->bio($other, ['name' => 'Ram Other', 'gender' => 'male']);
        Interest::unguarded(fn () => Interest::create(['from_biodata_id' => $b->id, 'to_biodata_id' => $b2->id, 'from_user_id' => $u->id, 'to_user_id' => $other->id, 'status' => 'sent']));
        Post::unguarded(fn () => Post::create(['user_id' => $u->id, 'body' => 'Hello samaj post', 'visibility' => 'members', 'status' => 'visible']));
        $ev = Event::unguarded(fn () => Event::create(['title' => 'Milan Sammelan', 'starts_at' => now()->addDay(), 'is_published' => true]));
        EventRegistration::create(['event_id' => $ev->id, 'user_id' => $u->id]);
        Payment::create(['user_id' => $u->id, 'amount_paise' => 51100, 'purpose' => 'donation', 'status' => 'paid']);
        AuditLog::create(['admin_id' => $sa->id, 'action' => 'user.approve', 'target_type' => 'user', 'target_id' => $u->id, 'note' => 'approved ok']);

        $this->actingAs($sa)->get(route('superadmin.users.show', $u->id))->assertOk()
            ->assertSee('9876501234')->assertSee('5-10L')->assertSee('Sita Hidden')->assertSee('Ram Other')
            ->assertSee('Hello samaj post')->assertSee('Milan Sammelan')->assertSee('₹511')->assertSee('approved ok');
        // also works for an admin account
        $this->get(route('superadmin.users.show', $this->mk('admin')->id))->assertOk();
    }
}
