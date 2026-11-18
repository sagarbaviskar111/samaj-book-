@extends('layouts.admin')
@php $title = __('Record payment'); @endphp
@section('content')
<form method="POST" action="{{ route('superadmin.payments.store') }}" class="card-pad mx-auto max-w-xl space-y-4">
    @csrf
    <div><label class="field-label" for="user_phone">{{ __('Member phone (optional)') }}</label><input class="input" id="user_phone" name="user_phone" inputmode="numeric" maxlength="10" value="{{ old('user_phone') }}">@error('user_phone')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="amount">{{ __('Amount (₹)') }}</label><input class="input" id="amount" name="amount" type="number" step="0.01" min="1" value="{{ old('amount') }}" required>@error('amount')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="field-label" for="purpose">{{ __('Purpose') }}</label><select class="input" id="purpose" name="purpose">@foreach(\App\Models\Payment::PURPOSES as $s)<option value="{{ $s }}" @selected(old('purpose', 'donation') === $s)>{{ $s }}</option>@endforeach</select></div>
        <div><label class="field-label" for="method">{{ __('Method') }}</label><select class="input" id="method" name="method">@foreach(['cash', 'upi', 'bank', 'cheque', 'other'] as $s)<option value="{{ $s }}" @selected(old('method') === $s)>{{ $s }}</option>@endforeach</select></div>
    </div>
    <div><label class="field-label" for="paid_at">{{ __('Paid on (optional)') }}</label><input class="input" id="paid_at" name="paid_at" type="datetime-local" value="{{ old('paid_at') }}">@error('paid_at')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="note">{{ __('Note') }}</label><textarea class="input" id="note" name="note" rows="3" maxlength="1000">{{ old('note') }}</textarea></div>
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Save') }}</button><a href="{{ route('superadmin.payments.index') }}" class="btn-ghost">{{ __('Cancel') }}</a></div>
</form>
@endsection
