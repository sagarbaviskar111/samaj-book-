<?php

namespace Tests\Unit\Kundli;

use App\Services\Kundli\GunMilan;
use App\Services\Kundli\Tables;
use App\Support\BiodataOptions;
use PHPUnit\Framework\TestCase;

class GunMilanTest extends TestCase
{
    private function run2(string $br, string $bn, string $gr, string $gn, ?bool $bm = null, ?bool $gm = null): array
    {
        return GunMilan::compute(
            ['rashi' => $br, 'nakshatra' => $bn, 'manglik' => $bm],
            ['rashi' => $gr, 'nakshatra' => $gn, 'manglik' => $gm],
        );
    }

    public function test_hand_computed_example_rohini_vrishabha_boy_hasta_kanya_girl(): void
    {
        // Boy: Rohini (idx 3) / Vrishabha(1). Girl: Hasta (idx 12) / Kanya(5).
        // Varna: both Vaishya (2>=2) = 1.   Vashya: Chatushpada x Manav = 1.
        // Tara: boy->girl count 10 (10%9=1 ok), girl->boy count 19 (19%9=1 ok) = 3.
        // Yoni: Serpent x Buffalo = 1.      Graha Maitri: Venus/Mercury friend+friend = 5.
        // Gana: Manushya boy x Deva girl = 5. Bhakoot: kanya is 5th from vrishabha (5/9) = 0.
        // Nadi: Rohini Antya vs Hasta Adi = 8.   Total 1+1+3+1+5+5+0+8 = 24 (Madhyam).
        $r = $this->run2('vrishabha', 'rohini', 'kanya', 'hasta');
        $o = array_map(fn ($k) => (int) $k['obtained'], $r['kootas']);
        $this->assertSame(['varna' => 1, 'vashya' => 1, 'tara' => 3, 'yoni' => 1, 'graha_maitri' => 5, 'gana' => 5, 'bhakoot' => 0, 'nadi' => 8], $o);
        $this->assertEquals(24, $r['total']);
        $this->assertSame('average', $r['band']['key']);
        $this->assertTrue($r['flags']['bhakoot_dosha']);
        $this->assertFalse($r['flags']['nadi_dosha']);
    }

    public function test_same_nakshatra_same_rashi_example(): void
    {
        // Both Ashwini / Mesha: Varna 1, Vashya 2, Tara 3 (count 1 both ways), Yoni 4, Graha Maitri 5,
        // Gana 6, Bhakoot 7 (same rashi), Nadi 0 (same nadi) = 28 (Achha).
        $r = $this->run2('mesha', 'ashwini', 'mesha', 'ashwini');
        $this->assertEquals(28, $r['total']);
        $this->assertSame('good', $r['band']['key']);
        $this->assertEquals(0, $r['kootas']['nadi']['obtained']);
        $this->assertEquals(7, $r['kootas']['bhakoot']['obtained']);
        $this->assertTrue($r['flags']['nadi_dosha']);
    }

    public function test_every_nakshatra_pair_stays_in_bounds_and_nadi_rule(): void
    {
        $maxes = array_map(fn ($k) => $k['max'], Tables::KOOTAS);
        $this->assertSame(36, array_sum($maxes));
        foreach (Tables::NAKSHATRAS as $bn) {
            foreach (Tables::NAKSHATRAS as $gn) {
                foreach ([['mesha', 'karka'], ['tula', 'meena'], ['simha', 'dhanu']] as [$br, $gr]) {
                    $r = $this->run2($br, $bn, $gr, $gn);
                    $this->assertGreaterThanOrEqual(0, $r['total']);
                    $this->assertLessThanOrEqual(36, $r['total']);
                    foreach ($r['kootas'] as $k) {
                        $this->assertGreaterThanOrEqual(0, $k['obtained']);
                        $this->assertLessThanOrEqual($k['max'], $k['obtained'], $k['name_en']);
                    }
                    $this->assertSame(Tables::nadi($bn) === Tables::nadi($gn), $r['kootas']['nadi']['obtained'] == 0);
                }
            }
        }
    }

    public function test_bhakoot_and_graha_maitri_symmetry_over_all_rashi_pairs(): void
    {
        $dosha = 0;
        foreach (Tables::RASHIS as $a) {
            foreach (Tables::RASHIS as $b) {
                $ab = $this->run2($a, 'ashwini', $b, 'bharani')['kootas'];
                $ba = $this->run2($b, 'ashwini', $a, 'bharani')['kootas'];
                $this->assertEquals($ab['bhakoot']['obtained'], $ba['bhakoot']['obtained'], "$a/$b bhakoot");
                $this->assertEquals($ab['graha_maitri']['obtained'], $ba['graha_maitri']['obtained'], "$a/$b maitri");
                $this->assertContains((int) $ab['bhakoot']['obtained'], [0, 7]);
                if ($a === $b) {
                    $this->assertEquals(7, $ab['bhakoot']['obtained']);
                    $this->assertEquals(5, $ab['graha_maitri']['obtained']);
                }
                $dosha += $ab['bhakoot']['obtained'] == 0 ? 1 : 0;
            }
        }
        // per rashi: positions 2,12,5,9,6,8 are dosha => 6 of 12 partners; 12*6 = 72 ordered pairs
        $this->assertSame(72, $dosha);
    }

    public function test_tables_are_consistent(): void
    {
        $n = count(Tables::YONI_ORDER);
        for ($i = 0; $i < $n; $i++) {
            $this->assertSame(4, Tables::YONI_MATRIX[$i][$i]);
            for ($j = 0; $j < $n; $j++) {
                $this->assertSame(Tables::YONI_MATRIX[$i][$j], Tables::YONI_MATRIX[$j][$i], "yoni $i,$j");
            }
        }
        $this->assertSame(Tables::NAKSHATRAS, array_keys(BiodataOptions::nakshatras()));
        $this->assertSame(Tables::RASHIS, array_keys(BiodataOptions::rashis()));
        $this->assertCount(27, Tables::YONI);
        $this->assertSame(['deva' => 9, 'manushya' => 9, 'rakshasa' => 9], array_count_values(Tables::GANA));
        $this->assertSame(['adi' => 9, 'madhya' => 9, 'antya' => 9], array_count_values(array_map([Tables::class, 'nadi'], Tables::NAKSHATRAS)));
    }

    public function test_rashi_from_nakshatra_pada(): void
    {
        $this->assertSame('mesha', BiodataOptions::rashiFromNakshatraPada('ashwini', 1));
        $this->assertSame('mesha', BiodataOptions::rashiFromNakshatraPada('krittika', 1));
        $this->assertSame('vrishabha', BiodataOptions::rashiFromNakshatraPada('krittika', 2));
        $this->assertSame('meena', BiodataOptions::rashiFromNakshatraPada('revati', 4));
        $this->assertNull(BiodataOptions::rashiFromNakshatraPada('ashwini', 5));
        $this->assertNull(BiodataOptions::rashiFromNakshatraPada('bogus', 1));
        $this->assertSame(['mesha', 'vrishabha'], BiodataOptions::rashisForNakshatra('krittika'));
    }

    public function test_graha_maitri_scores(): void
    {
        // sun(simha) vs moon(karka): friend+friend = 5
        $this->assertEquals(5, $this->run2('simha', 'ashwini', 'karka', 'bharani')['kootas']['graha_maitri']['obtained']);
        // mars(mesha) vs mercury(mithuna): mars->mercury enemy, mercury->mars neutral = 0.5
        $this->assertEquals(0.5, $this->run2('mesha', 'ashwini', 'mithuna', 'bharani')['kootas']['graha_maitri']['obtained']);
        // venus(tula) vs sun(simha): enemy+enemy = 0
        $this->assertEquals(0, $this->run2('tula', 'ashwini', 'simha', 'bharani')['kootas']['graha_maitri']['obtained']);
    }

    public function test_missing_data_returns_structure_without_exceptions(): void
    {
        $r = GunMilan::compute(['rashi' => 'mesha', 'nakshatra' => null], ['rashi' => null, 'nakshatra' => null]);
        $this->assertFalse($r['complete']);
        $this->assertSame(['nakshatra'], $r['missing']['boy']);
        $this->assertSame(['rashi', 'nakshatra'], $r['missing']['girl']);
        $this->assertFalse(GunMilan::compute([], [])['complete']);
        $this->assertFalse(GunMilan::compute(['rashi' => 'zzz', 'nakshatra' => 'yyy'], ['rashi' => 'mesha', 'nakshatra' => 'ashwini'])['complete']);
    }

    public function test_bands_and_manglik(): void
    {
        $this->assertSame('low', GunMilan::band(17.5)['key']);
        $this->assertSame('average', GunMilan::band(18)['key']);
        $this->assertSame('good', GunMilan::band(25)['key']);
        $this->assertSame('excellent', GunMilan::band(33)['key']);
        $this->assertSame('ok', GunMilan::manglik(true, true)['status']);
        $this->assertSame('ok', GunMilan::manglik(false, false)['status']);
        $this->assertSame('check', GunMilan::manglik(true, false)['status']);
        $this->assertSame('unknown', GunMilan::manglik(null, false)['status']);
    }
}
