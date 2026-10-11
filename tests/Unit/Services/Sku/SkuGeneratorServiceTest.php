<?php

namespace Tests\Unit\Services\Sku;

use App\Models\Category;
use App\Models\SkuCounter;
use App\Services\Sku\SkuGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SkuGeneratorService
    {
        return app(SkuGeneratorService::class);
    }

    public function test_generates_product_sku_using_category_code(): void
    {
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-ELEC-000001', $sku);
    }

    public function test_product_sku_sequence_increments_per_prefix(): void
    {
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $first = $this->service()->generateForProduct($category);
        $second = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-ELEC-000001', $first);
        $this->assertSame('LGB-ELEC-000002', $second);
    }

    public function test_derives_prefix_from_slug_when_category_code_is_empty(): void
    {
        $category = Category::create(['name' => 'Mode Homme', 'slug' => 'mode-homme', 'code' => null]);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-MODE-000001', $sku);
    }

    public function test_falls_back_to_gen_prefix_when_slug_has_no_letters(): void
    {
        $category = Category::create(['name' => '2026', 'slug' => '2026', 'code' => null]);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-GEN-000001', $sku);
    }

    public function test_uses_div_prefix_when_no_category(): void
    {
        $sku = $this->service()->generateForProduct(null);

        $this->assertSame('LGB-DIV-000001', $sku);
    }

    public function test_two_categories_sharing_the_same_code_never_collide(): void
    {
        $catA = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);
        $catB = Category::create(['name' => 'Électroménager', 'slug' => 'electromenager', 'code' => 'ELEC']);

        $fromA = $this->service()->generateForProduct($catA);
        $fromB = $this->service()->generateForProduct($catB);

        $this->assertNotSame($fromA, $fromB);
        $this->assertSame('LGB-ELEC-000001', $fromA);
        $this->assertSame('LGB-ELEC-000002', $fromB);
    }

    public function test_counter_is_created_on_first_use(): void
    {
        $this->assertSame(0, SkuCounter::count());

        $category = Category::create(['name' => 'Mode', 'slug' => 'mode', 'code' => 'MOD']);
        $this->service()->generateForProduct($category);

        $this->assertSame(1, SkuCounter::count());
        $this->assertSame(2, SkuCounter::where('scope_key', 'prefix:MOD')->first()->next_value);
    }
}
