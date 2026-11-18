@extends('layouts.admin')
@php $title = __('Success stories'); @endphp
@section('content')
<div class="mb-4 flex justify-end"><a href="{{ route('admin.stories.create') }}" class="btn-gold">+ {{ __('New story') }}</a></div>
<div class="grid gap-3 sm:grid-cols-2">
    @forelse($items as $s)
        <div class="card flex gap-3 p-3">
            @if($s->image)<img src="{{ asset('storage/'.$s->image) }}" alt="" class="h-24 w-24 shrink-0 rounded-2xl object-cover" loading="lazy">
            @else<span class="grid h-24 w-24 shrink-0 place-items-center rounded-2xl bg-gold-50 text-3xl">&#10084;</span>@endif
            <div class="flex min-w-0 flex-1 flex-col">
                <span class="{{ $s->is_published ? 'badge-ok' : 'badge-warn' }} self-start">{{ $s->is_published ? __('Live') : __('Draft') }}</span>
                <p class="mt-1 line-clamp-2 font-semibold text-ink-900">{{ $s->couple_names }}</p>
                <div class="mt-auto flex gap-1.5 pt-2">
                    <a href="{{ route('admin.stories.edit', $s->id) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('admin.stories.destroy', $s->id) }}" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="btn-sm rounded-full px-3 py-1.5 text-xs font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete') }}</button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="card-pad py-14 text-center sm:col-span-2"><p class="font-display text-xl font-bold text-ink-800">{{ __('No stories yet') }}</p></div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
