<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromotionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'category_ids' => [$this->tshirts->id],
            'is_active' => true,
        ], $overrides);
    }

    public function test_index_is_reachable(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.promotions.index'))
            ->assertOk();
    }

    public function test_store_creates_the_promotion_with_its_categories(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload())
            ->assertRedirect();

        $promotion = Promotion::firstOrFail();

        $this->assertSame('3 t-shirts pour 10 000 F', $promotion->name);
        $this->assertTrue($promotion->categories->contains('id', $this->tshirts->id));
    }

    public function test_store_rejects_lot_qty_below_two(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['lot_qty' => 1]))
            ->assertSessionHasErrors('lot_qty');
    }

    public function test_store_rejects_inverted_price_band(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['price_min' => 5000, 'price_max' => 4000]))
            ->assertSessionHasErrors('price_max');
    }

    public function test_store_rejects_promotion_without_any_target(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['category_ids' => [], 'product_ids' => []]))
            ->assertSessionHasErrors('category_ids');
    }

    public function test_update_replaces_the_categories(): void
    {
        $polos = Category::create(['name' => 'Polos', 'slug' => 'polos']);
        $promotion = Promotion::create($this->payloadForModel());
        $promotion->categories()->attach($this->tshirts->id);

        $this->actingAs($this->admin)
            ->put(route('admin.promotions.update', $promotion), $this->payload(['category_ids' => [$polos->id]]))
            ->assertRedirect();

        $promotion->refresh()->load('categories');

        $this->assertTrue($promotion->categories->contains('id', $polos->id));
        $this->assertFalse($promotion->categories->contains('id', $this->tshirts->id));
    }

    public function test_destroy_removes_the_promotion(): void
    {
        $promotion = Promotion::create($this->payloadForModel());

        $this->actingAs($this->admin)
            ->delete(route('admin.promotions.destroy', $promotion))
            ->assertRedirect();

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_guest_is_rejected(): void
    {
        $this->get(route('admin.promotions.index'))->assertRedirect();
    }

    // ================================================================
    // marginPreview()
    // ================================================================

    private function productAt(float $sale, ?float $purchase, ?Category $category = null): \App\Models\Product
    {
        return \App\Models\Product::create([
            'name' => 'T-shirt '.fake()->unique()->numerify('###'),
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $sale,
            'purchase_price' => $purchase ?? 0,
            'status' => 'active',
            'category_id' => ($category ?? $this->tshirts)->id,
        ]);
    }

    public function test_margin_preview_counts_eligible_products(): void
    {
        $this->productAt(4000, 2500);
        $this->productAt(4200, 2600);
        $this->productAt(5000, 3000);   // hors fourchette

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min' => 4000,
            'price_max' => 4499,
            'lot_qty' => 3,
            'lot_price' => 10000,
        ]);

        $response->assertOk()->assertJsonPath('eligible_count', 2);
    }

    public function test_margin_preview_flags_a_lot_price_below_cost(): void
    {
        $this->productAt(4000, 3800);

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min' => 4000,
            'price_max' => 4499,
            'lot_qty' => 3,
            'lot_price' => 10000,   // 3 333 / unité contre 3 800 de coût
        ]);

        $response->assertOk()->assertJsonPath('below_cost', true);
    }

    public function test_margin_preview_separates_products_without_purchase_price(): void
    {
        $this->productAt(4000, 2500);
        $this->productAt(4100, null);

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min' => 4000,
            'price_max' => 4499,
            'lot_qty' => 3,
            'lot_price' => 10000,
        ]);

        $response->assertOk()->assertJsonPath('without_purchase_price', 1);
    }

    public function test_margin_preview_returns_zero_when_nothing_matches(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min' => 4000,
            'price_max' => 4499,
            'lot_qty' => 3,
            'lot_price' => 10000,
        ]);

        $response->assertOk()->assertJsonPath('eligible_count', 0);
    }

    private function payloadForModel(): array
    {
        return [
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ];
    }
}
