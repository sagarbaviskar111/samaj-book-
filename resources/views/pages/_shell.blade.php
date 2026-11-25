@extends('layouts.public')

@section('content')
<section class="hr-hero hr-pattern relative overflow-hidden px-4 pb-20 pt-0 text-center text-white sm:pb-24">
    <div class="relative z-[2] -mx-4" aria-hidden="true"><x-brand.toran :units="12" class="h-9 sm:h-12" /></div>
    <svg viewBox="-50 -50 100 100" class="pub-spin-slow absolute -right-16 -top-16 h-64 w-64 text-gold-400/15" fill="currentColor" aria-hidden="true">@foreach (range(0, 11) as $k)<ellipse cx="0" cy="-30" rx="9" ry="19" transform="rotate({{ $k * 30 }})"/>@endforeach<circle r="16"/></svg>
    <div class="relative z-[2] mx-auto mt-6 max-w-3xl">
        <p class="font-brand text-sm tracking-wide text-gold-300">॥ श्री गणेशाय नमः ॥</p>
        <h1 class="hr-brand mt-2 text-3xl sm:text-5xl">@yield('heading')</h1>
        <p class="mx-auto mt-3 max-w-xl text-base text-kumkum-100/90">@yield('sub')</p>
        <x-brand.rangoli class="mt-5 text-gold-300" />
    </div>
</section>

<div class="mx-auto -mt-10 grid max-w-5xl gap-6 px-4 pb-20 lg:grid-cols-[14rem_1fr] lg:gap-10">
    <aside class="hidden lg:block">
        <div class="sticky top-24 space-y-5">
            <nav class="pub-toc" aria-label="{{ __('विषय सूची') }}">
                <p class="mb-2 text-sm font-bold text-ink-800">{{ __('इस पेज पर') }}</p>
                <ul id="pubtoc" class="space-y-1 border-l border-ink-100 text-sm"></ul>
            </nav>
            <div class="rounded-2xl bg-white p-3 text-sm shadow-soft ring-1 ring-gold-200">
                <p class="mb-1 text-sm font-bold text-ink-800">{{ __('और पन्ने') }}</p>
                <ul class="space-y-1">
                    <li><a class="block rounded-lg px-2 py-2.5 text-base hover:bg-ink-50" href="{{ route('page.about') }}">{{ __('हमारे बारे में') }}</a></li>
                    <li><a class="block rounded-lg px-2 py-2.5 text-base hover:bg-ink-50" href="{{ route('page.faq') }}">{{ __('मदद चाहिए?') }}</a></li>
                    <li><a class="block rounded-lg px-2 py-2.5 text-base hover:bg-ink-50" href="{{ route('page.terms') }}">{{ __('नियम और शर्तें') }}</a></li>
                    <li><a class="block rounded-lg px-2 py-2.5 text-base hover:bg-ink-50" href="{{ route('page.privacy') }}">{{ __('गोपनीयता नीति') }}</a></li>
                </ul>
            </div>
        </div>
    </aside>

    <article class="min-w-0">
        <div id="pubarticle" class="pub-prose card space-y-8 p-5 text-base leading-[1.8] text-ink-900/85 sm:p-9 [&_h2]:mb-3 [&_h2]:border-l-4 [&_h2]:border-kumkum-600 [&_h2]:pl-3 [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:leading-[1.6] [&_h2]:text-kumkum-800 sm:[&_h2]:text-2xl [&_li]:mt-1.5 [&_ul]:list-disc [&_ul]:pl-5 [&_a]:font-semibold [&_a]:text-kumkum-700 [&_a]:underline [&_strong]:text-ink-900">
            @yield('page')
        </div>
        <p class="mt-6 text-center text-sm text-ink-700"><a href="{{ route('landing') }}" class="font-semibold text-kumkum-700 underline">&larr; {{ __('होम पेज') }}</a></p>
    </article>
</div>

<script>
(function () {
    var list = document.getElementById('pubtoc'), art = document.getElementById('pubarticle');
    if (!list || !art) return;
    var hs = art.querySelectorAll('h2');
    if (!hs.length) { list.closest('nav').hidden = true; return; }
    var links = [];
    hs.forEach(function (h, i) {
        if (!h.id) h.id = 'sec-' + (i + 1);
        var li = document.createElement('li'), a = document.createElement('a');
        a.href = '#' + h.id; a.textContent = h.textContent;
        a.className = '-ml-px block border-l-2 border-transparent py-1 pl-3 text-ink-800 hover:text-kumkum-700';
        li.appendChild(a); list.appendChild(li); links.push(a);
    });
    if (!('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
            if (e.isIntersecting) {
                links.forEach(function (l) { l.removeAttribute('aria-current'); });
                var l = list.querySelector('a[href="#' + e.target.id + '"]'); if (l) l.setAttribute('aria-current', 'true');
            }
        });
    }, {rootMargin: '-15% 0px -70% 0px'});
    hs.forEach(function (h) { io.observe(h); });
})();
</script>
@endsection
