<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryChargeSetting;
use Illuminate\Http\Request;

class DeliveryChargeController extends Controller
{
    private function getSetting(): DeliveryChargeSetting
    {
        return DeliveryChargeSetting::firstOrCreate([], [
            'inside_dhaka_delivery_charge' => 0,
            'outside_dhaka_delivery_charge' => 0,
            'custom_delivery_charge' => 0,
            'free_delivery_min_order_amount' => 0,
        ]);
    }

    public function index()
    {
        $setting = $this->getSetting();

        return view('admin.delivery-charge.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'inside_dhaka_delivery_charge' => 'required|numeric|min:0',
            'outside_dhaka_delivery_charge' => 'required|numeric|min:0',
            'custom_delivery_charge' => 'required|numeric|min:0',
            'free_delivery_min_order_amount' => 'required|numeric|min:0',
        ]);

        $this->getSetting()->update($validated);

        return redirect()->route('admin.delivery-charge.index')->with('success', 'Delivery charges updated successfully.');
    }
}
