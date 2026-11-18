{{-- Interest state/actions for the biodata being viewed. Needs $biodata only. --}}
@php
    $uid = auth()->id();
    $isOwner = $biodata->user_id === $uid;
    if (! $isOwner) {
        $level = \App\Support\AccessLevel::for(auth()->user());
        $hasApproved = $level === 'full' && \App\Models\Biodata::approved()->where('user_id', $uid)->exists();
        $mine = \App\Services\InterestService::eligibleSenders(auth()->user(), $biodata);
        $needGirl = $biodata->gender === 'male'; // target is a boy => sender profile must be a girl
        $interest = \App\Models\Interest::where(function ($q) use ($uid, $biodata) {
            $q->where(fn ($w) => $w->where('from_user_id', $uid)->where('to_biodata_id', $biodata->id))
              ->orWhere(fn ($w) => $w->where('to_user_id', $uid)->where('from_biodata_id', $biodata->id));
        })->latest('id')->first();
        $sentByMe = $interest && $interest->from_user_id === $uid;
    }
@endphp
@unless($isOwner)
<div class="card-pad space-y-3">
    @if(session('error'))<div class="rounded-xl bg-rose-50 px-3 py-2 text-base font-medium text-rose-700 ring-1 ring-rose-200" role="alert">{{ session('error') }}</div>@endif

    @if($interest && $interest->status === 'accepted')
        <div class="rounded-xl bg-emerald-50 p-3 text-base text-emerald-900 ring-1 ring-emerald-200">
            <p class="text-lg font-bold">रिश्ता मंज़ूर हुआ ✅</p>
            <p class="mt-1 text-sm">संपर्क नंबर और WhatsApp अब "रिश्ते की पसंद" पेज पर दिख रहे हैं।</p>
            <div class="mt-3 grid gap-2">
                <a href="{{ route('interests.index', ['tab' => 'accepted']) }}" class="btn-primary !min-h-12">नंबर देखें</a>
                @if(\Illuminate\Support\Facades\Route::has('interests.kundli'))<a href="{{ route('interests.kundli', $interest) }}" class="btn-gold !min-h-12">&#128302; कुंडली मिलान देखें</a>@endif
            </div>
        </div>
    @elseif($interest && $interest->status === 'sent' && $sentByMe)
        <div class="rounded-xl bg-gold-50 p-3 ring-1 ring-gold-200">
            <p class="text-base font-bold text-ink-900">आपने पसंद भेज दी है। जवाब का इंतज़ार है।</p>
            <form method="POST" action="{{ route('interests.withdraw', $interest) }}" class="mt-2" onsubmit="return confirm('अपनी भेजी हुई पसंद वापस लें?')">@csrf<button class="btn-ghost !min-h-12">&#8617; वापस लें</button></form>
        </div>
    @elseif($interest && $interest->status === 'sent')
        <div class="rounded-xl bg-gold-50 p-3 ring-1 ring-gold-200">
            <p class="text-base font-bold text-ink-900">इन्होंने आपको पसंद भेजी है</p>
            <div class="mt-2 grid grid-cols-2 gap-3">
                <form method="POST" action="{{ route('interests.respond', $interest) }}">@csrf<input type="hidden" name="action" value="accept"><button class="btn-primary w-full !min-h-12">&#10003; मंज़ूर करें</button></form>
                <form method="POST" action="{{ route('interests.respond', $interest) }}" onsubmit="return confirm('क्या आप सच में मना करना चाहते हैं? मना करने के बाद यह रिश्ता बंद हो जाएगा।')">@csrf<input type="hidden" name="action" value="reject"><button class="btn-ghost w-full !min-h-12">&#10005; मना करें</button></form>
            </div>
        </div>
    @elseif($interest && $interest->status === 'rejected' && $sentByMe)
        <p class="rounded-xl bg-rose-50 p-3 text-base text-rose-800 ring-1 ring-rose-200">धन्यवाद, इस बार संयोग नहीं बैठा। आपको जल्द सही रिश्ता मिले।</p>
    @elseif($level === 'view_only')
        <div class="rounded-xl bg-ink-50 p-3 text-base text-ink-900 ring-1 ring-ink-100">
            <p class="font-bold">&#128065; अभी आप सिर्फ़ देख सकते हैं</p>
            <p class="mt-1 text-sm text-ink-800">पसंद भेजने के लिए पहले अपना बायोडाटा बनाइए।</p>
            <a href="{{ route('biodata.create') }}" class="btn-primary mt-2 !min-h-12 w-full">मेरा बायोडाटा बनाएँ</a>
        </div>
    @elseif($mine->isEmpty())
        @if($hasApproved)
            <div class="rounded-xl bg-gold-50 p-3 text-base text-ink-900 ring-1 ring-gold-200">
                <p class="font-bold">पसंद किसकी तरफ़ से भेज रहे हैं?</p>
                <p class="mt-1 text-sm text-ink-800">पहले {{ $needGirl ? 'लड़की' : 'लड़के' }} का बायोडाटा बनाइए, तभी आप उसके लिए पसंद भेज सकते हैं।</p>
                <a href="{{ route('biodata.create') }}" class="btn-gold mt-2 !min-h-12 w-full">{{ $needGirl ? 'लड़की' : 'लड़के' }} का बायोडाटा बनाएँ</a>
            </div>
        @else
            <p class="text-base text-ink-800">पसंद भेजने के लिए पहले अपना बायोडाटा बनाइए और उसके मंज़ूर होने का इंतज़ार कीजिए।</p>
            <a href="{{ route('biodata.create') }}" class="btn-primary !min-h-12 w-full">मेरा बायोडाटा बनाएँ</a>
        @endif
    @else
        <form method="POST" action="{{ route('interests.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="to_biodata_id" value="{{ $biodata->id }}">
            <label class="field-label" for="from_biodata_id{{ $idSuffix ?? '' }}">पसंद किसकी तरफ़ से भेज रहे हैं?</label>
            <select name="from_biodata_id" id="from_biodata_id{{ $idSuffix ?? '' }}" class="input">
                @foreach($mine as $m)<option value="{{ $m->id }}">{{ \App\Services\InterestService::senderLabel($m) }}</option>@endforeach
            </select>
            @if($mine->contains(fn ($m) => $m->added_by !== 'self'))
                <p class="text-sm text-ink-700">किसी रिश्तेदार के लिए भेज रहे हैं तो उसका नाम चुनिए। सामने वाले को आपका नाम और रिश्ता दिखेगा।</p>
            @endif
            <button class="btn-primary w-full !min-h-14 !text-lg">&#10084; पसंद भेजें</button>
            <p class="text-center text-sm text-ink-700">सामने वाले को आपकी पसंद भेजी जाएगी। संपर्क नंबर तभी दिखेगा जब दोनों तरफ़ से हाँ हो।</p>
        </form>
    @endif
</div>
@endunless
