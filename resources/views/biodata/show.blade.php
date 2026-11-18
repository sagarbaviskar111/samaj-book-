@extends('layouts.app')
@section('title', $biodata->name)
@section('wide', '1')
@section('content')
@use('App\Support\BiodataOptions', 'O')
@php
    $row = fn ($label, $val) => filled($val) ? '<div class="flex justify-between gap-4 py-2.5 text-sm"><dt class="shrink-0 text-ink-700">'.e($label).'</dt><dd class="text-right font-semibold text-ink-900">'.e($val).'</dd></div>' : '';
    $photos = $biodata->photos;
    $ordered = $photos->sortByDesc('is_primary')->values();
    $loc = collect([$biodata->current_city, $biodata->district, $biodata->state])->filter()->unique()->implode(', ');
    $h = $biodata->height_cm ? (O::heights()[$biodata->height_cm] ?? $biodata->height_cm.' cm') : null;
    $pct = $biodata->completeness();
    $initial = mb_strtoupper(mb_substr(trim($biodata->name), 0, 1));

    // Match % against the viewer's own approved biodata of the opposite gender.
    $match = null;
    if (! $isOwner) {
        $viewerBd = auth()->user()->biodatas()->approved()->where('gender', '!=', $biodata->gender)->latest('id')->first();
        $match = $viewerBd ? \App\Services\MatchScore::between($viewerBd, $biodata) : null;
    }
    $ringColor = $match === null ? '#d63a34' : ($match >= 70 ? '#2f7d57' : ($match >= 45 ? '#ee9e0a' : '#d63a34'));

    $ic = [
        'person' => 'M15.5 7.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM5 20a7 7 0 0 1 14 0',
        'work' => 'M4 8h16v11H4V8Zm5 0V5h6v3M4 13h16',
        'family' => 'M17 20v-1.500a3.500 3.500 0 0 0-3.500-3.500h-3A3.500 3.500 0 0 0 7 18.500V20M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 9v-1a3 3 0 0 0-2-2.800M4 20v-1a3 3 0 0 1 2-2.800',
        'pin' => 'M12 21s7-5.700 7-11a7 7 0 1 0-14 0c0 5.300 7 11 7 11Zm0-8.500a2.500 2.500 0 1 0 0-5 2.500 2.500 0 0 0 0 5Z',
        'about' => 'M8 10h8M8 14h5M5 4h14a1 1 0 0 1 1 1v14l-4-3H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z',
        'heart' => 'M12 21s-8-5.300-8-11a4.500 4.500 0 0 1 8-2.800A4.500 4.500 0 0 1 20 10c0 5.700-8 11-8 11Z',
    ];

    $sections = [];
    $sections[] = ['title' => 'व्यक्तिगत', 'icon' => 'person', 'tint' => 'from-neel-500 to-neel-400', 'rows' =>
        $row('जन्म तारीख़', $biodata->dob->format('d M Y'))
        .$row('वैवाहिक स्थिति', O::maritalStatuses()[$biodata->marital_status] ?? null)
        .$row('कद', $h)
        .$row('वज़न', $biodata->weight_kg ? $biodata->weight_kg.' kg' : null)
        .$row('रंग', O::complexions()[$biodata->complexion] ?? null)
        .$row('ब्लड ग्रुप', $biodata->blood_group)
        .$row('मांगलिक', is_null($biodata->manglik) ? null : ($biodata->manglik ? 'हाँ' : 'नहीं'))
        .$row('दिव्यांगता', $biodata->disability)
        .$row('जन्म का समय', $biodata->birth_time ? substr($biodata->birth_time, 0, 5) : null)
        .$row('जन्म स्थान', $biodata->birth_place)];
    $sections[] = ['title' => 'पढ़ाई और काम', 'icon' => 'work', 'tint' => 'from-gold-500 to-gold-300', 'rows' =>
        $row('पढ़ाई', trim(($biodata->education ?? '').' '.(isset(O::educationLevels()[$biodata->education_level]) ? '('.O::educationLevels()[$biodata->education_level].')' : '')))
        .$row('काम का प्रकार', O::occupationTypes()[$biodata->occupation_type] ?? null)
        .$row('काम / पद', $biodata->occupation)
        .$row('कंपनी / संस्था', $biodata->company)
        .($biodata->income_range ? ($canIncome ? $row('सालाना आमदनी', O::incomeRanges()[$biodata->income_range] ?? $biodata->income_range) : $row('सालाना आमदनी', 'छिपी है (रिश्ता मंज़ूर होने पर दिखेगी)')) : '')];
    $sections[] = ['title' => 'परिवार', 'icon' => 'family', 'tint' => 'from-rose-brand to-gold-400', 'rows' =>
        $row('पिता', trim($biodata->father_name.($biodata->father_occupation ? ' ('.$biodata->father_occupation.')' : '')))
        .$row('माता', trim($biodata->mother_name.($biodata->mother_occupation ? ' ('.$biodata->mother_occupation.')' : '')))
        .$row('भाई', $biodata->brothers.' ('.$biodata->brothers_married.' '.'विवाहित'.')')
        .$row('बहन', $biodata->sisters.' ('.$biodata->sisters_married.' '.'विवाहित'.')')
        .$row('परिवार का प्रकार', O::familyTypes()[$biodata->family_type] ?? null)
        .$row('मामा का गोत्र', $biodata->mama_gotra)
        .$row('मामा का गाँव', $biodata->mama_village)];
    $sections[] = ['title' => 'समाज और पता', 'icon' => 'pin', 'tint' => 'from-neel-700 to-neel-500', 'rows' =>
        $row('गोत्र', $biodata->gotra)
        .$row('उपजाति', $biodata->upjati)
        .$row('गाँव', $biodata->village)
        .$row('तहसील', $biodata->tehsil)
        .$row('ज़िला', $biodata->district)
        .$row('राज्य', $biodata->state)
        .$row('अभी का शहर', $biodata->current_city)
        .$row('इलाक़ा', $biodata->area)];

    $factors = [['उम्र', 35], ['पढ़ाई', 20], ['ज़िला', 20], ['गोत्र', 15], ['वैवाहिक स्थिति', 10]];
@endphp

<div class="lg:grid lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)] lg:items-start lg:gap-8">

    {{-- LEFT: gallery + (desktop) sticky actions --}}
    <div class="-mx-4 -mt-4 sm:mx-0 sm:mt-0 lg:sticky lg:top-20">
        <div class="relative overflow-hidden bg-ink-900 shadow-soft sm:rounded-3xl" oncontextmenu="return false">
            @if($ordered->isNotEmpty())
                <div class="bd-gallery flex snap-x snap-mandatory overflow-x-auto scroll-smooth" id="gallery">
                    @foreach($ordered as $p)
                        <img src="{{ route('biodata.photo', [$p, 'size' => 'full']) }}" alt="{{ $biodata->name }}" draggable="false"
                             class="aspect-[4/5] max-h-[75vh] w-full shrink-0 snap-center object-cover select-none" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    @endforeach
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-ink-950/70 to-transparent lg:hidden"></div>
                @if($ordered->count() > 1)
                    <span class="absolute right-3 top-3 rounded-full bg-black/50 px-2.5 py-1 text-sm font-semibold text-white backdrop-blur"><span id="galleryIdx">1</span>/{{ $ordered->count() }} &middot; {{ 'और फ़ोटो के लिए सरकाएँ' }} &rarr;</span>
                    <div class="absolute inset-x-0 bottom-3 flex justify-center gap-1.5 lg:hidden" id="galleryDots">
                        @foreach($ordered as $p)<span class="h-1.5 w-1.5 rounded-full bg-white/60 transition-all"></span>@endforeach
                    </div>
                @endif
            @else
                <div class="bd-paisley grid aspect-[4/5] max-h-[75vh] w-full place-items-center bg-gradient-to-br from-kumkum-800 to-kumkum-500">
                    <span class="font-display text-8xl font-bold text-white/90">{{ $initial }}</span>
                </div>
            @endif
            <div class="absolute left-3 top-3 flex max-w-[70%] flex-col items-start gap-1.5">
                @if($photoMode === 'blur')<span class="badge-warn !bg-gold-100">&#128274; {{ 'रिश्ता मंज़ूर होने तक फ़ोटो धुंधली है' }}</span>@endif
                @if($photoMode === 'placeholder')<span class="badge-warn !bg-gold-100">&#128274; {{ 'रिश्ता मंज़ूर होने के बाद फ़ोटो दिखेगी' }}</span>@endif
                @if($biodata->verified)<span class="badge-ok !bg-white/95 shadow">&#10003; {{ 'जाँचा हुआ' }}</span>@endif
            </div>
        </div>

        @if($ordered->count() > 1)
            <div class="mt-3 hidden gap-2 overflow-x-auto lg:flex" id="thumbs">
                @foreach($ordered as $p)
                    <button type="button" data-thumb="{{ $loop->index }}" aria-label="{{ 'फ़ोटो' }} {{ $loop->iteration }}"
                            class="h-16 w-14 shrink-0 overflow-hidden rounded-xl ring-2 ring-transparent transition hover:ring-ink-300 {{ $loop->first ? '!ring-kumkum-500' : '' }}">
                        <img src="{{ route('biodata.photo', [$p, 'size' => 'thumb']) }}" alt="" class="h-full w-full object-cover" loading="lazy" draggable="false">
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Desktop action card --}}
        <div class="mt-4 hidden space-y-3 lg:block" id="actions">
            @if($isOwner)
                <div class="card-pad space-y-2">
                    <a href="{{ route('biodata.edit', $biodata) }}" class="btn-primary w-full !min-h-11">{{ 'बदलाव करें' }}</a>
                    @include('biodata.partials.share-button', ['biodata' => $biodata, 'class' => 'btn-gold w-full !min-h-11'])
                    @if($canPdf)<a href="{{ route('biodata.pdf', $biodata) }}" class="btn-ghost w-full !min-h-11">&#128196; {{ 'PDF डाउनलोड' }}</a>@endif
                </div>
            @else
                @includeIf('biodata.partials.interest-actions', ['biodata' => $biodata])
                <div class="card-pad grid grid-cols-2 gap-2">
                    @includeIf('biodata.partials.shortlist-button', ['biodata' => $biodata, 'labelled' => true])
                    @if($canPdf)<a href="{{ route('biodata.pdf', $biodata) }}" class="em-act">&#128196; PDF डाउनलोड</a>@else<span></span>@endif
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: details --}}
    <div class="mt-5 min-w-0 lg:mt-0">
        <div class="card-pad bd-paisley relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="font-display text-3xl font-bold leading-tight text-ink-900">{{ $biodata->name }}</h1>
                    <p class="mt-1 text-sm text-ink-700">{{ $biodata->age }} {{ 'वर्ष' }}@if($h) &middot; {{ $h }}@endif @if($loc) &middot; {{ $loc }}@endif</p>
                    <p class="mt-1 text-sm text-ink-700">{{ 'बायोडाटा बनाने वाले' }} {{ O::addedBy()[$biodata->added_by] ?? $biodata->added_by }}</p>
                    @if($biodata->status !== 'approved')<span class="badge-warn mt-2">{{ ['pending' => 'जाँच बाकी', 'draft' => 'ड्राफ़्ट', 'rejected' => 'नामंज़ूर', 'paused' => 'रुका हुआ', 'married' => 'रिश्ता हो गया'][$biodata->status] ?? $biodata->status }} ({{ ($isOwner || $isAdmin) ? 'सिर्फ़ आप और एडमिन देख सकते हैं' : '' }})</span>@endif
                </div>
                @if($biodata->verified)
                    <div class="shrink-0 text-center" title="{{ 'जाँचा हुआ' }}">
                        <div class="grid h-14 w-14 place-items-center rounded-full bg-gradient-to-br from-tulsi-500 to-tulsi-600 text-white shadow-lg shadow-emerald-500/30 ring-4 ring-emerald-100">
                            <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.700 5.300a1 1 0 0 1 0 1.400l-7.500 7.500a1 1 0 0 1-1.400 0L3.300 9.700a1 1 0 1 1 1.400-1.400l3.800 3.800 6.800-6.800a1 1 0 0 1 1.400 0Z" clip-rule="evenodd"/></svg>
                        </div>
                        <p class="mt-1 text-sm font-bold text-emerald-700">{{ 'जाँचा हुआ' }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
                @if($biodata->education)<span class="badge-ink !px-3 !py-1.5 !text-sm">&#127891; {{ $biodata->education }}</span>@endif
                @if($biodata->occupation)<span class="badge-ink !px-3 !py-1.5 !text-sm">&#128188; {{ $biodata->occupation }}</span>@endif
                @if($biodata->gotra)<span class="badge-ink !px-3 !py-1.5 !text-sm">{{ 'गोत्र' }}: {{ $biodata->gotra }}</span>@endif
            </div>

            <div class="mt-4">
                <div class="flex justify-between text-sm font-semibold text-ink-700"><span>{{ 'बायोडाटा कितना भरा है' }}</span><span>{{ $pct }}%</span></div>
                <div class="mt-1 h-2 overflow-hidden rounded-full bg-ink-100"><div class="h-full rounded-full bg-gradient-to-r from-kumkum-500 to-gold-400" style="width: {{ $pct }}%"></div></div>
            </div>
        </div>

        @if($match !== null)
            <section class="card-pad mt-4">
                <div class="flex items-center gap-4">
                    <div class="relative grid h-20 w-20 shrink-0 place-items-center rounded-full bg-white shadow-soft">
                        <svg class="absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                            <circle cx="18" cy="18" r="15" fill="none" stroke="#f1e4d3" stroke-width="3"/>
                            <circle class="bd-ring-arc" cx="18" cy="18" r="15" fill="none" stroke="{{ $ringColor }}" stroke-width="3" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $match }} 100"/>
                        </svg>
                        <span class="relative text-lg font-bold text-ink-900">{{ $match }}%</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-ink-900">{{ 'आपके बायोडाटा से मेल' }}</h2>
                        <p class="text-sm text-ink-700">{{ 'उम्र, पढ़ाई, ज़िला, गोत्र और वैवाहिक स्थिति के आधार पर।' }}</p>
                    </div>
                </div>
                <ul class="mt-3 grid gap-x-6 gap-y-1.5 sm:grid-cols-2">
                    @foreach($factors as [$fl, $fw])
                        <li class="text-sm text-ink-700"><div class="flex justify-between"><span>{{ $fl }}</span><span class="text-ink-700">{{ $fw }}%</span></div>
                            <div class="mt-0.5 h-1.5 overflow-hidden rounded-full bg-ink-100"><div class="h-full rounded-full bg-gold-500" style="width: {{ $fw * 2.5 }}%"></div></div></li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Contact --}}
        @if($canContact)
            <div class="relative mt-4 overflow-hidden rounded-[var(--radius-card)] bg-gradient-to-br from-emerald-50 via-white to-gold-50 p-4 ring-1 ring-emerald-200">
                <div class="bd-confetti" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                <div class="relative text-sm font-bold uppercase tracking-wide text-emerald-700">&#127881; {{ 'संपर्क' }}</div>
                @if($biodata->contact_phone)
                    <div class="relative mt-1 flex flex-wrap items-center gap-3">
                        <a href="tel:{{ $biodata->contact_phone }}" class="text-xl font-bold text-emerald-900">&#128222; {{ $biodata->contact_phone }}</a>
                        <a href="https://wa.me/91{{ $biodata->contact_phone }}" target="_blank" rel="noopener" class="btn !min-h-12 bg-emerald-600 text-white hover:bg-emerald-700">WhatsApp</a>
                    </div>
                @else
                    <p class="relative text-sm text-emerald-800">{{ 'अभी नंबर नहीं जोड़ा गया है।' }}</p>
                @endif
            </div>
        @else
            <div class="bd-lock mt-4 flex items-center gap-4 rounded-[var(--radius-card)] p-4 ring-1 ring-ink-100">
                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white text-ink-700 shadow-soft">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-base font-bold text-ink-900">{{ 'संपर्क नंबर रिश्ता मंज़ूर होने के बाद दिखेगा।' }}</p>
                    <ol class="em-steps mt-2">
                        <li>"पसंद भेजें" दबाइए।</li>
                        <li>सामने वाला "मंज़ूर करें" दबाएगा।</li>
                        <li>नंबर और WhatsApp खुल जाएँगे।</li>
                    </ol>
                </div>
            </div>
        @endif

        {{-- Info cards --}}
        <div class="mt-4 grid gap-4 xl:grid-cols-2">
            @foreach($sections as $s)
                @if(trim($s['rows']) !== '')
                    <section class="card-pad bd-fade-up" style="--i: {{ $loop->index }}">
                        <h2 class="flex items-center gap-2.5 text-base font-bold text-ink-900">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br {{ $s['tint'] }} text-white shadow-sm"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic[$s['icon']] }}"/></svg></span>
                            {{ $s['title'] }}
                        </h2>
                        <dl class="mt-2 divide-y divide-ink-50">{!! $s['rows'] !!}</dl>
                    </section>
                @endif
            @endforeach
            @if(! empty($canKundli))
                @include('biodata.partials.kundli-card', ['biodata' => $biodata, 'kundliInterest' => $kundliInterest ?? null])
            @endif
        </div>

        <div class="mt-4 space-y-4">
            @if($biodata->about)
                <section class="card-pad">
                    <h2 class="flex items-center gap-2.5 text-base font-bold text-ink-900"><span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-kumkum-700 to-rose-brand text-white"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic['about'] }}"/></svg></span>{{ 'अपने बारे में' }}</h2>
                    <p class="mt-3 whitespace-pre-line text-base leading-relaxed text-ink-800">{{ $biodata->about }}</p>
                </section>
            @endif
            @if($biodata->expectation || $biodata->pref_age_min || $biodata->pref_district || $biodata->pref_education_level)
                <section class="card-pad">
                    <h2 class="flex items-center gap-2.5 text-base font-bold text-ink-900"><span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-rose-brand to-gold-400 text-white"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $ic['heart'] }}"/></svg></span>{{ 'जीवनसाथी से अपेक्षा' }}</h2>
                    @if($biodata->expectation)<p class="mt-3 whitespace-pre-line text-base leading-relaxed text-ink-800">{{ $biodata->expectation }}</p>@endif
                    <dl class="mt-2 divide-y divide-ink-50">
                        {!! $row('पसंदीदा उम्र', $biodata->pref_age_min ? $biodata->pref_age_min.' - '.($biodata->pref_age_max ?: '…') : null) !!}
                        {!! $row('पसंदीदा पढ़ाई', O::educationLevels()[$biodata->pref_education_level] ?? null) !!}
                        {!! $row('पसंदीदा ज़िला', $biodata->pref_district) !!}
                    </dl>
                </section>
            @endif
        </div>

        {{-- Phone: actions inline too (full form), bar below just scrolls here --}}
        <div class="mt-4 space-y-3 lg:hidden" id="actions-m">
            @if($isOwner)
                <div class="card-pad space-y-2"><a href="{{ route('biodata.edit', $biodata) }}" class="btn-primary w-full !min-h-12">{{ 'बदलाव करें' }}</a>@include('biodata.partials.share-button', ['biodata' => $biodata, 'class' => 'btn-gold w-full !min-h-12'])@if($canPdf)<a href="{{ route('biodata.pdf', $biodata) }}" class="btn-ghost w-full !min-h-12">&#128196; PDF डाउनलोड</a>@endif</div>
            @else
                @includeIf('biodata.partials.interest-actions', ['biodata' => $biodata, 'idSuffix' => '-m'])
                <div class="card-pad grid grid-cols-1 gap-2 min-[420px]:grid-cols-2">
                    @includeIf('biodata.partials.shortlist-button', ['biodata' => $biodata, 'labelled' => true])
                    @if($canPdf)<a href="{{ route('biodata.pdf', $biodata) }}" class="em-act">&#128196; PDF डाउनलोड</a>@endif
                </div>
            @endif
        </div>

        <div class="mt-4 rounded-2xl bg-ink-50/70 p-3 text-sm text-ink-700 ring-1 ring-ink-100">
            <p class="flex items-start gap-2"><span aria-hidden="true">&#128737;</span><span>{{ 'आपकी गोपनीयता सुरक्षित है' }}: {{ 'यह बायोडाटा किसी को न दिखाएँ और स्क्रीनशॉट न लें।' }} @if($canPdf){{ 'PDF पर आपका नाम छपेगा।' }}@endif</span></p>
        </div>

        @unless($isOwner)
            @if(Route::has('reports.store'))
                <details class="mt-4 text-center">
                    <summary class="em-act mx-auto !w-auto cursor-pointer !text-base">&#9873; {{ 'रिपोर्ट करें' }} <span class="text-sm font-normal text-ink-700">(कुछ गड़बड़ लगे तो)</span></summary>
                    <form method="POST" action="{{ route('reports.store') }}" class="mx-auto mt-2 max-w-sm space-y-2">
                        @csrf
                        <input type="hidden" name="target_type" value="biodata">
                        <input type="hidden" name="target_id" value="{{ $biodata->id }}">
                        <textarea name="reason" rows="2" maxlength="500" required class="input" placeholder="{{ 'क्या गड़बड़ है? यहाँ लिखिए' }}"></textarea>
                        <button class="btn-danger !min-h-12">{{ 'रिपोर्ट भेजें' }}</button>
                    </form>
                </details>
            @endif
        @endunless
        <p class="mt-4 text-center text-sm text-ink-700">{{ 'आप देख रहे हैं:' }} {{ auth()->user()->name }} &middot; {{ 'यह बायोडाटा किसी को न दिखाएँ और स्क्रीनशॉट न लें।' }}</p>
    </div>
</div>

{{-- Phone sticky action bar (sits above the bottom nav) --}}
<div class="fixed inset-x-0 bottom-[4.25rem] z-30 px-3 lg:hidden">
    <div class="glass mx-auto flex max-w-md items-center gap-2 rounded-2xl p-2 shadow-glow">
        @if($isOwner)
            <a href="{{ route('biodata.edit', $biodata) }}" class="btn-primary flex-1 !min-h-11">{{ 'बदलाव करें' }}</a>
        @else
            @includeIf('biodata.partials.shortlist-button', ['biodata' => $biodata])
            <a href="#actions-m" class="btn-gold flex-1 !min-h-12 !text-base">&#10084; पसंद भेजें</a>
        @endif
        @if($canPdf)<a href="{{ route('biodata.pdf', $biodata) }}" class="btn-ghost !min-h-12 !px-3 !text-base">&#128196; PDF</a>@endif
    </div>
</div>
<div class="h-16 lg:hidden" aria-hidden="true"></div>

@if($ordered->count() > 1)
@push('scripts')
<script>
(function () {
    var g = document.getElementById('gallery'); if (!g) return;
    var dots = [].slice.call(document.querySelectorAll('#galleryDots span')), idx = document.getElementById('galleryIdx');
    var thumbs = [].slice.call(document.querySelectorAll('[data-thumb]'));
    function mark(i) {
        dots.forEach(function (d, k) { d.className = 'h-1.5 rounded-full transition-all ' + (k === i ? 'w-4 bg-white' : 'w-1.5 bg-white/60'); });
        thumbs.forEach(function (t, k) { t.classList.toggle('!ring-kumkum-500', k === i); });
        if (idx) idx.textContent = i + 1;
    }
    g.addEventListener('scroll', function () { mark(Math.round(g.scrollLeft / g.clientWidth)); }, {passive: true});
    thumbs.forEach(function (t) { t.addEventListener('click', function () { g.scrollTo({left: g.clientWidth * parseInt(t.getAttribute('data-thumb'), 10), behavior: 'smooth'}); }); });
    mark(0);
})();
</script>
@endpush
@endif
@endsection
