<div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0">
    @foreach(['superadmin.logs.audit' => 'Audit log', 'superadmin.logs.logins' => 'Login log', 'superadmin.logs.security' => 'Security events'] as $r => $label)
        <a href="{{ route($r) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition {{ request()->routeIs($r) ? 'bg-kumkum-700 text-white shadow-glow' : 'bg-white text-ink-700 ring-1 ring-ink-100 hover:bg-ink-50' }}">{{ __($label) }}</a>
    @endforeach
</div>
