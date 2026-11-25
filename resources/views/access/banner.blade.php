{{-- Access-mode banner: CTA for lite members, "looking for" summary for view-only members. --}}
@php
    $level = \App\Support\AccessLevel::for(auth()->user());
    $lf = $level === 'view_only' ? \App\Support\AccessLevel::lookingFor(auth()->user()) : null;
@endphp
@if($level === 'lite')
    <div class="mb-4 rounded-2xl bg-gradient-to-br from-gold-50 to-white p-4 ring-1 ring-gold-200">
        <p class="text-lg font-bold text-ink-900">&#128274; {{ __('बायोडाटा देखने के लिए पहले अपना बायोडाटा बनाइए') }}</p>
        <p class="mt-1 text-base text-ink-800">{{ __('अभी कोई बायोडाटा नहीं दिख रहा। अपना बायोडाटा बनाते ही सब खुल जाएँगे।') }}</p>
        <div class="mt-3">
            <a href="{{ route('biodata.create') }}" class="btn-primary w-full !min-h-14 !text-lg">{{ __('मेरा बायोडाटा बनाएँ') }}</a>
        </div>
    </div>
@elseif($lf)
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-ink-50 px-4 py-3 text-base text-ink-900 ring-1 ring-ink-100">
        <span>{!! __('आप अपने :whom के लिए :who देख रहे हैं', ['whom' => '<strong>'.e(__($lf->forWhomLabel())).'</strong>', 'who' => e($lf->looking_for === 'bride' ? __('दुल्हन') : __('दूल्हा'))]) !!} &middot; <a href="{{ route('looking-for.edit') }}" class="inline-flex min-h-11 items-center font-bold underline">{{ __('बदलें') }}</a></span>
        <a href="{{ route('biodata.create') }}" class="btn-primary btn-sm !min-h-12">{{ __('पसंद भेजने के लिए अपना बायोडाटा बनाएँ') }}</a>
    </div>
@endif
