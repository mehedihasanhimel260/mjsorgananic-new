<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\AdminMaintenanceController;
use App\Http\Controllers\Admin\AdminManagerController;
use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\AffiliateWithdrawController as AdminAffiliateWithdrawController;
use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FbSettingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductCommissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductStockBatchController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\SiteMenuController;
use App\Http\Controllers\Admin\SitePageController as AdminSitePageController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SmsMarketingController;
use App\Http\Controllers\Admin\SteadfastController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::middleware('auth:admin')->group(function () {
            Route::post('/maintenance/clear', [AdminMaintenanceController::class, 'clearApplicationData'])->name('maintenance.clear');

            Route::get('/dashboard', [LoginController::class, 'dashboard'])
                ->middleware('admin.permission:dashboard.view')
                ->name('dashboard');
            Route::get('/account/profile', [AdminAccountController::class, 'profile'])->name('account.profile');
            Route::patch('/account/profile', [AdminAccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::get('/account/settings', [AdminAccountController::class, 'settings'])->name('account.settings');
            Route::patch('/account/settings', [AdminAccountController::class, 'updateSettings'])->name('account.settings.update');

            Route::middleware('admin.permission:products.manage')->group(function () {
                Route::resource('categories', CategoryController::class);
                Route::resource('products', ProductController::class);
                Route::resource('product-stocks', ProductStockBatchController::class);
                Route::get('product-commissions', [ProductCommissionController::class, 'index'])->name('product-commissions.index');
                Route::get('product-commissions/create', [ProductCommissionController::class, 'create'])->name('product-commissions.create');
                Route::post('product-commissions', [ProductCommissionController::class, 'store'])->name('product-commissions.store');
                Route::get('product-commissions/{productCommission}/edit', [ProductCommissionController::class, 'edit'])->name('product-commissions.edit');
                Route::patch('product-commissions/{productCommission}', [ProductCommissionController::class, 'update'])->name('product-commissions.update');
                Route::post('product-commissions/{productCommission}/toggle-status', [ProductCommissionController::class, 'toggleStatus'])->name('product-commissions.toggle-status');
            });

            Route::middleware('admin.permission:orders.manage')->group(function () {
                Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
                Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
                Route::post('orders/{order}/discount', [AdminOrderController::class, 'updateDiscount'])->name('orders.discount.update');
                Route::post('orders/{order}/delivery-charge', [AdminOrderController::class, 'updateDeliveryCharge'])->name('orders.delivery-charge.update');
                Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status.update');
                Route::post('orders/{order}/items', [AdminOrderController::class, 'addItem'])->name('orders.items.add');
                Route::post('orders/{order}/items/{itemId}', [AdminOrderController::class, 'updateItem'])->name('orders.items.update');
                Route::post('orders/{order}/items/{itemId}/remove', [AdminOrderController::class, 'removeItem'])->name('orders.items.remove');
                Route::post('orders/{order}/book-courier', [AdminOrderController::class, 'bookCourier'])->name('orders.book-courier');
                Route::post('orders/{order}/sync-courier-status', [AdminOrderController::class, 'syncCourierStatus'])->name('orders.sync-courier-status');
            });

            Route::middleware('admin.permission:users.manage')->group(function () {
                Route::get('users', [UserController::class, 'index'])->name('users.index');
                Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
                Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
            });

            Route::middleware('admin.permission:affiliates.manage')->group(function () {
                Route::get('affiliates', [AdminAffiliateController::class, 'index'])->name('affiliates.index');
                Route::post('affiliates/{affiliate}/impersonate', [AdminAffiliateController::class, 'impersonate'])->name('affiliates.impersonate');
                Route::get('affiliate-withdraws', [AdminAffiliateWithdrawController::class, 'index'])->name('affiliate-withdraws.index');
                Route::get('affiliate-withdraws/{affiliateWithdrawRequest}', [AdminAffiliateWithdrawController::class, 'show'])->name('affiliate-withdraws.show');
                Route::post('affiliate-withdraws/{affiliateWithdrawRequest}/approve', [AdminAffiliateWithdrawController::class, 'approve'])->name('affiliate-withdraws.approve');
                Route::post('affiliate-withdraws/{affiliateWithdrawRequest}/reject', [AdminAffiliateWithdrawController::class, 'reject'])->name('affiliate-withdraws.reject');
                Route::post('affiliate-withdraws/{affiliateWithdrawRequest}/mark-paid', [AdminAffiliateWithdrawController::class, 'markPaid'])->name('affiliate-withdraws.mark-paid');
            });

            Route::middleware('admin.permission:chats.manage')->group(function () {
                Route::get('chats', [AdminChatController::class, 'index'])->name('chats.index');
                Route::get('chats/{chat}', [AdminChatController::class, 'show'])->name('chats.show');
                Route::post('chats/{chat}/reply', [AdminChatController::class, 'reply'])->name('chats.reply');
            });

            Route::middleware('admin.permission:faqs.manage')->group(function () {
                Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
                Route::get('faqs/create', [FaqController::class, 'create'])->name('faqs.create');
                Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
                Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
                Route::patch('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            });

            Route::middleware('admin.permission:settings.manage')->group(function () {
                Route::get('steadfast', [SteadfastController::class, 'index'])->name('steadfast.index');
                Route::post('steadfast', [SteadfastController::class, 'update'])->name('steadfast.update');
                Route::post('steadfast/refresh-balance', [SteadfastController::class, 'refreshBalance'])->name('steadfast.refresh-balance');
                Route::get('delivery-charge', [DeliveryChargeController::class, 'index'])->name('delivery-charge.index');
                Route::post('delivery-charge', [DeliveryChargeController::class, 'update'])->name('delivery-charge.update');
                Route::get('ai-settings', [AiSettingController::class, 'index'])->name('ai-settings.index');
                Route::get('ai-settings/{aiSetting}/edit', [AiSettingController::class, 'edit'])->name('ai-settings.edit');
                Route::patch('ai-settings/{aiSetting}', [AiSettingController::class, 'update'])->name('ai-settings.update');
                Route::get('fb-settings', [FbSettingController::class, 'index'])->name('fb-settings.index');
                Route::post('fb-settings', [FbSettingController::class, 'update'])->name('fb-settings.update');
                Route::get('seo-settings', [SeoSettingController::class, 'index'])->name('seo-settings.index');
                Route::post('seo-settings', [SeoSettingController::class, 'update'])->name('seo-settings.update');
                Route::get('sms-settings', [SmsMarketingController::class, 'index'])->name('sms-settings.index');
                Route::post('sms-settings', [SmsMarketingController::class, 'update'])->name('sms-settings.update');
                Route::post('sms-settings/refresh-balance', [SmsMarketingController::class, 'refreshBalance'])->name('sms-settings.refresh-balance');
                Route::post('sms-settings/send-bulk', [SmsMarketingController::class, 'sendBulk'])->name('sms-settings.send-bulk');
                Route::post('sms-settings/send-single', [SmsMarketingController::class, 'sendSingle'])->name('sms-settings.send-single');
                Route::post('sms-settings/templates', [SmsMarketingController::class, 'storeTemplate'])->name('sms-settings.templates.store');
                Route::patch('sms-settings/templates/{smsTemplate}', [SmsMarketingController::class, 'updateTemplate'])->name('sms-settings.templates.update');
                Route::delete('sms-settings/templates/{smsTemplate}', [SmsMarketingController::class, 'destroyTemplate'])->name('sms-settings.templates.destroy');
                Route::get('site-settings/general', [SiteSettingController::class, 'index'])->name('site-settings.general');
                Route::post('site-settings/general', [SiteSettingController::class, 'update'])->name('site-settings.general.update');
                Route::get('site-settings/footer', [SiteSettingController::class, 'footer'])->name('site-settings.footer');
                Route::post('site-settings/footer', [SiteSettingController::class, 'updateFooter'])->name('site-settings.footer.update');
                Route::get('site-settings/menus', [SiteMenuController::class, 'index'])->name('site-settings.menus');
                Route::post('site-settings/menus', [SiteMenuController::class, 'store'])->name('site-settings.menus.store');
                Route::get('site-settings/menus/{siteMenu}/edit', [SiteMenuController::class, 'edit'])->name('site-settings.menus.edit');
                Route::patch('site-settings/menus/{siteMenu}', [SiteMenuController::class, 'update'])->name('site-settings.menus.update');
                Route::get('site-settings/pages', [AdminSitePageController::class, 'index'])->name('site-settings.pages.index');
                Route::get('site-settings/pages/create', [AdminSitePageController::class, 'create'])->name('site-settings.pages.create');
                Route::post('site-settings/pages', [AdminSitePageController::class, 'store'])->name('site-settings.pages.store');
                Route::get('site-settings/pages/{sitePage}/edit', [AdminSitePageController::class, 'edit'])->name('site-settings.pages.edit');
                Route::patch('site-settings/pages/{sitePage}', [AdminSitePageController::class, 'update'])->name('site-settings.pages.update');
            });

            Route::middleware('admin.permission:access-control.manage')->group(function () {
                Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
                Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
                Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
                Route::patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
                Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
                Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
                Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
                Route::patch('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
                Route::get('admins', [AdminManagerController::class, 'index'])->name('admins.index');
                Route::post('admins', [AdminManagerController::class, 'store'])->name('admins.store');
                Route::post('admins/{admin}/roles', [AdminManagerController::class, 'updateRoles'])->name('admins.roles.update');
            });
        });
    });
