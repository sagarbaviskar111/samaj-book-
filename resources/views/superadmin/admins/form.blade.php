@extends('layouts.admin')
@php $title = $adm->exists ? __('Edit admin') : __('New admin'); @endphp
@section('content')
<form method="POST" class="card-pad mx-auto max-w-xl space-y-4"
      action="{{ $adm->exists ? route('superadmin.admins.update', $adm->id) : route('superadmin.admins.store') }}">
    @csrf
    @if($adm->exists) @method('PUT') @endif
    <div><label class="field-label" for="name">{{ __('Name') }}</label><input class="input" id="name" name="name" value="{{ old('name', $adm->name) }}" required maxlength="120">@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="phone">{{ __('Phone (login)') }}</label><input class="input" id="phone" name="phone" inputmode="numeric" value="{{ old('phone', $adm->phone) }}" required maxlength="10">@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="email">{{ __('Email (optional)') }}</label><input class="input" id="email" name="email" type="email" value="{{ old('email', $adm->email) }}" maxlength="190">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div>
        <label class="field-label" for="password">{{ $adm->exists ? __('New password (leave empty to keep)') : __('Password') }}</label>
        <input class="input" id="password" name="password" type="text" autocomplete="new-password" minlength="8" @required(! $adm->exists)>
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Save') }}</button><a href="{{ route('superadmin.admins.index') }}" class="btn-ghost">{{ __('Cancel') }}</a></div>
</form>
@endsection
