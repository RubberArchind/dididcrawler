<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Redirect to appropriate dashboard based on user role
Route::get('/dashboard', function () {
    if (Auth::user()->isSuperAdmin()) {
        return redirect()->route('superadmin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// SuperAdmin routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/create', [SuperAdminController::class, 'createUser'])->name('users.create');
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
    Route::get('/payments', [SuperAdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/pay', [SuperAdminController::class, 'markAsPaid'])->name('payments.pay');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/backup', [SuperAdminController::class, 'backup'])->name('backup');
    Route::post('/maintenance', [SuperAdminController::class, 'maintenance'])->name('maintenance');
});

// User routes
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [UserDashboardController::class, 'reports'])->name('reports');
    Route::get('/payments', [UserDashboardController::class, 'payments'])->name('payments');
});

// Webhook routes (no authentication required)
Route::post('/webhook/payment', [SuperAdminController::class, 'webhook'])->name('webhook.payment');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
