@extends('layouts.admin')
@php $title = __('Integrations & keys'); @endphp
@section('content')
<p class="mb-4 rounded-xl bg-gold-50 p-3 text-sm text-gold-600 ring-1 ring-gold-200">{{ __('Secrets are encrypted at rest and never shown in full. Leave a field blank to keep the stored value; type a new value to replace it.') }}</p>
@if(session('check_error'))<div class="mb-4 rounded-xl bg-kumkum-50 px-4 py-3 text-sm font-medium text-kumkum-700 ring-1 ring-kumkum-200" role="alert">{{ session('check_error') }}</div>@endif

<section class="card-pad mb-5">
    <h2 class="text-lg font-bold text-ink-900">{{ __('Payment settings') }}</h2>
    <form method="POST" action="{{ route('superadmin.integrations.payment-config') }}" class="mt-3 grid gap-3 sm:grid-cols-2">
        @csrf @method('PUT')
        <div><label class="field-label" for="pc-currency">{{ __('Currency') }}</label><input class="input" id="pc-currency" name="currency" maxlength="3" value="{{ old('currency', $config['currency']) }}" required></div>
        <div><label class="field-label" for="pc-gw">{{ __('Default gateway') }}</label><select class="input" id="pc-gw" name="default_gateway">@foreach(['razorpay', 'phonepe', 'stripe', 'manual'] as $g)<option value="{{ $g }}" @selected(old('default_gateway', $config['default_gateway']) === $g)>{{ ucfirst($g) }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><label class="field-label" for="pc-fee">{{ __('Platform fee text') }}</label><input class="input" id="pc-fee" name="platform_fee_text" maxlength="300" value="{{ old('platform_fee_text', $config['platform_fee_text']) }}"></div>
        <label class="flex items-center gap-2 text-sm font-medium sm:col-span-2"><input type="checkbox" name="enabled" value="1" class="h-5 w-5 rounded" @checked(old('enabled', $config['enabled']))> {{ __('Enable payments') }}</label>
        <div class="sm:col-span-2"><button class="btn-primary">{{ __('Save') }}</button></div>
    </form>
</section>

@foreach(collect($providers)->groupBy('section', true) as $section => $items)
    <h2 class="mb-2 mt-6 text-sm font-bold uppercase tracking-wide text-ink-500">{{ __($section) }}</h2>
    <div class="grid gap-4 lg:grid-cols-2">
    @foreach($items as $group => $def)
        <section class="card-pad">
            <div class="flex items-center justify-between"><h3 class="font-display text-lg font-bold text-ink-900">{{ $def['title'] }}</h3>
                <form method="POST" action="{{ route('superadmin.integrations.check', $group) }}">@csrf<button class="btn-ghost btn-sm" title="{{ __('Checks the key format only, no network call') }}">{{ __('Check format') }}</button></form></div>
            <form method="POST" action="{{ route('superadmin.integrations.update', $group) }}" class="mt-3 space-y-3" autocomplete="off">
                @csrf @method('PUT')
                @foreach($def['fields'] as $key => $f)
                    @php $id = $group.'-'.$key; $cur = $values[$group][$key] ?? null; @endphp
                    <div>
                        <label class="field-label" for="{{ $id }}">{{ __($f[0]) }} @if($cur)<span class="ml-1 font-mono text-xs text-ink-500">{{ $cur }}</span>@else<span class="ml-1 text-xs text-ink-400">{{ __('not set') }}</span>@endif</label>
                        @if(($f[2] ?? null) === 'select')
                            <select class="input" id="{{ $id }}" name="{{ $key }}"><option value="">{{ __('(keep current)') }}</option>@foreach($f[3] as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach</select>
                        @else
                            <input class="input" id="{{ $id }}" name="{{ $key }}" type="{{ $f[1] ? 'password' : 'text' }}" autocomplete="new-password" placeholder="{{ $cur ? __('Enter new value to replace') : '' }}" value="">
                        @endif
                    </div>
                @endforeach
                <button class="btn-primary">{{ __('Update') }}</button>
            </form>
        </section>
    @endforeach
    </div>
@endforeach

<h2 class="mb-2 mt-6 text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Push (VAPID)') }}</h2>
<section class="card-pad">
    <p class="text-xs text-ink-500">{{ __('Read from .env on the server. Not editable here.') }}</p>
    <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
        <div class="min-w-0"><dt class="text-[11px] font-semibold uppercase text-ink-400">{{ __('Public key') }}</dt><dd class="break-all font-mono text-xs">{{ $push['public'] ?: '—' }}</dd></div>
        <div><dt class="text-[11px] font-semibold uppercase text-ink-400">{{ __('Private key') }}</dt><dd class="font-mono text-xs">{{ $push['private'] ?: '—' }}</dd></div>
        <div><dt class="text-[11px] font-semibold uppercase text-ink-400">{{ __('Subject') }}</dt><dd class="break-all font-mono text-xs">{{ $push['subject'] ?: '—' }}</dd></div>
    </dl>
</section>

<h2 class="mb-2 mt-6 text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Custom keys') }}</h2>
<section class="card-pad">
    <ul class="divide-y divide-ink-50 text-sm">
        @forelse($custom as $c)
            <li class="flex items-center justify-between gap-3 py-2"><span class="min-w-0"><b>{{ $c['key'] }}</b> <span class="font-mono text-xs text-ink-500">{{ $c['masked'] }}</span></span>
                <form method="POST" action="{{ route('superadmin.integrations.custom.destroy', $c['key']) }}" onsubmit="return confirm('{{ __('Remove this key?') }}')">@csrf @method('DELETE')<button class="text-xs font-semibold text-kumkum-700">{{ __('Remove') }}</button></form></li>
        @empty
            <li class="py-2 text-ink-400">{{ __('No custom keys yet.') }}</li>
        @endforelse
    </ul>
    <form method="POST" action="{{ route('superadmin.integrations.custom.store') }}" class="mt-4 grid gap-3 sm:grid-cols-3" autocomplete="off">
        @csrf
        <div><label class="field-label" for="cu-name">{{ __('Name') }}</label><input class="input" id="cu-name" name="name" maxlength="80" placeholder="PAYTM_MERCHANT_KEY" required></div>
        <div class="sm:col-span-2"><label class="field-label" for="cu-value">{{ __('Value') }}</label><input class="input" id="cu-value" name="value" type="password" autocomplete="new-password" required></div>
        <div class="sm:col-span-3"><button class="btn-gold">{{ __('Add / replace key') }}</button></div>
    </form>
</section>
@endsection
