<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWalletTransaction;
use App\Models\Chat;
use App\Models\DeliveryChargeSetting;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            /** @var Admin $admin */
            $admin = Auth::guard('admin')->user();

            return redirect()->route($this->resolveRedirectRoute($admin));
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field => $request->login,
            'password' => $request->password,
        ];

        $remember = $request->filled('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            /** @var Admin $admin */
            $admin = Auth::guard('admin')->user();

            return redirect()->route($this->resolveRedirectRoute($admin));
        }

        return back()
            ->withErrors([
                'login' => 'Invalid email/phone or password',
            ])
            ->withInput();
    }
    public function dashboard()
    {
        $range = request('range', '7d');
        $validRanges = ['today', '7d', '30d'];

        if (! in_array($range, $validRanges, true)) {
            $range = '7d';
        }

        $fromDate = match ($range) {
            'today' => Carbon::today(),
            '30d' => Carbon::now()->subDays(29)->startOfDay(),
            default => Carbon::now()->subDays(6)->startOfDay(),
        };

        $toDate = Carbon::now();

        $ordersQuery = Order::query()->whereBetween('created_at', [$fromDate, $toDate]);
        $usersQuery = User::query()->whereBetween('created_at', [$fromDate, $toDate]);
        $chatsQuery = Chat::query()->whereBetween('last_message_at', [$fromDate, $toDate]);
        $affiliateCommissionQuery = AffiliateCommission::query()->whereBetween('created_at', [$fromDate, $toDate]);
        $walletQuery = AffiliateWalletTransaction::query()->whereBetween('created_at', [$fromDate, $toDate]);

        $summary = [
            'total_orders' => (clone $ordersQuery)->count(),
            'total_sales' => (float) (clone $ordersQuery)->sum('total_amount'),
            'total_users' => (clone $usersQuery)->count(),
            'pending_chats' => (clone $chatsQuery)->where('status', 'open')->count(),
            'low_stock_products' => ProductStockBatch::select('product_id', DB::raw('SUM(quantity) as stock_qty'))
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) <= 5')
                ->get()
                ->count(),
            'affiliate_orders' => (clone $ordersQuery)->where('order_type', 'affiliate')->count(),
            'affiliate_commission' => (float) (clone $affiliateCommissionQuery)->sum('commission_amount'),
        ];

        $recentOrders = Order::with(['user'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest()
            ->take(10)
            ->get();

        $recentChats = Chat::with(['user', 'latestConversion'])
            ->whereBetween('last_message_at', [$fromDate, $toDate])
            ->latest('last_message_at')
            ->take(10)
            ->get();

        $stockAlerts = Product::query()
            ->leftJoin('product_stock_batches', 'products.id', '=', 'product_stock_batches.product_id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.status',
                DB::raw('COALESCE(SUM(product_stock_batches.quantity), 0) as stock_qty')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.status')
            ->havingRaw('COALESCE(SUM(product_stock_batches.quantity), 0) <= 5')
            ->orderBy('stock_qty')
            ->take(10)
            ->get();

        $affiliateActivities = AffiliateCommission::with(['affiliate', 'order', 'product'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest()
            ->take(10)
            ->get();

        $courierSnapshot = [
            'booked' => (clone $ordersQuery)->whereNotNull('track_id')->count(),
            'unbooked' => (clone $ordersQuery)->whereNull('track_id')->count(),
            'in_review' => (clone $ordersQuery)->where('order_status', 'in_review')->count(),
            'delivered' => (clone $ordersQuery)->where('order_status', 'delivered')->count(),
        ];

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.quantity * order_items.sell_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $sourceOverview = [
            'direct_orders' => (clone $ordersQuery)->where('order_type', 'direct')->count(),
            'affiliate_orders' => (clone $ordersQuery)->where('order_type', 'affiliate')->count(),
        ];

        $deliverySetting = DeliveryChargeSetting::latest()->first();
        $insideCharge = (float) ($deliverySetting->inside_dhaka_delivery_charge ?? 0);
        $outsideCharge = (float) ($deliverySetting->outside_dhaka_delivery_charge ?? 0);
        $customCharge = (float) ($deliverySetting->custom_delivery_charge ?? 0);

        $deliveryOverview = [
            'inside_charge' => $insideCharge,
            'outside_charge' => $outsideCharge,
            'custom_charge' => $customCharge,
            'inside_count' => (clone $ordersQuery)->where('delivery_charge', $insideCharge)->count(),
            'outside_count' => (clone $ordersQuery)->where('delivery_charge', $outsideCharge)->count(),
            'custom_count' => (clone $ordersQuery)->where('delivery_charge', $customCharge)->count(),
            'free_count' => (clone $ordersQuery)->where('delivery_charge', 0)->count(),
            'delivery_amount' => (float) (clone $ordersQuery)->sum('delivery_charge'),
            'wallet_credit' => (float) (clone $walletQuery)->where('type', 'credit')->sum('amount'),
        ];

        return view('admin.dashboard.index', compact(
            'range',
            'summary',
            'recentOrders',
            'recentChats',
            'stockAlerts',
            'affiliateActivities',
            'courierSnapshot',
            'topProducts',
            'sourceOverview',
            'deliveryOverview'
        ));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function resolveRedirectRoute(Admin $admin): string
    {
        $routePermissionMap = [
            'admin.dashboard' => 'dashboard.view',
            'admin.products.index' => 'products.manage',
            'admin.orders.index' => 'orders.manage',
            'admin.users.index' => 'users.manage',
            'admin.affiliates.index' => 'affiliates.manage',
            'admin.chats.index' => 'chats.manage',
            'admin.faqs.index' => 'faqs.manage',
            'admin.site-settings.general' => 'settings.manage',
            'admin.roles.index' => 'access-control.manage',
        ];

        foreach ($routePermissionMap as $routeName => $permission) {
            if ($admin->hasPermission($permission)) {
                return $routeName;
            }
        }

        return 'admin.login';
    }
}
