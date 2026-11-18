<?php

use App\Http\Controllers\Feed\CommentController;
use App\Http\Controllers\Feed\FeedController;
use App\Http\Controllers\Feed\LikeController;
use App\Http\Controllers\Feed\PostController;
use App\Http\Controllers\Feed\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/home', [FeedController::class, 'index'])->name('home');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show')->whereNumber('post');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});
