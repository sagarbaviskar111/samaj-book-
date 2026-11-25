@extends('layouts.admin')
@php $title = __('News & Events'); @endphp
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex gap-2 overflow-x-auto">
        @foreach([null => 'All', 'news' => 'News', 'poster' => 'Posters', 'event' => 'Events'] as $k => $l)
            <a href="{{ route('admin.news.index', $k ? ['type' => $k] : []) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold {{ (string) $type === (string) $k ? 'bg-kumkum-700 text-white shadow-glow' : 'bg-white text-ink-700 ring-1 ring-ink-100' }}">{{ __($l) }}</a>
        @endforeach
    </div>
    <a href="{{ route('admin.news.create') }}" class="btn-gold">+ {{ __('New') }}</a>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    @forelse($items as $n)
        <div class="card flex gap-3 p-3">
            @if($n->image)<img src="{{ asset('storage/'.$n->image) }}" alt="" class="h-24 w-24 shrink-0 rounded-2xl object-cover" loading="lazy">
            @else<span class="grid h-24 w-24 shrink-0 place-items-center rounded-2xl bg-ink-50 text-xs font-semibold uppercase text-ink-300">{{ __(ucfirst($n->type)) }}</span>@endif
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex flex-wrap gap-1"><span class="badge-ink">{{ __(ucfirst($n->type)) }}</span>
                    <span class="{{ $n->is_published ? 'badge-ok' : 'badge-warn' }}">{{ $n->is_published ? __('Live') : __('Draft') }}</span></div>
                <p class="mt-1 line-clamp-2 font-semibold text-ink-900">{{ $n->title }}</p>
                @if($n->event_at)<p class="text-xs text-ink-500">{{ $n->event_at->format('d M Y, h:i A') }} {{ $n->event_place }}</p>@endif
                <div class="mt-auto flex flex-wrap gap-1.5 pt-2">
                    <a href="{{ route('admin.news.edit', $n->id) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('admin.news.publish', $n->id) }}">@csrf<button class="btn-ghost btn-sm">{{ $n->is_published ? __('Unpublish') : __('Publish') }}</button></form>
                    <form method="POST" action="{{ route('admin.news.destroy', $n->id) }}" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn-sm rounded-full px-3 py-1.5 text-xs font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete') }}</button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="card-pad py-14 text-center sm:col-span-2">
            <p class="font-display text-xl font-bold text-ink-800">{{ __('Nothing published yet') }}</p>
            <a href="{{ route('admin.news.create') }}" class="btn-primary mt-4">{{ __('Create the first one') }}</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
