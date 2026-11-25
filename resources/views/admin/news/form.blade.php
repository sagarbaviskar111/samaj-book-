@extends('layouts.admin')
@php $title = $item->exists ? __('Edit item') : __('New item'); @endphp
@section('content')
<div class="grid gap-5 lg:grid-cols-5">
<form method="POST" enctype="multipart/form-data" class="card-pad space-y-4 lg:col-span-3"
      action="{{ $item->exists ? route('admin.news.update', $item->id) : route('admin.news.store') }}">
    @csrf
    @if($item->exists) @method('PUT') @endif

    <div>
        <label class="field-label">{{ __('Type') }}</label>
        <div class="grid grid-cols-3 gap-2" id="type-pick">
            @foreach(['news' => 'News', 'poster' => 'Poster', 'event' => 'Event'] as $k => $l)
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="{{ $k }}" class="peer sr-only" @checked(old('type', $item->type) === $k) onchange="document.getElementById('event-fields').hidden = this.value !== 'event'; admPrev()">
                    <span class="block min-h-11 rounded-xl border border-ink-200 py-2.5 text-center text-sm font-semibold text-ink-700 peer-checked:border-kumkum-600 peer-checked:bg-kumkum-600 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-ink-200">{{ __($l) }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div><label class="field-label" for="title">{{ __('Title') }}</label><input class="input" id="title" name="title" value="{{ old('title', $item->title) }}" required maxlength="200" oninput="admPrev()">@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="body">{{ __('Details') }}</label><textarea class="input" id="body" name="body" rows="6" oninput="admPrev()">{{ old('body', $item->body) }}</textarea>@error('body')<p class="field-error">{{ $message }}</p>@enderror</div>

    <div id="event-fields" class="grid gap-3 sm:grid-cols-2" @if(old('type', $item->type) !== 'event') hidden @endif>
        <div><label class="field-label" for="event_at">{{ __('Date and time') }}</label><input type="datetime-local" class="input" id="event_at" name="event_at" value="{{ old('event_at', $item->event_at?->format('Y-m-d\TH:i')) }}" onchange="admPrev()">@error('event_at')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="field-label" for="event_place">{{ __('Place') }}</label><input class="input" id="event_place" name="event_place" value="{{ old('event_place', $item->event_place) }}" maxlength="200" oninput="admPrev()"></div>
    </div>

    <div>
        <label class="field-label" for="image">{{ __('Image / poster') }}</label>
        @if($item->image)
            <div class="mb-2 flex items-center gap-3"><img src="{{ asset('storage/'.$item->image) }}" class="h-20 w-20 rounded-xl object-cover" alt="">
                <label class="flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" name="remove_image" value="1" class="rounded"> {{ __('Remove image') }}</label></div>
        @endif
        <div id="adm-drop" class="adm-drop relative grid min-h-32 place-items-center rounded-2xl border-2 border-dashed border-ink-200 p-4 text-center text-sm text-ink-600 transition">
            <div><svg class="mx-auto h-8 w-8 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4 4 4 3-3 5 5M4 5h16v14H4zM9 9h.01"/></svg>
            <p class="mt-1 font-semibold">{{ __('Drop an image here or tap to choose') }}</p><p id="adm-fname" class="text-xs text-ink-500"></p></div>
            <input type="file" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" id="image" name="image" accept="image/jpeg,image/png,image/webp" onchange="admPrev()">
        </div>
        <p class="mt-1 text-xs text-ink-500">{{ __('JPG/PNG/WebP, up to 6 MB. Re-saved at max 1600px.') }}</p>
        @error('image')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="space-y-2 rounded-2xl bg-ink-50 p-3 text-sm font-medium text-ink-800">
        <label class="flex items-center gap-3"><input type="checkbox" name="is_published" value="1" class="h-5 w-5 rounded" @checked(old('is_published', $item->is_published))> {{ __('Published') }}</label>
        <label class="flex items-center gap-3"><input type="checkbox" name="show_on_landing" value="1" class="h-5 w-5 rounded" @checked(old('show_on_landing', $item->show_on_landing))> {{ __('Show on landing page') }}</label>
        <label class="flex items-center gap-3"><input type="checkbox" name="notify_all" value="1" class="h-5 w-5 rounded" @checked(old('notify_all', true))> {{ __('Send notification to everyone') }}</label>
    </div>

    <div class="flex gap-2">
        <button class="btn-primary min-h-11 flex-1">{{ __('Save') }}</button>
        <a href="{{ route('admin.news.index') }}" class="btn-ghost min-h-11">{{ __('Cancel') }}</a>
    </div>
</form>

<aside class="lg:col-span-2">
    <div class="lg:sticky lg:top-24">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-ink-600">{{ __('Live preview') }}</p>
        <article class="card adm-preview overflow-hidden">
            <div class="grid h-44 place-items-center bg-gradient-to-br from-sandal to-gold-100">
                <img id="pv-img" alt="" class="{{ $item->image ? '' : 'hidden' }} h-full w-full object-cover" @if($item->image) src="{{ asset('storage/'.$item->image) }}" @endif>
                <span id="pv-noimg" class="{{ $item->image ? 'hidden' : '' }} font-display text-3xl font-bold text-ink-300">{{ __('No image') }}</span>
            </div>
            <div class="p-4">
                <span id="pv-type" class="badge-ink">{{ __(ucfirst(old('type', $item->type ?: 'news'))) }}</span>
                <h3 id="pv-title" class="mt-2 font-display text-xl font-bold text-ink-900">{{ old('title', $item->title) ?: __('Your title appears here') }}</h3>
                <p id="pv-event" class="mt-1 text-xs font-semibold text-gold-600"></p>
                <p id="pv-body" class="mt-2 line-clamp-5 whitespace-pre-line text-sm text-ink-700">{{ old('body', $item->body) }}</p>
            </div>
        </article>
    </div>
</aside>
</div>
@endsection
@push('scripts')
<script>
function admPrev(){
    var g = function(id){ return document.getElementById(id); };
    var t = document.querySelector('input[name=type]:checked');
    g('pv-type').textContent = t ? t.nextElementSibling.textContent : '';
    g('pv-title').textContent = g('title').value || @json(__('Your title appears here'));
    g('pv-body').textContent = g('body').value;
    var ev = [g('event_at').value ? g('event_at').value.replace('T', ' ') : '', g('event_place').value].filter(Boolean).join(' · ');
    g('pv-event').textContent = t && t.value === 'event' ? ev : '';
    var f = g('image').files && g('image').files[0];
    g('adm-fname').textContent = f ? f.name : '';
    if (f) { var r = new FileReader(); r.onload = function(e){ g('pv-img').src = e.target.result; g('pv-img').classList.remove('hidden'); g('pv-noimg').classList.add('hidden'); }; r.readAsDataURL(f); }
}
(function(){ var d = document.getElementById('adm-drop'); if(!d) return;
  ['dragenter','dragover'].forEach(function(e){ d.addEventListener(e, function(){ d.classList.add('is-over'); }); });
  ['dragleave','drop'].forEach(function(e){ d.addEventListener(e, function(){ d.classList.remove('is-over'); }); });
  admPrev(); })();
</script>
@endpush
