@extends('layouts.admin')
@php
    $title = __('Superadmin console');
    $matrix = [
        // [capability, superadmin, admin, subadmin]
        ['Members, biodata, reports, news, events (own scope)', 'All areas', 'All areas', 'Own area'],
        ['Block / delete members, verify biodata, settings, stories', 'Yes', 'Yes', 'No'],
        ['Create and manage sub-admins', 'Yes', 'Yes', 'No'],
        ['Create and manage admins', 'Yes', 'No', 'No'],
        ['Payments (list, manual entry, refunds, export)', 'Yes', 'No', 'No'],
        ['Integrations, payment settings and API keys', 'Yes', 'No', 'No'],
        ['Login logs and security events', 'Yes', 'No', 'No'],
        ['Audit log', 'Everything', 'Admin and sub-admin actions only', 'No'],
        ['Full 360° user profile (hidden fields, payments, logins)', 'Yes', 'No', 'No'],
    ];
@endphp
@section('content')
<div class="adm-band mb-5 flex items-center gap-3 rounded-2xl px-5 py-4 text-white">
    <svg class="h-7 w-7 shrink-0 text-gold-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 4.500-3.400 8-8 9-4.600-1-8-4.500-8-9V6l8-3zM9 12l2 2 4-4"/></svg>
    <div><p class="font-display text-lg font-bold leading-tight">{{ __('Superadmin console') }}</p><p class="text-xs text-gold-200">{{ __('Every action here is logged.') }}</p></div>
</div>
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach([
        [__('Admins'), $stats['admins'].($stats['admins_blocked'] ? ' ('.$stats['admins_blocked'].' blocked)' : ''), route('superadmin.admins.index')],
        [__('Payments this month'), \App\Models\Payment::inr($stats['paid_month']), route('superadmin.payments.index', ['status' => 'paid'])],
        [__('Failed logins (24h)'), $stats['failed_24h'], route('superadmin.logs.security')],
        [__('Members'), $stats['members'].' · '.$stats['subadmins'].' '.__('sub-admins'), route('admin.users.index', ['status' => 'active'])],
    ] as [$label, $value, $href])
        <a href="{{ $href }}" class="card-pad block border-t-4 border-gold-400 transition hover:-translate-y-0.5 hover:shadow-glow">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ $label }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ $value }}</p>
        </a>
    @endforeach
</div>

<div class="mt-5 flex flex-wrap gap-2">
    <a href="{{ route('superadmin.admins.create') }}" class="btn-gold">+ {{ __('New admin') }}</a>
    <a href="{{ route('superadmin.payments.create') }}" class="btn-ghost">+ {{ __('Record payment') }}</a>
    <a href="{{ route('superadmin.logs.audit') }}" class="btn-ghost">{{ __('Full logs') }}</a>
    <a href="{{ route('superadmin.integrations.index') }}" class="btn-ghost">{{ __('Integrations & keys') }}</a>
    <a href="{{ route('admin.dashboard') }}" class="btn-ghost">{{ __('Admin panel') }}</a>
</div>

<section class="card-pad mt-6">
    <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-ink-900">{{ __('Recent audit entries') }}</h2><a href="{{ route('superadmin.logs.audit') }}" class="text-xs font-semibold text-ink-700 hover:underline">{{ __('See all') }}</a></div>
    <ul class="mt-4 space-y-3 text-sm">
        @forelse($recent as $l)
            <li class="flex flex-wrap items-start justify-between gap-2"><span><span class="badge-ink">{{ $l->action }}</span> <span class="text-ink-600">{{ $l->note }}</span></span><span class="text-xs text-ink-500">{{ $l->admin?->name ?? __('system') }} · {{ $l->created_at?->format('d M, H:i') }}</span></li>
        @empty
            <li class="text-ink-400">{{ __('No entries yet.') }}</li>
        @endforelse
    </ul>
</section>

<section class="card-pad mt-6">
    <h2 class="text-lg font-bold text-ink-900">{{ __('Permission matrix') }}</h2>
    <div class="mt-3 overflow-x-auto">
        <table class="w-full min-w-[34rem] text-left text-sm">
            <thead class="text-xs uppercase tracking-wide text-ink-500"><tr><th class="py-2 pr-3">{{ __('Capability') }}</th><th class="px-2">{{ __('Superadmin') }}</th><th class="px-2">{{ __('Admin') }}</th><th class="px-2">{{ __('Sub-admin') }}</th></tr></thead>
            <tbody class="divide-y divide-ink-50">
            @foreach($matrix as [$cap, $s, $a, $b])
                <tr><td class="py-2 pr-3 font-medium text-ink-800">{{ __($cap) }}</td><td class="px-2">{{ __($s) }}</td><td class="px-2">{{ __($a) }}</td><td class="px-2">{{ __($b) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-ink-500">{{ __('Hierarchy: superadmin > admin > sub-admin > member. Superadmins can only be created from the server console (php artisan superadmin:create).') }}</p>
</section>
@endsection
