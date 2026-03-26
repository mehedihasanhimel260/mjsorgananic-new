<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use Illuminate\Http\Request;

class AffiliateTrackingController extends Controller
{
    public function handle(Request $request, string $trackingCode)
    {
        $affiliateLink = AffiliateLink::with('product')
            ->where('tracking_code', $trackingCode)
            ->first();

        if ($affiliateLink) {
            save_affiliate_attribution($request, $affiliateLink);
        }

        return redirect()->route('home');
    }
}
