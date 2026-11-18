@extends('layouts.admin')
@php $title = __('Biodata'); @endphp
@section('content')
@include('admin.partials.tabs', ['tabs' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paused' => 'Paused', 'married' => 'Married', 'all' => 'All'], 'counts' => $counts, 'current' => $status, 'route' => 'admin.biodata.index', 'extra' => array_filter(['q' => $q, 'gender' => $gender, 'district' => $district])])

<form method="GET" class="card-pad mb-4 grid gap-2 sm:grid-cols-5">
    <input type="hidden" name="status" value="{{ $status }}">
    <input class="input sm:col-span-2" name="q" value="{{ $q }}" placeholder="{{ __('Search name') }}" type="search">
    <input class="input" name="district" value="{{ $district }}" placeholder="{{ __('District') }}">
    <select class="input" name="gender">
        <option value="">{{ __('Any gender') }}</option>
        <option value="male" @selected($gender === 'male')>{{ __('Male') }}</option>
        <option value="female" @selected($gender === 'female')>{{ __('Female') }}</option>
    </select>
    <button class="btn-primary">{{ __('Filter') }}</button>
</form>

<div class="grid gap-3 sm:grid-cols-2">
    @forelse($items as $b)
        <a href="{{ route('admin.biodata.show', $b->id) }}" class="card flex gap-3 p-3 transition hover:-translate-y-0.5 hover:shadow-glow">
            @if($b->primaryPhoto)
                <img src="{{ asset('storage/'.$b->primaryPhoto->path) }}" alt="" class="h-20 w-20 shrink-0 rounded-2xl object-cover" loading="lazy">
            @else
                <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-ink-50 font-display text-2xl font-bold text-ink-300">{{ mb_substr($b->name, 0, 1) }}</span>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <p class="truncate font-semibold text-ink-900">{{ $b->name }}</p>
                    @include('admin.partials.status', ['s' => $b->status])
                </div>
                <p class="text-xs text-ink-500">{{ ucfirst($b->gender) }} · {{ $b->dob?->age }} yrs · {{ $b->district ?: '—' }}</p>
                <p class="mt-1 truncate text-xs text-ink-400">{{ __('By') }} {{ $b->user?->name }} ({{ $b->added_by }})</p>
                <div class="mt-1.5 flex flex-wrap gap-1">
                    @if($b->verified)<span class="badge-ok">{{ __('Verified') }}</span>@endif
                    <span class="badge-ink">{{ $b->completeness() }}%</span>
                </div>
            </div>
        </a>
    @empty
        <div class="card-pad py-14 text-center sm:col-span-2">
            <p class="font-display text-xl font-bold text-ink-800">{{ __('Queue is clear') }}</p>
            <p class="mt-1 text-sm text-ink-400">{{ __('No biodata in this view.') }}</p>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
