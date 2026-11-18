<?php

use App\Http\Controllers\Superadmin\AdminController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\IntegrationController;
use App\Http\Controllers\Superadmin\LogController;
use App\Http\Controllers\Superadmin\PaymentController;
use App\Http\Controllers\Superadmin\UserProfileController;
use Illuminate\Support\Facades\Route;

// Superadmin-only console. Every route here requires role=superadmin (active).
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Admins
    Route::post('admins/{id}/block', [AdminController::class, 'block'])->whereNumber('id')->name('admins.block');
    Route::post('admins/{id}/unblock', [AdminController::class, 'unblock'])->whereNumber('id')->name('admins.unblock');
    Route::post('admins/{id}/reset-password', [AdminController::class, 'resetPassword'])->whereNumber('id')->name('admins.reset-password');
    Route::resource('admins', AdminController::class)->except('show')->parameters(['admins' => 'id']);

    // Logs
    Route::get('logs', fn () => redirect()->route('superadmin.logs.audit'))->name('logs.index');
    Route::get('logs/audit', [LogController::class, 'audit'])->name('logs.audit');
    Route::get('logs/audit/export', [LogController::class, 'auditExport'])->name('logs.audit.export');
    Route::get('logs/logins', [LogController::class, 'logins'])->name('logs.logins');
    Route::get('logs/logins/export', [LogController::class, 'loginsExport'])->name('logs.logins.export');
    Route::get('logs/security', [LogController::class, 'security'])->name('logs.security');

    // 360 profile
    Route::get('users/{id}', [UserProfileController::class, 'show'])->whereNumber('id')->name('users.show');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{id}', [PaymentController::class, 'show'])->whereNumber('id')->name('payments.show');
    Route::post('payments/{id}/refund', [PaymentController::class, 'refund'])->whereNumber('id')->name('payments.refund');
    Route::post('payments/{id}/mark-paid', [PaymentController::class, 'markPaid'])->whereNumber('id')->name('payments.mark-paid');

    // Integrations / API keys
    Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::put('integrations/payment-config', [IntegrationController::class, 'paymentConfig'])->name('integrations.payment-config');
    Route::post('integrations/custom', [IntegrationController::class, 'storeCustom'])->name('integrations.custom.store');
    Route::delete('integrations/custom/{key}', [IntegrationController::class, 'destroyCustom'])->name('integrations.custom.destroy');
    Route::put('integrations/{group}', [IntegrationController::class, 'update'])->name('integrations.update');
    Route::post('integrations/{group}/check', [IntegrationController::class, 'check'])->name('integrations.check');
});
