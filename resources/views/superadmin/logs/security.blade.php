@extends('layouts.admin')
@php $title = __('Full logs'); @endphp
@section('content')
@include('superadmin.partials.logtabs')
<p class="mb-4 text-sm text-ink-600">{{ __('Failed logins in the last 24 hours') }}: <b>{{ $total }}</b></p>
<div class="grid gap-5 lg:grid-cols-2">
    <section class="card overflow-x-auto"><h2 class="px-4 pt-4 font-bold text-ink-900">{{ __('By IP address') }}</h2>
        <table class="w-full text-left text-sm"><thead class="text-xs uppercase text-ink-500"><tr><th class="p-3">IP</th><th>{{ __('Attempts') }}</th><th>{{ __('Phones') }}</th><th>{{ __('Last') }}</th></tr></thead><tbody class="divide-y divide-ink-50">
        @forelse($byIp as $r)<tr><td class="p-3"><a class="underline" href="{{ route('superadmin.logs.logins', ['q' => $r->ip, 'event' => 'failed']) }}">{{ $r->ip ?: '—' }}</a></td><td><b>{{ $r->attempts }}</b></td><td>{{ $r->phones }}</td><td class="text-xs text-ink-500">{{ \Illuminate\Support\Carbon::parse($r->last_at)->format('H:i') }}</td></tr>
        @empty<tr><td colspan="4" class="p-4 text-ink-400">{{ __('Nothing suspicious.') }}</td></tr>@endforelse
        </tbody></table></section>
    <section class="card overflow-x-auto"><h2 class="px-4 pt-4 font-bold text-ink-900">{{ __('By phone') }}</h2>
        <table class="w-full text-left text-sm"><thead class="text-xs uppercase text-ink-500"><tr><th class="p-3">{{ __('Phone') }}</th><th>{{ __('Attempts') }}</th><th>IPs</th><th>{{ __('Last') }}</th></tr></thead><tbody class="divide-y divide-ink-50">
        @forelse($byPhone as $r)<tr><td class="p-3"><a class="underline" href="{{ route('superadmin.logs.logins', ['q' => $r->phone, 'event' => 'failed']) }}">{{ $r->phone ?: '—' }}</a></td><td><b>{{ $r->attempts }}</b></td><td>{{ $r->ips }}</td><td class="text-xs text-ink-500">{{ \Illuminate\Support\Carbon::parse($r->last_at)->format('H:i') }}</td></tr>
        @empty<tr><td colspan="4" class="p-4 text-ink-400">{{ __('Nothing suspicious.') }}</td></tr>@endforelse
        </tbody></table></section>
</div>
@endsection
