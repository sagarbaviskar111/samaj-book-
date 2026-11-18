<?php

namespace Tests\Feature\Search;

use App\Models\Interest;
use App\Models\Report;
use App\Models\Setting;
use App\Notifications\GeneralNotification;
use App\Services\NewBiodataAlert;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class InterestTest extends SearchTestCase
{
    private function pair(): array
    {
        $boyU = $this->member();
        $girlU = $this->member();
        $boy = $this->bio($boyU, ['gender' => 'male']);
        $girl = $this->bio($girlU, ['gender' => 'female']);

        return [$boyU, $boy, $girlU, $girl];
    }

    public function test_send_creates_interest_and_notifies_owner(): void
    {
        [$boyU, $boy, $girlU, $girl] = $this->pair();
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id])->assertSessionHas('status');

        $i = Interest::first();
        $this->assertSame('sent', $i->status);
        $this->assertSame($girlU->id, $i->to_user_id);
        $this->assertTrue($i->expires_at->between(now()->addDays(6), now()->addDays(8)));
        $this->assertSame('interest_received', $girlU->notifications()->first()->data['type']);
    }

    public function test_invalid_sends_are_refused(): void
    {
        [$boyU, $boy, , $girl] = $this->pair();
        $otherBoy = $this->bio($this->member(), ['gender' => 'male']);
        $pendingGirl = $this->bio($this->member(), ['gender' => 'female', 'status' => 'pending']);

        $this->actingAs($boyU);
        $this->post('/interests', ['from_biodata_id' => $girl->id, 'to_biodata_id' => $boy->id])->assertSessionHas('error');
        $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $boy->id])->assertSessionHas('error');
        $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $otherBoy->id])->assertSessionHas('error');
        $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $pendingGirl->id])->assertSessionHas('error');
        $this->assertSame(0, Interest::count());
    }

    public function test_duplicate_refused(): void
    {
        [$boyU, $boy, , $girl] = $this->pair();
        $this->actingAs($boyU);
        $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id])->assertSessionHas('error');
        $this->assertSame(1, Interest::count());
    }

    public function test_daily_limit(): void
    {
        Setting::put('interest_daily_limit', 2);
        $boyU = $this->member();
        $boy = $this->bio($boyU, ['gender' => 'male']);
        $girls = collect(range(1, 3))->map(fn () => $this->bio($this->member(), ['gender' => 'female']));
        $this->actingAs($boyU);
        foreach ($girls as $g) {
            $this->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $g->id]);
        }
        $this->assertSame(2, Interest::count());
    }

    public function test_respond_only_by_receiver_and_notifies_sender(): void
    {
        [$boyU, $boy, $girlU, $girl] = $this->pair();
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $i = Interest::first();

        $this->actingAs($boyU)->post("/interests/{$i->id}/respond", ['action' => 'accept'])->assertForbidden();
        $this->actingAs($this->member())->post("/interests/{$i->id}/respond", ['action' => 'accept'])->assertForbidden();

        $this->actingAs($girlU)->post("/interests/{$i->id}/respond", ['action' => 'accept'])->assertRedirect();
        $this->assertSame('accepted', $i->fresh()->status);
        $this->assertSame('interest_accepted', $boyU->notifications()->first()->data['type']);
    }

    public function test_reject_sends_soft_message(): void
    {
        [$boyU, $boy, $girlU, $girl] = $this->pair();
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $this->actingAs($girlU)->post('/interests/'.Interest::first()->id.'/respond', ['action' => 'reject']);
        $n = $boyU->notifications()->first()->data;
        $this->assertSame('interest_rejected', $n['type']);
        $this->assertStringContainsString('संयोग नहीं बैठा', $n['body']);
    }

    public function test_withdraw_only_by_sender(): void
    {
        [$boyU, $boy, $girlU, $girl] = $this->pair();
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $i = Interest::first();
        $this->actingAs($girlU)->post("/interests/{$i->id}/withdraw")->assertForbidden();
        $this->actingAs($boyU)->post("/interests/{$i->id}/withdraw");
        $this->assertSame('withdrawn', $i->fresh()->status);
    }

    public function test_contact_revealed_only_when_accepted_and_help_creates_report(): void
    {
        [$boyU, $boy, $girlU, $girl] = $this->pair();
        $girl->update(['contact_phone' => '9123456780']);
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $i = Interest::first();

        $this->actingAs($boyU)->get('/interests?tab=sent')->assertDontSee('9123456780');
        $this->actingAs($boyU)->post("/interests/{$i->id}/help")->assertSessionHas('error');

        $this->actingAs($girlU)->post("/interests/{$i->id}/respond", ['action' => 'accept']);
        $this->actingAs($boyU)->get('/interests?tab=accepted')->assertSee('9123456780')->assertSee('wa.me/919123456780', false);
        $this->actingAs($boyU)->post("/interests/{$i->id}/help");
        $this->assertDatabaseHas('reports', ['target_type' => 'interest_help', 'target_id' => $i->id, 'reporter_id' => $boyU->id]);
        $this->assertSame(1, Report::count());
    }

    public function test_expire_command(): void
    {
        [$boyU, $boy, , $girl] = $this->pair();
        $this->actingAs($boyU)->post('/interests', ['from_biodata_id' => $boy->id, 'to_biodata_id' => $girl->id]);
        $i = Interest::first();
        Artisan::call('interests:expire');
        $this->assertSame('sent', $i->fresh()->status);
        $i->update(['expires_at' => now()->subMinute()]);
        Artisan::call('interests:expire');
        $this->assertSame('expired', $i->fresh()->status);
    }

    public function test_shortlist_toggle_and_page(): void
    {
        [$boyU, , , $girl] = $this->pair();
        $this->actingAs($boyU)->postJson("/shortlist/{$girl->id}")->assertJson(['shortlisted' => true]);
        $this->actingAs($boyU)->get('/shortlist')->assertOk()->assertSee(explode(' ', $girl->name)[0]);
        $this->actingAs($boyU)->postJson("/shortlist/{$girl->id}")->assertJson(['shortlisted' => false]);
        $this->assertDatabaseCount('shortlists', 0);
    }

    public function test_area_alert_notifies_matching_district_users(): void
    {
        Notification::fake();
        $girlU = $this->member();
        $this->bio($girlU, ['gender' => 'female', 'district' => 'Pune']);
        $farU = $this->member();
        $this->bio($farU, ['gender' => 'female', 'district' => 'Akola']);
        $newBoy = $this->bio($this->member(), ['gender' => 'male', 'district' => 'Pune']);

        $this->assertSame(1, app(NewBiodataAlert::class)->handle($newBoy));
        Notification::assertSentTo($girlU, GeneralNotification::class, fn ($n) => $n->type === 'new_biodata_area');
        Notification::assertNotSentTo($farU, GeneralNotification::class);
    }
}
