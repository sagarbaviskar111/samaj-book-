<?php

use App\Http\Controllers\Search\BiodataSearchController;
use App\Http\Controllers\Search\InterestController;
use App\Http\Controllers\Search\ShortlistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/biodata', [BiodataSearchController::class, 'index'])->name('biodata.index');

    Route::get('/interests', [InterestController::class, 'index'])->name('interests.index');
    Route::post('/interests', [InterestController::class, 'store'])->middleware('throttle:30,1')->name('interests.store');
    Route::post('/interests/{interest}/respond', [InterestController::class, 'respond'])->name('interests.respond');
    Route::post('/interests/{interest}/withdraw', [InterestController::class, 'withdraw'])->name('interests.withdraw');
    Route::post('/interests/{interest}/help', [InterestController::class, 'help'])->name('interests.help');

    Route::get('/shortlist', [ShortlistController::class, 'index'])->name('shortlist.index');
    Route::post('/shortlist/{biodata}', [ShortlistController::class, 'toggle'])->name('shortlist.toggle');
});
