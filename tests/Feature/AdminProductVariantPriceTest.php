<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductVariantPriceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function productWithVariants(array $variantPrices): Product
    {
        $product = Product::create([
            'name' => 'T-shirt',
            'sale_price' => 5000,
            'has_variants' => true,
            'status' => 'active',
        ]);

        foreach ($variantPrices as $i => $price) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => "Variante {$i}",
                'sku' => "TS-{$product->id}-{$i}",
                'sale_price' => $price,
                'stock_quantity' => 10,
                'is_active' => true,
            ]);
        }

        return $product->fresh();
    }

    public function test_admin_can_change_the_price_of_a_single_variant(): void
    {
        $product = $this->productWithVariants([1000, 2000]);
        $variant = $product->variants->first();

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.products.variants.update', [$product, $variant]), [
                'sale_price' => 1500,
            ]);

        $response->assertRedirect();
        $this->assertEquals(1500, $variant->fresh()->sale_price);
    }

    public function test_admin_can_apply_the_same_price_to_all_variants_at_once(): void
    {
        $product = $this->productWithVariants([1000, 2000, 3000]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.products.variants.bulk-price', $product), [
                'sale_price' => 4200,
            ]);

        $response->assertRedirect();

        foreach ($product->variants as $variant) {
            $this->assertEquals(4200, $variant->fresh()->sale_price);
        }
    }

    public function test_bulk_price_update_rejects_a_negative_price(): void
    {
        $product = $this->productWithVariants([1000, 2000]);
        $originalPrices = $product->variants->pluck('sale_price', 'id');

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.products.variants.bulk-price', $product), [
                'sale_price' => -100,
            ]);

        $response->assertSessionHasErrors('sale_price');

        foreach ($product->variants as $variant) {
            $this->assertEquals($originalPrices[$variant->id], $variant->fresh()->sale_price);
        }
    }

    public function test_bulk_price_update_only_affects_variants_of_the_targeted_product(): void
    {
        $product = $this->productWithVariants([1000, 2000]);
        $otherProduct = $this->productWithVariants([500]);

        $this->actingAs($this->admin)
            ->patch(route('admin.products.variants.bulk-price', $product), [
                'sale_price' => 9999,
            ]);

        $this->assertEquals(500, $otherProduct->variants->first()->fresh()->sale_price);
    }
}
