<?php

namespace Tests\Feature\Couriers;

use App\Models\Order;

trait CreatesOrders
{
    private static int $orderSeq = 0;

    protected function makeOrder(array $attributes = []): Order
    {
        self::$orderSeq++;
        $address = ['first_name' => 'Awa', 'last_name' => 'Koné', 'address' => 'Rue 12', 'city' => 'Cocody', 'postal_code' => '00225', 'country' => 'CI'];

        return Order::create(array_merge(
            [
                'order_number' => 'CMD-LIV-'.self::$orderSeq,
                'status' => 'processing',
                'payment_status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => 5000,
                'total' => 5000,
                'billing_email' => 'client'.self::$orderSeq.'@example.com',
                'billing_phone' => '0707000'.str_pad((string) self::$orderSeq, 3, '0', STR_PAD_LEFT),
                'shipping_phone' => '0707000'.str_pad((string) self::$orderSeq, 3, '0', STR_PAD_LEFT),
            ],
            collect($address)->mapWithKeys(fn ($v, $k) => ["billing_{$k}" => $v, "shipping_{$k}" => $v])->all(),
            $attributes,
        ));
    }
}
