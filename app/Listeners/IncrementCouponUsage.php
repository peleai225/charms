<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\Log;

class IncrementCouponUsage
{
    /**
     * Incrémente l'usage du coupon et crée un enregistrement CouponUsage
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if (!$order->coupon_code) {
            return;
        }

        try {
            $coupon = Coupon::where('code', $order->coupon_code)->first();

            if (!$coupon) {
                Log::warning('Coupon non trouvé lors de l\'incrémentation', [
                    'order_id' => $order->id,
                    'coupon_code' => $order->coupon_code,
                ]);
                return;
            }

            // Incrémenter le compteur d'usage
            $coupon->incrementUsage();

            // Créer un enregistrement CouponUsage pour le suivi
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'discount_amount' => $order->discount_amount,
            ]);

            Log::info('Usage du coupon incrémenté', [
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'incrémentation de l\'usage du coupon', [
                'order_id' => $order->id,
                'coupon_code' => $order->coupon_code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
