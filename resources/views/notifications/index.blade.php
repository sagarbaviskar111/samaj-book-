@extends('layouts.app')
@section('content')
@php
    $heart = 'M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z';
    $icons = [
        'like' => [$heart, 'bg-gradient-to-br from-rose-100 to-rose-50 text-rose-brand ring-rose-200'],
        'comment' => ['M12 20.25c4.97 0 9-3.69 9-8.25s-4.03-8.25-9-8.25S3 7.44 3 12c0 2.1.86 4.02 2.28 5.47-.2 1.1-.78 2.1-1.53 2.78 1.6-.1 3.07-.74 4.2-1.67 1.2.45 2.5.67 3.75.67Z', 'bg-gradient-to-br from-ink-100 to-ink-50 text-ink-600 ring-ink-200'],
        'interest' => [$heart, 'bg-gradient-to-br from-gold-200 to-gold-100 text-gold-600 ring-gold-200'],
        'new_biodata_area' => ['M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0', 'bg-gradient-to-br from-emerald-100 to-emerald-50 text-emerald-600 ring-emerald-200'],
        'news' => ['M12 9v3.75m0 3.75h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z', 'bg-gradient-to-br from-gold-200 to-gold-100 text-gold-600 ring-gold-200'],
        'system' => ['M11.25 11.25l.04-.02a.75.75 0 0 1 1.06.85l-.7 2.83a.75.75 0 0 0 1.06.85L13 15.75M12 8.25h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'bg-gradient-to-br from-sky-100 to-sky-50 text-sky-600 ring-sky-200'],
    ];
@endphp
<div class="mx-auto max-w-xl space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="section-title">{{ __('सूचनाएँ') }}</h1>
            @if($unread)<p class="text-xs font-semibold text-rose-brand">{{ $unread }} {{ __('नई') }}</p>@endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('notifications.settings') }}" class="btn-ghost" aria-label="{{ __('सूचना सेटिंग') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                {{ __('सेटिंग') }}
            </a>
            @if($unread)
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-ghost">{{ __('सब पढ़ा हुआ करें') }}</button></form>
            @endif
        </div>
    </div>

    <div id="push-cta" class="card card-pad relative hidden flex items-center gap-3 overflow-hidden !bg-gradient-to-r from-kumkum-900 to-kumkum-600 text-white">
        <span class="pointer-events-none absolute -right-6 -top-8 h-24 w-24 rounded-full bg-gold-400/25 blur-2xl"></span>
        <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-gold-300"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.08 5.46 1.31m5.71 0a24.26 24.26 0 0 1-5.71 0m5.71 0a3 3 0 1 1-5.71 0"/></svg></span>
        <div class="relative flex-1">
            <p class="text-sm font-semibold">{{ __('फ़ोन पर सूचना पाएँ') }}</p>
            <p class="text-xs text-ink-100">{{ __('ऐप बंद होने पर भी लाइक, कमेंट और रिश्ते की खबर मिलेगी।') }}</p>
        </div>
        <button type="button" data-enable-push class="btn-gold relative shrink-0">{{ __('सूचना चालू करें') }}</button>
    </div>

    @foreach([__('आज') => $today, __('पहले की') => $earlier] as $label => $group)
        @if($group->isNotEmpty())
            <section>
                <h2 class="mb-2 flex items-center gap-2 px-1 text-base font-bold text-ink-700">{{ $label }}<span class="h-px flex-1 bg-ink-100"></span></h2>
                <div class="space-y-2">
                    @foreach($group as $n)
                        @php($g = \App\Services\NotificationPrefs::groupOf($n->data['type'] ?? null))
                        @php($ic = $icons[$g] ?? $icons['system'])
                        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                            @csrf
                            <button class="mb-row card flex w-full items-start gap-3 px-4 py-3.5 text-left hover:shadow-lg {{ $n->read_at ? '' : '!bg-gradient-to-r from-gold-50 to-white ring-gold-200' }}">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full ring-1 {{ $ic[1] }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic[0] }}"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-base leading-snug {{ $n->read_at ? 'text-ink-600' : 'font-semibold text-ink-900' }}">{{ $n->data['title'] ?? '' }}</span>
                                    @if(!empty($n->data['body']))<span class="mt-0.5 block truncate text-sm text-ink-700">{{ $n->data['body'] }}</span>@endif
                                    <span class="mt-1 block text-sm text-ink-600">{{ $n->created_at->diffForHumans() }}</span>
                                </span>
                                @unless($n->read_at)<span class="mb-badge mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-rose-brand" aria-label="{{ __('नई') }}"></span>@endunless
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if($items->isEmpty())
        <div class="card card-pad relative overflow-hidden py-12 text-center">
            <div class="mb-paisley pointer-events-none absolute inset-0"></div>
            <svg class="relative mx-auto h-28 w-28" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                <circle cx="60" cy="60" r="52" fill="#fbf0e2"/>
                <path d="M60 28c-14 0-22 10-22 24v10l-6 10h56l-6-10V52c0-14-8-24-22-24Z" fill="#fff" stroke="#d63a34" stroke-width="3" stroke-linejoin="round"/>
                <path d="M52 78a8 8 0 0 0 16 0" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
                <circle cx="86" cy="34" r="6" fill="#e11d63"/>
            </svg>
            <p class="relative mt-3 font-display text-xl font-bold text-ink-800">{{ __('अभी कोई सूचना नहीं है') }}</p>
            <p class="relative mt-1 text-sm text-ink-700">{{ __('लाइक, कमेंट और रिश्ते की खबरें यहाँ दिखेंगी।') }}</p>
            <a href="{{ route('home') }}" class="btn-primary relative mt-5 min-h-12 px-8 text-lg">{{ __('होम पर जाएँ') }}</a>
        </div>
    @endif

    {{ $items->links() }}
</div>
@endsection
@push('scripts')
<script>window.SamajVapidPublic = @json($vapidPublic);</script>
@endpush
