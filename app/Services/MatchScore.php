<?php

namespace App\Services;

use App\Models\Biodata;

/**
 * Match % between the viewer's biodata (with its expectations) and a candidate.
 * Weights: age 35, education 20, district 20, gotra 15, marital status 10.
 */
class MatchScore
{
    private const EDU_RANK = ['below_10' => 1, '12' => 2, 'diploma' => 3, 'graduate' => 4, 'post_graduate' => 5, 'professional' => 5];

    public static function between(Biodata $viewer, Biodata $candidate): int
    {
        $score = self::age($viewer, $candidate) * 35
            + self::education($viewer, $candidate) * 20
            + self::district($viewer, $candidate) * 20
            + self::gotra($viewer, $candidate) * 15
            + self::marital($viewer, $candidate) * 10;

        return max(0, min(100, (int) round($score)));
    }

    /** Each helper returns 0..1. No stated preference gives a neutral 0.7. */
    private static function age(Biodata $v, Biodata $c): float
    {
        if (! $v->pref_age_min && ! $v->pref_age_max) {
            return 0.7;
        }
        $age = $c->dob->age;
        $min = $v->pref_age_min ?: 0;
        $max = $v->pref_age_max ?: 200;
        if ($age >= $min && $age <= $max) {
            return 1.0;
        }
        $gap = $age < $min ? $min - $age : $age - $max;

        return match (true) {
            $gap <= 1 => 0.6,
            $gap <= 3 => 0.3,
            default => 0.0,
        };
    }

    private static function education(Biodata $v, Biodata $c): float
    {
        if (! $v->pref_education_level) {
            return 0.7;
        }
        $want = self::EDU_RANK[$v->pref_education_level] ?? null;
        $has = self::EDU_RANK[$c->education_level] ?? null;
        if ($want === null || $has === null) {
            return 0.4;
        }
        if ($has >= $want) {
            return 1.0;
        }

        return $want - $has === 1 ? 0.5 : 0.0;
    }

    private static function district(Biodata $v, Biodata $c): float
    {
        if (! $v->pref_district) {
            return 0.7;
        }
        if (! $c->district) {
            return 0.3;
        }

        return mb_strtolower(trim($v->pref_district)) === mb_strtolower(trim($c->district)) ? 1.0 : 0.0;
    }

    /** Same gotra is not allowed in marriage, so different gotra scores full. */
    private static function gotra(Biodata $v, Biodata $c): float
    {
        if (! $v->gotra || ! $c->gotra) {
            return 0.5;
        }

        return mb_strtolower(trim($v->gotra)) === mb_strtolower(trim($c->gotra)) ? 0.0 : 1.0;
    }

    private static function marital(Biodata $v, Biodata $c): float
    {
        if (! $v->marital_status || ! $c->marital_status) {
            return 0.5;
        }

        return $v->marital_status === $c->marital_status ? 1.0 : 0.3;
    }
}
