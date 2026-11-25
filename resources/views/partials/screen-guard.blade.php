{{-- Screenshot / copy deterrents for logged-in users. Browsers cannot truly block OS screenshots,
     so this (1) blocks the easy routes, (2) hides content when the app loses focus,
     (3) tiles the viewer's identity over the whole screen so any leaked screenshot is traceable. --}}
@auth
@php $sbWho = auth()->user()->name.' · '.\Illuminate\Support\Str::mask((string) auth()->user()->phone, '*', 0, -4).' · ID '.auth()->id(); @endphp
<style>
    body { -webkit-touch-callout: none; -webkit-user-select: none; user-select: none; }
    input, textarea, [contenteditable="true"] { -webkit-user-select: text; user-select: text; }
    img { -webkit-user-drag: none; user-drag: none; }
    html.sb-hide body > *:not(#sb-wm) { filter: blur(28px) brightness(.6); pointer-events: none; }
    #sb-wm { position: fixed; inset: 0; z-index: 2147483000; pointer-events: none; background-repeat: repeat; }
    @media print { body > * { display: none !important; } body::before { content: 'Printing is disabled'; display: block; padding: 2rem; font: 20px sans-serif; } }
</style>
<script>
(function () {
    var who = @json($sbWho);
    var root = document.documentElement;

    // 1. Viewer watermark tiled over the entire screen (also lands in any screenshot).
    function watermark() {
        var c = document.createElement('canvas'); c.width = 320; c.height = 200;
        var x = c.getContext('2d'); x.translate(160, 100); x.rotate(-Math.PI / 7);
        x.font = '600 15px sans-serif'; x.textAlign = 'center';
        x.fillStyle = 'rgba(120,120,120,.22)'; x.fillText(who, 0, 0);
        x.fillStyle = 'rgba(255,255,255,.14)'; x.fillText(who, 1, 1);
        var d = document.createElement('div'); d.id = 'sb-wm';
        d.style.backgroundImage = 'url(' + c.toDataURL() + ')';
        document.body.appendChild(d);
        // re-add if someone deletes it from devtools
        new MutationObserver(function () { if (!document.getElementById('sb-wm')) document.body.appendChild(d); })
            .observe(document.body, { childList: true });
    }
    if (document.body) watermark(); else document.addEventListener('DOMContentLoaded', watermark);

    function editable(t) { return t && t.closest && t.closest('input,textarea,[contenteditable="true"]'); }

    // 2. Block context menu, drag, copy/cut, select outside inputs.
    ['contextmenu', 'dragstart', 'copy', 'cut', 'selectstart'].forEach(function (ev) {
        document.addEventListener(ev, function (e) { if (!editable(e.target)) e.preventDefault(); }, true);
    });

    // 3. Block screenshot / save / print / view-source shortcuts and blank the screen briefly.
    function flash() {
        root.classList.add('sb-hide');
        try { navigator.clipboard && navigator.clipboard.writeText(' '); } catch (e) {}
        setTimeout(function () { if (document.hasFocus() && !document.hidden) root.classList.remove('sb-hide'); }, 1500);
    }
    document.addEventListener('keyup', function (e) { if (e.key === 'PrintScreen' || e.keyCode === 44) flash(); });
    document.addEventListener('keydown', function (e) {
        var k = (e.key || '').toLowerCase(), mod = e.ctrlKey || e.metaKey;
        var shot = e.metaKey && e.shiftKey && ['3', '4', '5'].indexOf(k) > -1;      // macOS
        var win = e.shiftKey && e.metaKey && k === 's';                              // Win+Shift+S
        var blocked = (mod && ['p', 's', 'u'].indexOf(k) > -1) || (mod && e.shiftKey && ['i', 'j', 'c'].indexOf(k) > -1) || k === 'f12';
        if (e.key === 'PrintScreen' || shot || win || blocked) { e.preventDefault(); flash(); }
    }, true);

    // 4. Hide content whenever the app loses focus (snipping tools, app-switcher thumbnail, overlays).
    function hide() { root.classList.add('sb-hide'); }
    function show() { if (document.hasFocus() && !document.hidden) root.classList.remove('sb-hide'); }
    window.addEventListener('blur', hide);
    window.addEventListener('focus', show);
    document.addEventListener('visibilitychange', function () { document.hidden ? hide() : show(); });
    window.addEventListener('pagehide', hide);
    window.addEventListener('pageshow', show);
    window.addEventListener('beforeprint', hide);
})();
</script>
@endauth
