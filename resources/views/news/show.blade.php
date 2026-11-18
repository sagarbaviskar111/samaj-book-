@extends('layouts.public')

@section('content')
<section class="mx-auto max-w-3xl px-4 pb-20 pt-8">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="inline-flex min-h-[2.75rem] items-center gap-1 text-base font-semibold text-kumkum-700 hover:text-kumkum-900">&larr; {{ __('वापस') }}</a>

    <article class="card mt-2 overflow-hidden border-t-4 border-gold-400 shadow-glow">
        @if($item->image)
            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title }}" class="w-full object-cover">
        @else
            <div class="hr-hero hr-pattern relative grid h-56 place-items-center overflow-hidden px-6 text-center font-display text-3xl font-bold leading-[1.6] text-white">
                <span class="relative z-[1]">{{ $item->title }}</span>
            </div>
        @endif

        <div class="p-5 sm:p-7">
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge-ink">{{ ucfirst($item->type) }}</span>
                <span class="text-sm text-ink-700">{{ $item->created_at->format('d M Y') }}</span>
                @if($item->area)<span class="badge-warn">{{ $item->area }}</span>@endif
            </div>

            <h1 class="hr-h mt-3 text-3xl font-bold text-ink-900 sm:text-4xl">{{ $item->title }}</h1>
            <x-brand.rangoli class="mt-3 justify-start text-gold-500" />

            @if($item->type === 'event' && $item->event_at)
                <div class="mt-4 rounded-2xl bg-gold-50 p-4 ring-1 ring-gold-200">
                    <p class="text-base font-semibold text-ink-900">{{ $item->event_at->format('l, d F Y · h:i A') }}</p>
                    @if($item->event_place)<p class="mt-1 text-base text-ink-700">{{ $item->event_place }}</p>@endif
                </div>
            @endif

            @if($item->body)
                <div class="mt-4 whitespace-pre-line text-lg leading-[1.8] text-ink-900/85">{{ $item->body }}</div>
            @endif
        </div>
    </article>

    @if($more->isNotEmpty())
        <h2 class="hr-h mt-8 text-xl font-bold text-ink-900">{{ __('और समाचार') }}</h2>
        <div class="mt-3 grid gap-3">
            @foreach($more as $m)
                <a href="{{ route('news.show', $m) }}" class="card-pad flex items-center justify-between gap-3 hover:bg-gold-50">
                    <span class="text-base font-semibold text-ink-900">{{ $m->title }}</span>
                    <span class="badge-ink shrink-0">{{ ucfirst($m->type) }}</span>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
