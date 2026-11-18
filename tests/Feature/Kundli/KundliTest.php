<?php

namespace Tests\Feature\Kundli;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\User;
use App\Support\BiodataAccess;
use App\Support\KundliAccess;
use Tests\Feature\Search\SearchTestCase;

class KundliTest extends SearchTestCase
{
    private User $boyU;
    private User $girlU;
    private Biodata $boy;
    private Biodata $girl;

    protected function setUp(): void
    {
        parent::setUp();
        BiodataAccess::flush();
        $this->boyU = $this->member();
        $this->girlU = $this->member();
        $this->boy = $this->bio($this->boyU, ['gender' => 'male', 'name' => 'Rahul Test', 'rashi' => 'vrishabha', 'nakshatra' => 'rohini', 'nakshatra_pada' => 2, 'manglik' => true]);
        $this->girl = $this->bio($this->girlU, ['gender' => 'female', 'name' => 'Pooja Test', 'rashi' => 'kanya', 'nakshatra' => 'hasta', 'nakshatra_pada' => 3, 'manglik' => false]);
    }

    private function interest(string $status, ?Biodata $from = null, ?Biodata $to = null): Interest
    {
        $from ??= $this->boy;
        $to ??= $this->girl;

        return Interest::create([
            'from_biodata_id' => $from->id, 'to_biodata_id' => $to->id,
            'from_user_id' => $from->user_id, 'to_user_id' => $to->user_id, 'status' => $status,
        ]);
    }

    private function admin(): User
    {
        return $this->member(['role' => 'admin']);
    }

    public function test_access_matrix(): void
    {
        $stranger = $this->member();
        $this->bio($stranger, ['gender' => 'male']);
        $admin = $this->admin();

        foreach (['sent', 'rejected', 'withdrawn', 'expired'] as $status) {
            $i = $this->interest($status);
            BiodataAccess::flush();
            $this->assertFalse(KundliAccess::canSeeKundli($this->boyU, $this->boy, $this->girl), $status);
            $this->assertFalse(KundliAccess::canSeeKundli($this->girlU, $this->boy, $this->girl), $status);
            $this->actingAs($this->boyU)->get(route('interests.kundli', $i))->assertForbidden();
            $this->actingAs($this->girlU)->get(route('interests.kundli', $i))->assertForbidden();
            $i->delete();
        }

        $this->assertFalse(KundliAccess::canSeeKundli($this->boyU, $this->boy, $this->girl));
        $this->assertTrue(KundliAccess::canSeeKundli($admin, $this->boy, $this->girl));

        $i = $this->interest('accepted');
        $this->assertTrue(KundliAccess::canSeeKundli($this->boyU, $this->boy, $this->girl));
        $this->assertTrue(KundliAccess::canSeeKundli($this->girlU, $this->boy, $this->girl));
        $this->assertFalse(KundliAccess::canSeeKundli($stranger, $this->boy, $this->girl));
        $this->actingAs($stranger)->get(route('interests.kundli', $i))->assertForbidden();
        $this->actingAs($this->boyU)->get(route('interests.kundli', $i))->assertOk();
        $this->actingAs($this->girlU)->get(route('interests.kundli', $i))->assertOk();
        $this->actingAs($admin)->get(route('interests.kundli', $i))->assertOk();
        auth()->logout();
        $this->get(route('interests.kundli', $i))->assertRedirect();
    }

    public function test_reverse_direction_and_on_behalf_sender(): void
    {
        // girl's side (a relative, added_by != self) sent the request, boy accepted
        $relative = $this->member();
        $girl2 = $this->bio($relative, ['gender' => 'female', 'added_by' => 'cousin', 'rashi' => 'kanya', 'nakshatra' => 'hasta']);
        $i = $this->interest('accepted', $girl2, $this->boy);
        $this->actingAs($relative)->get(route('interests.kundli', $i))->assertOk()->assertSee('Rahul Test');
        $this->actingAs($this->boyU)->get(route('interests.kundli', $i))->assertOk();
        $this->actingAs($this->girlU)->get(route('interests.kundli', $i))->assertForbidden();
    }

    public function test_data_hidden_before_acceptance_and_visible_after(): void
    {
        $viewer = $this->boyU;
        $this->actingAs($viewer)->get(route('biodata.show', $this->girl))->assertOk()->assertDontSee('Kanya')->assertDontSee('Hasta')->assertDontSee('kundli-card', false);

        $i = $this->interest('sent');
        BiodataAccess::flush();
        $this->actingAs($viewer)->get(route('biodata.show', $this->girl))->assertDontSee('Kanya')->assertDontSee('Hasta');

        $i->update(['status' => 'accepted']);
        BiodataAccess::flush();
        $this->actingAs($viewer)->get(route('biodata.show', $this->girl))->assertOk()
            ->assertSee('Kanya')->assertSee('Hasta')->assertSee('कुंडली मिलान देखें');

        // an unrelated member still cannot see them
        $other = $this->member();
        $this->bio($other, ['gender' => 'male']);
        BiodataAccess::flush();
        $this->actingAs($other)->get(route('biodata.show', $this->girl))->assertOk()->assertDontSee('Kanya')->assertDontSee('Hasta');
    }

    public function test_owner_and_admin_see_values_and_search_never_leaks(): void
    {
        $this->actingAs($this->girlU)->get(route('biodata.show', $this->girl))->assertSee('Kanya')->assertSee('Hasta');
        $this->actingAs($this->admin())->get(route('biodata.show', $this->girl))->assertSee('Kanya');

        $this->actingAs($this->boyU)->get(route('biodata.index'))->assertOk()->assertSee('Pooja')->assertDontSee('Kanya')->assertDontSee('Hasta');
    }

    public function test_lite_member_never_sees_kundli(): void
    {
        $lite = $this->member(); // no biodata
        $this->actingAs($lite)->get(route('biodata.show', $this->girl))->assertDontSee('Kanya')->assertDontSee('Hasta');
    }

    public function test_result_page_content_and_disclaimer(): void
    {
        $i = $this->interest('accepted');
        $res = $this->actingAs($this->boyU)->get(route('interests.kundli', $i))->assertOk();
        $res->assertSee('कुंडली मिलान')
            ->assertSee('100% सही नहीं है')
            ->assertSee('not 100% accurate')
            ->assertSee('मध्यम (Average)')
            ->assertSee('24')
            ->assertSee('/36', false)
            ->assertSee('नाड़ी ठीक')->assertDontSee('kundli-missing', false);
        $res->assertSee('भकूट दोष');
        $res->assertSee('मांगलिक: पंडित से पूछें');
    }

    public function test_missing_data_state(): void
    {
        $this->girl->update(['rashi' => null, 'nakshatra' => null, 'nakshatra_pada' => null]);
        $i = $this->interest('accepted');

        $this->actingAs($this->boyU)->get(route('interests.kundli', $i))->assertOk()
            ->assertSee('दोनों तरफ़ की राशि और नक्षत्र चाहिए')
            ->assertSee('Pooja Test')
            ->assertSee('उनसे कहिए')
            ->assertDontSee(route('biodata.edit', $this->girl), false)
            ->assertSee('100% सही नहीं है');

        $this->actingAs($this->girlU)->get(route('interests.kundli', $i))->assertOk()
            ->assertSee(route('biodata.edit', $this->girl), false);
    }

    public function test_accepted_interest_card_has_button(): void
    {
        $i = $this->interest('accepted');
        $this->actingAs($this->boyU)->get(route('interests.index', ['tab' => 'accepted']))->assertOk()->assertSee(route('interests.kundli', $i), false);
    }
}
