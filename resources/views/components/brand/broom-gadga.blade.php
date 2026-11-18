@props(['show' => 'both'])
{{-- Sant Gadge Maharaj symbols: the broom (safai) and the earthen pot "gadga". Uses currentColor for strokes; accents in marigold.
     show = both | broom | gadga --}}
<svg viewBox="0 0 {{ $show === 'both' ? 120 : 64 }} 80" role="img" aria-label="झाड़ू और गडगा" {{ $attributes->class(['block']) }}>
    @if($show !== 'gadga')
        <g @if($show === 'both') transform="translate(0 0)" @endif>
            <path d="M46 6L24 50" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
            <path d="M18 46l16 6 10 20c-14 4-28 2-38-4z" fill="#ee9e0a" transform="rotate(-8 24 60)"/>
            <path d="M14 52l-4 18M20 54l-3 18M26 56l-2 18M32 58l-1 16M38 58l0 15" stroke="#96202a" stroke-width="1.6" stroke-linecap="round" opacity=".7" transform="rotate(-8 24 60)"/>
            <path d="M16 49l20 7" stroke="#d63a34" stroke-width="3.6" stroke-linecap="round" transform="rotate(-8 24 60)"/>
        </g>
    @endif
    @if($show !== 'broom')
        <g transform="translate({{ $show === 'both' ? 62 : 0 }} 0)">
            <ellipse cx="30" cy="74" rx="18" ry="3.2" fill="currentColor" opacity=".15"/>
            <path d="M20 22c-2 4 0 8 3 10C10 38 6 50 8 60c2 10 12 14 22 14s20-4 22-14c2-10-2-22-15-28 3-2 5-6 3-10-1-2-3-3-5-3H25c-2 0-4 1-5 3z" fill="#c75f1c"/>
            <path d="M16 20h28c2 0 3 1 3 3s-1 3-3 3H16c-2 0-3-1-3-3s1-3 3-3z" fill="#e08a3c"/>
            <path d="M10 52h40" stroke="#ffd23f" stroke-width="2.4" stroke-dasharray="2 3.5" stroke-linecap="round"/>
            <path d="M12 44h36" stroke="#96202a" stroke-width="2" opacity=".6"/>
            <path d="M16 38c-4 8-5 16-3 22" stroke="#fff" stroke-opacity=".28" stroke-width="3" stroke-linecap="round" fill="none"/>
        </g>
    @endif
</svg>
