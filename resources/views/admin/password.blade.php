@extends('layouts.admin')
@php $title = __('My password'); @endphp
@section('content')
<form method="POST" action="{{ route('admin.password.update') }}" class="card-pad mx-auto max-w-md space-y-4">
    @csrf @method('PUT')
    <div><label class="field-label" for="current_password">{{ __('Current password') }}</label><input type="password" class="input" id="current_password" name="current_password" autocomplete="current-password" required>@error('current_password')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="password">{{ __('New password') }}</label><input type="password" class="input" id="password" name="password" autocomplete="new-password" minlength="8" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="password_confirmation">{{ __('Confirm new password') }}</label><input type="password" class="input" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required></div>
    <button class="btn-primary w-full">{{ __('Change password') }}</button>
</form>
@endsection
