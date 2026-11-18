<?php

use App\Http\Controllers\Biodata\BiodataController;
use App\Http\Controllers\Biodata\PdfController;
use App\Http\Controllers\Biodata\PhotoController;
use App\Http\Controllers\Biodata\ShareController;
use Illuminate\Support\Facades\Route;

// Biodata module. NOTE: GET /biodata (biodata.index) belongs to the Search module.
Route::middleware(['auth', 'active'])->prefix('biodata')->name('biodata.')->group(function () {
    // static paths first, so they never match {biodata}
    Route::get('/mine', [BiodataController::class, 'mine'])->name('mine');
    Route::get('/create', [BiodataController::class, 'create'])->name('create');
    Route::post('/', [BiodataController::class, 'store'])->name('store');

    Route::get('/photo/{photo}', [PhotoController::class, 'show'])->whereNumber('photo')->name('photo');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->whereNumber('photo')->name('photos.destroy');
    Route::post('/photos/{photo}/primary', [PhotoController::class, 'primary'])->whereNumber('photo')->name('photos.primary');

    Route::get('/{biodata}', [BiodataController::class, 'show'])->whereNumber('biodata')->name('show');
    Route::get('/{biodata}/edit', [BiodataController::class, 'edit'])->whereNumber('biodata')->name('edit');
    Route::put('/{biodata}', [BiodataController::class, 'update'])->whereNumber('biodata')->name('update');
    Route::delete('/{biodata}', [BiodataController::class, 'destroy'])->whereNumber('biodata')->name('destroy');
    Route::post('/{biodata}/status', [BiodataController::class, 'changeStatus'])->whereNumber('biodata')->name('status');
    Route::post('/{biodata}/photos', [PhotoController::class, 'store'])->whereNumber('biodata')->name('photos.store');
    Route::get('/{biodata}/pdf', [PdfController::class, 'download'])->whereNumber('biodata')->name('pdf');

    // owner-only share link management (JSON)
    Route::match(['get', 'post'], '/{biodata}/share', [ShareController::class, 'show'])->whereNumber('biodata')->name('share.show');
    Route::post('/{biodata}/share/reset', [ShareController::class, 'reset'])->whereNumber('biodata')->name('share.reset');
    Route::post('/{biodata}/share/toggle', [ShareController::class, 'toggle'])->whereNumber('biodata')->name('share.toggle');
});

// Public deep link (NOT a public page): redirects to login / the normal biodata page.
Route::get('/b/{token}', [ShareController::class, 'open'])->where('token', '[A-Za-z0-9]{6,24}')->middleware('throttle:60,1')->name('biodata.shared');
