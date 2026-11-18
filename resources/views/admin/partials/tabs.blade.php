{{-- $tabs: [key => label], $counts, $current, $route, $extra (query to keep) --}}
<div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0">
    @foreach($tabs as $key => $label)
        <a href="{{ route($route, array_merge($extra ?? [], ['status' => $key])) }}"
           class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition {{ $current === $key ? 'bg-kumkum-700 text-white shadow-glow' : 'bg-white text-ink-700 ring-1 ring-ink-100 hover:bg-ink-50' }}">
            {{ __($label) }}
            @if(isset($counts[$key]))<span class="ml-1 rounded-full {{ $current === $key ? 'bg-white/20' : 'bg-ink-50' }} px-1.5 py-0.5 text-[11px]">{{ $counts[$key] }}</span>@endif
        </a>
    @endforeach
</div>
