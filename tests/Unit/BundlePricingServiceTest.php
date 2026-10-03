<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Services\BundlePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Expose les méthodes protégées du moteur pour les tester isolément.
 */
class ProbedBundlePricingService extends BundlePricingService
{
    public function probePool(Promotion $promotion, iterable $items, array $consumed = []): array
    {
        return $this->pool($promotion, $items, $consumed);
    }
}

class BundlePricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);
    }

    // ================================================================
    // Fabriques locales — aucune factory n'existe pour ces modèles
    // ================================================================

    private function product(float $price, ?Category $category = null, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => 'active',
            'category_id' => $category?->id ?? $this->tshirts->id,
        ]);
    }

    private function cart(): Cart
    {
        return Cart::create(['session_id' => 'test-session']);
    }

    private function addItem(Cart $cart, Product $product, int $quantity, ?float $unitPrice = null, ?ProductVariant $variant = null): CartItem
    {
        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice ?? $product->sale_price,
        ]);
    }

    private function promotion(array $attributes = [], ?Category $category = null): Promotion
    {
        $promotion = Promotion::create(array_merge([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ], $attributes));

        $promotion->categories()->attach(($category ?? $this->tshirts)->id);

        return $promotion->fresh(['categories', 'products']);
    }

    private function service(): ProbedBundlePricingService
    {
        return new ProbedBundlePricingService;
    }

    private function items(Cart $cart)
    {
        return $cart->items()->with(['product.category', 'variant'])->get();
    }

    // ================================================================
    // pool() — éligibilité
    // ================================================================

    public function test_pool_expands_quantity_into_units(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(3, $units);
        $this->assertSame(4000.0, $units[0]['price']);
    }

    public function test_pool_sorts_units_by_price_descending(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 1);
        $this->addItem($cart, $this->product(4499), 1);
        $this->addItem($cart, $this->product(4200), 1);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertSame([4499.0, 4200.0, 4000.0], array_column($units, 'price'));
    }

    public function test_pool_excludes_price_outside_band(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 2);
        $this->addItem($cart, $this->product(5000), 2);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(2, $units);
        $this->assertSame([4000.0, 4000.0], array_column($units, 'price'));
    }

    public function test_pool_excludes_other_category(): void
    {
        $polos = Category::create(['name' => 'Polos', 'slug' => 'polos']);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000, $polos, 'Polo'), 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(0, $units);
    }

    public function test_pool_includes_descendant_category(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $this->tshirts->update(['parent_id' => $parent->id]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $promotion = $this->promotion([], $parent);
        $units = $this->service()->probePool($promotion, $this->items($cart));

        $this->assertCount(3, $units);
    }

    /** Review Focus nº2 — products.category_id est nullable. */
    public function test_pool_excludes_product_without_category(): void
    {
        $cart = $this->cart();
        $orphan = Product::create([
            'name' => 'Article sans catégorie',
            'sku' => 'SKU-ORPHAN',
            'sale_price' => 4000,
            'status' => 'active',
            'category_id' => null,
        ]);
        $this->addItem($cart, $orphan, 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(0, $units);
    }

    public function test_pool_includes_product_listed_explicitly(): void
    {
        $polos = Category::create(['name' => 'Polos', 'slug' => 'polos']);
        $product = $this->product(4000, $polos, 'Polo listé');

        $cart = $this->cart();
        $this->addItem($cart, $product, 3);

        $promotion = $this->promotion();
        $promotion->products()->attach($product->id);

        $units = $this->service()->probePool($promotion->fresh(['categories', 'products']), $this->items($cart));

        $this->assertCount(3, $units);
    }

    public function test_pool_uses_variant_price_stored_on_the_item(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VAR-L',
            'name' => 'L',
            'sale_price' => 4200,
        ]);

        $cart = $this->cart();
        $this->addItem($cart, $product, 2, 4200, $variant);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertSame([4200.0, 4200.0], array_column($units, 'price'));
    }

    public function test_pool_skips_already_consumed_units(): void
    {
        $cart = $this->cart();
        $item = $this->addItem($cart, $this->product(4000), 5);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart), [$item->id => 3]);

        $this->assertCount(2, $units);
    }

    public function test_pool_is_empty_for_empty_cart(): void
    {
        $units = $this->service()->probePool($this->promotion(), $this->items($this->cart()));

        $this->assertSame([], $units);
    }
}
