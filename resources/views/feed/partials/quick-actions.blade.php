{{-- Home quick actions for approved members: big, labelled tiles (like Facebook/Instagram shortcuts). --}}
@php
    $qa = auth()->user();
    $has = fn ($r) => \Illuminate\Support\Facades\Route::has($r);
    $qaWaiting = \Illuminate\Support\Facades\Cache::remember('mb.reqwait.'.$qa->id, 60, fn () => \App\Models\Interest::where('to_user_id', $qa->id)->where('status', 'sent')->count());
    $qaHasBiodata = $qa->biodatas()->exists();
    $tiles = [
        ['biodata.index', 'रिश्ता खोजें', 'bg-kumkum-100 text-kumkum-700', 'M21 21l-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z', 0],
        ['interests.index', 'मेरे रिश्ते', 'bg-rose-100 text-rose-700', 'M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z', $qaWaiting],
        ['biodata.mine', 'मेरा बायोडाटा', 'bg-gold-100 text-gold-600', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0', 0],
        ['events.index', 'कार्यक्रम', 'bg-emerald-100 text-emerald-800', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z', 0],
    ];
@endphp
@if(! $qaHasBiodata && $has('biodata.create'))
    <a href="{{ route('biodata.create') }}" class="relative flex items-center gap-4 overflow-hidden rounded-3xl bg-gradient-to-r from-gold-400 to-gold-300 p-4 text-ink-950 shadow-lg ring-2 ring-gold-500/40 transition active:scale-[.98]">
        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white/80 text-kumkum-700"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></span>
        <span class="min-w-0 flex-1">
            <span class="block font-display text-xl font-bold leading-snug">{{ __('अपना बायोडाटा बनाएँ') }}</span>
            <span class="block text-base font-medium leading-snug">{{ __('अपने या परिवार के किसी सदस्य के लिए रिश्ता खोजना शुरू करें।') }}</span>
        </span>
        <span class="shrink-0 rounded-full bg-kumkum-700 px-4 py-2 text-base font-bold text-white">{{ __('शुरू करें') }}</span>
    </a>
@endif
<nav aria-label="{{ __('जल्दी खोलें') }}" class="grid grid-cols-2 gap-3 min-[420px]:grid-cols-4">
    @foreach($tiles as [$r, $label, $tone, $icon, $badge])
        @if($has($r))
            <a href="{{ route($r) }}" class="mb-tile relative">
                <span class="relative grid h-14 w-14 place-items-center rounded-2xl {{ $tone }}">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    @if($badge)<span class="mb-badge absolute -right-2 -top-2 grid h-6 min-w-6 place-items-center rounded-full bg-rose-brand px-1.5 text-sm font-bold leading-none text-white ring-2 ring-white" aria-label="{{ $badge }} {{ __('नए अनुरोध') }}">{{ $badge > 99 ? '99+' : $badge }}</span>@endif
                </span>
                <span>{{ __($label) }}</span>
            </a>
        @endif
    @endforeach
</nav>
