<?php

use App\Http\Controllers\Member\FollowController;
use App\Http\Controllers\Member\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->prefix('members')->name('members.')->group(function () {
    Route::get('/{member}', [MemberController::class, 'show'])->whereNumber('member')->name('show');
    Route::get('/{member}/followers', [MemberController::class, 'followers'])->whereNumber('member')->name('followers');
    Route::get('/{member}/following', [MemberController::class, 'following'])->whereNumber('member')->name('following');
    Route::post('/{member}/follow', [FollowController::class, 'toggle'])->whereNumber('member')->middleware('throttle:60,1')->name('follow');
});
