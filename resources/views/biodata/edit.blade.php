@extends('layouts.app')
@section('title', __('बायोडाटा बदलें'))
@section('content')
<div class="mx-auto mb-4 max-w-3xl">
    <a href="{{ route('biodata.mine') }}" class="inline-flex min-h-11 items-center text-base font-semibold text-ink-800 hover:text-ink-900">&larr; {{ __('मेरे बायोडाटा') }}</a>
    <h1 class="mt-1 font-display text-2xl font-bold text-ink-900 sm:text-3xl">{{ __('बायोडाटा बदलें') }}</h1>
    @if($biodata->status === 'approved')
        <p class="mt-1 rounded-xl bg-gold-50 px-3 py-2 text-base text-ink-900 ring-1 ring-gold-200">&#9432; {{ __('नाम, जन्म तारीख़, लड़का/लड़की बदलने या नई फ़ोटो जोड़ने पर बायोडाटा दोबारा जाँच के लिए जाएगा।') }}</p>
    @endif
    @if($biodata->status === 'rejected' && $biodata->reject_reason)
        <div class="mt-2 rounded-xl bg-rose-50 px-4 py-3 text-base text-rose-800 ring-1 ring-rose-200"><b>{{ __('नामंज़ूर होने का कारण:') }}</b> {{ $biodata->reject_reason }}</div>
    @endif
</div>

@include('biodata.partials.photos', ['biodata' => $biodata])

<div class="mt-4">
    @include('biodata._form-steps', ['biodata' => $biodata, 'action' => route('biodata.update', $biodata), 'method' => 'PUT'])
</div>
@endsection
