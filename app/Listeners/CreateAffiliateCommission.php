<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;

class CreateAffiliateCommission
{
    public function handle(OrderPaid $event): void
    {
        try {
            $order = $event->order;

            if (empty($order->affiliate_code)) {
                return;
            }

            $affiliate = Affiliate::active()->where('code', $order->affiliate_code)->first();

            if (!$affiliate) {
                return;
            }

            // Pas d'auto-commission
            $customer = $order->customer;
            if ($customer && $customer->user_id === $affiliate->user_id) {
                return;
            }

            $rate = $affiliate->effective_rate;
            $commissionAmount = round($order->total * $rate / 100, 2);

            if ($commissionAmount <= 0) {
                return;
            }

            AffiliateCommission::create([
                'affiliate_id' => $affiliate->id,
                'order_id' => $order->id,
                'order_total' => $order->total,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            $affiliate->increment('total_earned', $commissionAmount);
        } catch (\Throwable $e) {
            \Log::error('CreateAffiliateCommission failed', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
