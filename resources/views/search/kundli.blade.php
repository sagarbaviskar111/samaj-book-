@extends('layouts.app')
@section('title', 'कुंडली मिलान')
@section('wide', '1')
@section('content')
@use('App\Support\BiodataOptions', 'O')
@php
    $complete = $result['complete'];
    $sides = ['boy' => $boy, 'girl' => $girl];
    $palette = [
        'rose' => ['stroke' => '#d63a34', 'text' => 'text-kumkum-700', 'bg' => 'bg-kumkum-50', 'ring' => 'ring-kumkum-200', 'bar' => 'bg-kumkum-500'],
        'amber' => ['stroke' => '#ee9e0a', 'text' => 'text-gold-600', 'bg' => 'bg-gold-50', 'ring' => 'ring-gold-200', 'bar' => 'bg-gold-500'],
        'emerald' => ['stroke' => '#2f7d57', 'text' => 'text-tulsi-600', 'bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200', 'bar' => 'bg-tulsi-500'],
    ];
    $explain = [
        'varna' => 'स्वभाव और काम करने के तरीक़े का मेल।',
        'vashya' => 'एक-दूसरे पर असर और तालमेल।',
        'tara' => 'सेहत और भाग्य का मेल।',
        'yoni' => 'आपसी लगाव और स्वभाव की समझ।',
        'graha_maitri' => 'मन और सोच का मेल। यह सबसे अहम माना जाता है।',
        'gana' => 'आदतों और मिज़ाज का मेल।',
        'bhakoot' => 'परिवार की खुशहाली और आपसी प्रेम।',
        'nadi' => 'सेहत और संतान से जुड़ा मेल। इसे बहुत अहम माना जाता है।',
    ];
    $chip = fn ($b) => [
        'name' => $b->name,
        'rashi' => $b->rashi ? (O::rashis()[$b->rashi] ?? $b->rashi) : null,
        'nak' => $b->nakshatra ? (O::nakshatras()[$b->nakshatra] ?? $b->nakshatra).($b->nakshatra_pada ? ' · चरण '.$b->nakshatra_pada : '') : null,
    ];
@endphp

<div class="mx-auto max-w-3xl">
    <a href="{{ route('interests.index', ['tab' => 'accepted']) }}" class="mb-3 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-ink-600">&larr; रिश्ते की पसंद पर वापस जाएँ</a>
    <h1 class="section-title !text-2xl sm:!text-3xl">कुंडली मिलान <span class="text-gold-600">&#128302;</span></h1>
    <p class="text-base text-ink-700">अष्टकूट गुण मिलान (Kundli Milan) &middot; कुल 36 गुण</p>

    {{-- Disclaimer: always visible, above the fold --}}
    <div id="kundli-disclaimer" class="mt-3 rounded-2xl bg-gold-50 p-4 ring-1 ring-gold-200" role="note">
        <p class="flex items-start gap-2 text-base font-bold text-ink-900"><span aria-hidden="true">&#9888;&#65039;</span><span>यह नतीजा सिर्फ़ ऑनलाइन गणना से बना अनुमान है, 100% सही नहीं है। कृपया अपने पंडित से कुंडली मिलवाकर ही फ़ैसला लीजिए।</span></p>
        <p class="mt-1.5 pl-6 text-sm text-ink-700">This is an algorithm-based estimate only and is not 100% accurate. Please have your own pandit verify the kundli before deciding.</p>
    </div>

    {{-- Profile chips --}}
    <div class="mt-4 grid grid-cols-2 gap-3">
        @foreach($sides as $role => $b)
            @php $c = $chip($b); @endphp
            <div class="card-pad !p-3.5">
                <p class="text-sm font-bold text-ink-700">{{ $role === 'boy' ? 'वर (दूल्हा)' : 'वधू (दुल्हन)' }}</p>
                <p class="truncate font-display text-base font-bold text-ink-900">{{ $c['name'] }}</p>
                <p class="mt-1 text-sm text-ink-700">{{ $c['rashi'] ?? 'राशि: —' }}</p>
                <p class="text-sm text-ink-700">{{ $c['nak'] ?? 'नक्षत्र: —' }}</p>
            </div>
        @endforeach
    </div>

    @if(! $complete)
        {{-- Missing data state --}}
        <div class="card-pad mt-4 text-center" id="kundli-missing">
            <p class="text-4xl" aria-hidden="true">&#128302;</p>
            <p class="mt-2 font-display text-xl font-bold text-ink-900">कुंडली मिलान के लिए दोनों तरफ़ की राशि और नक्षत्र चाहिए</p>
            <div class="mt-4 space-y-3 text-left">
                @foreach($sides as $role => $b)
                    @php
                        $miss = $result['missing'][$role];
                        $mine = (int) $b->user_id === (int) $uid;
                    @endphp
                    @if($miss)
                        <div class="rounded-2xl bg-ink-50 p-3 ring-1 ring-ink-100">
                            <p class="text-base font-bold text-ink-900">{{ $b->name }} ({{ $role === 'boy' ? 'वर' : 'वधू' }}) की {{ implode(' और ', array_map(fn ($m) => $m === 'rashi' ? 'राशि' : 'नक्षत्र', $miss)) }} अभी नहीं भरी गई है।</p>
                            @if($mine)
                                <a href="{{ route('biodata.edit', $b) }}" class="btn-primary mt-2 !min-h-12">अपना बायोडाटा बदलकर भरिए</a>
                            @else
                                <p class="mt-1 text-sm text-ink-700">उनसे कहिए कि अपने बायोडाटा में राशि और नक्षत्र भर दें।</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        @php
            $band = $result['band'];
            $pc = $palette[$band['color']];
            $r = 54; $circ = 2 * M_PI * $r;
            $dash = $circ * min(1, $result['total'] / $result['max']);
            $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
            $f = $result['flags'];
        @endphp

        {{-- Score --}}
        <div class="card-pad mt-4 flex flex-col items-center gap-3 text-center sm:flex-row sm:text-left" id="kundli-score">
            <div class="relative h-40 w-40 shrink-0">
                <svg viewBox="0 0 128 128" class="h-full w-full -rotate-90" aria-hidden="true">
                    <circle cx="64" cy="64" r="{{ $r }}" fill="none" stroke="#f1e4d3" stroke-width="12"/>
                    <circle cx="64" cy="64" r="{{ $r }}" fill="none" stroke="{{ $pc['stroke'] }}" stroke-width="12" stroke-linecap="round" stroke-dasharray="{{ round($dash, 2) }} {{ round($circ, 2) }}"/>
                </svg>
                <div class="absolute inset-0 grid place-items-center">
                    <div><p class="font-display text-4xl font-bold text-ink-900">{{ $fmt($result['total']) }}<span class="text-lg text-ink-500">/{{ $result['max'] }}</span></p><p class="text-sm text-ink-700">{{ $fmt($result['percentage']) }}%</p></div>
                </div>
            </div>
            <div>
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold ring-1 {{ $pc['bg'] }} {{ $pc['text'] }} {{ $pc['ring'] }}">{{ $band['hi'] }} ({{ $band['en'] }})</span>
                <p class="mt-2 text-base text-ink-800">18 से कम: कम &middot; 18 से 24: मध्यम &middot; 25 से 32: अच्छा &middot; 33 से ज़्यादा: उत्तम</p>
            </div>
        </div>

        {{-- Dosha chips --}}
        @php
            $mk = $f['manglik'];
            $chips = [
                ['Nadi', $f['nadi_dosha'], 'नाड़ी दोष', 'नाड़ी ठीक'],
                ['Bhakoot', $f['bhakoot_dosha'], 'भकूट दोष', 'भकूट ठीक'],
                ['Gana', $f['gana_dosha'], 'गण दोष', 'गण ठीक'],
            ];
        @endphp
        <div class="mt-4 flex flex-wrap gap-2" id="kundli-doshas">
            @foreach($chips as [$k, $bad, $badLabel, $okLabel])
                <span class="{{ $bad ? 'badge-bad' : 'badge-ok' }}">{{ $bad ? $badLabel : $okLabel }}</span>
            @endforeach
            <span class="{{ $mk['status'] === 'ok' ? 'badge-ok' : ($mk['status'] === 'check' ? 'badge-warn' : 'badge-ink') }}">मांगलिक: {{ $mk['status'] === 'ok' ? 'ठीक' : ($mk['status'] === 'check' ? 'पंडित से पूछें' : 'जानकारी नहीं') }}</span>
        </div>
        <p class="mt-1 text-sm text-ink-700">{{ $mk['status'] === 'ok' ? 'दोनों तरफ़ मांगलिक की स्थिति एक जैसी है।' : ($mk['status'] === 'check' ? 'मांगलिक की स्थिति अलग-अलग है। इसके बारे में पंडित जी से ज़रूर पूछिए।' : 'मांगलिक की जानकारी पूरी नहीं है।') }}</p>

        {{-- Koota rows --}}
        <div class="card-pad mt-4 space-y-4" id="kundli-kootas">
            @foreach($result['kootas'] as $k)
                @php $w = $k['max'] > 0 ? round($k['obtained'] / $k['max'] * 100) : 0; $bar = $w >= 70 ? 'bg-emerald-500' : ($w >= 35 ? 'bg-amber-500' : 'bg-rose-500'); @endphp
                <div>
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-base font-bold text-ink-900">{{ $k['name_hi'] }} <span class="font-medium text-ink-700">({{ $k['name_en'] }})</span></p>
                        <p class="text-sm font-bold text-ink-900">{{ $fmt($k['obtained']) }}<span class="text-ink-500">/{{ $k['max'] }}</span></p>
                    </div>
                    <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-ink-50" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $k['max'] }}" aria-valuenow="{{ $k['obtained'] }}">
                        <div class="h-full rounded-full {{ $bar }}" style="width: {{ $w }}%"></div>
                    </div>
                    <p class="mt-1 text-sm text-ink-700">{{ $k['boy_value'] }} &harr; {{ $k['girl_value'] }}</p>
                    <p class="text-base text-ink-800">{{ $explain[$k['key']] ?? '' }} <strong>{{ $w >= 70 ? 'यहाँ मेल अच्छा है।' : ($w >= 35 ? 'यहाँ मेल ठीक-ठाक है।' : 'यहाँ मेल कम है।') }}</strong></p>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-center text-base text-ink-800">फिर से याद रखिए: यह सिर्फ़ अनुमान है। आख़िरी फ़ैसले से पहले पंडित जी से कुंडली ज़रूर मिलवाइए।</p>
    @endif
</div>
@endsection
