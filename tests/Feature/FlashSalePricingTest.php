<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Ventes flash : le prix remisé est réellement facturé pendant la vente, et
 * plus jamais après. Une vente flash ne se cumule ni avec les offres par lot
 * ni avec les codes promo.
 */
class FlashSalePricingTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');

        // Sans cookie épinglé, chaque requête du test ouvrirait une session neuve
        // et le checkout ne retrouverait pas le panier rempli juste avant.
        $this->withCookie(config('session.cookie'), str_repeat('a', 40));

        $this->category = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function product(float $price = 10000, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Basket',
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => 'active',
            'category_id' => $this->category->id,
            'stock_quantity' => 50,
        ], $attributes));
    }

    /**
     * @param  Product[]  $products
     */
    private function sale(int $percent, array $products, string $start = '-1 hour', string $end = '+2 hours', bool $active = true): FlashSale
    {
        $sale = FlashSale::create([
            'name' => "Flash -{$percent} %",
            'discount_percent' => $percent,
            'starts_at' => now()->modify($start),
            'ends_at' => now()->modify($end),
            'is_active' => $active,
        ]);

        $sale->products()->attach(collect($products)->pluck('id')->all());

        return $sale;
    }

    private function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        $this->post('/panier/ajouter', [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
        ]);
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'phone' => '0700000000',
            'email' => 'awa@example.test',
            'shipping_first_name' => 'Awa',
            'shipping_last_name' => 'Koné',
            'shipping_address' => 'Cocody, Abidjan',
            'shipping_city' => 'Abidjan',
            'shipping_country' => 'CI',
            'same_billing' => true,
            'payment_method' => 'cod',
        ], $overrides);
    }

    // ================================================================
    // Ajout au panier
    // ================================================================

    public function test_adding_during_the_sale_charges_the_flash_price(): void
    {
        $product = $this->product(10000);
        $sale = $this->sale(20, [$product]);

        $this->add($product);

        $item = CartItem::firstOrFail();
        $this->assertSame('8000.00', $item->unit_price);
        $this->assertSame($sale->id, $item->flash_sale_id);
    }

    public function test_adding_after_the_sale_ended_charges_the_normal_price(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product], '-3 hours', '-1 hour');

        $this->add($product);

        $item = CartItem::firstOrFail();
        $this->assertSame('10000.00', $item->unit_price);
        $this->assertNull($item->flash_sale_id);
    }

    public function test_a_scheduled_or_disabled_sale_does_not_apply(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product], '+1 hour', '+3 hours');
        $this->sale(30, [$product], '-1 hour', '+2 hours', active: false);

        $this->add($product);

        $this->assertSame('10000.00', CartItem::firstOrFail()->unit_price);
    }

    public function test_variants_get_the_same_percentage(): void
    {
        $product = $this->product(10000, ['has_variants' => true]);
        $large = ProductVariant::create([
            'product_id' => $product->id, 'name' => '44', 'sku' => 'B-44',
            'sale_price' => 12000, 'stock_quantity' => 5, 'is_active' => true,
        ]);
        $regular = ProductVariant::create([
            'product_id' => $product->id, 'name' => '40', 'sku' => 'B-40',
            'sale_price' => null, 'stock_quantity' => 5, 'is_active' => true,
        ]);
        $this->sale(20, [$product]);

        $this->add($product, 1, $large);
        $this->add($product, 1, $regular);

        $this->assertSame('9600.00', CartItem::where('product_variant_id', $large->id)->firstOrFail()->unit_price);
        $this->assertSame('8000.00', CartItem::where('product_variant_id', $regular->id)->firstOrFail()->unit_price);
    }

    public function test_overlapping_sales_apply_the_best_discount(): void
    {
        $product = $this->product(10000);
        $this->sale(10, [$product]);
        $best = $this->sale(30, [$product], '-2 hours', '+1 hour');

        $this->add($product);

        $item = CartItem::firstOrFail();
        $this->assertSame('7000.00', $item->unit_price);
        $this->assertSame($best->id, $item->flash_sale_id);
    }

    public function test_the_flash_price_is_rounded_to_whole_francs(): void
    {
        $sale = new FlashSale(['discount_percent' => 15]);

        $this->assertSame(4249.0, $sale->priceFor(4999));  // 4 249,15
        $this->assertSame(3613.0, $sale->priceFor(4250));  // 3 612,5
    }

    // ================================================================
    // Fin de la vente : plus jamais de prix flash
    // ================================================================

    public function test_an_item_added_during_the_sale_returns_to_the_normal_price_after_it(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product]);
        $this->add($product, 2);

        Carbon::setTestNow(now()->addHours(3));

        $this->assertSame(20000.0, Cart::firstOrFail()->total);
        $item = CartItem::firstOrFail();
        $this->assertSame('10000.00', $item->unit_price);
        $this->assertNull($item->flash_sale_id);
    }

    public function test_checkout_after_the_end_charges_the_normal_price(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product]);
        $this->add($product, 2);

        Carbon::setTestNow(now()->addHours(3));
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::firstOrFail();
        $this->assertSame('20000.00', $order->subtotal);
        $this->assertSame('10000.00', $order->items()->firstOrFail()->unit_price);
    }

    public function test_checkout_refuses_a_flash_total_once_the_sale_has_ended(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product]);
        $this->add($product, 2);

        // Le client a vu 16 000 F, la vente se termine avant qu'il valide.
        Carbon::setTestNow(now()->addHours(3));

        $this->post(route('checkout.store'), $this->checkoutPayload(['expected_total' => 16000]))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_the_order_records_the_flash_price_paid(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product]);
        $this->add($product, 2);

        $this->post(route('checkout.store'), $this->checkoutPayload(['expected_total' => 16000]));

        $order = Order::firstOrFail();
        $item = $order->items()->firstOrFail();
        $this->assertSame('16000.00', $order->subtotal);
        $this->assertSame('8000.00', $item->unit_price);
        $this->assertSame('16000.00', $item->total);
    }

    public function test_an_item_already_in_the_cart_gets_the_flash_price_once_the_sale_runs(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product], '+1 hour', '+3 hours');
        $this->add($product);

        Carbon::setTestNow(now()->addHours(2));
        $this->add($product);

        $this->assertSame(16000.0, Cart::firstOrFail()->total);
        $this->assertSame('8000.00', CartItem::firstOrFail()->unit_price);
    }

    public function test_deleting_a_running_sale_restores_the_normal_price(): void
    {
        $product = $this->product(10000);
        $sale = $this->sale(20, [$product]);
        $this->add($product);

        $sale->delete();

        $this->assertSame(10000.0, Cart::firstOrFail()->total);
        $this->assertNull(CartItem::firstOrFail()->flash_sale_id);
    }

    // ================================================================
    // Pas de cumul avec les offres par lot ni les codes promo
    // ================================================================

    public function test_flash_items_do_not_count_toward_lot_offers(): void
    {
        Promotion::create([
            'name' => '3 pour 10 000 F', 'lot_qty' => 3, 'lot_price' => 10000, 'is_active' => true,
        ])->categories()->attach($this->category->id);

        $flash = $this->product(5000, ['name' => 'Basket flash']);
        $normal = $this->product(5000, ['name' => 'Basket normale']);
        $this->sale(20, [$flash]);

        $this->add($flash, 3);
        $this->add($normal, 3);

        $cart = Cart::firstOrFail();
        // 3 × 4 000 (flash, sans lot) + un lot de 3 normales à 10 000
        $this->assertSame(22000.0, $cart->payable_subtotal);
        $this->assertSame(5000.0, $cart->bundle_discount);
    }

    public function test_coupons_do_not_apply_to_flash_items(): void
    {
        Coupon::create([
            'code' => 'MOINS10', 'name' => '10 %', 'type' => 'percentage', 'value' => 10,
            'is_active' => true, 'usage_count' => 0,
        ]);
        $flash = $this->product(10000, ['name' => 'Basket flash']);
        $normal = $this->product(5000, ['name' => 'Casquette']);
        $this->sale(20, [$flash]);

        $this->add($flash);
        $this->add($normal);
        $this->post('/panier/coupon', ['coupon_code' => 'MOINS10']);

        $cart = Cart::firstOrFail();
        $this->assertSame(500.0, $cart->discount_amount);
        $this->assertSame(12500.0, $cart->total);
    }

    // ================================================================
    // Affichage dans le panier
    // ================================================================

    public function test_the_cart_page_flags_flash_lines_with_the_normal_price(): void
    {
        $product = $this->product(10000);
        $this->sale(20, [$product]);
        $this->add($product);

        $this->get('/panier')->assertInertia(fn (Assert $page) => $page
            ->where('cart.items.0.flash_sale', 'Flash -20 %')
            ->where('cart.items.0.regular_price', fn ($price) => (float) $price === 10000.0)
            ->where('cart.total', fn ($total) => (float) $total === 8000.0));
    }
}
