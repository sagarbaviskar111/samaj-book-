@extends('layouts.admin')
@php $title = __('Full logs'); @endphp
@section('content')
@include('superadmin.partials.logtabs')
<form method="GET" class="card-pad mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="{{ __('Phone or IP') }}">
    <select class="input" name="event"><option value="">{{ __('All events') }}</option>@foreach(['login', 'failed', 'logout', 'dev_login'] as $e)<option value="{{ $e }}" @selected(request('event') === $e)>{{ $e }}</option>@endforeach</select>
    <input class="input" type="date" name="from" value="{{ request('from') }}" aria-label="{{ __('From') }}">
    <input class="input" type="date" name="to" value="{{ request('to') }}" aria-label="{{ __('To') }}">
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Filter') }}</button><a class="btn-ghost" href="{{ route('superadmin.logs.logins.export', request()->query()) }}">CSV</a></div>
</form>
<div class="card overflow-x-auto">
    <table class="w-full min-w-[40rem] text-left text-sm">
        <thead class="adm-thead text-xs uppercase tracking-wide text-ink-600"><tr><th>{{ __('When') }}</th><th>{{ __('Event') }}</th><th>{{ __('Phone') }}</th><th>{{ __('User') }}</th><th>IP</th><th>{{ __('Device') }}</th></tr></thead>
        <tbody class="divide-y divide-ink-50">
        @forelse($logs as $l)
            <tr>
                <td class="whitespace-nowrap">{{ $l->created_at?->format('d M, H:i:s') }}</td>
                <td><span class="{{ $l->event === 'failed' ? 'badge-bad' : 'badge-ink' }}">{{ $l->event }}</span></td>
                <td>{{ $l->phone ?: '—' }}</td>
                <td>@if($l->user)<a class="underline" href="{{ route('superadmin.users.show', $l->user_id) }}">{{ $l->user->name }}</a>@else — @endif</td>
                <td>{{ $l->ip }}</td>
                <td class="max-w-[16rem] truncate text-xs text-ink-500" title="{{ $l->user_agent }}">{{ $l->user_agent }}</td>
            </tr>
        @empty
            <tr><td colspan="6">@include('admin.partials.empty', ['title' => __('No entries yet.'), 'text' => ''])</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
