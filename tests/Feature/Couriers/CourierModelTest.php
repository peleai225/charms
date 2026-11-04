<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\CourierMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CourierModelTest extends TestCase
{
    use CreatesOrders, RefreshDatabase;

    private function courier(): Courier
    {
        return Courier::create(['name' => 'Koné', 'phone' => '0707070707', 'type' => Courier::TYPE_INDEPENDENT]);
    }

    public function test_an_order_can_be_marked_courier_on_the_way(): void
    {
        $order = $this->makeOrder();

        $order->update(['status' => 'delivery_in_progress']);

        $this->assertSame('delivery_in_progress', $order->fresh()->status);
    }

    public function test_the_balance_is_the_sum_of_movements(): void
    {
        $courier = $this->courier();
        foreach ([15000, -10000, 2500] as $change) {
            CourierMovement::create(['courier_id' => $courier->id, 'type' => 'point', 'balance_change' => $change]);
        }

        $this->assertSame(7500.0, $courier->balance());
    }

    public function test_a_validated_movement_cannot_be_changed(): void
    {
        $movement = CourierMovement::create(['courier_id' => $this->courier()->id, 'type' => 'payment', 'amount_received' => 5000, 'balance_change' => -5000]);

        $this->expectException(LogicException::class);

        $movement->update(['amount_received' => 0]);
    }

    public function test_orders_on_route_lists_only_orders_with_the_courier_on_the_way(): void
    {
        $courier = $this->courier();
        $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $courier->id]);
        $this->makeOrder(['status' => 'delivered', 'courier_id' => $courier->id]);

        $this->assertSame(1, $courier->ordersOnRoute()->count());
    }
}
