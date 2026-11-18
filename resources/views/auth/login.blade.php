@extends('auth.layout')
@section('card')
<div class="mb-6 flex items-center gap-3">
    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-kumkum-700 to-kumkum-500 text-white shadow-glow ring-2 ring-gold-300"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg></span>
    <div>
        <h1 class="font-display text-2xl font-bold leading-tight text-ink-900 sm:text-3xl">{{ __('लॉगिन करें') }}</h1>
        <p class="text-base text-ink-800">{{ __('अपने फ़ोन नंबर और पासवर्ड से अंदर आएँ') }}</p>
    </div>
</div>

@include('auth._flash')
@if ($errors->any())
    <div class="mb-4 flex items-start gap-2 rounded-xl bg-rose-50 px-3.5 py-3 text-sm font-medium text-rose-800 ring-1 ring-rose-200" role="alert">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
        <span>{{ __('कृपया नीचे दी गई गलतियाँ सुधारें।') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-5" data-once>
    @csrf
    <div>
        <label for="phone" class="field-label">{{ __('मोबाइल नंबर') }}</label>
        <p id="phone-help" class="mb-1.5 text-sm text-ink-700">{{ __('10 अंकों का मोबाइल नंबर') }}</p>
        <div class="relative">
            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg>
            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="14" autocomplete="tel" value="{{ old('phone') }}" required autofocus class="input min-h-[3.25rem] pl-11 text-base @error('phone') border-rose-400 ring-4 ring-rose-100 @enderror" placeholder="98XXXXXXXX" @error('phone') aria-invalid="true" @enderror>
        </div>
        @error('phone')<p class="field-error flex items-center gap-1"><svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2zm0-4h-2V7h2z"/></svg>{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="field-label">{{ __('पासवर्ड') }}</label>
        <div class="relative">
            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z"/></svg>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="input min-h-[3.25rem] pl-11 pr-12 text-base @error('password') border-rose-400 ring-4 ring-rose-100 @enderror" @error('password') aria-invalid="true" @enderror>
            <button type="button" data-toggle-pass="password" aria-pressed="false" aria-label="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" title="{{ __('पासवर्ड दिखाएँ/छिपाएँ') }}" class="absolute right-1.5 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full text-ink-600 hover:bg-ink-50">
                <svg data-eye-on viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg data-eye-off viewBox="0 0 24 24" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A9.7 9.7 0 0112 5c6 0 10 7 10 7a17 17 0 01-3.2 3.9M6.6 6.6A17 17 0 002 12s4 7 10 7a9.6 9.6 0 004.4-1.1M9.9 9.9a3 3 0 004.2 4.2"/></svg>
            </button>
        </div>
        @error('password')<p class="field-error flex items-center gap-1"><svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2zm0-4h-2V7h2z"/></svg>{{ $message }}</p>@enderror
    </div>
    <label class="flex min-h-[2.75rem] cursor-pointer items-center gap-3 text-base text-ink-900">
        <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-ink-300 text-kumkum-600 focus:ring-gold-300"> {{ __('मुझे याद रखें') }}
    </label>
    <button type="submit" class="btn-primary min-h-[3.25rem] w-full py-3.5 text-base">{{ __('लॉगिन') }}
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14m-6-6l6 6-6 6"/></svg>
    </button>
</form>

<div class="mt-6 rounded-2xl bg-gold-50 px-4 py-3 text-center text-sm text-ink-800 ring-1 ring-gold-200">{{ __('नए हैं?') }} <a href="{{ route('register') }}" class="font-semibold text-kumkum-700 underline">{{ __('रजिस्टर करें') }}</a></div>
@endsection

@if(\App\Support\DevLogin::enabled())
@section('below')
<div class="mt-5 rounded-3xl bg-ink-950/40 p-4 ring-1 ring-white/15 backdrop-blur">
    <div class="mb-3 flex items-center justify-between">
        <p class="text-sm font-bold text-gold-300">Dev quick login</p>
        <span class="rounded-full bg-emerald-400/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300">local only</span>
    </div>
    @include('partials.dev-accounts', ['variant' => 'panel'])
</div>
@endsection
@endif
