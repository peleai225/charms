<?php

namespace Tests\Feature\Front;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create(['name' => 'Électronique', 'slug' => 'electronique']);
    }

    private function product(Category $category, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Produit',
            'category_id' => $category->id,
            'status' => 'active',
            'sale_price' => 1000,
            'stock_quantity' => 10,
        ], $overrides));
    }

    public function test_category_page_filters_products_by_min_price(): void
    {
        $category = $this->category();
        $cheap = $this->product($category, ['name' => 'Pas cher', 'sale_price' => 500]);
        $expensive = $this->product($category, ['name' => 'Cher', 'sale_price' => 5000]);

        $response = $this->get("/categorie/{$category->slug}?".http_build_query(['min_price' => 1000]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.id', $expensive->id)
        );
    }

    public function test_category_page_filters_products_by_max_price(): void
    {
        $category = $this->category();
        $cheap = $this->product($category, ['name' => 'Pas cher', 'sale_price' => 500]);
        $expensive = $this->product($category, ['name' => 'Cher', 'sale_price' => 5000]);

        $response = $this->get("/categorie/{$category->slug}?".http_build_query(['max_price' => 1000]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.id', $cheap->id)
        );
    }

    public function test_category_page_filters_products_on_sale(): void
    {
        $category = $this->category();
        $onSale = $this->product($category, ['name' => 'Promo', 'sale_price' => 500, 'compare_price' => 1000]);
        $fullPrice = $this->product($category, ['name' => 'Plein tarif', 'sale_price' => 1000]);

        $response = $this->get("/categorie/{$category->slug}?".http_build_query(['on_sale' => '1']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.id', $onSale->id)
        );
    }

    public function test_category_page_exposes_price_and_sale_filters_back_to_the_view(): void
    {
        $category = $this->category();

        $response = $this->get("/categorie/{$category->slug}?".http_build_query([
            'min_price' => 100,
            'max_price' => 900,
            'on_sale' => '1',
            'sort' => 'price_asc',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.min_price', '100')
            ->where('filters.max_price', '900')
            ->where('filters.on_sale', '1')
            ->where('filters.sort', 'price_asc')
        );
    }
}
