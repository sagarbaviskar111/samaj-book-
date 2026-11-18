@extends('layouts.admin')
@php $title = __('Payments'); $inr = fn ($p) => \App\Models\Payment::inr($p); @endphp
@section('content')
<div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
    <div class="card-pad"><p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __('Paid this month') }}</p><p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ $inr($totals['paid_month']) }}</p></div>
    <div class="card-pad"><p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __('Pending') }}</p><p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ $inr($totals['pending']) }}</p></div>
    <div class="card-pad"><p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __('Refunded') }}</p><p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ $inr($totals['refunded']) }}</p></div>
</div>

<form method="GET" class="card-pad mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
    <input class="input lg:col-span-2" name="q" value="{{ request('q') }}" placeholder="{{ __('User or reference') }}">
    <select class="input" name="status"><option value="">{{ __('Any status') }}</option>@foreach(\App\Models\Payment::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <select class="input" name="gateway"><option value="">{{ __('Any gateway') }}</option>@foreach(\App\Models\Payment::GATEWAYS as $s)<option value="{{ $s }}" @selected(request('gateway') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <select class="input" name="purpose"><option value="">{{ __('Any purpose') }}</option>@foreach(\App\Models\Payment::PURPOSES as $s)<option value="{{ $s }}" @selected(request('purpose') === $s)>{{ $s }}</option>@endforeach</select>
    <input class="input" type="date" name="from" value="{{ request('from') }}" aria-label="{{ __('From') }}">
    <input class="input" type="date" name="to" value="{{ request('to') }}" aria-label="{{ __('To') }}">
    <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-7">
        <button class="btn-primary">{{ __('Filter') }}</button>
        <a class="btn-ghost" href="{{ route('superadmin.payments.export', request()->query()) }}">CSV</a>
        <a class="btn-gold ml-auto" href="{{ route('superadmin.payments.create') }}">+ {{ __('Record payment') }}</a>
    </div>
</form>

<div class="card mt-5 overflow-x-auto">
    <table class="w-full min-w-[40rem] text-left text-sm">
        <thead class="adm-thead text-xs uppercase tracking-wide text-ink-600"><tr><th>#</th><th>{{ __('When') }}</th><th>{{ __('Member') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Purpose') }}</th><th>{{ __('Gateway') }}</th><th>{{ __('Status') }}</th></tr></thead>
        <tbody class="divide-y divide-ink-50">
        @forelse($payments as $p)
            <tr>
                <td><a class="font-semibold underline" href="{{ route('superadmin.payments.show', $p->id) }}">{{ $p->id }}</a></td>
                <td class="whitespace-nowrap">{{ ($p->paid_at ?? $p->created_at)?->format('d M Y') }}</td>
                <td>{{ $p->user?->name ?? '—' }}</td>
                <td class="font-semibold">{{ $p->amountFormatted() }}</td>
                <td>{{ $p->purpose }}</td>
                <td>{{ $p->gateway }}</td>
                <td>@include('admin.partials.status', ['s' => $p->status])</td>
            </tr>
        @empty
            <tr><td colspan="7">@include('admin.partials.empty', ['title' => __('No payments yet.'), 'text' => __('Record offline donations or wait for gateway payments.')])</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
