@extends('layouts.base')
{{-- Member shell.
     Phone  : top bar + bottom navigation (always labelled, Instagram-like).
     Desktop: full top navigation bar with labelled links + profile menu.
     Pages can opt into a wider canvas with @section('wide', '1'). --}}
@section('body')
@php
    $u = auth()->user();
    $unread = $u?->unreadNotifications()->count() ?? 0;
    $wide = trim($__env->yieldContent('wide')) !== '';
    $pending = (bool) $u?->isPending();
    // requests waiting for this member's answer (cached 60s)
    $reqWaiting = ($u && ! $pending)
        ? \Illuminate\Support\Facades\Cache::remember('mb.reqwait.'.$u->id, 60, fn () => \App\Models\Interest::where('to_user_id', $u->id)->where('status', 'sent')->count())
        : 0;
    $links = [
        ['route' => 'home',            'label' => 'होम',           'match' => 'home'],
        ['route' => 'biodata.index',   'label' => 'रिश्ता खोजें',   'match' => 'biodata.index'],
        ['route' => 'interests.index', 'label' => 'मेरे रिश्ते',     'match' => 'interests.*', 'badge' => $reqWaiting],
        ['route' => 'shortlist.index', 'label' => 'पसंद की सूची',   'match' => 'shortlist.*'],
        ['route' => 'biodata.mine',    'label' => 'मेरा बायोडाटा',   'match' => 'biodata.mine'],
        ['route' => 'events.index',    'label' => 'कार्यक्रम',       'match' => 'events.*'],
    ];
    if ($pending) { $links = [$links[0]]; }
    $menuLink = 'flex items-center gap-3 rounded-xl px-3 py-3 text-base font-semibold text-ink-800 transition hover:bg-sandal/60';
    $ico = fn ($d) => '<svg class="h-5 w-5 shrink-0 text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
    $contactPhone = \App\Models\Setting::get('contact_phone');
    $contactDigits = preg_replace('/[^0-9+]/', '', (string) $contactPhone);
@endphp
<div class="min-h-screen pb-32 lg:pb-12">
    <header class="mb-header sticky top-0 z-30">
        <div class="mx-auto flex max-w-2xl items-center justify-between gap-2 px-4 py-2.5 sm:gap-4 lg:max-w-6xl lg:gap-3">
            <a href="{{ route('home') }}" class="flex min-h-11 shrink-0 items-center" aria-label="SamajBook"><x-brand.logo class="mb-logo" size="h-9" :wordmark="true" /></a>

            {{-- desktop navigation --}}
            <nav class="hidden flex-1 items-center justify-center gap-0.5 lg:flex" aria-label="Main">
                @foreach($links as $l)
                    @if(Route::has($l['route']))
                        @php($on = request()->routeIs($l['match']))
                        <a href="{{ route($l['route']) }}" @if($on) aria-current="page" @endif
                           class="relative inline-flex min-h-11 items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-2 text-[0.9375rem] font-bold transition xl:px-3.5 {{ $on ? 'bg-kumkum-700 text-white' : 'text-ink-800 hover:bg-sandal/70' }}">{{ __($l['label']) }}@if(!empty($l['badge']))<span class="grid h-5 min-w-5 place-items-center rounded-full bg-rose-brand px-1 text-xs font-bold leading-none text-white">{{ $l['badge'] > 99 ? '99+' : $l['badge'] }}</span>@endif</a>
                    @endif
                @endforeach
            </nav>

            <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
                @include('partials.text-size')
                @if(Route::has('posts.create') && ! $pending)
                    <a href="{{ route('posts.create') }}" class="btn-gold hidden whitespace-nowrap lg:inline-flex">+ {{ __('पोस्ट करें') }}</a>
                @endif
                <a href="{{ route('notifications.index') }}" class="relative flex min-h-11 min-w-11 items-center justify-center gap-1.5 rounded-full px-1 transition hover:bg-sandal/60 active:scale-95 lg:px-3 {{ request()->routeIs('notifications.*') ? 'bg-kumkum-50 ring-1 ring-kumkum-200' : '' }}" aria-label="{{ __('सूचनाएँ') }}{{ $unread ? ' ('.$unread.' '.__('नई').')' : '' }}">
                    <svg class="h-6 w-6 text-ink-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    <span class="hidden text-[0.9375rem] font-bold text-ink-800 lg:inline">{{ __('सूचनाएँ') }}</span>
                    @if($unread)<span class="mb-badge absolute right-0 top-0 grid h-5 min-w-5 place-items-center rounded-full bg-rose-brand px-1 text-xs font-bold leading-none text-white ring-2 ring-cream">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
                </a>

                {{-- desktop profile menu (no JS needed: <details>) --}}
                @if($u && Route::has('profile.show'))
                <details class="relative hidden lg:block">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full py-1 pl-1 pr-3 transition hover:bg-sandal/60" aria-label="{{ __('मेरा मेन्यू') }}">
                        @include('profile._avatar', ['user' => $u, 'size' => 'h-9 w-9', 'text' => 'text-sm', 'ring' => 'ring-2'])
                        <span class="max-w-[8rem] truncate text-[0.9375rem] font-bold text-ink-800">{{ \Illuminate\Support\Str::before($u->name, ' ') }}</span>
                        <svg class="h-4 w-4 text-ink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="mb-rise absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-ink-100">
                        <a href="{{ route('profile.show') }}" class="{{ $menuLink }}">{!! $ico('M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0') !!}{{ __('मेरी प्रोफ़ाइल') }}</a>
                        <a href="{{ route('profile.edit') }}" class="{{ $menuLink }}">{!! $ico('m16.86 4.49 1.69-1.69a1.88 1.88 0 1 1 2.65 2.65L6.83 19.82a4.5 4.5 0 0 1-1.9 1.13L2.25 21.75l.8-2.69a4.5 4.5 0 0 1 1.13-1.9L16.86 4.49Z') !!}{{ __('प्रोफ़ाइल बदलें') }}</a>
                        @if(Route::has('notifications.settings') && ! $pending)<a href="{{ route('notifications.settings') }}" class="{{ $menuLink }}">{!! $ico('M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0') !!}{{ __('सूचना सेटिंग') }}</a>@endif
                        @if(Route::has('page.faq'))<a href="{{ route('page.faq') }}" class="{{ $menuLink }}">{!! $ico('M9.88 9.88a3 3 0 1 1 4.24 4.24M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-5.25h.01') !!}{{ __('मदद चाहिए?') }}</a>@endif
                        @if($u->isAdmin() && Route::has('admin.dashboard'))<a href="{{ route('admin.dashboard') }}" class="{{ $menuLink }}">{!! $ico('M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z') !!}{{ __('एडमिन पैनल') }}</a>@endif
                        @if($u->isSuperAdmin() && Route::has('superadmin.dashboard'))<a href="{{ route('superadmin.dashboard') }}" class="{{ $menuLink }}">{!! $ico('M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5') !!}{{ __('सुपरएडमिन कंसोल') }}</a>@endif
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-base font-semibold text-rose-700 transition hover:bg-rose-50"><svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>{{ __('लॉगआउट') }}</button>
                        </form>
                    </div>
                </details>
                @endif
            </div>
        </div>
    </header>

    @if($pending)
    <div class="border-b border-gold-200 bg-gold-50" role="status">
        <div class="mx-auto flex max-w-2xl items-start gap-2 px-4 py-3 text-base font-semibold text-ink-900 lg:max-w-6xl">
            <span aria-hidden="true">&#9203;</span>
            <p class="min-w-0 flex-1 leading-snug">{{ __('एडमिन की मंज़ूरी बाकी है। मंज़ूरी के बाद आप रिश्ते, बायोडाटा और कार्यक्रम देख पाएँगे।') }}
                @if(Route::has('account.status'))<a href="{{ route('account.status') }}" class="whitespace-nowrap underline">{{ __('स्थिति देखें') }}</a>@endif</p>
        </div>
    </div>
    @endif

    <main class="mx-auto max-w-2xl px-4 pt-4 {{ $wide ? 'lg:max-w-6xl lg:pt-8' : 'lg:max-w-4xl lg:pt-6' }}">
        @if(session('status'))<div class="mb-4 flex items-start gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-base font-semibold text-emerald-900 ring-1 ring-emerald-300" role="status"><svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg><span>{{ session('status') }}</span></div>@endif
        @if(session('pending_notice'))<div class="mb-4 flex items-start gap-3 rounded-2xl bg-gold-50 px-4 py-3 text-base font-semibold text-ink-900 ring-1 ring-gold-300" role="status"><svg class="mt-0.5 h-6 w-6 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg><span>{{ session('pending_notice') }} &mdash; {{ __('एडमिन की मंज़ूरी के बाद यह भाग खुलेगा।') }}</span></div>@endif
        @if(session('error'))<div class="mb-4 flex items-start gap-3 rounded-2xl bg-rose-50 px-4 py-3 text-base font-semibold text-rose-900 ring-1 ring-rose-300" role="alert"><svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg><span>{{ session('error') }}</span></div>@endif
        @yield('content')

        {{-- help footer --}}
        <footer class="mt-10 rounded-2xl bg-white/70 px-4 py-4 text-center ring-1 ring-sandal">
            @if(Route::has('page.faq'))
                <a href="{{ route('page.faq') }}" class="btn-ghost w-full sm:w-auto"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a3 3 0 1 1 4.24 4.24M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-5.25h.01"/></svg>{{ __('मदद चाहिए?') }}</a>
            @endif
            @if($contactPhone)
                <p class="mt-3 text-base text-ink-800">{{ __('फ़ोन या व्हाट्सऐप पर एडमिन से बात करें') }}:
                    <a href="tel:{{ $contactDigits }}" class="whitespace-nowrap font-bold text-kumkum-700 underline">{{ $contactPhone }}</a></p>
            @endif
        </footer>
    </main>
</div>

@include('partials.bottom-nav')
@unless($pending)<x-event-popup />@endunless
@endsection
