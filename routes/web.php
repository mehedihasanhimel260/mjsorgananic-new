<?php
//common Route link start
use Illuminate\Support\Facades\Route;
//common Route link end

//user Route link start
use App\Http\Controllers\ProfileController;
//user Route link end

//admin Route link start
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\FbSettingController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCommissionController;
use App\Http\Controllers\Admin\ProductStockBatchController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\SiteMenuController;
use App\Http\Controllers\Admin\SitePageController as AdminSitePageController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SteadfastController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Affiliate\AuthController as AffiliateAuthController;
use App\Http\Controllers\Affiliate\AccountController as AffiliateAccountController;
use App\Http\Controllers\Affiliate\DashboardController as AffiliateDashboardController;
use App\Http\Controllers\Affiliate\LinkController as AffiliateLinkController;
use App\Http\Controllers\Affiliate\ReportController as AffiliateReportController;
use App\Http\Controllers\Front_site\AffiliateTrackingController;
use App\Http\Controllers\Front_site\ChatController as FrontChatController;
use App\Http\Controllers\Front_site\FrontSiteController;
use App\Http\Controllers\Front_site\OrderController;
use App\Http\Controllers\Front_site\SitePageController;
use App\Http\Controllers\FbWebhookController;
//admin Route link End

//admin Route start

use App\Models\Admin;
use App\Models\Category;

use Illuminate\Support\Facades\DB;

Route::get('/db-check', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'success',
            'message' => 'Database connected successfully 🎉'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed ❌',
            'error' => $e->getMessage()
        ]);
    }
});


Route::get('/make-admin', function () {
    $admin = Admin::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'phone' => '01764366127',
        'password' => '123456',
    ]);

    return 'Admin created successfully!';
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::middleware('auth:admin')->group(function () {
            Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
            Route::get('/account/profile', [AdminAccountController::class, 'profile'])->name('account.profile');
            Route::patch('/account/profile', [AdminAccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::get('/account/settings', [AdminAccountController::class, 'settings'])->name('account.settings');
            Route::patch('/account/settings', [AdminAccountController::class, 'updateSettings'])->name('account.settings.update');
            Route::resource('categories', CategoryController::class);
            Route::resource('products', ProductController::class);
            Route::get('product-commissions', [ProductCommissionController::class, 'index'])->name('product-commissions.index');
            Route::get('product-commissions/create', [ProductCommissionController::class, 'create'])->name('product-commissions.create');
            Route::post('product-commissions', [ProductCommissionController::class, 'store'])->name('product-commissions.store');
            Route::get('product-commissions/{productCommission}/edit', [ProductCommissionController::class, 'edit'])->name('product-commissions.edit');
            Route::patch('product-commissions/{productCommission}', [ProductCommissionController::class, 'update'])->name('product-commissions.update');
            Route::post('product-commissions/{productCommission}/toggle-status', [ProductCommissionController::class, 'toggleStatus'])->name('product-commissions.toggle-status');
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/discount', [AdminOrderController::class, 'updateDiscount'])->name('orders.discount.update');
            Route::post('orders/{order}/delivery-charge', [AdminOrderController::class, 'updateDeliveryCharge'])->name('orders.delivery-charge.update');
            Route::post('orders/{order}/items', [AdminOrderController::class, 'addItem'])->name('orders.items.add');
            Route::post('orders/{order}/items/{itemId}', [AdminOrderController::class, 'updateItem'])->name('orders.items.update');
            Route::post('orders/{order}/items/{itemId}/remove', [AdminOrderController::class, 'removeItem'])->name('orders.items.remove');
            Route::post('orders/{order}/book-courier', [AdminOrderController::class, 'bookCourier'])->name('orders.book-courier');
            Route::get('steadfast', [SteadfastController::class, 'index'])->name('steadfast.index');
            Route::post('steadfast', [SteadfastController::class, 'update'])->name('steadfast.update');
            Route::post('steadfast/refresh-balance', [SteadfastController::class, 'refreshBalance'])->name('steadfast.refresh-balance');
            Route::get('delivery-charge', [DeliveryChargeController::class, 'index'])->name('delivery-charge.index');
            Route::post('delivery-charge', [DeliveryChargeController::class, 'update'])->name('delivery-charge.update');
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::get('ai-settings', [AiSettingController::class, 'index'])->name('ai-settings.index');
            Route::get('ai-settings/{aiSetting}/edit', [AiSettingController::class, 'edit'])->name('ai-settings.edit');
            Route::patch('ai-settings/{aiSetting}', [AiSettingController::class, 'update'])->name('ai-settings.update');
            Route::get('fb-settings', [FbSettingController::class, 'index'])->name('fb-settings.index');
            Route::post('fb-settings', [FbSettingController::class, 'update'])->name('fb-settings.update');
            Route::get('seo-settings', [SeoSettingController::class, 'index'])->name('seo-settings.index');
            Route::post('seo-settings', [SeoSettingController::class, 'update'])->name('seo-settings.update');
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
            Route::get('chats', [AdminChatController::class, 'index'])->name('chats.index');
            Route::get('chats/{chat}', [AdminChatController::class, 'show'])->name('chats.show');
            Route::post('chats/{chat}/reply', [AdminChatController::class, 'reply'])->name('chats.reply');
            Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
            Route::get('faqs/create', [FaqController::class, 'create'])->name('faqs.create');
            Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
            Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
            Route::patch('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            Route::resource('product-stocks', ProductStockBatchController::class);
        });
    });

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
            Route::post('/logout', [AffiliateAuthController::class, 'logout'])->name('logout');
        });
    });

Route::get('/', [FrontSiteController::class, 'index'])->name('home');
Route::get('/products/{product}', [FrontSiteController::class, 'show'])->name('products.show');
Route::get('/pages/{slug}', [SitePageController::class, 'show'])->name('pages.show');
Route::get('/ref/{tracking_code}', [AffiliateTrackingController::class, 'handle'])->name('affiliate.track');
Route::get('/facebook/webhook', [FbWebhookController::class, 'verify'])->name('facebook.webhook.verify');
Route::post('/facebook/webhook', [FbWebhookController::class, 'receive'])->name('facebook.webhook.receive');
Route::post('/visitor/ping', [OrderController::class, 'visitorPing'])->name('visitor.ping');
Route::post('/visitor/register', [OrderController::class, 'registerVisitor'])->name('visitor.register');
Route::post('/cart/add', [OrderController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update-quantity', [OrderController::class, 'updateCartQuantity'])->name('cart.updateQuantity');
Route::post('/order/complete', [OrderController::class, 'completeOrder'])->name('order.complete');
Route::get('/cart', [OrderController::class, 'getCart'])->name('cart.get');
Route::get('/chat/history', [FrontChatController::class, 'history'])->name('chat.history');
Route::post('/chat/message', [FrontChatController::class, 'storeMessage'])->name('chat.message');

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
