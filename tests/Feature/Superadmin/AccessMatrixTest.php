<?php

namespace Tests\Feature\Superadmin;

use App\Models\Payment;

class AccessMatrixTest extends SuperadminTestCase
{
    public function test_matrix(): void
    {
        $member = $this->mk();
        $p = Payment::create(['amount_paise' => 5000, 'status' => 'paid', 'paid_at' => now()]);
        $urls = [
            route('superadmin.dashboard'), route('superadmin.admins.index'), route('superadmin.admins.create'),
            route('superadmin.logs.audit'), route('superadmin.logs.logins'), route('superadmin.logs.security'),
            route('superadmin.users.show', $member->id), route('superadmin.payments.index'), route('superadmin.payments.create'),
            route('superadmin.payments.show', $p->id), route('superadmin.integrations.index'),
        ];

        foreach ($urls as $u) {
            $this->get($u)->assertRedirect(route('login'));
        }

        foreach (['user' => $this->mk('user'), 'subadmin' => $this->mk('subadmin', 'active', ['admin_area' => 'Nagpur']), 'admin' => $this->mk('admin')] as $who => $actor) {
            foreach ($urls as $u) {
                $this->actingAs($actor)->get($u)->assertForbidden();
            }
        }

        $sa = $this->mk('superadmin');
        foreach ($urls as $u) {
            $this->actingAs($sa)->get($u)->assertOk();
        }
    }

    public function test_blocked_superadmin_and_blocked_admin_lose_access(): void
    {
        $this->actingAs($this->mk('superadmin', 'blocked'))->get(route('superadmin.dashboard'))->assertForbidden();
        $this->actingAs($this->mk('admin', 'blocked'))->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_superadmin_passes_admin_and_full_admin_areas_and_sees_all_areas(): void
    {
        $sa = $this->mk('superadmin');
        $this->actingAs($sa)->get(route('admin.dashboard'))->assertOk()->assertSee('Super admin')->assertSee('SUPERADMIN');
        $this->actingAs($sa)->get(route('admin.settings'))->assertOk();
        $this->actingAs($sa)->get(route('admin.subadmins.index'))->assertOk();
        $this->assertNull(\App\Services\Admin\AdminScope::area($sa));
    }

    public function test_role_labels(): void
    {
        $this->actingAs($this->mk('admin'))->get(route('admin.dashboard'))->assertOk()->assertDontSee('Super admin');
        $sub = $this->mk('subadmin', 'active', ['admin_area' => 'Nagpur']);
        $this->assertSame('Sub-admin · Nagpur', $sub->roleLabel());
        $this->assertSame('Admin', $this->mk('admin')->roleLabel());
    }

    public function test_login_redirects_superadmin_to_console(): void
    {
        $sa = $this->mk('superadmin');
        $this->post(route('login'), ['phone' => $sa->phone, 'password' => 'secret123'])->assertRedirect(route('superadmin.dashboard'));
    }

    public function test_360_link_only_for_superadmin(): void
    {
        $m = $this->mk('user');
        $this->actingAs($this->mk('admin'))->get(route('admin.users.show', $m->id))->assertOk()->assertDontSee('Full profile (360');
        $this->actingAs($this->mk('superadmin'))->get(route('admin.users.show', $m->id))->assertOk()->assertSee('Full profile (360');
    }
}
