@extends('layouts.app')
@php $title = 'सहेजे हुए रिश्ते'; @endphp
@section('wide', '1')
@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="section-title !text-2xl sm:!text-3xl">मेरे सहेजे हुए रिश्ते</h1>
        <p class="text-sm text-ink-700 sm:text-base">जो बायोडाटा आपको पसंद आए, वे यहाँ एक जगह मिलेंगे।</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('interests.index') }}" class="btn-ghost btn-sm !min-h-12">रिश्ते की पसंद</a>
        <a href="{{ route('biodata.index') }}" class="btn-primary btn-sm !min-h-12">रिश्ते खोजें</a>
    </div>
</div>

@if($biodatas->isEmpty())
    <div class="card-pad bd-paisley mx-auto max-w-lg py-14 text-center">
        <svg class="mx-auto mb-3 h-20 w-20" viewBox="0 0 80 80" fill="none" aria-hidden="true">
            <path d="M40 66S12 48 12 29a15 15 0 0 1 28-7.500A15 15 0 0 1 68 29c0 19-28 37-28 37Z" fill="#fde7ef" stroke="#e11d63" stroke-width="3" stroke-dasharray="5 5"/>
            <path d="M40 30v14M33 37h14" stroke="#e11d63" stroke-width="3.500" stroke-linecap="round"/>
        </svg>
        <p class="font-display text-xl font-bold text-ink-900">अभी कोई बायोडाटा सहेजा नहीं है</p>
        <p class="mt-1 text-base text-ink-700">कोई बायोडाटा पसंद आए तो उसके ऊपर "सहेजें" दबाइए। वह यहाँ आ जाएगा।</p>
        <a href="{{ route('biodata.index') }}" class="btn-primary mt-5 !min-h-12">रिश्ते देखें</a>
    </div>
@else
    <p class="mb-3 text-lg font-bold text-ink-900">{{ $biodatas->total() }} रिश्ते सहेजे हुए हैं</p>
    <div class="grid grid-cols-1 gap-4 min-[520px]:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
        @foreach($biodatas as $biodata)
            @include('biodata.partials.card', ['biodata' => $biodata, 'idx' => $loop->index])
        @endforeach
    </div>
    <div class="mt-6">{{ $biodatas->links() }}</div>
@endif
@endsection
