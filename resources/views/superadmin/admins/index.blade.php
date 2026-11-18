@extends('layouts.admin')
@php $title = __('Admins'); @endphp
@section('content')
<div class="mb-4 flex justify-end"><a href="{{ route('superadmin.admins.create') }}" class="btn-gold">+ {{ __('New admin') }}</a></div>
<div class="card divide-y divide-ink-50 overflow-hidden">
    @forelse($admins as $a)
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 sm:p-4">
            <div class="min-w-0">
                <p class="truncate font-semibold text-ink-900">{{ $a->name }}</p>
                <p class="text-xs text-ink-500">{{ $a->phone }}@if($a->email) · {{ $a->email }}@endif · {{ __('Last login') }}: {{ isset($lastLogin[$a->id]) ? \Illuminate\Support\Carbon::parse($lastLogin[$a->id])->format('d M Y, H:i') : '—' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.partials.status', ['s' => $a->status])
                <a href="{{ route('superadmin.users.show', $a->id) }}" class="btn-ghost btn-sm">{{ __('Profile') }}</a>
                <a href="{{ route('superadmin.admins.edit', $a->id) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                @if($a->status === 'blocked')
                    <form method="POST" action="{{ route('superadmin.admins.unblock', $a->id) }}">@csrf<button class="btn-ghost btn-sm">{{ __('Unblock') }}</button></form>
                @else
                    <form method="POST" action="{{ route('superadmin.admins.block', $a->id) }}" onsubmit="return confirm('{{ __('Block this admin?') }}')">@csrf<button class="btn-ghost btn-sm">{{ __('Block') }}</button></form>
                @endif
                <form method="POST" action="{{ route('superadmin.admins.reset-password', $a->id) }}" onsubmit="return confirm('{{ __('Reset password?') }}')">@csrf<button class="btn-ghost btn-sm">{{ __('Reset password') }}</button></form>
                <form method="POST" action="{{ route('superadmin.admins.destroy', $a->id) }}" onsubmit="return confirm('{{ __('Remove this admin?') }}')">@csrf @method('DELETE')<button class="btn-sm rounded-full px-3 py-1.5 text-xs font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Remove') }}</button></form>
            </div>
        </div>
    @empty
        <p class="px-4 py-14 text-center text-sm text-ink-400">{{ __('No admins yet.') }}</p>
    @endforelse
</div>
@endsection
