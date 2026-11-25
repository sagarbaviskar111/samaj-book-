{{-- "People to follow" card. Needs $suggestions. --}}
@if(isset($suggestions) && $suggestions->isNotEmpty())
<section class="card card-pad">
    <h2 class="font-display text-lg font-bold text-ink-900">{{ __('इन्हें फ़ॉलो करें') }}</h2>
    <ul class="mt-3 space-y-3">
        @foreach($suggestions as $s)
            <li class="flex items-center gap-3">
                <a href="{{ route('members.show', $s) }}" class="flex min-w-0 flex-1 items-center gap-3">
                    @include('feed.partials.avatar', ['u' => $s, 'size' => 'h-10 w-10'])
                    <span class="min-w-0"><span class="block truncate text-sm font-semibold text-ink-900">{{ $s->name }}</span>@if($s->city)<span class="block truncate text-xs text-ink-700">{{ $s->city }}</span>@endif</span>
                </a>
                @include('members.partials.follow-button', ['m' => $s, 'small' => true, 'followsMe' => false])
            </li>
        @endforeach
    </ul>
</section>
@endif
