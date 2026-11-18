@extends('layouts.admin')
@php $title = __('Events'); @endphp
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex gap-2">
        @foreach(['upcoming' => 'Upcoming', 'past' => 'Past'] as $k => $l)
            <a href="{{ route('admin.events.index', ['tab' => $k]) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold {{ $tab === $k ? 'bg-kumkum-700 text-white shadow-glow' : 'bg-white text-ink-700 ring-1 ring-ink-100' }}">{{ __($l) }}</a>
        @endforeach
    </div>
    <a href="{{ route('admin.events.create') }}" class="btn-gold">+ {{ __('New event') }}</a>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    @forelse($events as $e)
        <div class="card flex gap-3 p-3">
            @if($e->image)<img src="{{ $e->imageUrl() }}" alt="" class="h-24 w-24 shrink-0 rounded-2xl object-cover" loading="lazy">
            @else<span class="grid h-24 w-24 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-sandal to-gold-100 text-center leading-tight text-ink-700"><span><span class="block font-display text-2xl font-bold">{{ $e->starts_at->format('d') }}</span><span class="block text-xs font-semibold uppercase">{{ $e->starts_at->format('M') }}</span></span></span>@endif
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex flex-wrap gap-1">
                    <span class="{{ $e->is_published ? 'badge-ok' : 'badge-warn' }}">{{ $e->is_published ? __('Live') : __('Draft') }}</span>
                    <span class="badge-ink">{{ $e->area ?: __('All areas') }}</span>
                    @if($e->popup_enabled)<span class="badge-ink">{{ __('Popup') }} / {{ $e->reminder_interval_min }} min</span>@endif
                </div>
                <p class="mt-1 line-clamp-2 font-semibold text-ink-900">{{ $e->title }}</p>
                <p class="text-xs text-ink-500">{{ $e->startsHuman() }}@if($e->place) · {{ $e->place }}@endif</p>
                <p class="text-xs font-semibold text-ink-700">{{ $e->registrations_count }} {{ __('registered') }} · {{ $e->joined_count }} {{ __('joined') }}</p>
                <div class="mt-auto flex flex-wrap gap-1.5 pt-2">
                    <a href="{{ route('admin.events.registrants', $e->id) }}" class="btn-ghost btn-sm">{{ __('Registrants') }}</a>
                    <a href="{{ route('admin.events.edit', $e->id) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('admin.events.destroy', $e->id) }}" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn-sm rounded-full px-3 py-1.5 text-xs font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete') }}</button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="card-pad py-14 text-center sm:col-span-2">
            <p class="font-display text-xl font-bold text-ink-800">{{ __('No events here yet') }}</p>
            <a href="{{ route('admin.events.create') }}" class="btn-primary mt-4">{{ __('Create an event') }}</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection
