@extends('layouts.admin')
@php $title = __('Audit log'); @endphp
@section('content')
<div class="card-pad">
    <ul class="space-y-5">
    @forelse($logs as $l)
        <li class="adm-tl">
            <span class="adm-tl-dot"></span>
            <div class="flex flex-wrap items-start justify-between gap-2 text-sm">
                <div class="min-w-0">
                    <span class="badge-ink">{{ $l->action }}</span>
                    @if($l->target_type)<span class="ml-1 text-xs text-ink-500">{{ $l->target_type }}#{{ $l->target_id }}</span>@endif
                    <p class="mt-1 break-words text-ink-700">{{ $l->note ?: '—' }}</p>
                </div>
                <div class="text-right text-xs text-ink-500"><p class="font-semibold text-ink-700">{{ $l->admin?->name ?? __('system') }}@if($l->admin?->admin_area) <span class="badge-ink">{{ $l->admin->admin_area }}</span>@endif</p><p>{{ $l->created_at->format('d M Y, h:i A') }}</p></div>
            </div>
        </li>
    @empty
        <li>@include('admin.partials.empty', ['title' => __('No entries yet.'), 'text' => ''])</li>
    @endforelse
    </ul>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
