{{-- $title, $text, optional $cta + $href --}}
<div class="px-4 py-14 text-center">
    <svg class="mx-auto h-16 w-16 text-ink-200" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="32" cy="32" r="26" stroke-dasharray="4 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 33l7 7 13-14" stroke="#d63a34"/></svg>
    <p class="mt-3 font-display text-xl font-bold text-ink-800">{{ $title }}</p>
    <p class="mt-1 text-sm text-ink-500">{{ $text ?? '' }}</p>
    @isset($href)<a href="{{ $href }}" class="btn-primary mt-4">{{ $cta }}</a>@endisset
</div>
