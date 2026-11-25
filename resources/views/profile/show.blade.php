@extends('layouts.app')
@section('wide', '1')
@section('content')
@php
    $pending = $pending ?? false;
    $loc = collect([$user->area, $user->city, $user->district, $user->state])->filter()->implode(', ');
    $statusChip = ['approved' => 'badge-ok', 'pending' => 'badge-warn', 'draft' => 'badge-ink', 'rejected' => 'badge-bad', 'paused' => 'badge-ink', 'married' => 'badge-ok'];

    $menu = $pending ? [] : [
        'मेरा काम' => [
            ['biodata.mine', 'मेरा बायोडाटा', __(':n बायोडाटा', ['n' => $stats['biodatas']]), 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0'],
            ['interests.index', 'मेरे रिश्ते', $stats['received'] ? __(':n नए अनुरोध आपके जवाब का इंतज़ार कर रहे हैं', ['n' => $stats['received']]) : __('भेजी हुई, आई हुई और मंज़ूर पसंद'), 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
            ['shortlist.index', 'पसंद की सूची', __(':n सेव किए हुए', ['n' => $stats['shortlist']]), 'M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38a.56.56 0 0 1-.84.61L12 16.76l-4.73 2.87a.56.56 0 0 1-.84-.61l1.28-5.38-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44 2.13-5.11Z'],
            ['events.index', 'कार्यक्रम', __('नाम लिखवाएँ और जुड़ें'), 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['notifications.index', 'सूचनाएँ', $stats['unread'] ? __(':n नई', ['n' => $stats['unread']]) : __('सब देख लिया'), 'M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.08 5.46 1.31m5.71 0a24.26 24.26 0 0 1-5.71 0m5.71 0a3 3 0 1 1-5.71 0'],
        ],
        'अकाउंट' => [
            ['profile.edit', 'प्रोफ़ाइल बदलें', __('नाम, फ़ोटो, जगह'), 'm16.86 4.49 1.69-1.69a1.88 1.88 0 1 1 2.65 2.65L6.83 19.82a4.5 4.5 0 0 1-1.9 1.13L2.25 21.75l.8-2.69a4.5 4.5 0 0 1 1.13-1.9L16.86 4.49Z'],
            ['profile.edit', 'पासवर्ड बदलें', __('प्रोफ़ाइल बदलें पेज में नीचे'), 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
            ['notifications.settings', 'सूचना सेटिंग', __('कौन सी सूचना चाहिए'), 'M10.34 3.94c.09-.54.56-.94 1.11-.94h1.1c.55 0 1.02.4 1.11.94l.21 1.28c.07.37.32.68.65.86.07.04.14.08.21.12.34.2.76.25 1.13.1l1.2-.48c.51-.2 1.09 0 1.37.48l.55.95c.27.48.17 1.08-.25 1.43l-1.01.84c-.29.24-.43.61-.4.98a7.5 7.5 0 0 1 0 .28c-.03.37.11.74.4.98l1 .84c.43.35.53.95.26 1.43l-.55.95c-.28.48-.86.68-1.37.48l-1.2-.48c-.37-.15-.8-.1-1.13.1-.07.04-.14.08-.21.12-.33.18-.58.49-.65.86l-.21 1.28c-.09.54-.56.94-1.1.94h-1.1c-.55 0-1.02-.4-1.11-.94l-.21-1.28a1.2 1.2 0 0 0-.65-.86 6 6 0 0 1-.21-.12c-.34-.2-.76-.25-1.13-.1l-1.2.48c-.51.2-1.09 0-1.37-.48l-.55-.95a1.1 1.1 0 0 1 .25-1.43l1.01-.84c.29-.24.43-.61.4-.98a7.5 7.5 0 0 1 0-.28c.03-.37-.11-.74-.4-.98l-1-.84a1.1 1.1 0 0 1-.26-1.43l.55-.95c.28-.48.86-.68 1.37-.48l1.2.48c.37.15.8.1 1.13-.1.07-.04.14-.08.21-.12.33-.18.58-.49.65-.86l.21-1.28Z'],
        ],
        'मदद और गोपनीयता' => [
            ['page.privacy', 'गोपनीयता नीति', __('आपकी जानकारी कैसे सुरक्षित है'), 'M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z'],
            ['page.terms', 'नियम और शर्तें', __('समाज के नियम'), 'M19.5 14.25v-2.63a3.38 3.38 0 0 0-3.38-3.37h-1.5A1.13 1.13 0 0 1 13.5 7.12v-1.5a3.38 3.38 0 0 0-3.38-3.37H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 0 0-9-9Z'],
            ['page.faq', 'मदद चाहिए?', __('अक्सर पूछे जाने वाले सवाल'), 'M9.88 9.88a3 3 0 1 1 4.24 4.24m-4.24-4.24L6.3 6.3m3.58 3.58-3.58 3.58M14.12 14.12 17.7 17.7M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z'],
        ],
    ];
@endphp

<div class="grid gap-5 lg:grid-cols-[22rem_1fr] lg:items-start">

    {{-- ============ LEFT: identity card ============ --}}
    <aside class="space-y-4 lg:sticky lg:top-20">
        <div class="card overflow-hidden">
            <div class="gradient-hero mb-cover relative h-32"></div>
            <div class="-mt-12 px-5 pb-5">
                <div class="relative z-10 w-fit">@include('profile._avatar', ['user' => $user, 'size' => 'h-24 w-24', 'text' => 'text-4xl'])</div>
                <h1 class="mt-3 font-display text-2xl font-bold leading-tight text-ink-900">{{ $user->name }}</h1>
                @if($loc)
                    <p class="mt-1 flex items-start gap-1.5 text-sm text-ink-600">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        {{ $loc }}
                    </p>
                @endif
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="badge-ink">{{ $user->phone }}</span>
                    @if($user->gotra)<span class="badge-warn">{{ $user->gotra }}</span>@endif
                    @if($pending)<span class="badge-warn">{{ __('मंज़ूरी बाकी') }}</span>@else<span class="badge-ok">{{ __('मंज़ूर सदस्य') }}</span>@endif
                </div>

                @unless($pending)
                <div class="mt-4 flex items-center gap-4 rounded-2xl bg-gold-50/70 p-3 ring-1 ring-gold-200/70">
                    <div class="mb-ring shrink-0" style="--p: {{ (int) $completeness }}" role="img" aria-label="{{ __('बायोडाटा कितना पूरा है') }} {{ $completeness }}%"><span>{{ $completeness }}%</span></div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink-900">{{ __('बायोडाटा कितना पूरा है') }}</p>
                        @if($completeness < 100 && $biodatas->isNotEmpty())
                            <p class="mt-0.5 text-xs leading-snug text-ink-600">{{ __('पूरा बायोडाटा = ज़्यादा अच्छे रिश्ते।') }} <a class="font-bold text-kumkum-700 underline" href="{{ route('biodata.mine') }}">{{ __('पूरा करें') }}</a></p>
                        @else
                            <p class="mt-0.5 text-xs text-ink-600">{{ __('आपकी प्रोफ़ाइल तैयार है।') }}</p>
                        @endif
                    </div>
                </div>
                @endunless

                <a href="{{ route('profile.edit') }}" class="btn-primary mt-4 w-full">{{ __('प्रोफ़ाइल बदलें') }}</a>
            </div>
        </div>

        {{-- quick stats --}}
        @unless($pending)
        <div class="grid grid-cols-4 gap-2 lg:grid-cols-2">
            @foreach([[__('पोस्ट'), $stats['posts'], 'from-ink-50 to-white text-ink-800'], [__('बायोडाटा'), $stats['biodatas'], 'from-gold-50 to-white text-gold-600'], [__('भेजी पसंद'), $stats['sent'], 'from-rose-50 to-white text-rose-brand'], [__('मंज़ूर रिश्ते'), $stats['accepted'], 'from-emerald-50 to-white text-emerald-600']] as [$l, $n, $tone])
                <div class="card mb-rise bg-gradient-to-b p-3 text-center {{ $tone }}">
                    <p class="font-display text-2xl font-bold">{{ $n }}</p>
                    <p class="text-sm font-bold text-ink-700">{{ $l }}</p>
                </div>
            @endforeach
        </div>
        @endunless
    </aside>

    {{-- ============ RIGHT: dashboard ============ --}}
    <div class="space-y-5">

        @if($pending)
        <section class="card card-pad relative overflow-hidden">
            <div class="mb-paisley pointer-events-none absolute inset-0"></div>
            <h2 class="relative section-title !text-xl">{{ __('अकाउंट की स्थिति') }}</h2>
            <p class="relative mt-1 text-sm text-ink-600">{{ __('एडमिन की मंज़ूरी बाकी है। मंज़ूरी के बाद आपको पूरा इस्तेमाल मिलेगा।') }}</p>
            <ol class="relative mt-4 space-y-3 text-sm">
                <li class="flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-white" aria-hidden="true">&#10003;</span><span class="font-semibold text-ink-800">{{ __('अकाउंट बन गया') }}</span></li>
                <li class="flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-gold-100 text-gold-600 ring-2 ring-gold-300" aria-hidden="true">&#9203;</span><span class="font-semibold text-ink-800">{{ __('एडमिन जाँच रहे हैं') }}</span></li>
                <li class="flex items-center gap-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-ink-50 text-ink-600 ring-1 ring-ink-100" aria-hidden="true">3</span><span class="font-semibold text-ink-700">{{ __('पूरा इस्तेमाल') }}</span></li>
            </ol>
            <a href="{{ route('account.status') }}" class="btn-ghost relative mt-4">{{ __('स्थिति देखें') }}</a>
        </section>
        @else
        {{-- my biodata --}}
        <section class="card card-pad">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="section-title !text-xl">{{ __('मेरे बायोडाटा') }}</h2>
                <a href="{{ route('biodata.create') }}" class="btn-gold">+ {{ __('नया बायोडाटा') }}</a>
            </div>
            @forelse($biodatas as $b)
                <a href="{{ route('biodata.show', $b) }}" class="mb-row flex items-center gap-3 rounded-2xl p-2.5 transition hover:bg-ink-50">
                    @if($b->primaryPhoto)
                        <img src="{{ route('biodata.photo', ['photo' => $b->primaryPhoto->id, 'size' => 'thumb']) }}" alt="" class="h-14 w-14 shrink-0 rounded-xl object-cover">
                    @else
                        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-neel-400 to-neel-600 font-display text-xl font-bold text-white">{{ mb_strtoupper(mb_substr($b->name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-ink-900">{{ $b->name }}</p>
                        <p class="truncate text-xs text-ink-700">{{ __(':n साल', ['n' => $b->age]) }} · {{ ['male' => __('लड़का'), 'female' => __('लड़की')][$b->gender] ?? ucfirst($b->gender) }} · {{ $b->current_city ?: $b->district }}</p>
                    </div>
                    <span class="{{ $statusChip[$b->status] ?? 'badge-ink' }}">{{ ['approved' => __('मंज़ूर'), 'pending' => __('जाँच बाकी'), 'draft' => __('अधूरा'), 'rejected' => __('अस्वीकृत'), 'paused' => __('रुका हुआ'), 'married' => __('विवाह तय')][$b->status] ?? ucfirst($b->status) }}</span>
                </a>
            @empty
                <div class="relative overflow-hidden rounded-2xl bg-gold-50 p-5 text-center ring-1 ring-gold-200">
                    <div class="mb-paisley pointer-events-none absolute inset-0"></div>
                    <svg class="relative mx-auto mb-2 h-14 w-14" viewBox="0 0 64 64" fill="none" aria-hidden="true"><circle cx="32" cy="32" r="28" fill="#fef3c7"/><circle cx="32" cy="25" r="8" fill="#fff" stroke="#d97706" stroke-width="2.5"/><path d="M16 50c2-9 9-13 16-13s14 4 16 13" stroke="#d97706" stroke-width="2.5" stroke-linecap="round"/></svg>
                    <p class="font-display text-lg font-bold text-ink-900">{{ __('अभी कोई बायोडाटा नहीं है') }}</p>
                    <p class="mt-1 text-base text-ink-700">{{ __('अपना या परिवार के किसी सदस्य का बायोडाटा बनाएँ और रिश्ता खोजना शुरू करें।') }}</p>
                    <a href="{{ route('biodata.create') }}" class="btn-gold mt-3 min-h-12 px-8 text-lg">{{ __('बायोडाटा बनाएँ') }}</a>
                </div>
            @endforelse
        </section>
        @endif

        {{-- menu groups --}}
        @foreach($menu as $title => $items)
            <section class="card overflow-hidden">
                <h2 class="px-5 pt-4 text-base font-bold text-ink-700">{{ __($title) }}</h2>
                <ul class="divide-y divide-ink-50 p-2">
                    @foreach($items as [$route, $label, $hint, $icon])
                        @if(Route::has($route))
                        <li>
                            <a href="{{ route($route) }}" class="mb-row group flex items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-ink-50">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-ink-50 to-ink-100 text-ink-600 ring-1 ring-ink-100 transition group-hover:from-ink-100 group-hover:to-ink-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-base font-bold text-ink-900">{{ __($label) }}</span>
                                    <span class="block text-sm text-ink-700">{{ $hint }}</span>
                                </span>
                                <svg class="h-4 w-4 text-ink-300 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endforeach

        {{-- language --}}
        <section class="card card-pad">
            <h2 class="text-base font-bold text-ink-700">{{ __('भाषा') }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach(['hi' => 'हिन्दी', 'en' => 'English', 'mr' => 'मराठी'] as $code => $label)
                    <a href="{{ route('locale.switch', $code) }}" class="{{ app()->getLocale() === $code ? 'btn-primary' : 'btn-ghost' }} btn-sm">{{ $label }}</a>
                @endforeach
            </div>
        </section>

        {{-- my posts --}}
        @if(! $pending && $recentPosts->isNotEmpty())
        <section class="card card-pad">
            <h2 class="section-title !text-xl mb-3">{{ __('मेरी पोस्ट') }}</h2>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6">
                @foreach($recentPosts as $p)
                    <a href="{{ route('posts.show', $p) }}" class="group relative aspect-square overflow-hidden rounded-xl bg-ink-50 ring-1 ring-ink-100">
                        @if($p->image)
                            <img src="{{ asset('storage/'.$p->image) }}" alt="" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105">
                        @else
                            <span class="grid h-full w-full place-items-center p-2 text-center text-xs leading-snug text-ink-600">{{ \Illuminate\Support\Str::limit($p->body, 60) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- danger zone --}}
        <section class="card card-pad !ring-rose-100">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn-ghost w-full sm:w-auto">{{ __('लॉगआउट') }}</button>
                </form>
                <details class="text-sm" @if($errors->delete->any()) open @endif>
                    <summary class="cursor-pointer list-none font-semibold text-rose-600 hover:underline">{{ __('मेरा अकाउंट हटाएँ') }}</summary>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-3 space-y-2 rounded-2xl bg-rose-50 p-4"
                          onsubmit="return confirm(@json(__('अकाउंट और सारी जानकारी हमेशा के लिए हट जाएगी। क्या आप पक्का हटाना चाहते हैं?')))">
                        @csrf @method('DELETE')
                        <p class="text-base text-rose-800">{{ __('आपकी प्रोफ़ाइल, बायोडाटा, फ़ोटो, पोस्ट और पसंद हमेशा के लिए हटा दी जाएँगी। यह वापस नहीं हो सकता।') }}</p>
                        <input type="password" name="confirm_password" placeholder="{{ __('पक्का करने के लिए पासवर्ड डालें') }}" aria-label="{{ __('पासवर्ड') }}" class="input" autocomplete="current-password" required>
                        @if($errors->delete->any())<p class="field-error">{{ $errors->delete->first('confirm_password') }}</p>@endif
                        <button class="btn-danger">{{ __('हाँ, हटाएँ') }}</button>
                    </form>
                </details>
            </div>
        </section>
    </div>
</div>
@endsection
