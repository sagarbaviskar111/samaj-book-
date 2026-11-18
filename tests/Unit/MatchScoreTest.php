<?php

namespace Tests\Unit;

use App\Models\Biodata;
use App\Services\MatchScore;
use Tests\TestCase;

class MatchScoreTest extends TestCase
{
    private function b(array $a): Biodata
    {
        return (new Biodata)->forceFill($a + ['dob' => now()->subYears(25)->toDateString(), 'marital_status' => 'never_married']);
    }

    public function test_perfect_match_is_high(): void
    {
        $v = $this->b(['pref_age_min' => 22, 'pref_age_max' => 28, 'pref_education_level' => 'graduate', 'pref_district' => 'Pune', 'gotra' => 'Atri']);
        $c = $this->b(['education_level' => 'post_graduate', 'district' => 'Pune', 'gotra' => 'Kashyap']);
        $this->assertGreaterThanOrEqual(90, MatchScore::between($v, $c));
    }

    public function test_poor_match_is_low(): void
    {
        $v = $this->b(['pref_age_min' => 22, 'pref_age_max' => 24, 'pref_education_level' => 'post_graduate', 'pref_district' => 'Pune', 'gotra' => 'Atri']);
        $c = $this->b(['dob' => now()->subYears(38)->toDateString(), 'education_level' => 'below_10', 'district' => 'Akola', 'gotra' => 'Atri', 'marital_status' => 'divorced']);
        $this->assertLessThanOrEqual(10, MatchScore::between($v, $c));
    }

    public function test_neutral_defaults_in_range(): void
    {
        $s = MatchScore::between($this->b([]), $this->b([]));
        $this->assertGreaterThan(40, $s);
        $this->assertLessThanOrEqual(100, $s);
    }
}
