<?php

namespace Tests\Feature\Events;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Freeze at midday so "starts in 30 minutes" never crosses midnight (otherwise the test is flaky after 23:30).
        $this->travelTo(now()->setTime(12, 0));
    }

    private function mk(string $role = 'user', array $extra = []): User
    {
        $this->n++;

        return User::forceCreate(array_merge([
            'name' => "User {$this->n}", 'phone' => '92000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT),
            'password' => 'secret123', 'role' => $role, 'status' => 'active',
        ], $extra));
    }

    private function ev(array $o = []): Event
    {
        return Event::create($o + [
            'title' => 'Sammelan', 'starts_at' => now()->addDays(2), 'is_published' => true,
            'registration_open' => true, 'popup_enabled' => true, 'reminder_interval_min' => 15,
        ]);
    }

    private function sub(string $area = 'Nagpur'): User
    {
        return $this->mk('subadmin', ['admin_area' => $area]);
    }

    public function test_register_popup_nearest_event_only(): void
    {
        $u = $this->mk();
        $far = $this->ev(['title' => 'Far', 'starts_at' => now()->addDays(9)]);
        $near = $this->ev(['title' => 'Near', 'starts_at' => now()->addDays(1)]);

        $r = $this->actingAs($u)->getJson(route('events.popup'))->assertOk();
        $r->assertJsonPath('register.id', $near->id)->assertJsonPath('join', null);
        $this->assertNotNull($r->json('server_time'));
        $this->assertNotSame($far->id, $r->json('register.id'));
    }

    public function test_register_popup_excludes_registered_ended_unpublished_closed_and_other_area(): void
    {
        $u = $this->mk('user', ['district' => 'Pune']);
        $reg = $this->ev(['title' => 'Reg']);
        EventRegistration::create(['event_id' => $reg->id, 'user_id' => $u->id]);
        $this->ev(['title' => 'Ended', 'starts_at' => now()->subDays(2)]);
        $this->ev(['title' => 'Draft', 'is_published' => false]);
        $this->ev(['title' => 'Closed', 'registration_open' => false]);
        $this->ev(['title' => 'NoPopup', 'popup_enabled' => false]);
        $this->ev(['title' => 'Other', 'area' => 'Nagpur']);

        $this->actingAs($u)->getJson(route('events.popup'))->assertOk()->assertJsonPath('register', null);

        Cache::flush();
        $mine = $this->ev(['title' => 'Mine', 'area' => 'Pune', 'starts_at' => now()->addDays(5)]);
        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('register.id', $mine->id);
    }

    public function test_join_popup_only_for_registered_on_event_day(): void
    {
        $u = $this->mk();
        $today = $this->ev(['title' => 'Today', 'starts_at' => now()->addMinutes(30), 'ends_at' => now()->addHours(2), 'reminder_interval_min' => 10, 'join_url' => 'https://meet.example.com/x']);
        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('join', null);

        EventRegistration::create(['event_id' => $today->id, 'user_id' => $u->id]);
        Cache::flush();
        $r = $this->actingAs($u)->getJson(route('events.popup'))->assertOk();
        $r->assertJsonPath('join.id', $today->id)->assertJsonPath('join.interval_min', 10)->assertJsonPath('register', null);
        $this->assertSame(route('events.join', $today), $r->json('join.join_url'));
    }

    public function test_join_popup_not_for_tomorrow_or_ended_event(): void
    {
        $u = $this->mk();
        $tomorrow = $this->ev(['starts_at' => now()->addDay()]);
        $ended = $this->ev(['title' => 'Ended', 'starts_at' => now()->subHours(6), 'ends_at' => now()->subHours(4)]);
        EventRegistration::create(['event_id' => $tomorrow->id, 'user_id' => $u->id]);
        EventRegistration::create(['event_id' => $ended->id, 'user_id' => $u->id]);

        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('join', null);
    }

    public function test_join_records_and_redirects_then_popup_stops(): void
    {
        $u = $this->mk();
        $e = $this->ev(['starts_at' => now()->addMinutes(20), 'join_url' => 'https://meet.example.com/abc']);
        EventRegistration::create(['event_id' => $e->id, 'user_id' => $u->id]);

        $this->actingAs($u)->get(route('events.join', $e))->assertRedirect('https://meet.example.com/abc');
        $this->assertNotNull(EventRegistration::first()->joined_at);
        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('join', null);
    }

    public function test_join_without_url_goes_to_event_page_and_requires_registration(): void
    {
        $u = $this->mk();
        $e = $this->ev(['starts_at' => now()->addMinutes(20)]);
        $this->actingAs($u)->get(route('events.join', $e))->assertRedirect(route('events.show', $e));
        $this->assertSame(0, EventRegistration::count());

        EventRegistration::create(['event_id' => $e->id, 'user_id' => $u->id]);
        $this->actingAs($u)->get(route('events.join', $e))->assertRedirect(route('events.show', $e));
        $this->assertNotNull(EventRegistration::first()->joined_at);
    }

    public function test_register_and_unregister(): void
    {
        $u = $this->mk();
        $e = $this->ev();

        $this->actingAs($u)->postJson(route('events.register', $e))->assertOk()->assertJson(['ok' => true]);
        $this->actingAs($u)->postJson(route('events.register', $e))->assertOk();
        $this->assertSame(1, EventRegistration::count());
        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('register', null);

        $this->actingAs($u)->deleteJson(route('events.unregister', $e))->assertOk();
        $this->assertSame(0, EventRegistration::count());
        $this->actingAs($u)->getJson(route('events.popup'))->assertJsonPath('register.id', $e->id);
    }

    public function test_cannot_register_when_closed_or_ended_or_unpublished(): void
    {
        $u = $this->mk();
        $closed = $this->ev(['registration_open' => false]);
        $ended = $this->ev(['starts_at' => now()->subDays(1)]);
        $draft = $this->ev(['is_published' => false]);

        $this->actingAs($u)->postJson(route('events.register', $closed))->assertStatus(422);
        $this->actingAs($u)->postJson(route('events.register', $ended))->assertStatus(422);
        $this->actingAs($u)->postJson(route('events.register', $draft))->assertNotFound();
        $this->assertSame(0, EventRegistration::count());
    }

    public function test_pages_render_and_guests_and_pending_are_blocked(): void
    {
        $u = $this->mk();
        $e = $this->ev(['title' => 'Grand Sammelan']);
        $this->actingAs($u)->get(route('events.index'))->assertOk()->assertSee('Grand Sammelan');
        $this->actingAs($u)->get(route('events.index', ['tab' => 'mine']))->assertOk();
        $this->actingAs($u)->get(route('events.index', ['tab' => 'past']))->assertOk();
        $this->actingAs($u)->get(route('events.show', $e))->assertOk()->assertSee('नाम लिखवाएँ');
        $this->actingAs($u)->get(route('events.ics', $e))->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->actingAs($u)->get(route('home'))->assertOk()->assertSee('ev-pop-cfg', false);

        auth()->logout();
        $this->getJson(route('events.popup'))->assertUnauthorized();
        $pending = $this->mk('user', ['status' => 'pending']);
        $this->actingAs($pending)->get(route('events.index'))->assertRedirect(route('home'));
    }

    public function test_admin_creates_event_audit_and_fanout(): void
    {
        $admin = $this->mk('admin');
        $a = $this->mk('user', ['district' => 'Nagpur']);
        $b = $this->mk('user', ['district' => 'Pune']);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'City Meet', 'starts_at' => now()->addDays(3)->format('Y-m-d\TH:i'), 'reminder_interval_min' => 10,
            'is_published' => 1, 'registration_open' => 1, 'popup_enabled' => 1, 'notify_all' => 1, 'area' => 'Nagpur',
            'join_url' => 'https://meet.google.com/abc',
        ])->assertRedirect(route('admin.events.index'));

        $e = Event::first();
        $this->assertSame('Nagpur', $e->area);
        $this->assertSame(10, $e->reminder_interval_min);
        $this->assertTrue(AuditLog::where('action', 'event.create')->exists());
        $this->assertSame(1, $a->notifications()->count());
        $this->assertSame('event', $a->notifications()->first()->data['type']);
        $this->assertSame(0, $b->notifications()->count());

        $this->actingAs($admin)->put(route('admin.events.update', $e->id), [
            'title' => 'City Meet 2', 'starts_at' => now()->addDays(3)->format('Y-m-d\TH:i'), 'reminder_interval_min' => 30, 'is_published' => 1,
        ])->assertRedirect();
        $this->assertSame('City Meet 2', $e->fresh()->title);
        $this->assertNull($e->fresh()->area);
        $this->assertTrue(AuditLog::where('action', 'event.update')->exists());

        $this->actingAs($admin)->delete(route('admin.events.destroy', $e->id))->assertRedirect();
        $this->assertSame(0, Event::count());
        $this->assertTrue(AuditLog::where('action', 'event.delete')->exists());
    }

    public function test_admin_validation(): void
    {
        $admin = $this->mk('admin');
        $base = ['title' => 'X', 'starts_at' => now()->addDay()->format('Y-m-d\TH:i'), 'reminder_interval_min' => 15];
        $this->actingAs($admin)->post(route('admin.events.store'), $base + ['join_url' => 'javascript:alert(1)'])->assertSessionHasErrors('join_url');
        $this->actingAs($admin)->post(route('admin.events.store'), array_merge($base, ['reminder_interval_min' => 7]))->assertSessionHasErrors('reminder_interval_min');
        $this->actingAs($admin)->post(route('admin.events.store'), $base + ['ends_at' => now()->format('Y-m-d\TH:i')])->assertSessionHasErrors('ends_at');
    }

    public function test_subadmin_is_scoped_to_own_area(): void
    {
        $sub = $this->sub('Nagpur');
        $mine = $this->ev(['title' => 'Mine NGP', 'area' => 'Nagpur']);
        $other = $this->ev(['title' => 'Other PUNE', 'area' => 'Pune']);
        $all = $this->ev(['title' => 'Global', 'area' => null]);

        $this->actingAs($sub)->get(route('admin.events.index'))->assertOk()->assertSee('Mine NGP')->assertDontSee('Other PUNE')->assertDontSee('Global');
        $this->actingAs($sub)->get(route('admin.events.edit', $other->id))->assertNotFound();
        $this->actingAs($sub)->get(route('admin.events.edit', $all->id))->assertNotFound();
        $this->actingAs($sub)->delete(route('admin.events.destroy', $other->id))->assertNotFound();
        $this->actingAs($sub)->get(route('admin.events.registrants', $other->id))->assertNotFound();
        $this->actingAs($sub)->get(route('admin.events.edit', $mine->id))->assertOk();

        $this->actingAs($sub)->post(route('admin.events.store'), [
            'title' => 'Sub made', 'starts_at' => now()->addDay()->format('Y-m-d\TH:i'), 'reminder_interval_min' => 15, 'area' => 'Pune', 'is_published' => 1,
        ])->assertRedirect();
        $this->assertSame('Nagpur', Event::where('title', 'Sub made')->first()->area);
    }

    public function test_member_cannot_open_admin_events(): void
    {
        $this->actingAs($this->mk())->get(route('admin.events.index'))->assertForbidden();
    }

    public function test_registrants_page_and_csv(): void
    {
        $admin = $this->mk('admin');
        $u = $this->mk('user', ['name' => '=cmd', 'district' => 'Nagpur']);
        $e = $this->ev();
        EventRegistration::create(['event_id' => $e->id, 'user_id' => $u->id, 'joined_at' => now()]);

        $this->actingAs($admin)->get(route('admin.events.registrants', $e->id))->assertOk()->assertSee($u->phone);
        $csv = $this->actingAs($admin)->get(route('admin.events.registrants.csv', $e->id))->assertOk()->streamedContent();
        $this->assertStringContainsString('Name,Phone,Area', $csv);
        $this->assertStringContainsString($u->phone, $csv);
        $this->assertStringContainsString("'=cmd", $csv);
    }

    public function test_admin_event_form_renders(): void
    {
        $admin = $this->mk('admin');
        $this->actingAs($admin)->get(route('admin.events.create'))->assertOk()->assertSee('Join popup repeats every');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('admin/events', false);
    }
}
