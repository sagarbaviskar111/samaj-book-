{{-- Lite (limited) card: photo (privacy rules apply), first name and age ONLY. Nothing else may be rendered here. --}}
@php
    $b = $biodata;
    $first = explode(' ', trim($b->name))[0] ?? $b->name;
    $mode = \App\Support\BiodataAccess::photoMode(auth()->user(), $b);
    $photo = $b->primaryPhoto ?? null;
    $showPhoto = $photo && in_array($mode, ['clear', 'blur'], true);
    $blur = $mode === 'blur';
    $grad = ['from-kumkum-700 to-kumkum-500', 'from-gold-500 to-kumkum-600', 'from-neel-700 to-neel-500', 'from-tulsi-600 to-gold-500'][$b->id % 4];
@endphp
<article class="card bd-card bd-fade-up group relative flex flex-col overflow-hidden" style="--i: {{ $idx ?? 0 }}">
    <a href="{{ route('biodata.show', $b) }}" class="relative block aspect-[4/5] overflow-hidden bg-ink-50" aria-label="{{ $first }}, {{ $b->dob->age }} वर्ष">
        @if($showPhoto)
            <img src="{{ route('biodata.photo', ['photo' => $photo->id, 'size' => 'thumb']) }}" alt="{{ $first }}" loading="lazy"
                 class="h-full w-full object-cover {{ $blur ? 'blur-xl scale-110' : '' }}" oncontextmenu="return false" draggable="false">
        @else
            <div class="bd-paisley grid h-full w-full place-items-center bg-gradient-to-br {{ $grad }}">
                <span class="font-display text-5xl font-bold text-white/90">{{ mb_strtoupper(mb_substr($first, 0, 1)) }}</span>
            </div>
        @endif
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/85 via-ink-950/30 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-3 text-white">
            <h3 class="font-display text-xl font-bold leading-tight">{{ $first }}, {{ $b->dob->age }} वर्ष</h3>
        </div>
    </a>
    <div class="p-3.5">
        <a href="{{ route('biodata.show', $b) }}" class="btn-ghost w-full !min-h-12">&#128274; पूरा बायोडाटा देखें</a>
    </div>
</article>
