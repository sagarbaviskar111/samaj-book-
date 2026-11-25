@extends('layouts.app')
@section('content')
@php($me = auth()->user())
<div class="mx-auto max-w-xl">
    <a href="{{ route('home') }}" class="mb-2 inline-flex items-center gap-1 text-sm font-semibold text-ink-700 hover:text-ink-700">&larr; {{ __('वापस') }}</a>
    <h1 class="section-title mb-4">{{ __('नई पोस्ट') }}</h1>
    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="card card-pad space-y-5">
        @csrf
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-gradient-to-tr from-gold-400 via-kumkum-500 to-kumkum-700 p-[2px]">@include('feed.partials.avatar', ['u' => $me])</span>
            <div>
                <p class="text-sm font-semibold text-ink-900">{{ $me->name }}</p>
                <p class="text-xs text-ink-700">{{ $me->city ?: __('समाज के सदस्य') }}</p>
            </div>
        </div>

        <div>
            <label class="sr-only" for="body">{{ __('आप क्या साझा करना चाहते हैं?') }}</label>
            <textarea id="body" name="body" rows="5" maxlength="1500" class="input !border-0 !bg-ink-50/60 !text-base !shadow-none" placeholder="{{ __('यहाँ लिखें...') }}">{{ old('body') }}</textarea>
            <p class="mt-1 text-right text-sm text-ink-600"><span id="body-count">0</span>/1500</p>
            @error('body')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="field-label" for="image">{{ __('फ़ोटो (ज़रूरी नहीं, 5 MB तक)') }}</label>
            <label for="image" class="group relative flex min-h-[8rem] cursor-pointer flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border-2 border-dashed border-ink-200 bg-ink-50/50 p-4 text-center text-sm text-ink-700 transition hover:border-gold-400 hover:bg-gold-50/40">
                <img id="preview" alt="{{ __('चुनी हुई फ़ोटो') }}" class="hidden max-h-72 w-full rounded-xl object-cover">
                <span id="preview-icon" class="grid h-12 w-12 place-items-center rounded-full bg-gold-100 text-gold-600 transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.25 2.25m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                </span>
                <span id="preview-hint" class="font-medium">{{ __('फ़ोटो जोड़ने के लिए यहाँ दबाएँ') }}</span>
            </label>
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
            <button type="button" id="preview-clear" class="mt-2 hidden text-base font-bold text-rose-700 underline">{{ __('फ़ोटो हटाएँ') }}</button>
            @error('image')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="field-label">{{ __('इसे कौन देख सकता है?') }}</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['members' => __('सभी सदस्य'), 'city' => __('सिर्फ़ मेरा शहर')] as $v => $l)
                    <label class="cursor-pointer">
                        <input type="radio" name="visibility" value="{{ $v }}" class="peer sr-only" @checked(old('visibility', 'members') === $v)>
                        <span class="block rounded-xl border border-ink-200 bg-white px-4 py-3 text-center text-base font-bold text-ink-700 transition peer-checked:border-kumkum-600 peer-checked:bg-ink-50 peer-checked:text-ink-800 peer-checked:ring-4 peer-checked:ring-ink-100 peer-focus-visible:ring-4 peer-focus-visible:ring-ink-200">{{ $l }}</span>
                    </label>
                @endforeach
            </div>
            @error('visibility')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <a href="{{ route('home') }}" class="btn-ghost flex-1 text-center">{{ __('रद्द करें') }}</a>
            <button class="btn-primary min-h-12 flex-1 text-lg">{{ __('पोस्ट करें') }}</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
(function () {
    var inp = document.getElementById('image'), p = document.getElementById('preview'), hint = document.getElementById('preview-hint'),
        icon = document.getElementById('preview-icon'), clr = document.getElementById('preview-clear'),
        body = document.getElementById('body'), cnt = document.getElementById('body-count');
    inp.addEventListener('change', function () {
        var f = this.files[0];
        if (!f) { return; }
        p.src = URL.createObjectURL(f); p.classList.remove('hidden');
        icon.classList.add('hidden'); clr.classList.remove('hidden');
        hint.textContent = f.name;
    });
    clr.addEventListener('click', function () {
        inp.value = ''; p.classList.add('hidden'); p.removeAttribute('src');
        icon.classList.remove('hidden'); clr.classList.add('hidden'); hint.textContent = @json(__('फ़ोटो जोड़ने के लिए यहाँ दबाएँ'));
    });
    function count() { cnt.textContent = body.value.length; }
    body.addEventListener('input', count); count();
})();
</script>
@endpush
