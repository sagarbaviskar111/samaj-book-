@extends('layouts.app')
@php $title = $event->title; @endphp
@section('content')
<article class="card overflow-hidden">
    <div class="bd-paisley relative h-48 bg-gradient-to-br from-kumkum-900 via-kumkum-700 to-gold-500 sm:h-64">
        @if($event->image)<img src="{{ $event->imageUrl() }}" alt="{{ $event->title }}" class="h-full w-full object-cover">@endif
    </div>
    <div class="space-y-4 p-5">
        <div>
            <a href="{{ route('events.index') }}" class="text-base font-bold text-ink-700 underline hover:text-ink-900">&larr; {{ __('सभी कार्यक्रम') }}</a>
            <h1 class="mt-1 font-display text-2xl font-bold text-ink-900">{{ $event->title }}</h1>
        </div>
        <dl class="grid gap-2 text-base text-ink-800 sm:grid-cols-2">
            <div class="rounded-xl bg-ink-50 p-3"><dt class="text-sm font-bold text-ink-700">{{ __('कब') }}</dt><dd class="font-semibold">{{ $event->startsHuman() }}@if($event->ends_at) &ndash; {{ $event->ends_at->format('h:i A') }}@endif</dd></div>
            <div class="rounded-xl bg-ink-50 p-3"><dt class="text-sm font-bold text-ink-700">{{ __('कहाँ') }}</dt><dd class="font-semibold">{{ $event->place ?: __('ऑनलाइन / जल्द बताया जाएगा') }}</dd></div>
        </dl>
        @if($event->description)<p class="whitespace-pre-line text-ink-700">{{ $event->description }}</p>@endif
        <p class="text-base font-bold text-ink-700">{{ $count }} {{ __('लोगों ने नाम लिखा') }}</p>

        <div class="flex flex-wrap gap-2">
            @if($reg)
                @if($event->isToday() && ! $event->isOver())
                    <a href="{{ route('events.join', $event) }}" class="btn-gold min-h-12 px-8 text-base">{{ __('अभी जुड़ें') }}</a>
                @else
                    <span class="badge-ok self-center">{{ __('आपका नाम लिखा है') }}</span>
                @endif
                <form method="POST" action="{{ route('events.unregister', $event) }}" onsubmit="return confirm('{{ __('क्या आप अपना नाम हटाना चाहते हैं?') }}')">@csrf @method('DELETE')<button class="btn-ghost min-h-12">{{ __('नाम हटाएँ') }}</button></form>
            @elseif($event->acceptsRegistration())
                <form method="POST" action="{{ route('events.register', $event) }}">@csrf<button class="btn-primary min-h-12 px-8">{{ __('नाम लिखवाएँ') }}</button></form>
            @else
                <span class="badge-warn self-center">{{ $event->isOver() ? __('कार्यक्रम खत्म हो गया') : __('नाम लिखवाना बंद है') }}</span>
            @endif
            <a href="{{ route('events.ics', $event) }}" class="btn-ghost min-h-12">{{ __('कैलेंडर में जोड़ें') }}</a>
        </div>
    </div>
</article>
@endsection
