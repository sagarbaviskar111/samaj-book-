@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-xl space-y-4">
    <div class="flex items-center gap-2">
        <a href="{{ route('notifications.index') }}" class="rounded-full px-3 py-2 text-xl text-ink-700 hover:bg-ink-50" aria-label="{{ __('वापस') }}" title="{{ __('वापस') }}">&larr;</a>
        <h1 class="section-title">{{ __('सूचना सेटिंग') }}</h1>
    </div>

    <form method="POST" action="{{ route('notifications.settings.save') }}" class="card divide-y divide-ink-50 overflow-hidden">
        @csrf
        @foreach($labels as $key => $label)
            <label class="flex cursor-pointer items-center justify-between gap-3 px-4 py-4">
                <span class="text-base font-semibold text-ink-800">{{ ['like' => __('मेरी पोस्ट पर लाइक'), 'comment' => __('मेरी पोस्ट पर कमेंट'), 'interest' => __('रिश्ते की पसंद और जवाब'), 'new_biodata_area' => __('मेरे इलाके के नए बायोडाटा'), 'news' => __('समाचार, पोस्टर और कार्यक्रम'), 'system' => __('अकाउंट और दूसरी सूचनाएँ')][$key] ?? $label }}</span>
                <input type="checkbox" name="prefs[{{ $key }}]" value="1" class="peer sr-only" @checked($prefs[$key])>
                <span class="relative h-7 w-12 shrink-0 rounded-full bg-ink-100 transition peer-checked:bg-kumkum-600 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
            </label>
        @endforeach
        <div class="p-4"><button class="btn-primary min-h-12 w-full text-lg">{{ __('सेव करें') }}</button></div>
    </form>

    <div id="push-cta" class="hidden card card-pad space-y-3">
        <p class="text-sm font-semibold text-ink-800">{{ __('फ़ोन पर सूचना') }}</p>
        <p class="text-sm text-ink-700">{{ __('ऐप बंद होने पर भी सूचना पाएँ। आप इसे कभी भी बंद कर सकते हैं।') }}</p>
        <div class="flex gap-2">
            <button type="button" data-enable-push class="btn-gold">{{ __('सूचना चालू करें') }}</button>
            <button type="button" data-disable-push class="btn-ghost">{{ __('बंद करें') }}</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>window.SamajVapidPublic = @json($vapidPublic);</script>
@endpush
