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
