@foreach($posts as $post)
    @include('feed.partials.post', ['post' => $post])
@endforeach
