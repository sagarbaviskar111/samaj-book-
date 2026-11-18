@php($flash = session('share_hint') ?: session('status'))
@if($flash)
    <div class="mb-5 flex items-start gap-3 rounded-2xl bg-gold-50 px-4 py-3 text-base font-semibold text-ink-900 ring-1 ring-gold-300" role="status">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-6 w-6 shrink-0 text-gold-600" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.04-.02a.75.75 0 0 1 1.06.85l-.7 2.83a.75.75 0 0 0 1.06.85L13 15.75M12 8.25h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span>{{ $flash }}</span>
    </div>
@endif
