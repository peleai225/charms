<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportBulkPricingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_writes_nothing(): void
    {
        Product::create([
            'name' => 'T-shirt',
            'sku' => 'SKU-TS1',
            'sale_price' => 4000,
            'status' => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules --dry-run')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_product_rule_becomes_an_inactive_promotion(): void
    {
        $product = Product::create([
            'name' => 'T-shirt',
            'sku' => 'SKU-TS2',
            'sale_price' => 4000,
            'status' => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $promotion = Promotion::firstOrFail();

        $this->assertSame(3, $promotion->lot_qty);
        $this->assertSame('9999.00', $promotion->lot_price);
        $this->assertFalse($promotion->is_active);
        $this->assertTrue($promotion->products->contains('id', $product->id));
    }

    public function test_category_rule_creates_one_promotion_per_price_band(): void
    {
        $category = Category::create([
            'name' => 'T-shirts',
            'slug' => 't-shirts',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        Product::create(['name' => 'A', 'sku' => 'SKU-A', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);
        Product::create(['name' => 'B', 'sku' => 'SKU-B', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);
        Product::create(['name' => 'C', 'sku' => 'SKU-C', 'sale_price' => 5000, 'status' => 'active', 'category_id' => $category->id]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        // Un palier 4 000 et un palier 5 000
        $this->assertDatabaseCount('promotions', 2);

        $bands = Promotion::orderBy('price_min')->pluck('price_min')->map(fn ($v) => (float) $v)->all();
        $this->assertSame([4000.0, 5000.0], $bands);
    }

    public function test_category_band_spans_five_hundred_francs(): void
    {
        $category = Category::create([
            'name' => 'T-shirts',
            'slug' => 't-shirts',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        Product::create(['name' => 'A', 'sku' => 'SKU-D', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $promotion = Promotion::firstOrFail();

        $this->assertSame('4000.00', $promotion->price_min);
        $this->assertSame('4499.00', $promotion->price_max);
    }

    public function test_running_twice_does_not_duplicate(): void
    {
        Product::create([
            'name' => 'T-shirt',
            'sku' => 'SKU-TS3',
            'sale_price' => 4000,
            'status' => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();
        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 1);
    }

    public function test_nothing_to_import_is_not_an_error(): void
    {
        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 0);
    }
}
