<?php

namespace Tests\Feature\News;

use App\Models\NewsItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_news_is_public(): void
    {
        $this->withoutVite();
        $n = NewsItem::create(['type' => 'event', 'title' => 'Parichay Sammelan', 'body' => 'Details', 'event_at' => now()->addWeek(), 'event_place' => 'Nagpur']);

        $this->get(route('news.show', $n))->assertOk()->assertSee('Parichay Sammelan')->assertSee('Nagpur');
    }

    public function test_unpublished_news_is_404(): void
    {
        $n = NewsItem::create(['type' => 'news', 'title' => 'Draft', 'is_published' => false]);

        $this->get(route('news.show', $n))->assertNotFound();
    }
}
