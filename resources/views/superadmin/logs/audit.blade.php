@extends('layouts.admin')
@php $title = __('Full logs'); @endphp
@section('content')
@include('superadmin.partials.logtabs')
<form method="GET" class="card-pad mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
    <input class="input" name="actor" value="{{ request('actor') }}" placeholder="{{ __('Actor name / phone') }}">
    <input class="input" name="action" value="{{ request('action') }}" placeholder="{{ __('Action') }}">
    <select class="input" name="type"><option value="">{{ __('All targets') }}</option>@foreach($types as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select>
    <input class="input" type="date" name="from" value="{{ request('from') }}" aria-label="{{ __('From') }}">
    <input class="input" type="date" name="to" value="{{ request('to') }}" aria-label="{{ __('To') }}">
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Filter') }}</button><a class="btn-ghost" href="{{ route('superadmin.logs.audit.export', request()->query()) }}">CSV</a></div>
</form>
<div class="card-pad">
    <ul class="space-y-5">
    @forelse($logs as $l)
        <li class="adm-tl">
            <span class="adm-tl-dot"></span>
            <div class="flex flex-wrap items-start justify-between gap-2 text-sm">
                <div class="min-w-0">
                    <span class="badge-ink">{{ $l->action }}</span>
                    @if($l->target_type)<span class="ml-1 text-xs text-ink-500">{{ $l->target_type }}#{{ $l->target_id }}</span>@endif
                    <p class="mt-1 break-words text-ink-700">{{ $l->note ?: '—' }}</p>
                </div>
                <div class="text-right text-xs text-ink-500"><p class="font-semibold text-ink-700">{{ $l->admin?->name ?? __('system') }} @if($l->admin)<span class="badge-ink">{{ $l->admin->role }}</span>@endif</p><p>{{ $l->created_at?->format('d M Y, H:i') }}</p></div>
            </div>
        </li>
    @empty
        <li>@include('admin.partials.empty', ['title' => __('No entries yet.'), 'text' => ''])</li>
    @endforelse
    </ul>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
