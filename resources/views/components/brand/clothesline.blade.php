@props(['cards' => null])
{{-- Clothesline of "freshly washed" biodata cards that flutter gently (the samaj's own trade, as a signature motif).
     Cards are decorative samples: blurred photo, first name and age only. --}}
@php
    $cards = $cards ?? [
        ['name' => 'प्रिया', 'age' => 25, 'meta' => 'B.Ed. · शिक्षिका', 'tone' => 'from-gold-300 to-kumkum-400', 'ok' => true],
        ['name' => 'राहुल', 'age' => 28, 'meta' => 'इंजीनियर · पुणे', 'tone' => 'from-neel-300 to-neel-500', 'ok' => true],
        ['name' => 'सपना', 'age' => 24, 'meta' => 'B.Com. · नागपुर', 'tone' => 'from-kumkum-300 to-gold-400', 'ok' => false],
        ['name' => 'अमित', 'age' => 27, 'meta' => 'व्यापारी · इंदौर', 'tone' => 'from-tulsi-500 to-gold-300', 'ok' => true],
    ];
@endphp
<div {{ $attributes->class(['relative mx-auto w-full max-w-xl select-none pt-3']) }} role="img" aria-label="{{ __('कपड़े सुखाने की रस्सी पर टँगे बायोडाटा कार्ड') }}">
    {{-- rope --}}
    <svg viewBox="0 0 100 12" preserveAspectRatio="none" class="absolute inset-x-0 top-0 h-3 w-full text-gold-200" aria-hidden="true">
        <path d="M0 3Q50 11 100 3" fill="none" stroke="currentColor" stroke-width="1.6" vector-effect="non-scaling-stroke"/>
    </svg>
    <div class="grid grid-cols-3 gap-2.5 px-2 sm:grid-cols-4 sm:gap-3">
        @foreach ($cards as $i => $c)
            <div class="hr-cloth {{ $i === 3 ? 'hidden sm:block' : '' }} {{ in_array($i, [1, 2]) ? 'mt-1.5' : '' }}" style="--sd: {{ -$i * 0.9 }}s; --dur: {{ 4.6 + $i * 0.5 }}s">
                {{-- pegs --}}
                <div class="relative z-10 mx-auto -mb-1.5 flex w-3/4 justify-between" aria-hidden="true">
                    <span class="h-4 w-1.5 rounded-sm bg-gold-300 shadow ring-1 ring-gold-600/50"></span>
                    <span class="h-4 w-1.5 rounded-sm bg-gold-300 shadow ring-1 ring-gold-600/50"></span>
                </div>
                <div class="overflow-hidden rounded-lg bg-cream text-ink-900 shadow-[0_12px_24px_-10px_rgb(0_0_0/.55)] ring-1 ring-gold-200/70">
                    <div class="relative h-14 bg-gradient-to-br {{ $c['tone'] }} sm:h-24">
                        <svg viewBox="0 0 100 100" class="absolute inset-0 m-auto h-10 w-10 text-white/75 blur-[2.5px] sm:h-16 sm:w-16" fill="currentColor" aria-hidden="true"><circle cx="50" cy="36" r="20"/><path d="M12 96c0-22 17-36 38-36s38 14 38 36z"/></svg>
                        @if($c['ok'])
                            <span class="absolute bottom-1 right-1 grid h-4 w-4 place-items-center rounded-full bg-tulsi-500 text-white sm:h-5 sm:w-5"><svg viewBox="0 0 24 24" class="h-2.5 w-2.5 sm:h-3 sm:w-3" fill="none" stroke="currentColor" stroke-width="3.5"><path d="M5 13l4 4L19 7"/></svg></span>
                        @endif
                    </div>
                    <div class="px-1.5 py-1.5 text-center sm:px-2 sm:py-2">
                        <div class="font-display text-[13px] font-bold leading-[1.6] sm:text-base">{{ __($c['name']) }}, {{ $c['age'] }}</div>
                        <div class="hidden truncate text-[11px] leading-snug text-ink-600 sm:block">{{ __($c['meta']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
