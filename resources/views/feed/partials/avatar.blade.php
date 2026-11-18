@php
    $size = $size ?? 'h-10 w-10';
    $av = $u->avatar ?? null;
    $src = $av ? (str_starts_with($av, 'http') ? $av : asset('storage/'.$av)) : null;
@endphp
@if($src)
    <img src="{{ $src }}" alt="" loading="lazy" class="{{ $size }} shrink-0 rounded-full object-cover ring-2 ring-white">
@else
    <span class="{{ $size }} grid shrink-0 place-items-center rounded-full bg-gradient-to-br from-kumkum-600 to-kumkum-800 text-sm font-bold text-gold-300 ring-2 ring-white">{{ mb_strtoupper(mb_substr($u->name ?? '?', 0, 1)) }}</span>
@endif
