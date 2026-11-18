@extends('layouts.admin')
@php $title = $user->name; @endphp
@section('content')
<a href="{{ route('admin.users.index', ['status' => $user->status]) }}" class="mb-3 inline-block text-sm font-semibold text-ink-600">&larr; {{ __('Members') }}</a>

<div class="grid gap-5 lg:grid-cols-3">
    <section class="card overflow-hidden lg:col-span-2"><div class="gradient-hero h-20"></div><div class="p-4 sm:p-5">
        <div class="-mt-10 flex items-center gap-4">
            <span class="grid h-16 w-16 place-items-center rounded-full bg-gradient-to-br from-kumkum-500 to-kumkum-800 ring-4 ring-white font-display text-2xl font-bold text-white">{{ mb_substr($user->name, 0, 1) }}</span>
            <div class="min-w-0">
                <h2 class="truncate text-xl font-bold text-ink-900">{{ $user->name }}</h2>
                @include('admin.partials.status', ['s' => $user->status])
            </div>
        </div>
        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @foreach([['Phone', $user->phone], ['Email', $user->email], ['Gotra', $user->gotra], ['State', $user->state], ['District', $user->district], ['City', $user->city], ['Area', $user->area], ['Joined', $user->created_at->format('d M Y, h:i A')], ['Last seen', $user->last_seen_at?->diffForHumans()], ['Posts', $user->posts_count], ['Biodata', $user->biodatas_count]] as [$k, $v])
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ __($k) }}</dt><dd class="font-medium text-ink-900">{{ $v ?: '—' }}</dd></div>
            @endforeach
        </dl>
        @if($user->reject_reason)<p class="mt-4 rounded-xl bg-kumkum-50 p-3 text-sm text-kumkum-700"><b>{{ __('Reject reason') }}:</b> {{ $user->reject_reason }}</p>@endif

        @if($biodatas->count())
            <h3 class="mt-6 text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Biodata') }}</h3>
            <div class="mt-2 divide-y divide-ink-50 rounded-xl ring-1 ring-ink-100">
                @foreach($biodatas as $b)
                    <a href="{{ route('admin.biodata.show', $b->id) }}" class="flex items-center justify-between px-3 py-2.5 text-sm hover:bg-ink-50">
                        <span class="font-medium">{{ $b->name }} <span class="text-ink-400">· {{ $b->added_by }}</span></span>
                        @include('admin.partials.status', ['s' => $b->status])
                    </a>
                @endforeach
            </div>
        @endif
    </div></section>

    <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
        <section class="card-pad space-y-2">
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('Actions') }}</h3>
            @if(auth()->user()->isSuperAdmin())<a href="{{ route('superadmin.users.show', $user->id) }}" class="btn-gold w-full">{{ __('Full profile (360°)') }}</a>@endif
            @if($user->status !== 'active' && $user->status !== 'blocked')
                <form method="POST" action="{{ route('admin.users.approve', $user->id) }}">@csrf<button class="btn-primary w-full">{{ __('Approve') }}</button></form>
            @endif
            @if(auth()->user()->isFullAdmin())
            @if($user->status === 'blocked')
                <form method="POST" action="{{ route('admin.users.unblock', $user->id) }}">@csrf<button class="btn-gold w-full">{{ __('Unblock') }}</button></form>
            @else
                <form method="POST" action="{{ route('admin.users.block', $user->id) }}" onsubmit="return confirm('{{ __('Block this member?') }}')">@csrf<button class="btn-ghost w-full">{{ __('Block') }}</button></form>
            @endif
            <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" onsubmit="return confirm('{{ __('Generate a new temporary password?') }}')">@csrf<button class="btn-ghost w-full">{{ __('Reset password') }}</button></form>
            @endif

            @if($user->status !== 'rejected')
            <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" class="space-y-2 border-t border-ink-50 pt-3">@csrf
                <label class="field-label" for="reason">{{ __('Reject with reason') }}</label>
                <textarea id="reason" name="reason" rows="2" class="input" required minlength="3" maxlength="500" placeholder="{{ __('Reason shown to the member') }}"></textarea>
                <button class="btn-danger w-full">{{ __('Reject') }}</button>
            </form>
            @endif

            @if(auth()->user()->isFullAdmin())
            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="border-t border-ink-50 pt-3" onsubmit="return confirm('{{ __('Delete permanently? This cannot be undone.') }}')">@csrf @method('DELETE')
                <button class="w-full rounded-full px-4 py-2 text-sm font-semibold text-kumkum-700 hover:bg-kumkum-50">{{ __('Delete member') }}</button>
            </form>
            @endif
        </section>

        <section class="card-pad">
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500">{{ __('History') }}</h3>
            <ul class="mt-2 space-y-2 text-sm">
                @forelse($logs as $l)
                    <li class="adm-tl"><span class="adm-tl-dot"></span><span class="badge-ink">{{ $l->action }}</span> <span class="text-xs text-ink-500">{{ $l->created_at->diffForHumans() }}</span></li>
                @empty
                    <li class="text-ink-400">{{ __('No actions yet.') }}</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
@endsection
