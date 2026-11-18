@extends('layouts.admin')
@php $title = __('Payment').' #'.$payment->id; @endphp
@section('content')
<a href="{{ route('superadmin.payments.index') }}" class="mb-3 inline-block text-sm font-semibold text-ink-600">&larr; {{ __('Payments') }}</a>
<div class="grid gap-5 lg:grid-cols-3">
    <section class="card-pad lg:col-span-2">
        <p class="font-display text-3xl font-bold text-ink-900">{{ $payment->amountFormatted() }} @include('admin.partials.status', ['s' => $payment->status])</p>
        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @foreach([
                [__('Member'), $payment->user ? $payment->user->name.' · '.$payment->user->phone : '—'],
                [__('Purpose'), $payment->purpose], [__('Gateway'), $payment->gateway], [__('Method'), $payment->method],
                [__('Order ID'), $payment->gateway_order_id], [__('Payment ID'), $payment->gateway_payment_id],
                [__('Created'), $payment->created_at?->format('d M Y, H:i')], [__('Paid at'), $payment->paid_at?->format('d M Y, H:i')],
                [__('Entered by'), $payment->creator?->name], [__('Note'), $payment->note],
            ] as [$k, $v])
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ $k }}</dt><dd class="break-words font-medium text-ink-900">{{ $v ?: '—' }}</dd></div>
            @endforeach
        </dl>
        @if($payment->user)<a href="{{ route('superadmin.users.show', $payment->user_id) }}" class="mt-4 inline-block text-sm font-semibold underline">{{ __('Full profile (360°)') }}</a>@endif
    </section>
    <aside class="card-pad space-y-2 lg:self-start">
        <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Actions') }}</h3>
        @if($payment->status === 'pending')
            <form method="POST" action="{{ route('superadmin.payments.mark-paid', $payment->id) }}">@csrf<button class="btn-primary w-full">{{ __('Mark as paid') }}</button></form>
        @endif
        @if($payment->status === 'paid')
            <form method="POST" action="{{ route('superadmin.payments.refund', $payment->id) }}" onsubmit="return confirm('{{ __('Mark as refunded?') }}')">@csrf<button class="btn-ghost w-full">{{ __('Mark as refunded') }}</button></form>
        @endif
        @if(! in_array($payment->status, ['pending', 'paid'], true))<p class="text-sm text-ink-500">{{ __('No actions available.') }}</p>@endif
    </aside>
</div>
@endsection
