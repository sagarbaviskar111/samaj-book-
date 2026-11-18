<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Biodata;
use App\Models\NewsItem;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAdminTest extends TestCase
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
            'name' => "User {$this->n}", 'phone' => '91000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT),
            'password' => 'secret123', 'role' => $role, 'status' => $status,
        ], $extra)));
    }

    private function sub(string $area = 'Nagpur'): User
    {
        return $this->mk('subadmin', 'active', ['admin_area' => $area]);
    }

    private function member(string $district, string $status = 'pending'): User
    {
        return $this->mk('user', $status, ['district' => $district, 'city' => $district]);
    }

    private function bio(User $owner, string $district, string $status = 'pending'): Biodata
    {
        return Biodata::unguarded(fn () => Biodata::create([
            'user_id' => $owner->id, 'name' => 'Sita', 'gender' => 'female', 'dob' => '2000-01-01',
            'status' => $status, 'district' => $district,
        ]));
    }

    public function test_member_scope_isolation(): void
    {
        $sub = $this->sub('Nagpur');
        $mine = $this->member('Nagpur');
        $other = $this->member('Pune');

        $this->actingAs($sub)->get(route('admin.users.show', $mine->id))->assertOk();
        $this->actingAs($sub)->get(route('admin.users.show', $other->id))->assertNotFound();
        $this->actingAs($sub)->post(route('admin.users.approve', $other->id))->assertNotFound();
        $this->assertSame('pending', $other->fresh()->status);
        $this->actingAs($sub)->post(route('admin.users.reject', $other->id), ['reason' => 'bad data'])->assertNotFound();

        $this->actingAs($sub)->get(route('admin.users.index'))->assertSee($mine->name)->assertDontSee($other->name);

        $this->actingAs($sub)->post(route('admin.users.approve', $mine->id))->assertRedirect();
        $this->assertSame('active', $mine->fresh()->status);
        $log = AuditLog::where('action', 'user.approve')->first();
        $this->assertSame($sub->id, $log->admin_id);

        $this->actingAs($sub)->post(route('admin.users.bulk-approve'), ['ids' => [$other->id]]);
        $this->assertSame('pending', $other->fresh()->status);
    }

    public function test_biodata_scope_isolation(): void
    {
        $sub = $this->sub('Nagpur');
        $mine = $this->bio($this->member('Nagpur'), 'Nagpur');
        $other = $this->bio($this->member('Pune'), 'Pune');

        $this->actingAs($sub)->get(route('admin.biodata.show', $other->id))->assertNotFound();
        $this->actingAs($sub)->post(route('admin.biodata.approve', $other->id))->assertNotFound();
        $this->assertSame('pending', $other->fresh()->status);

        $this->actingAs($sub)->post(route('admin.biodata.approve', $mine->id))->assertRedirect();
        $this->assertSame('approved', $mine->fresh()->status);
    }

    public function test_news_scope_and_area_autoset_and_notification(): void
    {
        $sub = $this->sub('Nagpur');
        $inArea = $this->member('Nagpur', 'active');
        $outArea = $this->member('Pune', 'active');
        $pendingIn = $this->member('Nagpur', 'pending');

        $this->actingAs($sub)->post(route('admin.news.store'), [
            'type' => 'news', 'title' => 'Nagpur meet', 'is_published' => 1, 'notify_all' => 1, 'area' => 'Pune',
        ])->assertRedirect(route('admin.news.index'));

        $item = NewsItem::first();
        $this->assertSame('Nagpur', $item->area);
        $this->assertSame(1, $inArea->notifications()->count());
        $this->assertSame(0, $outArea->notifications()->count());
        $this->assertSame(0, $pendingIn->notifications()->count());

        $foreign = NewsItem::create(['type' => 'news', 'title' => 'Pune only', 'area' => 'Pune', 'created_by' => $this->mk('admin')->id]);
        $global = NewsItem::create(['type' => 'news', 'title' => 'Global', 'created_by' => null]);

        $this->actingAs($sub)->get(route('admin.news.index'))->assertSee('Nagpur meet')->assertDontSee('Pune only')->assertDontSee('Global');
        $this->actingAs($sub)->get(route('admin.news.edit', $foreign->id))->assertNotFound();
        $this->actingAs($sub)->get(route('admin.news.edit', $global->id))->assertNotFound();
        $this->actingAs($sub)->put(route('admin.news.update', $foreign->id), ['type' => 'news', 'title' => 'hack'])->assertNotFound();
        $this->actingAs($sub)->delete(route('admin.news.destroy', $foreign->id))->assertNotFound();
        $this->actingAs($sub)->post(route('admin.news.publish', $foreign->id))->assertNotFound();

        $this->actingAs($sub)->put(route('admin.news.update', $item->id), ['type' => 'news', 'title' => 'Renamed'])->assertRedirect();
        $this->assertSame('Nagpur', $item->fresh()->area);
        $this->actingAs($sub)->delete(route('admin.news.destroy', $item->id))->assertRedirect();
        $this->assertNull(NewsItem::find($item->id));
    }

    public function test_main_admin_news_stays_global(): void
    {
        $admin = $this->mk('admin');
        $a = $this->member('Nagpur', 'active');
        $b = $this->member('Pune', 'active');
        $this->actingAs($admin)->post(route('admin.news.store'), ['type' => 'news', 'title' => 'All', 'is_published' => 1, 'notify_all' => 1]);
        $this->assertNull(NewsItem::first()->area);
        $this->assertSame(1, $a->notifications()->count());
        $this->assertSame(1, $b->notifications()->count());
    }

    public function test_report_scope(): void
    {
        $sub = $this->sub('Nagpur');
        $reporter = $this->member('Pune', 'active');
        $inUser = $this->member('Nagpur', 'active');
        $outUser = $this->member('Pune', 'active');
        $inPost = Post::unguarded(fn () => Post::create(['user_id' => $inUser->id, 'body' => 'x']));
        $outPost = Post::unguarded(fn () => Post::create(['user_id' => $outUser->id, 'body' => 'y']));

        $rIn = Report::unguarded(fn () => Report::create(['reporter_id' => $reporter->id, 'target_type' => 'post', 'target_id' => $inPost->id, 'status' => 'open']));
        $rOut = Report::unguarded(fn () => Report::create(['reporter_id' => $reporter->id, 'target_type' => 'post', 'target_id' => $outPost->id, 'status' => 'open']));

        $this->actingAs($sub)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($sub)->post(route('admin.reports.resolve', $rOut->id))->assertNotFound();
        $this->actingAs($sub)->post(route('admin.reports.hide-post', $rOut->id))->assertNotFound();
        $this->assertSame('visible', $outPost->fresh()->status);

        $this->actingAs($sub)->post(route('admin.reports.hide-post', $rIn->id))->assertRedirect();
        $this->assertSame('hidden', $inPost->fresh()->status);
        $this->assertSame('resolved', $rIn->fresh()->status);

        // blocking users is main-admin only
        $this->actingAs($sub)->post(route('admin.reports.block-user', $rIn->id))->assertForbidden();
    }

    public function test_forbidden_pages_for_subadmin(): void
    {
        $sub = $this->sub();
        $m = $this->member('Nagpur');
        $b = $this->bio($m, 'Nagpur');

        foreach (['admin.settings', 'admin.audit', 'admin.subadmins.index', 'admin.subadmins.create', 'admin.stories.index'] as $r) {
            $this->actingAs($sub)->get(route($r))->assertForbidden();
        }
        $this->actingAs($sub)->put(route('admin.settings.update'), [])->assertForbidden();
        $this->actingAs($sub)->post(route('admin.subadmins.store'), [])->assertForbidden();
        $this->actingAs($sub)->post(route('admin.users.block', $m->id))->assertForbidden();
        $this->actingAs($sub)->post(route('admin.users.reset-password', $m->id))->assertForbidden();
        $this->actingAs($sub)->delete(route('admin.users.destroy', $m->id))->assertForbidden();
        $this->actingAs($sub)->delete(route('admin.biodata.destroy', $b->id))->assertForbidden();
        $this->actingAs($sub)->post(route('admin.biodata.verify', $b->id))->assertForbidden();
        $this->assertNotNull(User::find($m->id));
    }

    public function test_subadmin_ui(): void
    {
        $sub = $this->sub('Nagpur');
        $this->actingAs($sub)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('SUB-ADMIN · Nagpur')->assertSee('Area: Nagpur')
            ->assertDontSee('Sub-admins')->assertDontSee('Audit log')->assertDontSee('Success stories');

        $this->actingAs($this->mk('admin'))->get(route('admin.dashboard'))->assertOk()->assertSee('Sub-admins');

        $this->assertSame(route('admin.dashboard'), url('/admin'));
    }

    public function test_dashboard_stats_scoped(): void
    {
        $sub = $this->sub('Nagpur');
        $this->member('Nagpur');
        $this->member('Pune');
        $this->member('Pune');
        $stats = $this->actingAs($sub)->get(route('admin.dashboard'))->viewData('stats');
        $this->assertSame(1, $stats['members']);
        $this->assertSame(1, $stats['pending_members']);
    }

    public function test_blocked_subadmin_loses_access(): void
    {
        $sub = $this->sub();
        $sub->forceFill(['status' => 'blocked'])->save();
        $this->actingAs($sub)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_subadmin_without_area_sees_nothing(): void
    {
        $sub = $this->mk('subadmin', 'active', ['admin_area' => null]);
        $this->member('Nagpur');
        $this->actingAs($sub)->get(route('admin.dashboard'))->assertOk()->viewData('stats');
        $this->assertSame(0, $this->actingAs($sub)->get(route('admin.dashboard'))->viewData('stats')['members']);
    }

    public function test_main_admin_manages_subadmins_and_audit_shows_who(): void
    {
        $admin = $this->mk('admin');

        $this->actingAs($admin)->get(route('admin.subadmins.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.subadmins.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.subadmins.store'), [
            'name' => 'Ravi', 'phone' => '9777777701', 'password' => 'longpass1', 'admin_area' => 'Nagpur', 'status' => 'active',
        ])->assertRedirect(route('admin.subadmins.index'));
        $sub = User::where('phone', '9777777701')->first();
        $this->assertSame('subadmin', $sub->role);
        $this->assertSame('Nagpur', $sub->admin_area);

        $this->actingAs($admin)->post(route('admin.subadmins.store'), [
            'name' => 'Bad', 'phone' => '9777777702', 'password' => 'longpass1', 'admin_area' => 'Atlantis', 'status' => 'active',
        ])->assertSessionHasErrors('admin_area');

        $this->actingAs($admin)->get(route('admin.subadmins.edit', $sub->id))->assertOk();
        $this->actingAs($admin)->put(route('admin.subadmins.update', $sub->id), [
            'name' => 'Ravi K', 'phone' => '9777777701', 'password' => 'newpass123', 'admin_area' => 'Pune', 'status' => 'blocked',
        ])->assertRedirect();
        $sub->refresh();
        $this->assertSame('Pune', $sub->admin_area);
        $this->assertSame('blocked', $sub->status);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $sub->password));

        // empty password keeps the old one
        $this->actingAs($admin)->put(route('admin.subadmins.update', $sub->id), [
            'name' => 'Ravi K', 'phone' => '9777777701', 'password' => '', 'admin_area' => 'Pune', 'status' => 'active',
        ])->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $sub->fresh()->password));

        // main admin cannot manage ordinary users through this resource
        $m = $this->member('Nagpur');
        $this->actingAs($admin)->get(route('admin.subadmins.edit', $m->id))->assertNotFound();

        // sub-admin acts, main admin audit log shows name + area
        $sub = $sub->fresh();
        $mem = $this->member('Pune');
        $this->actingAs($sub)->post(route('admin.users.approve', $mem->id))->assertRedirect();
        $this->actingAs($admin)->get(route('admin.audit'))->assertOk()->assertSee('Ravi K')->assertSee('Pune');

        $this->actingAs($admin)->delete(route('admin.subadmins.destroy', $sub->id))->assertRedirect();
        $this->assertNull(User::find($sub->id));
    }

    public function test_main_admin_still_sees_everything(): void
    {
        $admin = $this->mk('admin');
        $a = $this->member('Nagpur');
        $b = $this->member('Pune');
        $this->actingAs($admin)->get(route('admin.users.show', $a->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.show', $b->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings'))->assertOk();
    }
}
