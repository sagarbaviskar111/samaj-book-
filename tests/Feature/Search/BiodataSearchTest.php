<?php

namespace Tests\Feature\Search;

use App\Models\Biodata;

class BiodataSearchTest extends SearchTestCase
{
    public function test_guest_redirected_and_pending_user_blocked(): void
    {
        $this->get('/biodata')->assertRedirect();
        $this->actingAs($this->member(['status' => 'pending']))->get('/biodata')->assertRedirect();
    }

    public function test_shows_opposite_gender_approved_only_and_excludes_own(): void
    {
        $me = $this->member();
        $this->bio($me, ['gender' => 'male', 'name' => 'Myself Male']);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Visible Girl']);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Pending Girl', 'status' => 'pending']);
        $this->bio($this->member(), ['gender' => 'male', 'name' => 'Other Boy']);

        $this->actingAs($me)->get('/biodata')
            ->assertOk()->assertSee('Visible')->assertDontSee('Pending')->assertDontSee('Other')->assertDontSee('Myself');
    }

    public function test_own_biodata_excluded_even_when_searching_own_side(): void
    {
        $me = $this->member();
        $this->bio($me, ['gender' => 'female', 'name' => 'Selfie Sita']);
        $this->actingAs($me)->get('/biodata?for=bride')->assertOk()->assertDontSee('Selfie');
    }

    public function test_without_own_biodata_gets_lite_mode_with_choices(): void
    {
        $this->actingAs($this->member())->get('/biodata')->assertOk()->assertSee('मेरा बायोडाटा बनाएँ')->assertSee('किसी और के लिए देख रहे हैं');
    }

    public function test_filters_age_district_education_verified_keyword(): void
    {
        $me = $this->member();
        $this->bio($me, ['gender' => 'male']);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Young Pune', 'dob' => now()->subYears(22)->toDateString(), 'district' => 'Pune', 'education_level' => 'post_graduate', 'verified' => true]);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Older Nagpur', 'dob' => now()->subYears(32)->toDateString(), 'district' => 'Nagpur', 'education_level' => '12']);

        $this->actingAs($me)->get('/biodata?age_max=25')->assertSee('Young')->assertDontSee('Older');
        $this->actingAs($me)->get('/biodata?age_min=30')->assertSee('Older')->assertDontSee('Young');
        $this->actingAs($me)->get('/biodata?district=Pune')->assertSee('Young')->assertDontSee('Older');
        $this->actingAs($me)->get('/biodata?education_level=12')->assertSee('Older')->assertDontSee('Young');
        $this->actingAs($me)->get('/biodata?verified=1')->assertSee('Young')->assertDontSee('Older');
        $this->actingAs($me)->get('/biodata?q=Older')->assertSee('Older')->assertDontSee('Young');
    }

    public function test_exclude_own_gotra_default_on_and_can_be_disabled(): void
    {
        $me = $this->member(['gotra' => 'Kashyap']);
        $this->bio($me, ['gender' => 'male']);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Same Gotra', 'gotra' => 'Kashyap']);
        $this->bio($this->member(), ['gender' => 'female', 'name' => 'Diff Gotra', 'gotra' => 'Atri']);

        $this->actingAs($me)->get('/biodata')->assertSee('Diff')->assertDontSee('Same');
        $this->actingAs($me)->get('/biodata?ex_gotra=0')->assertSee('Diff')->assertSee('Same');
    }

    public function test_pagination_and_sorts(): void
    {
        $me = $this->member();
        $this->bio($me, ['gender' => 'male', 'pref_district' => 'Pune']);
        Biodata::factory()->count(14)->female()->create();
        $this->actingAs($me)->get('/biodata')->assertOk()->assertSee('page=2', false);
        $this->actingAs($me)->get('/biodata?sort=match')->assertOk();
        $this->actingAs($me)->get('/biodata?sort=age&page=2')->assertOk();
    }

    public function test_invalid_filter_rejected(): void
    {
        $me = $this->member(); $this->bio($me, ['gender' => 'male']); $this->actingAs($me)->get('/biodata?for=bride&age_min=5')->assertSessionHasErrors('age_min');
    }
}
