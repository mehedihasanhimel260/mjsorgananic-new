<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateWithdrawController extends Controller
{
    public function index()
    {
        $withdrawRequests = AffiliateWithdrawRequest::with('affiliate')
            ->latest('requested_at')
            ->paginate(20);

        return view('admin.affiliate-withdraws.index', compact('withdrawRequests'));
    }

    public function show(AffiliateWithdrawRequest $affiliateWithdrawRequest)
    {
        $affiliateWithdrawRequest->load('affiliate');

        return view('admin.affiliate-withdraws.show', compact('affiliateWithdrawRequest'));
    }

    public function approve(Request $request, AffiliateWithdrawRequest $affiliateWithdrawRequest): RedirectResponse
    {
        if ($affiliateWithdrawRequest->status !== AffiliateWithdrawRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $affiliateWithdrawRequest->update([
            'status' => AffiliateWithdrawRequest::STATUS_APPROVED,
            'admin_note' => $request->input('admin_note'),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Withdraw request approved successfully.');
    }

    public function reject(Request $request, AffiliateWithdrawRequest $affiliateWithdrawRequest): RedirectResponse
    {
        if ($affiliateWithdrawRequest->status !== AffiliateWithdrawRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $affiliateWithdrawRequest->update([
            'status' => AffiliateWithdrawRequest::STATUS_REJECTED,
            'admin_note' => $request->input('admin_note'),
        ]);

        return back()->with('success', 'Withdraw request rejected successfully.');
    }

    public function markPaid(Request $request, AffiliateWithdrawRequest $affiliateWithdrawRequest): RedirectResponse
    {
        if ($affiliateWithdrawRequest->status !== AffiliateWithdrawRequest::STATUS_APPROVED) {
            return back()->with('error', 'Only approved requests can be marked as paid.');
        }

        try {
            DB::transaction(function () use ($request, $affiliateWithdrawRequest) {
                $affiliateWithdrawRequest->refresh();

                if ($affiliateWithdrawRequest->status !== AffiliateWithdrawRequest::STATUS_APPROVED) {
                    throw new \RuntimeException('Withdraw request status changed. Please try again.');
                }

                $affiliate = $affiliateWithdrawRequest->affiliate()->lockForUpdate()->first();

                if ((float) $affiliate->balance < (float) $affiliateWithdrawRequest->amount) {
                    throw new \RuntimeException('Affiliate balance is lower than the approved withdraw amount.');
                }

                $affiliate->decrement('balance', (float) $affiliateWithdrawRequest->amount);

                AffiliateWalletTransaction::create([
                    'affiliate_id' => $affiliate->id,
                    'type' => 'debit',
                    'amount' => $affiliateWithdrawRequest->amount,
                    'description' => 'Withdraw paid for request #' . $affiliateWithdrawRequest->id,
                ]);

                $affiliateWithdrawRequest->update([
                    'status' => AffiliateWithdrawRequest::STATUS_PAID,
                    'admin_note' => $request->input('admin_note'),
                    'paid_at' => now(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Withdraw request marked as paid and wallet debited.');
    }
}
