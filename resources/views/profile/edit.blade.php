@extends('layouts.app')
@section('wide', '1')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <div>
        <a href="{{ route('profile.show') }}" class="text-base font-bold text-ink-700 underline">&larr; {{ __('वापस') }}</a>
        <h1 class="section-title mt-1">{{ __('प्रोफ़ाइल एडिट') }}</h1>
    </div>
</div>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-5 lg:grid-cols-[18rem_1fr] lg:items-start">
    @csrf

    {{-- photo --}}
    <aside class="card-pad text-center lg:sticky lg:top-20">
        <div class="flex justify-center" id="avatar-preview">
            @include('profile._avatar', ['user' => $user, 'size' => 'h-28 w-28', 'text' => 'text-5xl'])
        </div>
        <p class="mt-3 font-display text-lg font-bold text-ink-900">{{ $user->name }}</p>
        <p class="text-base text-ink-700">{{ $user->phone }}</p>

        <label for="avatar" class="btn-ghost mt-4 cursor-pointer">{{ __('फ़ोटो बदलें') }}</label>
        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
               onchange="const f=this.files[0]; if(f){ const i=document.querySelector('#avatar-preview img'); if(i){ i.src=URL.createObjectURL(f);} else { document.getElementById('avatar-name').textContent=f.name; } }">
        <p id="avatar-name" class="mt-2 break-all text-sm text-ink-700">{{ __('JPG, PNG या WebP फ़ोटो, 2 MB तक') }}</p>
        @error('avatar')<p class="field-error">{{ $message }}</p>@enderror
    </aside>

    <div class="space-y-5">
        {{-- details --}}
        <section class="card-pad">
            <h2 class="section-title !text-xl mb-4">{{ __('आपकी जानकारी') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach([['name', 'पूरा नाम', true, 'sm:col-span-2', ''], ['email', 'ईमेल (ज़रूरी नहीं)', false, '', ''], ['gotra', 'गोत्र / उपजाति', false, '', 'अपनी जाति/उपजाति'], ['state', 'राज्य', true, '', ''], ['district', 'ज़िला', true, '', ''], ['city', 'शहर / गाँव', true, '', ''], ['area', 'क्षेत्र / मोहल्ला', false, '', '']] as [$f, $label, $req, $span, $help])
                    <div class="{{ $span }}">
                        <label for="{{ $f }}" class="field-label">{{ __($label) }}@if($req) <span class="text-rose-500">*</span>@endif</label>
                        @if(in_array($f, ['state', 'district'], true))
                            @php $opts = $f === 'state' ? \App\Support\BiodataOptions::states() : \App\Support\BiodataOptions::districtLabels(); @endphp
                            <select id="{{ $f }}" name="{{ $f }}" required class="input">
                                <option value="">{{ __('चुनें') }}</option>
                                @foreach($opts as $k => $l)<option value="{{ $k }}" @selected(old($f, $user->$f) === $k)>{{ $l }}</option>@endforeach
                            </select>
                        @else
                        <input id="{{ $f }}" name="{{ $f }}" type="{{ $f === 'email' ? 'email' : 'text' }}" value="{{ old($f, $user->$f) }}" @required($req) class="input">
                        @endif
                        @if($help)<p class="mt-1 text-sm text-ink-700">{{ __($help) }}</p>@endif
                        @error($f)<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-base text-ink-700">{{ __('फ़ोन नंबर (:phone) आपकी लॉगिन पहचान है, इसलिए यहाँ से नहीं बदल सकते। बदलना हो तो एडमिन से बात करें।', ['phone' => $user->phone]) }}</p>
        </section>

        {{-- password --}}
        <section class="card-pad" id="password-section">
            <h2 class="section-title !text-xl">{{ __('पासवर्ड बदलें') }}</h2>
            <p class="mb-4 text-base text-ink-700">{{ __('ज़रूरी नहीं। बदलना हो तभी भरें।') }}</p>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="current_password" class="field-label">{{ __('मौजूदा पासवर्ड') }}</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="input">
                    @error('current_password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="field-label">{{ __('नया पासवर्ड') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="input">
                    <p class="mt-1 text-sm text-ink-700">{{ __('कम से कम 8 अक्षर') }}</p>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="field-label">{{ __('नया पासवर्ड दोबारा') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input">
                </div>
            </div>
        </section>

        <div class="flex gap-2 lg:justify-end">
            <a href="{{ route('profile.show') }}" class="btn-ghost">{{ __('रद्द करें') }}</a>
            <button type="submit" class="btn-primary min-h-12 flex-1 text-lg lg:flex-none lg:px-10">{{ __('सेव करें') }}</button>
        </div>
    </div>
</form>
@endsection
