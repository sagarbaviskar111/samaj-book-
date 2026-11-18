<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Biodata;
use App\Models\NewsItem;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\Admin\AdminScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function mk(string $role = 'user', string $status = 'active', array $extra = []): User
    {
        $this->n++;

        return User::unguarded(fn () => User::create(array_merge([
            'name' => "User {$this->n}", 'phone' => '90000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT),
            'password' => 'secret123', 'role' => $role, 'status' => $status,
        ], $extra)));
    }

    private function admin(): User
    {
        return $this->mk('admin');
    }

    private function bio(User $owner, string $status = 'pending'): Biodata
    {
        return Biodata::unguarded(fn () => Biodata::create([
            'user_id' => $owner->id, 'name' => 'Sita', 'gender' => 'female', 'dob' => '2000-01-01', 'status' => $status,
        ]));
    }

    public function test_guard(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs($this->mk())->get('/admin')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Registrations');
    }

    public function test_all_pages_render(): void
    {
        $a = $this->admin();
        $u = $this->mk('user', 'pending');
        $b = $this->bio($u);
        NewsItem::create(['title' => 'N', 'type' => 'news']);
        foreach (['admin.users.index', 'admin.biodata.index', 'admin.news.index', 'admin.news.create', 'admin.stories.index', 'admin.stories.create', 'admin.reports.index', 'admin.settings', 'admin.audit', 'admin.password'] as $r) {
            $this->actingAs($a)->get(route($r))->assertOk();
        }
        $this->actingAs($a)->get(route('admin.users.show', $u->id))->assertOk();
        $this->actingAs($a)->get(route('admin.biodata.show', $b->id))->assertOk();
    }

    public function test_admin_scope_default_is_all_areas(): void
    {
        $this->assertNull(AdminScope::area());
        $this->mk('user', 'active', ['area' => 'A']);
        $this->mk('user', 'active', ['area' => 'B']);
        $this->admin();
        $this->assertSame(2, AdminScope::users(User::query())->count());
    }

    public function test_approve_and_reject_user(): void
    {
        $a = $this->admin();
        $u = $this->mk('user', 'pending');
        $v = $this->mk('user', 'pending');

        $this->actingAs($a)->post(route('admin.users.approve', $u->id))->assertRedirect();
        $this->assertSame('active', $u->fresh()->status);
        $this->assertSame('account_approved', $u->notifications()->first()->data['type']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.approve', 'target_id' => $u->id]);

        $this->actingAs($a)->post(route('admin.users.reject', $v->id), [])->assertSessionHasErrors('reason');
        $this->actingAs($a)->post(route('admin.users.reject', $v->id), ['reason' => 'Fake details'])->assertRedirect();
        $this->assertSame('rejected', $v->fresh()->status);
        $this->assertSame('account_rejected', $v->notifications()->first()->data['type']);
    }

    public function test_bulk_approve_block_reset_delete_user(): void
    {
        $a = $this->admin();
        $u1 = $this->mk('user', 'pending');
        $u2 = $this->mk('user', 'pending');
        $this->actingAs($a)->post(route('admin.users.bulk-approve'), ['ids' => [$u1->id, $u2->id, $a->id]])->assertRedirect();
        $this->assertSame('active', $u2->fresh()->status);

        $this->actingAs($a)->post(route('admin.users.block', $u1->id));
        $this->assertSame('blocked', $u1->fresh()->status);
        $this->actingAs($a)->post(route('admin.users.unblock', $u1->id));
        $this->assertSame('active', $u1->fresh()->status);

        $old = $u1->password;
        $this->actingAs($a)->post(route('admin.users.reset-password', $u1->id))->assertSessionHas('temp_password');
        $this->assertNotSame($old, $u1->fresh()->password);

        $this->actingAs($a)->delete(route('admin.users.destroy', $u2->id));
        $this->assertDatabaseMissing('users', ['id' => $u2->id]);

        // admins are out of reach
        $this->actingAs($a)->post(route('admin.users.block', $a->id))->assertNotFound();
    }

    public function test_approve_and_reject_biodata(): void
    {
        $a = $this->admin();
        $u = $this->mk();
        $b = $this->bio($u);
        $c = $this->bio($u);

        $this->actingAs($a)->post(route('admin.biodata.approve', $b->id))->assertRedirect();
        $b->refresh();
        $this->assertSame('approved', $b->status);
        $this->assertSame($a->id, $b->approved_by);
        $this->assertNotNull($b->approved_at);
        $this->assertContains('biodata_approved', $u->notifications->pluck('data.type')->all());

        $this->actingAs($a)->post(route('admin.biodata.reject', $c->id), ['reason' => 'Photo missing']);
        $this->assertSame('rejected', $c->fresh()->status);
        $this->assertContains('biodata_rejected', $u->fresh()->notifications->pluck('data.type')->all());

        $this->actingAs($a)->post(route('admin.biodata.verify', $b->id));
        $this->assertTrue($b->fresh()->verified);
        $this->actingAs($a)->post(route('admin.biodata.status', $b->id), ['status' => 'married']);
        $this->assertSame('married', $b->fresh()->status);
        $this->assertGreaterThan(3, AuditLog::count());
    }

    public function test_content_crud_and_notification(): void
    {
        Storage::fake('public');
        $a = $this->admin();
        $m = $this->mk();
        $pending = $this->mk('user', 'pending');

        $this->actingAs($a)->post(route('admin.news.store'), [
            'type' => 'event', 'title' => 'Sammelan', 'event_at' => '2026-12-01 10:00', 'event_place' => 'Hall',
            'is_published' => 1, 'notify_all' => 1, 'image' => UploadedFile::fake()->image('p.jpg', 3000, 2000),
        ])->assertRedirect(route('admin.news.index'));

        $item = NewsItem::first();
        $this->assertSame('event', $item->type);
        $this->assertTrue($item->is_published);
        Storage::disk('public')->assertExists($item->image);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($item->image));
        $this->assertLessThanOrEqual(1600, $w);
        $this->assertSame('event', $m->notifications()->first()->data['type']);
        $this->assertSame(0, $pending->notifications()->count());

        $this->actingAs($a)->put(route('admin.news.update', $item->id), ['type' => 'event', 'title' => 'Renamed', 'event_at' => '2026-12-01 10:00'])->assertRedirect();
        $this->assertSame('Renamed', $item->fresh()->title);
        $this->assertFalse($item->fresh()->is_published);

        $this->actingAs($a)->post(route('admin.news.publish', $item->id));
        $this->assertTrue($item->fresh()->is_published);

        $this->actingAs($a)->post(route('admin.news.store'), ['type' => 'event', 'title' => 'No date'])->assertSessionHasErrors('event_at');

        $this->actingAs($a)->delete(route('admin.news.destroy', $item->id));
        $this->assertSame(0, NewsItem::count());

        $this->actingAs($a)->post(route('admin.stories.store'), ['couple_names' => 'A & B', 'is_published' => 1])->assertRedirect();
        $this->assertDatabaseHas('success_stories', ['couple_names' => 'A & B', 'is_published' => 1]);
    }

    public function test_report_actions(): void
    {
        $a = $this->admin();
        $author = $this->mk();
        $reporter = $this->mk();
        $post = Post::unguarded(fn () => Post::create(['user_id' => $author->id, 'body' => 'bad']));
        $mk = fn (string $type, int $id) => Report::create(['reporter_id' => $reporter->id, 'target_type' => $type, 'target_id' => $id, 'reason' => 'x']);

        $r1 = $mk('post', $post->id);
        $this->actingAs($a)->get(route('admin.reports.index'))->assertOk()->assertSee('bad');
        $this->actingAs($a)->post(route('admin.reports.hide-post', $r1->id));
        $this->assertSame('hidden', $post->fresh()->status);
        $this->assertSame('resolved', $r1->fresh()->status);

        $r2 = $mk('post', $post->id);
        $this->actingAs($a)->post(route('admin.reports.block-user', $r2->id));
        $this->assertSame('blocked', $author->fresh()->status);

        $r3 = $mk('user', $reporter->id);
        $this->actingAs($a)->post(route('admin.reports.dismiss', $r3->id));
        $this->assertSame('dismissed', $r3->fresh()->status);

        $r4 = $mk('user', $reporter->id);
        $this->actingAs($a)->post(route('admin.reports.resolve', $r4->id));
        $this->assertSame('resolved', $r4->fresh()->status);
    }

    public function test_interest_help_report_shows_contacts(): void
    {
        $a = $this->admin();
        $u1 = $this->mk();
        $u2 = $this->mk();
        $b1 = $this->bio($u1, 'approved');
        $b2 = $this->bio($u2, 'approved');
        $b2->contact_phone = '8888888888';
        $b2->save();
        $i = \App\Models\Interest::create(['from_biodata_id' => $b1->id, 'to_biodata_id' => $b2->id, 'from_user_id' => $u1->id, 'to_user_id' => $u2->id]);
        Report::create(['reporter_id' => $u1->id, 'target_type' => 'interest_help', 'target_id' => $i->id, 'reason' => 'help']);

        $this->actingAs($a)->get(route('admin.reports.index'))->assertOk()->assertSee('8888888888')->assertSee($u1->phone);
    }

    public function test_settings_and_password(): void
    {
        $a = $this->admin();
        $this->actingAs($a)->put(route('admin.settings.update'), ['site_name' => 'SB', 'interest_daily_limit' => 7])->assertRedirect();
        $this->assertSame('7', \App\Models\Setting::get('interest_daily_limit'));
        $this->actingAs($a)->get(route('admin.settings'))->assertOk()->assertSee('value="7"', false);

        $this->actingAs($a)->put(route('admin.password.update'), ['current_password' => 'wrong', 'password' => 'newpass123', 'password_confirmation' => 'newpass123'])->assertSessionHasErrors('current_password');
        $this->actingAs($a)->put(route('admin.password.update'), ['current_password' => 'secret123', 'password' => 'newpass123', 'password_confirmation' => 'newpass123'])->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $a->fresh()->password));
    }
}
