@props(['caption' => null])
{{-- Sant Gadge Maharaj framed portrait (cutout in arched marigold frame). Silhouette fallback only if image file is absent. --}}
@php
    $hasPng = file_exists(public_path('images/sant-gadge-maharaj.png'));
    $hasWebp = file_exists(public_path('images/sant-gadge-maharaj.webp'));
    $hasImg = $hasPng || $hasWebp;
@endphp
<figure {{ $attributes->class(['mx-auto w-full max-w-[17rem] text-center sm:max-w-sm lg:max-w-md']) }}>
    <div class="hr-arch hr-sant relative mx-auto aspect-[3/4] w-full">
        {{-- marigold ring --}}
        <div class="absolute -inset-1.5 rounded-t-[999px] rounded-b-2xl bg-gold-400" style="background-image: radial-gradient(circle at 50% 50%, #ee9e0a 0 2px, transparent 2.5px); background-size: 12px 12px;"></div>
        <div class="hr-sant-frame absolute inset-0 overflow-hidden rounded-t-[999px] rounded-b-xl ring-4 ring-kumkum-700">
            <span class="hr-sant-halo" aria-hidden="true"></span>
            @if($hasImg)
                <picture>
                    @if($hasWebp)<source type="image/webp" srcset="{{ asset('images/sant-gadge-maharaj.webp') }}">@endif
                    <img src="{{ asset($hasPng ? 'images/sant-gadge-maharaj.png' : 'images/sant-gadge-maharaj.webp') }}" alt="संत गाडगे महाराज" width="728" height="1090" loading="lazy" decoding="async" class="relative z-[1] h-full w-full object-contain object-bottom px-3 pb-2 pt-10">
                </picture>
            @else
                <svg viewBox="0 0 200 250" class="relative z-[1] h-full w-full" role="img" aria-label="संत गाडगे महाराज (चित्रात्मक)">
                    <path d="M0 214c40-10 120-10 200 0v36H0z" fill="#d9c3a0"/>
                    <path d="M62 226c-4-52 6-96 38-100 32 4 42 48 38 100z" fill="#2f5d8c"/>
                    <circle cx="100" cy="108" r="19" fill="#8a6a52"/>
                    <path d="M80 104c2-16 12-22 20-22s18 6 20 22c-10-6-30-6-40 0z" fill="#c75f1c"/>
                    <path d="M40 206c-6-10-2-22 8-26-2-3 0-6 4-6h8c4 0 6 3 4 6 10 4 14 16 8 26-2 4-8 6-16 6s-14-2-16-6z" fill="#c75f1c"/>
                </svg>
            @endif
        </div>
        {{-- bottom marigold garland --}}
        <div class="absolute -bottom-3 left-1/2 z-[2] flex -translate-x-1/2 items-center gap-1 rounded-full bg-kumkum-700 px-3 py-1 text-gold-200 shadow-soft ring-2 ring-gold-300">
            <svg viewBox="-12 -12 24 24" class="h-3.5 w-3.5" fill="currentColor" aria-hidden="true">@foreach(range(0,7) as $k)<ellipse cx="0" cy="-6" rx="2.6" ry="4.6" transform="rotate({{ $k*45 }})"/>@endforeach<circle r="3"/></svg>
            <span class="font-display text-xs">१८७६ &ndash; १९५६</span>
        </div>
    </div>
    @if($caption)
        <figcaption class="mt-6 font-display text-lg text-kumkum-800">{{ $caption }}</figcaption>
    @endif
</figure>
