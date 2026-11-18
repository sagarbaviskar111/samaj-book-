{{-- Heart toggle with a visible word. Needs $biodata. Optional: $isShortlisted (else queried), $floating (overlay on card), $labelled (full-width button). --}}
@php
    $isShortlisted = $isShortlisted ?? \App\Models\Shortlist::where('user_id', auth()->id())->where('biodata_id', $biodata->id)->exists();
@endphp
@if(\Illuminate\Support\Facades\Route::has('shortlist.toggle') && $biodata->user_id !== auth()->id())
<form method="POST" action="{{ route('shortlist.toggle', $biodata) }}" data-shortlist
      class="{{ !empty($floating) ? 'absolute right-2 top-2 z-10' : (!empty($labelled) ? 'block' : 'inline-block') }}">
    @csrf
    @if(!empty($labelled))
        <button type="submit" aria-pressed="{{ $isShortlisted ? 'true' : 'false' }}" class="em-act">
            <svg class="h-6 w-6 {{ $isShortlisted ? 'fill-rose-brand text-rose-brand' : 'fill-transparent text-ink-600' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5-1.74 0-3.41.99-4.5 2.57-1.09-1.58-2.76-2.57-4.5-2.57C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
            <span data-sl-label>{{ $isShortlisted ? 'सहेजा गया' : 'सहेजें' }}</span>
        </button>
    @else
        <button type="submit" aria-pressed="{{ $isShortlisted ? 'true' : 'false' }}"
                class="inline-flex min-h-11 items-center gap-1.5 rounded-full bg-white/95 px-3.5 text-base font-bold text-ink-900 shadow-md ring-1 ring-ink-200 backdrop-blur transition active:scale-95 hover:bg-white">
            <svg class="h-5 w-5 {{ $isShortlisted ? 'fill-rose-brand text-rose-brand' : 'fill-transparent text-ink-700' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5-1.74 0-3.41.99-4.5 2.57-1.09-1.58-2.76-2.57-4.5-2.57C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
            <span data-sl-label>{{ $isShortlisted ? 'सहेजा गया' : 'सहेजें' }}</span>
        </button>
    @endif
</form>
@once
@push('scripts')
<script>
document.addEventListener('submit', function (e) {
    var f = e.target.closest('form[data-shortlist]');
    if (!f || !window.fetch) return;
    e.preventDefault();
    fetch(f.action, {method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': f.querySelector('input[name=_token]').value}})
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var svg = f.querySelector('svg'), btn = f.querySelector('button'), lbl = f.querySelector('[data-sl-label]');
            svg.classList.toggle('fill-rose-brand', d.shortlisted); svg.classList.toggle('text-rose-brand', d.shortlisted);
            svg.classList.toggle('fill-transparent', !d.shortlisted); svg.classList.toggle('text-ink-700', !d.shortlisted);
            btn.setAttribute('aria-pressed', d.shortlisted);
            if (lbl) lbl.textContent = d.shortlisted ? 'सहेजा गया' : 'सहेजें';
            svg.classList.remove('bd-pop'); void svg.getBoundingClientRect(); if (d.shortlisted) svg.classList.add('bd-pop');
        })
        .catch(function () { f.submit(); });
});
</script>
@endpush
@endonce
@endif
