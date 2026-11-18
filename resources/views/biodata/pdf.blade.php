@use('App\Support\BiodataOptions', 'O')
@php
    $h = $b->height_cm ? (O::heights()[$b->height_cm] ?? $b->height_cm.' cm') : null;
    $loc = collect([$b->current_city, $b->district, $b->state])->filter()->unique()->implode(', ');
    $kundli = $showKundli ? collect([
        'Rashi' => $b->rashi, 'Nakshatra' => $b->nakshatra ? $b->nakshatra.($b->nakshatra_pada ? ' (pada '.$b->nakshatra_pada.')' : '') : null,
    ])->filter() : collect();

    $sections = [
        'Personal' => [
            'Date of birth' => $b->dob->format('d M Y'),
            'Marital status' => O::maritalStatuses()[$b->marital_status] ?? null,
            'Height' => $h, 'Weight' => $b->weight_kg ? $b->weight_kg.' kg' : null,
            'Complexion' => O::complexions()[$b->complexion] ?? null, 'Blood group' => $b->blood_group,
            'Manglik' => is_null($b->manglik) ? null : ($b->manglik ? 'Yes' : 'No'),
            'Disability' => $b->disability, 'Birth time' => $b->birth_time ? substr($b->birth_time, 0, 5) : null, 'Birth place' => $b->birth_place,
        ] + $kundli->all(),
        'Education & Work' => [
            'Education' => $b->education, 'Level' => O::educationLevels()[$b->education_level] ?? null,
            'Work type' => O::occupationTypes()[$b->occupation_type] ?? null, 'Occupation' => $b->occupation, 'Company' => $b->company,
            'Annual income' => ($showIncome || $isOwner) ? (O::incomeRanges()[$b->income_range] ?? null) : null,
        ],
        'Family' => [
            'Father' => trim($b->father_name.' '.($b->father_occupation ? '('.$b->father_occupation.')' : '')),
            'Mother' => trim($b->mother_name.' '.($b->mother_occupation ? '('.$b->mother_occupation.')' : '')),
            'Brothers' => $b->brothers.' ('.$b->brothers_married.' married)', 'Sisters' => $b->sisters.' ('.$b->sisters_married.' married)',
            'Family type' => O::familyTypes()[$b->family_type] ?? null, 'Maternal gotra' => $b->mama_gotra, 'Maternal village' => $b->mama_village,
        ],
        'Samaj & Location' => [
            'Gotra' => $b->gotra, 'Sub-caste' => $b->upjati, 'Village' => $b->village, 'Tehsil' => $b->tehsil,
            'District' => $b->district, 'State' => $b->state, 'Current city' => $b->current_city,
        ],
    ];
    if ($showContact && filled($b->contact_phone)) {
        $sections['Contact'] = ['Mobile / WhatsApp' => $b->contact_phone];
    }
    $pref = [
        'Preferred age' => $b->pref_age_min ? $b->pref_age_min.' - '.($b->pref_age_max ?: '...') : null,
        'Preferred education' => O::educationLevels()[$b->pref_education_level] ?? null,
        'Preferred district' => $b->pref_district,
    ];
@endphp
<!doctype html>
<html><head><meta charset="utf-8"><title>Biodata</title>
<style>
    @page { background-color: #fff9ef; }
    body { font-family: dejavusans; font-size: 9.5pt; color: #3b2a20; }
    table { border-collapse: collapse; }
    .band { background-color: #701a24; width: 100%; border-bottom: 1.2mm solid #ee9e0a; }
    .band td { color: #fff9ef; vertical-align: middle; }
    .cap { font-size: 8.5pt; color: #ffe08a; text-align: center; }
    .title { font-size: 17pt; color: #ffffff; text-align: center; font-weight: bold; }
    .name { font-size: 21pt; color: #701a24; font-weight: bold; }
    .chip { background-color: #fdeccd; color: #701a24; border: 0.3mm solid #ee9e0a; font-size: 8.5pt; padding: 1mm 3mm; }
    .bar { background-color: #96202a; color: #ffffff; font-size: 9pt; font-weight: bold; letter-spacing: 0.4mm; padding: 1.1mm 3mm; border-left: 1.6mm solid #ee9e0a; }
    td.l { color: #86624e; font-size: 8pt; width: 24%; padding: 1.2mm 1.5mm; border-bottom: 0.2mm solid #f1e4d3; vertical-align: top; }
    td.v { font-weight: bold; color: #2b1c16; width: 26%; padding: 1.2mm 1.5mm; border-bottom: 0.2mm solid #f1e4d3; vertical-align: top; }
    .txt { padding: 1.5mm 3mm; line-height: 1.5; }
</style></head>
<body>
<table class="band" cellpadding="0" cellspacing="0"><tr>
    <td style="width:24%;padding:3mm 3mm 3mm 4mm">@if($logoData)<img src="{{ $logoData }}" style="width:20mm">@endif</td>
    <td style="width:52%;padding:2mm 0;text-align:center">
        @if($ganeshData)<img src="{{ $ganeshData }}" style="width:14mm"><br>@endif
        <div class="cap">॥ श्री गणेशाय नमः ॥</div>
        <div class="title">बायोडाटा / BIODATA</div>
    </td>
    <td style="width:24%;padding:3mm 4mm 3mm 3mm;text-align:right">
        @if($photoData)<img src="{{ $photoData }}" style="width:27mm;border:0.8mm solid #ee9e0a">@endif
    </td>
</tr></table>

<table style="width:100%;margin-top:4mm" cellpadding="0" cellspacing="0"><tr>
    <td style="vertical-align:middle"><span class="name">{{ $b->name }}</span>
        <div style="font-size:8pt;color:#86624e;padding-top:1mm">SamajBook Biodata #{{ $b->id }}@if($b->verified) &middot; Verified @endif</div></td>
</tr></table>

<table style="width:100%;margin-top:2.5mm;margin-bottom:1mm" cellpadding="0" cellspacing="0"><tr>
    <td><span class="chip">{{ $b->age }} yrs</span></td>
    @if($h)<td style="padding-left:2mm"><span class="chip">{{ $h }}</span></td>@endif
    @if(O::maritalStatuses()[$b->marital_status] ?? null)<td style="padding-left:2mm"><span class="chip">{{ O::maritalStatuses()[$b->marital_status] }}</span></td>@endif
    @if($loc)<td style="padding-left:2mm"><span class="chip">{{ $loc }}</span></td>@endif
    <td style="width:30%"></td>
</tr></table>

@foreach($sections as $title => $data)
    @php($items = collect($data)->filter(fn ($v) => filled($v))->map(fn ($v, $k) => [$k, $v])->values())
    @if($items->isNotEmpty())
        <div class="bar" style="margin-top:3.5mm">{{ strtoupper($title) }}</div>
        <table style="width:100%" cellpadding="0" cellspacing="0">
            @foreach($items->chunk(2) as $pair)
                @php($pair = $pair->values())
                <tr>
                    <td class="l">{{ $pair[0][0] }}</td><td class="v">{{ $pair[0][1] }}</td>
                    @if(isset($pair[1]))<td class="l">{{ $pair[1][0] }}</td><td class="v">{{ $pair[1][1] }}</td>@else<td class="l"></td><td class="v"></td>@endif
                </tr>
            @endforeach
        </table>
    @endif
@endforeach

@if($b->about)
    <div class="bar" style="margin-top:3.5mm">ABOUT</div>
    <div class="txt">{!! nl2br(e($b->about)) !!}</div>
@endif
@if($b->expectation || collect($pref)->filter()->isNotEmpty())
    <div class="bar" style="margin-top:3.5mm">PARTNER EXPECTATION</div>
    @if($b->expectation)<div class="txt">{!! nl2br(e($b->expectation)) !!}</div>@endif
    @php($pi = collect($pref)->filter()->map(fn ($v, $k) => [$k, $v])->values())
    @if($pi->isNotEmpty())
        <table style="width:100%" cellpadding="0" cellspacing="0">
            @foreach($pi->chunk(2) as $pair)
                @php($pair = $pair->values())
                <tr>
                    <td class="l">{{ $pair[0][0] }}</td><td class="v">{{ $pair[0][1] }}</td>
                    @if(isset($pair[1]))<td class="l">{{ $pair[1][0] }}</td><td class="v">{{ $pair[1][1] }}</td>@else<td class="l"></td><td class="v"></td>@endif
                </tr>
            @endforeach
        </table>
    @endif
@endif
</body></html>
