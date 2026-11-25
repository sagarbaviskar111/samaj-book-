@extends('layouts.public')

@section('content')
@php
    // ---- Editable heritage copy -------------------------------------------------------------
    // OWNER: please verify the exact wording of this line with the samaj elders before launch.
    $gadgeQuote = __('भूखे को अन्न, प्यासे को पानी, वस्त्रहीन को वस्त्र, बेघर को आश्रय।');
    // -------------------------------------------------------------------------------------------
    $reg = Route::has('register') ? route('register') : '#';
    $log = Route::has('login') ? route('login') : '#';
    $grads = ['from-kumkum-800 to-kumkum-500', 'from-gold-500 to-kumkum-500', 'from-neel-700 to-neel-500', 'from-tulsi-600 to-gold-500'];
    $samples = [
        ['t' => __('परिचय सम्मेलन'), 'b' => __('समाज के युवाओं के लिए वार्षिक परिचय सम्मेलन की तैयारी शुरू।'), 'g' => 0],
        ['t' => __('सामूहिक विवाह'), 'b' => __('सामूहिक विवाह समारोह के लिए पंजीकरण जल्द खुलेंगे।'), 'g' => 1],
        ['t' => __('समाज बैठक'), 'b' => __('सभी सदस्यों से निवेदन है कि आगामी बैठक में अवश्य पधारें।'), 'g' => 2],
    ];
    $heart = 'M12 21s-7.5-4.6-9.3-9.2C1.4 8.3 3.4 5 6.8 5c2 0 3.5 1.1 5.2 3 1.7-1.9 3.2-3 5.2-3 3.4 0 5.4 3.3 4.1 6.8C19.5 16.4 12 21 12 21z';
    $bubbles = [[8, 16, 14, 0], [22, 10, 17, 3], [38, 22, 12, 6], [55, 12, 15, 1.5], [70, 18, 13, 8], [84, 11, 16, 4.5], [93, 20, 12, 10]];
    $petals = [[6, 17, 0], [19, 21, 4], [34, 15, 8], [52, 19, 2], [66, 14, 6], [79, 22, 10], [91, 16, 12]];
@endphp

{{-- shared marigold symbol --}}
<svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="pub-marigold" viewBox="-50 -50 100 100">
            <g fill="currentColor">
                @foreach (range(0, 11) as $k)
                    <ellipse cx="0" cy="-30" rx="9" ry="19" transform="rotate({{ $k * 30 }})" opacity=".85"/>
                @endforeach
                <circle r="16" opacity=".95"/>
            </g>
            <circle r="7" fill="#fff9ef" opacity=".55"/>
        </symbol>
    </defs>
</svg>

{{-- HERO --}}
<section class="hr-hero hr-pattern relative overflow-hidden pb-24 pt-16 text-white sm:pb-32">
    {{-- ribbon --}}
    <div class="relative z-[3] border-b border-gold-300/25 bg-kumkum-950/55 py-2">
        <div class="mx-auto flex max-w-6xl items-center justify-center gap-3 px-4">
            <x-brand.diya class="h-7 w-8 shrink-0" />
            <p class="font-brand text-base leading-[1.6] tracking-wide text-gold-200 sm:text-lg">॥ श्री गणेशाय नमः ॥</p>
            <x-brand.diya class="h-7 w-8 shrink-0" />
        </div>
    </div>

    {{-- toran across the hero --}}
    <div class="relative z-[2]" aria-hidden="true"><x-brand.toran :units="14" class="h-12 sm:h-[4.5rem]" /></div>

    {{-- decorative drifting bubbles + marigold petals (CSS only) --}}
    <div class="pointer-events-none absolute inset-0 z-[1] overflow-hidden" aria-hidden="true">
        @foreach ($bubbles as [$x, $s, $t, $dl])
            <span class="hr-bubble" style="--x: {{ $x }}%; --s: {{ $s }}px; --t: {{ $t }}s; --dl: -{{ $dl }}s; --dx: {{ $x % 2 ? 26 : -22 }}px"></span>
        @endforeach
        @foreach ($petals as [$x, $t, $dl])
            <span class="hr-petal" style="--x: {{ $x }}%; --t: {{ $t }}s; --dl: -{{ $dl }}s; --dx: {{ $x % 2 ? 50 : -40 }}px"></span>
        @endforeach
        <svg class="pub-spin-slow absolute -right-28 top-24 h-[26rem] w-[26rem] text-gold-400/10" aria-hidden="true"><use href="#pub-marigold"/></svg>
    </div>

    <div class="relative z-[2] mx-auto mt-4 grid max-w-6xl items-center gap-10 px-4 lg:grid-cols-[1.15fr_.85fr] lg:gap-8">
        <div class="hr-reveal">
            <span class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3.5 py-1.5 text-sm font-semibold text-gold-200 ring-1 ring-gold-300/35">
                <svg class="h-4 w-4 text-gold-300" aria-hidden="true"><use href="#pub-marigold"/></svg> {{ __('धोबी (रजक) समाज का अपना, सुरक्षित मंच') }}
            </span>
            <h1 class="hr-brand mt-5 text-[2.3rem] font-normal sm:text-5xl lg:text-[3.45rem]">
                {{ __('अपने समाज में,') }} <span class="hr-shine">{{ __('अपनों के बीच') }}</span> {{ __('जीवनसाथी की तलाश') }}
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-kumkum-100/95">{{ __('जाँचे-परखे बायोडाटा, आपके नियंत्रण में प्राइवेसी, और परिवार जैसा अपना समाज।') }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $reg }}" class="btn-gold min-h-[3.25rem] px-8 py-4 text-base shadow-xl shadow-gold-500/30 hover:-translate-y-0.5">{{ __('रजिस्टर करें') }}
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14m-6-6l6 6-6 6"/></svg>
                </a>
                <a href="{{ $log }}" class="btn min-h-[3.25rem] border border-gold-300/50 bg-transparent px-8 py-4 text-base text-gold-100 hover:bg-white/10">{{ __('लॉगिन') }}</a>
            </div>
            <ul class="mt-7 flex flex-wrap gap-2 text-sm font-medium">
                @foreach (['जाँचे हुए प्रोफ़ाइल', 'प्राइवेसी पहले', 'समाज के लिए निःशुल्क'] as $chip)
                    <li class="inline-flex items-center gap-1.5 rounded-full bg-black/20 px-3 py-1.5 ring-1 ring-gold-300/25">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-gold-300" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>{{ __($chip) }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Ganesh + kalash --}}
        <div class="hr-reveal relative mx-auto flex w-full max-w-sm items-end justify-center gap-1 sm:max-w-md" style="--d:150ms">
            <div class="absolute inset-x-6 bottom-6 top-6 rounded-t-[999px] rounded-b-3xl bg-black/15 ring-1 ring-gold-300/30" aria-hidden="true"></div>
            <x-brand.kalash class="relative z-[1] mb-3 hidden h-28 w-auto sm:block" />
            <x-brand.ganesh class="relative z-[1] w-52 sm:w-64 lg:w-72" :light="true" loading="eager" />
            <div class="relative z-[1] mb-3 hidden flex-col items-center gap-3 sm:flex">
                <x-brand.diya class="h-10 w-12" />
                <x-brand.kalash class="h-20 w-auto" />
            </div>
            <x-brand.diya class="relative z-[1] mb-6 h-9 w-11 sm:hidden" />
        </div>
    </div>

    {{-- clothesline of biodata cards: the samaj's own trade as a signature motif --}}
    <div class="hr-reveal relative z-[2] mx-auto mt-12 max-w-3xl px-4" style="--d:250ms">
        <x-brand.clothesline />
        <p class="mt-5 text-center font-display text-lg leading-[1.7] text-gold-100">{{ __('साफ़ नीयत, साफ़-सुथरे और भरोसेमंद रिश्ते') }}</p>
        <p class="text-center text-sm text-kumkum-100/70">{{ __('Verified biodata. Photos stay blurred until you accept an interest.') }}</p>
    </div>

    <svg class="absolute inset-x-0 bottom-0 z-[2] h-8 w-full text-cream sm:h-12" viewBox="0 0 1440 48" preserveAspectRatio="none" fill="currentColor" aria-hidden="true"><path d="M0 48V20c240 28 480 28 720 8s480-20 720 0v20z"/></svg>
</section>

{{-- COUNTERS --}}
<section class="relative z-10 mx-auto -mt-2 max-w-5xl px-4" aria-label="{{ __('Community statistics') }}">
    <div class="hr-reveal card relative overflow-hidden border-t-4 border-gold-400 px-2 py-6 text-center">
        <p class="hr-eyebrow mb-4">{{ __('समाज का भरोसा') }}</p>
        <div class="grid grid-cols-3 divide-x divide-gold-200">
            @foreach ([['members', 'सदस्य', 'Members'], ['biodata', 'बायोडाटा', 'Biodata'], ['matches', 'रिश्ते जुड़े', 'Matches']] as [$k, $hi, $en])
                <div class="px-2">
                    <div class="font-display text-3xl font-bold text-kumkum-700 sm:text-5xl" data-count="{{ $stats[$k] }}">{{ $stats[$k] }}</div>
                    <div class="mt-1 text-base font-semibold text-ink-900">{{ __($hi) }}</div>
                    <div class="text-xs uppercase tracking-wide text-ink-600">{{ __($en) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- SAMAJ KI VIRASAT: Sant Gadge Maharaj --}}
<section class="hr-paper relative mt-16 overflow-hidden py-16 sm:py-24" aria-labelledby="virasat-h">
    <div class="mx-auto max-w-6xl px-4">
        <div class="hr-reveal text-center">
            <p class="hr-eyebrow">{{ __('समाज की विरासत') }}</p>
            <h2 id="virasat-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('हमारे प्रेरणास्रोत, संत गाडगे महाराज') }}</h2>
        </div>
        <x-brand.rangoli class="mt-4" />

        <div class="mt-10 grid items-center gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <div class="hr-reveal">
                <x-brand.sant-portrait :caption="__('संत गाडगे महाराज')" />
            </div>

            <div class="hr-reveal" style="--d:120ms">
                <p class="text-lg leading-[1.9] text-ink-800">{{ __('संत गाडगे महाराज (1876–1956) हमारे धोबी समाज में जन्मे वे महान संत और समाज-सुधारक हैं, जिनका जन्म महाराष्ट्र के अमरावती क्षेत्र के शेंडगाँव में हुआ। हाथ में झाड़ू और साथ में मिट्टी का गडगा लेकर वे गाँव-गाँव घूमे और स्वच्छता, सेवा और शिक्षा का संदेश दिया।') }}</p>
                <p class="mt-4 text-lg leading-[1.9] text-ink-800">{!! __('उनकी सीख थी कि सच्ची पूजा इंसान की सेवा में है। हर वर्ष :date को उनकी जयंती पर समाज उन्हें श्रद्धा से याद करता है।', ['date' => '<strong class="text-kumkum-700">'.e(__('23 फ़रवरी')).'</strong>']) !!}</p>

                {{-- quote: edit $gadgeQuote at the top of this file. OWNER: verify exact wording with samaj elders. --}}
                <blockquote class="relative mt-7 overflow-hidden rounded-2xl border-l-4 border-kumkum-600 bg-white p-5 shadow-soft">
                    <x-brand.broom-gadga show="both" class="absolute right-3 top-3 h-10 w-auto text-kumkum-700/25" />
                    <p class="text-sm font-semibold text-kumkum-600">{{ __('संत गाडगे महाराज का संदेश') }}</p>
                    <p class="hr-h mt-1 text-xl leading-[1.8] text-ink-900">&ldquo;{{ $gadgeQuote }}&rdquo;</p>
                </blockquote>

                <ul class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['सफ़ाई', 'स्वच्छ तन, मन और गाँव', 'M12 3v8M9 11h6l1 4H8zM7 15l-1 6M10 15l-.5 6M14 15l.5 6M17 15l1 6'],
                        ['सेवा', 'ज़रूरतमंद की मदद', 'M12 21s-7.5-4.6-9.3-9.2C1.4 8.3 3.4 5 6.8 5c2 0 3.5 1.1 5.2 3 1.7-1.9 3.2-3 5.2-3 3.4 0 5.4 3.3 4.1 6.8C19.5 16.4 12 21 12 21z'],
                        ['शिक्षा', 'हर बच्चा पढ़े', 'M3 6c3-1.5 6-1.5 9 0v14c-3-1.5-6-1.5-9 0zM12 6c3-1.5 6-1.5 9 0v14c-3-1.5-6-1.5-9 0z'],
                        ['समानता', 'सब बराबर, सब अपने', 'M5 9h14M5 15h14'],
                    ] as $i => [$t, $d, $p])
                        <li class="rounded-2xl bg-white p-3.5 text-center shadow-soft ring-1 ring-gold-200/70">
                            <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-gradient-to-br from-gold-300 to-gold-500 text-kumkum-800">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $p }}"/></svg>
                            </span>
                            <span class="hr-h mt-2 block text-lg font-bold text-ink-900">{{ __($t) }}</span>
                            <span class="block text-sm leading-snug text-ink-600">{{ __($d) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- NEWS & POSTERS --}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:py-20" aria-labelledby="news-h">
    <div class="hr-reveal flex items-end justify-between">
        <div>
            <p class="hr-eyebrow">{{ __('Samaj updates') }}</p>
            <h2 id="news-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('समाचार और पोस्टर') }}</h2>
        </div>
        <div class="hidden gap-2 sm:flex">
            <button type="button" class="grid h-11 w-11 place-items-center rounded-full bg-white text-kumkum-700 shadow-soft ring-1 ring-gold-200 transition hover:-translate-y-0.5 hover:bg-gold-50" data-slide="-1" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
            <button type="button" class="grid h-11 w-11 place-items-center rounded-full bg-white text-kumkum-700 shadow-soft ring-1 ring-gold-200 transition hover:-translate-y-0.5 hover:bg-gold-50" data-slide="1" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
        </div>
    </div>
    <div id="newsrow" class="hr-snap -mx-4 mt-6 flex gap-4 overflow-x-auto px-4 pb-4" tabindex="0" aria-label="{{ __('News carousel') }}">
        @forelse ($news as $i => $n)
            <article class="card w-[82%] shrink-0 overflow-hidden transition hover:-translate-y-1 hover:shadow-glow sm:w-[340px]">
                @if ($n->image)
                    <img src="{{ asset('storage/'.$n->image) }}" alt="{{ $n->title }}" loading="lazy" class="h-52 w-full object-cover">
                @else
                    <div class="hr-pattern grid h-52 place-items-center bg-gradient-to-br {{ $grads[$i % 4] }} p-5 text-center font-display text-2xl font-bold leading-[1.6] text-white">{{ $n->title }}</div>
                @endif
                <div class="p-4">
                    <span class="badge-ink">{{ $n->type === 'poster' ? __('Poster') : __('News') }}</span>
                    <h3 class="mt-2 text-lg font-bold leading-snug">{{ $n->title }}</h3>
                    @if ($n->body)<p class="mt-1 line-clamp-3 text-base text-ink-800/80">{{ $n->body }}</p>@endif
                    <p class="mt-2 text-xs text-ink-600">{{ $n->created_at?->format('d M Y') }}</p>
                </div>
            </article>
        @empty
            @foreach ($samples as $s)
                <article class="card w-[82%] shrink-0 overflow-hidden transition hover:-translate-y-1 hover:shadow-glow sm:w-[340px]">
                    <div class="hr-pattern grid h-44 place-items-center bg-gradient-to-br {{ $grads[$s['g']] }} p-5 text-center font-display text-3xl font-bold leading-[1.6] text-white">{{ $s['t'] }}</div>
                    <div class="p-4">
                        <span class="badge-ink">{{ __('Samaj') }}</span>
                        <p class="mt-2 text-base text-ink-800/90">{{ $s['b'] }}</p>
                    </div>
                </article>
            @endforeach
        @endforelse
    </div>
</section>

{{-- EVENTS --}}
@if ($events->isNotEmpty())
<section class="hr-sandal py-16" aria-labelledby="ev-h">
    <div class="mx-auto max-w-4xl px-4">
        <div class="hr-reveal text-center">
            <p class="hr-eyebrow">{{ __('Upcoming') }}</p>
            <h2 id="ev-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('आगामी कार्यक्रम') }}</h2>
        </div>
        <ul class="mt-8 space-y-3">
            @foreach ($events as $e)
                <li class="hr-reveal card flex items-center gap-4 p-3 transition hover:-translate-y-0.5 sm:p-4" style="--d: {{ $loop->index * 80 }}ms">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-kumkum-800 to-kumkum-600 text-center text-white shadow-glow ring-2 ring-gold-300">
                        <div><div class="font-display text-2xl font-bold leading-none">{{ $e->event_at->format('d') }}</div><div class="text-[11px] font-semibold uppercase tracking-wider text-gold-200">{{ $e->event_at->format('M') }}</div></div>
                    </div>
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-bold">{{ $e->title }}</h3>
                        <p class="text-sm text-ink-700">{{ $e->event_at->format('h:i A') }}@if ($e->event_place) &middot; {{ $e->event_place }}@endif</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- HOW IT WORKS --}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:py-24" aria-labelledby="how-h">
    <div class="hr-reveal text-center">
        <p class="hr-eyebrow">{{ __('How it works') }}</p>
        <h2 id="how-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('कैसे काम करता है') }}</h2>
        <p class="mx-auto mt-2 max-w-md text-base text-ink-700">{{ __('चार आसान कदम, अपनों के बीच रिश्ते तक।') }}</p>
    </div>
    @php
        $steps = [
            ['रजिस्टर करें', 'फ़ोन नंबर से रजिस्टर करें। एडमिन केवल असली सदस्यों को अप्रूव करते हैं।', 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM19 8v6m3-3h-6'],
            ['बायोडाटा बनाएँ', 'अपना बायोडाटा भरें, फ़ोटो जोड़ें और PDF डाउनलोड करें।', 'M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8zM14 3v5h5M9 13h6M9 17h6'],
            ['खोजें और इंटरेस्ट भेजें', 'फ़िल्टर से खोजें, शॉर्टलिस्ट करें और इंटरेस्ट भेजें।', 'M21 21l-4.3-4.3M11 18a7 7 0 100-14 7 7 0 000 14z'],
            ['स्वीकार होने पर संपर्क', 'इंटरेस्ट स्वीकार होने के बाद ही संपर्क विवरण खुलता है।', $heart],
        ];
    @endphp
    <div class="relative mt-12">
        <div class="absolute left-[12.5%] right-[12.5%] top-7 hidden h-0.5 bg-gradient-to-r from-transparent via-gold-400 to-transparent lg:block" aria-hidden="true"></div>
        <div class="absolute bottom-6 left-7 top-6 w-0.5 bg-gradient-to-b from-gold-400 via-kumkum-300 to-transparent lg:hidden" aria-hidden="true"></div>
        <ol class="grid gap-8 lg:grid-cols-4 lg:gap-6">
            @foreach ($steps as $i => [$t, $d, $p])
                <li class="hr-reveal relative flex gap-4 lg:block lg:text-center" style="--d: {{ $i * 120 }}ms">
                    <div class="relative z-10 shrink-0 lg:mx-auto lg:w-14">
                        <div class="relative grid h-14 w-14 place-items-center rounded-full bg-gradient-to-br from-kumkum-700 to-kumkum-500 text-white shadow-glow ring-4 ring-cream">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $p }}"/></svg>
                        </div>
                        <span class="absolute -right-1 -top-1 grid h-6 w-6 place-items-center rounded-full bg-gold-400 text-xs font-extrabold text-ink-950 ring-2 ring-cream">{{ $i + 1 }}</span>
                    </div>
                    <div class="card-pad flex-1 lg:mt-5">
                        <h3 class="text-lg font-bold leading-snug">{{ __($t) }}</h3>
                        <p class="mt-1 text-base text-ink-700">{{ __($d) }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- PRIVACY VISUAL --}}
<section class="hr-neel hr-pattern relative overflow-hidden py-16 text-white sm:py-24" aria-labelledby="feat-h">
    <div class="relative mx-auto max-w-6xl px-4">
        <div class="hr-reveal mx-auto max-w-2xl text-center">
            <p class="hr-eyebrow hr-eyebrow-light">{{ __('Why Samaj Book') }}</p>
            <h2 id="feat-h" class="hr-h mt-2 text-3xl font-bold sm:text-4xl">{{ __('आपकी प्राइवेसी, हमारी प्राथमिकता') }}</h2>
            <p class="mt-3 text-base text-neel-100">{{ __('आपकी फ़ोटो और नंबर कौन देखे, यह आप तय करते हैं।') }} <span class="text-neel-200">{{ __('(कार्ड पर टैप करें या माउस ले जाएँ।)') }}</span></p>
        </div>

        <div class="mt-10 grid items-center gap-8 lg:grid-cols-2">
            <div class="hr-reveal pub-privacy mx-auto w-full max-w-sm outline-none" tabindex="0" role="img" aria-label="{{ __('Illustration: contact hidden until interest is accepted, photo blurred with watermark') }}">
                <div class="rounded-[1.75rem] bg-cream p-3 text-ink-950 shadow-2xl ring-2 ring-gold-300/60">
                    <div class="relative h-52 overflow-hidden rounded-2xl bg-gradient-to-br from-gold-300 via-kumkum-400 to-kumkum-800">
                        <svg viewBox="0 0 100 100" class="pub-blur-demo absolute inset-0 m-auto h-40 w-40 text-white/80" fill="currentColor" aria-hidden="true"><circle cx="50" cy="36" r="20"/><path d="M12 96c0-22 17-36 38-36s38 14 38 36z"/></svg>
                        <div class="absolute inset-0 grid place-items-center overflow-hidden" aria-hidden="true">
                            <span class="-rotate-12 select-none whitespace-nowrap font-display text-2xl font-bold tracking-widest text-white/30">SAMAJBOOK &middot; SAMAJBOOK</span>
                        </div>
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-kumkum-800"><svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z"/></svg>{{ __('Photo blur + watermark') }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 rounded-2xl bg-sandal px-4 py-3">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-ink-700">{{ __('Contact') }}</div>
                            <div class="relative font-mono text-base font-bold tracking-wider text-ink-900">
                                <span class="pub-lock-shut" aria-hidden="true">98•••• ••••0</span>
                                <span class="pub-lock-open absolute left-0 top-0" aria-hidden="true">98765 43210</span>
                            </div>
                        </div>
                        <div class="relative h-10 w-10">
                            <span class="pub-lock-shut absolute inset-0 grid place-items-center rounded-full bg-kumkum-700 text-white"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z"/></svg></span>
                            <span class="pub-lock-open absolute inset-0 grid place-items-center rounded-full bg-tulsi-500 text-white"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-1.5 text-center text-[11px] font-semibold">
                        <span class="rounded-full bg-kumkum-50 py-1.5 text-kumkum-700">1 Interest</span>
                        <span class="rounded-full bg-gold-100 py-1.5 text-gold-600">2 Accept</span>
                        <span class="rounded-full bg-emerald-50 py-1.5 text-tulsi-600">3 Unlocked</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @php
                    $feats = [
                        ['संपर्क तभी खुले जब आप चाहें', 'फ़ोन नंबर तभी दिखेगा जब आप इंटरेस्ट स्वीकार करें।', 'M12 11c1.1 0 2-.9 2-2V7a2 2 0 10-4 0v2c0 1.1.9 2 2 2zM6 11h12v9H6z'],
                        ['फ़ोटो ब्लर और वॉटरमार्क', 'दुरुपयोग से बचाव के लिए सुरक्षित फ़ोटो।', 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z'],
                        ['एडमिन द्वारा जाँच', 'हर सदस्य और बायोडाटा की एडमिन जाँच।', 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4'],
                        ['बायोडाटा PDF', 'सुंदर बायोडाटा PDF डाउनलोड और शेयर करें।', 'M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8zM14 3v5h5'],
                        ['स्मार्ट सूचनाएँ', 'नए रिश्ते, इंटरेस्ट और अपडेट की सूचना।', 'M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9zM13.7 21a2 2 0 01-3.4 0'],
                        ['समाज का फ़ीड', 'समाज की खबरें, फ़ोटो और बधाइयाँ एक जगह।', 'M9 11a4 4 0 100-8 4 4 0 000 8zM3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2M16 3.1a4 4 0 010 7.8M21 21v-2a4 4 0 00-3-3.9'],
                    ];
                @endphp
                @foreach ($feats as $i => [$t, $d, $p])
                    <div class="hr-reveal rounded-[var(--radius-card)] bg-white/[.07] p-4 ring-1 ring-gold-300/25 transition hover:-translate-y-1 hover:bg-white/10" style="--d: {{ ($i % 2) * 90 }}ms">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-gold-400 text-ink-950">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $p }}"/></svg>
                        </div>
                        <h3 class="mt-3 font-sans text-base font-bold leading-snug">{{ __($t) }}</h3>
                        <p class="mt-1 text-base leading-snug text-neel-100">{{ __($d) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- SUCCESS STORIES --}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:py-24" aria-labelledby="ss-h">
    <div class="hr-reveal text-center">
        <p class="hr-eyebrow">{{ __('Success stories') }}</p>
        <h2 id="ss-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('सफल कहानियाँ') }}</h2>
    </div>
    @if ($stories->isNotEmpty())
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($stories as $s)
                <figure class="hr-reveal card overflow-hidden transition hover:-translate-y-1 hover:shadow-glow" style="--d: {{ ($loop->index % 3) * 90 }}ms">
                    @if ($s->image)
                        <img src="{{ asset('storage/'.$s->image) }}" alt="{{ $s->couple_names }}" loading="lazy" class="h-48 w-full object-cover">
                    @else
                        <div class="hr-pattern relative grid h-36 place-items-center bg-gradient-to-br from-kumkum-500 to-kumkum-800 text-white">
                            <svg viewBox="0 0 24 24" class="h-12 w-12 drop-shadow" fill="currentColor" aria-hidden="true"><path d="{{ $heart }}"/></svg>
                            <svg class="absolute right-3 top-3 h-10 w-10 text-gold-300/70" aria-hidden="true"><use href="#pub-marigold"/></svg>
                        </div>
                    @endif
                    <figcaption class="p-5">
                        <div class="font-display text-xl font-bold leading-[1.6]">{{ $s->couple_names }}</div>
                        @if ($s->story)<p class="mt-2 line-clamp-4 text-base leading-relaxed text-ink-800/85">&ldquo;{{ $s->story }}&rdquo;</p>@endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @else
        <div class="hr-reveal mx-auto mt-10 grid max-w-4xl gap-5 md:grid-cols-[1fr_1.2fr]">
            <div class="hr-pattern relative grid min-h-[12rem] place-items-center overflow-hidden rounded-[var(--radius-card)] bg-gradient-to-br from-kumkum-500 via-kumkum-700 to-kumkum-900 text-white shadow-glow">
                <div class="relative z-10 flex items-center">
                    <span class="grid h-16 w-16 place-items-center rounded-full bg-white/15 ring-2 ring-gold-300/70"><svg viewBox="0 0 100 100" class="h-9 w-9" fill="currentColor"><circle cx="50" cy="36" r="18"/><path d="M14 94c0-22 16-34 36-34s36 12 36 34z"/></svg></span>
                    <svg viewBox="0 0 24 24" class="pub-float z-10 -mx-2 h-9 w-9 text-gold-300" fill="currentColor" aria-hidden="true"><path d="{{ $heart }}"/></svg>
                    <span class="grid h-16 w-16 place-items-center rounded-full bg-white/15 ring-2 ring-gold-300/70"><svg viewBox="0 0 100 100" class="h-9 w-9" fill="currentColor"><circle cx="50" cy="36" r="18"/><path d="M14 94c0-22 16-34 36-34s36 12 36 34z"/></svg></span>
                </div>
            </div>
            <div class="card relative overflow-hidden bg-gradient-to-br from-white to-gold-50 p-7">
                <span class="absolute -right-2 -top-4 font-display text-8xl leading-none text-gold-300/60" aria-hidden="true">&rdquo;</span>
                <p class="relative font-display text-xl font-bold leading-[1.7]">{{ __('आपकी कहानी यहाँ हो सकती है') }}</p>
                <p class="relative mt-2 text-base leading-relaxed text-ink-700">{{ __('अपना जीवनसाथी खोजें और अपनी खुशी की खबर पूरे समाज के साथ बाँटें।') }}</p>
                <a href="{{ $reg }}" class="btn-primary relative mt-5 min-h-[2.75rem]">{{ __('अभी शुरू करें') }}</a>
            </div>
        </div>
    @endif
</section>

{{-- FAQ --}}
<section class="hr-sandal py-16 sm:py-24" aria-labelledby="faq-h">
    <div class="mx-auto max-w-3xl px-4">
        <div class="hr-reveal text-center">
            <p class="hr-eyebrow">{{ __('FAQ') }}</p>
            <h2 id="faq-h" class="hr-h mt-2 text-3xl font-bold text-ink-900 sm:text-4xl">{{ __('अक्सर पूछे जाने वाले प्रश्न') }}</h2>
        </div>
        <div class="hr-faq mt-8 space-y-3">
            @foreach ([
                ['क्या यह सच में फ्री है?', 'हाँ, समाज के सदस्यों के लिए पूरी तरह निःशुल्क है।'],
                ['मेरा फ़ोन नंबर कौन देख सकता है?', 'केवल वही जिसका इंटरेस्ट आप स्वीकार करें।'],
                ['रजिस्ट्रेशन के बाद क्या होता है?', 'एडमिन आपकी जानकारी जाँचकर अप्रूव करते हैं, फिर पूरा एक्सेस मिलता है।'],
                ['कम से कम उम्र क्या है?', 'लड़की 18 और लड़के की 21 वर्ष।'],
                ['क्या आधार नंबर चाहिए?', 'नहीं, हम आधार नंबर नहीं माँगते और न ही सेव करते हैं।'],
            ] as $i => [$q, $a])
                <details class="hr-reveal overflow-hidden rounded-2xl border border-gold-200 bg-white/70 transition" style="--d: {{ $i * 60 }}ms">
                    <summary class="flex min-h-[3.5rem] cursor-pointer items-center justify-between gap-3 px-4 py-3 text-base font-semibold text-ink-900 sm:px-5">
                        <span>{{ __($q) }}</span>
                        <span class="hr-plus grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gold-100 text-kumkum-700">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </summary>
                    <p class="border-t border-gold-100 px-4 pb-4 pt-3 text-base leading-relaxed text-ink-800 sm:px-5">{{ __($a) }}</p>
                </details>
            @endforeach
        </div>
        <p class="mt-6 text-center text-base"><a href="{{ route('page.faq') }}" class="font-semibold text-kumkum-700 underline">{{ __('सभी प्रश्न देखें') }}</a></p>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="px-4 py-16 sm:py-20">
    <div class="hr-reveal hr-frame mx-auto max-w-4xl shadow-glow">
        <div class="hr-frame-in hr-hero hr-pattern relative overflow-hidden px-6 py-14 text-center text-white">
            <x-brand.diya class="absolute bottom-4 left-4 hidden h-12 w-14 sm:block" />
            <x-brand.kalash class="absolute bottom-4 right-5 hidden h-24 w-auto sm:block" />
            <x-brand.broom-gadga show="both" class="mx-auto mb-3 h-10 w-auto text-gold-200" />
            <h2 class="hr-brand text-3xl sm:text-5xl">{{ __('आज ही अपना सफ़र शुरू करें') }}</h2>
            <p class="mx-auto mt-3 max-w-lg text-lg text-kumkum-100/90">{{ __('अपने समाज के परिवार से जुड़ें। एक मिनट में रजिस्टर करें और सही रिश्ते को आप तक आने दें।') }}</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $reg }}" class="btn-gold min-h-[3.25rem] px-10 py-4 text-base shadow-xl hover:-translate-y-0.5">{{ __('रजिस्टर करें') }}</a>
                <button type="button" data-install class="btn min-h-[3.25rem] border border-gold-300/50 bg-transparent px-8 py-4 text-base text-gold-100 hover:bg-white/10" hidden>{{ __('Install App') }}</button>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var els = document.querySelectorAll('.hr-reveal');
    if (reduce || !('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); }
    else {
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } });
        }, {threshold: .12, rootMargin: '0px 0px -40px 0px'});
        els.forEach(function (e) { io.observe(e); });
    }

    // count-up (progressive enhancement; server-rendered text stays as fallback)
    var counters = document.querySelectorAll('[data-count]');
    var run = function (el) {
        var raw = el.dataset.count || '', m = raw.match(/^(\D*)([\d,]+)(.*)$/);
        if (!m) return;
        var target = parseInt(m[2].replace(/,/g, ''), 10);
        if (!isFinite(target) || target <= 0) return;
        var t0 = null, dur = 1400;
        var tick = function (t) {
            if (t0 === null) t0 = t;
            var p = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - p, 3);
            el.textContent = m[1] + Math.round(target * e).toLocaleString('en-IN') + m[3];
            if (p < 1) requestAnimationFrame(tick); else el.textContent = raw;
        };
        requestAnimationFrame(tick);
    };
    if (!reduce && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (es) {
            es.forEach(function (x) { if (x.isIntersecting) { run(x.target); cio.unobserve(x.target); } });
        }, {threshold: .6});
        counters.forEach(function (c) { cio.observe(c); });
    }

    var row = document.getElementById('newsrow');
    if (row) {
        var step = function (d) { var c = row.firstElementChild; row.scrollBy({left: d * (c ? c.getBoundingClientRect().width + 16 : 300), behavior: reduce ? 'auto' : 'smooth'}); };
        document.querySelectorAll('[data-slide]').forEach(function (b) { b.addEventListener('click', function () { step(+b.dataset.slide); }); });
        if (!reduce && row.children.length > 1) {
            var paused = false;
            setInterval(function () {
                if (paused || document.hidden) return;
                var end = row.scrollLeft + row.clientWidth >= row.scrollWidth - 8;
                if (end) row.scrollTo({left: 0, behavior: 'smooth'}); else step(1);
            }, 5000);
            ['pointerdown', 'focusin', 'mouseenter', 'touchstart'].forEach(function (ev) { row.addEventListener(ev, function () { paused = true; }, {passive: true}); });
        }
    }
})();
</script>
@endpush
