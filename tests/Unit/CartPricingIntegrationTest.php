<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPricingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);

        Promotion::create([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ])->categories()->attach($this->tshirts->id);
    }

    private function product(float $price = 4000, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => 'active',
            'category_id' => $this->tshirts->id,
        ]);
    }

    // ================================================================
    // Le prix catalogue survit à l'application de l'offre
    // ================================================================

    public function test_unit_price_is_never_mutated(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame('4000.00', $cart->items()->first()->unit_price);
    }

    public function test_subtotal_is_the_catalog_total(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(12000.0, $cart->fresh()->subtotal);
    }

    public function test_bundle_discount_is_exposed(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(2000.0, $cart->fresh()->bundle_discount);
    }

    public function test_payable_subtotal_is_net_of_bundle(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->payable_subtotal);
    }

    public function test_total_matches_the_lot_price(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    // ================================================================
    // Le prix se recalcule quand le panier change
    // ================================================================

    public function test_pricing_refreshes_after_quantity_change(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($this->product(), 2);

        $this->assertSame(8000.0, $cart->fresh()->total);

        $cart->updateItemQuantity($item->id, 3);

        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    public function test_pricing_refreshes_after_removal(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->total);

        $cart->updateItemQuantity($item->id, 2);

        $this->assertSame(8000.0, $cart->fresh()->total);
    }

    public function test_adding_the_same_product_twice_aggregates_quantity(): void
    {
        $product = $this->product();

        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($product, 1);
        $cart->addItem($product, 2);

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    // ================================================================
    // addItem retient le prix de la variante
    // ================================================================

    public function test_add_item_stores_the_variant_price(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VAR-L',
            'name' => 'L',
            'sale_price' => 4200,
        ]);

        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($product, 1, $variant);

        $this->assertSame('4200.00', $item->unit_price);
    }

    public function test_variant_priced_items_enter_the_lot(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VAR-M',
            'name' => 'M',
            'sale_price' => 4200,
        ]);

        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($product, 2, $variant);
        $cart->addItem($product, 1);

        // 4200 + 4200 + 4000 = 12 400 au catalogue, lot à 10 000
        $this->assertSame(12400.0, $cart->fresh()->subtotal);
        $this->assertSame(10000.0, $cart->fresh()->total);
    }
}
