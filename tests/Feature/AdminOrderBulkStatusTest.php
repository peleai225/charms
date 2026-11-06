<?php

namespace Tests\Feature;

use App\Events\OrderCancelled;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminOrderBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $socks;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->socks = Product::create(['name' => 'Chaussettes', 'sku' => 'CH-1', 'sale_price' => 2500, 'status' => 'active', 'track_stock' => true, 'stock_quantity' => 10]);
    }

    private function order(string $status, string $paymentStatus = 'pending', string $method = 'cod'): Order
    {
        static $n = 0;
        $n++;
        $address = ['first_name' => 'Awa', 'last_name' => 'Koné', 'address' => 'Cocody', 'city' => 'Abidjan', 'postal_code' => '00225', 'country' => 'CI'];
        $order = Order::create(array_merge(
            ['order_number' => 'CMD-BULK-'.$n, 'status' => $status, 'payment_status' => $paymentStatus, 'payment_method' => $method,
                'subtotal' => 5000, 'total' => 5000, 'billing_email' => "client{$n}@example.com"],
            collect($address)->mapWithKeys(fn ($v, $k) => ["billing_{$k}" => $v, "shipping_{$k}" => $v])->all(),
        ));
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->socks->id, 'name' => 'Chaussettes', 'sku' => 'CH-1',
            'quantity' => 2, 'unit_price' => 2500, 'total' => 5000]);

        return $order;
    }

    private function bulk(array $orders, string $status)
    {
        return $this->actingAs($this->admin)->post(route('admin.orders.bulk-status'), [
            'ids' => collect($orders)->pluck('id')->all(),
            'status' => $status,
        ]);
    }

    public function test_several_orders_are_marked_delivered_and_cash_on_delivery_is_recorded_as_paid(): void
    {
        $a = $this->order('processing');
        $b = $this->order('confirmed');

        $this->bulk([$a, $b], 'delivered')->assertSessionHasNoErrors()->assertRedirect();

        foreach ([$a, $b] as $order) {
            $order->refresh();
            $this->assertSame('delivered', $order->status);
            $this->assertSame('paid', $order->payment_status);
            $this->assertNotNull($order->delivered_at);
        }
    }

    public function test_bulk_changes_do_not_message_customers(): void
    {
        Event::fake([OrderCancelled::class]);
        $orders = [$this->order('processing'), $this->order('processing')];

        $this->bulk($orders, 'shipped');
        $this->bulk($orders, 'cancelled');

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
        Event::assertNotDispatched(OrderCancelled::class);
    }

    public function test_cancelling_in_bulk_puts_the_items_back_in_stock(): void
    {
        $order = $this->order('processing');

        $this->bulk([$order], 'cancelled');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(12, $this->socks->fresh()->stock_quantity);
    }

    public function test_cancelled_or_delivered_orders_are_not_changed_by_mistake(): void
    {
        $cancelled = $this->order('cancelled');
        $delivered = $this->order('delivered', 'paid');

        $this->bulk([$cancelled], 'delivered');
        $this->bulk([$delivered], 'cancelled');

        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertSame('delivered', $delivered->fresh()->status);
        $this->assertSame(10, $this->socks->fresh()->stock_quantity);
    }

    public function test_only_follow_up_statuses_are_allowed(): void
    {
        $this->bulk([$this->order('processing')], 'refunded')->assertSessionHasErrors('status');
    }
}
