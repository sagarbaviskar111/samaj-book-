@extends('layouts.app')
@section('wide', '1')
@section('content')
@php
    $me = auth()->user();
    $first = \Illuminate\Support\Str::before($me->name, ' ');
    $has = fn ($r) => \Illuminate\Support\Facades\Route::has($r);
    $pending = $me->isPending();
    $shortcuts = [
        ['events.index', 'कार्यक्रम', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ['biodata.mine', 'मेरा बायोडाटा', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0'],
        ['interests.index', 'मेरे रिश्ते', 'M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
        ['shortlist.index', 'पसंद की सूची', 'M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38a.56.56 0 0 1-.84.61L12 16.76l-4.73 2.87a.56.56 0 0 1-.84-.61l1.28-5.38-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44 2.13-5.11Z'],
        ['notifications.index', 'सूचनाएँ', 'M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.08 5.46 1.31m5.71 0a24.26 24.26 0 0 1-5.71 0m5.71 0a3 3 0 1 1-5.71 0'],
    ];
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="lg:grid {{ $pending ? 'lg:grid-cols-[17rem_minmax(0,1fr)]' : 'lg:grid-cols-[15rem_minmax(0,1fr)_17rem]' }} lg:items-start lg:gap-6">

    {{-- ============ LEFT (desktop): mini profile + shortcuts ============ --}}
    @if($pending)
    <aside class="space-y-4 lg:sticky lg:top-24">
        <section class="card card-pad relative overflow-hidden" aria-label="{{ __('मंज़ूरी की स्थिति') }}">
            <div class="mb-paisley pointer-events-none absolute inset-0"></div>
            <div class="mb-petals" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <p class="relative font-display text-xl font-bold leading-snug text-ink-900">{{ __('आपका अकाउंट मंज़ूरी का इंतज़ार कर रहा है') }}</p>
            <p class="relative mt-1 text-base text-ink-700">{{ __('एडमिन जाँच रहे हैं कि आप समाज से हैं। तब तक आप सिर्फ़ पोस्ट देख सकते हैं।') }}</p>
            <ol class="relative mt-4 space-y-3 text-base">
                <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-600 text-white" aria-hidden="true">&#10003;</span><span class="font-semibold text-ink-800">{{ __('अकाउंट बन गया') }}</span></li>
                <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-full bg-gold-100 text-gold-600 ring-2 ring-gold-300" aria-hidden="true">&#9203;</span><span class="font-semibold text-ink-800">{{ __('एडमिन जाँच रहे हैं') }}</span></li>
                <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-full bg-ink-50 text-ink-600 ring-1 ring-ink-100" aria-hidden="true">3</span><span class="font-semibold text-ink-700">{{ __('पूरा इस्तेमाल') }}</span></li>
            </ol>
            @if($has('account.status'))<a href="{{ route('account.status') }}" class="btn-ghost relative mt-4 w-full">{{ __('स्थिति देखें') }}</a>@endif
        </section>
    </aside>
    @else
    <aside class="hidden space-y-4 lg:sticky lg:top-24 lg:block">
        <div class="card overflow-hidden">
            <div class="gradient-hero mb-cover h-16"></div>
            <div class="-mt-8 px-4 pb-4 text-center">
                <div class="mx-auto w-fit">@include('profile._avatar', ['user' => $me, 'size' => 'h-16 w-16', 'text' => 'text-2xl'])</div>
                <p class="mt-2 truncate font-display text-lg font-bold text-ink-900">{{ $me->name }}</p>
                <p class="truncate text-sm text-ink-700">{{ $me->city ?: __('समाज के सदस्य') }}</p>
                @if($has('profile.show'))<a href="{{ route('profile.show') }}" class="btn-ghost mt-3 w-full">{{ __('प्रोफ़ाइल देखें') }}</a>@endif
            </div>
        </div>
        <nav class="card p-2" aria-label="{{ __('जल्दी खोलें') }}">
            @foreach($shortcuts as [$r, $label, $icon])
                @if($has($r))
                    <a href="{{ route($r) }}" class="group flex min-h-12 items-center gap-3 rounded-xl px-3 py-2 text-base font-bold text-ink-800 transition hover:bg-ink-50">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-ink-50 text-ink-700 transition group-hover:bg-ink-100"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg></span>
                        {{ __($label) }}
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>
    @endif

    {{-- ============ CENTRE: feed ============ --}}
    <div class="min-w-0 space-y-4">
        @unless($pending)
            @include('feed.partials.quick-actions')
        @endunless

        <section aria-label="{{ __('समाचार') }}">
            <div class="mb-noscroll -mx-4 flex gap-3.5 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0">
                @if($has('posts.create') && ! $pending)
                    <a href="{{ route('posts.create') }}" class="group w-20 shrink-0 text-center">
                        <span class="mb-story-add block rounded-full p-[3px] transition group-active:scale-95">
                            <span class="block rounded-full bg-white p-[2px]">
                                <span class="grid h-[4.1rem] w-[4.1rem] place-items-center rounded-full bg-gold-50 text-gold-600">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </span>
                            </span>
                        </span>
                        <span class="mt-1 block truncate text-sm font-bold text-ink-800">{{ __('आपकी पोस्ट') }}</span>
                    </a>
                @endif
                @foreach($stories as $s)
                    @php($tag = ['news' => __('समाचार'), 'poster' => __('पोस्टर'), 'event' => __('कार्यक्रम')][$s->type] ?? __('समाचार'))
                    <a @if($has('news.show')) href="{{ route('news.show', $s) }}" @endif class="group w-20 shrink-0 text-center">
                        <span class="mb-story block rounded-full p-[3px] transition group-active:scale-95">
                            <span class="block overflow-hidden rounded-full bg-white p-[2px]">
                                @if($s->image)
                                    <img src="{{ asset('storage/'.$s->image) }}" alt="" loading="lazy" class="h-[4.1rem] w-[4.1rem] rounded-full object-cover">
                                @else
                                    <span class="grid h-[4.1rem] w-[4.1rem] place-items-center rounded-full bg-gradient-to-br from-kumkum-700 to-kumkum-900 font-display text-xl font-bold text-gold-300">{{ mb_strtoupper(mb_substr($s->title, 0, 1)) }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="mt-1 block truncate text-sm font-bold text-ink-800">{{ $s->title }}</span>
                        <span class="block text-sm font-semibold text-gold-600">{{ $tag }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- composer --}}
        @unless($pending)
        <div class="card card-pad">
            <a href="{{ route('posts.create') }}" class="flex items-center gap-3">
                @include('feed.partials.avatar', ['u' => $me])
                <span class="min-h-12 flex-1 truncate rounded-full bg-ink-50 px-4 py-3 text-base text-ink-700 ring-1 ring-ink-200 transition hover:bg-ink-100/60">{{ __('समाज के साथ कुछ साझा करें') }}, {{ $first }}...</span>
            </a>
            <div class="mt-3 flex items-center gap-2 border-t border-ink-100 pt-3">
                <a href="{{ route('posts.create') }}" class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl py-2 text-base font-bold text-emerald-800 transition hover:bg-emerald-50">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.25 2.25m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                    {{ __('फ़ोटो डालें') }}
                </a>
                <a href="{{ route('posts.create') }}" class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl py-2 text-base font-bold text-gold-600 transition hover:bg-gold-50">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 1.69-1.69a1.88 1.88 0 1 1 2.65 2.65L6.83 19.82a4.5 4.5 0 0 1-1.9 1.13L2.25 21.75l.8-2.69a4.5 4.5 0 0 1 1.13-1.9L16.86 4.49Z"/></svg>
                    {{ __('कुछ लिखें') }}
                </a>
            </div>
        </div>
        @endunless

        <div id="feed-list" class="space-y-4">
            @forelse($posts as $post)
                @include('feed.partials.post', ['post' => $post])
            @empty
                <div class="card card-pad relative overflow-hidden py-12 text-center">
                    <div class="mb-paisley pointer-events-none absolute inset-0"></div>
            <div class="mb-petals" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <svg class="relative mx-auto h-28 w-28" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                        <circle cx="60" cy="60" r="52" fill="#fbf0e2"/>
                        <rect x="28" y="34" width="64" height="52" rx="10" fill="#fff" stroke="#e6c9a8" stroke-width="2"/>
                        <circle cx="42" cy="48" r="6" fill="#fbbf24"/>
                        <path d="M36 78l14-14 10 10 8-8 16 14" stroke="#d63a34" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M92 28c4 6 1 11-4 11s-6-6 4-11Z" fill="#e11d63"/>
                    </svg>
                    <p class="relative mt-3 font-display text-xl font-bold text-ink-800">{{ __('अभी कोई पोस्ट नहीं है') }}</p>
                    <p class="relative mt-1 text-base text-ink-700">{{ $pending ? __('अभी कोई पोस्ट नहीं है।') : __('समाज के साथ अपना पहला पल साझा करें।') }}</p>
                    @unless($pending)<a href="{{ route('posts.create') }}" class="btn-gold relative mt-5 inline-flex min-h-12 px-8 text-lg">{{ __('पोस्ट करें') }}</a>@endunless
                </div>
            @endforelse
        </div>

        @if($posts->hasMorePages())
            <div class="pb-4 text-center">
                <a href="{{ $posts->nextPageUrl() }}" data-load-more data-next="{{ $posts->nextPageUrl() }}" class="btn-ghost inline-flex min-h-12 px-8">{{ __('और देखें') }}</a>
            </div>
        @endif
    </div>

    {{-- ============ RIGHT (desktop): events + rishta teaser ============ --}}
    @unless($pending)
    <aside class="hidden space-y-4 lg:sticky lg:top-24 lg:block">
        <section class="card card-pad">
            <h2 class="font-display text-lg font-bold text-ink-900">{{ __('आने वाले कार्यक्रम') }}</h2>
            @if(count($upcoming))
                <ul class="mt-3 space-y-3">
                    @foreach($upcoming as $e)
                        @php($ts = $e['at'] ? \Illuminate\Support\Carbon::createFromTimestamp($e['at']) : null)
                        <li>
                            <a @if($has('news.show')) href="{{ route('news.show', $e['id']) }}" @endif class="group flex items-center gap-3">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-gold-100 to-gold-200 text-center leading-none text-gold-600 ring-1 ring-gold-200">
                                    <span><span class="block text-base font-bold">{{ $ts?->format('d') }}</span><span class="block text-xs font-semibold uppercase">{{ $ts?->format('M') }}</span></span>
                                </span>
                                <span class="line-clamp-2 text-base font-semibold text-ink-800 group-hover:text-ink-600">{{ $e['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-base text-ink-700">{{ __('अभी कोई कार्यक्रम तय नहीं है। थोड़ी देर बाद फिर देखें।') }}</p>
            @endif
        </section>

        @if($has('biodata.index'))
            <section class="gradient-hero relative overflow-hidden rounded-[var(--radius-card)] p-5 text-white shadow-glow">
                <div class="mb-cover pointer-events-none absolute inset-0 opacity-70"></div>
                <p class="relative text-sm font-bold text-gold-300">{{ __('रिश्ता') }}</p>
                <p class="relative mt-1 font-display text-xl font-bold leading-snug">{{ __('आपके इलाके के नए बायोडाटा') }}</p>
                <p class="relative mt-1 text-base text-ink-100">{{ __('अपने ही समाज में सुरक्षित तरीके से रिश्ता खोजें।') }}</p>
                <a href="{{ route('biodata.index') }}" class="btn-gold relative mt-4">{{ __('बायोडाटा देखें') }}</a>
            </section>
        @endif
    </aside>
    @endunless
</div>
@endsection
@push('scripts')
    @include('feed.partials.scripts')
@endpush
