<?php

namespace App\Services;

use App\Models\Order;

class UserVerificationService
{
    public function markVerifiedFromOrder(Order $order): void
    {
        $order->loadMissing('user');

        if (! $order->user) {
            return;
        }

        if (! in_array($order->order_status, ['delivered', 'partial_delivered'], true)) {
            return;
        }

        $order->user->update([
            'status' => 'verified',
        ]);
    }
}
