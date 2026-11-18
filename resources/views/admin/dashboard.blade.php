@extends('layouts.admin')
@php $title = __('Dashboard'); @endphp
@section('content')
@unless(auth()->user()->isFullAdmin())
    <p class="mb-3"><span class="badge-ink">{{ __('Area') }}: {{ auth()->user()->admin_area ?: '—' }}</span></p>
@endunless
@php
    $icons = [
        'users' => 'M15 19a6 6 0 00-12 0M9 11a4 4 0 100-8 4 4 0 000 8zm12 8a6 6 0 00-4-5.6M16 3.4a4 4 0 010 7.2',
        'clock' => 'M12 8v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'doc' => 'M9 12h6M9 16h6M7 3h7l5 5v13H7zM14 3v5h5',
        'check' => 'M5 13l4 4L19 7',
        'flag' => 'M5 21V4m0 0h11l-2 4 2 4H5',
        'heart' => 'M12 21s-7-4.4-9-9a5 5 0 019-3 5 5 0 019 3c-2 4.6-9 9-9 9z',
        'chat' => 'M4 5h16v11H9l-5 4z',
    ];
    $cards = [
        ['Members', $stats['members'], 'admin.users.index', ['status' => 'active'], 'neel', 'users'],
        ['Pending members', $stats['pending_members'], 'admin.users.index', ['status' => 'pending'], $stats['pending_members'] ? 'kumkum' : 'neel', 'clock'],
        ['Pending biodata', $stats['pending_biodata'], 'admin.biodata.index', ['status' => 'pending'], $stats['pending_biodata'] ? 'kumkum' : 'neel', 'doc'],
        ['Approved biodata', $stats['approved_biodata'], 'admin.biodata.index', ['status' => 'approved'], 'green', 'check'],
        ['Open reports', $stats['open_reports'], 'admin.reports.index', [], $stats['open_reports'] ? 'kumkum' : 'neel', 'flag'],
        ['Interests this month', $stats['interests_sent'], null, [], 'gold', 'heart'],
        ['Accepted this month', $stats['interests_accepted'], null, [], 'green', 'check'],
        ['Posts this week', $stats['posts_week'], null, [], 'neel', 'chat'],
    ];
    $tint = [
        'neel' => ['bg-gradient-to-br from-neel-50 to-white', 'bg-neel-100 text-neel-700', 'text-neel-800', '#2f5d8c'],
        'gold' => ['bg-gradient-to-br from-gold-50 to-white ring-gold-300', 'bg-gold-100 text-gold-600', 'text-gold-600', '#ee9e0a'],
        'kumkum' => ['bg-gradient-to-br from-kumkum-50 to-white ring-kumkum-200', 'bg-kumkum-100 text-kumkum-700', 'text-kumkum-700', '#b8262b'],
        'green' => ['bg-gradient-to-br from-tulsi-500/10 to-white', 'bg-tulsi-500/15 text-tulsi-600', 'text-tulsi-600', '#2f7d57'],
    ];
    $counts = $series->pluck('count')->values();
    $max = max(1, $counts->max());
    $n = max(1, $counts->count());
    $half = (int) floor($n / 2);
    $recent = $counts->slice($half)->sum();
    $prev = $counts->slice(0, $half)->sum();
    $delta = $prev > 0 ? round(($recent - $prev) / $prev * 100) : ($recent > 0 ? 100 : 0);
    // SVG chart geometry
    $W = 600; $H = 180; $pad = 8; $step = ($W - $pad * 2) / max(1, $n - 1);
    $pts = [];
    foreach ($counts as $i => $c) { $pts[] = [round($pad + $i * $step, 1), round($H - 10 - ($c / $max) * ($H - 30), 1)]; }
    $line = collect($pts)->map(fn ($p) => $p[0].','.$p[1])->implode(' ');
    $area = $line.' '.end($pts)[0].','.$H.' '.$pts[0][0].','.$H;
    $queues = [
        ['Pending members', $stats['pending_members'], 'admin.users.index', ['status' => 'pending'], 'Review pending members', 'clock'],
        ['Pending biodata', $stats['pending_biodata'], 'admin.biodata.index', ['status' => 'pending'], 'Review pending biodata', 'doc'],
        ['Open reports', $stats['open_reports'], 'admin.reports.index', [], 'Open reports', 'flag'],
    ];
@endphp

{{-- Needs your attention --}}
<section aria-labelledby="attn" class="mb-6">
    <h2 id="attn" class="mb-3 text-lg font-bold text-ink-900">{{ __('Needs your attention') }}</h2>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach($queues as [$label, $count, $route, $params, $cta, $ic])
            <div class="card relative overflow-hidden p-4 {{ $count ? 'ring-kumkum-200' : '' }}">
                <span class="absolute -right-6 -top-6 h-24 w-24 rounded-full {{ $count ? 'bg-kumkum-100' : 'bg-sandal' }}"></span>
                <div class="relative flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl {{ $count ? 'bg-kumkum-600 text-white' : 'bg-ink-100 text-ink-600' }}"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$ic] }}"/></svg></span>
                    <div><p class="font-display text-3xl font-bold leading-none text-ink-900">{{ number_format($count) }}</p><p class="mt-1 text-xs font-semibold uppercase tracking-wide text-ink-600">{{ __($label) }}</p></div>
                </div>
                <a href="{{ route($route, $params) }}" class="{{ $count ? 'btn-primary' : 'btn-ghost' }} relative mt-4 w-full min-h-11">{{ $count ? __($cta) : __('All clear') }}</a>
            </div>
        @endforeach
    </div>
</section>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach($cards as [$label, $value, $route, $params, $t, $ic])
        @php($tag = $route ? 'a' : 'div')
        <{{ $tag }} @if($route) href="{{ route($route, $params) }}" @endif class="card relative overflow-hidden p-4 {{ $tint[$t][0] }} {{ $route ? 'transition hover:-translate-y-0.5 hover:shadow-glow focus-visible:ring-2 focus-visible:ring-kumkum-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="grid h-9 w-9 place-items-center rounded-xl {{ $tint[$t][1] }}"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$ic] }}"/></svg></span>
                <svg class="h-6 w-14" viewBox="0 0 56 24" fill="none" aria-hidden="true"><polyline points="0,18 10,14 20,16 30,8 40,11 56,3" stroke="{{ $tint[$t][3] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".55"/></svg>
            </div>
            <p class="mt-3 font-display text-3xl font-bold {{ $tint[$t][2] }}">{{ number_format($value) }}</p>
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-600">{{ __($label) }}</p>
        </{{ $tag }}>
    @endforeach
</div>

<div class="mt-6 grid gap-5 lg:grid-cols-5">
    <section class="card-pad lg:col-span-3">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-lg font-bold text-ink-900">{{ __('Registrations, last 14 days') }}</h2>
            <div class="flex items-center gap-2">
                <span class="{{ $delta >= 0 ? 'badge-ok' : 'badge-bad' }}">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%</span>
                <span class="badge-ink">{{ $series->sum('count') }} {{ __('total') }}</span>
            </div>
        </div>
        <div class="mt-4" role="img" aria-label="Registrations per day">
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="h-44 w-full overflow-visible" preserveAspectRatio="none">
                <defs><linearGradient id="admgrad" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#f9b81c" stop-opacity=".55"/><stop offset="1" stop-color="#f9b81c" stop-opacity="0"/></linearGradient></defs>
                @foreach([0.25, 0.5, 0.75] as $g)<line x1="0" x2="{{ $W }}" y1="{{ round($H * $g) }}" y2="{{ round($H * $g) }}" stroke="#e6d3c4" stroke-dasharray="4 5"/>@endforeach
                <polygon points="{{ $area }}" fill="url(#admgrad)"/>
                <polyline points="{{ $line }}" fill="none" stroke="#b8262b" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                @foreach($series as $i => $d)
                    <g class="adm-col" tabindex="0">
                        <rect x="{{ round($pts[$i][0] - $step / 2, 1) }}" y="0" width="{{ round($step, 1) }}" height="{{ $H }}" fill="transparent"><title>{{ $d['label'] }}: {{ $d['count'] }}</title></rect>
                        <circle cx="{{ $pts[$i][0] }}" cy="{{ $pts[$i][1] }}" r="{{ $d['count'] ? 4 : 3 }}" fill="#fff" stroke="#b8262b" stroke-width="2.5"/>
                    </g>
                @endforeach
            </svg>
            <div class="mt-1 flex">
                @foreach($series as $d)<span class="flex-1 text-center text-[10px] text-ink-500"><span class="block font-semibold text-ink-700">{{ $d['count'] ?: '' }}</span>{{ $d['short'] }}</span>@endforeach
            </div>
        </div>
    </section>

    <section class="card-pad lg:col-span-2">
        <h2 class="text-lg font-bold text-ink-900">{{ __('Quick actions') }}</h2>
        <div class="mt-3 grid gap-2">
            <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="btn-primary min-h-11 justify-between">{{ __('Review pending members') }} <span>{{ $stats['pending_members'] }}</span></a>
            <a href="{{ route('admin.biodata.index', ['status' => 'pending']) }}" class="btn-primary min-h-11 justify-between">{{ __('Review pending biodata') }} <span>{{ $stats['pending_biodata'] }}</span></a>
            <a href="{{ route('admin.reports.index') }}" class="btn-ghost min-h-11 justify-between">{{ __('Open reports') }} <span>{{ $stats['open_reports'] }}</span></a>
            <a href="{{ route('admin.news.create') }}" class="btn-gold min-h-11">{{ __('Post news / event') }}</a>
        </div>
    </section>
</div>

<section class="card-pad mt-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-ink-900">{{ __('Recent activity') }}</h2>
@if(auth()->user()->isFullAdmin())        <a href="{{ route('admin.audit') }}" class="text-xs font-semibold text-ink-700 hover:underline">{{ __('See all') }}</a>@endif
    </div>
    <ul class="mt-4 space-y-4">
        @forelse($activity as $a)
            <li class="adm-tl">
                <span class="adm-tl-dot"></span>
                <div class="flex items-start justify-between gap-3 text-sm">
                    <div class="min-w-0">
                        <span class="badge-ink">{{ $a->action }}</span>
                        <span class="ml-1 break-words text-ink-700">{{ \Illuminate\Support\Str::limit($a->note, 60) }}</span>
                    </div>
                    <span class="shrink-0 text-xs text-ink-500">{{ $a->created_at->diffForHumans(short: true) }}</span>
                </div>
            </li>
        @empty
            <li class="py-8 text-center text-sm text-ink-500">{{ __('No activity yet.') }}</li>
        @endforelse
    </ul>
</section>
@endsection
