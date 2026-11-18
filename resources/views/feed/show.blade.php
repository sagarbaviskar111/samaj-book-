@extends('layouts.app')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<a href="{{ route('home') }}" class="mb-3 inline-flex items-center gap-1 text-base font-bold text-ink-700 underline">&larr; {{ __("वापस") }}</a>
@include('feed.partials.post', ['post' => $post, 'open' => true])
@endsection
@push('scripts')
    <script>document.body.dataset.single = '1';</script>
    @include('feed.partials.scripts')
@endpush
