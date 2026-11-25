@php($max = \App\Support\BiodataImages::MAX_PHOTOS)
@php($count = $biodata->photos->count())
<section class="card-pad mx-auto max-w-3xl" id="photos">
    <div class="flex items-start gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-gold-500 to-rose-brand text-white shadow-lg shadow-gold-500/30">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l2-2h6l2 2h3v12H4V7Zm8 9.500a3.500 3.500 0 1 0 0-7 3.500 3.500 0 0 0 0 7Z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h2 class="font-display text-xl font-bold leading-tight text-ink-900">{{ __('फ़ोटो') }} <span class="text-base font-normal text-ink-700">({{ $count }}/{{ $max }})</span></h2>
            <p class="text-sm text-ink-600 sm:text-sm">{{ __('JPG, PNG या WebP फ़ोटो, हर फ़ोटो 4 MB तक। फ़ोटो की लोकेशन अपने आप हट जाती है।') }}</p>
        </div>
    </div>
    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-ink-100"><div class="h-full rounded-full bg-gradient-to-r from-kumkum-500 to-gold-400" style="width: {{ min(100, $count / max(1, $max) * 100) }}%"></div></div>

    @if($biodata->photos->isNotEmpty())
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($biodata->photos as $p)
                <div class="bd-fade-up overflow-hidden rounded-2xl bg-white shadow-soft ring-1 {{ $p->is_primary ? 'ring-2 ring-gold-400' : 'ring-ink-100' }}" style="--i: {{ $loop->index }}">
                    <div class="relative">
                        <img src="{{ route('biodata.photo', [$p, 'size' => 'thumb']) }}" alt="{{ __('फ़ोटो :n', ['n' => $loop->iteration]) }}" class="aspect-[3/4] w-full object-cover" loading="lazy">
                        @if($p->is_primary)<span class="badge-warn absolute left-2 top-2 !bg-gold-100 shadow">&#9733; {{ __('मुख्य फ़ोटो') }}</span>@endif
                    </div>
                    <div class="flex divide-x divide-ink-100 border-t border-ink-100 bg-white text-sm font-semibold">
                        @unless($p->is_primary)
                            <form method="POST" action="{{ route('biodata.photos.primary', $p) }}" class="flex-1">@csrf<button class="min-h-12 w-full px-2 text-ink-700 hover:bg-ink-50">{{ __('मुख्य बनाएँ') }}</button></form>
                        @endunless
                        <form method="POST" action="{{ route('biodata.photos.destroy', $p) }}" class="flex-1" onsubmit="return confirm({{ Js::from(__('यह फ़ोटो हटाएँ?')) }})">@csrf @method('DELETE')<button class="min-h-12 w-full px-2 text-rose-600 hover:bg-rose-50">{{ __('हटाएँ') }}</button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($count < $max)
        <form method="POST" action="{{ route('biodata.photos.store', $biodata) }}" enctype="multipart/form-data" class="mt-4" id="photo-form">
            @csrf
            <label id="photo-drop" class="bd-drop relative flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl px-4 py-10 text-center">
                <svg class="h-10 w-10 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0-4 4m4-4 4 4M4 15v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/></svg>
                <span class="text-xl font-bold text-ink-900">&#128247; {{ __('फ़ोटो चुनें') }}</span>
                <span class="text-base text-ink-700">{{ __('यहाँ दबाइए और फ़ोन से फ़ोटो चुनिए।') }} {{ __(':n फ़ोटो और जोड़ सकते हैं।', ['n' => $max - $count]) }}</span>
                <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="{{ __('फ़ोटो चुनें') }}">
            </label>
            <div id="photo-preview" class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6"></div>
            <div id="photo-progress" class="mt-3 hidden">
                <div class="h-2 overflow-hidden rounded-full bg-ink-100"><div class="bd-skel h-full w-full !bg-gradient-to-r from-kumkum-500 to-gold-400"></div></div>
                <p class="mt-1 text-sm font-semibold text-ink-600">{{ __('अपलोड हो रहा है... कृपया रुकिए।') }}</p>
            </div>
            <div class="mt-3 flex justify-end"><button class="btn-primary !min-h-12" id="photo-btn">{{ __('फ़ोटो अपलोड करें') }}</button></div>
        </form>
        @error('photos')<p class="field-error">{{ $message }}</p>@enderror
        @error('photos.*')<p class="field-error">{{ $message }}</p>@enderror
        <script>
        (function () {
            var form = document.getElementById('photo-form'); if (!form) return;
            var drop = document.getElementById('photo-drop'), input = form.querySelector('input[type=file]'), prev = document.getElementById('photo-preview');
            function render() {
                prev.innerHTML = '';
                [].forEach.call(input.files, function (f) {
                    var im = document.createElement('img'); im.alt = ''; im.className = 'aspect-square w-full rounded-xl object-cover ring-1 ring-ink-100';
                    im.src = URL.createObjectURL(f); prev.appendChild(im);
                });
            }
            input.addEventListener('change', render);
            ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
            ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.remove('is-over'); }); });
            drop.addEventListener('drop', function (e) {
                e.preventDefault();
                if (e.dataTransfer && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; render(); }
            });
            form.addEventListener('submit', function () {
                document.getElementById('photo-progress').classList.remove('hidden');
                var b = document.getElementById('photo-btn'); b.disabled = true;
            });
        })();
        </script>
    @endif
</section>
