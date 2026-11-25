@props(['flame' => true])
{{-- Clay diya with a flickering flame. --}}
<svg viewBox="0 0 80 70" role="img" aria-label="{{ __('दीया') }}" {{ $attributes->class(['block overflow-visible']) }}>
    <ellipse cx="40" cy="22" rx="26" ry="20" fill="#ffd23f" class="{{ $flame ? 'hr-glow' : '' }}" opacity=".35"/>
    @if($flame)
        <g class="hr-flame" style="transform-origin: 40px 44px">
            <path d="M40 8c8 10 12 18 8 26-2 4-6 8-8 8s-6-4-8-8c-4-8 0-16 8-26z" fill="#f9b81c"/>
            <path d="M40 20c4 6 6 11 4 16-1 3-3 5-4 5s-3-2-4-5c-2-5 0-10 4-16z" fill="#fff3b8"/>
        </g>
    @endif
    <path d="M36 42c2-2 6-2 8 0v4h-8z" fill="#2b1c16"/>
    <path d="M6 46c0-2 2-4 4-4h60c2 0 4 2 4 4 0 14-12 22-34 22S6 60 6 46z" fill="#c75f1c"/>
    <path d="M6 46c0-2 2-4 4-4h60c2 0 4 2 4 4 0 2-1 4-2 5H8c-1-1-2-3-2-5z" fill="#e08a3c"/>
    <path d="M18 58c6 5 14 6 22 6" stroke="#fff" stroke-opacity=".25" stroke-width="3" stroke-linecap="round" fill="none"/>
    <path d="M14 51h52" stroke="#ffd23f" stroke-width="2" stroke-dasharray="2 4" stroke-linecap="round"/>
</svg>
