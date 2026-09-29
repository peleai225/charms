<?php

namespace App\Observers;

use App\Models\AffiliateCommission;
use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === Order::STATUS_DELIVERED) {
            AffiliateCommission::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
        }
    }
}
