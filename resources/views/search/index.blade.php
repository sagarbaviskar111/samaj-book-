@extends('layouts.app')
@php $title = 'रिश्ते खोजें'; @endphp
@section('wide', '1')
@section('content')
@php
    $districts = \App\Support\BiodataOptions::districtLabels();
    $states = \App\Support\BiodataOptions::states();
    $sel = fn ($name, $val) => (string) ($f[$name] ?? '') === (string) $val ? 'selected' : '';
    $forQs = $for ? ['for' => $for, 'nd' => 1] : [];
    $total = $biodatas->total();
    $lite = $lite ?? false;
    $ageRanges = [[18, 22, '18 से 22'], [22, 26, '22 से 26'], [26, 30, '26 से 30'], [30, 35, '30 से 35'], [35, null, '35 से ऊपर']];
    $ageNow = ($f['age_min'] ?? null) || ($f['age_max'] ?? null);
    $ageLabel = $ageNow ? (($f['age_min'] ?? null) ?: '…').' - '.(($f['age_max'] ?? null) ?: '…') : null;
    $moreCount = collect(['height_min', 'height_max', 'income_range', 'marital_status', 'manglik', 'state', 'area', 'current_city', 'gotra', 'verified', 'with_photo', 'new_days'])
        ->filter(fn ($k) => ! empty($f[$k]))->count() + (($f['ex_gotra'] ?? true) ? 0 : 1);
@endphp

@include('access.banner')

@if(session('error'))<div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-base font-medium text-rose-700 ring-1 ring-rose-200" role="alert">{{ session('error') }}</div>@endif

{{-- Page head --}}
<div class="mb-3 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="section-title !text-2xl sm:!text-3xl">रिश्ते खोजें</h1>
        <p class="text-sm text-ink-700 sm:text-base">अपने समाज के जाँचे हुए बायोडाटा देखिए।</p>
    </div>
    @unless($lite)
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('shortlist.index') }}" class="btn-ghost btn-sm !min-h-11">
            <svg class="h-5 w-5 text-rose-brand" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-8-5.3-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 5.7-8 11-8 11Z"/></svg>सहेजे हुए</a>
        <a href="{{ route('interests.index') }}" class="btn-ghost btn-sm !min-h-11">रिश्ते की पसंद</a>
    </div>
    @endunless
</div>

{{-- Sticky top row: who are you looking for? --}}
<div class="sticky top-[57px] z-20 -mx-4 bg-cream/95 px-4 py-2 backdrop-blur lg:top-[72px] lg:mx-0 lg:rounded-2xl lg:px-0">
    <div class="em-switch max-w-2xl">
        <a href="{{ route('biodata.index', ['for' => 'bride']) }}" aria-current="{{ $for === 'bride' ? 'true' : 'false' }}">दुल्हन चाहिए (लड़की)</a>
        <a href="{{ route('biodata.index', ['for' => 'groom']) }}" aria-current="{{ $for === 'groom' ? 'true' : 'false' }}">दूल्हा चाहिए (लड़का)</a>
    </div>
</div>

@if(! $for)
    <div class="card-pad bd-paisley mx-auto mt-3 max-w-xl py-10 text-center">
        <div class="mx-auto mb-3 grid h-16 w-16 place-items-center rounded-full bg-gradient-to-br from-gold-300 to-rose-brand text-3xl shadow-glow" aria-hidden="true">&#128141;</div>
        <p class="font-display text-xl font-bold text-ink-900">आप किसके लिए रिश्ता ढूँढ रहे हैं?</p>
        <p class="mt-1 text-base text-ink-700">ऊपर से "दुल्हन चाहिए" या "दूल्हा चाहिए" चुनिए।</p>
        @unless($hasOwn)<p class="mt-3 text-sm text-ink-700">बेहतर मेल (%) देखने के लिए अपना बायोडाटा भी बनाइए।</p>@endunless
    </div>
@else

<div id="searchWrap" class="mt-2 {{ $lite ? '' : 'lg:items-start lg:gap-6' }}">
@unless($lite)

    {{-- "और फ़िल्टर": bottom sheet on phone (JS), side column on desktop, collapsed by default; inline form without JS --}}
    <aside class="lg:sticky lg:top-32 lg:max-h-[calc(100vh-9rem)] lg:overflow-y-auto">
        <form id="filterForm" method="GET" action="{{ route('biodata.index') }}">
            <input type="hidden" name="for" value="{{ $for }}">
            <div id="filterSheet" class="bd-filter-sheet">
                <div class="bd-filter-backdrop" id="filterBackdrop"></div>
                <div class="bd-filter-panel card overflow-hidden">
                    <div class="sticky top-0 z-10 flex items-center justify-between bg-white px-4 py-3 ring-1 ring-ink-100">
                        <h2 class="font-display text-xl font-bold text-ink-900">और फ़िल्टर</h2>
                        <button type="button" id="closeFilters" class="btn-ghost btn-sm !min-h-11" aria-label="फ़िल्टर बंद करें">&#10005; बंद करें</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4">
                        <p class="em-help col-span-2">जो जानकारी पता हो वही भरिए। बाक़ी खाली छोड़ दीजिए।</p>
                        <div><label class="field-label" for="m-age_min">उम्र कम से कम</label><input id="m-age_min" type="number" name="age_min" min="18" max="80" inputmode="numeric" value="{{ $f['age_min'] ?? '' }}" class="input"></div>
                        <div><label class="field-label" for="m-age_max">उम्र ज़्यादा से ज़्यादा</label><input id="m-age_max" type="number" name="age_max" min="18" max="80" inputmode="numeric" value="{{ $f['age_max'] ?? '' }}" class="input"></div>
                        <div><label class="field-label" for="m-height_min">कद कम से कम (cm)</label><input id="m-height_min" type="number" name="height_min" min="100" max="230" inputmode="numeric" value="{{ $f['height_min'] ?? '' }}" class="input"></div>
                        <div><label class="field-label" for="m-height_max">कद ज़्यादा से ज़्यादा (cm)</label><input id="m-height_max" type="number" name="height_max" min="100" max="230" inputmode="numeric" value="{{ $f['height_max'] ?? '' }}" class="input"></div>

                        <div class="col-span-2"><label class="field-label" for="m-income">सालाना आमदनी</label>
                            <select id="m-income" name="income_range" class="input"><option value="">कोई भी</option>@foreach($opts['income'] as $k => $l)<option value="{{ $k }}" {{ $sel('income_range', $k) }}>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-span-2"><label class="field-label" for="m-marital">वैवाहिक स्थिति</label>
                            <select id="m-marital" name="marital_status" class="input"><option value="">कोई भी</option>@foreach($opts['marital'] as $k => $l)<option value="{{ $k }}" {{ $sel('marital_status', $k) }}>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-span-2"><label class="field-label" for="m-manglik">मांगलिक</label>
                            <select id="m-manglik" name="manglik" class="input"><option value="">कोई भी</option><option value="1" {{ $sel('manglik', '1') }}>मांगलिक</option><option value="0" {{ $sel('manglik', '0') }}>मांगलिक नहीं</option></select></div>

                        <div class="col-span-2"><label class="field-label" for="m-state">राज्य</label>
                            <select id="m-state" name="state" class="input"><option value="">कोई भी</option>@foreach($states as $s)<option {{ $sel('state', $s) }}>{{ $s }}</option>@endforeach</select></div>
                        <div class="col-span-2"><label class="field-label" for="m-area">इलाक़ा या गाँव</label><input id="m-area" type="text" name="area" value="{{ $f['area'] ?? '' }}" class="input" placeholder="जैसे: कामठी"></div>
                        <div class="col-span-2"><label class="field-label" for="m-city">अभी किस शहर में रहते हैं</label><input id="m-city" type="text" name="current_city" value="{{ $f['current_city'] ?? '' }}" class="input"></div>
                        <div class="col-span-2"><label class="field-label" for="m-gotra">गोत्र</label><input id="m-gotra" type="text" name="gotra" value="{{ $f['gotra'] ?? '' }}" class="input"></div>

                        <div class="col-span-2 space-y-1 rounded-xl bg-ink-50 p-3 text-base text-ink-900">
                            <input type="hidden" name="ex_gotra" value="0">
                            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="ex_gotra" value="1" class="h-6 w-6 rounded accent-kumkum-600" {{ ($f['ex_gotra'] ?? true) ? 'checked' : '' }}> मेरा अपना गोत्र छोड़कर दिखाओ</label>
                            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="verified" value="1" class="h-6 w-6 rounded accent-kumkum-600" {{ !empty($f['verified']) ? 'checked' : '' }}> सिर्फ़ जाँचे हुए (वेरिफ़ाइड) बायोडाटा</label>
                            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="with_photo" value="1" class="h-6 w-6 rounded accent-kumkum-600" {{ !empty($f['with_photo']) ? 'checked' : '' }}> सिर्फ़ फ़ोटो वाले बायोडाटा</label>
                        </div>
                        <div class="col-span-2"><label class="field-label" for="m-new">नए बायोडाटा</label>
                            <select id="m-new" name="new_days" class="input"><option value="">सब</option><option value="7" {{ $sel('new_days', 7) }}>पिछले 7 दिन के</option><option value="30" {{ $sel('new_days', 30) }}>पिछले 30 दिन के</option></select></div>
                    </div>
                    <div class="sticky bottom-0 flex gap-2 bg-white p-4 ring-1 ring-ink-100">
                        <a href="{{ route('biodata.index', $forQs) }}" class="btn-ghost flex-1 !min-h-12">सब हटाएँ</a>
                        <button class="btn-primary flex-[2] !min-h-12">रिश्ते दिखाओ</button>
                    </div>
                </div>
            </div>
        </form>
    </aside>
@endunless

    <div class="min-w-0">
        @unless($lite)
        {{-- Simple search box (inputs belong to #filterForm via the form attribute) --}}
        <div class="mt-1 flex items-center gap-2">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                <input type="search" name="q" form="filterForm" value="{{ $f['q'] ?? '' }}" class="input !min-h-12 !rounded-full !pl-11" placeholder="नाम या गाँव लिखें" aria-label="नाम या गाँव लिखें">
            </div>
            <button type="submit" form="filterForm" class="btn-primary !min-h-12 !px-6">खोजें</button>
        </div>

        {{-- 4 quick filters: each opens one simple picker --}}
        <div class="relative mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4" id="quickFilters">
            <details class="em-qf {{ $ageNow ? 'em-qf-on' : '' }}">
                <summary><span aria-hidden="true">&#127874;</span> उम्र{{ $ageNow ? ': '.$ageLabel : '' }} &#9662;</summary>
                <div class="em-qf-panel">
                    <p class="mb-2 text-base font-bold text-ink-900">कितनी उम्र चाहिए?</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ageRanges as [$a, $z, $txt])
                            @php
                                $on = (int) ($f['age_min'] ?? 0) === $a && (int) ($f['age_max'] ?? 0) === (int) $z;
                                $q = array_merge(request()->query(), ['for' => $for, 'age_min' => $a, 'age_max' => $z, 'nd' => 1]);
                                if ($z === null) { unset($q['age_max']); }
                                unset($q['page']);
                            @endphp
                            <a href="{{ route('biodata.index', $q) }}" class="em-pill" aria-current="{{ $on ? 'true' : 'false' }}">{{ $txt }}</a>
                        @endforeach
                    </div>
                    <p class="em-help mt-3">दूसरी उम्र के लिए "और फ़िल्टर" में अपनी उम्र भरिए।</p>
                </div>
            </details>

            <details class="em-qf {{ !empty($f['district']) ? 'em-qf-on' : '' }}">
                <summary><span aria-hidden="true">&#128205;</span> ज़िला{{ !empty($f['district']) ? ': '.explode(' (', $districts[$f['district']] ?? $f['district'])[0] : '' }} &#9662;</summary>
                <div class="em-qf-panel">
                    <label class="mb-2 block text-base font-bold text-ink-900" for="q-district">कौन से ज़िले का रिश्ता?</label>
                    <select id="q-district" name="district" form="filterForm" class="input" data-autosubmit><option value="">सभी ज़िले</option>@foreach($districts as $k => $l)<option value="{{ $k }}" {{ $sel('district', $k) }}>{{ $l }}</option>@endforeach</select>
                    <button type="submit" form="filterForm" class="btn-primary mt-3 w-full !min-h-12">यह ज़िला लगाएँ</button>
                </div>
            </details>

            <details class="em-qf {{ !empty($f['education_level']) ? 'em-qf-on' : '' }}">
                <summary><span aria-hidden="true">&#127891;</span> पढ़ाई{{ !empty($f['education_level']) ? ': '.explode(' (', $opts['education'][$f['education_level']] ?? $f['education_level'])[0] : '' }} &#9662;</summary>
                <div class="em-qf-panel">
                    <label class="mb-2 block text-base font-bold text-ink-900" for="q-edu">कितनी पढ़ाई?</label>
                    <select id="q-edu" name="education_level" form="filterForm" class="input" data-autosubmit><option value="">कोई भी</option>@foreach($opts['education'] as $k => $l)<option value="{{ $k }}" {{ $sel('education_level', $k) }}>{{ $l }}</option>@endforeach</select>
                    <button type="submit" form="filterForm" class="btn-primary mt-3 w-full !min-h-12">यह पढ़ाई लगाएँ</button>
                </div>
            </details>

            <details class="em-qf {{ !empty($f['occupation_type']) ? 'em-qf-on' : '' }}">
                <summary><span aria-hidden="true">&#128188;</span> नौकरी{{ !empty($f['occupation_type']) ? ': '.explode(' (', $opts['occupation'][$f['occupation_type']] ?? $f['occupation_type'])[0] : '' }} &#9662;</summary>
                <div class="em-qf-panel">
                    <label class="mb-2 block text-base font-bold text-ink-900" for="q-occ">कैसा काम?</label>
                    <select id="q-occ" name="occupation_type" form="filterForm" class="input" data-autosubmit><option value="">कोई भी</option>@foreach($opts['occupation'] as $k => $l)<option value="{{ $k }}" {{ $sel('occupation_type', $k) }}>{{ $l }}</option>@endforeach</select>
                    <button type="submit" form="filterForm" class="btn-primary mt-3 w-full !min-h-12">यह काम लगाएँ</button>
                </div>
            </details>
        </div>

        <div class="mt-3">
            <button type="button" id="openFilters" class="btn-ghost relative !min-h-12 w-full sm:w-auto" aria-controls="filterSheet" aria-expanded="false">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M6 12h12M10 18h4"/></svg>
                और फ़िल्टर
                @if($moreCount)<span class="em-count">{{ $moreCount }}</span>@endif
            </button>
        </div>
        @endunless

        {{-- Result count + sort --}}
        <div class="mb-3 mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-lg font-bold text-ink-900"><span>{{ $total }}</span> रिश्ते मिले
                @if(empty($matches) && ! $lite)<span class="block text-sm font-normal text-ink-700">मेल (%) देखने के लिए अपना मंज़ूर बायोडाटा बनाइए</span>@endif</p>
            @unless($lite)<label class="flex items-center gap-2 text-sm font-semibold text-ink-800">
                <span>क्रम:</span>
                <select name="sort" form="filterForm" id="sortSelect" class="input !w-auto !min-h-11 !rounded-full !py-1.5 !pr-8 text-base" aria-label="क्रम चुनें">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>सबसे नए पहले</option>
                    <option value="match" {{ $sort === 'match' ? 'selected' : '' }}>सबसे ज़्यादा मेल पहले</option>
                    <option value="age" {{ $sort === 'age' ? 'selected' : '' }}>उम्र छोटी से बड़ी</option>
                </select>
            </label>@endunless
        </div>

        {{-- Chosen filters: removable chips + one big clear button --}}
        @if(count($chips))
            <div class="mb-4">
                <p class="mb-1 text-sm font-semibold text-ink-700">आपने ये फ़िल्टर लगाए हैं (हटाने के लिए &#10005; दबाइए):</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($chips as $c)
                        <a href="{{ $c['url'] }}" class="em-chip" aria-label="{{ $c['label'] }} हटाएँ">{{ $c['label'] }} <b aria-hidden="true">&#10005;</b></a>
                    @endforeach
                </div>
                <a href="{{ route('biodata.index', $forQs) }}" class="btn-ghost mt-3 w-full !min-h-12 !border-kumkum-300 !text-kumkum-700 sm:w-auto">&#10005; सब फ़िल्टर हटाएँ</a>
            </div>
        @endif

        @if($biodatas->isEmpty())
            <div class="card-pad bd-paisley py-12 text-center">
                <svg class="mx-auto mb-3 h-20 w-20" viewBox="0 0 80 80" fill="none" aria-hidden="true">
                    <circle cx="36" cy="36" r="22" fill="#f1e4d3" stroke="#ee5a52" stroke-width="3"/>
                    <path d="m52 52 15 15" stroke="#f59e0b" stroke-width="5" stroke-linecap="round"/>
                    <path d="M28 40c2.5 3 5.5 4.5 8 4.5s5.5-1.5 8-4.5M29 29h.01M43 29h.01" stroke="#96202a" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <p class="font-display text-xl font-bold text-ink-900">कोई रिश्ता नहीं मिला</p>
                <p class="mx-auto mt-1 max-w-sm text-base text-ink-700">{{ $lite ? 'नए बायोडाटा रोज़ जुड़ते हैं।' : 'फ़िल्टर कम करके देखें। नए बायोडाटा रोज़ जुड़ते हैं।' }}</p>
                @unless($lite)
                    <ul class="mx-auto mt-4 max-w-xs space-y-1 text-left text-sm text-ink-700">
                        <li>&#10024; उम्र की सीमा बड़ी रखिए</li>
                        <li>&#10024; सिर्फ़ ज़िला चुनिए, गाँव या इलाक़ा नहीं</li>
                        <li>&#10024; "सिर्फ़ जाँचे हुए" या "फ़ोटो वाले" बंद कीजिए</li>
                    </ul>
                    <a href="{{ route('biodata.index', $forQs) }}" class="btn-primary mt-5 !min-h-12">सब फ़िल्टर हटाएँ</a>
                @endunless
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 min-[520px]:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                @foreach($biodatas as $biodata)
                    @include($lite ? 'biodata.partials.card-lite' : 'biodata.partials.card', ['biodata' => $biodata, 'idx' => $loop->index])
                @endforeach
            </div>
            <div class="mt-6">{{ $biodatas->links() }}</div>
        @endif
    </div>
</div>
@endif

@push('head')
<script>document.documentElement.classList.add('js');</script>
<style>
    #openFilters, #closeFilters { display: none; }
    .js #openFilters { display: inline-flex; }
    .js #closeFilters { display: inline-flex; }
</style>
@endpush
@push('scripts')
<script>
(function () {
    document.documentElement.classList.add('js');
    var sheet = document.getElementById('filterSheet');
    var form = document.getElementById('filterForm');
    if (!sheet || !form) return;
    var wrap = document.getElementById('searchWrap'), openBtn = document.getElementById('openFilters');
    var wide = window.matchMedia('(min-width: 1024px)');
    function set(open) {
        sheet.classList.toggle('open', open);
        if (wrap) wrap.classList.toggle('em-side', open);
        if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = (open && !wide.matches) ? 'hidden' : '';
    }
    openBtn.addEventListener('click', function () { set(!sheet.classList.contains('open')); });
    document.getElementById('closeFilters').addEventListener('click', function () { set(false); });
    document.getElementById('filterBackdrop').addEventListener('click', function () { set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
    var sort = document.getElementById('sortSelect');
    if (sort) sort.addEventListener('change', function () { form.requestSubmit ? form.requestSubmit() : form.submit(); });
    [].forEach.call(document.querySelectorAll('select[data-autosubmit]'), function (s) {
        s.addEventListener('change', function () { form.requestSubmit ? form.requestSubmit() : form.submit(); });
    });
    // Only one quick-filter picker open at a time.
    var qf = [].slice.call(document.querySelectorAll('#quickFilters details'));
    qf.forEach(function (d) { d.addEventListener('toggle', function () { if (d.open) qf.forEach(function (o) { if (o !== d) o.open = false; }); }); });
    // Drop empty fields so the URL stays short.
    form.addEventListener('submit', function () {
        var els = [].slice.call(form.querySelectorAll('input[type=number],input[type=text],input[type=search],select'));
        els = els.concat([].slice.call(document.querySelectorAll('[form=filterForm]')));
        els.forEach(function (el) { if (el.value === '') el.disabled = true; });
    });
})();
</script>
@endpush
@endsection
