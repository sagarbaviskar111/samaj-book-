{{-- Global event popup (register + event-day "Join now"). Included once in layouts/app.blade.php for active members.
     Data: GET events.popup. State lives in localStorage "sbEvPop" (per-event reminders) and sessionStorage "sbEvVisit" (per-visit flags). --}}
@php
    $evUser = auth()->user();
    $evCurrent = request()->routeIs('events.*') ? optional(request()->route('event'))->id : null;
@endphp
@if($evUser && $evUser->role === 'user' && $evUser->isActive() && \Illuminate\Support\Facades\Route::has('events.popup') && ! request()->routeIs('events.join'))
@php
    $evCfg = ['url' => route('events.popup'), 'uid' => $evUser->id, 'csrf' => csrf_token(), 'current' => $evCurrent];
@endphp
<script type="application/json" id="ev-pop-cfg">{!! json_encode($evCfg, JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
<script>
(function () {
    var cfgEl = document.getElementById('ev-pop-cfg');
    if (!cfgEl || !window.fetch) { return; }
    var cfg = JSON.parse(cfgEl.textContent);
    var LS = 'sbEvPop', SS = 'sbEvVisit', AWAY_MS = 5 * 60 * 1000, REFRESH_MS = 2 * 60 * 1000;
    var data = null, fetchedAt = 0, busy = false, modal = null, lastFocus = null;
    var memV = null;

    function rd(store, key) { try { return JSON.parse(store.getItem(key)) || null; } catch (e) { return null; } }
    function wr(store, key, v) { try { store.setItem(key, JSON.stringify(v)); } catch (e) {} }

    // ---- persistent per-event state (shared by all tabs) ----
    function L() {
        var s = rd(localStorage, LS);
        if (!s || s.u !== cfg.uid) { s = { u: cfg.uid, last: {}, joined: {}, regd: {} }; }
        return s;
    }
    // ---- per-visit state (one tab / one app session) ----
    function V() {
        var v = rd(sessionStorage, SS) || memV;
        if (!v) { v = { reg: false, jf: {}, hiddenAt: 0 }; }
        // came back after being away 5+ minutes: this is a new visit
        if (v.hiddenAt && Date.now() - v.hiddenAt >= AWAY_MS) { v = { reg: false, jf: {}, hiddenAt: 0 }; }
        memV = v; wr(sessionStorage, SS, v);
        return v;
    }
    function saveV(v) { memV = v; wr(sessionStorage, SS, v); }

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text != null) { n.textContent = text; }
        return n;
    }

    function closeModal(restore) {
        if (!modal) { return; }
        document.removeEventListener('keydown', onKey, true);
        modal.remove(); modal = null;
        document.documentElement.classList.remove('overflow-hidden');
        if (restore !== false && lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
    }
    var onDismiss = null;
    function dismiss() { var f = onDismiss; onDismiss = null; closeModal(); if (f) { f(); } }

    function onKey(e) {
        if (!modal) { return; }
        if (e.key === 'Escape') { e.preventDefault(); dismiss(); return; }
        if (e.key !== 'Tab') { return; }
        var f = modal.querySelectorAll('a[href],button:not([disabled])');
        if (!f.length) { return; }
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }

    function build(kind, ev) {
        lastFocus = document.activeElement;
        var root = el('div', 'fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4');
        root.setAttribute('role', 'dialog'); root.setAttribute('aria-modal', 'true'); root.setAttribute('aria-labelledby', 'ev-pop-title');
        var back = el('div', 'absolute inset-0 bg-ink-950/60 backdrop-blur-sm');
        back.addEventListener('click', dismiss);
        var panel = el('div', 'relative w-full overflow-hidden rounded-t-3xl bg-white shadow-2xl ring-1 ring-ink-100 sm:max-w-md sm:rounded-3xl');
        panel.style.animation = 'mb-rise .4s cubic-bezier(.2,.7,.2,1) both';
        panel.style.paddingBottom = 'env(safe-area-inset-bottom)';

        var head = el('div', 'mb-cover relative h-40');
        if (ev.image_url) {
            var img = el('img', 'h-full w-full object-cover'); img.src = ev.image_url; img.alt = ''; head.appendChild(img);
            head.appendChild(el('div', 'absolute inset-0 bg-gradient-to-t from-ink-950/60 to-transparent'));
        }
        var chip = el('span', 'absolute left-4 top-4 rounded-full bg-gold-400 px-3 py-1 text-sm font-bold text-ink-950 shadow', kind === 'join' ? 'आज - लाइव' : 'निमंत्रण');
        head.appendChild(chip);
        var x = el('button', 'absolute right-3 top-3 grid h-10 w-10 place-items-center rounded-full bg-white/20 text-xl text-white backdrop-blur transition hover:bg-white/30', '×');
        x.type = 'button'; x.setAttribute('aria-label', 'बंद करें'); x.title = 'बंद करें'; x.addEventListener('click', dismiss);
        head.appendChild(x);
        panel.appendChild(head);

        var body = el('div', 'space-y-3 p-5'); body.id = 'ev-pop-body';
        var t = el('h2', 'font-display text-2xl font-bold leading-tight text-ink-900', ev.title); t.id = 'ev-pop-title';
        body.appendChild(el('p', 'text-base font-bold text-gold-600', kind === 'join' ? 'कार्यक्रम शुरू होने वाला है!' : 'आपको निमंत्रण है। नाम लिखवाएँ'));
        body.appendChild(t);
        body.appendChild(el('p', 'text-base font-semibold text-ink-800', ev.starts_at_human));
        if (ev.place) { body.appendChild(el('p', 'text-base text-ink-700', ev.place)); }
        panel.appendChild(body);

        var acts = el('div', 'space-y-2 px-5 pb-5'); acts.id = 'ev-pop-acts';
        panel.appendChild(acts);
        root.appendChild(back); root.appendChild(panel);
        document.body.appendChild(root);
        document.documentElement.classList.add('overflow-hidden');
        document.addEventListener('keydown', onKey, true);
        modal = root;
        return { body: body, acts: acts, title: t };
    }

    function btnPrimary(label) { var b = el('button', 'btn-gold min-h-14 w-full text-lg', label); b.type = 'button'; return b; }
    function later() { var b = el('button', 'btn-ghost min-h-11 w-full', 'बाद में'); b.type = 'button'; b.addEventListener('click', dismiss); return b; }

    function showRegister(ev) {
        var p = build('register', ev);
        var reg = btnPrimary('नाम लिखवाएँ');
        reg.addEventListener('click', function () {
            reg.disabled = true; reg.textContent = '...';
            fetch(ev.register_url, { method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (!res.ok) { reg.disabled = false; reg.textContent = (res.j && res.j.message) || 'फिर कोशिश करें'; return; }
                    var s = L(); s.regd[ev.id] = true; wr(localStorage, LS, s);
                    data = null; onDismiss = null;
                    p.acts.textContent = '';
                    p.body.appendChild(el('p', 'rounded-2xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-200', 'आपका नाम लिख लिया गया! कार्यक्रम वाले दिन याद दिलाया जाएगा।'));
                    var view = el('a', 'btn-primary min-h-12 w-full', 'कार्यक्रम देखें'); view.href = ev.event_url;
                    var cl = el('button', 'btn-ghost min-h-11 w-full', 'बंद करें'); cl.type = 'button'; cl.addEventListener('click', dismiss);
                    p.acts.appendChild(view); p.acts.appendChild(cl); view.focus();
                })
                .catch(function () { reg.disabled = false; reg.textContent = 'फिर कोशिश करें'; });
        });
        p.acts.appendChild(reg);
        var more = el('a', 'block py-2 text-center text-base font-bold text-ink-700 underline-offset-2 hover:underline', 'पूरी जानकारी देखें'); more.href = ev.event_url;
        p.acts.appendChild(more);
        p.acts.appendChild(later());
        reg.focus();
    }

    function showJoin(ev) {
        var p = build('join', ev);
        var go = el('a', 'btn-gold min-h-14 w-full text-lg', 'अभी जुड़ें');
        go.href = ev.join_url; go.target = '_blank'; go.rel = 'noopener';
        go.addEventListener('click', function () {
            var s = L(); s.joined[ev.id] = true; wr(localStorage, LS, s);
            data = null; onDismiss = null; closeModal(false);
        });
        p.acts.appendChild(go);
        p.acts.appendChild(later());
        go.focus();
    }

    function evaluate() {
        if (modal || document.hidden || !data) { return; }
        var v = V(), s = L(), now = Date.now();

        var j = data.join;
        if (j && !s.joined[j.id] && String(cfg.current) !== String(j.id)) {
            var interval = Math.max(1, j.interval_min || 15) * 60000;
            var due = !s.last[j.id] || now - s.last[j.id] >= interval;
            if (!v.jf[j.id] || due) {
                v.jf[j.id] = true; saveV(v);
                s.last[j.id] = now; wr(localStorage, LS, s);
                onDismiss = function () { var q = L(); q.last[j.id] = Date.now(); wr(localStorage, LS, q); };
                showJoin(j);
                return;
            }
        }
        var r = data.register;
        if (r && !v.reg && !s.regd[r.id] && String(cfg.current) !== String(r.id)) {
            v.reg = true; saveV(v);
            onDismiss = null;
            showRegister(r);
        }
    }

    function tick() {
        if (document.hidden || busy) { return; }
        if (data && Date.now() - fetchedAt < REFRESH_MS) { evaluate(); return; }
        busy = true;
        fetch(cfg.url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { busy = false; if (j) { data = j; fetchedAt = Date.now(); evaluate(); } })
            .catch(function () { busy = false; });
    }

    function away() { var v = V(); v.hiddenAt = Date.now(); saveV(v); }
    function back() {
        var v = rd(sessionStorage, SS) || memV;
        if (v && v.hiddenAt && Date.now() - v.hiddenAt >= AWAY_MS) { data = null; }
        V(); // normalises the visit (resets flags when away long enough)
        var w = V(); w.hiddenAt = 0; saveV(w);
        tick();
    }

    document.addEventListener('visibilitychange', function () { if (document.hidden) { away(); } else { back(); } });
    window.addEventListener('pageshow', function (e) { if (e.persisted) { back(); } });
    window.addEventListener('pagehide', away);

    // Page load: V() resets the visit flags when we were gone 5+ minutes.
    var v0 = V(); v0.hiddenAt = 0; saveV(v0);
    setTimeout(tick, 1200);
    setInterval(tick, 30000);
})();
</script>
@endif
