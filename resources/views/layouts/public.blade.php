@extends('layouts.base')

@push('head')
    <meta name="description" content="{{ $metaDescription ?? __('धोबी (रजक) समाज का अपना, सुरक्षित मैट्रिमोनी और कम्युनिटी प्लेटफ़ॉर्म। सत्यापित बायोडाटा, समाज के लिए मुफ़्त।') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ ($title ?? config('app.name')) }} — {{ config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? __('अपने समाज में, अपनों के बीच जीवनसाथी की तलाश।') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <link rel="canonical" href="{{ url()->current() }}">
    <style>
        .pub-nav a:focus-visible, .pub-foot a:focus-visible, summary:focus-visible, button:focus-visible { outline: 2px solid #f9b81c; outline-offset: 2px; border-radius: .5rem; }
    </style>
@endpush

@section('body')
@php
    $transparent = $transparentNav ?? false;
    $loc = app()->getLocale();
@endphp
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[100] focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:text-sm">{{ __('Skip to content') }}</a>

<header id="pubnav" class="pub-nav fixed inset-x-0 top-0 z-50 transition-all duration-300 {{ $transparent ? 'text-white' : 'border-b border-gold-200/70 bg-cream/90 text-ink-900 shadow-sm backdrop-blur-xl' }}" data-transparent="{{ $transparent ? 1 : 0 }}">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
        <a href="{{ route('landing') }}" class="flex items-center" aria-label="{{ config('app.name') }}">
            <x-brand.logo size="h-10" :wordmark="true" :light="$transparent" class="text-xl" />
        </a>
        <nav class="flex items-center gap-1.5 text-sm font-semibold" aria-label="{{ __('Main') }}">
            @if (Route::has('locale.switch'))
                <span class="mr-1 hidden items-center gap-1 rounded-full {{ $transparent ? 'bg-white/10' : 'bg-sandal' }} px-1 py-0.5 text-xs sm:flex">
                    @foreach (['hi' => 'हि', 'mr' => 'म', 'en' => 'EN'] as $code => $label)
                        <a href="{{ route('locale.switch', $code) }}" class="rounded-full px-2 py-1 {{ $loc === $code ? 'bg-gold-400 text-ink-950' : 'opacity-80 hover:opacity-100' }}">{{ $label }}</a>
                    @endforeach
                </span>
            @endif
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="rounded-full px-3.5 py-2 {{ $transparent ? 'hover:bg-white/10' : 'hover:bg-sandal' }}">{{ __('Login') }}</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-gold btn-sm">{{ __('Register') }}</a>
            @endif
        </nav>
    </div>
</header>

<main id="main" class="{{ $transparent ? '' : 'pt-16' }}">
    @yield('content')
</main>

<footer class="pub-foot hr-hero hr-pattern relative overflow-hidden text-kumkum-100">
    <div class="absolute inset-x-0 top-0 z-[2]" aria-hidden="true"><x-brand.toran :units="14" class="h-12 sm:h-16" /></div>
    <div class="relative mx-auto grid max-w-6xl gap-10 px-4 pb-12 pt-24 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
            <x-brand.logo size="h-11" :wordmark="true" :light="true" :tagline="true" class="text-2xl" />
            <p class="mt-4 max-w-xs text-[15px] leading-relaxed text-kumkum-100/90">{{ __('अपने समाज में, अपनों के बीच। धोबी (रजक) समाज के परिवारों के लिए सुरक्षित मैट्रिमोनी और समाज मंच।') }}</p>
            <p class="mt-3 inline-flex items-center gap-2 text-sm text-gold-200">
                <x-brand.broom-gadga show="both" class="h-6 w-auto text-gold-200" />
                <span class="font-display">{{ __('संत गाडगे महाराज की प्रेरणा से') }}</span>
            </p>
            <div>
                <button type="button" data-install class="btn-gold btn-sm mt-5 min-h-[2.5rem]" hidden>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                    {{ __('Install App') }}
                </button>
            </div>
        </div>
        <div>
            <div class="font-display text-base text-gold-300">{{ __('लिंक') }}</div>
            <ul class="mt-4 space-y-2.5 text-[15px]">
                <li><a class="hover:text-white" href="{{ route('page.about') }}">{{ __('हमारे बारे में') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('page.faq') }}">{{ __('FAQ') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('page.terms') }}">{{ __('नियम और शर्तें') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('page.privacy') }}">{{ __('गोपनीयता नीति') }}</a></li>
            </ul>
        </div>
        <div>
            <div class="font-display text-base text-gold-300">{{ __('जुड़ें') }}</div>
            <ul class="mt-4 space-y-2.5 text-[15px]">
                @if (Route::has('register'))<li><a class="hover:text-white" href="{{ route('register') }}">{{ __('Register') }}</a></li>@endif
                @if (Route::has('login'))<li><a class="hover:text-white" href="{{ route('login') }}">{{ __('Login') }}</a></li>@endif
            </ul>
            <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1.5 text-xs ring-1 ring-gold-300/30">
                <svg viewBox="0 0 24 24" class="h-4 w-4 text-gold-300" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4"/></svg>
                {{ __('Aadhaar not stored') }}
            </div>
        </div>
        <div>
            <div class="font-display text-base text-gold-300">{{ __('भाषा') }}</div>
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                @foreach (['hi' => 'हिन्दी', 'mr' => 'मराठी', 'en' => 'English'] as $code => $label)
                    @if (Route::has('locale.switch'))
                        <a href="{{ route('locale.switch', $code) }}" class="rounded-full border border-gold-300/30 px-3.5 py-1.5 hover:bg-white/10 {{ $loc === $code ? 'bg-white/15 text-white' : '' }}">{{ $label }}</a>
                    @else
                        <span class="rounded-full border border-gold-300/30 px-3 py-1">{{ $label }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <div class="relative border-t border-gold-300/20 py-4 text-center text-xs text-kumkum-100/80 {{ auth()->guest() ? 'pb-24 sm:pb-4' : '' }}">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('समाज की सेवा में, प्रेम के साथ।') }}</div>
</footer>

@guest
@if (Route::has('register'))
<div id="pubcta" class="safe-bottom fixed inset-x-0 bottom-0 z-40 translate-y-full border-t-2 border-gold-400 bg-cream/95 px-3 py-2.5 shadow-[0_-10px_30px_-10px_rgb(112_26_36/.35)] backdrop-blur-xl transition-transform duration-300 sm:hidden">
    <div class="mx-auto flex max-w-md items-center gap-3">
        <p class="min-w-0 flex-1 text-sm font-semibold leading-tight text-ink-900">{{ __('समाज में जुड़ें') }}<span class="block text-xs font-normal text-ink-700">{{ __('Free for samaj members') }}</span></p>
        <a href="{{ route('register') }}" class="btn-primary min-h-[2.75rem] px-5 py-2.5 text-sm">{{ __('Register') }}</a>
    </div>
</div>
<script>
(function () {
    var bar = document.getElementById('pubcta'); if (!bar) return;
    var f = function () { bar.classList.toggle('translate-y-full', window.scrollY < 380); };
    f(); window.addEventListener('scroll', f, {passive: true});
})();
</script>
@endif
@endguest

<script>
(function () {
    var nav = document.getElementById('pubnav');
    if (nav && nav.dataset.transparent === '1') {
        var on = function () {
            var s = window.scrollY > 24;
            nav.classList.toggle('bg-kumkum-950/85', s);
            nav.classList.toggle('backdrop-blur-xl', s);
            nav.classList.toggle('shadow-lg', s);
        };
        on(); window.addEventListener('scroll', on, {passive: true});
    }
    document.querySelectorAll('[data-install]').forEach(function (b) {
        if (window.SamajPWA && window.SamajPWA.install) { b.hidden = false; }
        b.addEventListener('click', function () { window.SamajPWA && window.SamajPWA.install && window.SamajPWA.install(); });
    });
    window.addEventListener('beforeinstallprompt', function () {
        document.querySelectorAll('[data-install]').forEach(function (b) { b.hidden = false; });
    });
})();
</script>
@endsection
