@extends('layouts.app')
@section('title', __('मेरे बायोडाटा'))
@section('wide', '1')
@section('content')
@php
    $chip = ['draft' => 'badge-ink', 'pending' => 'badge-warn', 'approved' => 'badge-ok', 'rejected' => 'badge-bad', 'paused' => 'badge-ink', 'married' => 'badge-ok'];
    // timeline position per status: 0 draft, 1 pending, 2 approved
    $pos = ['draft' => 0, 'rejected' => 1, 'pending' => 1, 'approved' => 2, 'paused' => 2, 'married' => 2];
@endphp
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="section-title !text-2xl sm:!text-3xl">{{ __('मेरे बायोडाटा') }}</h1>
        <p class="text-base text-ink-700">{{ __('अपना या परिवार के किसी सदस्य का बायोडाटा यहाँ संभालिए।') }}</p>
    </div>
    @if($canAdd)<a href="{{ route('biodata.create') }}" class="btn-gold !min-h-11">{{ __('+ नया बायोडाटा बनाएँ') }}</a>@endif
</div>

@include('access.banner')

<div class="grid gap-4 lg:grid-cols-2">
@forelse($biodatas as $b)
    @php
        $pct = $b->completeness();
        $at = $pos[$b->status] ?? 0;
        $ringColor = $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ee5a52');
        $steps = [[__('ड्राफ़्ट'), 'M4 20h4L18.500 9.500a2.100 2.100 0 0 0-3-3L5 17v3Z'], [__('जाँच में'), 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'], [__('मंज़ूर'), 'M5 13l4 4L19 7']];
    @endphp
    <article class="card bd-fade-up overflow-hidden" style="--i: {{ $loop->index }}">
        <div class="bd-paisley bg-gradient-to-r from-ink-50 to-gold-50 px-4 py-3 sm:px-5">
            {{-- status timeline --}}
            <ol class="flex items-center" aria-label="{{ __('बायोडाटा की स्थिति') }}">
                @foreach($steps as $k => [$sl, $sp])
                    @php
                        $done = $k < $at || ($k === $at && in_array($b->status, ['approved', 'paused', 'married'], true));
                        $current = $k === $at;
                        $bad = $b->status === 'rejected' && $k === 1;
                    @endphp
                    <li class="flex items-center {{ $k < 2 ? 'flex-1' : '' }}">
                        <span class="flex flex-col items-center gap-1">
                            <span class="grid h-8 w-8 place-items-center rounded-full ring-2 {{ $bad ? 'bg-rose-500 text-white ring-rose-200' : ($done ? 'bg-emerald-500 text-white ring-emerald-200' : ($current ? 'bd-pulse bg-kumkum-600 text-white ring-ink-200' : 'bg-white text-ink-500 ring-ink-100')) }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.200" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bad ? 'M6 6l12 12M18 6 6 18' : $sp }}"/></svg>
                            </span>
                            <span class="text-sm font-semibold {{ $current || $done ? 'text-ink-800' : 'text-ink-700' }}">{{ $bad ? __('नामंज़ूर') : $sl }}</span>
                        </span>
                        @if($k < 2)<span class="mx-1 mb-4 h-0.5 flex-1 rounded-full {{ $k < $at ? 'bg-emerald-400' : 'bg-ink-100' }}"></span>@endif
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="p-4 sm:p-5">
            <div class="flex gap-3.5">
                <div class="h-28 w-24 shrink-0 overflow-hidden rounded-2xl bg-ink-50 ring-1 ring-ink-100">
                    @if($b->primaryPhoto)<img src="{{ route('biodata.photo', [$b->primaryPhoto, 'size' => 'thumb']) }}" alt="" class="h-full w-full object-cover">@else
                        <div class="grid h-full place-items-center bg-gradient-to-br from-gold-500 to-kumkum-600 font-display text-3xl font-bold text-white">{{ mb_strtoupper(mb_substr(trim($b->name), 0, 1)) }}</div>@endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate font-display text-lg font-bold text-ink-900">{{ $b->name }}</h2>
                        <span class="{{ $chip[$b->status] ?? 'badge-ink' }}">{{ __(['draft' => 'ड्राफ़्ट', 'pending' => 'जाँच में', 'approved' => 'मंज़ूर', 'rejected' => 'नामंज़ूर', 'paused' => 'रुका हुआ', 'married' => 'रिश्ता हो गया'][$b->status] ?? $b->status) }}</span>
                        @if($b->verified)<span class="badge-ok">&#10003; {{ __('जाँचा हुआ') }}</span>@endif
                    </div>
                    <p class="text-sm text-ink-600">{{ __(':n वर्ष', ['n' => $b->age]) }} &middot; {{ $b->gender === 'male' ? __('लड़का') : __('लड़की') }} &middot; {{ $b->current_city ?: $b->district ?: '—' }}</p>
                    <p class="text-sm text-ink-700">&#128065; {{ __(':n बार देखा गया', ['n' => number_format($b->views)]) }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="relative grid h-12 w-12 shrink-0 place-items-center">
                            <svg class="absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#f1e4d3" stroke-width="3.500"/>
                                <circle class="bd-ring-arc" cx="18" cy="18" r="15" fill="none" stroke="{{ $ringColor }}" stroke-width="3.500" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $pct }} 100"/>
                            </svg>
                            <span class="relative text-sm font-bold text-ink-900">{{ $pct }}%</span>
                        </div>
                        <p class="text-sm font-semibold text-ink-700">{{ __('बायोडाटा कितना भरा है') }}
                            @if($pct < 100)<span class="block font-normal text-ink-700">{{ __('फ़ोटो, अपने बारे में और परिवार की जानकारी जोड़कर बढ़ाइए') }}</span>@endif</p>
                    </div>
                </div>
            </div>

            @if($b->status === 'rejected' && $b->reject_reason)
                <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-sm text-rose-700 ring-1 ring-rose-100">{{ __('कारण:') }} {{ $b->reject_reason }}</p>
            @endif
            @if($b->status === 'pending')
                <p class="mt-3 rounded-xl bg-gold-50 px-3 py-2 text-sm text-gold-600 ring-1 ring-gold-200">&#9203; {{ __('एडमिन की मंज़ूरी का इंतज़ार है।') }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('biodata.show', $b) }}" class="btn-ghost btn-sm !min-h-11">{{ __('देखें') }}</a>
                <a href="{{ route('biodata.edit', $b) }}" class="btn-primary btn-sm !min-h-11">{{ __('बदलाव करें') }}</a>
                @include('biodata.partials.share-button', ['biodata' => $b])
                @if($b->status === 'approved')
                    <form method="POST" action="{{ route('biodata.status', $b) }}">@csrf<input type="hidden" name="action" value="pause"><button class="btn-ghost btn-sm !min-h-11">{{ __('रोकें') }}</button></form>
                @elseif($b->status === 'paused')
                    <form method="POST" action="{{ route('biodata.status', $b) }}">@csrf<input type="hidden" name="action" value="resume"><button class="btn-ghost btn-sm !min-h-11">{{ __('फिर चालू करें') }}</button></form>
                @endif
                @if(in_array($b->status, ['approved', 'paused', 'pending']))
                    <form method="POST" action="{{ route('biodata.status', $b) }}" onsubmit="return confirm({{ Js::from(__('रिश्ता हो गया? अब यह बायोडाटा खोज में नहीं दिखेगा।')) }})">@csrf<input type="hidden" name="action" value="married"><button class="btn-gold btn-sm !min-h-11">{{ __('रिश्ता हो गया') }}</button></form>
                @endif
                <form method="POST" action="{{ route('biodata.destroy', $b) }}" onsubmit="return confirm({{ Js::from(__('यह बायोडाटा हमेशा के लिए हटा दें?')) }})" class="ml-auto">@csrf @method('DELETE')<button class="btn-ghost btn-sm !min-h-11 text-rose-600">{{ __('हटाएँ') }}</button></form>
            </div>
        </div>
    </article>
@empty
    <div class="card-pad bd-paisley py-14 text-center lg:col-span-2">
        <svg class="mx-auto mb-3 h-20 w-20" viewBox="0 0 80 80" fill="none" aria-hidden="true">
            <circle cx="30" cy="42" r="16" stroke="#f59e0b" stroke-width="5"/>
            <circle cx="50" cy="42" r="16" stroke="#d63a34" stroke-width="5"/>
            <path d="M40 14v8M34 18h12" stroke="#e11d63" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <h2 class="font-display text-xl font-bold text-ink-900">{{ __('अभी कोई बायोडाटा नहीं है') }}</h2>
        <p class="mx-auto mt-1 max-w-sm text-sm text-ink-700">{{ __('अपना या परिवार के किसी सदस्य का बायोडाटा बनाइए।') }}</p>
        <a href="{{ route('biodata.create') }}" class="btn-gold mt-5 !min-h-11">{{ __('बायोडाटा बनाएँ') }}</a>
    </div>
@endforelse
</div>
@endsection
