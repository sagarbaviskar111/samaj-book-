<?php

namespace Tests\Feature\Superadmin;

use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class AdminsAndLogsTest extends SuperadminTestCase
{
    public function test_admin_crud_block_and_reset(): void
    {
        $sa = $this->mk('superadmin');
        $this->actingAs($sa)->post(route('superadmin.admins.store'), ['name' => 'New Admin', 'phone' => '9811111111', 'password' => 'longpass1', 'email' => 'a@x.in'])
            ->assertRedirect(route('superadmin.admins.index'));
        $a = User::where('phone', '9811111111')->firstOrFail();
        $this->assertSame('admin', $a->role);
        $this->assertSame('active', $a->status);

        $this->post(route('superadmin.admins.update', $a->id), ['_method' => 'PUT', 'name' => 'Renamed', 'phone' => '9811111111', 'password' => ''])->assertRedirect();
        $this->assertSame('Renamed', $a->fresh()->name);

        $this->post(route('superadmin.admins.block', $a->id))->assertRedirect();
        $this->assertSame('blocked', $a->fresh()->status);
        // takes effect on the next request
        $this->actingAs($a->fresh())->get(route('admin.dashboard'))->assertForbidden();

        $this->actingAs($sa)->post(route('superadmin.admins.unblock', $a->id));
        $this->assertSame('active', $a->fresh()->status);
        $this->actingAs($a->fresh())->get(route('admin.dashboard'))->assertOk();

        $this->actingAs($sa)->post(route('superadmin.admins.reset-password', $a->id))->assertSessionHas('temp_password');
        $this->delete(route('superadmin.admins.destroy', $a->id))->assertRedirect();
        $this->assertNull(User::find($a->id));

        foreach (['admin.create', 'admin.update', 'admin.block', 'admin.unblock', 'admin.reset-password', 'admin.delete'] as $act) {
            $this->assertTrue(AuditLog::where('action', 'sa.'.$act)->exists(), $act);
        }
    }

    public function test_cannot_create_or_touch_superadmin_from_ui(): void
    {
        $sa = $this->mk('superadmin');
        $other = $this->mk('superadmin');
        $this->actingAs($sa)->post(route('superadmin.admins.store'), ['name' => 'X', 'phone' => '9822222222', 'password' => 'longpass1', 'role' => 'superadmin']);
        $this->assertSame('admin', User::where('phone', '9822222222')->value('role'));

        $this->get(route('superadmin.admins.edit', $other->id))->assertNotFound();
        $this->post(route('superadmin.admins.block', $other->id))->assertNotFound();
        $this->delete(route('superadmin.admins.destroy', $sa->id))->assertNotFound();
        $this->assertNotNull(User::find($sa->id));
    }

    public function test_admin_cannot_create_admins(): void
    {
        $this->actingAs($this->mk('admin'))->post(route('superadmin.admins.store'), ['name' => 'X', 'phone' => '9833333333', 'password' => 'longpass1'])->assertForbidden();
    }

    public function test_audit_separation(): void
    {
        $sa = $this->mk('superadmin');
        $admin = $this->mk('admin');
        AuditLog::create(['admin_id' => $sa->id, 'action' => 'user.approve', 'note' => 'by-superadmin-note']);
        AuditLog::create(['admin_id' => $admin->id, 'action' => 'sa.payment.create', 'note' => 'sa-prefixed-note']);
        AuditLog::create(['admin_id' => $admin->id, 'action' => 'user.reject', 'note' => 'by-admin-note']);

        $this->actingAs($admin)->get(route('admin.audit'))->assertOk()->assertSee('by-admin-note')->assertDontSee('by-superadmin-note')->assertDontSee('sa-prefixed-note');
        $this->actingAs($sa)->get(route('superadmin.logs.audit'))->assertOk()->assertSee('by-admin-note')->assertSee('by-superadmin-note')->assertSee('sa-prefixed-note');
        $this->get(route('superadmin.logs.audit', ['actor' => $admin->name]))->assertSee('by-admin-note')->assertDontSee('by-superadmin-note');
        $this->get(route('superadmin.logs.audit.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_login_logs_recorded_and_security_tab(): void
    {
        $u = $this->mk('user');
        $this->post(route('login'), ['phone' => $u->phone, 'password' => 'wrong-pass'])->assertSessionHasErrors('phone');
        $this->post(route('login'), ['phone' => '9000000000', 'password' => 'wrong-pass']);
        $this->post(route('login'), ['phone' => $u->phone, 'password' => 'secret123']);
        $this->post(route('logout'));

        $this->assertSame(2, LoginLog::where('event', 'failed')->count());
        $this->assertSame(1, LoginLog::where('event', 'login')->where('user_id', $u->id)->count());
        $this->assertSame(1, LoginLog::where('event', 'logout')->where('user_id', $u->id)->count());
        $this->assertStringNotContainsString('wrong-pass', json_encode(LoginLog::all()->toArray()));

        $sa = $this->mk('superadmin');
        $this->actingAs($sa)->get(route('superadmin.logs.logins', ['event' => 'failed']))->assertOk()->assertSee('9000000000');
        $this->get(route('superadmin.logs.logins.export'))->assertOk();
        $this->get(route('superadmin.logs.security'))->assertOk()->assertSee('9000000000')->assertSee('127.0.0.1');
    }

    public function test_create_command(): void
    {
        $this->artisan('superadmin:create', ['phone' => '9844444444', 'name' => 'Boss', '--password' => 'longpass1'])->assertExitCode(0);
        $u = User::where('phone', '9844444444')->firstOrFail();
        $this->assertSame('superadmin', $u->role);
        $this->assertTrue(password_verify('longpass1', $u->password));

        $this->artisan('superadmin:create', ['phone' => '9844444444', 'name' => 'Dup', '--password' => 'longpass1'])->assertExitCode(1);
        $this->artisan('superadmin:create', ['phone' => '123', 'name' => 'Bad', '--password' => 'longpass1'])->assertExitCode(1);
        $this->artisan('superadmin:create', ['phone' => '9855555555', 'name' => 'Short', '--password' => 'abc'])->assertExitCode(1);
    }
}
