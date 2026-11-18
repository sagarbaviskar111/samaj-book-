<?php

use App\Http\Controllers\Search\KundliController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/interests/{interest}/kundli', [KundliController::class, 'show'])->whereNumber('interest')->name('interests.kundli');
});
