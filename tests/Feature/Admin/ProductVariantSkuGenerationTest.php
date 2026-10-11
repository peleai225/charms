<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductVariantSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('secret123'),
        ]);
    }

    private function makeProductWithColorAttribute(): array
    {
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);

        return [$product, $attribute, $value];
    }

    public function test_store_variant_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        [$product, $attribute, $value] = $this->makeProductWithColorAttribute();

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.store', $product), [
            'attributes' => [$attribute->id => $value->id],
            'stock_quantity' => 5,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
    }

    public function test_store_variant_with_manual_sku_is_preserved(): void
    {
        $admin = $this->makeAdmin();
        [$product, $attribute, $value] = $this->makeProductWithColorAttribute();

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.store', $product), [
            'attributes' => [$attribute->id => $value->id],
            'stock_quantity' => 5,
            'sku' => 'FOURNISSEUR-ROUGE',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'FOURNISSEUR-ROUGE']);
    }

    public function test_bulk_store_variants_generates_sku_for_blank_rows_and_keeps_explicit_ones(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);
        $bleu = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Bleu']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => '', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
                ['sku' => 'FOURNISSEUR-BLEU', 'stock_quantity' => 3, 'attributes' => [$attribute->id => $bleu->id]],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(2, $product->variants()->count());
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'FOURNISSEUR-BLEU']);
    }

    public function test_bulk_store_variants_with_all_blank_skus_generates_distinct_ones(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);
        $bleu = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Bleu']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => '', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
                ['sku' => '', 'stock_quantity' => 3, 'attributes' => [$attribute->id => $bleu->id]],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(2, $response->json('created'));
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-02']);
    }

    public function test_bulk_store_variants_still_skips_explicit_duplicate_sku(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        ProductVariant::create(['product_id' => $product->id, 'sku' => 'DEJA-PRIS']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => 'DEJA-PRIS', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, ProductVariant::where('sku', 'DEJA-PRIS')->count());
        $this->assertSame(0, $response->json('created'));
        $this->assertSame(1, $response->json('skipped'));
    }
}
