{{-- Kalash (auspicious pot) with mango leaves and coconut. --}}
<svg viewBox="0 0 140 180" role="img" aria-label="{{ __('कलश') }}" {{ $attributes->class(['block']) }}>
    {{-- mango leaves --}}
    <g fill="#2f7d57">
        <path d="M70 62C52 44 36 36 18 40c6 20 24 32 52 22z"/>
        <path d="M70 62c18-18 34-26 52-22-6 20-24 32-52 22z"/>
        <path d="M70 60C60 40 56 22 62 6c14 12 20 34 8 54z" fill="#25664a"/>
        <path d="M70 60c10-20 14-38 8-54-14 12-20 34-8 54z" fill="#2f7d57"/>
    </g>
    <path d="M70 60V10M70 62L22 42M70 62l48-20" stroke="#bfe3cf" stroke-width="1" opacity=".5" fill="none"/>
    {{-- coconut --}}
    <ellipse cx="70" cy="62" rx="22" ry="20" fill="#8a5a2b"/>
    <ellipse cx="70" cy="62" rx="22" ry="20" fill="none" stroke="#5e3a18" stroke-width="2"/>
    <path d="M62 50c4-4 12-4 16 0M60 72c6 4 14 4 20 0" stroke="#5e3a18" stroke-width="1.5" fill="none" opacity=".7"/>
    <circle cx="64" cy="62" r="2" fill="#2b1c16"/><circle cx="76" cy="62" r="2" fill="#2b1c16"/><circle cx="70" cy="68" r="2" fill="#2b1c16"/>
    <path d="M70 42c-4-6-2-12 0-16 2 4 4 10 0 16z" fill="#c47c07"/>
    {{-- pot --}}
    <path d="M44 82h52c4 0 6 4 4 8l-4 8c18 10 30 28 28 46-2 14-14 22-30 22H46c-16 0-28-8-30-22-2-18 10-36 28-46l-4-8c-2-4 0-8 4-8z" fill="#d63a34"/>
    <path d="M44 82h52c4 0 6 4 4 8l-2 5H42l-2-5c-2-4 0-8 4-8z" fill="#ee9e0a"/>
    <path d="M46 98h48" stroke="#ffd23f" stroke-width="3"/>
    {{-- swirl motif --}}
    <g fill="none" stroke="#ffd23f" stroke-width="2.6" stroke-linecap="round">
        <path d="M30 138h80"/><path d="M26 152h88"/>
    </g>
    <g fill="#ffd23f">
        <circle cx="70" cy="122" r="9"/>
        @foreach (range(0, 7) as $k)<circle cx="{{ 70 + 15 * cos($k * M_PI / 4) }}" cy="{{ 122 + 15 * sin($k * M_PI / 4) }}" r="2.6"/>@endforeach
    </g>
    <circle cx="70" cy="122" r="4.4" fill="#96202a"/>
    <path d="M34 112c-8 10-10 22-6 32" stroke="#fff" stroke-opacity=".25" stroke-width="4" stroke-linecap="round" fill="none"/>
</svg>
