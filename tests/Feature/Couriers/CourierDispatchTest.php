<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CourierDispatchTest extends TestCase
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
        $this->setZoneFee(1500);
    }

    private function setZoneFee(float $fee): void
    {
        Setting::set('shipping_zones', json_encode([['name' => 'Cocody', 'cities' => 'Cocody', 'price' => 1500, 'courier_fee' => $fee]]));
        Setting::clearCache();
    }

    private function dispatch(array $orders, ?Courier $courier = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.couriers.dispatch', $courier ?? $this->courier), [
            'ids' => collect($orders)->pluck('id')->all(),
        ]);
    }

    public function test_confirmed_and_processing_orders_leave_with_the_courier(): void
    {
        $orders = [$this->makeOrder(['status' => 'confirmed']), $this->makeOrder(['status' => 'processing'])];

        $this->dispatch($orders)->assertSessionHasNoErrors()->assertRedirect();

        foreach ($orders as $order) {
            $order->refresh();
            $this->assertSame('delivery_in_progress', $order->status);
            $this->assertSame($this->courier->id, $order->courier_id);
            $this->assertNotNull($order->dispatched_at);
            $this->assertSame(1500.0, (float) $order->courier_fee);
        }
    }

    public function test_other_orders_are_skipped(): void
    {
        $other = Courier::create(['name' => 'Yango', 'phone' => '0101010101', 'type' => 'company']);
        $orders = [
            $this->makeOrder(['status' => 'pending']),
            $this->makeOrder(['status' => 'cancelled']),
            $this->makeOrder(['status' => 'delivered']),
            $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $other->id]),
        ];

        $this->dispatch($orders)->assertSessionHas('success', fn ($m) => str_contains($m, '4 ignorée'));

        $this->assertSame(['pending', 'cancelled', 'delivered', 'delivery_in_progress'], collect($orders)->map(fn ($o) => $o->fresh()->status)->all());
        $this->assertSame($other->id, $orders[3]->fresh()->courier_id);
    }

    public function test_an_order_outside_known_zones_leaves_with_an_unknown_fee(): void
    {
        $order = $this->makeOrder(['shipping_city' => 'Bouaké']);

        $this->dispatch([$order]);

        $this->assertSame('delivery_in_progress', $order->fresh()->status);
        $this->assertNull($order->fresh()->courier_fee);
    }

    public function test_the_fee_is_kept_when_the_zone_price_changes_later(): void
    {
        $order = $this->makeOrder();
        $this->dispatch([$order]);

        $this->setZoneFee(2000);

        $this->assertSame(1500.0, (float) $order->fresh()->courier_fee);
    }

    public function test_an_order_withdrawn_before_leaving_goes_back_to_preparation(): void
    {
        $order = $this->makeOrder();
        $this->dispatch([$order]);

        $this->actingAs($this->admin)->post(route('admin.orders.withdraw-courier', $order))->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertNull($order->courier_id);
        $this->assertNull($order->courier_fee);
        $this->assertNull($order->dispatched_at);
        $this->assertSame(0, $order->delivery_attempts);
    }

    public function test_handing_over_sends_no_message(): void
    {
        $this->dispatch([$this->makeOrder(), $this->makeOrder()]);

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_the_order_list_shows_who_carries_each_order(): void
    {
        Courier::create(['name' => 'Ancien', 'phone' => '0101', 'type' => 'independent', 'is_active' => false]);
        $order = $this->makeOrder();
        $this->dispatch([$order]);

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->where('couriers', fn ($c) => collect($c)->pluck('name')->all() === ['Koné'])
                ->where('orders.data.0.courier_name', 'Koné'));
    }

    public function test_an_inactive_courier_cannot_receive_orders(): void
    {
        $this->courier->update(['is_active' => false]);
        $order = $this->makeOrder();

        $this->dispatch([$order])->assertSessionHasErrors('courier');

        $this->assertSame('processing', $order->fresh()->status);
    }
}
