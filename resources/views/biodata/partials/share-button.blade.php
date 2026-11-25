{{-- Owner "Share" button. $biodata required, $class optional. Opens the shared bottom sheet. --}}
@php($ok = $biodata->status === 'approved')
@if($ok)
    <button type="button" data-share-open data-share-endpoint="{{ route('biodata.share.show', $biodata) }}" data-share-reset="{{ route('biodata.share.reset', $biodata) }}" data-share-toggle="{{ route('biodata.share.toggle', $biodata) }}" data-share-pdf="{{ route('biodata.pdf', $biodata) }}"
            class="{{ $class ?? 'btn-gold btn-sm !min-h-11' }}">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4M18 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm12 7a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
        {{ __('शेयर करें') }}
    </button>
    @include('biodata.partials.share-sheet')
@else
    <span class="inline-flex flex-col">
        <button type="button" disabled aria-disabled="true" class="{{ $class ?? 'btn-gold btn-sm !min-h-11' }} cursor-not-allowed opacity-50">{{ __('शेयर करें') }}</button>
        <span class="mt-1 text-sm text-ink-700">{{ __('बायोडाटा मंज़ूर होने के बाद "शेयर करें" चालू होगा।') }}</span>
    </span>
@endif
