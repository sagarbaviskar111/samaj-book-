@extends('layouts.admin')
@php $title = __('Reports'); @endphp
@section('content')
@include('admin.partials.tabs', ['tabs' => ['open' => 'Open', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed', 'all' => 'All'], 'counts' => $counts, 'current' => $status, 'route' => 'admin.reports.index'])

<div class="space-y-4">
@forelse($reports as $r)
    @php
        $t = $targets[$r->target_type][$r->target_id] ?? null;
        $isPost = $r->target_type === 'post';
    @endphp
    <article class="card-pad border-l-4 {{ $r->status === 'open' ? 'border-l-gold-400' : 'border-l-ink-200' }}">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="badge-ink">{{ str_replace('_', ' ', $r->target_type) }} #{{ $r->target_id }}</span>
                @include('admin.partials.status', ['s' => $r->status])
            </div>
            <span class="text-xs text-ink-400">{{ $r->created_at->diffForHumans() }} · {{ __('by') }} {{ $r->reporter?->name }}</span>
        </div>
        <p class="mt-2 text-sm text-ink-800"><b>{{ __('Reason') }}:</b> {{ $r->reason ?: '—' }}</p>

        <div class="mt-3 rounded-2xl bg-ink-50 p-3 text-sm">
            @if(! $t)
                <p class="text-ink-400">{{ __('Target no longer exists.') }}</p>
            @elseif($r->target_type === 'post')
                <p class="text-xs font-semibold text-ink-500">{{ $t->user?->name }} · {{ $t->status }}</p>
                <p class="mt-1 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($t->body, 300) }}</p>
                @if($t->image)<img src="{{ asset('storage/'.$t->image) }}" class="mt-2 max-h-48 rounded-xl" alt="" loading="lazy">@endif
            @elseif($r->target_type === 'comment')
                <p class="text-xs font-semibold text-ink-500">{{ $t->user?->name }}</p>
                <p class="mt-1">{{ $t->body }}</p>
            @elseif($r->target_type === 'biodata')
                <p class="font-semibold">{{ $t->name }} <span class="text-ink-400">({{ $t->status }})</span></p>
                <p class="text-xs text-ink-500">{{ __('Owner') }}: {{ $t->user?->name }} · {{ $t->user?->phone }}</p>
            @elseif($r->target_type === 'user')
                <p class="font-semibold">{{ $t->name }} <span class="text-ink-400">({{ $t->status }})</span></p>
                <p class="text-xs text-ink-500">{{ $t->phone }}</p>
            @elseif($r->target_type === 'interest_help')
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gold-600">{{ __('Help request: contact both parties') }}</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach([[__('Sender'), $t->fromUser, $t->fromBiodata], [__('Receiver'), $t->toUser, $t->toBiodata]] as [$role, $u, $bd])
                        <div class="rounded-xl bg-white p-3 ring-1 ring-ink-100">
                            <p class="text-[11px] font-semibold uppercase text-ink-400">{{ $role }}</p>
                            <p class="font-semibold">{{ $bd?->name ?? $u?->name }}</p>
                            <p class="text-xs">{{ __('Account') }}: <a class="font-semibold text-ink-600 underline" href="tel:{{ $u?->phone }}">{{ $u?->phone }}</a></p>
                            @if($bd?->contact_phone)<p class="text-xs">{{ __('Contact') }}: <a class="font-semibold text-ink-600 underline" href="tel:{{ $bd->contact_phone }}">{{ $bd->contact_phone }}</a></p>@endif
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-ink-500">{{ __('Interest status') }}: {{ $t->status }}</p>
            @endif
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            @if($t)
                @php
                    $jump = match ($r->target_type) {
                        'biodata' => route('admin.biodata.show', $r->target_id),
                        'user' => route('admin.users.show', $r->target_id),
                        'post' => \App\Services\Admin\AdminNotifier::routeOrNull('posts.show', $r->target_id),
                        'interest_help' => route('admin.biodata.show', $t->from_biodata_id),
                        default => null,
                    };
                @endphp
                @if($jump)<a href="{{ $jump }}" class="btn-ghost btn-sm">{{ __('Open target') }}</a>@endif
            @endif
            @if($r->status === 'open')
                @if($isPost && $t)<form method="POST" action="{{ route('admin.reports.hide-post', $r->id) }}">@csrf<button class="btn-gold btn-sm">{{ __('Hide post') }}</button></form>@endif
                @if($t && auth()->user()->isFullAdmin() && in_array($r->target_type, ['post', 'comment', 'biodata', 'user'], true))
                    <form method="POST" action="{{ route('admin.reports.block-user', $r->id) }}" onsubmit="return confirm('{{ __('Block the user?') }}')">@csrf<button class="btn-danger btn-sm">{{ __('Block user') }}</button></form>
                @endif
                <form method="POST" action="{{ route('admin.reports.resolve', $r->id) }}">@csrf<button class="btn-primary btn-sm">{{ __('Resolve') }}</button></form>
                <form method="POST" action="{{ route('admin.reports.dismiss', $r->id) }}">@csrf<button class="btn-ghost btn-sm">{{ __('Dismiss') }}</button></form>
            @endif
        </div>
    </article>
@empty
    <div class="card-pad py-14 text-center"><p class="font-display text-xl font-bold text-ink-800">{{ __('All clear') }}</p><p class="mt-1 text-sm text-ink-400">{{ __('No reports in this view.') }}</p></div>
@endforelse
</div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
