{{-- Access-mode banner: CTA for lite members, "looking for" summary for view-only members. --}}
@php
    $level = \App\Support\AccessLevel::for(auth()->user());
    $lf = $level === 'view_only' ? \App\Support\AccessLevel::lookingFor(auth()->user()) : null;
@endphp
@if($level === 'lite')
    <div class="mb-4 rounded-2xl bg-gradient-to-br from-gold-50 to-white p-4 ring-1 ring-gold-200">
        <p class="text-lg font-bold text-ink-900">&#128274; पूरा बायोडाटा देखने के लिए इनमें से एक चुनिए</p>
        <p class="mt-1 text-base text-ink-800">अभी आपको सिर्फ़ फ़ोटो, पहला नाम और उम्र दिख रही है।</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            <a href="{{ route('biodata.create') }}" class="btn-primary !min-h-14 !text-lg">मेरा बायोडाटा बनाएँ</a>
            <a href="{{ route('looking-for.create') }}" class="btn-ghost !min-h-14 !text-lg">किसी और के लिए देख रहे हैं</a>
        </div>
    </div>
@elseif($lf)
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-ink-50 px-4 py-3 text-base text-ink-900 ring-1 ring-ink-100">
        <span>आप अपने <strong>{{ $lf->forWhomLabel() }}</strong> के लिए {{ $lf->looking_for === 'bride' ? 'दुल्हन' : 'दूल्हा' }} देख रहे हैं &middot; <a href="{{ route('looking-for.edit') }}" class="inline-flex min-h-11 items-center font-bold underline">बदलें</a></span>
        <a href="{{ route('biodata.create') }}" class="btn-primary btn-sm !min-h-12">पसंद भेजने के लिए अपना बायोडाटा बनाएँ</a>
    </div>
@endif
