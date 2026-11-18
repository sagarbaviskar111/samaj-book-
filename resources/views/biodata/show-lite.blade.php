@extends('layouts.app')
@section('title', $first)
@section('content')
@php
    $photo = $biodata->primaryPhoto;
    $showPhoto = $photo && in_array($photoMode, ['clear', 'blur'], true);
@endphp
<div class="mx-auto max-w-md">
    <div class="relative overflow-hidden rounded-3xl bg-ink-900 shadow-soft" oncontextmenu="return false">
        @if($showPhoto)
            <img src="{{ route('biodata.photo', [$photo, 'size' => 'full']) }}" alt="{{ $first }}" draggable="false"
                 class="aspect-[4/5] max-h-[70vh] w-full select-none object-cover {{ $photoMode === 'blur' ? 'blur-xl scale-110' : '' }}">
        @else
            <div class="bd-paisley grid aspect-[4/5] max-h-[70vh] w-full place-items-center bg-gradient-to-br from-kumkum-800 to-kumkum-500">
                <span class="font-display text-8xl font-bold text-white/90">{{ mb_strtoupper(mb_substr($first, 0, 1)) }}</span>
            </div>
        @endif
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-950/80 to-transparent p-4 text-white">
            <h1 class="font-display text-3xl font-bold">{{ $first }}, {{ $age }} वर्ष</h1>
            @if($photoMode !== 'clear')<p class="text-sm text-white/90">&#128274; फ़ोटो की गोपनीयता लागू है</p>@endif
        </div>
    </div>

    <div class="card-pad mt-4 space-y-3 bg-gradient-to-br from-gold-50 to-white ring-1 ring-gold-200">
        <p class="flex items-center gap-2 text-lg font-bold text-ink-900"><span aria-hidden="true">&#128274;</span> पूरा बायोडाटा अभी बंद है</p>
        <p class="text-base text-ink-800">पूरा बायोडाटा देखने के लिए इनमें से <strong>एक</strong> चुनिए:</p>
        <a href="{{ route('biodata.create') }}" class="btn-primary w-full !min-h-14 !text-lg">मेरा बायोडाटा बनाएँ</a>
        <a href="{{ route('looking-for.create') }}" class="btn-ghost w-full !min-h-14 !text-lg">किसी और के लिए देख रहे हैं</a>
        <p class="text-center text-sm text-ink-700">पसंद भेजने के लिए अपना बायोडाटा होना ज़रूरी है।</p>
    </div>
    <a href="{{ route('biodata.index', ['for' => $biodata->gender === 'male' ? 'groom' : 'bride']) }}" class="btn-ghost mt-4 w-full !min-h-12">&larr; पीछे जाएँ</a>
</div>
@endsection
