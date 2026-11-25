{{-- Floating "Install app" banner + iOS hint. Include once in layouts: <x-install-prompt /> --}}
<div id="install-banner" class="fixed inset-x-3 bottom-28 z-50 mx-auto hidden max-w-md items-center gap-3 overflow-hidden rounded-3xl bg-gradient-to-br from-kumkum-950 via-kumkum-800 to-kumkum-600 p-3 pr-2 text-white shadow-2xl ring-1 ring-white/15 lg:bottom-6" role="dialog" aria-label="{{ __('ऐप इंस्टॉल करें') }}" style="animation: mb-rise .45s cubic-bezier(.2,.7,.2,1) both">
    <span class="pointer-events-none absolute -right-6 -top-8 h-24 w-24 rounded-full bg-gold-400/25 blur-2xl"></span>
    <img src="/icons/icon-192.png" alt="" class="relative h-12 w-12 rounded-2xl shadow-lg ring-2 ring-white/20">
    <div class="relative min-w-0 flex-1">
        <p class="text-sm font-semibold">{{ __('समाजBook ऐप इंस्टॉल करें') }}</p>
        <p id="install-text" class="text-sm leading-snug text-ink-100">{{ __('फ़ोन की होम स्क्रीन से सीधे खोलें।') }}</p>
    </div>
    <button type="button" id="install-btn" class="btn-gold relative shrink-0">{{ __('इंस्टॉल करें') }}</button>
    <button type="button" id="install-close" class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full text-2xl text-ink-100 transition hover:bg-white/10" aria-label="{{ __('बंद करें') }}" title="{{ __('बंद करें') }}">&times;</button>
</div>
<script>
(function () {
    var KEY = 'sb_install_dismissed', b = document.getElementById('install-banner');
    if (!b) { return; }
    try { if (localStorage.getItem(KEY)) { return; } } catch (e) {}
    function render(s) {
        if (s.installed) { b.classList.add('hidden'); b.classList.remove('flex'); return; }
        var show = s.canInstall || s.ios;
        if (s.ios && !s.canInstall) {
            document.getElementById('install-text').textContent = @json(__('Share दबाएँ, फिर "Add to Home Screen" चुनें।'));
            document.getElementById('install-btn').classList.add('hidden');
        }
        b.classList.toggle('hidden', !show); b.classList.toggle('flex', show);
    }
    function init() { if (window.SamajPWA) { window.SamajPWA.onChange(render); } else { setTimeout(init, 300); } }
    init();
    document.getElementById('install-btn').addEventListener('click', function () { window.SamajPWA.install(); });
    document.getElementById('install-close').addEventListener('click', function () {
        try { localStorage.setItem(KEY, '1'); } catch (e) {}
        b.classList.add('hidden'); b.classList.remove('flex');
    });
})();
</script>
