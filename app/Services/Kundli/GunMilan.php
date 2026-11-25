<?php

namespace App\Services\Kundli;

use App\Models\Biodata;
use App\Support\BiodataOptions;

/**
 * Ashtakoot (Gun Milan) compatibility, 36 points. Pure tables + arithmetic: no AI, no API, no cost.
 * This is an algorithm-based estimate only; never a substitute for a pandit.
 */
class GunMilan
{
    public const MAX = 36;

    /** Rashi + nakshatra of a biodata (deriving rashi from nakshatra+pada when needed). */
    public static function profileOf(Biodata $b): array
    {
        $rashi = $b->rashi;
        if (! $rashi && $b->nakshatra && $b->nakshatra_pada) {
            $rashi = BiodataOptions::rashiFromNakshatraPada($b->nakshatra, $b->nakshatra_pada);
        }

        return ['rashi' => $rashi ?: null, 'nakshatra' => $b->nakshatra ?: null, 'pada' => $b->nakshatra_pada, 'manglik' => $b->manglik];
    }

    /** Boy = male side, girl = female side. If genders do not decide it, $a is treated as the boy. */
    public static function forBiodatas(Biodata $a, Biodata $b): array
    {
        [$boy, $girl] = ($a->gender === 'female' && $b->gender === 'male') ? [$b, $a] : [$a, $b];

        return static::compute(static::profileOf($boy), static::profileOf($girl));
    }

    /**
     * @param array{rashi?:?string,nakshatra?:?string,manglik?:?bool} $boy
     * @param array{rashi?:?string,nakshatra?:?string,manglik?:?bool} $girl
     */
    public static function compute(array $boy, array $girl): array
    {
        $valid = fn ($v, array $list) => is_string($v) && in_array($v, $list, true) ? $v : null;
        $b = ['rashi' => $valid($boy['rashi'] ?? null, Tables::RASHIS), 'nakshatra' => $valid($boy['nakshatra'] ?? null, Tables::NAKSHATRAS)];
        $g = ['rashi' => $valid($girl['rashi'] ?? null, Tables::RASHIS), 'nakshatra' => $valid($girl['nakshatra'] ?? null, Tables::NAKSHATRAS)];

        $missing = ['boy' => [], 'girl' => []];
        foreach (['rashi', 'nakshatra'] as $k) {
            if (! $b[$k]) {
                $missing['boy'][] = $k;
            }
            if (! $g[$k]) {
                $missing['girl'][] = $k;
            }
        }
        if ($missing['boy'] || $missing['girl']) {
            return ['complete' => false, 'missing' => $missing];
        }

        $br = array_search($b['rashi'], Tables::RASHIS, true);
        $gr = array_search($g['rashi'], Tables::RASHIS, true);
        $bn = array_search($b['nakshatra'], Tables::NAKSHATRAS, true);
        $gn = array_search($g['nakshatra'], Tables::NAKSHATRAS, true);

        $kootas = [
            'varna' => static::varna($b['rashi'], $g['rashi']),
            'vashya' => static::vashya($b['rashi'], $g['rashi']),
            'tara' => static::tara($bn, $gn),
            'yoni' => static::yoni($b['nakshatra'], $g['nakshatra']),
            'graha_maitri' => static::grahaMaitri($b['rashi'], $g['rashi']),
            'gana' => static::gana($b['nakshatra'], $g['nakshatra']),
            'bhakoot' => static::bhakoot($br, $gr),
            'nadi' => static::nadi($b['nakshatra'], $g['nakshatra']),
        ];

        $rows = [];
        $total = 0.0;
        foreach ($kootas as $key => $r) {
            $meta = Tables::KOOTAS[$key];
            $rows[$key] = ['key' => $key, 'name_hi' => $meta['hi'], 'name_en' => $meta['en'], 'max' => $meta['max']] + $r;
            $total += $r['obtained'];
        }
        $total = round($total, 1);

        $bm = $boy['manglik'] ?? null;
        $gm = $girl['manglik'] ?? null;

        return [
            'complete' => true,
            'missing' => ['boy' => [], 'girl' => []],
            'kootas' => $rows,
            'total' => $total,
            'max' => self::MAX,
            'percentage' => round($total / self::MAX * 100, 1),
            'band' => static::band($total),
            'flags' => [
                'nadi_dosha' => $rows['nadi']['obtained'] == 0,
                'bhakoot_dosha' => $rows['bhakoot']['obtained'] == 0,
                'gana_dosha' => $rows['gana']['obtained'] <= 1,
                'manglik' => static::manglik($bm, $gm),
            ],
        ];
    }

    public static function band(float $total): array
    {
        return match (true) {
            $total < 18 => ['key' => 'low', 'hi' => 'कम', 'en' => 'Not recommended', 'label' => 'Kam / Not recommended', 'color' => 'rose'],
            $total < 25 => ['key' => 'average', 'hi' => 'मध्यम', 'en' => 'Average', 'label' => 'Madhyam / Average', 'color' => 'amber'],
            $total < 33 => ['key' => 'good', 'hi' => 'अच्छा', 'en' => 'Good', 'label' => 'Achha / Good', 'color' => 'emerald'],
            default => ['key' => 'excellent', 'hi' => 'उत्तम', 'en' => 'Excellent', 'label' => 'Uttam / Excellent', 'color' => 'emerald'],
        };
    }

    /** @return array{status:string,boy:?bool,girl:?bool,note:string} */
    public static function manglik(?bool $boy, ?bool $girl): array
    {
        if ($boy === null || $girl === null) {
            $status = 'unknown';
            $note = __('Manglik ki jaankari dono taraf nahi hai. Pandit se poochhein.');
        } elseif ($boy === $girl) {
            $status = 'ok';
            $note = $boy ? __('Dono manglik hain: aam taur par santulan maana jata hai.') : __('Dono non-manglik hain: koi manglik dosh nahi.');
        } else {
            $status = 'check';
            $note = __('Ek taraf manglik hai: pandit se zaroor jaanch karayein.');
        }

        return ['status' => $status, 'boy' => $boy, 'girl' => $girl, 'note' => $note];
    }

    // ---- individual kootas: each returns obtained, boy_value, girl_value, note ----

    protected static function varna(string $br, string $gr): array
    {
        $bv = Tables::VARNA[$br];
        $gv = Tables::VARNA[$gr];
        $ok = $bv >= $gv;

        return [
            'obtained' => $ok ? 1 : 0,
            'boy_value' => Tables::VARNA_NAMES[$bv], 'girl_value' => Tables::VARNA_NAMES[$gv],
            'note' => $ok ? __('Mansik star aur ahankar ka taalmel achha hai.') : __('Varna me antar hai: swabhav me thoda farq ho sakta hai.'),
        ];
    }

    protected static function vashya(string $br, string $gr): array
    {
        $bv = Tables::VASHYA[$br];
        $gv = Tables::VASHYA[$gr];
        $p = Tables::VASHYA_MATRIX[$bv][$gv];

        return [
            'obtained' => $p, 'boy_value' => ucfirst($bv), 'girl_value' => ucfirst($gv),
            'note' => $p >= 2 ? __('Ek dusre par prabhav aur aapsi samajh bahut achhi.') : ($p > 0 ? __('Aapsi samajh aur prabhav madhyam.') : __('Ek dusre ko samajhne me mehnat lagegi.')),
        ];
    }

    /** Tara: count boy->girl and girl->boy, remainder mod 9; 3 (Vipat), 5 (Pratyak), 7 (Naidhana) are unfavourable. */
    protected static function tara(int $bn, int $gn): array
    {
        $fav = fn (int $from, int $to) => ! in_array((((($to - $from) % 27) + 27) % 27 + 1) % 9, [3, 5, 7], true);
        $a = $fav($bn, $gn);
        $b = $fav($gn, $bn);
        $p = ($a ? 1.5 : 0) + ($b ? 1.5 : 0);

        return [
            'obtained' => $p,
            'boy_value' => $a ? 'Shubh' : 'Ashubh', 'girl_value' => $b ? 'Shubh' : 'Ashubh',
            'note' => $p == 3 ? __('Dono taraf se tara shubh: sehat aur bhagya me sahayak.') : ($p > 0 ? __('Ek taraf se tara shubh hai.') : __('Dono taraf tara ashubh hai.')),
        ];
    }

    protected static function yoni(string $bn, string $gn): array
    {
        $ba = Tables::YONI[$bn];
        $ga = Tables::YONI[$gn];
        $p = Tables::YONI_MATRIX[array_search($ba, Tables::YONI_ORDER, true)][array_search($ga, Tables::YONI_ORDER, true)];

        return [
            'obtained' => $p, 'boy_value' => ucfirst($ba), 'girl_value' => ucfirst($ga),
            'note' => $p >= 4 ? __('Ek hi yoni: shaaririk aur bhavnatmak taalmel bahut achha.') : ($p >= 3 ? __('Mitra yoni: achha taalmel.') : ($p >= 2 ? __('Samaanya taalmel.') : ($p >= 1 ? __('Taalmel kam hai.') : __('Shatru yoni: swabhav me takraav ki sambhavna.')))),
        ];
    }

    protected static function grahaMaitri(string $br, string $gr): array
    {
        $bl = Tables::LORD[$br];
        $gl = Tables::LORD[$gr];
        if ($bl === $gl) {
            $p = 5;
        } else {
            $rel = [Tables::relation($bl, $gl), Tables::relation($gl, $bl)];
            sort($rel); // enemy, friend, neutral order is irrelevant; the pair set decides
            $p = match (implode('+', $rel)) {
                'friend+friend' => 5,
                'friend+neutral' => 4,
                'neutral+neutral' => 3,
                'enemy+friend' => 1,
                'enemy+neutral' => 0.5,
                default => 0, // enemy+enemy
            };
        }

        return [
            'obtained' => $p, 'boy_value' => ucfirst($bl), 'girl_value' => ucfirst($gl),
            'note' => $p >= 4 ? __('Rashi swamiyon me mitrata: vichar aur sochne ka tareeka milta hai.') : ($p >= 3 ? __('Rashi swami sam: madhyam taalmel.') : __('Rashi swamiyon me matbhed ki sambhavna.')),
        ];
    }

    protected static function gana(string $bn, string $gn): array
    {
        $bv = Tables::GANA[$bn];
        $gv = Tables::GANA[$gn];
        $p = Tables::GANA_MATRIX[$bv][$gv];

        return [
            'obtained' => $p, 'boy_value' => ucfirst($bv), 'girl_value' => ucfirst($gv),
            'note' => $p >= 5 ? __('Swabhav (temperament) ka achha taalmel.') : ($p >= 1 ? __('Swabhav me thoda antar hai.') : __('Swabhav me bada antar: pandit se salah lein.')),
        ];
    }

    /** Bhakoot: girl's rashi counted from boy's; 2/12, 5/9, 6/8 positions give 0, else 7. */
    protected static function bhakoot(int $br, int $gr): array
    {
        $d = ((($gr - $br) % 12) + 12) % 12 + 1;
        $bad = in_array($d, [2, 12, 5, 9, 6, 8], true);
        $rev = ((($br - $gr) % 12) + 12) % 12 + 1;

        return [
            'obtained' => $bad ? 0 : 7, 'boy_value' => $d.'/'.$rev, 'girl_value' => $rev.'/'.$d,
            'note' => $bad ? __('Bhakoot dosh: :d/:r ka sambandh: arthik ya parivarik chunautiyan ho sakti hain.', ['d' => $d, 'r' => $rev]) : __('Bhakoot anukool: parivar aur samriddhi ke liye achha.'),
        ];
    }

    protected static function nadi(string $bn, string $gn): array
    {
        $bv = Tables::nadi($bn);
        $gv = Tables::nadi($gn);
        $same = $bv === $gv;

        return [
            'obtained' => $same ? 0 : 8, 'boy_value' => ucfirst($bv), 'girl_value' => ucfirst($gv),
            'note' => $same ? __('Nadi dosh: dono ki nadi ek hai: pandit se parihar poochhein.') : __('Nadi alag hai: swasthya aur santaan ke liye anukool.'),
        ];
    }
}
