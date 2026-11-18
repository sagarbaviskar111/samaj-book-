{{-- Kundli info card. Only included when the controller says the viewer may see it. Needs $biodata, $kundliInterest (nullable). --}}
@use('App\Support\BiodataOptions', 'KO')
@if($biodata->rashi || $biodata->nakshatra || ! is_null($biodata->manglik) || ($kundliInterest ?? null))
<section class="card-pad bd-fade-up" id="kundli-card">
    <h2 class="flex items-center gap-2.5 text-base font-bold text-ink-900">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-gold-500 to-kumkum-600 text-white shadow-sm" aria-hidden="true">&#128302;</span>
        कुंडली
    </h2>
    <dl class="mt-2 divide-y divide-ink-50">
        @if($biodata->rashi)<div class="flex justify-between gap-4 py-2.5 text-sm"><dt class="text-ink-700">राशि</dt><dd class="text-right font-semibold text-ink-900">{{ KO::rashis()[$biodata->rashi] ?? $biodata->rashi }}</dd></div>@endif
        @if($biodata->nakshatra)<div class="flex justify-between gap-4 py-2.5 text-sm"><dt class="text-ink-700">नक्षत्र</dt><dd class="text-right font-semibold text-ink-900">{{ KO::nakshatras()[$biodata->nakshatra] ?? $biodata->nakshatra }}@if($biodata->nakshatra_pada) &middot; चरण {{ $biodata->nakshatra_pada }}@endif</dd></div>@endif
        @unless(is_null($biodata->manglik))<div class="flex justify-between gap-4 py-2.5 text-sm"><dt class="text-ink-700">मांगलिक</dt><dd class="text-right font-semibold text-ink-900">{{ $biodata->manglik ? 'हाँ' : 'नहीं' }}</dd></div>@endunless
    </dl>
    @if($kundliInterest ?? null)
        <a href="{{ route('interests.kundli', $kundliInterest) }}" class="btn-gold mt-3 w-full !min-h-12">&#128302; कुंडली मिलान देखें</a>
    @endif
</section>
@endif
