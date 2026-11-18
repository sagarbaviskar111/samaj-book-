<?php

use App\Http\Controllers\News\NewsController;
use Illuminate\Support\Facades\Route;

// Public detail page for a news / poster / event item (opened from feed strip and landing).
Route::get('/news/{newsItem}', [NewsController::class, 'show'])->name('news.show');
