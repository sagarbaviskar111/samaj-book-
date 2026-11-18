{{-- LOCAL DEV ONLY floating account switcher (bottom-left). Rendered only when DevLogin::enabled(). --}}
<details class="fixed bottom-24 right-3 z-[60] lg:bottom-4">
    <summary class="flex h-9 cursor-pointer list-none items-center gap-1.5 rounded-full bg-ink-950/90 px-3 text-[11px] font-bold tracking-wider text-gold-300 shadow-lg ring-1 ring-white/20 backdrop-blur">
        <span class="h-2 w-2 rounded-full bg-emerald-400"></span> DEV
    </summary>
    <div class="mt-2 max-h-[70vh] w-72 overflow-y-auto rounded-2xl bg-white p-3 shadow-soft ring-1 ring-ink-100">
        <p class="mb-2 text-xs font-bold text-ink-900">Switch account <span class="font-normal text-ink-400">(local only)</span></p>
        @include('partials.dev-accounts', ['variant' => 'menu'])
    </div>
</details>
