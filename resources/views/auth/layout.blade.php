@extends('layouts.base')
@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
    {{-- heritage panel --}}
    <aside class="hr-hero hr-pattern relative overflow-hidden px-6 pb-14 pt-0 text-white lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-14 lg:pb-10 lg:pt-0">
        <div class="relative z-[2] -mx-6 lg:-mx-14" aria-hidden="true"><x-brand.toran :units="10" class="h-10 sm:h-12 lg:h-14" /></div>
        <div class="pointer-events-none absolute inset-0 z-[1] overflow-hidden" aria-hidden="true">
            @foreach ([[12, 17, 0], [38, 21, 6], [64, 15, 3], [88, 19, 9]] as [$x, $t, $dl])
                <span class="hr-petal" style="--x: {{ $x }}%; --t: {{ $t }}s; --dl: -{{ $dl }}s; --dx: {{ $x % 2 ? 40 : -30 }}px"></span>
            @endforeach
        </div>

        <a href="{{ url('/') }}" class="relative z-[2] mt-3 block text-center lg:mt-8 lg:text-left" aria-label="{{ config('app.name') }}">
            <x-brand.logo size="h-12 lg:h-14" :wordmark="true" :light="true" :tagline="true" class="text-2xl lg:text-3xl" />
            <span class="mt-2 block text-base text-kumkum-100/90">{{ $tagline ?? __('अपना समाज, अपनी पहचान') }}</span>
        </a>

        <div class="relative z-[2] hidden lg:block">
            <div class="mb-6 flex items-end gap-3" aria-hidden="true">
                <x-brand.diya class="mb-4 h-10 w-12" />
                <x-brand.ganesh class="w-52 xl:w-60" :caption="false" :float="false" />
                <x-brand.kalash class="mb-2 h-24 w-auto" />
            </div>
            <p class="font-brand text-lg text-gold-200">॥ श्री गणेशाय नमः ॥</p>
            <h2 class="hr-brand mt-3 max-w-md text-3xl xl:text-4xl">स्वागत है, <span class="text-gold-300">अपनों के बीच</span></h2>
            <p class="mt-2 max-w-md text-base text-kumkum-100/85">धोबी (रजक) समाज का अपना परिवार, जहाँ रिश्ते भरोसे से जुड़ते हैं।</p>
            <ul class="mt-7 max-w-md space-y-4">
                @foreach ([
                    ['एडमिन द्वारा जाँचे हुए सदस्य', 'हर सदस्य और बायोडाटा की जाँच होती है।', 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4'],
                    ['संपर्क रहता है निजी', 'इंटरेस्ट स्वीकार होने तक नंबर छिपा रहता है।', 'M12 11c1.1 0 2-.9 2-2V7a2 2 0 10-4 0v2c0 1.1.9 2 2 2zM6 11h12v9H6z'],
                    ['समाज के लिए निःशुल्क', 'समाज के सदस्यों के लिए पूरी तरह मुफ़्त।', 'M12 8v8m-3-5h6M12 21a9 9 0 100-18 9 9 0 000 18z'],
                ] as [$t, $d, $p])
                    <li class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gold-400 text-kumkum-900"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $p }}"/></svg></span>
                        <span><span class="block text-base font-semibold">{{ $t }}</span><span class="block text-base text-kumkum-100/80">{{ $d }}</span></span>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative z-[2] mt-8 hidden items-center gap-2 text-sm text-gold-200 lg:flex">
            <x-brand.broom-gadga show="both" class="h-6 w-auto" /> संत गाडगे महाराज की प्रेरणा से &middot; &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </aside>

    {{-- form side --}}
    <main class="hr-paper relative flex flex-col px-4 pb-10 lg:min-h-screen lg:justify-center lg:overflow-y-auto lg:px-12 lg:py-12">
        <div class="mx-auto -mt-8 w-full max-w-md lg:mt-0 lg:max-w-lg">
            <div class="card pub-fade-up border-t-4 border-gold-400 p-5 shadow-glow sm:p-8">
                @yield('card')
            </div>

            @hasSection('below')
                <div class="mt-5 rounded-3xl bg-ink-900 p-1">
                    @yield('below')
                </div>
            @endif

            <div class="mt-6 flex justify-center">@include('partials.text-size')</div>
            <div class="mt-4 flex flex-wrap justify-center gap-2 text-base text-ink-800">
                @foreach(['hi' => 'हिन्दी', 'en' => 'English', 'mr' => 'मराठी'] as $code => $label)
                    <a href="{{ route('locale.switch', $code) }}" class="inline-flex min-h-11 items-center rounded-full px-4 py-1.5 ring-1 ring-gold-300 hover:bg-gold-50 {{ app()->getLocale() === $code ? 'bg-kumkum-700 font-semibold text-white ring-kumkum-700 hover:bg-kumkum-700' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <p class="mt-4 text-center text-base text-ink-800"><a href="{{ url('/') }}" class="inline-block min-h-11 py-2 underline">&larr; {{ __('होम पेज') }}</a>@if(Route::has('page.faq')) &nbsp;&middot;&nbsp; <a href="{{ route('page.faq') }}" class="inline-block min-h-11 py-2 font-bold underline">{{ __('मदद चाहिए?') }}</a>@endif</p>
        </div>
    </main>
</div>

<script>
(function () {
    // show/hide password
    document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
        var input = document.getElementById(btn.dataset.togglePass);
        if (!input) return;
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.querySelector('[data-eye-on]').classList.toggle('hidden', show);
            btn.querySelector('[data-eye-off]').classList.toggle('hidden', !show);
        });
    });
    // disable double submit
    document.querySelectorAll('form[data-once]').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('[type=submit]');
            if (b) { b.disabled = true; b.classList.add('opacity-70'); }
        });
    });
})();
</script>
@endsection
