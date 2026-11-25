@extends('layouts.app')
@php $title = $m->name; $meU = auth()->user(); @endphp
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<a href="{{ route('home') }}" class="mb-3 inline-flex items-center gap-1 text-base font-bold text-ink-700 underline">&larr; {{ __('वापस') }}</a>

<section class="card card-pad">
    <div class="flex items-center gap-4">
        <span class="rounded-full bg-gradient-to-tr from-gold-400 via-kumkum-500 to-kumkum-700 p-[3px]">@include('feed.partials.avatar', ['u' => $m, 'size' => 'h-20 w-20'])</span>
        <div class="min-w-0 flex-1">
            <h1 class="truncate font-display text-2xl font-bold text-ink-900">{{ $m->name }}</h1>
            @if($m->city)<p class="truncate text-base text-ink-700">{{ $m->city }}</p>@endif
            @if($followsMe)<span class="mt-1 inline-block rounded-full bg-ink-50 px-2.5 py-0.5 text-sm font-semibold text-ink-700 ring-1 ring-ink-100">{{ __('आपको फ़ॉलो करता है') }}</span>@endif
        </div>
    </div>
    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
        <div class="rounded-2xl bg-ink-50 p-3"><p class="text-xl font-bold text-ink-900">{{ $stats['posts'] }}</p><p class="text-sm text-ink-700">{{ __('पोस्ट') }}</p></div>
        <a href="{{ route('members.followers', $m) }}" class="rounded-2xl bg-ink-50 p-3 transition hover:bg-ink-100"><p class="text-xl font-bold text-ink-900" data-followers-count="{{ route('members.follow', $m) }}">{{ $stats['followers'] }}</p><p class="text-sm text-ink-700">{{ __('फ़ॉलोअर्स') }}</p></a>
        <a href="{{ route('members.following', $m) }}" class="rounded-2xl bg-ink-50 p-3 transition hover:bg-ink-100"><p class="text-xl font-bold text-ink-900">{{ $stats['following'] }}</p><p class="text-sm text-ink-700">{{ __('फ़ॉलोइंग') }}</p></a>
    </div>
    @if($m->id !== $meU->id)
        <div class="mt-4 flex">@include('members.partials.follow-button', ['m' => $m, 'followsMe' => $followsMe])</div>
    @endif
</section>

<div id="feed-list" class="mt-4 space-y-4">
    @forelse($posts as $post)
        @include('feed.partials.post', ['post' => $post])
    @empty
        <div class="card card-pad py-10 text-center text-base text-ink-700">{{ __('अभी कोई पोस्ट नहीं है') }}</div>
    @endforelse
</div>
@if($posts->hasMorePages())
    <div class="py-4 text-center"><a href="{{ $posts->nextPageUrl() }}" data-load-more data-next="{{ $posts->nextPageUrl() }}" class="btn-ghost inline-flex min-h-12 px-8">{{ __('और देखें') }}</a></div>
@endif
@endsection
@push('scripts')
    @include('feed.partials.scripts')
@endpush
