{{-- LOCAL DEV ONLY: one-click login buttons. $variant = 'panel' (login page) | 'menu' (floating switcher) --}}
@php($variant = $variant ?? 'panel')
@foreach(\App\Support\DevLogin::groups() as $group => $rows)
    <div class="{{ $variant === 'panel' ? 'mb-3' : 'mb-2' }}">
        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider {{ $variant === 'panel' ? 'text-white/60' : 'text-ink-400' }}">{{ $group }}</p>
        <div class="grid {{ $variant === 'panel' ? 'grid-cols-2 gap-1.5 sm:grid-cols-3' : 'grid-cols-2 gap-1' }}">
            @foreach($rows as $r)
                <form method="POST" action="{{ route('dev.login', $r['phone']) }}">
                    @csrf
                    <button type="submit" title="{{ $r['name'] }} · {{ $r['phone'] }}"
                        class="w-full rounded-xl px-2.5 py-2 text-left text-xs font-semibold leading-tight transition
                        {{ $variant === 'panel' ? 'bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20' : 'bg-ink-50 text-ink-800 hover:bg-ink-100' }}
                        {{ $group === 'Admin' ? '!bg-gold-400/90 !text-ink-950 hover:!bg-gold-300' : '' }}">
                        <span class="block truncate">{{ in_array($group, ['Admin', 'Special cases', 'Naye members (biodata nahi, lite view)'], true) ? $r['label'] : $r['name'] }}</span>
                        <span class="block truncate text-[10px] font-normal opacity-70">{{ $r['phone'] }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endforeach
