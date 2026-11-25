@extends('layouts.admin')
@php $title = __('Match requests'); @endphp
@section('content')
@include('admin.partials.tabs', ['tabs' => ['all' => 'All requests', 'sent' => 'Sent', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'expired' => 'Expired'], 'counts' => $counts, 'current' => $status, 'route' => 'admin.interests.index'])

<div class="space-y-3">
@forelse($interests as $i)
    @php
        $accepted = $i->status === 'accepted';
        $badge = ['accepted' => 'badge-ok', 'sent' => 'badge-warn', 'rejected' => 'badge-bad'][$i->status] ?? 'badge-ink';
    @endphp
    <article class="card-pad border-l-4 {{ $accepted ? 'border-l-green-500' : 'border-l-ink-200' }}">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="{{ $badge }}">{{ $accepted ? __('Accepted') : __(ucfirst($i->status)) }}</span>
            <span class="text-xs text-ink-400">
                {{ __('Sent') }} {{ $i->created_at->format('d M Y, h:i A') }}
                @if($i->responded_at) · {{ __('Replied') }} {{ $i->responded_at->format('d M Y') }} @endif
            </span>
        </div>
        <div class="mt-3 grid items-center gap-2 sm:grid-cols-[1fr_auto_1fr]">
            @foreach([[__('From'), $i->fromBiodata, $i->fromUser], [null], [__('To'), $i->toBiodata, $i->toUser]] as $part)
                @if($part[0] === null)
                    <div class="hidden text-center text-xl text-ink-300 sm:block">→</div>
                @else
                    @php [$label, $bd, $u] = $part; @endphp
                    <div class="rounded-2xl bg-ink-50 p-3 text-sm">
                        <p class="text-[11px] font-semibold uppercase text-ink-400">{{ $label }}</p>
                        <p class="font-semibold">
                            @if($bd)<a class="underline" href="{{ route('admin.biodata.show', $bd->id) }}">{{ $bd->name }}</a>@else — @endif
                            @if($bd)<span class="ml-1 {{ $bd->gender === 'male' ? 'badge-ink' : 'badge-warn' }}">{{ $bd->gender === 'male' ? __('Boy') : __('Girl') }}</span>@endif
                        </p>
                        @if($bd)<p class="text-xs text-ink-500">{{ $bd->dob ? $bd->age.' '.__('yrs') : '' }} · {{ $bd->current_city ?: $bd->district }}</p>@endif
                        <p class="text-xs text-ink-500">
                            {{ __('Account') }}:
                            @if($u)<a class="underline" href="{{ route('admin.users.show', $u->id) }}">{{ $u->name }}</a> · {{ $u->phone }}@else — @endif
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
        @if($i->message)<p class="mt-2 text-xs text-ink-500"><b>{{ __('Message') }}:</b> {{ \Illuminate\Support\Str::limit($i->message, 200) }}</p>@endif
    </article>
@empty
    <p class="card-pad text-center text-ink-400">{{ __('No requests yet.') }}</p>
@endforelse
</div>
<div class="mt-4">{{ $interests->links() }}</div>
@endsection
