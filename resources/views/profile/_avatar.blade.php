@php($ring = $ring ?? 'ring-4')
@if($user->avatar)
    <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="{{ $size ?? 'h-16 w-16' }} shrink-0 rounded-full object-cover {{ $ring }} ring-white shadow-soft">
@else
    <span class="{{ $size ?? 'h-16 w-16' }} {{ $text ?? 'text-2xl' }} {{ $ring }} grid shrink-0 place-items-center rounded-full bg-gradient-to-br from-kumkum-500 to-kumkum-700 font-bold text-white ring-white shadow-soft">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
@endif
