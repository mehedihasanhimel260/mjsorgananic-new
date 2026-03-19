<?php
//common Route link start
use Illuminate\Support\Facades\Route;
//common Route link end

//user Route link start
use App\Http\Controllers\ProfileController;
//user Route link end

//admin Route link start
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductStockBatchController;
use App\Http\Controllers\Front_site\FrontSiteController;
use App\Http\Controllers\Front_site\OrderController;
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
            // Product Stock start
            Route::resource('product-stocks', ProductStockBatchController::class);
            // Product end
        });
    });

//admin Route End

Route::get('/', [FrontSiteController::class, 'index'])->name('home');
Route::post('/cart/add', [OrderController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update-quantity', [OrderController::class, 'updateCartQuantity'])->name('cart.updateQuantity');
Route::get('/cart', [OrderController::class, 'getCart'])->name('cart.get');

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
