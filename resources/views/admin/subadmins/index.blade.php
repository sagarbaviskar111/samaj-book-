@extends('layouts.admin')
@php $title = __('Sub-admins'); @endphp
@section('content')
<div class="mb-4 flex justify-end"><a href="{{ route('admin.subadmins.create') }}" class="btn-gold">+ {{ __('New sub-admin') }}</a></div>
<div class="card divide-y divide-ink-50 overflow-hidden">
    @forelse($subs as $s)
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 sm:p-4">
            <div class="min-w-0">
                <p class="truncate font-semibold text-ink-900">{{ $s->name }} <span class="badge-ink">{{ $s->admin_area ?: '—' }}</span></p>
                <p class="text-xs text-ink-500">{{ $s->phone }}</p>
            </div>
            <div class="flex items-center gap-2">
                @include('admin.partials.status', ['s' => $s->status])
                <a href="{{ route('admin.subadmins.edit', $s->id) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('admin.subadmins.destroy', $s->id) }}" onsubmit="return confirm('{{ __('Remove this sub-admin?') }}')">@csrf @method('DELETE')<button class="btn-sm rounded-full px-3 py-1.5 text-xs font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete') }}</button></form>
            </div>
        </div>
    @empty
        <p class="px-4 py-14 text-center text-sm text-ink-400">{{ __('No sub-admins yet.') }}</p>
    @endforelse
</div>
@endsection
