@extends('layouts.app')
@php $title = __('रिश्ते खोजें'); @endphp
@section('content')
@php
    $forLabel = $for === 'bride' ? __('दुल्हन के बायोडाटा आपका इंतज़ार कर रहे हैं') : __('दूल्हे के बायोडाटा आपका इंतज़ार कर रहे हैं');
    $count = $for === 'bride' ? $brides : $grooms;
    $max = max(1, ...array_values($byDistrict ?: [1]));
@endphp

<div class="mx-auto max-w-2xl">
    <h1 class="section-title !text-2xl sm:!text-3xl">{{ __('रिश्ते खोजें') }}</h1>

    <div class="em-switch mt-3 max-w-2xl">
        <a href="{{ route('biodata.index', ['for' => 'bride']) }}" aria-current="{{ $for === 'bride' ? 'true' : 'false' }}">{{ __('दुल्हन चाहिए (लड़की)') }}</a>
        <a href="{{ route('biodata.index', ['for' => 'groom']) }}" aria-current="{{ $for === 'groom' ? 'true' : 'false' }}">{{ __('दूल्हा चाहिए (लड़का)') }}</a>
    </div>

    {{-- Big count + hook --}}
    <div class="card-pad bd-paisley mt-4 text-center">
        <p class="text-base font-semibold text-ink-700">{{ __('हमारे समाज में अभी') }}</p>
        <p class="font-display text-6xl font-bold text-kumkum-700" aria-label="{{ $count }}">{{ $count }}</p>
        <p class="text-xl font-bold text-ink-900">{{ $forLabel }}</p>
        <p class="mt-1 text-sm text-ink-700">{{ __('दुल्हन') }}: <strong>{{ $brides }}</strong> &middot; {{ __('दूल्हा') }}: <strong>{{ $grooms }}</strong></p>
    </div>

    {{-- Where they are (names + numbers only) --}}
    @if($byDistrict)
    <div class="card-pad mt-4">
        <h2 class="font-display text-lg font-bold text-ink-900">&#128205; {{ __('ये बायोडाटा कहाँ-कहाँ से हैं') }}</h2>
        <ul class="mt-3 space-y-2">
            @foreach($byDistrict as $name => $c)
                <li>
                    <div class="flex items-center justify-between text-base font-semibold text-ink-900"><span>{{ $name }}</span><span>{{ $c }}</span></div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-ink-100"><div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-kumkum-500" style="width: {{ max(6, round($c / $max * 100)) }}%"></div></div>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Why create --}}
    <div class="card-pad mt-4 bg-gradient-to-br from-gold-50 to-white ring-1 ring-gold-200">
        <p class="text-xl font-bold text-ink-900">&#128274; {{ __('बायोडाटा देखने के लिए पहले अपना बायोडाटा बनाइए') }}</p>
        <p class="mt-1 text-base text-ink-800">{{ __('सिर्फ़ 5 मिनट लगते हैं। बायोडाटा बनाते ही आप:') }}</p>
        <ul class="mt-3 space-y-2 text-base text-ink-900">
            <li>&#10004; {{ __('सारे :n बायोडाटा फ़ोटो और पूरी जानकारी के साथ देख सकेंगे', ['n' => $brides + $grooms]) }}</li>
            <li>&#10004; {{ __('पसंद आने पर रिश्ते की पसंद भेज सकेंगे') }}</li>
            <li>&#10004; {{ __('आपका बायोडाटा भी समाज के परिवारों को दिखेगा, रिश्ते आपके पास आएँगे') }}</li>
            <li>&#10004; {{ __('मेल (%) देख सकेंगे और सुंदर PDF बायोडाटा पा सकेंगे') }}</li>
        </ul>
        <a href="{{ route('biodata.create') }}" class="btn-primary mt-4 w-full !min-h-14 !text-lg">{{ __('मेरा बायोडाटा बनाएँ') }}</a>
        <p class="mt-3 text-center text-sm text-ink-700">{{ __('आपकी जानकारी सिर्फ़ मंज़ूर हुए समाज के सदस्यों को दिखती है। फ़ोन नंबर पसंद मंज़ूर होने के बाद ही खुलता है।') }}</p>
    </div>
</div>
@endsection
