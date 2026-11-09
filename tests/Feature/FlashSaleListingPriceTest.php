<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Un produit en vente flash affiche le même prix partout (boutique, catégorie, accueil) :
 * celui que le panier facture. Après la vente, le prix normal revient partout.
 */
class FlashSaleListingPriceTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');
        $this->category = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures', 'is_active' => true]);
        $this->product = Product::create([
            'name' => 'Basket', 'sku' => 'SKU-FLASH-1', 'sale_price' => 10000, 'compare_price' => 12000,
            'status' => 'active', 'category_id' => $this->category->id, 'stock_quantity' => 10, 'is_featured' => true,
        ]);
        $sale = FlashSale::create([
            'name' => 'Ventes flash', 'discount_percent' => 20,
            'starts_at' => now()->subHour(), 'ends_at' => now()->addHours(2), 'is_active' => true,
        ]);
        $sale->products()->attach($this->product->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function card(iterable $cards): array
    {
        return (array) collect($cards)->map(fn ($c) => (array) $c)->firstWhere('id', $this->product->id);
    }

    public function test_the_shop_grid_shows_the_flash_price(): void
    {
        $this->get('/boutique')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.data', function ($cards) {
                $card = $this->card($cards);

                return (float) $card['price'] === 8000.0 && (float) $card['compare_price'] === 10000.0;
            }));
    }

    public function test_the_category_page_and_the_home_page_show_the_flash_price(): void
    {
        $this->get('/categorie/chaussures')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.data', fn ($cards) => (float) $this->card($cards)['price'] === 8000.0));

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('featured_products', fn ($cards) => (float) $this->card($cards)['price'] === 8000.0));
    }

    public function test_the_normal_price_comes_back_when_the_sale_is_over(): void
    {
        Carbon::setTestNow('2026-11-10 13:00:00');

        $this->get('/boutique')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.data', function ($cards) {
                $card = $this->card($cards);

                return (float) $card['price'] === 10000.0 && (float) $card['compare_price'] === 12000.0;
            }));
    }
}
