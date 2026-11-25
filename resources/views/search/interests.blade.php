@extends('layouts.app')
@php $title = __('रिश्ते की पसंद'); @endphp
@section('wide', '1')
@section('content')
@php
    $uid = auth()->id();
    // Order matters: accepted matches first.
    $tabs = ['accepted' => __('मंज़ूर हुए'), 'received' => __('आए हुए'), 'sent' => __('भेजे हुए')];
    $tabIcon = [
        'accepted' => 'M5 13l4 4L19 7',
        'received' => 'M3 8l9 6 9-6M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'sent' => 'M5 12h14m0 0-5-5m5 5-5 5',
    ];
    $tabHelp = [
        'accepted' => __('यहाँ वे रिश्ते हैं जहाँ दोनों तरफ़ से हाँ हो गई है। संपर्क नंबर यहीं दिखेगा।'),
        'received' => __('यहाँ वे लोग हैं जिन्होंने आपको पसंद भेजी है। आप मंज़ूर करें या मना करें।'),
        'sent' => __('यहाँ वे लोग हैं जिन्हें आपने पसंद भेजी है। उनके जवाब का इंतज़ार है।'),
    ];
    $emptyCopy = [
        'accepted' => [__('अभी कोई रिश्ता मंज़ूर नहीं हुआ'), __('जब कोई रिश्ता मंज़ूर होगा, उसका नंबर और WhatsApp यहीं दिखेगा।')],
        'received' => [__('अभी आपको कोई पसंद नहीं आई'), __('अपना बायोडाटा पूरा रखें और फ़ोटो जोड़ें, इससे पसंद जल्दी आती हैं।')],
        'sent' => [__('आपने अभी किसी को पसंद नहीं भेजी'), __('रिश्ते देखिए और जो अच्छा लगे उसे "पसंद भेजें" दबाइए।')],
    ];
    $show = fn ($b) => \Illuminate\Support\Facades\Route::has('biodata.show') ? route('biodata.show', $b) : '#';
@endphp
<div class="mb-4">
    <h1 class="section-title !text-2xl sm:!text-3xl">{{ __('रिश्ते की पसंद') }}</h1>
    <p class="text-sm text-ink-700">{{ __('आई हुई, भेजी हुई और मंज़ूर हुई पसंद, सब एक जगह।') }}</p>
</div>

@if(session('error'))<div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-base font-medium text-rose-700 ring-1 ring-rose-200" role="alert">{{ session('error') }}</div>@endif

{{-- Waiting-for-answer banner (shown when another tab is open) --}}
@if(($counts['received'] ?? 0) > 0 && $tab !== 'received')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gold-100 p-4 ring-2 ring-gold-300" role="status" id="waiting-banner">
        <p class="text-base font-bold text-ink-900"><span aria-hidden="true">&#128276;</span> {{ __(':n नए रिश्ते आपके जवाब का इंतज़ार कर रहे हैं', ['n' => $counts['received']]) }}</p>
        <a href="{{ route('interests.index', ['tab' => 'received']) }}" class="btn-primary !min-h-12">{{ __('जवाब देने के लिए खोलें') }}</a>
    </div>
@endif

<div class="mb-2 grid max-w-2xl grid-cols-3 gap-1.5 rounded-3xl bg-ink-50 p-1.5 text-center ring-1 ring-ink-100" role="tablist" aria-label="{{ __('रिश्ते की पसंद') }}">
    @foreach($tabs as $key => $label)
        <a href="{{ route('interests.index', ['tab' => $key]) }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
           class="em-tab {{ $tab === $key ? 'em-tab-on' : '' }}">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tabIcon[$key] }}"/></svg>
            <span class="leading-tight">{{ $label }}</span>
            <span class="em-count {{ ($counts[$key] ?? 0) > 0 ? '' : 'em-count-zero' }}">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>
<p class="mb-5 max-w-2xl text-base text-ink-700">{{ $tabHelp[$tab] }}</p>

<div class="grid gap-4 lg:grid-cols-2">
@forelse($interests as $i)
    @php
        $mineIsSender = $i->from_user_id === $uid;
        $other = $mineIsSender ? $i->toBiodata : $i->fromBiodata;
        $mine = $mineIsSender ? $i->fromBiodata : $i->toBiodata;
        $first = explode(' ', trim($other->name))[0];
        $onBehalf = $i->isOnBehalf();
        $relation = $i->relationLabel();
        $senderPhone = preg_replace('/\D/', '', (string) ($i->fromUser->phone ?? ''));
        $photo = $other->primaryPhoto;
        $privacy = $other->photo_privacy ?? 'members';
        $canPhoto = $photo && \Illuminate\Support\Facades\Route::has('biodata.photo') && ($privacy !== 'accepted_only' || $i->status === 'accepted');
        $blur = $photo && $privacy === 'blur_until_accepted' && $i->status !== 'accepted';
        $phone = preg_replace('/\D/', '', (string) $other->contact_phone);
        $wa = strlen($phone) === 10 ? '91'.$phone : $phone;
        $isAccepted = $i->status === 'accepted';
        $statusBadge = ['sent' => 'badge-warn', 'accepted' => 'badge-ok', 'rejected' => 'badge-bad', 'expired' => 'badge-ink', 'withdrawn' => 'badge-ink'][$i->status] ?? 'badge-ink';
        $statusText = ['sent' => __('जवाब बाकी'), 'accepted' => __('मंज़ूर'), 'rejected' => __('मना किया'), 'expired' => __('समय पूरा'), 'withdrawn' => __('वापस लिया')][$i->status] ?? $i->status;
        $stripe = ['sent' => 'from-gold-300 to-gold-500', 'accepted' => 'from-emerald-400 to-emerald-600', 'rejected' => 'from-rose-300 to-rose-500'][$i->status] ?? 'from-ink-200 to-ink-300';
        $stateLabel = match($i->status) {
            'sent' => $mineIsSender ? __('सामने वाले के जवाब का इंतज़ार') : __('आपके जवाब का इंतज़ार'),
            'accepted' => __('रिश्ता मंज़ूर हुआ ✅'),
            'rejected' => $mineIsSender ? __('इस बार संयोग नहीं बैठा') : __('आपने मना किया'),
            'expired' => __('समय पूरा हो गया'),
            'withdrawn' => __('पसंद वापस ले ली गई'),
            default => $i->status,
        };
    @endphp
    <div class="card bd-fade-up relative overflow-hidden" style="--i: {{ $loop->index }}">
        <div class="h-2 bg-gradient-to-r {{ $stripe }}"></div>
        <div class="p-4 sm:p-5">
            <div class="flex gap-4">
                <a href="{{ $show($other) }}" class="relative shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-neel-600 to-kumkum-500 shadow-soft ring-2 ring-white {{ $isAccepted ? 'h-32 w-28' : 'h-28 w-24' }}" aria-label="{{ __(':name की बायोडाटा देखें', ['name' => $first]) }}">
                    @if($canPhoto)<img src="{{ route('biodata.photo', ['photo' => $photo->id, 'size' => 'thumb']) }}" alt="" loading="lazy" class="h-full w-full object-cover {{ $blur ? 'blur-md' : '' }}">
                    @else<span class="grid h-full w-full place-items-center font-display text-4xl font-bold text-white">{{ mb_strtoupper(mb_substr($first, 0, 1)) }}</span>@endif
                </a>
                <div class="min-w-0 flex-1">
                    <a href="{{ $show($other) }}" class="block font-display text-xl font-bold leading-tight text-ink-900 hover:text-ink-700">{{ $first }}, {{ __(':n वर्ष', ['n' => $other->dob->age]) }}</a>
                    <span class="{{ $statusBadge }} mt-1">{{ $statusText }}</span>
                    @if($onBehalf && ! $mineIsSender)<span class="badge-warn mt-1">{{ __(':relation के लिए · भेजा: :name', ['relation' => $relation, 'name' => $i->fromUser->name ?? '']) }}</span>@endif
                    <p class="mt-1 text-base text-ink-800">{{ collect([$other->education, $other->occupation])->filter()->implode(' · ') ?: '—' }}</p>
                    <p class="text-sm text-ink-700">{{ collect([$other->current_city, $other->district])->filter()->unique()->implode(', ') }}</p>
                </div>
            </div>

            <p class="mt-3 text-base font-bold text-ink-800">{{ $stateLabel }}</p>
            <p class="text-sm text-ink-700">{{ $mineIsSender ? __('आपकी तरफ़ से: :name', ['name' => $mine->name]).($onBehalf ? ' ('.$relation.')' : '') : __('आपकी बायोडाटा: :name', ['name' => $mine->name]) }} · {{ $i->created_at->diffForHumans() }}</p>
            @if($i->status === 'sent' && $i->expires_at)<p class="text-sm font-semibold text-gold-600">&#9203; {{ __(':time बंद हो जाएगी', ['time' => $i->expires_at->diffForHumans()]) }}</p>@endif

            @if($i->status === 'sent' && ! $mineIsSender)
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <form method="POST" action="{{ route('interests.respond', $i) }}">@csrf<input type="hidden" name="action" value="accept"><button class="btn-primary w-full !min-h-14 !text-lg">&#10003; {{ __('मंज़ूर करें') }}</button></form>
                    <form method="POST" action="{{ route('interests.respond', $i) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('क्या आप सच में मना करना चाहते हैं? मना करने के बाद यह रिश्ता बंद हो जाएगा।')) }})">@csrf<input type="hidden" name="action" value="reject"><button class="btn-ghost w-full !min-h-14 !text-lg">&#10005; {{ __('मना करें') }}</button></form>
                </div>
            @elseif($i->status === 'sent' && $mineIsSender)
                <form method="POST" action="{{ route('interests.withdraw', $i) }}" class="mt-4" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('अपनी भेजी हुई पसंद वापस लें?')) }})">@csrf<button class="btn-ghost !min-h-12">&#8617; {{ __('वापस लें') }}</button></form>
            @elseif($isAccepted)
                <div class="relative mt-4 overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-gold-50 p-4 ring-1 ring-emerald-200">
                    <div class="bd-confetti" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <p class="relative text-base font-bold text-emerald-800">&#127881; {{ __('बधाई हो! संपर्क करें') }}</p>
                    @if($other->contact_phone)
                        <p class="relative mt-1 text-sm text-ink-700">{{ __('मोबाइल नंबर') }}</p>
                        <a href="tel:{{ $phone }}" class="relative block text-3xl font-bold tracking-wide text-ink-900">{{ $other->contact_phone }}</a>
                        <div class="relative mt-3 grid grid-cols-2 gap-3">
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn !min-h-14 bg-emerald-600 !text-lg text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8.500 8.500 0 0 1-12.500 7.500L3 21l1.500-5.300A8.500 8.500 0 1 1 21 12Z"/></svg>
                                WhatsApp</a>
                            <a href="tel:{{ $phone }}" class="btn !min-h-14 bg-emerald-600 !text-lg text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-700">&#128222; {{ __('फ़ोन करें') }}</a>
                        </div>
                    @else
                        <p class="relative mt-1 text-base text-ink-800">{{ __('नंबर अभी उपलब्ध नहीं है।') }}</p>
                    @endif
                    @if($onBehalf && ! $mineIsSender && $i->fromUser)
                        <div class="relative mt-4 rounded-xl bg-white/80 p-3 ring-1 ring-emerald-100">
                            <p class="text-sm font-bold text-emerald-800">{{ __('संपर्क करने वाले: :name (:relation की तरफ़ से)', ['name' => $i->fromUser->name, 'relation' => $relation]) }}</p>
                            <a href="tel:{{ $senderPhone }}" class="mt-1 block text-2xl font-bold text-ink-900">{{ $i->fromUser->phone }}</a>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                <a href="https://wa.me/{{ strlen($senderPhone) === 10 ? '91'.$senderPhone : $senderPhone }}" target="_blank" rel="noopener" class="btn-ghost !min-h-12">WhatsApp</a>
                                <a href="tel:{{ $senderPhone }}" class="btn-ghost !min-h-12">&#128222; {{ __('फ़ोन करें') }}</a>
                            </div>
                        </div>
                    @endif
                    <div class="relative mt-4 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('interests.kundli', $i) }}" class="btn-gold !min-h-12">&#128302; {{ __('कुंडली मिलान देखें') }}</a>
                        @if(in_array($i->id, $helpIds, true))
                            <p class="flex items-center text-sm font-semibold text-emerald-800">&#10003; {{ __('एडमिन से मदद का संदेश भेजा जा चुका है।') }}</p>
                        @else
                            <form method="POST" action="{{ route('interests.help', $i) }}">@csrf<button class="btn-ghost w-full !min-h-12">&#128222; {{ __('एडमिन से मदद लें') }}</button></form>
                        @endif
                    </div>
                </div>
            @elseif($i->status === 'rejected' && $mineIsSender)
                <p class="mt-3 rounded-xl bg-ink-50 px-3 py-2 text-base text-ink-800">{{ __('धन्यवाद, इस बार संयोग नहीं बैठा। आपको जल्द सही रिश्ता मिले।') }}</p>
            @elseif(in_array($i->status, ['expired', 'withdrawn'], true))
                <a href="{{ $show($other) }}" class="btn-ghost mt-3 !min-h-12">{{ __('बायोडाटा देखें') }}</a>
            @endif
        </div>
    </div>
@empty
    <div class="card-pad bd-paisley py-14 text-center lg:col-span-2">
        <svg class="mx-auto mb-3 h-20 w-20" viewBox="0 0 80 80" fill="none" aria-hidden="true">
            <rect x="10" y="20" width="60" height="42" rx="8" fill="#f1e4d3" stroke="#d63a34" stroke-width="3"/>
            <path d="m12 24 28 20 28-20" stroke="#d63a34" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="62" cy="22" r="9" fill="#fbbf24" stroke="#d97706" stroke-width="2"/>
        </svg>
        <p class="font-display text-xl font-bold text-ink-900">{{ $emptyCopy[$tab][0] }}</p>
        <p class="mx-auto mt-1 max-w-sm text-base text-ink-700">{{ $emptyCopy[$tab][1] }}</p>
        <a href="{{ route('biodata.index') }}" class="btn-primary mt-5 !min-h-12">{{ __('रिश्ते खोजें') }}</a>
    </div>
@endforelse
</div>

<div class="mt-4">{{ $interests->links() }}</div>
@endsection
