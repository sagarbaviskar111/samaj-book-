@props(['size' => 'h-10', 'wordmark' => true, 'light' => false, 'tagline' => false])
{{-- SamajBook brand lockup: marigold rosette around a heart (marriage) on a kumkum medallion.
     $light = true when placed on a dark/maroon background (wordmark turns cream/gold).
     To use the samaj's own official logo instead, drop it at public/images/samaj-logo.svg|png and it will be used automatically. --}}
@php
    $custom = file_exists(public_path('images/samaj-logo.svg')) ? asset('images/samaj-logo.svg')
            : (file_exists(public_path('images/samaj-logo.png')) ? asset('images/samaj-logo.png') : null);
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-2.5']) }}>
    @if($custom)
        <img src="{{ $custom }}" alt="{{ config('app.name') }}" class="{{ $size }} w-auto shrink-0">
    @else
        <svg viewBox="0 0 64 64" class="{{ $size }} aspect-square shrink-0 drop-shadow" role="img" aria-label="{{ config('app.name') }}">
            <defs>
                <radialGradient id="sbl-bg" cx="50%" cy="38%" r="70%">
                    <stop offset="0" stop-color="#d63a34"/><stop offset="1" stop-color="#701a24"/>
                </radialGradient>
                <linearGradient id="sbl-pt" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#ffd23f"/><stop offset="1" stop-color="#ee9e0a"/>
                </linearGradient>
            </defs>
            <circle cx="32" cy="32" r="31" fill="url(#sbl-bg)"/>
            <circle cx="32" cy="32" r="28" fill="none" stroke="#ffd23f" stroke-opacity=".55" stroke-width="1" stroke-dasharray="1.5 3"/>
            <g transform="translate(32 32)" fill="url(#sbl-pt)">
                @foreach (range(0, 11) as $k)
                    <ellipse cx="0" cy="-17" rx="5.2" ry="9.5" transform="rotate({{ $k * 30 }})" opacity="{{ $k % 2 ? .78 : 1 }}"/>
                @endforeach
            </g>
            <circle cx="32" cy="32" r="12.5" fill="#fff9ef"/>
            <path d="M32 40.5s-7.5-4.6-7.5-9.7c0-2.6 2-4.5 4.4-4.5 1.3 0 2.5.6 3.1 1.7.6-1.1 1.8-1.7 3.1-1.7 2.4 0 4.4 1.9 4.4 4.5 0 5.1-7.5 9.7-7.5 9.7z" fill="#b8262b"/>
        </svg>
    @endif
    @if($wordmark)
        <span class="leading-none">
            <span class="block font-brand text-[1.35em] {{ $light ? 'text-cream' : 'text-kumkum-700' }}">समाज<span class="{{ $light ? 'text-gold-300' : 'text-gold-600' }}">Book</span></span>
            @if($tagline)<span class="mt-1 block text-[.62em] font-semibold uppercase tracking-[.18em] {{ $light ? 'text-gold-200' : 'text-ink-500' }}">{{ __('धोबी समाज · अपना परिवार') }}</span>@endif
        </span>
    @endif
</span>
