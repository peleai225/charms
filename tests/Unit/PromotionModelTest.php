<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    private function promotion(array $attributes = []): Promotion
    {
        return Promotion::create(array_merge([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ], $attributes));
    }

    // ================================================================
    // matchesPrice()
    // ================================================================

    public function test_matches_price_inside_band(): void
    {
        $this->assertTrue($this->promotion()->matchesPrice(4200));
    }

    public function test_matches_price_on_exact_bounds(): void
    {
        $promotion = $this->promotion();

        $this->assertTrue($promotion->matchesPrice(4000));
        $this->assertTrue($promotion->matchesPrice(4499));
    }

    public function test_rejects_price_outside_band(): void
    {
        $promotion = $this->promotion();

        $this->assertFalse($promotion->matchesPrice(3999));
        $this->assertFalse($promotion->matchesPrice(4500));
    }

    public function test_null_bounds_accept_any_price(): void
    {
        $promotion = $this->promotion(['price_min' => null, 'price_max' => null]);

        $this->assertTrue($promotion->matchesPrice(1));
        $this->assertTrue($promotion->matchesPrice(999999));
    }

    // ================================================================
    // eligibleCategoryIds()
    // ================================================================

    public function test_eligible_category_ids_include_descendants_by_default(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $child = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts', 'parent_id' => $parent->id]);

        $promotion = $this->promotion();
        $promotion->categories()->attach($parent->id);

        $ids = $promotion->fresh()->eligibleCategoryIds();

        $this->assertContains($parent->id, $ids);
        $this->assertContains($child->id, $ids);
    }

    public function test_eligible_category_ids_exclude_descendants_when_disabled(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $child = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts', 'parent_id' => $parent->id]);

        $promotion = $this->promotion(['include_descendants' => false]);
        $promotion->categories()->attach($parent->id);

        $ids = $promotion->fresh()->eligibleCategoryIds();

        $this->assertContains($parent->id, $ids);
        $this->assertNotContains($child->id, $ids);
    }

    // ================================================================
    // scopeValid() et statut
    // ================================================================

    public function test_valid_scope_excludes_inactive(): void
    {
        $this->promotion(['is_active' => false]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_excludes_expired(): void
    {
        $this->promotion(['expires_at' => now()->subDay()]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_excludes_not_yet_started(): void
    {
        $this->promotion(['starts_at' => now()->addDay()]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_includes_open_window(): void
    {
        $this->promotion(['starts_at' => now()->subDay(), 'expires_at' => now()->addDay()]);

        $this->assertCount(1, Promotion::valid()->get());
    }

    public function test_status_reflects_lifecycle(): void
    {
        $this->assertSame('active', $this->promotion()->status);
        $this->assertSame('inactive', $this->promotion(['is_active' => false])->status);
        $this->assertSame('expired', $this->promotion(['expires_at' => now()->subDay()])->status);
        $this->assertSame('scheduled', $this->promotion(['starts_at' => now()->addDay()])->status);
    }

    // ================================================================
    // scopeResolutionOrder()
    // ================================================================

    public function test_resolution_order_sorts_by_priority_then_lot_price(): void
    {
        $low = $this->promotion(['name' => 'basse',    'priority' => 0, 'lot_price' => 9000]);
        $high = $this->promotion(['name' => 'haute',    'priority' => 5, 'lot_price' => 11000]);
        $cheapest = $this->promotion(['name' => 'pas cher', 'priority' => 0, 'lot_price' => 8000]);

        $names = Promotion::resolutionOrder()->pluck('name')->all();

        $this->assertSame(['haute', 'pas cher', 'basse'], $names);
    }
}
