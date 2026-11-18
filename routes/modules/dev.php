<?php

use App\Http\Controllers\Dev\DevLoginController;
use Illuminate\Support\Facades\Route;

// Local development only: the controller returns 404 unless APP_ENV=local, APP_DEBUG=true and the request is from loopback.
Route::post('/dev/login/{phone}', [DevLoginController::class, 'login'])->name('dev.login');
