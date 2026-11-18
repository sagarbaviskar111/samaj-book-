@php($me = auth()->user())
<div class="mb-rise flex items-start gap-2" data-comment="{{ $comment->id }}">
    @include('feed.partials.avatar', ['u' => $comment->user, 'size' => 'h-8 w-8'])
    <div class="min-w-0 flex-1">
        <div class="inline-block max-w-full rounded-2xl rounded-tl-md bg-white px-3.5 py-2 shadow-sm ring-1 ring-ink-100">
            <span class="block text-sm font-bold text-ink-900">{{ $comment->user->name }}</span>
            <p class="break-words text-base leading-snug text-ink-800">{{ $comment->body }}</p>
        </div>
        <div class="mt-0.5 flex items-center gap-3 px-2 text-sm text-ink-600">
            <span>{{ $comment->created_at->diffForHumans(null, true, true) }}</span>
            @if($comment->user_id === $me->id || $post->user_id === $me->id || $me->isAdmin())
                <button type="button" class="min-h-9 px-1 font-bold text-rose-700 underline hover:text-rose-brand" data-comment-delete="{{ route('comments.destroy', $comment) }}">{{ __('हटाएँ') }}</button>
            @endif
        </div>
    </div>
</div>
