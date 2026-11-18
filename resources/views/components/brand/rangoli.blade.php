@props(['petals' => 8])
{{-- Rangoli / mandala divider. Uses currentColor so set a text colour (e.g. text-gold-500). --}}
<div {{ $attributes->class(['flex items-center justify-center gap-3 text-gold-500']) }} aria-hidden="true">
    <span class="h-px max-w-[9rem] flex-1 bg-gradient-to-r from-transparent via-current to-current opacity-60"></span>
    <svg viewBox="-30 -30 60 60" class="h-9 w-9 shrink-0" fill="currentColor">
        @foreach (range(0, $petals - 1) as $k)
            <ellipse cx="0" cy="-17" rx="4.4" ry="9" transform="rotate({{ $k * (360 / $petals) }})" opacity=".9"/>
            <circle cx="0" cy="-26.5" r="1.7" transform="rotate({{ $k * (360 / $petals) + 180 / $petals }})"/>
        @endforeach
        <circle r="7.5"/>
        <circle r="3.4" fill="#fff9ef" opacity=".9"/>
    </svg>
    <span class="h-px max-w-[9rem] flex-1 bg-gradient-to-l from-transparent via-current to-current opacity-60"></span>
</div>
