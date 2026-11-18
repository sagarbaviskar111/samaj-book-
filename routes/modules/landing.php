<?php

use App\Http\Controllers\Landing\LandingController;
use App\Http\Controllers\Landing\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::controller(PageController::class)->group(function () {
    Route::get('/terms', 'terms')->name('page.terms');
    Route::get('/privacy', 'privacy')->name('page.privacy');
    Route::get('/about', 'about')->name('page.about');
    Route::get('/faq', 'faq')->name('page.faq');
});
