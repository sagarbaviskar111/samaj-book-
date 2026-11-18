@extends('layouts.admin')
@php
    $title = $user->name.' · 360°';
    $kv = fn (array $rows) => collect($rows);
@endphp
@section('content')
<a href="{{ route('superadmin.dashboard') }}" class="mb-3 inline-block text-sm font-semibold text-ink-600">&larr; {{ __('Superadmin console') }}</a>

<section class="card overflow-hidden">
    <div class="gradient-hero h-20"></div>
    <div class="p-4 sm:p-5">
        <div class="-mt-10 flex items-center gap-4">
            <span class="grid h-16 w-16 place-items-center rounded-full bg-gradient-to-br from-kumkum-500 to-kumkum-800 font-display text-2xl font-bold text-white ring-4 ring-white">{{ mb_substr($user->name, 0, 1) }}</span>
            <div class="min-w-0"><h2 class="truncate text-xl font-bold text-ink-900">{{ $user->name }}</h2>
                <span class="badge-ink">{{ $user->roleLabel() }}</span> @include('admin.partials.status', ['s' => $user->status])</div>
        </div>
        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm lg:grid-cols-4">
            @foreach([['Phone', $user->phone], ['Email', $user->email], ['Role', $user->role], ['Status', $user->status], ['Gotra', $user->gotra], ['State', $user->state], ['District', $user->district], ['City', $user->city], ['Area', $user->area], ['Admin area', $user->admin_area], ['Language', $user->language], ['Joined', $user->created_at?->format('d M Y, H:i')], ['Last seen', $user->last_seen_at?->format('d M Y, H:i')], ['Reject reason', $user->reject_reason]] as [$k, $v])
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __($k) }}</dt><dd class="break-words font-medium text-ink-900">{{ $v ?: '—' }}</dd></div>
            @endforeach
        </dl>
        @if($user->role === 'user')<a href="{{ route('admin.users.show', $user->id) }}" class="btn-ghost btn-sm mt-4">{{ __('Open in admin panel') }}</a>@endif
    </div>
</section>

<section class="card-pad mt-5">
    <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Biodata (all fields, including hidden)') }}</h3>
    @forelse($biodatas as $b)
        <div class="mt-3 rounded-2xl p-3 ring-1 ring-ink-100">
            <div class="flex items-center justify-between"><p class="font-semibold text-ink-900">{{ $b->name }} <span class="text-xs text-ink-400">· {{ $b->added_by }} · {{ $b->gender }}</span></p>@include('admin.partials.status', ['s' => $b->status])</div>
            @if($b->photos->count())
                <div class="mt-3 flex gap-2 overflow-x-auto pb-1">@foreach($b->photos as $p)<img src="{{ asset('storage/'.$p->path) }}" alt="{{ $b->name }}" class="h-28 w-24 shrink-0 rounded-xl object-cover ring-1 ring-ink-100" loading="lazy">@endforeach</div>
            @endif
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm lg:grid-cols-4">
                @foreach([
                    ['Contact phone', $b->contact_phone], ['Photo privacy', $b->photo_privacy], ['Income', $b->income_range], ['Income private', $b->income_private ? 'Yes' : 'No'],
                    ['DOB', $b->dob?->format('d M Y')], ['Education', $b->education], ['Occupation', $b->occupation], ['Company', $b->company],
                    ['Father', $b->father_name], ['Mother', $b->mother_name], ['Gotra', $b->gotra], ['Village', $b->village],
                    ['District', $b->district], ['State', $b->state], ['Verified', $b->verified ? 'Yes' : 'No'], ['Requests to parent', $b->requests_to_parent ? 'Yes' : 'No'],
                ] as [$k, $v])
                    <div><dt class="text-[11px] uppercase text-ink-400">{{ __($k) }}</dt><dd class="break-words font-medium">{{ $v ?: '—' }}</dd></div>
                @endforeach
            </dl>
            <a href="{{ route('admin.biodata.show', $b->id) }}" class="mt-2 inline-block text-xs font-semibold underline">{{ __('Open biodata review') }}</a>
        </div>
    @empty
        <p class="mt-2 text-sm text-ink-400">{{ __('No biodata.') }}</p>
    @endforelse
</section>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Interests sent') }} ({{ $interestsSent->count() }})</h3>
        <ul class="mt-2 space-y-1 text-sm">@forelse($interestsSent as $i)<li>{{ $i->toBiodata?->name ?? $i->toUser?->name ?? '—' }} · <span class="badge-ink">{{ $i->status }}</span> <span class="text-xs text-ink-400">{{ $i->created_at?->format('d M Y') }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Interests received') }} ({{ $interestsReceived->count() }})</h3>
        <ul class="mt-2 space-y-1 text-sm">@forelse($interestsReceived as $i)<li>{{ $i->fromBiodata?->name ?? $i->fromUser?->name ?? '—' }} · <span class="badge-ink">{{ $i->status }}</span> <span class="text-xs text-ink-400">{{ $i->created_at?->format('d M Y') }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Posts') }} ({{ $posts->count() }})</h3>
        <ul class="mt-2 space-y-2 text-sm">@forelse($posts as $p)<li><span class="text-ink-800">{{ \Illuminate\Support\Str::limit($p->body, 120) ?: '(image)' }}</span> <span class="text-xs text-ink-400">{{ $p->status }} · {{ $p->created_at?->format('d M Y') }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Event registrations') }} ({{ $registrations->count() }})</h3>
        <ul class="mt-2 space-y-1 text-sm">@forelse($registrations as $r)<li>{{ $r->event?->title ?? '—' }} <span class="text-xs text-ink-400">{{ $r->event?->starts_at?->format('d M Y') }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Payments') }} ({{ $payments->count() }})</h3>
        <ul class="mt-2 space-y-1 text-sm">@forelse($payments as $p)<li><a class="font-semibold underline" href="{{ route('superadmin.payments.show', $p->id) }}">{{ $p->amountFormatted() }}</a> · {{ $p->purpose }} · {{ $p->gateway }} · <span class="badge-ink">{{ $p->status }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
    <section class="card-pad">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Login history') }}</h3>
        <ul class="mt-2 space-y-1 text-sm">@forelse($logins as $l)<li><span class="{{ $l->event === 'failed' ? 'badge-bad' : 'badge-ink' }}">{{ $l->event }}</span> {{ $l->created_at?->format('d M, H:i') }} <span class="text-xs text-ink-400">{{ $l->ip }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
    </section>
</div>

<section class="card-pad mt-5">
    <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Audit trail (about this user and by this user)') }}</h3>
    <ul class="mt-2 space-y-2 text-sm">@forelse($audit as $l)<li><span class="badge-ink">{{ $l->action }}</span> <span class="text-ink-600">{{ $l->note }}</span> <span class="text-xs text-ink-400">{{ $l->admin?->name ?? __('system') }} · {{ $l->created_at?->format('d M Y, H:i') }}</span></li>@empty<li class="text-ink-400">—</li>@endforelse</ul>
</section>
@endsection
