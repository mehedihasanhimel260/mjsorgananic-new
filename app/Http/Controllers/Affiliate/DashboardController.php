<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateLink;
use App\Models\AffiliateWithdrawRequest;
use App\Models\AffiliateWalletTransaction;
use App\Models\Order;
use App\Models\ProductCommission;
use App\Models\SiteSetting;

class DashboardController extends Controller
{
    public function index()
    {
        $affiliate = auth()->guard('affiliate')->user();
        $minimumWithdrawAmount = (float) (SiteSetting::query()->value('affiliate_minimum_withdraw_amount') ?? 500);

        $links = AffiliateLink::with('product')
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->take(10)
            ->get();

        $affiliateOrdersQuery = Order::with(['user', 'items.product'])
            ->where('affiliate_id', $affiliate->id)
            ->latest();

        $affiliateOrders = (clone $affiliateOrdersQuery)->count();
        $recentOrders = (clone $affiliateOrdersQuery)->take(10)->get();

        $commissionsQuery = AffiliateCommission::with(['order', 'product'])
            ->where('affiliate_id', $affiliate->id)
            ->latest();

        $recentCommissions = (clone $commissionsQuery)->take(10)->get();
        $totalCommission = (float) (clone $commissionsQuery)->sum('commission_amount');
        $commissionProducts = ProductCommission::with('product')
            ->where('status', 'active')
            ->whereHas('product', function ($query) {
                $query->where('status', 'active');
            })
            ->latest()
            ->take(20)
            ->get();

        $walletQuery = AffiliateWalletTransaction::where('affiliate_id', $affiliate->id)
            ->latest();

        $walletTransactions = (clone $walletQuery)->take(10)->get();
        $totalWalletCredit = (float) (clone $walletQuery)->where('type', 'credit')->sum('amount');
        $totalWalletDebit = (float) (clone $walletQuery)->where('type', 'debit')->sum('amount');
        $withdrawRequests = AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
            ->latest('requested_at')
            ->take(10)
            ->get();
        $pendingWithdrawAmount = (float) AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
            ->where('status', AffiliateWithdrawRequest::STATUS_PENDING)
            ->sum('amount');
        $totalWithdrawn = (float) AffiliateWithdrawRequest::where('affiliate_id', $affiliate->id)
            ->where('status', AffiliateWithdrawRequest::STATUS_PAID)
            ->sum('amount');

        $summary = [
            'total_links' => AffiliateLink::where('affiliate_id', $affiliate->id)->count(),
            'total_orders' => $affiliateOrders,
            'total_commission' => $totalCommission,
            'wallet_credit' => $totalWalletCredit,
            'wallet_debit' => $totalWalletDebit,
            'balance' => (float) $affiliate->balance,
            'total_withdrawn' => $totalWithdrawn,
            'pending_withdraw' => $pendingWithdrawAmount,
            'minimum_withdraw' => $minimumWithdrawAmount,
        ];

        return view('affiliates.dashboard.index', compact(
            'affiliate',
            'links',
            'affiliateOrders',
            'recentOrders',
            'recentCommissions',
            'commissionProducts',
            'walletTransactions',
            'withdrawRequests',
            'summary'
        ));
    }
}
