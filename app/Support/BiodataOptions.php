<?php

namespace App\Support;

/**
 * Single source of dropdown option lists for biodata (form + search filters).
 * All methods return key => label arrays. Extend freely.
 */
class BiodataOptions
{
    public static function genders(): array
    {
        return ['male' => __('लड़का (Male)'), 'female' => __('लड़की (Female)')];
    }

    public static function addedBy(): array
    {
        return [
            'self' => __('खुद (Self)'),
            'mother' => __('माता (Mother)'),
            'father' => __('पिता (Father)'),
            'sister' => __('बहन (Sister)'),
            'brother' => __('भाई (Brother)'),
            'cousin' => __('चचेरे-ममेरे भाई-बहन (Cousin)'),
            'relative' => __('रिश्तेदार (Relative)'),
            'other' => __('अन्य (Other)'),
        ];
    }

    /**
     * How the biodata's subject relates to the member who created it (Devanagari, for sentences).
     * e.g. added_by=cousin => "कज़िन"; added_by=mother + male profile => "बेटा".
     */
    public static function profileRelation(?string $addedBy, ?string $gender): string
    {
        $male = $gender === 'male';

        return match ($addedBy) {
            'mother', 'father' => $male ? __('बेटा') : __('बेटी'),
            'sister', 'brother' => $male ? __('भाई') : __('बहन'),
            'cousin' => __('कज़िन'),
            'relative' => __('रिश्तेदार'),
            'other' => __('परिचित'),
            default => '',
        };
    }

    public static function maritalStatuses(): array
    {
        return [
            'never_married' => __('अविवाहित (Never married)'),
            'divorced' => __('तलाकशुदा (Divorced)'),
            'widowed' => __('विधवा / विधुर (Widowed)'),
        ];
    }

    public static function complexions(): array
    {
        return [
            'very_fair' => __('बहुत गोरा (Very fair)'),
            'fair' => __('गोरा (Fair)'),
            'wheatish' => __('गेहुँआ (Wheatish)'),
            'dark' => __('सांवला (Dark)'),
        ];
    }

    public static function bloodGroups(): array
    {
        return array_combine($g = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $g);
    }

    public static function educationLevels(): array
    {
        return [
            'below_10' => __('10वीं से कम'),
            '12' => __('12वीं पास'),
            'diploma' => __('डिप्लोमा / ITI'),
            'graduate' => __('स्नातक (Graduate)'),
            'post_graduate' => __('स्नातकोत्तर (Post graduate)'),
            'professional' => __('प्रोफ़ेशनल (इंजीनियर, डॉक्टर, CA आदि)'),
        ];
    }

    public static function occupationTypes(): array
    {
        return [
            'govt' => __('सरकारी नौकरी'),
            'private' => __('प्राइवेट नौकरी'),
            'business' => __('व्यवसाय (Business)'),
            'farming' => __('खेती'),
            'student' => __('विद्यार्थी'),
            'not_working' => __('नौकरी नहीं करते'),
        ];
    }

    public static function incomeRanges(): array
    {
        return [
            'below_2' => __('2 लाख से कम (सालाना)'),
            '2_5' => __('2 से 5 लाख (सालाना)'),
            '5_10' => __('5 से 10 लाख (सालाना)'),
            '10_20' => __('10 से 20 लाख (सालाना)'),
            'above_20' => __('20 लाख से ज़्यादा (सालाना)'),
        ];
    }

    public static function familyTypes(): array
    {
        return ['joint' => __('संयुक्त परिवार'), 'nuclear' => __('एकल परिवार')];
    }

    public static function photoPrivacies(): array
    {
        return [
            'members' => __('सभी मंज़ूर सदस्य देख सकें'),
            'blur_until_accepted' => __('रिश्ता मंज़ूर होने तक धुंधली'),
            'accepted_only' => __('सिर्फ़ रिश्ता मंज़ूर होने के बाद'),
        ];
    }

    public static function states(): array
    {
        $s = ['Maharashtra', 'Madhya Pradesh', 'Gujarat', 'Rajasthan', 'Uttar Pradesh', 'Chhattisgarh', 'Karnataka', 'Telangana', 'Delhi', 'Goa'];
        return array_combine($s, array_map(fn ($x) => __($x), $s));
    }

    /** Maharashtra districts (starter list; extend as needed). */
    public static function districts(): array
    {
        $d = ['Ahmednagar', 'Akola', 'Amravati', 'Aurangabad', 'Beed', 'Bhandara', 'Buldhana', 'Chandrapur', 'Dhule', 'Gadchiroli', 'Gondia', 'Hingoli', 'Jalgaon', 'Jalna', 'Kolhapur', 'Latur', 'Mumbai', 'Nagpur', 'Nanded', 'Nandurbar', 'Nashik', 'Osmanabad', 'Palghar', 'Parbhani', 'Pune', 'Raigad', 'Ratnagiri', 'Sangli', 'Satara', 'Sindhudurg', 'Solapur', 'Thane', 'Wardha', 'Washim', 'Yavatmal'];
        return array_combine($d, $d);
    }

    /** district (stored English value) => "हिंदी (English)" label for dropdowns. Values stay English. */
    public static function districtLabels(): array
    {
        $out = [];
        foreach (static::districts() as $d) {
            $t = __($d);
            $out[$d] = $t === $d ? $d : $t.' ('.$d.')';
        }

        return $out;
    }

    /** Height in cm =>"5'4\" (163 cm)". */
    public static function heights(): array
    {
        $out = [];
        for ($cm = 122; $cm <= 213; $cm++) {
            $in = (int) round($cm / 2.54);
            $out[$cm] = intdiv($in, 12)."'".($in % 12).'" ('.$cm.' cm)';
        }
        return $out;
    }

    /** Canonical rashi (Moon sign) keys in zodiac order => "Hindi / English". */
    public static function rashis(): array
    {
        return [
            'mesha' => __('मेष / Mesha (Aries)'),
            'vrishabha' => __('वृषभ / Vrishabha (Taurus)'),
            'mithuna' => __('मिथुन / Mithuna (Gemini)'),
            'karka' => __('कर्क / Karka (Cancer)'),
            'simha' => __('सिंह / Simha (Leo)'),
            'kanya' => __('कन्या / Kanya (Virgo)'),
            'tula' => __('तुला / Tula (Libra)'),
            'vrischika' => __('वृश्चिक / Vrischika (Scorpio)'),
            'dhanu' => __('धनु / Dhanu (Sagittarius)'),
            'makara' => __('मकर / Makara (Capricorn)'),
            'kumbha' => __('कुंभ / Kumbha (Aquarius)'),
            'meena' => __('मीन / Meena (Pisces)'),
        ];
    }

    /** The 27 nakshatras in traditional order => "Hindi / English". */
    public static function nakshatras(): array
    {
        return [
            'ashwini' => __('अश्विनी / Ashwini'),
            'bharani' => __('भरणी / Bharani'),
            'krittika' => __('कृत्तिका / Krittika'),
            'rohini' => __('रोहिणी / Rohini'),
            'mrigashira' => __('मृगशिरा / Mrigashira'),
            'ardra' => __('आर्द्रा / Ardra'),
            'punarvasu' => __('पुनर्वसु / Punarvasu'),
            'pushya' => __('पुष्य / Pushya'),
            'ashlesha' => __('आश्लेषा / Ashlesha'),
            'magha' => __('मघा / Magha'),
            'purva_phalguni' => __('पूर्वा फाल्गुनी / Purva Phalguni'),
            'uttara_phalguni' => __('उत्तरा फाल्गुनी / Uttara Phalguni'),
            'hasta' => __('हस्त / Hasta'),
            'chitra' => __('चित्रा / Chitra'),
            'swati' => __('स्वाति / Swati'),
            'vishakha' => __('विशाखा / Vishakha'),
            'anuradha' => __('अनुराधा / Anuradha'),
            'jyeshtha' => __('ज्येष्ठा / Jyeshtha'),
            'mula' => __('मूल / Mula'),
            'purva_ashadha' => __('पूर्वाषाढ़ा / Purva Ashadha'),
            'uttara_ashadha' => __('उत्तराषाढ़ा / Uttara Ashadha'),
            'shravana' => __('श्रवण / Shravana'),
            'dhanishta' => __('धनिष्ठा / Dhanishta'),
            'shatabhisha' => __('शतभिषा / Shatabhisha'),
            'purva_bhadrapada' => __('पूर्वा भाद्रपद / Purva Bhadrapada'),
            'uttara_bhadrapada' => __('उत्तरा भाद्रपद / Uttara Bhadrapada'),
            'revati' => __('रेवती / Revati'),
        ];
    }

    /**
     * Moon sign from nakshatra + pada: each rashi spans 9 padas (2 1/4 nakshatras).
     * rashi index = intdiv(nakshatraIndex * 4 + (pada - 1), 9). Returns null for unknown input.
     */
    public static function rashiFromNakshatraPada(?string $nakshatra, int|string|null $pada): ?string
    {
        $n = array_search($nakshatra, array_keys(static::nakshatras()), true);
        $pada = (int) $pada;
        if ($n === false || $pada < 1 || $pada > 4) {
            return null;
        }

        return array_keys(static::rashis())[intdiv($n * 4 + $pada - 1, 9)];
    }

    /** Rashi keys a nakshatra can fall in (1 or 2, depending on the pada). */
    public static function rashisForNakshatra(?string $nakshatra): array
    {
        return collect([1, 2, 3, 4])
            ->map(fn ($p) => static::rashiFromNakshatraPada($nakshatra, $p))
            ->filter()->unique()->values()->all();
    }

    public static function label(string $list, ?string $key): string
    {
        return (string) (static::$list()[$key] ?? ($key ?? ''));
    }
}
