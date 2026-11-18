@extends('layouts.admin')
@php $title = __('Settings'); @endphp
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="mx-auto max-w-3xl space-y-5">
    @csrf @method('PUT')
    <section class="card-pad space-y-4">
        <div><h2 class="text-lg font-bold text-ink-900">{{ __('Site') }}</h2><p class="text-sm text-ink-500">{{ __('Name and tagline shown across the app.') }}</p></div>
        <div><label class="field-label" for="site_name">{{ __('Site name') }}</label><input class="input" id="site_name" name="site_name" value="{{ old('site_name', $s['site_name']) }}" required>@error('site_name')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="field-label" for="site_tagline">{{ __('Tagline') }}</label><input class="input" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $s['site_tagline']) }}">@error('site_tagline')<p class="field-error">{{ $message }}</p>@enderror</div>
    </section>

    <section class="card-pad space-y-4">
        <div><h2 class="text-lg font-bold text-ink-900">{{ __('Members and limits') }}</h2><p class="text-sm text-ink-500">{{ __('Control how members join and interact.') }}</p></div>
        <div><label class="field-label" for="interest_daily_limit">{{ __('Interests allowed per member per day') }}</label><input type="number" min="1" max="200" class="input" id="interest_daily_limit" name="interest_daily_limit" value="{{ old('interest_daily_limit', $s['interest_daily_limit']) }}" required>@error('interest_daily_limit')<p class="field-error">{{ $message }}</p>@enderror</div>
        <label class="flex items-start gap-3 rounded-2xl bg-ink-50 p-3 text-sm font-medium text-ink-800">
            <input type="checkbox" name="auto_approve" value="1" class="mt-0.5 h-5 w-5 rounded" @checked(old('auto_approve', $s['auto_approve']))>
            <span>{{ __('Auto-approve new members') }}<span class="block text-xs font-normal text-ink-500">{{ __('Placeholder: stored for later use, not enforced yet.') }}</span></span>
        </label>
    </section>

    <section class="card-pad space-y-4">
        <div><h2 class="text-lg font-bold text-ink-900">{{ __('Contact') }}</h2><p class="text-sm text-ink-500">{{ __('Where members can reach the samaj team.') }}</p></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="field-label" for="contact_email">{{ __('Contact email') }}</label><input type="email" class="input" id="contact_email" name="contact_email" value="{{ old('contact_email', $s['contact_email']) }}">@error('contact_email')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div><label class="field-label" for="contact_phone">{{ __('Contact phone') }}</label><input class="input" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $s['contact_phone']) }}">@error('contact_phone')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
    </section>
    <button class="btn-primary min-h-11 w-full sm:w-auto">{{ __('Save settings') }}</button>
</form>
@endsection
