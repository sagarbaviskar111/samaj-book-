{{-- Step header. Params: n, title, hi (optional English word), help, icon --}}
@php
    $paths = [
        'person' => 'M15.5 7.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM5 20a7 7 0 0 1 14 0',
        'work' => 'M4 8h16v11H4V8Zm5 0V5h6v3M4 13h16',
        'family' => 'M17 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-3A3.5 3.5 0 0 0 7 18.5V20M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        'pin' => 'M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'about' => 'M8 10h8M8 14h5M5 4h14a1 1 0 0 1 1 1v14l-4-3H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z',
    ];
@endphp
<div class="mb-4 flex items-start gap-3">
    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-kumkum-600 to-kumkum-400 text-white shadow-glow"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$icon] ?? $paths['person'] }}"/></svg></span>
    <div class="min-w-0">
        <h2 class="font-display text-xl font-bold leading-tight text-ink-900">{{ __('चरण :n', ['n' => $n]) }}: {{ __($title) }}</h2>
        <p class="mt-0.5 text-base text-ink-700">{{ __($help) }}</p>
    </div>
</div>
