<?php

namespace Tests\Feature\Biodata;

use App\Models\Biodata;
use App\Models\User;
use App\Support\BiodataAccess;
use App\Support\BiodataPdf;
use App\Support\PdfWatermark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShareAndPdfTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        BiodataAccess::flush();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function member(array $o = []): User
    {
        $this->n++;

        return User::factory()->create(array_merge(['phone' => '97000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT), 'status' => 'active', 'role' => 'user'], $o));
    }

    private function bio(User $owner, array $o = []): Biodata
    {
        return Biodata::factory()->create(array_merge(['user_id' => $owner->id, 'name' => 'Sunita Secretname'], $o));
    }

    public function test_token_is_created_lazily_and_unique(): void
    {
        $owner = $this->member();
        $a = $this->bio($owner);
        $b = $this->bio($owner);
        $this->assertNull($a->fresh()->share_token);

        $url = $this->actingAs($owner)->postJson(route('biodata.share.show', $a))->assertOk()->json('url');
        $this->assertSame(route('biodata.shared', $a->fresh()->share_token), $url);
        $this->assertSame(20, strlen($a->fresh()->share_token));

        $this->actingAs($owner)->postJson(route('biodata.share.show', $b))->assertOk();
        $this->assertNotSame($a->fresh()->share_token, $b->fresh()->share_token);

        // same token on repeated calls
        $t = $a->fresh()->share_token;
        $this->actingAs($owner)->getJson(route('biodata.share.show', $a))->assertOk();
        $this->assertSame($t, $a->fresh()->share_token);
    }

    public function test_share_management_is_owner_only_and_needs_approval(): void
    {
        $owner = $this->member();
        $b = $this->bio($owner);
        $other = $this->member();

        $this->actingAs($other)->postJson(route('biodata.share.show', $b))->assertForbidden();
        $this->actingAs($other)->postJson(route('biodata.share.reset', $b))->assertForbidden();
        $this->actingAs($other)->postJson(route('biodata.share.toggle', $b))->assertForbidden();

        $pending = $this->bio($owner, ['status' => 'pending']);
        $this->actingAs($owner)->postJson(route('biodata.share.show', $pending))->assertStatus(422);
        $this->assertNull($pending->fresh()->share_token);
    }

    public function test_guest_is_sent_to_login_with_intended_url(): void
    {
        $b = $this->bio($this->member());
        $token = $b->ensureShareToken();

        $r = $this->get(route('biodata.shared', $token));
        $r->assertRedirect(route('login'));
        $r->assertSessionHas('url.intended', route('biodata.show', $b));
        $r->assertSessionHas('status');
    }

    public function test_active_member_is_redirected_to_normal_show_and_pending_is_bounced(): void
    {
        $b = $this->bio($this->member());
        $token = $b->ensureShareToken();

        $this->actingAs($this->member())->get(route('biodata.shared', $token))->assertRedirect(route('biodata.show', $b));
        $this->actingAs($this->member(['status' => 'pending']))->get(route('biodata.shared', $token))
            ->assertRedirect(route('home'))->assertSessionHas('pending_notice');
        $this->actingAs($this->member(['role' => 'admin']))->get(route('biodata.shared', $token))->assertRedirect(route('biodata.show', $b));
    }

    public function test_unavailable_links_leak_nothing(): void
    {
        $owner = $this->member();
        $pending = $this->bio($owner, ['status' => 'pending']);
        $pending->forceFill(['share_token' => 'PendingToken1234567'])->save();
        $disabled = $this->bio($owner);
        $disabled->ensureShareToken();
        $disabled->forceFill(['share_enabled' => false])->save();

        foreach (['PendingToken1234567', $disabled->share_token, 'doesnotexistdoesnot'] as $t) {
            $r = $this->get(route('biodata.shared', $t));
            $r->assertNotFound()->assertDontSee('Secretname')->assertDontSee('Pune')->assertSee('उपलब्ध नहीं');
        }
    }

    public function test_reset_kills_old_link_and_toggle_disables(): void
    {
        $owner = $this->member();
        $b = $this->bio($owner);
        $old = $b->ensureShareToken();

        $this->actingAs($owner)->postJson(route('biodata.share.reset', $b))->assertOk();
        $new = $b->fresh()->share_token;
        $this->assertNotSame($old, $new);
        $this->actingAs($this->member())->get(route('biodata.shared', $old))->assertNotFound();
        $this->actingAs($this->member())->get(route('biodata.shared', $new))->assertRedirect();

        $this->actingAs($owner)->postJson(route('biodata.share.toggle', $b))->assertOk()->assertJson(['enabled' => false]);
        $this->actingAs($this->member())->get(route('biodata.shared', $new))->assertNotFound();
        $this->actingAs($owner)->postJson(route('biodata.share.toggle', $b))->assertJson(['enabled' => true]);
        $this->actingAs($this->member())->get(route('biodata.shared', $new))->assertRedirect();
    }

    public function test_open_graph_tags_are_generic(): void
    {
        $b = $this->bio($this->member(), ['name' => 'Ramesh Uniquename']);
        $token = $b->ensureShareToken();

        foreach ([$this->get(route('biodata.shared', $token)), $this->get(route('biodata.shared', 'nonexistenttoken0000'))] as $r) {
            $html = $r->getContent();
            $this->assertStringContainsString('og:title" content="समाज बुक पर एक बायोडाटा"', $html);
            $this->assertStringNotContainsString('Uniquename', $html);
            $this->assertStringNotContainsString('og:image', $html);
            $this->assertStringNotContainsString('Pune', $html);
        }
    }

    public function test_owner_sees_share_button_only_when_approved(): void
    {
        $owner = $this->member();
        $ok = $this->bio($owner);
        $pending = $this->bio($owner, ['status' => 'pending']);

        $this->actingAs($owner)->get(route('biodata.mine'))->assertOk()->assertSee('data-share-open', false)->assertSee('मंज़ूर होने के बाद');
        $this->actingAs($owner)->get(route('biodata.show', $ok))->assertOk()->assertSee('shareSheet', false);
        $this->actingAs($this->member())->get(route('biodata.show', $ok))->assertOk()->assertDontSee('shareSheet', false);
    }

    public function test_pdf_downloads_for_owner_with_and_without_contact(): void
    {
        $owner = $this->member();
        $b = $this->bio($owner, ['name' => 'सुनीता कुमारी', 'about' => 'हम एक सुसंस्कृत परिवार से हैं', 'rashi' => 'Kark']);

        foreach (['' => true, '?contact=1' => true, '?contact=0' => true] as $q => $unused) {
            $r = $this->actingAs($owner)->get(route('biodata.pdf', $b).$q);
            $r->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $r->getContent());
        }
    }

    public function test_pdf_view_data_respects_contact_and_kundli_rules(): void
    {
        $owner = $this->member();
        $b = $this->bio($owner, ['rashi' => 'Kark', 'nakshatra' => 'Pushya', 'contact_phone' => '9123456780']);

        $d = BiodataPdf::data($b, $owner, true);
        $this->assertTrue($d['showContact']);
        $this->assertTrue($d['showKundli']);
        $this->assertStringContainsString('9123456780', view('biodata.pdf', $d)->render());
        $this->assertStringContainsString('Pushya', view('biodata.pdf', $d)->render());

        $d0 = BiodataPdf::data($b, $owner, false);
        $this->assertFalse($d0['showContact']);
        $this->assertStringNotContainsString('9123456780', view('biodata.pdf', $d0)->render());

        // admin: contact yes, kundli never
        $admin = $this->member(['role' => 'admin']);
        $da = BiodataPdf::data($b, $admin, true);
        $this->assertFalse($da['showKundli']);
        $html = view('biodata.pdf', $da)->render();
        $this->assertStringNotContainsString('Pushya', $html);
        $this->assertStringNotContainsString('Kark', $html);
    }

    public function test_non_owner_without_accepted_interest_cannot_download(): void
    {
        $b = $this->bio($this->member());
        $this->actingAs($this->member())->get(route('biodata.pdf', $b))->assertForbidden();
    }

    public function test_watermark_regenerates_when_logo_is_newer(): void
    {
        $wm = PdfWatermark::target();
        $logo = PdfWatermark::logo();
        $this->assertNotNull($logo);

        @unlink($wm);
        $this->assertSame($wm, PdfWatermark::path());
        $this->assertFileExists($wm);

        // make the watermark look old, logo newer
        touch($wm, filemtime($logo) - 100);
        clearstatcache();
        $before = filemtime($wm);
        PdfWatermark::path();
        clearstatcache();
        $this->assertGreaterThan($before, filemtime($wm));

        // very light: no pixel is more than ~10% opaque
        $im = imagecreatefrompng($wm);
        $maxOpacity = 0;
        foreach ([[350, 350], [300, 300], [400, 330]] as [$x, $y]) {
            $maxOpacity = max($maxOpacity, (127 - ((imagecolorat($im, $x, $y) >> 24) & 127)) / 127);
        }
        $this->assertLessThanOrEqual(0.1, $maxOpacity);
    }
}
