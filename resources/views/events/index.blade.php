@extends('layouts.app')
@php $title = __('कार्यक्रम'); @endphp
@section('content')
<div class="mb-4">
    <h1 class="font-display text-2xl font-bold text-ink-900">{{ __('कार्यक्रम') }}</h1>
    <p class="text-base text-ink-700">{{ __('समाज के सम्मेलन, बैठकें और भी बहुत कुछ।') }}</p>
</div>
<div class="mb-4 flex gap-2 overflow-x-auto">
    @foreach(['upcoming' => 'आने वाले', 'mine' => 'मेरे नाम लिखे', 'past' => 'बीते हुए'] as $k => $l)
        <a href="{{ route('events.index', ['tab' => $k]) }}" @if($tab === $k) aria-current="page" @endif class="shrink-0 whitespace-nowrap rounded-full px-5 py-3 text-base font-bold {{ $tab === $k ? 'bg-kumkum-600 text-white shadow-glow' : 'bg-white text-ink-800 ring-1 ring-ink-200' }}">{{ __($l) }}</a>
    @endforeach
</div>

<div class="grid gap-4 sm:grid-cols-2">
    @forelse($events as $e)
        @php $reg = in_array($e->id, $registeredIds, true); @endphp
        <a href="{{ route('events.show', $e) }}" class="card group overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="bd-paisley relative h-36 bg-gradient-to-br from-kumkum-900 via-kumkum-700 to-gold-500">
                @if($e->image)<img src="{{ $e->imageUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">@endif
                <span class="absolute left-3 top-3 rounded-xl bg-white/95 px-3 py-1.5 text-center leading-tight shadow"><span class="block font-display text-xl font-bold text-ink-800">{{ $e->starts_at->format('d') }}</span><span class="block text-sm font-bold uppercase text-gold-600">{{ $e->starts_at->format('M') }}</span></span>
                @if($reg)<span class="badge-ok absolute right-3 top-3">{{ __('नाम लिखा है') }}</span>@endif
            </div>
            <div class="p-4">
                <h2 class="font-display text-lg font-bold text-ink-900">{{ $e->title }}</h2>
                <p class="mt-1 text-base text-ink-700">{{ $e->startsHuman() }}</p>
                @if($e->place)<p class="text-base text-ink-700">{{ $e->place }}</p>@endif
                <p class="mt-2 text-sm font-bold text-ink-700">{{ $e->registrations_count }} {{ __('लोगों ने नाम लिखा') }}</p>
            </div>
        </a>
    @empty
        <div class="card-pad py-14 text-center sm:col-span-2">
            <p class="font-display text-xl font-bold text-ink-800">{{ __('यहाँ अभी कोई कार्यक्रम नहीं है') }}</p>
            <p class="mt-1 text-base text-ink-700">{{ __('थोड़ी देर बाद फिर देखें।') }}</p>
            <a href="{{ route('home') }}" class="btn-primary mt-4 min-h-12 px-8 text-lg">{{ __('होम पर जाएँ') }}</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection
