@extends('layouts.admin')
@php $title = __('Members'); @endphp
@section('content')
@include('admin.partials.tabs', ['tabs' => ['pending' => 'Pending', 'active' => 'Active', 'rejected' => 'Rejected', 'blocked' => 'Blocked'], 'counts' => $counts, 'current' => $status, 'route' => 'admin.users.index', 'extra' => array_filter(['q' => $q, 'area' => $area])])

<form method="GET" class="card-pad mb-4 grid gap-2 sm:grid-cols-4">
    <input type="hidden" name="status" value="{{ $status }}">
    <input class="input sm:col-span-2" name="q" value="{{ $q }}" placeholder="{{ __('Search name or phone') }}" type="search" aria-label="{{ __('Search name or phone') }}">
    <select class="input" name="area" aria-label="{{ __('All areas') }}">
        <option value="">{{ __('All areas') }}</option>
        @foreach($areas as $a)<option value="{{ $a }}" @selected($area === $a)>{{ $a }}</option>@endforeach
    </select>
    <button class="btn-primary min-h-11">{{ __('Search') }}</button>
</form>

<form method="POST" action="{{ route('admin.users.bulk-approve') }}" id="bulk">
    @csrf
    @php($canBulk = $status === 'pending' && $users->count())
    @if($canBulk)
        <div class="mb-3 flex items-center justify-between gap-3 rounded-2xl bg-ink-50 px-4 py-2.5 text-sm">
            <label class="flex items-center gap-2 font-medium text-ink-700"><input type="checkbox" class="h-5 w-5 rounded" onclick="document.querySelectorAll('.bulk-cb').forEach(c => c.checked = this.checked); admBulk()"> {{ __('Select all') }}</label>
            <span class="text-xs text-ink-500">{{ $users->count() }} {{ __('on this page') }}</span>
        </div>
    @endif

    {{-- Desktop table --}}
    <div class="card hidden overflow-hidden md:block">
        <div class="max-h-[70vh] overflow-auto">
        <table class="adm-table w-full text-sm">
            <thead class="adm-thead text-xs uppercase tracking-wide text-ink-600">
                <tr>@if($canBulk)<th class="w-10"><span class="sr-only">{{ __('Select') }}</span></th>@endif<th>{{ __('Member') }}</th><th>{{ __('Area') }}</th><th>{{ __('Joined') }}</th><th>{{ __('Status') }}</th><th class="w-12"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    @if($canBulk)<td><input type="checkbox" name="ids[]" value="{{ $u->id }}" class="bulk-cb h-5 w-5 rounded" onchange="admBulk()" aria-label="{{ $u->name }}"></td>@endif
                    <td><a href="{{ route('admin.users.show', $u->id) }}" class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-kumkum-500 to-kumkum-800 font-display font-bold text-white">{{ mb_substr($u->name, 0, 1) }}</span>
                        <span class="min-w-0"><span class="block truncate font-semibold text-ink-900">{{ $u->name }}</span><span class="block text-xs text-ink-500">{{ $u->phone }}</span></span></a></td>
                    <td class="text-ink-700">{{ $u->area ?: ($u->district ?: '—') }}</td>
                    <td class="text-ink-600">{{ $u->created_at->format('d M Y') }}</td>
                    <td>@include('admin.partials.status', ['s' => $u->status])</td>
                    <td><details class="adm-menu relative"><summary class="grid h-9 w-9 place-items-center rounded-full hover:bg-ink-50" aria-label="{{ __('Actions') }}">&#8942;</summary>
                        <div class="absolute right-0 z-10 mt-1 w-40 rounded-xl bg-white p-1 text-sm shadow-soft ring-1 ring-ink-100"><a class="block rounded-lg px-3 py-2 hover:bg-ink-50" href="{{ route('admin.users.show', $u->id) }}">{{ __('View') }}</a>@if(auth()->user()->isSuperAdmin())<a class="block rounded-lg px-3 py-2 text-gold-600 hover:bg-ink-50" href="{{ route('superadmin.users.show', $u->id) }}">{{ __('Full profile') }}</a>@endif</div></details></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @if(! $users->count())@include('admin.partials.empty', ['title' => __('Nothing here'), 'text' => __('No members match this filter.')])@endif
    </div>

    {{-- Phone card list --}}
    <div class="card divide-y divide-ink-50 overflow-hidden md:hidden">
        @forelse($users as $u)
            <div class="flex items-center gap-3 p-3">
                @if($canBulk)<input type="checkbox" name="ids[]" value="{{ $u->id }}" class="bulk-cb h-6 w-6 shrink-0 rounded" onchange="admBulk()" aria-label="{{ $u->name }}">@endif
                <a href="{{ route('admin.users.show', $u->id) }}" class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-kumkum-500 to-kumkum-800 font-display text-lg font-bold text-white">{{ mb_substr($u->name, 0, 1) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-ink-900">{{ $u->name }}</span>
                        <span class="block truncate text-xs text-ink-600">{{ $u->phone }} · {{ $u->area ?: ($u->district ?: '—') }} · {{ $u->created_at->format('d M Y') }}</span>
                    </span>
                </a>
                @include('admin.partials.status', ['s' => $u->status])
            </div>
        @empty
            @include('admin.partials.empty', ['title' => __('Nothing here'), 'text' => __('No members match this filter.')])
        @endforelse
    </div>

    @if($canBulk)
    <div id="adm-bulkbar" class="adm-bulkbar fixed inset-x-4 bottom-20 z-40 mx-auto flex max-w-xl items-center justify-between gap-3 rounded-2xl bg-ink-900 px-4 py-3 text-white shadow-2xl lg:bottom-6">
        <span class="text-sm font-semibold"><span id="adm-bulkn">0</span> {{ __('selected') }}</span>
        <button class="btn-gold btn-sm min-h-10" onclick="return confirm('{{ __('Approve selected members?') }}')">{{ __('Approve selected') }}</button>
    </div>
    @endif
</form>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
@push('scripts')
<script>
function admBulk(){
    var seen = {}, n = 0;
    document.querySelectorAll('.bulk-cb').forEach(function(c){ if(c.checked && !seen[c.value]){ seen[c.value] = 1; n++; } });
    var bar = document.getElementById('adm-bulkbar'); if(!bar) return;
    document.getElementById('adm-bulkn').textContent = n;
    bar.classList.toggle('is-on', n > 0);
}
</script>
@endpush
