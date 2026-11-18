@extends('layouts.admin')
@php $title = __('Registrants'); @endphp
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="font-display text-xl font-bold text-ink-900">{{ $event->title }}</p>
        <p class="text-sm text-ink-500">{{ $event->startsHuman() }} · {{ $regs->total() }} {{ __('registered') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.events.index') }}" class="btn-ghost btn-sm">&larr; {{ __('Events') }}</a>
        <a href="{{ route('admin.events.registrants.csv', $event->id) }}" class="btn-gold btn-sm">{{ __('Export CSV') }}</a>
    </div>
</div>

<div class="card overflow-x-auto border-t-4 border-gold-400">
    <table class="w-full min-w-[34rem] text-left text-sm">
        <thead class="bg-sandal text-xs uppercase tracking-wide text-ink-600">
            <tr><th class="px-4 py-3">{{ __('Name') }}</th><th class="px-4 py-3">{{ __('Phone') }}</th><th class="px-4 py-3">{{ __('Area') }}</th><th class="px-4 py-3">{{ __('Registered at') }}</th><th class="px-4 py-3">{{ __('Joined') }}</th></tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
            @forelse($regs as $r)
                <tr>
                    <td class="px-4 py-3 font-semibold text-ink-900">{{ $r->user?->name }}</td>
                    <td class="px-4 py-3">{{ $r->user?->phone }}</td>
                    <td class="px-4 py-3">{{ $r->user?->district ?: $r->user?->city }}</td>
                    <td class="px-4 py-3">{{ $r->created_at?->format('d M, h:i A') }}</td>
                    <td class="px-4 py-3"><span class="{{ $r->joined_at ? 'badge-ok' : 'badge-ink' }}">{{ $r->joined_at ? __('Yes') : __('No') }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-ink-500">{{ __('No one has registered yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $regs->links() }}</div>
@endsection
