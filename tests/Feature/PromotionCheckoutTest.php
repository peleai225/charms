<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    private Promotion $promotion;

    protected function setUp(): void
    {
        parent::setUp();

        // Le harnais de test ne transporte pas le cookie de session d'une requête
        // à l'autre : sans cookie épinglé, chaque requête ouvre une session neuve
        // et le checkout ne retrouve pas le panier rempli juste avant.
        $this->withCookie(config('session.cookie'), str_repeat('a', 40));

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);

        $this->promotion = Promotion::create([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ]);

        $this->promotion->categories()->attach($this->tshirts->id);
    }

    private function product(float $price = 4000, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => 'active',
            'category_id' => $this->tshirts->id,
            'stock_quantity' => 50,
        ]);
    }

    /**
     * Champs exigés par CheckoutController::store(). `cod` est la seule méthode
     * de paiement active par défaut (payment_cod_enabled vaut '1' sans réglage).
     */
    private function checkoutPayload(): array
    {
        return [
            'phone' => '0700000000',
            'email' => 'awa@example.test',
            'shipping_first_name' => 'Awa',
            'shipping_last_name' => 'Koné',
            'shipping_address' => 'Cocody, Abidjan',
            'shipping_city' => 'Abidjan',
            'shipping_country' => 'CI',
            'same_billing' => true,
            'payment_method' => 'cod',
        ];
    }

    // ================================================================
    // Persistance de la remise de lot
    // ================================================================

    public function test_order_item_carries_the_lot_discount(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('4000.00', $item->unit_price);
        $this->assertSame('2000.00', $item->discount_amount);
        $this->assertSame('10000.00', $item->total);
    }

    public function test_order_item_records_the_promotion(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $item = Order::latest()->firstOrFail()->items()->firstOrFail();

        $this->assertSame($this->promotion->id, $item->promotion_id);
        $this->assertSame('3 t-shirts pour 10 000 F', $item->promotion_name);
    }

    public function test_order_subtotal_is_net_of_the_lot(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        $this->assertSame('10000.00', $order->subtotal);
        $this->assertSame('0.00', $order->discount_amount);
    }

    public function test_order_total_matches_the_lot_price(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        $this->assertSame(
            (float) $order->subtotal + (float) $order->shipping_amount + (float) $order->tax_amount,
            (float) $order->total
        );
    }

    public function test_remainder_stays_at_catalog_price(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 5]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        // 1 lot (10 000) + 2 × 4 000 = 18 000
        $this->assertSame('18000.00', $order->subtotal);
    }

    public function test_bundle_discount_is_derivable_from_items(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->withSum('items', 'discount_amount')->firstOrFail();

        $this->assertSame(2000.0, (float) $order->items_sum_discount_amount);
    }

    // ================================================================
    // Sans offre, rien ne change
    // ================================================================

    public function test_items_without_promotion_record_no_discount(): void
    {
        $accessories = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);

        $product = Product::create([
            'name' => 'Casquette',
            'sku' => 'SKU-CAP',
            'sale_price' => 3000,
            'status' => 'active',
            'category_id' => $accessories->id,
            'stock_quantity' => 10,
        ]);

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $item = Order::latest()->firstOrFail()->items()->firstOrFail();

        $this->assertSame('0.00', $item->discount_amount);
        $this->assertNull($item->promotion_id);
        $this->assertSame('6000.00', $item->total);
    }

    // ================================================================
    // Écart entre le total affiché et le total encaissé
    // ================================================================

    public function test_order_is_refused_when_the_total_changed_since_the_cart(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);

        // Le client a vu 10 000 F, puis l'offre est désactivée avant qu'il valide.
        $this->promotion->update(['is_active' => false]);

        $this->post(route('checkout.store'), array_merge($this->checkoutPayload(), [
            'expected_total' => 10000,
        ]))->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_proceeds_when_the_expected_total_still_holds(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);

        $cart = \App\Models\Cart::firstOrFail();
        $total = $cart->total;

        $this->post(route('checkout.store'), array_merge($this->checkoutPayload(), [
            'expected_total' => $total,
        ]));

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_order_proceeds_when_no_expected_total_is_sent(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertDatabaseCount('orders', 1);
    }
}
