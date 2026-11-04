<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\CourierMovement;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourierSettlementTest extends TestCase
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

    private function onRoute(float $total, ?float $fee = 1000, string $paymentStatus = 'pending', ?Courier $courier = null): Order
    {
        return $this->makeOrder([
            'status' => 'delivery_in_progress',
            'courier_id' => ($courier ?? $this->courier)->id,
            'dispatched_at' => now(),
            'courier_fee' => $fee,
            'total' => $total,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function settle(array $outcomes, float $received, ?Courier $courier = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.couriers.settle', $courier ?? $this->courier), [
            'outcomes' => $outcomes,
            'amount_received' => $received,
        ]);
    }

    public function test_the_point_computes_what_the_courier_owes(): void
    {
        $a = $this->onRoute(5000);
        $b = $this->onRoute(7500);
        $c = $this->onRoute(6000);

        $this->settle([
            $a->id => ['outcome' => 'delivered'],
            $b->id => ['outcome' => 'delivered'],
            $c->id => ['outcome' => 'failed', 'issue' => 'absent'],
        ], 9500)->assertSessionHasNoErrors()->assertRedirect();

        $movement = CourierMovement::sole();
        $this->assertSame(12500.0, (float) $movement->amount_collected);
        $this->assertSame(3000.0, (float) $movement->fees, 'la livraison ratée est payée comme une livraison');
        $this->assertSame(9500.0, (float) $movement->amount_received);
        $this->assertSame(0.0, (float) $movement->balance_change);
        $this->assertSame(3, $movement->orders()->count());
        Mail::assertNothingQueued();
    }

    public function test_delivered_orders_become_delivered_and_paid(): void
    {
        $order = $this->onRoute(5000);

        $this->settle([$order->id => ['outcome' => 'delivered']], 4000);

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->delivered_at);
        $this->assertNotNull($order->paid_at);
    }

    public function test_failed_orders_go_back_to_preparation_with_the_reason(): void
    {
        $order = $this->onRoute(5000);

        $this->settle([$order->id => ['outcome' => 'failed', 'issue' => 'absent']], 0);

        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertNull($order->courier_id);
        $this->assertSame(1, $order->delivery_attempts);
        $this->assertSame('absent', $order->last_delivery_issue);
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_orders_left_out_of_the_point_stay_with_the_courier(): void
    {
        $settled = $this->onRoute(5000);
        $later = $this->onRoute(7500);

        $this->settle([$settled->id => ['outcome' => 'delivered']], 4000);

        $this->assertSame('delivery_in_progress', $later->fresh()->status);
        $this->assertSame($this->courier->id, $later->fresh()->courier_id);
    }

    public function test_an_order_already_paid_online_brings_no_cash(): void
    {
        $order = $this->onRoute(8000, 1000, 'paid');

        $this->settle([$order->id => ['outcome' => 'delivered']], 0);

        $movement = CourierMovement::sole();
        $this->assertSame(0.0, (float) $movement->amount_collected);
        $this->assertSame(1000.0, (float) $movement->fees);
        $this->assertSame(-1000.0, $this->courier->balance(), 'la boutique doit les frais au livreur');
    }

    public function test_a_company_courier_can_pay_later(): void
    {
        $company = Courier::create(['name' => 'Yango', 'phone' => '0101010101', 'type' => 'company']);
        $a = $this->onRoute(7500, 1000, 'pending', $company);
        $b = $this->onRoute(5000, 1000, 'pending', $company);

        $this->settle([$a->id => ['outcome' => 'delivered'], $b->id => ['outcome' => 'delivered']], 0, $company);
        $this->assertSame(10500.0, $company->balance());

        $this->actingAs($this->admin)->post(route('admin.couriers.payments.store', $company), ['amount' => 10500, 'note' => 'Virement du vendredi'])
            ->assertSessionHasNoErrors();
        $this->assertSame(0.0, $company->balance());
    }

    public function test_the_same_point_cannot_be_submitted_twice(): void
    {
        $order = $this->onRoute(5000);
        $outcomes = [$order->id => ['outcome' => 'delivered']];

        $this->settle($outcomes, 4000)->assertSessionHasNoErrors();
        $this->settle($outcomes, 4000)->assertSessionHasErrors('outcomes');

        $this->assertSame(1, CourierMovement::count());
        $this->assertSame(4000.0 - 4000.0, $this->courier->balance());
    }

    public function test_an_order_of_another_courier_is_refused(): void
    {
        $other = Courier::create(['name' => 'Bamba', 'phone' => '0505050505', 'type' => 'independent']);
        $foreign = $this->onRoute(5000, 1000, 'pending', $other);

        $this->settle([$foreign->id => ['outcome' => 'delivered']], 4000)->assertSessionHasErrors('outcomes');

        $this->assertSame('delivery_in_progress', $foreign->fresh()->status);
        $this->assertSame(0, CourierMovement::count());
    }

    public function test_an_inactive_courier_can_still_settle(): void
    {
        $order = $this->onRoute(5000);
        $this->courier->update(['is_active' => false]);

        $this->actingAs($this->admin)->get(route('admin.couriers.show', $this->courier))->assertOk();
        $this->settle([$order->id => ['outcome' => 'delivered']], 4000)->assertSessionHasNoErrors();

        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_a_deleted_order_is_not_in_the_point(): void
    {
        $kept = $this->onRoute(5000);
        $deleted = $this->onRoute(7500);
        $deleted->delete();

        $this->actingAs($this->admin)->get(route('admin.couriers.show', $this->courier))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Couriers/Show')
                ->where('orders', fn ($orders) => collect($orders)->pluck('order_id')->all() === [$kept->id]));
    }

    public function test_a_negative_amount_is_refused(): void
    {
        $order = $this->onRoute(5000);

        $this->settle([$order->id => ['outcome' => 'delivered', 'fee' => -5]], -1)
            ->assertSessionHasErrors(['amount_received', "outcomes.{$order->id}.fee"]);

        $this->assertSame(0, CourierMovement::count());
        $this->assertSame('delivery_in_progress', $order->fresh()->status);
    }

    public function test_the_whatsapp_link_lists_the_orders_to_deliver(): void
    {
        $order = $this->onRoute(5000);

        $this->actingAs($this->admin)->get(route('admin.couriers.show', $this->courier))
            ->assertInertia(fn (Assert $page) => $page
                ->where('whatsapp_url', fn ($url) => str_starts_with($url, 'https://wa.me/2250707070707?text=')
                    && str_contains(urldecode($url), $order->order_number)));
    }
}
