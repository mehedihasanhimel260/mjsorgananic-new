<?php

use App\Http\Controllers\Admin\SteadfastController;
use App\Http\Controllers\FbWebhookController;
use App\Http\Controllers\Front_site\AffiliateTrackingController;
use App\Http\Controllers\Front_site\ChatController as FrontChatController;
use App\Http\Controllers\Front_site\FrontSiteController;
use App\Http\Controllers\Front_site\OrderController;
use App\Http\Controllers\Front_site\SitePageController;
use App\Http\Controllers\ProfileController;
use App\Models\Admin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
   Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('route:clear');
Artisan::call('view:clear');
Artisan::call('optimize:clear');
    return 'Config cached!';
});

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

require __DIR__.'/admin.php';
require __DIR__.'/Affiliate.php';

Route::get('/', [FrontSiteController::class, 'index'])->name('home');
Route::get('/products/{product}', [FrontSiteController::class, 'show'])->name('products.show');
Route::get('/pages/{slug}', [SitePageController::class, 'show'])->name('pages.show');
Route::get('/ref/{tracking_code}', [AffiliateTrackingController::class, 'handle'])->name('affiliate.track');
Route::get('/schedule-run', [SteadfastController::class, 'runSchedule'])->name('steadfast.schedule-run');
Route::get('/queue-work', [SteadfastController::class, 'runQueueWorker'])->name('queue.work');
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
