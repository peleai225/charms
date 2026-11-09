<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\Storefront\PopularCategories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * « Nos univers populaires » sur l'accueil : les catégories cochées « Mise en avant » dans l'admin,
 * sinon les catégories principales qui ont des produits. Jamais de tuile vers une catégorie vide.
 */
class PopularCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name, array $attributes = []): Category
    {
        return Category::create(array_merge(['name' => $name, 'is_active' => true], $attributes));
    }

    private function products(Category $category, int $count = 1, string $status = 'active'): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Product::create([
                'name' => "{$category->name} {$status} {$i}",
                'sale_price' => 5000,
                'status' => $status,
                'category_id' => $category->id,
            ]);
        }
    }

    private function popular(): array
    {
        return app(PopularCategories::class)->forHome();
    }

    public function test_a_featured_category_comes_with_its_icon_and_the_products_of_its_subcategories(): void
    {
        $mode = $this->category('Mode', ['is_featured' => true, 'icon' => 'shirt', 'image' => 'categories/mode.png']);
        $femme = $this->category('Femme', ['parent_id' => $mode->id]);
        $this->products($mode, 1);
        $this->products($femme, 2);
        $this->products($femme, 1, 'draft');

        $this->assertSame([[
            'id' => $mode->id,
            'name' => 'Mode',
            'slug' => 'mode',
            'image' => 'categories/mode.png',
            'icon' => 'shirt',
            'products_count' => 3,
        ]], $this->popular());
    }

    public function test_featured_subcategories_are_shown_too_in_the_admin_order(): void
    {
        $electronique = $this->category('Électronique', ['is_featured' => true, 'order' => 2]);
        $smartphones = $this->category('Smartphones', ['parent_id' => $electronique->id, 'is_featured' => true, 'order' => 1]);
        $this->products($smartphones, 1);

        $this->assertSame(['Smartphones', 'Électronique'], array_column($this->popular(), 'name'));
    }

    public function test_featured_categories_without_products_or_inactive_are_left_out(): void
    {
        $vide = $this->category('Vide', ['is_featured' => true]);
        $cachee = $this->category('Cachée', ['is_featured' => true, 'is_active' => false]);
        $maison = $this->category('Maison', ['is_featured' => true]);
        $this->products($cachee, 2);
        $this->products($maison, 1);
        $this->products($vide, 1, 'draft');

        $this->assertSame(['Maison'], array_column($this->popular(), 'name'));
    }

    public function test_without_featured_categories_the_main_categories_with_products_are_shown(): void
    {
        $mode = $this->category('Mode', ['order' => 2]);
        $enfant = $this->category('Enfant', ['parent_id' => $mode->id]);
        $beaute = $this->category('Beauté', ['order' => 1]);
        $this->category('Sport', ['order' => 3]);
        $inactive = $this->category('Archives', ['order' => 0, 'is_active' => false]);
        $this->products($enfant, 2);
        $this->products($beaute, 1);
        $this->products($inactive, 1);

        $popular = $this->popular();

        $this->assertSame(['Beauté', 'Mode'], array_column($popular, 'name'));
        $this->assertSame([1, 2], array_column($popular, 'products_count'));
    }

    public function test_the_main_categories_are_also_shown_when_every_featured_one_is_empty(): void
    {
        $this->category('Vide', ['is_featured' => true]);
        $maison = $this->category('Maison');
        $this->products($maison, 1);

        $this->assertSame(['Maison'], array_column($this->popular(), 'name'));
    }

    public function test_at_most_twelve_categories_are_shown(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $this->products($this->category("Univers {$i}", ['is_featured' => true, 'order' => $i]), 1);
        }

        $popular = $this->popular();

        $this->assertCount(12, $popular);
        $this->assertSame('Univers 1', $popular[0]['name']);
        $this->assertSame('Univers 12', $popular[11]['name']);
    }

    public function test_nothing_is_shown_when_the_shop_has_no_product(): void
    {
        $this->category('Mode', ['is_featured' => true]);
        $this->category('Maison');

        $this->assertSame([], $this->popular());
    }

    public function test_the_home_page_receives_the_popular_categories(): void
    {
        $mode = $this->category('Mode', ['is_featured' => true, 'icon' => 'shirt']);
        $this->products($mode, 2);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('popular_categories', 1)
            ->where('popular_categories.0.name', 'Mode')
            ->where('popular_categories.0.icon', 'shirt')
            ->where('popular_categories.0.products_count', 2));
    }
}
