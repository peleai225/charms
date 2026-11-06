<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportsCountDeliveredOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $socks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->socks = Product::create(['name' => 'Chaussettes Bunny', 'sku' => 'CH-1', 'sale_price' => 2500, 'status' => 'active']);
    }

    private function order(string $status, string $paymentStatus, float $total, string $method = 'cod'): Order
    {
        static $n = 0;
        $n++;
        $address = ['first_name' => 'Awa', 'last_name' => 'Koné', 'address' => 'Cocody', 'city' => 'Abidjan', 'postal_code' => '00225', 'country' => 'CI'];
        $order = Order::create(array_merge(
            [
                'order_number' => 'CMD-TEST-'.$n,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $method,
                'subtotal' => $total,
                'total' => $total,
                'billing_email' => 'awa@example.com',
            ],
            collect($address)->mapWithKeys(fn ($v, $k) => ["billing_{$k}" => $v, "shipping_{$k}" => $v])->all(),
        ));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->socks->id,
            'name' => $this->socks->name,
            'sku' => $this->socks->sku,
            'quantity' => (int) ($total / 2500),
            'unit_price' => 2500,
            'total' => $total,
        ]);

        return $order;
    }

    private function reportFilters(): string
    {
        return '?'.http_build_query(['start_date' => now()->subDay()->toDateString(), 'end_date' => now()->toDateString()]);
    }

    public function test_cash_on_delivery_orders_count_as_sales_once_delivered(): void
    {
        $this->order('delivered', 'pending', 5000);   // livrée, payée à la livraison
        $this->order('processing', 'paid', 2500, 'jeko'); // payée en ligne
        $this->order('confirmed', 'pending', 7500);   // pas encore livrée
        $this->order('cancelled', 'paid', 10000, 'jeko'); // annulée

        $this->actingAs($this->admin)
            ->get(route('admin.reports.sales').$this->reportFilters())
            ->assertInertia(fn (Assert $page) => $page
                ->where('totals.orders', 2)
                ->where('totals.revenue', fn ($revenue) => (float) $revenue === 7500.0));
    }

    public function test_the_product_report_shows_products_sold_at_delivery(): void
    {
        $this->order('delivered', 'pending', 5000);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.products').$this->reportFilters())
            ->assertInertia(fn (Assert $page) => $page
                ->where('topProducts.0.name', 'Chaussettes Bunny')
                ->where('topProducts.0.quantity_sold', fn ($q) => (int) $q === 2)
                ->where('noSalesProducts', fn ($p) => ! collect($p)->pluck('name')->contains('Chaussettes Bunny')));
    }
}
