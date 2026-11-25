@extends('layouts.app')
@php $title = $kind === 'followers' ? __('फ़ॉलोअर्स') : __('फ़ॉलोइंग'); @endphp
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<a href="{{ route('members.show', $m) }}" class="mb-3 inline-flex items-center gap-1 text-base font-bold text-ink-700 underline">&larr; {{ $m->name }}</a>
<h1 class="mb-3 font-display text-2xl font-bold text-ink-900">{{ $title }}</h1>
<ul class="card divide-y divide-ink-100">
    @forelse($users as $u)
        <li class="flex items-center gap-3 p-3">
            <a href="{{ route('members.show', $u) }}" class="flex min-w-0 flex-1 items-center gap-3">
                @include('feed.partials.avatar', ['u' => $u, 'size' => 'h-12 w-12'])
                <span class="min-w-0"><span class="block truncate font-semibold text-ink-900">{{ $u->name }}</span>@if($u->city)<span class="block truncate text-sm text-ink-700">{{ $u->city }}</span>@endif</span>
            </a>
            @include('members.partials.follow-button', ['m' => $u, 'small' => true, 'followsMe' => false])
        </li>
    @empty
        <li class="p-8 text-center text-base text-ink-700">{{ $kind === 'followers' ? __('अभी कोई फ़ॉलोअर नहीं है') : __('अभी किसी को फ़ॉलो नहीं किया है') }}</li>
    @endforelse
</ul>
@endsection
