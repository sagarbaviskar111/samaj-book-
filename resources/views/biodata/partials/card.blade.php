{{-- Search result card. Needs $biodata (with primaryPhoto loaded). Optional: $matches[id], $interestMap[id], $shortlistIds, $idx (stagger). --}}
@php
    $b = $biodata;
    $first = explode(' ', trim($b->name))[0] ?? $b->name;
    $initials = mb_strtoupper(mb_substr($first, 0, 1)).(mb_strtoupper(mb_substr(trim(mb_substr($b->name, mb_strlen($first))), 0, 1)));
    $match = $matches[$b->id] ?? null;
    $imap = $interestMap[$b->id] ?? null;
    $isShort = in_array($b->id, $shortlistIds ?? [], true);
    $photo = $b->primaryPhoto ?? null;
    $privacy = $b->photo_privacy ?? 'members';
    $accepted = ($imap['status'] ?? null) === 'accepted';
    $showPhoto = $photo && \Illuminate\Support\Facades\Route::has('biodata.photo') && ($privacy === 'members' || $privacy === 'blur_until_accepted' || $accepted);
    $blur = $photo && $privacy === 'blur_until_accepted' && ! $accepted;
    $grad = ['from-kumkum-700 to-kumkum-500', 'from-gold-500 to-kumkum-600', 'from-neel-700 to-neel-500', 'from-tulsi-600 to-gold-500'][$b->id % 4];
    $show = \Illuminate\Support\Facades\Route::has('biodata.show') ? route('biodata.show', $b) : '#';
    $place = collect([$b->current_city ?: $b->village, $b->district])->filter()->unique()->implode(', ');
    $ringColor = $match === null ? '#d63a34' : ($match >= 70 ? '#2f7d57' : ($match >= 45 ? '#ee9e0a' : '#d63a34'));
@endphp
<article class="card bd-card bd-fade-up group relative flex flex-col overflow-hidden" style="--i: {{ $idx ?? 0 }}">
    <a href="{{ $show }}" class="mb-frame relative block aspect-[4/5] overflow-hidden bg-ink-50" aria-label="{{ $first }}, {{ __(':n वर्ष', ['n' => $b->dob->age]) }}, {{ __('बायोडाटा देखें') }}">
        @if($showPhoto)
            <img src="{{ route('biodata.photo', ['photo' => $photo->id, 'size' => 'thumb']) }}" alt="{{ $first }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-700 group-hover:scale-105 {{ $blur ? 'blur-xl scale-110' : '' }}" oncontextmenu="return false" draggable="false">
        @else
            <div class="bd-paisley grid h-full w-full place-items-center bg-gradient-to-br {{ $grad }}">
                <span class="font-display text-5xl font-bold text-white/90">{{ $initials }}</span>
            </div>
        @endif
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/85 via-ink-950/30 to-transparent"></div>

        @if($b->verified)
            <span class="badge-ok absolute left-2 top-2 !bg-white/95 shadow">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.8 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>{{ __('जाँचा हुआ') }}
            </span>
        @endif
        @if($blur)
            <span class="absolute bottom-20 left-2 rounded-full bg-black/55 px-2.5 py-0.5 text-sm font-semibold text-white backdrop-blur">&#128274; {{ __('फ़ोटो बंद है') }}</span>
        @endif

        @if($match !== null)
            <div class="absolute bottom-2 right-2 grid h-14 w-14 place-items-center rounded-full bg-white/95 shadow-lg" title="{{ __(':n% मेल', ['n' => $match]) }}">
                <svg class="absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                    <circle cx="18" cy="18" r="15" fill="none" stroke="#f1e4d3" stroke-width="3"/>
                    <circle class="bd-ring-arc" cx="18" cy="18" r="15" fill="none" stroke="{{ $ringColor }}" stroke-width="3" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $match }} 100"/>
                </svg>
                <span class="relative text-center text-sm font-bold leading-none text-ink-900">{{ $match }}%<span class="block text-[0.8125rem] font-semibold">{{ __('मेल') }}</span></span>
            </div>
        @endif

        <div class="absolute inset-x-0 bottom-0 p-3 pr-[4.5rem] text-white">
            <h3 class="font-display text-xl font-bold leading-tight">{{ $first }}, {{ __(':n वर्ष', ['n' => $b->dob->age]) }}</h3>
            <p class="truncate text-sm text-white/90">{{ $place ?: '—' }}</p>
        </div>
    </a>

    @include('biodata.partials.shortlist-button', ['biodata' => $b, 'isShortlisted' => $isShort, 'floating' => true])

    <div class="flex flex-1 flex-col justify-between gap-3 p-3.5">
        <ul class="grid grid-cols-1 gap-2 text-base text-ink-800">
            @if($b->height_cm)
                <li class="flex items-start gap-2"><span aria-hidden="true" class="w-5 shrink-0 text-center">&#128207;</span><span><span class="text-ink-700">{{ __('कद:') }}</span> {{ $b->height_cm }} cm</span></li>
            @endif
            @if($b->education)
                <li class="flex items-start gap-2"><span aria-hidden="true" class="w-5 shrink-0 text-center">&#127891;</span><span><span class="text-ink-700">{{ __('पढ़ाई:') }}</span> {{ $b->education }}</span></li>
            @endif
            @if($b->occupation)
                <li class="flex items-start gap-2"><span aria-hidden="true" class="w-5 shrink-0 text-center">&#128188;</span><span><span class="text-ink-700">{{ __('काम:') }}</span> {{ $b->occupation }}</span></li>
            @endif
            @if($b->current_city)
                <li class="flex items-start gap-2"><span aria-hidden="true" class="w-5 shrink-0 text-center">&#128205;</span><span><span class="text-ink-700">{{ __('शहर:') }}</span> {{ $b->current_city }}</span></li>
            @endif
        </ul>

        @if($imap)
            @php
                $chip = match(true) {
                    $imap['status'] === 'accepted' => ['badge-ok', __('रिश्ता मंज़ूर हुआ ✅')],
                    $imap['status'] === 'sent' && $imap['dir'] === 'sent' => ['badge-warn', __('आपने पसंद भेजी है')],
                    $imap['status'] === 'sent' => ['badge-warn', __('इन्होंने आपको पसंद भेजी है')],
                    $imap['status'] === 'rejected' => ['badge-bad', __('इस बार संयोग नहीं बैठा')],
                    default => ['badge-ink', __(['expired' => 'समय पूरा हो गया', 'withdrawn' => 'पसंद वापस ली गई'][$imap['status']] ?? $imap['status'])],
                };
            @endphp
            <div class="space-y-2">
                <span class="{{ $chip[0] }} !text-sm">{{ $chip[1] }}</span>
                @if($imap['status'] === 'sent' && $imap['dir'] === 'received')
                    <a href="{{ route('interests.index', ['tab' => 'received']) }}" class="btn-primary w-full !min-h-12">{{ __('जवाब दें') }}</a>
                @elseif($imap['status'] === 'accepted')
                    <a href="{{ route('interests.index', ['tab' => 'accepted']) }}" class="btn-primary w-full !min-h-12">{{ __('नंबर देखें') }}</a>
                @endif
            </div>
        @elseif(\Illuminate\Support\Facades\Route::has('interests.store'))
            @php
                $mineAll = request()->attributes->get('_my_bd');
                if ($mineAll === null) {
                    $mineAll = auth()->user()->biodatas()->approved()->get(['id', 'name', 'gender', 'added_by'])->all();
                    request()->attributes->set('_my_bd', $mineAll);
                }
                $mineList = collect($mineAll)->where('gender', '!=', $b->gender)->values();
            @endphp
            @if($mineList->count() === 1 && $mineList->first()->added_by === 'self')
                <form method="POST" action="{{ route('interests.store') }}" class="space-y-1.5">
                    @csrf
                    <input type="hidden" name="from_biodata_id" value="{{ $mineList->first()->id }}">
                    <input type="hidden" name="to_biodata_id" value="{{ $b->id }}">
                    <button class="btn-primary w-full !min-h-12 !text-lg"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-8-5.3-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 5.7-8 11-8 11Z"/></svg>{{ __('पसंद भेजें') }}</button>
                    <p class="text-center text-sm text-ink-700">{{ __('सामने वाले को आपकी पसंद भेजी जाएगी') }}</p>
                </form>
            @else
                <a href="{{ $show }}" class="btn-primary w-full !min-h-12 !text-lg">{{ __('बायोडाटा देखें') }}</a>
            @endif
        @endif
    </div>
</article>
