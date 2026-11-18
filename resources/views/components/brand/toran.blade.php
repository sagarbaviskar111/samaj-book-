@props(['units' => 12, 'sway' => true])
{{-- Marigold + mango-leaf toran (door garland). Gently sways. Stretches to container width. --}}
@php $w = $units * 100; @endphp
<svg viewBox="0 0 {{ $w }} 96" preserveAspectRatio="xMidYMin slice" aria-hidden="true" focusable="false" {{ $attributes->class(['pointer-events-none block w-full']) }}>
    <path d="M0 6H{{ $w }}" stroke="#96202a" stroke-width="3"/>
    @for ($i = 0; $i < $units; $i++)
        @php $x = $i * 100; $d = number_format(($i % 5) * -0.7, 1); @endphp
        <path d="M{{ $x }} 6Q{{ $x + 50 }} 30 {{ $x + 100 }} 6" fill="none" stroke="#2f7d57" stroke-width="2.4"/>
        {{-- mango leaves hanging --}}
        <g @class(['hr-garland' => $sway]) style="transform-origin: {{ $x + 24 }}px 14px; animation-delay: {{ $d }}s">
            <path d="M{{ $x + 24 }} 14c-8 12-8 26 0 38 8-12 8-26 0-38z" fill="#2f7d57"/>
            <path d="M{{ $x + 24 }} 16v34" stroke="#25664a" stroke-width="1.2"/>
        </g>
        <g @class(['hr-garland' => $sway]) style="transform-origin: {{ $x + 76 }}px 14px; animation-delay: {{ $d - 1.2 }}s">
            <path d="M{{ $x + 76 }} 14c-8 12-8 26 0 38 8-12 8-26 0-38z" fill="#2f7d57"/>
            <path d="M{{ $x + 76 }} 16v34" stroke="#25664a" stroke-width="1.2"/>
        </g>
        {{-- marigold strand --}}
        <g @class(['hr-garland' => $sway]) style="transform-origin: {{ $x + 50 }}px 20px; animation-delay: {{ $d - 0.6 }}s">
            <path d="M{{ $x + 50 }} 19V74" stroke="#c47c07" stroke-width="1.2"/>
            <circle cx="{{ $x + 50 }}" cy="27" r="8" fill="#f9b81c"/><circle cx="{{ $x + 50 }}" cy="27" r="3.6" fill="#c47c07"/>
            <circle cx="{{ $x + 50 }}" cy="44" r="8.5" fill="#ee9e0a"/><circle cx="{{ $x + 50 }}" cy="44" r="3.8" fill="#96202a" opacity=".85"/>
            <circle cx="{{ $x + 50 }}" cy="61" r="8" fill="#ffd23f"/><circle cx="{{ $x + 50 }}" cy="61" r="3.4" fill="#c47c07"/>
            <circle cx="{{ $x + 50 }}" cy="76" r="5" fill="#d63a34"/>
        </g>
    @endfor
</svg>
