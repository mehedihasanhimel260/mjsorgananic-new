<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawRequest;
use App\Models\AffiliateWalletTransaction;
use App\Models\Order;

class ReportController extends Controller
{
    public function orders()
    {
        $affiliate = auth()->guard('affiliate')->user();

        $orders = Order::with(['user', 'items.product'])
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(20);

        return view('affiliates.orders.index', compact('orders'));
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

        $transactions = AffiliateWalletTransaction::where('affiliate_id', $affiliate->id)
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
