@extends('layouts.admin')
@php $title = $event->exists ? __('Edit event') : __('New event'); @endphp
@section('content')
<form method="POST" enctype="multipart/form-data" class="card-pad mx-auto max-w-3xl space-y-4"
      action="{{ $event->exists ? route('admin.events.update', $event->id) : route('admin.events.store') }}">
    @csrf
    @if($event->exists) @method('PUT') @endif

    <div><label class="field-label" for="title">{{ __('Title') }}</label><input class="input" id="title" name="title" value="{{ old('title', $event->title) }}" required maxlength="200">@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="description">{{ __('Details') }}</label><textarea class="input" id="description" name="description" rows="5">{{ old('description', $event->description) }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div><label class="field-label" for="starts_at">{{ __('Starts at') }}</label><input type="datetime-local" class="input" id="starts_at" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required>@error('starts_at')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="field-label" for="ends_at">{{ __('Ends at (optional)') }}</label><input type="datetime-local" class="input" id="ends_at" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}">@error('ends_at')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="field-label" for="place">{{ __('Place') }}</label><input class="input" id="place" name="place" value="{{ old('place', $event->place) }}" maxlength="200">@error('place')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="field-label" for="join_url">{{ __('Join link (Meet / Zoom, optional)') }}</label><input type="url" class="input" id="join_url" name="join_url" value="{{ old('join_url', $event->join_url) }}" maxlength="500" placeholder="https://">@error('join_url')<p class="field-error">{{ $message }}</p>@enderror</div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="field-label" for="area">{{ __('Area') }}</label>
            @if(auth()->user()->isFullAdmin())
                <select class="input" id="area" name="area">
                    <option value="">{{ __('All areas') }}</option>
                    @foreach($districts as $d)<option value="{{ $d }}" @selected(old('area', $event->area) === $d)>{{ $d }}</option>@endforeach
                </select>
            @else
                <input class="input" value="{{ auth()->user()->admin_area }}" disabled>
            @endif
            @error('area')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label" for="reminder_interval_min">{{ __('Join popup repeats every') }}</label>
            <select class="input" id="reminder_interval_min" name="reminder_interval_min">
                @foreach(\App\Models\Event::INTERVALS as $m)<option value="{{ $m }}" @selected((int) old('reminder_interval_min', $event->reminder_interval_min) === $m)>{{ $m }} {{ __('minutes') }}</option>@endforeach
            </select>
            @error('reminder_interval_min')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="field-label" for="image">{{ __('Cover image') }}</label>
        @if($event->image)
            <div class="mb-2 flex items-center gap-3"><img src="{{ $event->imageUrl() }}" class="h-20 w-32 rounded-xl object-cover" alt="">
                <label class="flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" name="remove_image" value="1" class="rounded"> {{ __('Remove image') }}</label></div>
        @endif
        <input type="file" class="input" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        <p class="mt-1 text-xs text-ink-500">{{ __('JPG/PNG/WebP, up to 6 MB. Re-saved at max 1600px.') }}</p>
        @error('image')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 rounded-2xl bg-ink-50 p-3 text-sm font-medium text-ink-800">
        <label class="flex items-center gap-3"><input type="checkbox" name="is_published" value="1" class="h-5 w-5 rounded" @checked(old('is_published', $event->is_published))> {{ __('Published') }}</label>
        <label class="flex items-center gap-3"><input type="checkbox" name="registration_open" value="1" class="h-5 w-5 rounded" @checked(old('registration_open', $event->registration_open))> {{ __('Registration open') }}</label>
        <label class="flex items-center gap-3"><input type="checkbox" name="popup_enabled" value="1" class="h-5 w-5 rounded" @checked(old('popup_enabled', $event->popup_enabled))> {{ __('Show popups to members (register and join)') }}</label>
        <label class="flex items-center gap-3"><input type="checkbox" name="notify_all" value="1" class="h-5 w-5 rounded" @checked(old('notify_all', ! $event->exists))> {{ __('Notify members when published') }}</label>
    </div>

    <div class="flex gap-2">
        <button class="btn-primary min-h-11 flex-1">{{ __('Save') }}</button>
        <a href="{{ route('admin.events.index') }}" class="btn-ghost min-h-11">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
