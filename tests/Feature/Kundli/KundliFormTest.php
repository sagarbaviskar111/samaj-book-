<?php

namespace Tests\Feature\Kundli;

use App\Models\Biodata;
use App\Models\User;
use App\Support\BiodataAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KundliFormTest extends TestCase
{
    use RefreshDatabase;

    private User $u;

    protected function setUp(): void
    {
        parent::setUp();
        BiodataAccess::flush();
        $this->withoutVite();
        $this->u = User::factory()->create(['phone' => '9811111111', 'status' => 'active', 'role' => 'user']);
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'added_by' => 'self', 'name' => 'Asha Tester', 'gender' => 'female',
            'dob' => now()->subYears(24)->toDateString(), 'marital_status' => 'never_married',
            'contact_phone' => '9123456780', 'photo_privacy' => 'members',
            'brothers' => 0, 'brothers_married' => 0, 'sisters' => 0, 'sisters_married' => 0,
        ], $o);
    }

    public function test_optional_fields_can_be_empty(): void
    {
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['rashi' => '', 'nakshatra' => '', 'nakshatra_pada' => '']))->assertSessionDoesntHaveErrors();
        $b = Biodata::first();
        $this->assertNull($b->rashi);
        $this->assertNull($b->nakshatra);
    }

    public function test_rashi_is_derived_from_nakshatra_and_pada(): void
    {
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'rohini', 'nakshatra_pada' => 2]))->assertSessionDoesntHaveErrors();
        $this->assertSame('vrishabha', Biodata::first()->rashi);
    }

    public function test_contradicting_rashi_is_rejected(): void
    {
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'rohini', 'nakshatra_pada' => 2, 'rashi' => 'mesha']))->assertSessionHasErrors('rashi');
        $this->assertSame(0, Biodata::count());
    }

    public function test_rashi_with_nakshatra_without_pada(): void
    {
        // krittika spans mesha and vrishabha
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'krittika', 'rashi' => 'vrishabha']))->assertSessionDoesntHaveErrors();
        $this->assertSame('vrishabha', Biodata::first()->rashi);
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'krittika', 'rashi' => 'tula']))->assertSessionHasErrors('rashi');
    }

    public function test_invalid_values_and_pada_without_nakshatra(): void
    {
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'nope']))->assertSessionHasErrors('nakshatra');
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'rohini', 'nakshatra_pada' => 5]))->assertSessionHasErrors('nakshatra_pada');
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['rashi' => 'zz']))->assertSessionHasErrors('rashi');
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra_pada' => 2]))->assertSessionHasErrors('nakshatra');
    }

    public function test_owner_edit_form_shows_values_and_update_works(): void
    {
        $this->actingAs($this->u)->post(route('biodata.store'), $this->payload(['nakshatra' => 'hasta', 'nakshatra_pada' => 3]))->assertSessionDoesntHaveErrors();
        $b = Biodata::first();
        $this->actingAs($this->u)->get(route('biodata.edit', $b))->assertOk()->assertSee('f-nakshatra', false)->assertSee('कुंडली में लिखा होता है');
        $this->actingAs($this->u)->put(route('biodata.update', $b), $this->payload(['nakshatra' => 'ashwini', 'nakshatra_pada' => 1]))->assertSessionDoesntHaveErrors();
        $this->assertSame('mesha', $b->fresh()->rashi);
    }
}
