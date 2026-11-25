<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    function toast(msg) {
        var t = document.createElement('div');
        t.textContent = msg;
        t.className = 'fixed left-1/2 top-4 z-[100] -translate-x-1/2 rounded-full bg-ink-900 px-5 py-3 text-base font-semibold text-white shadow-xl';
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 2800);
    }
    function send(url, method, body) {
        var opts = { method: method, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
        if (body) { opts.body = body; }
        return fetch(url, opts).then(function (r) { if (!r.ok) throw r; return r.json(); });
    }

    function toggleLike(like) {
        var on = like.dataset.liked === '1', cnt = like.querySelector('[data-like-count]'), svg = like.querySelector('svg');
        function paint(v, n) {
            like.dataset.liked = v ? '1' : '0';
            like.classList.toggle('text-rose-brand', v); like.classList.toggle('text-ink-700', !v);
            svg.setAttribute('fill', v ? 'currentColor' : 'none'); cnt.textContent = n;
            if (v) { svg.classList.remove('mb-like-pop'); void svg.getBoundingClientRect(); svg.classList.add('mb-like-pop'); }
        }
        paint(!on, Math.max(0, parseInt(cnt.textContent, 10) + (on ? -1 : 1)));
        send(like.dataset.like, 'POST').then(function (d) { paint(d.liked, d.count); })
            .catch(function () { paint(on, parseInt(cnt.textContent, 10) + (on ? 1 : -1)); toast(@json(__('पसंद नहीं हो पाई। दोबारा कोशिश करें।'))); });
    }
    function burst(host) {
        var h = document.createElement('span');
        h.className = 'mb-burst';
        h.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>';
        host.appendChild(h);
        setTimeout(function () { h.remove(); }, 900);
    }
    function skeleton() {
        var d = document.createElement('div');
        d.setAttribute('data-skeleton', '');
        d.className = 'card space-y-3 p-4';
        d.innerHTML = '<div class="flex items-center gap-3"><div class="mb-skel h-10 w-10 !rounded-full"></div><div class="flex-1 space-y-2"><div class="mb-skel h-3 w-1/3"></div><div class="mb-skel h-3 w-1/4"></div></div></div><div class="mb-skel h-56 w-full !rounded-2xl"></div><div class="mb-skel h-3 w-2/3"></div>';
        return d;
    }

    document.addEventListener('click', function (e) {
        var like = e.target.closest('[data-like]');
        if (like) { toggleLike(like); return; }
        var dbl = e.target.closest('[data-dbl]');
        if (dbl && !e.metaKey && !e.ctrlKey && !e.shiftKey && e.button === 0) {
            e.preventDefault();
            if (dbl._t) {
                clearTimeout(dbl._t); dbl._t = null;
                var lb = dbl.closest('[data-post]').querySelector('[data-like]');
                if (lb.dataset.liked !== '1') { toggleLike(lb); }
                burst(dbl);
            } else {
                dbl._t = setTimeout(function () { dbl._t = null; location.href = dbl.href; }, 260);
            }
            return;
        }
        var tg = e.target.closest('[data-comments-toggle]');
        if (tg) { tg.closest('[data-post]').querySelector('[data-comments-box]').classList.toggle('hidden'); return; }

        var rep = e.target.closest('[data-report]');
        if (rep) {
            var reason = window.prompt(@json(__('आप शिकायत क्यों कर रहे हैं? (लिखना ज़रूरी नहीं)')), '');
            if (reason === null) { return; }
            var fd = new FormData(); fd.append('target_type', rep.dataset.type); fd.append('target_id', rep.dataset.id); fd.append('reason', reason);
            send(rep.dataset.url, 'POST', fd).then(function (d) { toast(d.message); }).catch(function () { toast(@json(__('शिकायत नहीं भेज पाए।'))); });
            rep.closest('details')?.removeAttribute('open');
            return;
        }
        var del = e.target.closest('[data-post-delete]');
        if (del) {
            if (!window.confirm(@json(__('क्या आप यह पोस्ट हटाना चाहते हैं?')))) { return; }
            send(del.dataset.postDelete, 'DELETE').then(function () {
                var card = del.closest('[data-post]');
                if (document.body.dataset.single) { location.href = @json(route('home')); } else { card.remove(); toast(@json(__('पोस्ट हटा दी गई।'))); }
            }).catch(function () { toast(@json(__('हटा नहीं पाए। दोबारा कोशिश करें।'))); });
            return;
        }
        var cd = e.target.closest('[data-comment-delete]');
        if (cd) {
            if (!window.confirm(@json(__('क्या आप यह कमेंट हटाना चाहते हैं?')))) { return; }
            send(cd.dataset.commentDelete, 'DELETE').then(function (d) {
                var post = cd.closest('[data-post]');
                cd.closest('[data-comment]').remove();
                post.querySelector('[data-comment-count]').textContent = d.count;
            }).catch(function () { toast(@json(__('हटा नहीं पाए। दोबारा कोशिश करें।'))); });
            return;
        }
        var more = e.target.closest('[data-load-more]');
        if (more) {
            e.preventDefault();
            more.disabled = true; more.textContent = @json(__('खुल रहा है...'));
            var sk = skeleton(); document.getElementById('feed-list').appendChild(sk);
            fetch(more.dataset.next, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); }).then(function (d) {
                    sk.remove();
                    document.getElementById('feed-list').insertAdjacentHTML('beforeend', d.html);
                    if (d.next) { more.dataset.next = d.next; more.disabled = false; more.textContent = @json(__('और देखें')); } else { more.remove(); }
                }).catch(function () { sk.remove(); more.disabled = false; more.textContent = @json(__('और देखें')); toast(@json(__('इंटरनेट में दिक्कत है। दोबारा कोशिश करें।'))); });
        }
    });

    document.addEventListener('submit', function (e) {
        var f = e.target.closest('[data-comment-form]');
        if (!f) { return; }
        e.preventDefault();
        var post = f.closest('[data-post]'), input = f.elements.body;
        if (!input.value.trim()) { return; }
        send(f.action, 'POST', new FormData(f)).then(function (d) {
            post.querySelector('[data-comments-list]').insertAdjacentHTML('beforeend', d.html);
            post.querySelector('[data-comment-count]').textContent = d.count;
            input.value = '';
        }).catch(function () { toast(@json(__('कमेंट नहीं भेज पाए।'))); });
    });

    // Infinite scroll: auto-click "Load more" when it nears the viewport.
    var btn = document.querySelector('[data-load-more]');
    if (btn && 'IntersectionObserver' in window) {
        new IntersectionObserver(function (en) {
            en.forEach(function (x) { var b = document.querySelector('[data-load-more]'); if (x.isIntersecting && b && !b.disabled) { b.click(); } });
        }, { rootMargin: '300px' }).observe(btn);
    }
})();
</script>
