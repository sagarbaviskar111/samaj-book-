@extends('layouts.app')
@section('title', 'बायोडाटा बनाएँ')
@section('content')
<div class="bd-paisley relative mx-auto mb-4 max-w-3xl overflow-hidden rounded-3xl bg-gradient-to-br from-kumkum-800 to-kumkum-600 p-5 text-white shadow-glow sm:p-6">
    <a href="{{ route('biodata.mine') }}" class="inline-flex min-h-11 items-center text-base font-semibold text-white underline">&larr; मेरे बायोडाटा</a>
    <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">बायोडाटा बनाएँ</h1>
    <p class="mt-1 text-base text-white">सिर्फ़ 5 छोटे चरण। फ़ोटो आप सेव करने के बाद जोड़ सकते हैं।</p>
    <p class="mt-2 text-base text-gold-200">&#10024; सही जानकारी भरने पर बेहतर रिश्ते मिलते हैं।</p>
</div>
@include('biodata._form-steps', ['biodata' => $biodata, 'action' => route('biodata.store'), 'method' => 'POST'])
@endsection
