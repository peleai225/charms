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

    public function probeDistribute(float $total, array $weights): array
    {
        return $this->distribute($total, $weights);
    }

    public function probeApplyPromotion(Promotion $promotion, array $units): array
    {
        return $this->applyPromotion($promotion, $units);
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

    // ================================================================
    // distribute() — méthode du plus grand reste
    // ================================================================

    public function test_distribute_sums_exactly_to_total(): void
    {
        $shares = $this->service()->probeDistribute(2699, [4499, 4200, 4000]);

        $this->assertSame(2699, array_sum($shares));
    }

    /** Le cas normatif de la spec §4. */
    public function test_distribute_matches_spec_example(): void
    {
        $shares = $this->service()->probeDistribute(2699, [4499, 4200, 4000]);

        $this->assertSame([956, 893, 850], array_values($shares));
    }

    public function test_distribute_returns_only_integers(): void
    {
        $shares = $this->service()->probeDistribute(2000, [4000, 4000, 4000]);

        foreach ($shares as $share) {
            $this->assertIsInt($share);
        }
    }

    public function test_distribute_handles_equal_weights(): void
    {
        $shares = $this->service()->probeDistribute(2000, [4000, 4000, 4000]);

        $this->assertSame(2000, array_sum($shares));
        $this->assertSame([667, 667, 666], array_values($shares));
    }

    public function test_distribute_returns_zeros_for_zero_total(): void
    {
        $shares = $this->service()->probeDistribute(0, [4000, 4000, 4000]);

        $this->assertSame([0, 0, 0], array_values($shares));
    }

    public function test_distribute_returns_zeros_for_zero_weights(): void
    {
        $shares = $this->service()->probeDistribute(500, [0, 0]);

        $this->assertSame([0, 0], array_values($shares));
    }

    // ================================================================
    // applyPromotion() — quantités
    // ================================================================

    private function unitsOf(float $price, int $count): array
    {
        return array_fill(0, $count, ['cart_item_id' => 1, 'price' => $price]);
    }

    public function test_two_units_get_no_discount(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 2));

        $this->assertSame([], $result['discounts']);
        $this->assertSame([], $result['consumed']);
    }

    public function test_three_units_discount_down_to_lot_price(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 3));

        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }

    public function test_five_units_discount_one_lot_only(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 5));

        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }

    public function test_six_units_discount_two_lots(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 6));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    public function test_seven_units_discount_two_lots(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 7));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    public function test_max_lots_per_order_caps_the_discount(): void
    {
        $promotion = $this->promotion(['max_lots_per_order' => 2]);
        $result = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 9));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    /** Review Focus nº5 — lot_qty supérieur au panier. */
    public function test_lot_larger_than_cart_yields_nothing(): void
    {
        $promotion = $this->promotion(['lot_qty' => 5]);
        $result = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 3));

        $this->assertSame([], $result['discounts']);
    }

    // ================================================================
    // applyPromotion() — arithmétique
    // ================================================================

    public function test_lot_price_above_catalog_sum_yields_no_discount(): void
    {
        $promotion = $this->promotion(['price_min' => null, 'price_max' => null, 'lot_price' => 10000]);
        $result = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(3000, 3));

        $this->assertSame(0.0, array_sum($result['discounts']));
    }

    /** Review Focus nº3 — « 3 pour 0 ». */
    public function test_zero_lot_price_discounts_the_whole_lot(): void
    {
        $promotion = $this->promotion(['lot_price' => 0]);
        $result = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 3));

        $this->assertSame(12000.0, array_sum($result['discounts']));
    }

    public function test_discount_aggregates_per_cart_item(): void
    {
        $units = [
            ['cart_item_id' => 7, 'price' => 4000],
            ['cart_item_id' => 7, 'price' => 4000],
            ['cart_item_id' => 9, 'price' => 4000],
        ];

        $result = $this->service()->probeApplyPromotion($this->promotion(), $units);

        $this->assertArrayHasKey(7, $result['discounts']);
        $this->assertArrayHasKey(9, $result['discounts']);
        $this->assertSame(2, $result['consumed'][7]);
        $this->assertSame(1, $result['consumed'][9]);
        $this->assertSame(2000.0, array_sum($result['discounts']));
    }

    /** Review Focus nº4 — même produit, deux variantes, deux lignes de panier. */
    public function test_same_product_in_two_variants_shares_one_pool(): void
    {
        $product = $this->product(4000);

        $medium = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VAR-M',
            'name' => 'M',
            'sale_price' => 4000,
        ]);

        $large = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VAR-L2',
            'name' => 'L',
            'sale_price' => 4000,
        ]);

        $cart = $this->cart();
        $this->addItem($cart, $product, 1, 4000, $medium);
        $this->addItem($cart, $product, 2, 4000, $large);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));
        $result = $this->service()->probeApplyPromotion($this->promotion(), $units);

        $this->assertCount(3, $units);
        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }
}
