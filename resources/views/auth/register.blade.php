@extends('auth.layout')
@section('card')
@php
    $err = fn ($f) => $errors->has($f) ? 'border-rose-400 ring-4 ring-rose-100' : '';
    $warnIcon = '<svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2zm0-4h-2V7h2z"/></svg>';
@endphp
<div class="mb-5 flex items-center gap-3">
    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-gold-500 to-gold-300 text-kumkum-900 shadow-lg shadow-gold-500/30 ring-2 ring-kumkum-600"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM19 8v6m3-3h-6"/></svg></span>
    <div>
        <h1 class="font-display text-2xl font-bold leading-tight text-ink-900 sm:text-3xl">{{ __('नया अकाउंट बनाएँ') }}</h1>
        <p class="text-base text-ink-800">{{ __('एडमिन अप्रूवल के बाद आप पूरा ऐप इस्तेमाल कर पाएँगे') }}</p>
    </div>
</div>

<div class="mb-6" aria-hidden="true">
    <div class="flex items-center justify-between text-sm font-bold text-ink-800"><span>{{ __('प्रगति') }}</span><span id="regpct">0%</span></div>
    <div class="mt-1 h-2 overflow-hidden rounded-full bg-ink-100"><div id="regbar" class="h-full w-0 rounded-full bg-gradient-to-r from-gold-400 to-kumkum-600 transition-all duration-500"></div></div>
</div>

@include('auth._flash')
@if ($errors->any())
    <div class="mb-5 flex items-start gap-2 rounded-xl bg-rose-50 px-3.5 py-3 text-sm font-medium text-rose-800 ring-1 ring-rose-200" role="alert">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
        <span>{{ __('कृपया नीचे लाल निशान वाली जानकारी सुधारें।') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-7" id="regform" data-once>
    @csrf

    {{-- 1: about you --}}
    <fieldset class="space-y-4">
        <legend class="mb-3 flex items-center gap-2 text-base font-bold text-ink-900"><span class="grid h-8 w-8 place-items-center rounded-full bg-kumkum-700 text-xs text-white ring-2 ring-gold-300">1</span>{{ __('आपके बारे में') }}</legend>
        <div>
            <label for="name" class="field-label">{{ __('पूरा नाम') }}</label>
            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="input min-h-[3.25rem] text-base {{ $err('name') }}">
            @error('name')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="phone" class="field-label">{{ __('मोबाइल नंबर') }}</label>
            <p id="phone-help" class="mb-1.5 text-sm text-ink-700">{{ __('10 अंकों का मोबाइल नंबर') }}</p>
            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="14" value="{{ old('phone') }}" required autocomplete="tel" class="input min-h-[3.25rem] text-base {{ $err('phone') }}" placeholder="98XXXXXXXX">
            @error('phone')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="field-label">{{ __('ईमेल (ज़रूरी नहीं)') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="input min-h-[3.25rem] text-base {{ $err('email') }}">
            @error('email')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
        </div>
    </fieldset>

    {{-- 2: security --}}
    <fieldset class="space-y-4 border-t border-ink-100 pt-6">
        <legend class="mb-3 flex items-center gap-2 text-base font-bold text-ink-900"><span class="grid h-8 w-8 place-items-center rounded-full bg-kumkum-700 text-xs text-white ring-2 ring-gold-300">2</span>{{ __('पासवर्ड') }}</legend>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="field-label">{{ __('पासवर्ड') }}</label>
                <p class="mb-1.5 text-sm text-ink-700">{{ __('कम से कम 8 अक्षर') }}</p>
                <div class="relative">
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="input min-h-[3.25rem] pr-12 text-base {{ $err('password') }}">
                    <button type="button" data-toggle-pass="password" aria-pressed="false" aria-label="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" title="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" class="absolute right-1.5 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full text-ink-600 hover:bg-ink-50">
                        <svg data-eye-on viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg data-eye-off viewBox="0 0 24 24" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A9.7 9.7 0 0112 5c6 0 10 7 10 7a17 17 0 01-3.2 3.9M6.6 6.6A17 17 0 002 12s4 7 10 7a9.6 9.6 0 004.4-1.1M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                    </button>
                </div>
                <div class="pub-strength mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100" aria-hidden="true"><i id="pwbar" style="width:0"></i></div>
                <p id="pwtxt" class="mt-1 text-sm text-ink-800" aria-live="polite"></p>
                @error('password')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="field-label">{{ __('पासवर्ड दोबारा') }}</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input min-h-[3.25rem] pr-12 text-base">
                    <button type="button" data-toggle-pass="password_confirmation" aria-pressed="false" aria-label="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" title="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" class="absolute right-1.5 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full text-ink-600 hover:bg-ink-50">
                        <svg data-eye-on viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg data-eye-off viewBox="0 0 24 24" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A9.7 9.7 0 0112 5c6 0 10 7 10 7a17 17 0 01-3.2 3.9M6.6 6.6A17 17 0 002 12s4 7 10 7a9.6 9.6 0 004.4-1.1M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                    </button>
                </div>
                <p id="pwmatch" class="mt-2 text-sm font-bold" aria-live="polite"></p>
            </div>
        </div>
    </fieldset>

    {{-- 3: samaj & place --}}
    <fieldset class="space-y-4 border-t border-ink-100 pt-6">
        <legend class="mb-3 flex items-center gap-2 text-base font-bold text-ink-900"><span class="grid h-8 w-8 place-items-center rounded-full bg-kumkum-700 text-xs text-white ring-2 ring-gold-300">3</span>{{ __('समाज व स्थान की जानकारी') }}</legend>
        <div>
            <label for="gotra" class="field-label">{{ __('गोत्र / उपजाति') }}</label>
            <p class="mb-1.5 text-sm text-ink-700">{{ __('अपनी जाति/उपजाति') }}</p>
            <input id="gotra" name="gotra" value="{{ old('gotra') }}" class="input min-h-[3.25rem] text-base {{ $err('gotra') }}">
            @error('gotra')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="state" class="field-label">{{ __('राज्य') }}</label>
                <input id="state" name="state" value="{{ old('state') }}" required class="input min-h-[3.25rem] text-base {{ $err('state') }}">
                @error('state')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="district" class="field-label">{{ __('ज़िला') }}</label>
                <input id="district" name="district" value="{{ old('district') }}" required class="input min-h-[3.25rem] text-base {{ $err('district') }}">
                @error('district')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="city" class="field-label">{{ __('शहर / गाँव') }}</label>
                <input id="city" name="city" value="{{ old('city') }}" required class="input min-h-[3.25rem] text-base {{ $err('city') }}">
                @error('city')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="area" class="field-label">{{ __('क्षेत्र / मोहल्ला') }}</label>
                <input id="area" name="area" value="{{ old('area') }}" class="input min-h-[3.25rem] text-base {{ $err('area') }}">
                @error('area')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
            </div>
        </div>
    </fieldset>

    {{-- 4: consent --}}
    <fieldset class="border-t border-ink-100 pt-6">
        <legend class="mb-3 flex items-center gap-2 text-base font-bold text-ink-900"><span class="grid h-8 w-8 place-items-center rounded-full bg-kumkum-700 text-xs text-white ring-2 ring-gold-300">4</span>{{ __('सहमति') }}</legend>
        <label class="flex items-start gap-3 rounded-2xl bg-ink-50/70 p-4 text-base text-ink-900 ring-1 {{ $errors->has('consent') ? 'ring-rose-300' : 'ring-ink-100' }}">
            <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-0.5 h-6 w-6 shrink-0 rounded border-ink-300 text-kumkum-600">
            <span>{{ __('मैं') }} <a href="{{ route('page.terms') }}" target="_blank" class="font-semibold underline">{{ __('नियम व शर्तें') }}</a> {{ __('और') }} <a href="{{ route('page.privacy') }}" target="_blank" class="font-semibold underline">{{ __('गोपनीयता नीति') }}</a> {{ __('से सहमत हूँ और अपनी जानकारी के उपयोग की अनुमति देता/देती हूँ।') }}</span>
        </label>
        @error('consent')<p class="field-error flex items-center gap-1">{!! $warnIcon !!}{{ $message }}</p>@enderror
    </fieldset>

    <button type="submit" class="btn-gold min-h-[3.25rem] w-full py-3.5 text-base">{{ __('रजिस्टर करें') }}
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14m-6-6l6 6-6 6"/></svg>
    </button>
</form>

<div class="mt-6 rounded-2xl bg-ink-50 px-4 py-3 text-center text-base text-ink-900 ring-1 ring-ink-100">{{ __('पहले से अकाउंट है?') }} <a href="{{ route('login') }}" class="font-semibold text-kumkum-700 underline">{{ __('लॉगिन') }}</a></div>

<script>
(function () {
    var form = document.getElementById('regform'); if (!form) return;
    var req = Array.prototype.slice.call(form.querySelectorAll('[required], [name=consent]'));
    var bar = document.getElementById('regbar'), pct = document.getElementById('regpct');
    var pw = document.getElementById('password'), pc = document.getElementById('password_confirmation');
    var pwbar = document.getElementById('pwbar'), pwtxt = document.getElementById('pwtxt'), pwm = document.getElementById('pwmatch');
    function progress() {
        var done = req.filter(function (el) { return el.type === 'checkbox' ? el.checked : el.value.trim() !== ''; }).length;
        var p = Math.round(done / req.length * 100);
        bar.style.width = p + '%'; pct.textContent = p + '%';
    }
    function strength() {
        var v = pw.value, s = 0;
        if (v.length >= 8) s++; if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
        if (!v) s = 0;
        var cols = ['', '#c8273a', '#ee9e0a', '#f9b81c', '#2f7d57'], txt = ['', 'कमज़ोर', 'ठीक-ठाक', 'अच्छा', 'मज़बूत'];
        pwbar.style.width = (s * 25) + '%'; pwbar.style.backgroundColor = cols[s]; pwtxt.textContent = v ? txt[s] : '';
    }
    function match() {
        if (!pc.value) { pwm.textContent = ''; return; }
        var ok = pc.value === pw.value;
        pwm.textContent = ok ? '✓ मिल गया' : '✗ मेल नहीं खाते';
        pwm.style.color = ok ? '#2f7d57' : '#b8262b';
    }
    form.addEventListener('input', function () { progress(); strength(); match(); });
    form.addEventListener('change', progress);
    progress(); strength(); match();
})();
</script>
@endsection
