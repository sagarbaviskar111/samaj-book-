@props(['caption' => true, 'light' => true, 'size' => 640, 'loading' => 'lazy', 'glow' => true, 'float' => true])
{{-- Real Ganesh image (webp + png fallback). Old SVG only if the image files are missing. Caption: ॥ श्री गणेशाय नमः ॥ --}}
@php
    $px = $size <= 240 ? 240 : 640;
    $hasImg = file_exists(public_path("images/ganesh-{$px}.png"));
    $hasWebp = file_exists(public_path("images/ganesh-{$px}.webp"));
@endphp
<figure {{ $attributes->class(['inline-block text-center']) }}>
    @if($hasImg)
        <div class="hr-ganesh {{ $glow ? 'hr-ganesh--glow' : '' }} {{ $float ? 'hr-ganesh--float' : '' }}">
            <picture>
                @if($hasWebp)<source type="image/webp" srcset="{{ asset("images/ganesh-{$px}.webp") }}">@endif
                <img src="{{ asset("images/ganesh-{$px}.png") }}" alt="{{ __('श्री गणेश') }}" width="{{ $px }}" height="{{ $px }}" loading="{{ $loading }}" decoding="async" @if($loading === 'eager') fetchpriority="high" @endif class="relative mx-auto block h-auto w-full">
            </picture>
        </div>
    @else
    <svg viewBox="0 0 220 240" class="mx-auto h-full w-full max-w-[16rem]" role="img" aria-label="{{ __('श्री गणेश') }}">
        <circle cx="110" cy="104" r="88" fill="#ffd23f" opacity=".16"/>
        <path d="M110 228c-30 0-62-6-84-20 24-6 50-2 66 8 4-10 12-16 18-18 6 2 14 8 18 18 16-10 42-14 66-8-22 14-54 20-84 20z" fill="#f9b81c"/>
        <path d="M60 214c-10-34 2-72 50-72s60 38 50 72c-14 8-86 8-100 0z" fill="#d63a34"/>
        <ellipse cx="110" cy="178" rx="34" ry="30" fill="#ee9e0a"/>
        <ellipse cx="110" cy="98" rx="46" ry="44" fill="#f5a04e" stroke="#c75f1c" stroke-width="2"/>
        <path d="M70 66c2-20 14-34 40-34s38 14 40 34c-14-8-26-10-40-10s-26 2-40 10z" fill="#ffd23f" stroke="#c47c07" stroke-width="2"/>
        <path d="M110 100c-6 20 4 34-6 46-8 10-24 8-26-4-1-8 8-12 12-6" fill="none" stroke="#f5a04e" stroke-width="16" stroke-linecap="round"/>
    </svg>
    @endif
    @if($caption)
        <figcaption class="mt-1 font-brand text-base tracking-wide sm:text-lg {{ $light ? 'text-gold-200' : 'text-kumkum-700' }}">{{ __('॥ श्री गणेशाय नमः ॥') }}</figcaption>
    @endif
</figure>
