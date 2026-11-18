<?php

namespace Tests\Feature\Search;

use App\Models\Interest;
use App\Models\LookingForProfile;
use App\Support\AccessLevel;

class AccessModesTest extends SearchTestCase
{
    private function girl(array $a = [])
    {
        return $this->bio($this->member(), $a + [
            'gender' => 'female', 'name' => 'Sunita Zendekar', 'father_name' => 'Ramesh Zendekar',
            'education' => 'Quantum Physics', 'occupation' => 'Astronaut', 'village' => 'Chandanpur', 'district' => 'Wardha',
            'gotra' => 'Kashyapx', 'about' => 'Secret about text', 'height_cm' => 161,
        ]);
    }

    public function test_access_levels(): void
    {
        $lite = $this->member();
        $draftOnly = $this->member();
        $this->bio($draftOnly, ['status' => 'draft']);
        $viewOnly = $this->member();
        LookingForProfile::create(['user_id' => $viewOnly->id, 'for_whom' => 'son', 'looking_for' => 'bride']);
        $full = $this->member();
        $this->bio($full, ['status' => 'pending']);
        $admin = $this->member(['role' => 'admin']);

        $this->assertSame('lite', AccessLevel::for($lite));
        $this->assertSame('lite', AccessLevel::for($draftOnly));
        $this->assertSame('view_only', AccessLevel::for($viewOnly));
        $this->assertSame('full', AccessLevel::for($full));
        $this->assertSame('full', AccessLevel::for($admin));
    }

    public function test_lite_search_hides_everything_and_ignores_filters(): void
    {
        $this->girl();
        $this->girl(['name' => 'Rekha Other', 'district' => 'Pune']);
        $me = $this->member();

        $r = $this->actingAs($me)->get('/biodata?for=bride&district=Pune&q=Rekha&age_min=40&sort=age')->assertOk();
        $r->assertSee('Sunita')->assertSee('Rekha'); // filters ignored: both shown
        $r->assertDontSee('Zendekar')->assertDontSee('Ramesh')->assertDontSee('Quantum')->assertDontSee('Astronaut')
            ->assertDontSee('Chandanpur')->assertDontSee('Wardha')->assertDontSee('Kashyapx')->assertDontSee('Secret')
            ->assertDontSee('161 cm')->assertDontSee('161cm')->assertDontSee('% match')->assertDontSee('name="age_min"', false)->assertDontSee('name="q"', false)
            ->assertDontSee('data-shortlist', false);
        $r->assertSee('मेरा बायोडाटा बनाएँ');
    }

    public function test_lite_show_hides_everything(): void
    {
        $b = $this->girl();
        $r = $this->actingAs($this->member())->get('/biodata/'.$b->id)->assertOk();
        $r->assertSee('Sunita')->assertSee((string) $b->dob->age)->assertSee('मेरा बायोडाटा बनाएँ')->assertSee(route('looking-for.create'), false);
        $r->assertDontSee('Zendekar')->assertDontSee('Ramesh')->assertDontSee('Quantum')->assertDontSee('Astronaut')
            ->assertDontSee('Chandanpur')->assertDontSee('Wardha')->assertDontSee('Secret')->assertDontSee('पसंद भेजें')
            ->assertDontSee('9123456780');
    }

    public function test_lite_cannot_shortlist_or_send(): void
    {
        $b = $this->girl();
        $me = $this->member();
        $this->actingAs($me)->post('/shortlist/'.$b->id)->assertForbidden();
        $this->post('/interests', ['from_biodata_id' => $b->id, 'to_biodata_id' => $b->id])->assertSessionHas('error');
        $this->assertSame(0, Interest::count());
    }

    public function test_view_only_sees_full_but_cannot_send(): void
    {
        $b = $this->girl();
        $me = $this->member();
        LookingForProfile::create(['user_id' => $me->id, 'for_whom' => 'brother', 'looking_for' => 'bride']);

        $this->actingAs($me)->get('/biodata/'.$b->id)->assertOk()->assertSee('Zendekar')->assertSee('Quantum')
            ->assertSee('पसंद भेजने के लिए पहले अपना बायोडाटा बनाइए')->assertDontSee('name="from_biodata_id"', false);
        $this->get('/biodata?for=bride&nd=1')->assertOk()->assertSee('Sunita')->assertSee('भाई');
        $this->post('/interests', ['from_biodata_id' => $b->id, 'to_biodata_id' => $b->id])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'बायोडाटा बनाइए'));
        $this->assertSame(0, Interest::count());
    }

    public function test_full_user_unaffected(): void
    {
        $b = $this->girl();
        $me = $this->member();
        $this->bio($me, ['gender' => 'male']);
        $this->actingAs($me)->get('/biodata/'.$b->id)->assertOk()->assertSee('Zendekar')->assertSee('पसंद भेजें');
        $this->get('/biodata?for=bride&district=Pune')->assertOk()->assertDontSee('Sunita');
    }

    public function test_looking_for_crud_and_defaults(): void
    {
        $me = $this->member();
        $this->girl(['name' => 'Zoya Alpha', 'district' => 'Nagpur', 'education_level' => 'graduate', 'dob' => now()->subYears(25)->toDateString()]);
        $this->girl(['name' => 'Wanda Beta', 'district' => 'Pune', 'education_level' => 'graduate']);

        $this->actingAs($me)->get('/looking-for')->assertOk()->assertSee('आप किसके लिए रिश्ता देख रहे हैं');
        $this->post('/looking-for', ['for_whom' => 'x', 'looking_for' => 'bride'])->assertSessionHasErrors('for_whom');
        $this->post('/looking-for', ['for_whom' => 'daughter', 'looking_for' => 'bride', 'age_min' => 22, 'age_max' => 28, 'district' => 'Nagpur', 'education_level' => 'graduate'])
            ->assertRedirect();
        $this->assertSame(1, LookingForProfile::where('user_id', $me->id)->count());

        $this->get('/biodata')->assertOk()->assertSee('Zoya')->assertDontSee('Wanda')->assertSee('बेटी')->assertSee('बदलें');
        // explicit filters override the defaults
        $this->get('/biodata?for=bride&district=Pune')->assertSee('Wanda')->assertDontSee('Zoya');

        $this->get('/looking-for')->assertRedirect(route('looking-for.edit'));
        $this->put('/looking-for', ['for_whom' => 'son', 'looking_for' => 'groom'])->assertRedirect();
        $this->assertSame('son', LookingForProfile::first()->for_whom);
        $this->delete('/looking-for')->assertRedirect();
        $this->assertSame(0, LookingForProfile::count());
    }

    // ---- on behalf ----

    private function onBehalfSetup(): array
    {
        $sender = $this->member(['name' => 'Rahul Sender']);
        $self = $this->bio($sender, ['gender' => 'male', 'name' => 'Rahul Self']);
        $cousin = $this->bio($sender, ['gender' => 'female', 'name' => 'Neha Cousin', 'added_by' => 'cousin']);
        $receiver = $this->member(['phone' => '8111111111']);
        $target = $this->bio($receiver, ['gender' => 'male', 'name' => 'Target Boy', 'contact_phone' => '9222222222']);

        return [$sender, $self, $cousin, $receiver, $target];
    }

    public function test_on_behalf_eligibility_list_only_opposite_gender(): void
    {
        [$sender, $self, , , $target] = $this->onBehalfSetup();
        $r = $this->actingAs($sender)->get('/biodata/'.$target->id)->assertOk();
        $r->assertSee('पसंद किसकी तरफ़ से भेज रहे हैं?')->assertSee('Neha Cousin (कज़िन)')
            ->assertDontSee('अपने लिए: Rahul Self');
        $this->post('/interests', ['from_biodata_id' => $self->id, 'to_biodata_id' => $target->id])->assertSessionHas('error');
    }

    public function test_no_eligible_profile_shows_create_hint(): void
    {
        $sender = $this->member();
        $this->bio($sender, ['gender' => 'male']);
        $target = $this->bio($this->member(), ['gender' => 'male']);
        $this->actingAs($sender)->get('/biodata/'.$target->id)->assertSee('पहले लड़की का बायोडाटा बनाइए');
    }

    public function test_on_behalf_notification_badge_and_contacts(): void
    {
        [$sender, , $cousin, $receiver, $target] = $this->onBehalfSetup();
        $this->actingAs($sender)->post('/interests', ['from_biodata_id' => $cousin->id, 'to_biodata_id' => $target->id])->assertSessionHas('status');

        $n = $receiver->notifications()->first()->data;
        $this->assertStringContainsString('Rahul Sender ने अपने कज़िन Neha के लिए पसंद भेजी है', $n['title']);

        $r = $this->actingAs($receiver)->get('/interests?tab=received')->assertOk();
        $r->assertSee('कज़िन के लिए')->assertSee('भेजा: Rahul Sender')->assertSee('Neha');

        $i = Interest::first();
        $this->post("/interests/{$i->id}/respond", ['action' => 'accept']);
        $this->assertStringContainsString('कज़िन Neha', $sender->notifications()->first()->data['title']);

        $r = $this->actingAs($receiver)->get('/interests?tab=accepted')->assertOk();
        $r->assertSee('9123456780')->assertSee($sender->phone)->assertSee('Rahul Sender');
    }

    public function test_on_behalf_rejection_mentions_relative(): void
    {
        [$sender, , $cousin, $receiver, $target] = $this->onBehalfSetup();
        $this->actingAs($sender)->post('/interests', ['from_biodata_id' => $cousin->id, 'to_biodata_id' => $target->id]);
        $this->actingAs($receiver)->post('/interests/'.Interest::first()->id.'/respond', ['action' => 'reject']);
        $this->assertStringContainsString('कज़िन Neha', $sender->notifications()->first()->data['title']);
    }
}
