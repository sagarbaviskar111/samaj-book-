<?php

use App\Http\Controllers\LookingFor\LookingForController;
use Illuminate\Support\Facades\Route;

// "Looking for" profile: view-only full access for members without their own biodata.
Route::middleware(['auth', 'active'])->prefix('looking-for')->name('looking-for.')->group(function () {
    Route::get('/', [LookingForController::class, 'create'])->name('create');
    Route::post('/', [LookingForController::class, 'store'])->name('store');
    Route::get('/edit', [LookingForController::class, 'edit'])->name('edit');
    Route::put('/', [LookingForController::class, 'update'])->name('update');
    Route::delete('/', [LookingForController::class, 'destroy'])->name('destroy');
});
