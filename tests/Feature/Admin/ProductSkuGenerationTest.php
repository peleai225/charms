<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductSkuGenerationTest extends TestCase
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

    private function baseProductPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Produit Test',
            'purchase_price' => 1000,
            'sale_price' => 2000,
            'tax_rate' => 20,
            'stock_quantity' => 10,
            'stock_alert_threshold' => 5,
            'status' => 'active',
        ], $overrides);
    }

    public function test_store_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'category_id' => $category->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-ELEC-000001']);
    }

    public function test_store_with_whitespace_only_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'category_id' => $category->id,
            'sku' => '   ',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-ELEC-000001']);
    }

    public function test_store_without_category_uses_div_prefix(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-DIV-000001']);
    }

    public function test_store_with_manual_sku_is_preserved(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'sku' => 'FOURNISSEUR-XYZ-42',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'FOURNISSEUR-XYZ-42']);
    }

    public function test_store_with_duplicate_manual_sku_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        Product::create(['name' => 'Existant', 'sale_price' => 1000, 'sku' => 'LGB-DIV-000001']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'sku' => 'LGB-DIV-000001',
        ]));

        $response->assertSessionHasErrors('sku');
        $this->assertSame(1, Product::where('sku', 'LGB-DIV-000001')->count());
    }

    public function test_update_does_not_change_sku_when_only_name_changes(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'Ancien Nom', 'sale_price' => 2000, 'sku' => 'LGB-DIV-000001']);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), $this->baseProductPayload([
            'name' => 'Nouveau Nom',
            'sku' => 'LGB-DIV-000001',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nouveau Nom', 'sku' => 'LGB-DIV-000001']);
    }

    public function test_csv_import_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();

        $csv = "sku;name;sale_price\n;Produit Import;1500\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.import-export.import-products'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Import', 'sku' => 'LGB-DIV-000001']);
    }
}
