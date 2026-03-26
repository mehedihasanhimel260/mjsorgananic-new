<?php
//common Route link start
use Illuminate\Support\Facades\Route;
//common Route link end

//user Route link start
use App\Http\Controllers\ProfileController;
//user Route link end

//admin Route link start
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\FbSettingController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductStockBatchController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\SteadfastController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Front_site\ChatController as FrontChatController;
use App\Http\Controllers\Front_site\FrontSiteController;
use App\Http\Controllers\Front_site\OrderController;
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
            // Category start
            Route::resource('categories', CategoryController::class);
            // Product  start
            Route::resource('products', ProductController::class);
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
            Route::get('chats', [AdminChatController::class, 'index'])->name('chats.index');
            Route::get('chats/{chat}', [AdminChatController::class, 'show'])->name('chats.show');
            Route::post('chats/{chat}/reply', [AdminChatController::class, 'reply'])->name('chats.reply');
            Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
            Route::get('faqs/create', [FaqController::class, 'create'])->name('faqs.create');
            Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
            Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
            Route::patch('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            // Product Stock start
            Route::resource('product-stocks', ProductStockBatchController::class);
            // Product end
        });
    });

//admin Route End

Route::get('/', [FrontSiteController::class, 'index'])->name('home');
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
