@extends('layouts.app')
@section('title', __('किसी और के लिए रिश्ता देखना'))
@section('content')
@use('App\Support\BiodataOptions', 'O')
@php
    $v = fn ($k) => old($k, $profile->{$k} ?? null);
    $action = $mode === 'edit' ? route('looking-for.update') : route('looking-for.store');
@endphp
<div class="mx-auto max-w-xl">
    <div class="mb-4">
        <h1 class="section-title !text-2xl sm:!text-3xl">{{ __('किसी और के लिए रिश्ता देख रहे हैं') }}</h1>
        <p class="mt-1 text-base text-ink-800">{{ __('बताइए कि आप किसके लिए रिश्ता देख रहे हैं और कैसा रिश्ता चाहिए।') }}</p>
        <p class="mt-1 text-sm text-ink-700">{{ __('इससे आप सभी बायोडाटा पूरी जानकारी के साथ देख सकेंगे। पसंद भेजने के लिए अपना बायोडाटा बनाना होगा।') }}</p>
    </div>

    <form method="POST" action="{{ $action }}" class="card-pad space-y-5">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div>
            <label class="field-label" for="for_whom">{{ __('आप किसके लिए रिश्ता देख रहे हैं?') }} <span class="text-rose-600">*</span></label>
            <select id="for_whom" name="for_whom" class="input" required>
                <option value="">{{ __('— चुनिए —') }}</option>
                @foreach(\App\Models\LookingForProfile::FOR_WHOM as $k => $l)<option value="{{ $k }}" @selected($v('for_whom') === $k)>{{ __($l) }}</option>@endforeach
            </select>
            @error('for_whom')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="field-label">{{ __('कैसा रिश्ता चाहिए?') }} <span class="text-rose-600">*</span></span>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['bride' => __('दुल्हन चाहिए (लड़की)'), 'groom' => __('दूल्हा चाहिए (लड़का)')] as $k => $l)
                    <label class="flex min-h-14 cursor-pointer items-center justify-center rounded-xl bg-ink-50 px-3 text-center text-base font-bold text-ink-800 ring-2 ring-ink-100 has-[:checked]:bg-kumkum-600 has-[:checked]:text-white has-[:focus-visible]:ring-kumkum-600">
                        <input type="radio" name="looking_for" value="{{ $k }}" class="sr-only" @checked($v('looking_for') === $k) required>{{ $l }}
                    </label>
                @endforeach
            </div>
            @error('looking_for')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="field-label">{{ __('कितनी उम्र चाहिए?') }}</p>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-sm font-semibold text-ink-800" for="age_min">{{ __('कम से कम') }}</label><input id="age_min" type="number" name="age_min" min="18" max="80" inputmode="numeric" value="{{ $v('age_min') }}" class="input">@error('age_min')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-semibold text-ink-800" for="age_max">{{ __('ज़्यादा से ज़्यादा') }}</label><input id="age_max" type="number" name="age_max" min="18" max="80" inputmode="numeric" value="{{ $v('age_max') }}" class="input">@error('age_max')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
        </div>

        <div>
            <label class="field-label" for="education_level">{{ __('कम से कम कितनी पढ़ाई?') }}</label>
            <select id="education_level" name="education_level" class="input"><option value="">{{ __('कोई भी') }}</option>
                @foreach(O::educationLevels() as $k => $l)<option value="{{ $k }}" @selected($v('education_level') === $k)>{{ $l }}</option>@endforeach</select>
            @error('education_level')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="field-label" for="district">{{ __('कौन सा ज़िला?') }}</label>
            <select id="district" name="district" class="input"><option value="">{{ __('कोई भी') }}</option>
                @foreach(O::districtLabels() as $k => $l)<option value="{{ $k }}" @selected($v('district') === $k)>{{ $l }}</option>@endforeach</select>
            @error('district')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="field-label" for="note">{{ __('कुछ और बताना चाहें तो लिखिए (ज़रूरी नहीं)') }}</label>
            <textarea id="note" name="note" rows="3" maxlength="500" class="input">{{ $v('note') }}</textarea>
            @error('note')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <button class="btn-gold w-full !min-h-14 !text-lg">{{ $mode === 'edit' ? __('बदलाव सेव करें') : __('सेव करें और रिश्ते देखें') }}</button>
    </form>

    @if($mode === 'edit')
        <form method="POST" action="{{ route('looking-for.destroy') }}" class="mt-3 text-center" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('यह जानकारी हटा दें?')) }})">
            @csrf @method('DELETE')
            <button class="inline-flex min-h-12 items-center px-4 text-base font-bold text-rose-700 underline">{{ __('हटाएँ') }}</button>
        </form>
    @endif

    <p class="mt-4 text-center text-base text-ink-800">{!! __('या :link।', ['link' => '<a class="font-bold underline" href="'.e(route('biodata.create')).'">'.e(__('अपना बायोडाटा बनाएँ')).'</a>']) !!}</p>
</div>
@endsection
