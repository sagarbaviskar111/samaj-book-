<?php

namespace App\Services\Kundli;

/**
 * Traditional Ashtakoot (Gun Milan) lookup tables.
 *
 * Source: the classical North-Indian Ashtakoot scheme as published in standard Vedic astrology
 * references (Muhurta Chintamani / BPHS-derived tables as reproduced by common panchang software).
 * Rashi and nakshatra keys are the canonical keys of App\Support\BiodataOptions.
 *
 * ASSUMPTIONS (no birth degrees are collected, so rashi-level simplifications apply):
 *  - Vashya: Dhanu is treated as Manav (first half) and Makara as Chatushpada (first half).
 *  - Gana matrix uses the variant Deva-Manushya 6/5, Deva-Rakshasa 1, Manushya-Rakshasa 0, Rakshasa-Deva/Manushya 0.
 */
final class Tables
{
    public const RASHIS = ['mesha', 'vrishabha', 'mithuna', 'karka', 'simha', 'kanya', 'tula', 'vrischika', 'dhanu', 'makara', 'kumbha', 'meena'];

    public const NAKSHATRAS = [
        'ashwini', 'bharani', 'krittika', 'rohini', 'mrigashira', 'ardra', 'punarvasu', 'pushya', 'ashlesha',
        'magha', 'purva_phalguni', 'uttara_phalguni', 'hasta', 'chitra', 'swati', 'vishakha', 'anuradha',
        'jyeshtha', 'mula', 'purva_ashadha', 'uttara_ashadha', 'shravana', 'dhanishta', 'shatabhisha',
        'purva_bhadrapada', 'uttara_bhadrapada', 'revati',
    ];

    /** Koota name + max points, in classical order. */
    public const KOOTAS = [
        'varna' => ['hi' => 'वर्ण', 'en' => 'Varna', 'max' => 1],
        'vashya' => ['hi' => 'वश्य', 'en' => 'Vashya', 'max' => 2],
        'tara' => ['hi' => 'तारा', 'en' => 'Tara', 'max' => 3],
        'yoni' => ['hi' => 'योनि', 'en' => 'Yoni', 'max' => 4],
        'graha_maitri' => ['hi' => 'ग्रह मैत्री', 'en' => 'Graha Maitri', 'max' => 5],
        'gana' => ['hi' => 'गण', 'en' => 'Gana', 'max' => 6],
        'bhakoot' => ['hi' => 'भकूट', 'en' => 'Bhakoot', 'max' => 7],
        'nadi' => ['hi' => 'नाड़ी', 'en' => 'Nadi', 'max' => 8],
    ];

    /** Varna by rashi: 4 Brahmin, 3 Kshatriya, 2 Vaishya, 1 Shudra. */
    public const VARNA = [
        'karka' => 4, 'vrischika' => 4, 'meena' => 4,
        'mesha' => 3, 'simha' => 3, 'dhanu' => 3,
        'vrishabha' => 2, 'kanya' => 2, 'makara' => 2,
        'mithuna' => 1, 'tula' => 1, 'kumbha' => 1,
    ];

    public const VARNA_NAMES = [4 => 'Brahmin', 3 => 'Kshatriya', 2 => 'Vaishya', 1 => 'Shudra'];

    /** Vashya group by rashi. */
    public const VASHYA = [
        'mesha' => 'chatushpada', 'vrishabha' => 'chatushpada', 'makara' => 'chatushpada',
        'mithuna' => 'manav', 'kanya' => 'manav', 'tula' => 'manav', 'dhanu' => 'manav', // Dhanu treated as Manav (no degree input)
        'karka' => 'jalchar', 'meena' => 'jalchar',
        // Kumbha (the water-bearer) is a human sign, so it belongs to Manav in the standard Vashya table.
        'kumbha' => 'manav',
        'simha' => 'vanchar',
        'vrischika' => 'keet',
    ];

    /** Vashya points [boy group][girl group]. */
    public const VASHYA_MATRIX = [
        'chatushpada' => ['chatushpada' => 2, 'manav' => 1, 'jalchar' => 1, 'vanchar' => 0.5, 'keet' => 1],
        'manav' => ['chatushpada' => 1, 'manav' => 2, 'jalchar' => 0.5, 'vanchar' => 0, 'keet' => 1],
        'jalchar' => ['chatushpada' => 1, 'manav' => 0.5, 'jalchar' => 2, 'vanchar' => 1, 'keet' => 1],
        'vanchar' => ['chatushpada' => 0, 'manav' => 0, 'jalchar' => 0, 'vanchar' => 2, 'keet' => 0],
        'keet' => ['chatushpada' => 1, 'manav' => 1, 'jalchar' => 1, 'vanchar' => 0, 'keet' => 2],
    ];

    /** Yoni animal by nakshatra. */
    public const YONI = [
        'ashwini' => 'horse', 'bharani' => 'elephant', 'krittika' => 'sheep', 'rohini' => 'serpent',
        'mrigashira' => 'serpent', 'ardra' => 'dog', 'punarvasu' => 'cat', 'pushya' => 'sheep',
        'ashlesha' => 'cat', 'magha' => 'rat', 'purva_phalguni' => 'rat', 'uttara_phalguni' => 'cow',
        'hasta' => 'buffalo', 'chitra' => 'tiger', 'swati' => 'buffalo', 'vishakha' => 'tiger',
        'anuradha' => 'deer', 'jyeshtha' => 'deer', 'mula' => 'dog', 'purva_ashadha' => 'monkey',
        'uttara_ashadha' => 'mongoose', 'shravana' => 'monkey', 'dhanishta' => 'lion', 'shatabhisha' => 'horse',
        'purva_bhadrapada' => 'lion', 'uttara_bhadrapada' => 'cow', 'revati' => 'elephant',
    ];

    public const YONI_ORDER = ['horse', 'elephant', 'sheep', 'serpent', 'dog', 'cat', 'rat', 'cow', 'buffalo', 'tiger', 'deer', 'monkey', 'mongoose', 'lion'];

    /** 14x14 yoni matrix, rows/cols follow YONI_ORDER (symmetric, diagonal 4). */
    public const YONI_MATRIX = [
        [4, 2, 2, 3, 2, 2, 2, 1, 0, 1, 3, 3, 2, 1],
        [2, 4, 3, 3, 2, 2, 2, 2, 3, 1, 2, 3, 2, 0],
        [2, 3, 4, 2, 1, 2, 1, 3, 3, 1, 2, 0, 3, 1],
        [3, 3, 2, 4, 2, 1, 1, 1, 1, 2, 2, 2, 0, 2],
        [2, 2, 1, 2, 4, 2, 1, 2, 2, 1, 0, 2, 1, 1],
        [2, 2, 2, 1, 2, 4, 0, 2, 2, 1, 3, 3, 2, 1],
        [2, 2, 1, 1, 1, 0, 4, 2, 2, 2, 2, 2, 1, 2],
        [1, 2, 3, 1, 2, 2, 2, 4, 3, 0, 3, 2, 2, 1],
        [0, 3, 3, 1, 2, 2, 2, 3, 4, 1, 2, 2, 2, 1],
        [1, 1, 1, 2, 1, 1, 2, 0, 1, 4, 1, 1, 2, 1],
        [3, 2, 2, 2, 0, 3, 2, 3, 2, 1, 4, 2, 2, 1],
        [3, 3, 0, 2, 2, 3, 2, 2, 2, 1, 2, 4, 3, 2],
        [2, 2, 3, 0, 1, 2, 1, 2, 2, 2, 2, 3, 4, 2],
        [1, 0, 1, 2, 1, 1, 2, 1, 1, 1, 1, 2, 2, 4],
    ];

    /** Rashi lord. */
    public const LORD = [
        'mesha' => 'mars', 'vrishabha' => 'venus', 'mithuna' => 'mercury', 'karka' => 'moon',
        'simha' => 'sun', 'kanya' => 'mercury', 'tula' => 'venus', 'vrischika' => 'mars',
        'dhanu' => 'jupiter', 'makara' => 'saturn', 'kumbha' => 'saturn', 'meena' => 'jupiter',
    ];

    /** Natural (naisargika) planetary friendship: planet => [friends, enemies]; the rest are neutral. */
    public const FRIENDSHIP = [
        'sun' => ['friends' => ['moon', 'mars', 'jupiter'], 'enemies' => ['venus', 'saturn']],
        'moon' => ['friends' => ['sun', 'mercury'], 'enemies' => []],
        'mars' => ['friends' => ['sun', 'moon', 'jupiter'], 'enemies' => ['mercury']],
        'mercury' => ['friends' => ['sun', 'venus'], 'enemies' => ['moon']],
        'jupiter' => ['friends' => ['sun', 'moon', 'mars'], 'enemies' => ['mercury', 'venus']],
        'venus' => ['friends' => ['mercury', 'saturn'], 'enemies' => ['sun', 'moon']],
        'saturn' => ['friends' => ['mercury', 'venus'], 'enemies' => ['sun', 'moon', 'mars']],
    ];

    /** Gana by nakshatra: D(eva), M(anushya), R(akshasa). */
    public const GANA = [
        'ashwini' => 'deva', 'bharani' => 'manushya', 'krittika' => 'rakshasa', 'rohini' => 'manushya',
        'mrigashira' => 'deva', 'ardra' => 'manushya', 'punarvasu' => 'deva', 'pushya' => 'deva',
        'ashlesha' => 'rakshasa', 'magha' => 'rakshasa', 'purva_phalguni' => 'manushya', 'uttara_phalguni' => 'manushya',
        'hasta' => 'deva', 'chitra' => 'rakshasa', 'swati' => 'deva', 'vishakha' => 'rakshasa',
        'anuradha' => 'deva', 'jyeshtha' => 'rakshasa', 'mula' => 'rakshasa', 'purva_ashadha' => 'manushya',
        'uttara_ashadha' => 'manushya', 'shravana' => 'deva', 'dhanishta' => 'rakshasa', 'shatabhisha' => 'rakshasa',
        'purva_bhadrapada' => 'manushya', 'uttara_bhadrapada' => 'manushya', 'revati' => 'deva',
    ];

    /** Gana points [boy][girl]. */
    public const GANA_MATRIX = [
        'deva' => ['deva' => 6, 'manushya' => 6, 'rakshasa' => 1],
        'manushya' => ['deva' => 5, 'manushya' => 6, 'rakshasa' => 0],
        'rakshasa' => ['deva' => 0, 'manushya' => 0, 'rakshasa' => 6],
    ];

    /** Nadi follows the traditional zig-zag over the 27 nakshatras: Adi, Madhya, Antya, Antya, Madhya, Adi, ... */
    public const NADI_PATTERN = ['adi', 'madhya', 'antya', 'antya', 'madhya', 'adi'];

    public static function nadi(string $nakshatra): string
    {
        return self::NADI_PATTERN[array_search($nakshatra, self::NAKSHATRAS, true) % 6];
    }

    /** Relation of planet $a towards planet $b: friend | neutral | enemy | same. */
    public static function relation(string $a, string $b): string
    {
        if ($a === $b) {
            return 'same';
        }
        $f = self::FRIENDSHIP[$a];

        return in_array($b, $f['friends'], true) ? 'friend' : (in_array($b, $f['enemies'], true) ? 'enemy' : 'neutral');
    }
}
