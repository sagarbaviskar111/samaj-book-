<?php

use App\Http\Controllers\Events\EventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/popup', [EventController::class, 'popup'])->name('events.popup');
    Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');
    Route::get('/events/{event}/join', [EventController::class, 'join'])->whereNumber('event')->name('events.join');
    Route::get('/events/{event}/calendar.ics', [EventController::class, 'ics'])->whereNumber('event')->name('events.ics');
    Route::post('/events/{event}/register', [EventController::class, 'register'])->whereNumber('event')->name('events.register');
    Route::delete('/events/{event}/register', [EventController::class, 'unregister'])->whereNumber('event')->name('events.unregister');
});
