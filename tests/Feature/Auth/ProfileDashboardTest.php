<?php

namespace Tests\Feature\Auth;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function member(string $phone = '9200000001'): User
    {
        return User::forceCreate(['name' => 'Rahul Gaikwad', 'phone' => $phone, 'password' => bcrypt('secret1234'),
            'role' => 'user', 'status' => 'active', 'district' => 'Nagpur', 'city' => 'Nagpur']);
    }

    public function test_profile_dashboard_shows_menu_and_biodata(): void
    {
        $this->withoutVite();
        $u = $this->member();
        Biodata::factory()->create(['user_id' => $u->id, 'name' => 'Rahul Gaikwad', 'status' => 'approved']);

        $this->actingAs($u)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('मेरे बायोडाटा')
            ->assertSee('Rahul Gaikwad')
            ->assertSee('सूचना सेटिंग')
            ->assertSee('गोपनीयता नीति')
            ->assertSee('मेरा अकाउंट हटाएँ');
    }

    public function test_edit_page_renders(): void
    {
        $this->withoutVite();
        $this->actingAs($this->member())->get(route('profile.edit'))->assertOk();
    }

    public function test_delete_account_needs_correct_password(): void
    {
        $u = $this->member();

        $this->actingAs($u)->delete(route('profile.destroy'), ['confirm_password' => 'wrong'])
            ->assertSessionHasErrors('confirm_password', null, 'delete');
        $this->assertNotNull(User::find($u->id));
    }

    public function test_delete_account_removes_user_and_data(): void
    {
        $u = $this->member();
        Biodata::factory()->create(['user_id' => $u->id]);

        $this->actingAs($u)->delete(route('profile.destroy'), ['confirm_password' => 'secret1234'])->assertRedirect('/');

        $this->assertNull(User::find($u->id));
        $this->assertSame(0, Biodata::where('user_id', $u->id)->count());
        $this->assertGuest();
    }

    public function test_admin_cannot_self_delete_here(): void
    {
        $a = User::forceCreate(['name' => 'Admin', 'phone' => '9999999990', 'password' => bcrypt('secret1234'), 'role' => 'admin', 'status' => 'active']);

        $this->actingAs($a)->delete(route('profile.destroy'), ['confirm_password' => 'secret1234'])->assertForbidden();
    }
}
