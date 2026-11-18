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
        return ['male' => 'लड़का (Male)', 'female' => 'लड़की (Female)'];
    }

    public static function addedBy(): array
    {
        return [
            'self' => 'खुद (Self)',
            'mother' => 'माता (Mother)',
            'father' => 'पिता (Father)',
            'sister' => 'बहन (Sister)',
            'brother' => 'भाई (Brother)',
            'cousin' => 'चचेरे-ममेरे भाई-बहन (Cousin)',
            'relative' => 'रिश्तेदार (Relative)',
            'other' => 'अन्य (Other)',
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
            'mother', 'father' => $male ? 'बेटा' : 'बेटी',
            'sister', 'brother' => $male ? 'भाई' : 'बहन',
            'cousin' => 'कज़िन',
            'relative' => 'रिश्तेदार',
            'other' => 'परिचित',
            default => '',
        };
    }

    public static function maritalStatuses(): array
    {
        return [
            'never_married' => 'अविवाहित (Never married)',
            'divorced' => 'तलाकशुदा (Divorced)',
            'widowed' => 'विधवा / विधुर (Widowed)',
        ];
    }

    public static function complexions(): array
    {
        return [
            'very_fair' => 'बहुत गोरा (Very fair)',
            'fair' => 'गोरा (Fair)',
            'wheatish' => 'गेहुँआ (Wheatish)',
            'dark' => 'सांवला (Dark)',
        ];
    }

    public static function bloodGroups(): array
    {
        return array_combine($g = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $g);
    }

    public static function educationLevels(): array
    {
        return [
            'below_10' => '10वीं से कम',
            '12' => '12वीं पास',
            'diploma' => 'डिप्लोमा / ITI',
            'graduate' => 'स्नातक (Graduate)',
            'post_graduate' => 'स्नातकोत्तर (Post graduate)',
            'professional' => 'प्रोफ़ेशनल (इंजीनियर, डॉक्टर, CA आदि)',
        ];
    }

    public static function occupationTypes(): array
    {
        return [
            'govt' => 'सरकारी नौकरी',
            'private' => 'प्राइवेट नौकरी',
            'business' => 'व्यवसाय (Business)',
            'farming' => 'खेती',
            'student' => 'विद्यार्थी',
            'not_working' => 'नौकरी नहीं करते',
        ];
    }

    public static function incomeRanges(): array
    {
        return [
            'below_2' => '2 लाख से कम (सालाना)',
            '2_5' => '2 से 5 लाख (सालाना)',
            '5_10' => '5 से 10 लाख (सालाना)',
            '10_20' => '10 से 20 लाख (सालाना)',
            'above_20' => '20 लाख से ज़्यादा (सालाना)',
        ];
    }

    public static function familyTypes(): array
    {
        return ['joint' => 'संयुक्त परिवार', 'nuclear' => 'एकल परिवार'];
    }

    public static function photoPrivacies(): array
    {
        return [
            'members' => 'सभी मंज़ूर सदस्य देख सकें',
            'blur_until_accepted' => 'रिश्ता मंज़ूर होने तक धुंधली',
            'accepted_only' => 'सिर्फ़ रिश्ता मंज़ूर होने के बाद',
        ];
    }

    public static function states(): array
    {
        $s = ['Maharashtra', 'Madhya Pradesh', 'Gujarat', 'Rajasthan', 'Uttar Pradesh', 'Chhattisgarh', 'Karnataka', 'Telangana', 'Delhi', 'Goa'];
        return array_combine($s, $s);
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
        $hi = ['Ahmednagar' => 'अहमदनगर', 'Akola' => 'अकोला', 'Amravati' => 'अमरावती', 'Aurangabad' => 'औरंगाबाद', 'Beed' => 'बीड', 'Bhandara' => 'भंडारा', 'Buldhana' => 'बुलढाणा', 'Chandrapur' => 'चंद्रपुर', 'Dhule' => 'धुले', 'Gadchiroli' => 'गडचिरोली', 'Gondia' => 'गोंदिया', 'Hingoli' => 'हिंगोली', 'Jalgaon' => 'जलगाँव', 'Jalna' => 'जालना', 'Kolhapur' => 'कोल्हापुर', 'Latur' => 'लातूर', 'Mumbai' => 'मुंबई', 'Nagpur' => 'नागपुर', 'Nanded' => 'नांदेड़', 'Nandurbar' => 'नंदुरबार', 'Nashik' => 'नासिक', 'Osmanabad' => 'उस्मानाबाद', 'Palghar' => 'पालघर', 'Parbhani' => 'परभणी', 'Pune' => 'पुणे', 'Raigad' => 'रायगढ़', 'Ratnagiri' => 'रत्नागिरी', 'Sangli' => 'सांगली', 'Satara' => 'सातारा', 'Sindhudurg' => 'सिंधुदुर्ग', 'Solapur' => 'सोलापुर', 'Thane' => 'ठाणे', 'Wardha' => 'वर्धा', 'Washim' => 'वाशिम', 'Yavatmal' => 'यवतमाल'];
        $out = [];
        foreach (static::districts() as $d) {
            $out[$d] = isset($hi[$d]) ? $hi[$d].' ('.$d.')' : $d;
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
            'mesha' => 'मेष / Mesha (Aries)',
            'vrishabha' => 'वृषभ / Vrishabha (Taurus)',
            'mithuna' => 'मिथुन / Mithuna (Gemini)',
            'karka' => 'कर्क / Karka (Cancer)',
            'simha' => 'सिंह / Simha (Leo)',
            'kanya' => 'कन्या / Kanya (Virgo)',
            'tula' => 'तुला / Tula (Libra)',
            'vrischika' => 'वृश्चिक / Vrischika (Scorpio)',
            'dhanu' => 'धनु / Dhanu (Sagittarius)',
            'makara' => 'मकर / Makara (Capricorn)',
            'kumbha' => 'कुंभ / Kumbha (Aquarius)',
            'meena' => 'मीन / Meena (Pisces)',
        ];
    }

    /** The 27 nakshatras in traditional order => "Hindi / English". */
    public static function nakshatras(): array
    {
        return [
            'ashwini' => 'अश्विनी / Ashwini',
            'bharani' => 'भरणी / Bharani',
            'krittika' => 'कृत्तिका / Krittika',
            'rohini' => 'रोहिणी / Rohini',
            'mrigashira' => 'मृगशिरा / Mrigashira',
            'ardra' => 'आर्द्रा / Ardra',
            'punarvasu' => 'पुनर्वसु / Punarvasu',
            'pushya' => 'पुष्य / Pushya',
            'ashlesha' => 'आश्लेषा / Ashlesha',
            'magha' => 'मघा / Magha',
            'purva_phalguni' => 'पूर्वा फाल्गुनी / Purva Phalguni',
            'uttara_phalguni' => 'उत्तरा फाल्गुनी / Uttara Phalguni',
            'hasta' => 'हस्त / Hasta',
            'chitra' => 'चित्रा / Chitra',
            'swati' => 'स्वाति / Swati',
            'vishakha' => 'विशाखा / Vishakha',
            'anuradha' => 'अनुराधा / Anuradha',
            'jyeshtha' => 'ज्येष्ठा / Jyeshtha',
            'mula' => 'मूल / Mula',
            'purva_ashadha' => 'पूर्वाषाढ़ा / Purva Ashadha',
            'uttara_ashadha' => 'उत्तराषाढ़ा / Uttara Ashadha',
            'shravana' => 'श्रवण / Shravana',
            'dhanishta' => 'धनिष्ठा / Dhanishta',
            'shatabhisha' => 'शतभिषा / Shatabhisha',
            'purva_bhadrapada' => 'पूर्वा भाद्रपद / Purva Bhadrapada',
            'uttara_bhadrapada' => 'उत्तरा भाद्रपद / Uttara Bhadrapada',
            'revati' => 'रेवती / Revati',
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
