<?php

namespace Tests\Feature;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminFlashSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->product = $this->product('Basket', 10000);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function product(string $name, float $price, string $status = 'active'): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => $status,
            'stock_quantity' => 10,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ventes flash du week-end',
            'discount_percent' => 20,
            'starts_at' => '2026-11-10T08:00',
            'ends_at' => '2026-11-12T20:00',
            'is_active' => true,
            'product_ids' => [$this->product->id],
        ], $overrides);
    }

    private function sale(array $attributes = []): FlashSale
    {
        $sale = FlashSale::create(array_merge([
            'name' => 'Vente',
            'discount_percent' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'is_active' => true,
        ], $attributes));
        $sale->products()->attach($this->product->id);

        return $sale;
    }

    // ================================================================
    // Liste
    // ================================================================

    public function test_index_lists_sales_with_their_status_and_product_count(): void
    {
        $this->sale(['name' => 'En cours']);
        $this->sale(['name' => 'Programmée', 'starts_at' => now()->addDay(), 'ends_at' => now()->addDays(2)]);
        $this->sale(['name' => 'Terminée', 'starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);
        $this->sale(['name' => 'Désactivée', 'is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.flash-sales.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/FlashSales/Index')
                ->has('sales.data', 4)
                ->where('sales.data', fn ($rows) => collect($rows)->pluck('status', 'name')->sortKeys()->all() === [
                    'Désactivée' => 'inactive',
                    'En cours' => 'running',
                    'Programmée' => 'scheduled',
                    'Terminée' => 'ended',
                ])
                ->where('sales.data.0.products_count', 1));
    }

    public function test_staff_cannot_manage_flash_sales(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.flash-sales.index'))
            ->assertForbidden();
    }

    // ================================================================
    // Création / modification / suppression
    // ================================================================

    public function test_store_creates_the_sale_with_its_products(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload())
            ->assertRedirect(route('admin.flash-sales.index'));

        $sale = FlashSale::firstOrFail();
        $this->assertSame('Ventes flash du week-end', $sale->name);
        $this->assertSame(20, $sale->discount_percent);
        $this->assertSame('2026-11-12 20:00:00', $sale->ends_at->format('Y-m-d H:i:s'));
        $this->assertTrue($sale->is_active);
        $this->assertSame([$this->product->id], $sale->products->pluck('id')->all());
    }

    public function test_store_rejects_an_end_before_the_start(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['ends_at' => '2026-11-10T07:00']))
            ->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('flash_sales', 0);
    }

    public function test_store_rejects_a_discount_outside_1_to_90(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['discount_percent' => 0]))
            ->assertSessionHasErrors('discount_percent');

        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['discount_percent' => 91]))
            ->assertSessionHasErrors('discount_percent');

        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['discount_percent' => 12.5]))
            ->assertSessionHasErrors('discount_percent');
    }

    public function test_store_requires_at_least_one_existing_product(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['product_ids' => []]))
            ->assertSessionHasErrors('product_ids');

        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['product_ids' => [999999]]))
            ->assertSessionHasErrors('product_ids.0');

        $this->assertDatabaseCount('flash_sales', 0);
    }

    public function test_validation_messages_are_in_french(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.flash-sales.store'), $this->payload(['ends_at' => '2026-11-10T07:00']))
            ->assertSessionHasErrors(['ends_at' => 'La fin doit être après le début.']);
    }

    public function test_edit_prefills_the_form(): void
    {
        $sale = $this->sale(['starts_at' => '2026-11-10 08:30:00', 'ends_at' => '2026-11-11 21:00:00']);

        $this->actingAs($this->admin)
            ->get(route('admin.flash-sales.edit', $sale))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/FlashSales/Edit')
                ->where('sale.starts_at', '2026-11-10T08:30')
                ->where('sale.ends_at', '2026-11-11T21:00')
                ->where('sale.products.0.id', $this->product->id)
                ->where('sale.products.0.name', 'Basket'));
    }

    public function test_update_replaces_the_fields_and_products(): void
    {
        $sale = $this->sale();
        $other = $this->product('Sac', 20000);

        $this->actingAs($this->admin)
            ->put(route('admin.flash-sales.update', $sale), $this->payload([
                'name' => 'Nouveau nom',
                'discount_percent' => 35,
                'is_active' => false,
                'product_ids' => [$other->id],
            ]))
            ->assertRedirect(route('admin.flash-sales.index'));

        $sale->refresh();
        $this->assertSame('Nouveau nom', $sale->name);
        $this->assertSame(35, $sale->discount_percent);
        $this->assertFalse($sale->is_active);
        $this->assertSame([$other->id], $sale->products->pluck('id')->all());
    }

    public function test_destroy_deletes_the_sale(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->admin)
            ->delete(route('admin.flash-sales.destroy', $sale))
            ->assertRedirect(route('admin.flash-sales.index'));

        $this->assertDatabaseCount('flash_sales', 0);
        $this->assertDatabaseCount('flash_sale_product', 0);
        $this->assertDatabaseHas('products', ['id' => $this->product->id]);
    }

    // ================================================================
    // Recherche de produits pour le formulaire
    // ================================================================

    public function test_product_search_returns_matching_active_products_only(): void
    {
        $this->product('Basket montante', 15000);
        $this->product('Basket archivée', 9000, 'draft');
        $this->product('Sac à dos', 12000);

        $this->actingAs($this->admin)
            ->getJson(route('admin.flash-sales.products', ['q' => 'bask']))
            ->assertOk()
            ->assertJsonCount(2, 'products')
            ->assertJsonFragment(['name' => 'Basket montante', 'price' => 15000.0])
            ->assertJsonMissing(['name' => 'Basket archivée']);
    }
}
