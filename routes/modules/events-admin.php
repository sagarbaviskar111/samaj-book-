<?php

use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('events/{id}/registrants', [EventController::class, 'registrants'])->whereNumber('id')->name('events.registrants');
    Route::get('events/{id}/registrants.csv', [EventController::class, 'registrantsCsv'])->whereNumber('id')->name('events.registrants.csv');
    Route::resource('events', EventController::class)->except('show')->parameters(['events' => 'id']);
});
