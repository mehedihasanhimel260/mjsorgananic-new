<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    private function getMinimumWithdrawAmount(): float
    {
        return (float) (SiteSetting::query()->value('affiliate_minimum_withdraw_amount') ?? 500);
    }

    public function store(Request $request): RedirectResponse
    {
        $affiliate = auth()->guard('affiliate')->user();
        $minimumWithdrawAmount = $this->getMinimumWithdrawAmount();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minimumWithdrawAmount],
            'account_type' => ['required', 'in:bkash,nagad,rocket'],
            'account_number' => ['required', 'string', 'max:30'],
            'account_name' => ['required', 'string', 'max:255'],
        ]);

        if ((float) $affiliate->balance < $minimumWithdrawAmount) {
            return back()->with('error', 'Minimum '.number_format($minimumWithdrawAmount, 2).' BDT balance required before requesting a withdraw.');
        }

        if ((float) $validated['amount'] > (float) $affiliate->balance) {
            return back()->with('error', 'Withdraw amount cannot be greater than your current balance.');
        }

        AffiliateWithdrawRequest::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $validated['amount'],
            'payment_method' => 'mobile_banking',
            'account_type' => $validated['account_type'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'status' => AffiliateWithdrawRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Withdraw request submitted successfully.');
    }
}
