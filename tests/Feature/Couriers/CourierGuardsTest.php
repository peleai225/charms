<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Une commande chez un livreur ne sort de chez lui que par le point ou la reprise.
 */
class CourierGuardsTest extends TestCase
{
    use CreatesOrders, RefreshDatabase;

    private User $admin;

    private Courier $courier;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->courier = Courier::create(['name' => 'Koné', 'phone' => '0707070707', 'type' => 'independent']);
    }

    private function onRoute()
    {
        return $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $this->courier->id, 'courier_fee' => 1000, 'dispatched_at' => now()]);
    }

    public function test_bulk_status_change_leaves_orders_with_a_courier_untouched(): void
    {
        $order = $this->onRoute();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.bulk-status'), ['ids' => [$order->id], 'status' => 'delivered'])
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'chez un livreur'));

        $this->assertSame('delivery_in_progress', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_an_order_with_a_courier_cannot_change_status_from_its_page(): void
    {
        $order = $this->onRoute();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHasErrors('status');

        $this->assertSame('delivery_in_progress', $order->fresh()->status);
    }

    public function test_the_courier_on_the_way_status_cannot_be_set_by_hand(): void
    {
        $order = $this->makeOrder(['status' => 'processing']);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'delivery_in_progress'])
            ->assertSessionHasErrors('status');

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_an_order_back_in_preparation_after_a_delivery_can_leave_again(): void
    {
        // Livrée par Koné (le livreur reste en mémoire), puis remise en préparation (échange, erreur…)
        $order = $this->makeOrder(['status' => 'delivered', 'courier_id' => $this->courier->id]);
        $this->actingAs($this->admin)->patch(route('admin.orders.status', $order), ['status' => 'processing']);

        $this->actingAs($this->admin)
            ->post(route('admin.couriers.dispatch', $this->courier), ['ids' => [$order->id]])
            ->assertSessionHas('success', fn ($m) => str_starts_with($m, '1 commande(s) confiée(s)'));

        $this->assertSame('delivery_in_progress', $order->fresh()->status);
    }
}
