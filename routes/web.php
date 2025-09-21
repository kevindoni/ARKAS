<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Redirect legacy /home to the unified user dashboard
Route::redirect('/home', '/dashboard')->name('home');

// User dashboard (after normal login via Fortify)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User profile routes
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil/info', [\App\Http\Controllers\ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Security merged into Profile: keep otpauth endpoint and redirect old page
    Route::redirect('/keamanan', '/profil')->name('security.index');
    Route::get('/keamanan/otpauth', [\App\Http\Controllers\SecurityController::class, 'otpauth'])->name('security.otpauth');

    // Note: Fortify provides the 2FA and email verification routes; we use client-side toasts

    // User School self-service (each user manages own data only)
    Route::get('/sekolah', [\App\Http\Controllers\SchoolController::class, 'index'])->name('school.index');
    Route::post('/sekolah', [\App\Http\Controllers\SchoolController::class, 'store'])->name('school.store');
    Route::put('/sekolah/{school}', [\App\Http\Controllers\SchoolController::class, 'update'])->name('school.update');
    Route::delete('/sekolah/{school}', [\App\Http\Controllers\SchoolController::class, 'destroy'])->name('school.destroy');

    // BKU sections
    Route::prefix('bku')->name('bku.')->group(function () {
        Route::get('/master', [\App\Http\Controllers\BKUController::class, 'master'])->name('master');
        Route::post('/master/upload', [\App\Http\Controllers\BKUController::class, 'masterUpload'])->name('master.upload');
        Route::post('/master/import', [\App\Http\Controllers\BKUController::class, 'masterImport'])->name('master.import');
        
        // BKU Tunai routes
        Route::post('/tunai/upload', [\App\Http\Controllers\BKUController::class, 'tunaiUpload'])->name('tunai.upload');
        Route::post('/tunai/import', [\App\Http\Controllers\BKUController::class, 'tunaiImport'])->name('tunai.import');
        
        Route::get('/umum', [\App\Http\Controllers\BKUController::class, 'umum'])->name('umum');
        Route::get('/umum/print', [\App\Http\Controllers\BKUController::class, 'umumPrint'])->name('umum.print');
        Route::get('/tunai', [\App\Http\Controllers\BKUController::class, 'tunai'])->name('tunai');
        Route::get('/bank', [\App\Http\Controllers\BKUController::class, 'bank'])->name('bank');
        Route::get('/pajak', [\App\Http\Controllers\BKUController::class, 'pajak'])->name('pajak');
        
        // Debug routes for tax categories
        Route::get('/pajak/categories', [\App\Http\Controllers\BKUController::class, 'showTaxCategories'])->name('pajak.categories');
        Route::post('/pajak/clear-cache', [\App\Http\Controllers\BKUController::class, 'clearTaxCache'])->name('pajak.clear-cache');
    });
});

// Admin routes
Route::prefix('admin')->group(function () {
    // Admin login (separate form)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Admin dashboard (protected)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
        Route::get('/users/{user}', [AdminUsersController::class, 'show'])->name('admin.users.show');
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('admin.profile');
        // Sensitive actions require recent password confirmation
        Route::middleware(['password.confirm'])->group(function () {
            Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
            Route::post('/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
            Route::post('/settings/test-mail', [AdminSettingsController::class, 'testMail'])->name('admin.settings.test-mail');
            Route::post('/profile/info', [\App\Http\Controllers\ProfileController::class, 'updateInfo'])->name('admin.profile.updateInfo');
            Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('admin.profile.updatePassword');
            // Future: Admin actions like regenerating user 2FA recovery codes should also live here
            Route::post('/users/{user}/two-factor/reset', [AdminUsersController::class, 'resetTwoFactor'])->name('admin.users.2fa.reset');
            Route::post('/users/{user}/two-factor/recovery-codes', [AdminUsersController::class, 'regenRecoveryCodes'])->name('admin.users.2fa.codes');
            // Resend email verification link to a user
            Route::post('/users/{user}/resend-verification', [AdminUsersController::class, 'resendVerification'])->name('admin.users.resend-verification');
            // Admin user management
            Route::get('/users/{user}/edit', [AdminUsersController::class, 'edit'])->name('admin.users.edit');
            Route::put('/users/{user}', [AdminUsersController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [AdminUsersController::class, 'destroy'])->name('admin.users.destroy');
        });
    });
});
