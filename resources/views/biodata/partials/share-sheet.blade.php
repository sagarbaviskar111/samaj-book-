@once
@push('scripts')
<div id="shareSheet" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-labelledby="shareTitle">
    <div data-share-close class="absolute inset-0 bg-black/50"></div>
    <div class="absolute inset-x-0 bottom-0 mx-auto max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-cream p-5 shadow-2xl sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 sm:rounded-3xl">
        <div class="flex items-start justify-between gap-3">
            <h2 id="shareTitle" class="font-display text-xl font-bold text-ink-900">{{ __('बायोडाटा शेयर करें') }}</h2>
            <button type="button" data-share-close id="shareX" class="grid h-12 w-12 place-items-center rounded-full text-3xl text-ink-700 hover:bg-ink-50" aria-label="{{ __('बंद करें') }}">&times;</button>
        </div>

        <div id="shareBody" class="mt-3 space-y-4">
            <p id="shareOff" class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm text-rose-700 ring-1 ring-rose-100">{{ __('लिंक अभी बंद है। चालू करने के लिए नीचे "लिंक चालू करें" दबाइए।') }}</p>

            <div>
                <label for="shareUrl" class="field-label">{{ __('बायोडाटा का लिंक') }}</label>
                <div class="mt-1 flex gap-2">
                    <input id="shareUrl" type="text" readonly class="input min-w-0 flex-1" value="" aria-describedby="shareHelp">
                    <button type="button" id="shareCopy" class="btn-primary !min-h-11 shrink-0">{{ __('कॉपी करें') }}</button>
                </div>
                <p id="shareCopied" class="mt-1 hidden text-sm font-semibold text-emerald-700" role="status">{{ __('लिंक कॉपी हो गया') }}</p>
                <p id="shareHelp" class="mt-2 text-sm text-ink-700">{{ __('यह लिंक सिर्फ़ मंज़ूर सदस्यों के लिए खुलता है (लॉगिन ज़रूरी)। PDF किसी को भी भेजा जा सकता है।') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <a id="shareWa" href="#" target="_blank" rel="noopener" class="btn-gold !min-h-11">WhatsApp</a>
                <button type="button" id="shareNative" class="btn-ghost hidden !min-h-11">{{ __('और तरीक़ों से शेयर करें') }}</button>
            </div>

            <div class="rounded-2xl bg-white p-3 ring-1 ring-ink-100">
                <label class="flex min-h-11 items-center gap-3 text-sm text-ink-800">
                    <input id="shareContact" type="checkbox" checked class="h-5 w-5 rounded border-ink-300">
                    <span>{{ __('PDF में फ़ोन नंबर दिखाएँ') }}</span>
                </label>
                <a id="sharePdf" href="#" class="btn-primary mt-2 w-full !min-h-11">&#128196; {{ __('PDF डाउनलोड करें') }}</a>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-ink-100 pt-3">
                <button type="button" id="shareToggle" class="btn-ghost btn-sm !min-h-11"></button>
                <button type="button" id="shareReset" class="btn-ghost btn-sm !min-h-11">{{ __('नया लिंक बनाएँ') }}</button>
            </div>
            <p id="shareErr" class="hidden text-sm text-rose-700" role="alert"></p>
        </div>
    </div>
</div>

<script>
(function () {
    var sheet = document.getElementById('shareSheet'); if (!sheet) return;
    var $ = function (id) { return document.getElementById(id); };
    var csrf = @json(csrf_token());
    var cur = null, last = null, data = null;

    function q(t) { return sheet.querySelectorAll(t); }
    function open(btn) {
        cur = btn; last = btn; $('shareErr').classList.add('hidden');
        sheet.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        $('shareUrl').value = '…';
        call(btn.dataset.shareEndpoint, 'POST');
        setTimeout(function () { $('shareX').focus(); }, 30);
    }
    function close() { sheet.classList.add('hidden'); document.body.style.overflow = ''; if (last) last.focus(); }
    function err(m) { var e = $('shareErr'); e.textContent = m; e.classList.remove('hidden'); }
    function call(url, method) {
        fetch(url, {method: method, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(render).catch(function () { err(@json(__('कुछ गड़बड़ हो गई, दोबारा कोशिश कीजिए।'))); });
    }
    function render(d) {
        data = d;
        $('shareUrl').value = d.url;
        $('shareWa').href = 'https://wa.me/?text=' + encodeURIComponent(d.message);
        $('shareOff').classList.toggle('hidden', d.enabled);
        $('shareToggle').textContent = d.enabled ? @json(__('लिंक बंद करें')) : @json(__('लिंक चालू करें'));
        pdf();
        $('shareNative').classList.toggle('hidden', !navigator.share);
    }
    function pdf() { if (cur) $('sharePdf').href = cur.dataset.sharePdf + '?contact=' + ($('shareContact').checked ? '1' : '0'); }

    document.addEventListener('click', function (e) {
        var o = e.target.closest('[data-share-open]'); if (o) { e.preventDefault(); open(o); return; }
        if (e.target.closest('[data-share-close]')) close();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !sheet.classList.contains('hidden')) close(); });
    $('shareContact').addEventListener('change', pdf);
    $('shareCopy').addEventListener('click', function () {
        var f = $('shareUrl'), done = function () { var c = $('shareCopied'); c.classList.remove('hidden'); setTimeout(function () { c.classList.add('hidden'); }, 2500); };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(f.value).then(done, fallback); } else { fallback(); }
        function fallback() { f.focus(); f.select(); f.setSelectionRange(0, 9999); try { document.execCommand('copy'); done(); } catch (x) {} }
    });
    $('shareNative').addEventListener('click', function () { if (data && navigator.share) navigator.share({title: 'SamajBook', text: data.message}).catch(function () {}); });
    $('shareReset').addEventListener('click', function () { if (confirm(@json(__('पुराना लिंक काम करना बंद कर देगा। नया लिंक बनाएँ?')))) call(cur.dataset.shareReset, 'POST'); });
    $('shareToggle').addEventListener('click', function () { call(cur.dataset.shareToggle, 'POST'); });
    // keep Tab inside the dialog
    sheet.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return;
        var f = [].slice.call(q('a[href],button:not([disabled]),input:not([disabled])')).filter(function (x) { return x.offsetParent !== null; });
        if (!f.length) return; var a = f[0], z = f[f.length - 1];
        if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); } else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
    });
})();
</script>
@endpush
@endonce
