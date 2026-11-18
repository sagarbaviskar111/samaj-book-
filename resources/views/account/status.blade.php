@extends('auth.layout', ['tagline' => __('आपके अकाउंट की स्थिति')])
@section('card')
@php($s = $user->status)
<div class="text-center">
    @if($s === 'rejected')
        <div class="relative mx-auto mb-5 grid h-28 w-28 place-items-center" aria-hidden="true">
            <span class="absolute inset-0 rounded-full bg-kumkum-50 ring-8 ring-kumkum-100/60"></span>
            <svg viewBox="0 0 96 96" class="relative h-20 w-20" fill="none">
                <rect x="22" y="14" width="52" height="68" rx="8" fill="#fff9ef" stroke="#b8262b" stroke-width="3"/>
                <path d="M34 34h28M34 46h28M34 58h16" stroke="#ffbdb8" stroke-width="4" stroke-linecap="round"/>
                <circle cx="68" cy="68" r="16" fill="#b8262b"/>
                <path d="M62 62l12 12M74 62L62 74" stroke="#fff" stroke-width="3.5" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="badge-bad">{{ __('अस्वीकृत') }}</span>
        <h1 class="mt-3 font-display text-2xl font-bold text-ink-900 sm:text-3xl">{{ __('आपकी रिक्वेस्ट अस्वीकार हुई') }}</h1>
        <p class="mt-3 text-base text-ink-800">{{ __('कारण') }}:</p>
        <p class="mt-1 rounded-xl bg-rose-50 px-4 py-3 text-base font-semibold text-rose-900 ring-1 ring-rose-200">{{ $user->reject_reason ?: __('कोई कारण नहीं बताया गया।') }}</p>
        <p class="mt-4 text-base text-ink-800">{{ __('सही जानकारी के साथ एडमिन से संपर्क करें।') }}</p>
    @elseif($s === 'blocked')
        <div class="relative mx-auto mb-5 grid h-28 w-28 place-items-center" aria-hidden="true">
            <span class="absolute inset-0 rounded-full bg-ink-50 ring-8 ring-ink-100/60"></span>
            <svg viewBox="0 0 96 96" class="relative h-20 w-20" fill="none">
                <path d="M30 42V32a18 18 0 0136 0v10" stroke="#96202a" stroke-width="5" stroke-linecap="round"/>
                <rect x="20" y="42" width="56" height="40" rx="9" fill="#96202a"/>
                <circle cx="48" cy="60" r="6" fill="#fbbf24"/>
                <path d="M48 64v8" stroke="#fbbf24" stroke-width="4" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="badge-ink">{{ __('ब्लॉक') }}</span>
        <h1 class="mt-3 font-display text-2xl font-bold text-ink-900 sm:text-3xl">{{ __('आपका अकाउंट ब्लॉक है') }}</h1>
        <p class="mt-3 text-base text-ink-800">{{ __('अधिक जानकारी के लिए एडमिन से संपर्क करें।') }}</p>
    @else
        <div class="relative mx-auto mb-5 grid h-28 w-28 place-items-center" aria-hidden="true">
            <span class="absolute inset-0 rounded-full bg-gold-100 ring-8 ring-gold-100/60"></span>
            <x-brand.diya class="relative h-16 w-20" />
            <svg class="pub-spin-slow absolute -right-1 -top-1 h-7 w-7 text-gold-500" viewBox="-50 -50 100 100" fill="currentColor">@foreach (range(0, 11) as $k)<ellipse cx="0" cy="-30" rx="9" ry="19" transform="rotate({{ $k * 30 }})" opacity=".85"/>@endforeach<circle r="16"/></svg>
        </div>
        <span class="badge-warn">{{ __('अप्रूवल बाकी') }}</span>
        <h1 class="mt-3 font-display text-2xl font-bold text-ink-900 sm:text-3xl">{{ __('नमस्ते') }}, {{ $user->name }}</h1>
        <p class="mt-3 text-base text-ink-900">{{ __('एडमिन की मंज़ूरी के बाद आप पूरा ऐप इस्तेमाल कर पाएँगे') }}</p>
        <p class="mt-1 text-base text-ink-800">{{ __('अप्रूव होते ही आपको सूचना मिलेगी।') }}</p>

        <ol class="mx-auto mt-6 max-w-xs space-y-3 text-left text-sm" aria-label="{{ __('प्रगति') }}">
            <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-full bg-tulsi-500 text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span><span class="font-semibold text-ink-900">{{ __('रजिस्टर हो गया') }}</span></li>
            <li class="flex items-center gap-3"><span class="relative grid h-8 w-8 place-items-center rounded-full bg-gold-400 text-ink-950"><span class="absolute inset-0 animate-ping rounded-full bg-gold-400/50 motion-reduce:hidden"></span><span class="relative text-xs font-bold">2</span></span><span class="font-semibold text-ink-900">{{ __('एडमिन जाँच जारी') }}</span></li>
            <li class="flex items-center gap-3 opacity-60"><span class="grid h-8 w-8 place-items-center rounded-full bg-sandal text-xs font-bold text-ink-700">3</span><span class="text-ink-800">{{ __('पूरा एक्सेस') }}</span></li>
        </ol>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-7">
        @csrf
        <button type="submit" class="btn-ghost min-h-[3rem] w-full">{{ __('लॉगआउट') }}</button>
    </form>
</div>
@endsection
