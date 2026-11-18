@extends('layouts.base')
{{-- Admin shell: collapsible grouped sidebar on desktop, slide-over + bottom bar on phones. --}}
@php
    $pendingUsers = \App\Services\Admin\AdminScope::users(\App\Models\User::query())->where('status', 'pending')->count();
    $pendingBio = \App\Services\Admin\AdminScope::biodatas(\App\Models\Biodata::query())->where('status', 'pending')->count();
    $openReports = \App\Services\Admin\AdminScope::reports(\App\Models\Report::query())->where('status', 'open')->count();
    $nav = [
        ['admin.dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10', null, 'admin.dashboard'],
        ['admin.users.index', 'Members', 'M15 19a6 6 0 00-12 0M9 11a4 4 0 100-8 4 4 0 000 8zm12 8a6 6 0 00-4-5.6M16 3.4a4 4 0 010 7.2', $pendingUsers, 'admin.users.*'],
        ['admin.biodata.index', 'Biodata', 'M9 12h6M9 16h6M7 3h7l5 5v13H7zM14 3v5h5', $pendingBio, 'admin.biodata.*'],
        ['admin.reports.index', 'Reports', 'M5 21V4m0 0h11l-2 4 2 4H5', $openReports, 'admin.reports.*'],
        ['admin.news.index', 'News & Events', 'M4 5h13v14H6a2 2 0 01-2-2V5zm13 3h3v9a2 2 0 01-2 2M8 9h5M8 13h5', null, 'admin.news.*'],
        ['admin.events.index', 'Events', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1zM9 14h2m2 0h2', null, 'admin.events.*'],
        ['admin.stories.index', 'Success stories', 'M12 21s-7-4.4-9-9a5 5 0 019-3 5 5 0 019 3c-2 4.6-9 9-9 9z', null, 'admin.stories.*'],
        ['admin.settings', 'Settings', 'M12 15a3 3 0 100-6 3 3 0 000 6zm7.4-3a7.4 7.4 0 00-.1-1.2l2-1.5-2-3.4-2.3.9a7.5 7.5 0 00-2-1.2L14.700 3h-4l-.4 2.6a7.5 7.5 0 00-2 1.2l-2.300-.9-2 3.400 2 1.500a7.400 7.400 0 000 2.400l-2 1.500 2 3.400 2.300-.9c.6.500 1.300.9 2 1.200l.4 2.600h4l.4-2.600c.7-.3 1.400-.7 2-1.200l2.300.9 2-3.400-2-1.500c.1-.4.100-.8.100-1.200z', null, 'admin.settings*'],
        ['admin.audit', 'Audit log', 'M12 8v5l3 2M21 12a9 9 0 11-3-6.700L21 8M21 3v5h-5', null, 'admin.audit'],
        ['admin.password', 'My password', 'M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z', null, 'admin.password*'],
    ];
    $isFull = auth()->user()->isFullAdmin();
    $isSuper = auth()->user()->isSuperAdmin();
    $subArea = auth()->user()->admin_area ?: '—';
    $initial = mb_substr(auth()->user()->name, 0, 1);
    if ($isFull) {
        array_splice($nav, 5, 0, [['admin.subadmins.index', 'Sub-admins', 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM19 8v6M22 11h-6', null, 'admin.subadmins.*']]);
    } else {
        $hide = ['admin.stories.index', 'admin.settings', 'admin.audit'];
        $nav = array_values(array_filter($nav, fn ($n) => ! in_array($n[0], $hide, true)));
    }
    if ($isSuper) {
        // SUPERADMIN group (own section, pushed in front of the account link)
        array_splice($nav, 1, 0, [
            ['superadmin.dashboard', 'Superadmin console', 'M12 3l8 3v6c0 4.500-3.400 8-8 9-4.600-1-8-4.500-8-9V6l8-3zM9 12l2 2 4-4', null, 'superadmin.dashboard'],
            ['superadmin.admins.index', 'Admins', 'M16 11a4 4 0 10-8 0M4 20a8 8 0 0116 0', null, 'superadmin.admins.*'],
            ['superadmin.payments.index', 'Payments', 'M3 7h18v10H3zM3 11h18M7 15h3', null, 'superadmin.payments.*'],
            ['superadmin.logs.audit', 'Full logs', 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01', null, 'superadmin.logs.*'],
            ['superadmin.integrations.index', 'Integrations & keys', 'M15 7a4 4 0 11-3.900 4.800L4 19v2h3v-2h2v-2h2l1.500-1.500A4 4 0 0115 7z', null, 'superadmin.integrations.*'],
        ]);
    }
    $groups = ['admin.dashboard' => 'Overview', 'admin.users.index' => 'People', 'admin.reports.index' => 'Moderation', 'admin.news.index' => 'Content', 'admin.settings' => 'System', 'admin.audit' => 'System', 'admin.password' => 'Account'];
    // show each group heading once, above the first visible item of that group
    $seen = [];
    $heads = [];
    foreach ($nav as $n) {
        $g = $groups[$n[0]] ?? null;
        if (! $g && $n[0] === 'admin.biodata.index') { $g = 'People'; }
        if (! $g && $n[0] === 'admin.subadmins.index') { $g = 'People'; }
        if (! $g && str_starts_with($n[0], 'superadmin.')) { $g = 'SUPERADMIN'; }
        if (! $g && $n[0] === 'admin.stories.index') { $g = 'Content'; }
        if ($g && ! isset($seen[$g])) { $seen[$g] = true; $heads[$n[0]] = $g; }
    }
    $bottom = ['admin.dashboard', 'admin.users.index', 'admin.biodata.index', 'admin.reports.index'];
@endphp
@section('body')
<script>try{if(localStorage.getItem('adm-collapsed')==='1')document.documentElement.classList.add('adm-collapsed')}catch(e){}</script>
<div class="adm-shell min-h-screen lg:pl-64">
    <div id="adm-backdrop" class="fixed inset-0 z-40 hidden bg-ink-950/50 backdrop-blur-sm lg:hidden" onclick="admMenu(false)"></div>
    <aside id="adm-menu" class="adm-side fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col adm-sidebar text-white shadow-2xl lg:translate-x-0" aria-label="{{ __('Admin menu') }}">
        <div class="flex items-center justify-between px-5 pb-3 pt-5">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-col gap-1.5">
                <span class="adm-label"><x-brand.logo size="h-9" :wordmark="true" :light="true" /></span>
                <span class="adm-hide-collapsed w-fit rounded-full px-2 py-0.5 font-sans text-[10px] font-bold uppercase tracking-wider {{ $isSuper ? 'bg-kumkum-800 text-gold-300 ring-1 ring-gold-400/60' : ($isFull ? 'bg-gold-400 text-ink-950' : 'bg-tulsi-500 text-white') }}">{{ $isSuper ? 'SUPERADMIN' : ($isFull ? 'ADMIN' : 'SUB-ADMIN · '.$subArea) }}</span>
            </a>
            <button type="button" class="rounded-full p-1.5 hover:bg-white/10 lg:hidden" onclick="admMenu(false)" aria-label="Close menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
            @foreach($nav as [$r, $label, $icon, $badge, $pattern])
                @if(isset($heads[$r]))<p class="adm-hide-collapsed px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-widest text-gold-300/70">{{ __($heads[$r]) }}</p>@endif
                @php($on = request()->routeIs($pattern))
                <a href="{{ route($r) }}" title="{{ __($label) }}" @if($on) aria-current="page" @endif class="adm-nav-link flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $on ? 'bg-cream text-neel-900 shadow-lg' : 'text-white/85 hover:bg-white/10' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    <span class="adm-label flex-1">{{ __($label) }}</span>
                    @if($badge)<span class="adm-label grid h-5 min-w-5 place-items-center rounded-full bg-gold-400 px-1.5 text-[11px] font-bold text-ink-950">{{ $badge }}</span>@endif
                </a>
            @endforeach
        </nav>
        <div class="space-y-2 border-t border-white/10 p-4 text-sm">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gold-400 font-display font-bold text-ink-950">{{ $initial }}</span>
                <span class="adm-label min-w-0 leading-tight"><span class="block truncate font-semibold">{{ auth()->user()->name }}</span><span class="block text-[11px] text-white/70">{{ auth()->user()->roleLabel() }}</span></span>
            </div>
            @if(Route::has('home'))<a href="{{ route('home') }}" class="adm-label block rounded-xl px-3 py-2 text-white/85 hover:bg-white/10">&larr; {{ __('Back to site') }}</a>@endif
            @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="w-full rounded-xl bg-white/10 px-3 py-2 text-left font-semibold hover:bg-white/20" title="{{ __('Log out') }}"><span class="adm-label">{{ __('Log out') }}</span><span class="adm-collapsed-only hidden text-center">&#x23FB;</span></button>
            </form>
            @endif
            <button type="button" onclick="admCollapse()" class="hidden w-full rounded-xl px-3 py-2 text-left text-xs text-white/70 hover:bg-white/10 lg:block"><span class="adm-label">&laquo; {{ __('Collapse menu') }}</span><span class="adm-collapsed-only hidden text-center">&raquo;</span></button>
        </div>
    </aside>

    <header class="adm-topbar sticky top-0 z-30 border-b border-ink-100/60">
        <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-3 lg:px-8">
            <button type="button" class="rounded-xl p-2 hover:bg-sandal lg:hidden" onclick="admMenu(true)" aria-label="Open menu">
                <svg class="h-6 w-6 text-ink-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <p class="hidden truncate text-xs text-ink-500 sm:block"><a href="{{ route('admin.dashboard') }}" class="hover:text-ink-800">{{ __('Admin') }}</a> <span aria-hidden="true">/</span> <span class="text-ink-700">{{ $title ?? 'Admin' }}</span></p>
                <h1 class="truncate font-display text-lg font-bold leading-tight text-ink-900 sm:text-2xl">{{ $title ?? 'Admin' }}</h1>
            </div>
            @unless($isFull)<span class="badge-ink hidden shrink-0 sm:inline-flex">{{ __('Area') }}: {{ $subArea }}</span>@endunless
            <form method="GET" action="{{ route('admin.users.index') }}" class="hidden md:block" role="search">
                <input type="hidden" name="status" value="active">
                <label class="sr-only" for="adm-q">{{ __('Search name or phone') }}</label>
                <input id="adm-q" name="q" type="search" placeholder="{{ __('Search members') }}…" class="input w-56 rounded-full bg-white/80">
            </form>
            <span class="hidden text-sm font-medium text-ink-700 xl:block">{{ auth()->user()->name }}</span>
        </div>
        @unless($isFull)<p class="border-t border-ink-100/60 px-4 py-1.5 text-center text-xs font-semibold text-ink-700 sm:hidden">{{ __('Area') }}: {{ $subArea }}</p>@endunless
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-28 pt-5 lg:px-8 lg:pb-10 lg:pt-7">
        @if(session('status'))<div class="mb-4 flex items-center gap-2 rounded-xl bg-tulsi-500/10 px-4 py-3 text-sm font-medium text-tulsi-600 ring-1 ring-tulsi-500/30" role="status"><span aria-hidden="true">&#10003;</span>{{ session('status') }}</div>@endif
        @if(session('temp_password'))
            <div class="mb-4 rounded-2xl bg-gold-50 p-4 ring-1 ring-gold-200">
                <p class="text-xs font-semibold uppercase tracking-wide text-gold-600">{{ __('Temporary password (shown once, share it privately)') }}</p>
                <p class="mt-1 select-all font-mono text-2xl font-bold tracking-wider text-ink-900">{{ session('temp_password') }}</p>
            </div>
        @endif
        @if($errors->any())<div class="mb-4 rounded-xl bg-kumkum-50 px-4 py-3 text-sm font-medium text-kumkum-700 ring-1 ring-kumkum-200" role="alert">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>

    {{-- Phone bottom bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 glass border-t border-ink-100/60 safe-bottom lg:hidden">
        <div class="mx-auto grid max-w-lg grid-cols-5">
            @foreach($nav as [$r, $label, $icon, $badge, $pattern])
                @continue(! in_array($r, $bottom))
                @php($on = request()->routeIs($pattern))
                <a href="{{ route($r) }}" class="relative flex min-h-12 flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ $on ? 'text-kumkum-700' : 'text-ink-500' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $on ? 2.2 : 1.7 }}"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    {{ __($label) }}
                    @if($badge)<span class="absolute right-[22%] top-1 grid h-4 min-w-4 place-items-center rounded-full bg-kumkum-600 px-1 text-[10px] font-bold text-white">{{ $badge }}</span>@endif
                </a>
            @endforeach
            <button type="button" onclick="admMenu(true)" class="flex min-h-12 flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-ink-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>{{ __('More') }}
            </button>
        </div>
    </nav>
</div>
@endsection
@push('scripts')
<script>
function admMenu(open){
    document.getElementById('adm-menu').classList.toggle('-translate-x-full', !open);
    document.getElementById('adm-backdrop').classList.toggle('hidden', !open);
}
function admCollapse(){
    var on = document.documentElement.classList.toggle('adm-collapsed');
    try{localStorage.setItem('adm-collapsed', on ? '1' : '0')}catch(e){}
}
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') admMenu(false); });
</script>
@endpush
