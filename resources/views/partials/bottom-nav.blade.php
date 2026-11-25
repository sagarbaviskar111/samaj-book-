@php
    $items = [
        ['route' => 'home', 'label' => 'होम', 'match' => ['home'], 'icon' => 'M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75v10.5h5.25v-6h4.5v6H19.5V9.75'],
        ['route' => 'biodata.index', 'label' => 'रिश्ता खोजें', 'match' => ['biodata.index', 'biodata.show'], 'icon' => 'M21 21l-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z'],
        ['route' => 'posts.create', 'label' => 'पोस्ट', 'match' => ['posts.create'], 'icon' => 'M12 4.5v15m7.5-7.5h-15', 'center' => true],
        ['route' => 'notifications.index', 'label' => 'सूचनाएँ', 'match' => ['notifications.*'], 'icon' => 'M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.08 5.46 1.31m5.71 0a24.26 24.26 0 0 1-5.71 0m5.71 0a3 3 0 1 1-5.71 0', 'badge' => true],
        ['route' => 'profile.show', 'label' => 'प्रोफ़ाइल', 'match' => ['profile.*'], 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.68 0-5.22-.59-7.5-1.63Z'],
    ];
    $navUnread = $unread ?? 0;
    $navPending = (bool) auth()->user()?->isPending();
    if ($navPending) {
        $items = array_values(array_filter($items, fn ($i) => in_array($i['route'], ['home', 'notifications.index', 'profile.show'], true)));
    }
@endphp
<nav class="mb-navbar safe-bottom fixed inset-x-0 bottom-0 z-40 lg:hidden" aria-label="{{ __('Main') }}">
    <ul class="mx-auto grid max-w-2xl {{ $navPending ? 'grid-cols-3' : 'grid-cols-5' }} items-end px-1 pt-1.5">
        @foreach($items as $i)
            @php($has = \Illuminate\Support\Facades\Route::has($i['route']))
            @php($active = $has && request()->routeIs(...$i['match']))
            <li class="min-w-0 text-center">
                <a href="{{ $has ? route($i['route']) : '#' }}" @if($active) aria-current="page" @endif
                   class="flex min-h-[3.75rem] flex-col items-center justify-end gap-0.5 px-0.5 pb-1 transition active:scale-95 {{ $active ? 'text-kumkum-800' : 'text-ink-800' }}">
                    @if(!empty($i['center']))
                        <span class="mb-plus -mt-5 grid h-12 w-12 place-items-center rounded-full bg-gradient-to-br from-gold-200 via-gold-400 to-gold-500 text-kumkum-900 ring-4 ring-cream">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $i['icon'] }}"/></svg>
                        </span>
                        <span class="mb-nav-label">{{ __($i['label']) }}</span>
                    @else
                        <span class="mb-nav-ico relative grid h-8 w-14 place-items-center rounded-full transition-colors {{ $active ? 'bg-kumkum-700 text-white' : '' }}">
                            <svg class="relative h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $active ? 2.2 : 1.9 }}" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $i['icon'] }}"/></svg>
                            @if(!empty($i['badge']) && $navUnread)
                                <span class="mb-badge absolute -right-0.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-rose-brand px-1 text-xs font-bold leading-none text-white ring-2 ring-cream">{{ $navUnread > 99 ? '99+' : $navUnread }}</span>
                            @endif
                        </span>
                        <span class="mb-nav-label {{ $active ? 'underline decoration-2 underline-offset-4' : '' }}">{{ __($i['label']) }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
