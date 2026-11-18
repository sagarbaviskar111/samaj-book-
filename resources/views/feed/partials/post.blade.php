@php($me = auth()->user())
@php($ro = $me->isPending())
<article class="card overflow-hidden" data-post="{{ $post->id }}">
    <div class="flex items-center gap-3 px-4 pt-4">
        <span class="rounded-full bg-gradient-to-tr from-gold-400 via-kumkum-500 to-kumkum-700 p-[2px]">@include('feed.partials.avatar', ['u' => $post->user, 'size' => 'h-10 w-10'])</span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-ink-900">{{ $post->user->name }}</p>
            <p class="truncate text-sm text-ink-700">
                @if($post->user->city){{ $post->user->city }} &middot; @endif{{ $post->created_at->diffForHumans() }}
                @if($post->visibility === 'city') &middot; <span class="font-medium text-gold-600">{{ __('सिर्फ़ मेरा शहर') }}</span> @endif
            </p>
        </div>
        @unless($ro)
        <details class="relative">
            <summary class="grid h-10 w-10 cursor-pointer list-none place-items-center rounded-full text-ink-600 transition hover:bg-ink-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ink-100" aria-label="{{ __('और विकल्प') }}" title="{{ __('और विकल्प') }}">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
            </summary>
            <div class="mb-rise absolute right-0 z-10 mt-1 w-44 overflow-hidden rounded-2xl bg-white p-1 text-sm shadow-xl ring-1 ring-ink-100">
                @if($post->user_id !== $me->id)
                    <button type="button" class="block w-full rounded-xl px-4 py-3 text-left text-ink-700 hover:bg-ink-50" data-report data-type="post" data-id="{{ $post->id }}" data-url="{{ route('reports.store') }}">{{ __('पोस्ट की शिकायत करें') }}</button>
                @endif
                @if($post->user_id === $me->id || $me->isAdmin())
                    <button type="button" class="block w-full rounded-xl px-4 py-3 text-left font-medium text-rose-brand hover:bg-rose-50" data-post-delete="{{ route('posts.destroy', $post) }}">{{ __('पोस्ट हटाएँ') }}</button>
                @endif
            </div>
        </details>
        @endunless
    </div>

    @if($post->body && !$post->image)
        <p class="whitespace-pre-line break-words px-4 pb-1 pt-3 text-base leading-relaxed text-ink-800">{{ $post->body }}</p>
    @endif

    @if($post->image)
        {{-- single tap opens the post, double tap likes (see scripts) --}}
        <a href="{{ route('posts.show', $post) }}" @unless($ro) data-dbl @endunless class="mb-media relative mt-3 block px-3" aria-label="{{ __('पोस्ट खोलें') }}">
            <span class="relative block overflow-hidden rounded-2xl bg-ink-50 ring-1 ring-ink-100">
                <img src="{{ asset('storage/'.$post->image) }}" alt="{{ \Illuminate\Support\Str::limit($post->body ?: __('पोस्ट की फ़ोटो').': '.$post->user->name, 80) }}" loading="lazy" class="max-h-[34rem] min-h-[12rem] w-full object-cover">
                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-ink-950/15 to-transparent"></span>
            </span>
        </a>
        @if($post->body)
            <p class="whitespace-pre-line break-words px-4 pt-3 text-[15px] leading-relaxed text-ink-800"><span class="font-semibold text-ink-900">{{ \Illuminate\Support\Str::before($post->user->name, ' ') }}</span> {{ $post->body }}</p>
        @endif
    @endif

    @if($ro)
    <div class="flex items-center gap-4 px-4 pb-4 pt-3 text-sm font-semibold text-ink-700">
        <span class="inline-flex items-center gap-1.5"><svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>{{ $post->likes_count }}</span>
        <span class="inline-flex items-center gap-1.5"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.69 9-8.25s-4.03-8.25-9-8.25S3 7.44 3 12c0 2.1.86 4.02 2.28 5.47-.2 1.1-.78 2.1-1.53 2.78 1.6-.1 3.07-.74 4.2-1.67 1.2.45 2.5.67 3.75.67Z"/></svg>{{ $post->comments_count }}</span>
    </div>
    @else
    <div class="flex items-center gap-1 px-2 pb-1 pt-2">
        <button type="button" data-like="{{ route('posts.like', $post) }}" data-liked="{{ $post->liked_by_me ? '1' : '0' }}"
                class="group flex min-h-11 items-center gap-1.5 rounded-full px-3 py-2 text-base font-semibold transition hover:bg-rose-50 {{ $post->liked_by_me ? 'text-rose-brand' : 'text-ink-700' }}" aria-label="{{ __('पसंद') }}">
            <svg class="h-6 w-6 transition group-active:scale-125" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="{{ $post->liked_by_me ? 'currentColor' : 'none' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.02-4.5-4.5-4.5-1.74 0-3.25.99-4 2.44-.75-1.45-2.26-2.44-4-2.44A4.5 4.5 0 0 0 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
            <span data-like-count>{{ $post->likes_count }}</span><span>{{ __("पसंद") }}</span>
        </button>
        <button type="button" data-comments-toggle class="flex min-h-11 items-center gap-1.5 rounded-full px-3 py-2 text-base font-semibold text-ink-700 transition hover:bg-ink-50" aria-label="{{ __('कमेंट') }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.69 9-8.25s-4.03-8.25-9-8.25S3 7.44 3 12c0 2.1.86 4.02 2.28 5.47-.2 1.1-.78 2.1-1.53 2.78 1.6-.1 3.07-.74 4.2-1.67 1.2.45 2.5.67 3.75.67Z"/></svg>
            <span data-comment-count>{{ $post->comments_count }}</span><span>{{ __("कमेंट") }}</span>
        </button>
    </div>

    <div class="{{ ($open ?? false) ? '' : 'hidden' }} space-y-2.5 border-t border-ink-50 bg-ink-50/30 px-4 pb-4 pt-3" data-comments-box>
        <div class="space-y-2.5" data-comments-list>
            @foreach($post->comments->sortBy('id') as $c)
                @include('feed.partials.comment', ['comment' => $c, 'post' => $post])
            @endforeach
        </div>
        @if(($open ?? false) === false && $post->comments_count > $post->comments->count())
            <a href="{{ route('posts.show', $post) }}" class="block text-base font-semibold text-kumkum-700 underline">{{ __('सभी कमेंट देखें') }} ({{ $post->comments_count }})</a>
        @endif
        <form method="POST" action="{{ route('comments.store', $post) }}" class="flex items-center gap-2" data-comment-form>
            @csrf
            <input type="text" name="body" maxlength="1000" required placeholder="{{ __('कमेंट लिखें...') }}" class="input !rounded-full !py-2.5" aria-label="{{ __('कमेंट लिखें') }}">
            <button class="btn-primary shrink-0">{{ __('भेजें') }}</button>
        </form>
    </div>
    @endif
</article>
