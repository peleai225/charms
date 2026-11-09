<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Caisse au pistolet : scans rapides sans perte d'article, et produits à tailles / couleurs
 * jamais ajoutés sans leur variante (sinon le stock du mauvais article serait décompté).
 */
class PosScannerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private ProductVariant $size40;

    private ProductVariant $size44;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures', 'is_active' => true]);
        $this->product = Product::create([
            'name' => 'Basket', 'sku' => 'BASKET', 'barcode' => '3000000000001', 'sale_price' => 10000,
            'status' => 'active', 'category_id' => $category->id, 'stock_quantity' => 0,
        ]);
        $this->size40 = ProductVariant::create([
            'product_id' => $this->product->id, 'name' => '40', 'sku' => 'BASKET-40', 'barcode' => '3000000000040',
            'sale_price' => null, 'stock_quantity' => 4, 'is_active' => true,
        ]);
        $this->size44 = ProductVariant::create([
            'product_id' => $this->product->id, 'name' => '44', 'sku' => 'BASKET-44', 'barcode' => '3000000000044',
            'sale_price' => 12000, 'stock_quantity' => 2, 'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $this->product->id, 'name' => '46', 'sku' => 'BASKET-46',
            'sale_price' => null, 'stock_quantity' => 1, 'is_active' => false,
        ]);
    }

    public function test_cart_requests_of_the_till_wait_for_each_other_instead_of_overwriting_the_cart(): void
    {
        // Deux scans coup sur coup : sans verrou de session, la 2e requête écrase le panier de la 1re
        foreach (['admin.scanner.cart.add', 'admin.scanner.cart.update', 'admin.scanner.cart.remove', 'admin.scanner.cart.clear', 'admin.scanner.checkout'] as $name) {
            $this->assertNotNull(Route::getRoutes()->getByName($name)?->locksFor(), "{$name} doit verrouiller la session");
        }
    }

    public function test_scanning_the_label_of_a_product_with_sizes_lists_its_sizes(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.scanner.scan'), ['code' => '3000000000001'])
            ->assertOk()
            ->assertJsonPath('data.has_variants', true)
            ->assertJsonCount(2, 'data.variants')
            ->assertJsonPath('data.variants.0.variant_id', $this->size40->id)
            ->assertJsonPath('data.variants.0.name', '40')
            ->assertJsonPath('data.variants.0.price', 10000)
            ->assertJsonPath('data.variants.1.price', 12000)
            ->assertJsonPath('data.variants.1.stock', 2);
    }

    public function test_a_product_with_sizes_is_not_added_without_its_size(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.scanner.cart.add'), ['product_id' => $this->product->id])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonCount(2, 'variants');

        $this->actingAs($this->admin)->getJson(route('admin.scanner.cart'))->assertJsonPath('count', 0);
    }

    public function test_choosing_the_size_adds_that_size(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.scanner.cart.add'), [
            'product_id' => $this->product->id, 'variant_id' => $this->size44->id,
        ])->assertOk()
            ->assertJsonPath('cart.items.0.variant_id', $this->size44->id)
            ->assertJsonPath('cart.total', 12000);
    }

    public function test_scanning_the_label_of_a_size_still_adds_it_directly(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.scanner.scan'), ['code' => '3000000000040'])
            ->assertOk()
            ->assertJsonPath('type', 'variant')
            ->assertJsonPath('data.variant_id', $this->size40->id);
    }
}
