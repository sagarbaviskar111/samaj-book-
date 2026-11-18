<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function show(Request $request, NewsItem $newsItem)
    {
        abort_unless($newsItem->is_published, 404);

        // Members awaiting approval never see event items.
        $pending = (bool) $request->user()?->isPending();
        abort_if($pending && $newsItem->type === 'event', 404);

        $more = NewsItem::published()
            ->when($pending, fn ($q) => $q->where('type', '!=', 'event'))
            ->where('id', '!=', $newsItem->id)
            ->latest()
            ->take(4)
            ->get();

        return view('news.show', ['item' => $newsItem, 'more' => $more]);
    }
}
