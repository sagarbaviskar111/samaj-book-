@extends('layouts.admin')
@php $title = $sub->exists ? __('Edit sub-admin') : __('New sub-admin'); @endphp
@section('content')
<form method="POST" class="card-pad mx-auto max-w-xl space-y-4"
      action="{{ $sub->exists ? route('admin.subadmins.update', $sub->id) : route('admin.subadmins.store') }}">
    @csrf
    @if($sub->exists) @method('PUT') @endif
    <div><label class="field-label" for="name">{{ __('Name') }}</label><input class="input" id="name" name="name" value="{{ old('name', $sub->name) }}" required maxlength="120">@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="phone">{{ __('Phone (login)') }}</label><input class="input" id="phone" name="phone" inputmode="numeric" value="{{ old('phone', $sub->phone) }}" required maxlength="10">@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div>
        <label class="field-label" for="admin_area">{{ __('Area (district)') }}</label>
        <select class="input" id="admin_area" name="admin_area" required>
            <option value="">{{ __('Select') }}</option>
            @foreach($districts as $d)<option value="{{ $d }}" @selected(old('admin_area', $sub->admin_area) === $d)>{{ $d }}</option>@endforeach
        </select>
        @error('admin_area')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="field-label" for="status">{{ __('Status') }}</label>
        <select class="input" id="status" name="status">
            <option value="active" @selected(old('status', $sub->status) === 'active')>{{ __('Active') }}</option>
            <option value="blocked" @selected(old('status', $sub->status) === 'blocked')>{{ __('Deactivated') }}</option>
        </select>
    </div>
    <div>
        <label class="field-label" for="password">{{ $sub->exists ? __('New password (leave empty to keep)') : __('Password') }}</label>
        <input class="input" id="password" name="password" type="text" autocomplete="new-password" minlength="8" @required(! $sub->exists)>
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Save') }}</button><a href="{{ route('admin.subadmins.index') }}" class="btn-ghost">{{ __('Cancel') }}</a></div>
</form>
@endsection
