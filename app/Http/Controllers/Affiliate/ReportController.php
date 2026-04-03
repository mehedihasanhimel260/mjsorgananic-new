<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawRequest;
use App\Models\AffiliateWalletTransaction;
use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly SteadfastService $steadfastService)
    {
    }

    public function orders()
    {
        $affiliate = auth()->guard('affiliate')->user();

        $orders = Order::with(['user', 'items.product'])
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(20);

        return view('affiliates.orders.index', compact('orders'));
    }

    public function bookCourier(Order $order)
    {
        $affiliate = auth()->guard('affiliate')->user();

        if ((int) $order->affiliate_id !== (int) $affiliate->id) {
            abort(403);
        }

        if ($order->track_id) {
            return back()->with('error', 'Courier booking already completed for this order.');
        }

        if ($order->order_status === 'cancelled') {
            return back()->with('error', 'Cancelled orders cannot be booked.');
        }

        $result = $this->steadfastService->bookOrder($order);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function cancelOrder(Order $order)
    {
        $affiliate = auth()->guard('affiliate')->user();

        if ((int) $order->affiliate_id !== (int) $affiliate->id) {
            abort(403);
        }

        if ($order->track_id) {
            return back()->with('error', 'Booked courier orders can no longer be cancelled from the affiliate panel.');
        }

        $order->update([
            'order_status' => 'cancelled',
        ]);

        return back()->with('success', 'Order cancelled successfully.');
    }

    public function commissions()
    {
        $affiliate = auth()->guard('affiliate')->user();

        $commissions = AffiliateCommission::with(['order', 'product'])
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(20);

        return view('affiliates.commissions.index', compact('commissions'));
    }

    public function wallet()
    {
        $affiliate = auth()->guard('affiliate')->user();

        $transactions = AffiliateWalletTransaction::with('order')
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(20);

        $withdrawRequests = AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
            ->latest('requested_at')
            ->paginate(20, ['*'], 'withdraw_page');

        $summary = [
            'balance' => (float) $affiliate->balance,
            'minimum_withdraw' => 500,
            'pending_withdraw' => (float) AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
                ->where('status', AffiliateWithdrawRequest::STATUS_PENDING)
                ->sum('amount'),
            'total_withdrawn' => (float) AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
                ->where('status', AffiliateWithdrawRequest::STATUS_PAID)
                ->sum('amount'),
        ];

        return view('affiliates.wallet.index', compact('transactions', 'withdrawRequests', 'summary'));
    }
}
