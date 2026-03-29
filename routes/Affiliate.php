<?php

use App\Http\Controllers\Affiliate\AccountController as AffiliateAccountController;
use App\Http\Controllers\Affiliate\AuthController as AffiliateAuthController;
use App\Http\Controllers\Affiliate\DashboardController as AffiliateDashboardController;
use App\Http\Controllers\Affiliate\LinkController as AffiliateLinkController;
use App\Http\Controllers\Affiliate\ReportController as AffiliateReportController;
use App\Http\Controllers\Affiliate\WithdrawController as AffiliateWithdrawController;
use Illuminate\Support\Facades\Route;

Route::prefix('affiliates')
    ->name('affiliates.')
    ->group(function () {
        Route::middleware('guest:affiliate')->group(function () {
            Route::get('/', [AffiliateAuthController::class, 'showLoginForm'])->name('login');
            Route::get('/login', [AffiliateAuthController::class, 'showLoginForm'])->name('login.form');
            Route::post('/login', [AffiliateAuthController::class, 'login'])->name('login.submit');
            Route::get('/register', [AffiliateAuthController::class, 'showRegisterForm'])->name('register');
            Route::post('/register', [AffiliateAuthController::class, 'register'])->name('register.submit');
        });

        Route::middleware('auth:affiliate')->group(function () {
            Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])->name('dashboard');
            Route::get('/account/profile', [AffiliateAccountController::class, 'profile'])->name('account.profile');
            Route::patch('/account/profile', [AffiliateAccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::get('/account/settings', [AffiliateAccountController::class, 'settings'])->name('account.settings');
            Route::patch('/account/settings', [AffiliateAccountController::class, 'updateSettings'])->name('account.settings.update');
            Route::get('/links', [AffiliateLinkController::class, 'index'])->name('links.index');
            Route::post('/links', [AffiliateLinkController::class, 'store'])->name('links.store');
            Route::get('/orders', [AffiliateReportController::class, 'orders'])->name('orders.index');
            Route::get('/commissions', [AffiliateReportController::class, 'commissions'])->name('commissions.index');
            Route::get('/wallet', [AffiliateReportController::class, 'wallet'])->name('wallet.index');
            Route::post('/wallet/withdraw', [AffiliateWithdrawController::class, 'store'])->name('wallet.withdraw.store');
            Route::post('/logout', [AffiliateAuthController::class, 'logout'])->name('logout');
        });
    });
