<?php

namespace Tests\Feature;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Whole product journey, in order:
 * register -> pending -> admin approves -> biodata -> admin approves biodata
 * -> search -> interest -> accept -> contact visible.
 */
class FullJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $phone, string $name): User
    {
        $this->post('/register', [
            'name' => $name, 'phone' => $phone,
            'password' => 'secret1234', 'password_confirmation' => 'secret1234',
            'state' => 'Maharashtra', 'district' => 'Nagpur', 'city' => 'Nagpur', 'area' => 'Sitabuldi',
            'consent' => '1',
        ])->assertRedirect(route('home'));

        $this->post('/logout');

        return User::where('phone', $phone)->firstOrFail();
    }

    private function biodataPayload(string $name, string $gender, string $phone): array
    {
        return [
            'added_by' => 'self', 'name' => $name, 'gender' => $gender,
            'dob' => $gender === 'male' ? now()->subYears(27)->toDateString() : now()->subYears(24)->toDateString(),
            'height_cm' => 165, 'marital_status' => 'never_married',
            'education_level' => 'graduate', 'education' => 'B.Com', 'occupation_type' => 'private', 'occupation' => 'Accountant',
            'father_name' => 'Ramesh', 'mother_name' => 'Sunita', 'brothers' => 1, 'sisters' => 1,
            'gotra' => 'Kashyap', 'district' => 'Nagpur', 'state' => 'Maharashtra', 'current_city' => 'Nagpur',
            'about' => 'Simple family.', 'expectation' => 'Educated and caring.',
            'contact_phone' => $phone, 'photo_privacy' => 'members',
        ];
    }

    public function test_complete_journey(): void
    {
        $this->withoutVite();
        $admin = User::forceCreate(['name' => 'Admin', 'phone' => '9999999999', 'password' => bcrypt('admin12345'), 'role' => 'admin', 'status' => 'active']);

        $groom = $this->register('9100000001', 'Ravi Kumar');
        $bride = $this->register('9100000002', 'Anita Sharma');

        // pending members are held on the status page
        $this->actingAs($groom)->get(route('biodata.index'))->assertRedirect(route('home'));

        // admin approves both
        $this->actingAs($admin);
        foreach ([$groom, $bride] as $u) {
            $this->post(route('admin.users.approve', $u))->assertRedirect();
            $this->assertSame('active', $u->refresh()->status);
        }

        // both create biodata (pending)
        $this->actingAs($groom)->post(route('biodata.store'), $this->biodataPayload('Ravi Kumar', 'male', '9100000001'))->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($bride)->post(route('biodata.store'), $this->biodataPayload('Anita Sharma', 'female', '9100000002'))->assertSessionHasNoErrors()->assertRedirect();
        $gb = Biodata::where('user_id', $groom->id)->firstOrFail();
        $bb = Biodata::where('user_id', $bride->id)->firstOrFail();
        $this->assertSame('pending', $gb->status);

        // not visible in search until approved
        $this->actingAs($groom)->get(route('biodata.index', ['for' => 'bride']))->assertDontSee('Anita');

        // admin approves biodata
        $this->actingAs($admin);
        $this->post(route('admin.biodata.approve', $gb))->assertRedirect();
        $this->post(route('admin.biodata.approve', $bb))->assertRedirect();

        // groom finds bride, contact hidden
        $this->actingAs($groom)->get(route('biodata.index', ['for' => 'bride']))->assertOk()->assertSee('Anita');
        $this->actingAs($groom)->get(route('biodata.show', $bb))->assertOk()->assertDontSee('9100000002');

        // interest -> bride notified
        $this->actingAs($groom)->post(route('interests.store'), ['from_biodata_id' => $gb->id, 'to_biodata_id' => $bb->id])->assertRedirect();
        $interest = Interest::firstOrFail();
        $this->assertSame('sent', $interest->status);
        $this->assertTrue($bride->unreadNotifications()->where('data->type', 'interest_received')->exists());

        // bride accepts -> groom notified, contacts revealed
        $this->actingAs($bride)->post(route('interests.respond', $interest), ['action' => 'accept'])->assertRedirect();
        $this->assertSame('accepted', $interest->fresh()->status);
        $this->assertTrue($groom->unreadNotifications()->where('data->type', 'interest_accepted')->exists());

        \App\Support\BiodataAccess::flush();
        $this->actingAs($groom)->get(route('biodata.show', $bb))->assertOk()->assertSee('9100000002');
    }
}
