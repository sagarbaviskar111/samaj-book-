{{-- $m (User), optional $followsMe (bool), $small (bool). Toggles via JSON; label becomes Follow / Follow back / Following. --}}
@php
    $meU = auth()->user();
    $isMe = $meU->id === $m->id;
    $on = ! $isMe && $meU->isFollowing($m->id);
    $fb = ! empty($followsMe);
    $pad = ! empty($small) ? 'min-h-9 px-3 text-sm' : 'min-h-11 px-5 text-base';
@endphp
@if(! $isMe && ! $meU->isPending() && ! $meU->isAdmin())
<button type="button" data-follow="{{ route('members.follow', $m) }}" data-on="{{ $on ? '1' : '0' }}" data-fb="{{ $fb ? '1' : '0' }}"
        data-l-follow="{{ __('फ़ॉलो करें') }}" data-l-back="{{ __('फ़ॉलो बैक करें') }}" data-l-on="{{ __('फ़ॉलोइंग') }}"
        class="{{ $pad }} shrink-0 rounded-full font-bold transition {{ $on ? 'bg-ink-100 text-ink-800 ring-1 ring-ink-200' : 'bg-kumkum-700 text-white shadow-glow' }}">
    {{ $on ? __('फ़ॉलोइंग') : ($fb ? __('फ़ॉलो बैक करें') : __('फ़ॉलो करें')) }}
</button>
@once
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-follow]'); if (!b || b.disabled) return;
    e.preventDefault(); b.disabled = true;
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrf = meta ? meta.content : @json(csrf_token());
    fetch(b.dataset.follow, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { if (!r.ok) throw r; return r.json(); })
        .then(function (d) {
            b.dataset.on = d.following ? '1' : '0';
            b.textContent = d.following ? b.dataset.lOn : (b.dataset.fb === '1' ? b.dataset.lBack : b.dataset.lFollow);
            ['bg-kumkum-700', 'text-white', 'shadow-glow'].forEach(function (c) { b.classList.toggle(c, !d.following); });
            ['bg-ink-100', 'text-ink-800', 'ring-1', 'ring-ink-200'].forEach(function (c) { b.classList.toggle(c, d.following); });
            var c = document.querySelector('[data-followers-count="' + b.dataset.follow + '"]'); if (c) c.textContent = d.followers;
        })
        .catch(function () {})
        .finally(function () { b.disabled = false; });
});
</script>
@endonce
@endif
