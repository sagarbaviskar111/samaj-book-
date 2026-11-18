<?php

use App\Http\Controllers\Admin\BiodataController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StoryController;
use App\Http\Controllers\Admin\SubAdminController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Sub-admins (role subadmin, one area) get everything NOT marked 'admin.full'.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users/bulk-approve', [UserController::class, 'bulkApprove'])->name('users.bulk-approve');
    Route::get('users/{id}', [UserController::class, 'show'])->whereNumber('id')->name('users.show');
    Route::post('users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');

    Route::get('biodata', [BiodataController::class, 'index'])->name('biodata.index');
    Route::get('biodata/{id}', [BiodataController::class, 'show'])->whereNumber('id')->name('biodata.show');
    Route::post('biodata/{id}/approve', [BiodataController::class, 'approve'])->name('biodata.approve');
    Route::post('biodata/{id}/reject', [BiodataController::class, 'reject'])->name('biodata.reject');

    Route::resource('news', NewsController::class)->except('show')->parameters(['news' => 'id']);
    Route::post('news/{id}/publish', [NewsController::class, 'togglePublish'])->name('news.publish');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/{id}/resolve', [ReportController::class, 'resolve'])->name('reports.resolve');
    Route::post('reports/{id}/dismiss', [ReportController::class, 'dismiss'])->name('reports.dismiss');
    Route::post('reports/{id}/hide-post', [ReportController::class, 'hidePost'])->name('reports.hide-post');

    Route::get('password', [PasswordController::class, 'edit'])->name('password');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Main admin only
    Route::middleware('admin.full')->group(function () {
        Route::post('users/{id}/block', [UserController::class, 'block'])->name('users.block');
        Route::post('users/{id}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
        Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('biodata/{id}/verify', [BiodataController::class, 'toggleVerified'])->name('biodata.verify');
        Route::post('biodata/{id}/status', [BiodataController::class, 'setStatus'])->name('biodata.status');
        Route::delete('biodata/{id}', [BiodataController::class, 'destroy'])->name('biodata.destroy');

        Route::resource('stories', StoryController::class)->except('show')->parameters(['stories' => 'id']);
        Route::post('reports/{id}/block-user', [ReportController::class, 'blockUser'])->name('reports.block-user');

        Route::get('settings', [SettingsController::class, 'edit'])->name('settings');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('audit', [SettingsController::class, 'audit'])->name('audit');

        Route::resource('subadmins', SubAdminController::class)->except('show')->parameters(['subadmins' => 'id']);
    });
});
