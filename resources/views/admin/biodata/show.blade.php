@extends('layouts.admin')
@php $title = $b->name; @endphp
@section('content')
@php
    $rows = [
        'Personal' => [['Gender', __(ucfirst($b->gender))], ['Date of birth', $b->dob?->format('d M Y').' ('.$b->dob?->age.' '.__('yrs').')'], ['Height', $b->height_cm ? $b->height_cm.' '.__('cm') : null], ['Weight', $b->weight_kg ? $b->weight_kg.' '.__('kg') : null], ['Complexion', $b->complexion], ['Blood group', $b->blood_group], ['Marital status', $b->marital_status ? __(ucfirst(str_replace('_', ' ', $b->marital_status))) : null], ['Manglik', is_null($b->manglik) ? null : ($b->manglik ? __('Yes') : __('No'))], ['Disability', $b->disability], ['Birth', trim(($b->birth_time ?? '').' '.($b->birth_place ?? ''))]],
        'Education and work' => [['Education', $b->education], ['Level', $b->education_level], ['Occupation', trim($b->occupation_type.' '.$b->occupation)], ['Company', $b->company], ['Income', $b->income_range.($b->income_private ? ' ('.__('private').')' : '')]],
        'Family' => [['Father', trim($b->father_name.' '.($b->father_occupation ? '('.$b->father_occupation.')' : ''))], ['Mother', trim($b->mother_name.' '.($b->mother_occupation ? '('.$b->mother_occupation.')' : ''))], ['Brothers', $b->brothers.' ('.$b->brothers_married.' '.__('married').')'], ['Sisters', $b->sisters.' ('.$b->sisters_married.' '.__('married').')'], ['Family type', $b->family_type], ['Mama gotra / village', trim($b->mama_gotra.' '.$b->mama_village)]],
        'Samaj and location' => [['Gotra', $b->gotra], ['Upjati', $b->upjati], ['Village', $b->village], ['Tehsil', $b->tehsil], ['District', $b->district], ['State', $b->state], ['Current city', $b->current_city], ['Area', $b->area]],
        'Contact (admin only)' => [['Contact phone', $b->contact_phone], ['Account phone', $b->user?->phone], ['Photo privacy', $b->photo_privacy], ['Requests go to parent', $b->requests_to_parent ? __('Yes') : __('No')]],
        'Preferences' => [['Age range', $b->pref_age_min ? $b->pref_age_min.' - '.$b->pref_age_max : null], ['Education', $b->pref_education_level], ['District', $b->pref_district]],
    ];
@endphp

<a href="{{ route('admin.biodata.index', ['status' => $b->status]) }}" class="mb-3 inline-block text-sm font-semibold text-ink-600">&larr; {{ __('Biodata') }}</a>

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <section class="card-pad">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-ink-900">{{ $b->name }}</h2>
                @include('admin.partials.status', ['s' => $b->status])
                @if($b->verified)<span class="badge-ok">&#10003; {{ __('Verified') }}</span>@endif
            </div>
            <p class="mt-1 text-sm text-ink-500">{{ __('Added by') }} {{ $b->added_by }} · <a class="font-semibold text-ink-600 underline" href="{{ route('admin.users.show', $b->user_id) }}">{{ $b->user?->name }}</a>@if(auth()->user()->isSuperAdmin() && $b->user_id) · <a class="font-semibold text-gold-600 underline" href="{{ route('superadmin.users.show', $b->user_id) }}">{{ __('Full profile (360°)') }}</a>@endif</p>

            @if($b->photos->count())
                <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                    @foreach($b->photos as $p)
                        <a href="{{ asset('storage/'.$p->path) }}" target="_blank" rel="noopener" class="relative shrink-0">
                            <img src="{{ asset('storage/'.$p->path) }}" alt="" class="h-44 w-36 rounded-2xl object-cover ring-1 ring-ink-100" loading="lazy">
                            @if($p->is_primary)<span class="badge-warn absolute left-2 top-2">{{ __('Primary') }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @else
                <p class="mt-4 rounded-xl bg-gold-50 p-3 text-sm text-gold-600">{{ __('No photos uploaded.') }}</p>
            @endif

            @if($b->reject_reason)<p class="mt-4 rounded-xl bg-kumkum-50 p-3 text-sm text-kumkum-700"><b>{{ __('Reject reason') }}:</b> {{ $b->reject_reason }}</p>@endif
        </section>

        @foreach($rows as $group => $list)
            <section class="card-pad">
                <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __($group) }}</h3>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-3">
                    @foreach($list as [$k, $v])
                        <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __($k) }}</dt><dd class="break-words font-medium text-ink-900">{{ filled(trim((string) $v)) ? $v : '—' }}</dd></div>
                    @endforeach
                </dl>
            </section>
        @endforeach
        @foreach(['About' => $b->about, 'Expectation' => $b->expectation] as $k => $v)
            <section class="card-pad"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __($k) }}</h3><p class="mt-2 whitespace-pre-line text-sm text-ink-800">{{ $v ?: '—' }}</p></section>
        @endforeach
    </div>

    <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
        <section class="card-pad">
            <div class="flex items-center justify-between"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Checklist') }}</h3><span class="badge-ink">{{ collect($checks)->filter()->count() }}/{{ count($checks) }}</span></div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100"><div class="h-full rounded-full bg-gradient-to-r from-kumkum-500 to-gold-400" style="width: {{ count($checks) ? round(collect($checks)->filter()->count() / count($checks) * 100) : 0 }}%"></div></div>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($checks as $label => $ok)
                    <li class="flex items-center gap-2 {{ $ok ? 'text-ink-800' : 'text-kumkum-700' }}">
                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full text-xs font-bold {{ $ok ? 'bg-tulsi-500/10 text-tulsi-600' : 'bg-kumkum-100 text-kumkum-700' }}">{{ $ok ? '✓' : '✕' }}</span>{{ __($label) }}
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 grid grid-cols-2 gap-2 rounded-xl bg-ink-50 p-3 text-xs">
                <div><p class="font-semibold uppercase text-ink-400">{{ __('Account') }}</p><p class="font-medium text-ink-900">{{ $b->user?->name }}</p><p>{{ $b->user?->phone }}</p><p>{{ $b->user?->district }}</p></div>
                <div><p class="font-semibold uppercase text-ink-400">{{ __('Biodata') }}</p><p class="font-medium text-ink-900">{{ $b->name }}</p><p>{{ $b->contact_phone ?: '—' }}</p><p>{{ $b->district }}</p></div>
            </div>
        </section>

        <section class="card-pad space-y-2 ring-2 ring-ink-200">
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Decision') }}</h3>
            @if($b->status !== 'approved')
                <form method="POST" action="{{ route('admin.biodata.approve', $b->id) }}">@csrf<button class="btn-primary w-full">{{ __('Approve') }}</button></form>
            @endif
@if(auth()->user()->isFullAdmin())
            <form method="POST" action="{{ route('admin.biodata.verify', $b->id) }}">@csrf<button class="btn-gold w-full">{{ $b->verified ? __('Remove verified badge') : __('Mark verified') }}</button></form>

            <form method="POST" action="{{ route('admin.biodata.status', $b->id) }}" class="flex gap-2">@csrf
                <select name="status" class="input">
                    @foreach(['approved', 'paused', 'married'] as $s)<option value="{{ $s }}" @selected($b->status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach
                </select>
                <button class="btn-ghost">{{ __('Set') }}</button>
            </form>
            @endif

            @if($b->status !== 'rejected')
            <form method="POST" action="{{ route('admin.biodata.reject', $b->id) }}" class="space-y-2 border-t border-ink-50 pt-3">@csrf
                <label class="field-label" for="reason">{{ __('Reject with reason') }}</label>
                <textarea id="reason" name="reason" rows="2" class="input" required minlength="3" maxlength="500" placeholder="{{ __('What should the owner fix?') }}"></textarea>
                <button class="btn-danger w-full">{{ __('Reject') }}</button>
            </form>
            @endif

            @if(auth()->user()->isFullAdmin())
            <form method="POST" action="{{ route('admin.biodata.destroy', $b->id) }}" class="border-t border-ink-50 pt-3" onsubmit="return confirm('{{ __('Delete this biodata permanently?') }}')">@csrf @method('DELETE')
                <button class="w-full rounded-full px-4 py-2 text-sm font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete biodata') }}</button>
            </form>
            @endif
        </section>
    </aside>
</div>
@endsection
