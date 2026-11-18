@extends('layouts.admin')
@php $title = $item->exists ? __('Edit story') : __('New story'); @endphp
@section('content')
<form method="POST" enctype="multipart/form-data" class="card-pad mx-auto max-w-2xl space-y-4"
      action="{{ $item->exists ? route('admin.stories.update', $item->id) : route('admin.stories.store') }}">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div><label class="field-label" for="couple_names">{{ __('Couple names') }}</label><input class="input" id="couple_names" name="couple_names" value="{{ old('couple_names', $item->couple_names) }}" required maxlength="200">@error('couple_names')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="story">{{ __('Story') }}</label><textarea class="input" id="story" name="story" rows="6">{{ old('story', $item->story) }}</textarea>@error('story')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div>
        <label class="field-label" for="image">{{ __('Photo') }}</label>
        @if($item->image)<img src="{{ asset('storage/'.$item->image) }}" class="mb-2 h-20 w-20 rounded-xl object-cover" alt="">@endif
        <input type="file" class="input" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        @error('image')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-3 rounded-2xl bg-ink-50 p-3 text-sm font-medium text-ink-800"><input type="checkbox" name="is_published" value="1" class="h-5 w-5 rounded" @checked(old('is_published', $item->is_published))> {{ __('Published') }}</label>
    <div class="flex gap-2"><button class="btn-primary flex-1">{{ __('Save') }}</button><a href="{{ route('admin.stories.index') }}" class="btn-ghost">{{ __('Cancel') }}</a></div>
</form>
@endsection
