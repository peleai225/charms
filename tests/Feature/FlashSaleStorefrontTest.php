<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Ventes flash côté boutique : le bloc de l'accueil, la page /ventes-flash et
 * la fiche produit montrent le prix réellement facturé et l'heure de fin.
 */
class FlashSaleStorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');
        $this->category = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function product(string $name = 'Basket', float $price = 10000, string $status = 'active'): Product
    {
        return Product::create([
            'name' => $name,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'sale_price' => $price,
            'status' => $status,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);
    }

    /**
     * @param  Product[]  $products
     */
    private function sale(int $percent, array $products, string $end = '+2 hours', string $start = '-1 hour', string $name = 'Ventes flash'): FlashSale
    {
        $sale = FlashSale::create([
            'name' => $name,
            'discount_percent' => $percent,
            'starts_at' => now()->modify($start),
            'ends_at' => now()->modify($end),
            'is_active' => true,
        ]);
        $sale->products()->attach(collect($products)->pluck('id')->all());

        return $sale;
    }

    private static function money(float $expected): \Closure
    {
        return fn ($value) => (float) $value === $expected;
    }

    // ================================================================
    // Accueil
    // ================================================================

    public function test_home_has_no_flash_block_without_a_current_sale(): void
    {
        $this->sale(20, [$this->product()], '+5 hours', '+1 hour');           // programmée
        $this->sale(20, [$this->product('Sac')], '-1 hour', '-3 hours');      // terminée

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('flash_sale', null));
    }

    public function test_home_has_no_flash_block_when_no_product_of_the_sale_is_active(): void
    {
        $this->sale(20, [$this->product('Brouillon', 10000, 'draft')]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('flash_sale', null));
    }

    public function test_home_shows_the_current_sale_with_flash_prices(): void
    {
        $basket = $this->product('Basket', 10000);
        $draft = $this->product('Brouillon', 5000, 'draft');
        $sale = $this->sale(20, [$basket, $draft], name: 'Flash du jour');

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('flash_sale.id', $sale->id)
            ->where('flash_sale.name', 'Flash du jour')
            ->where('flash_sale.discount_percent', 20)
            ->where('flash_sale.ends_at', '2026-11-10T12:00:00+00:00')
            ->has('flash_sale.products', 1)
            ->where('flash_sale.products.0.id', $basket->id)
            ->where('flash_sale.products.0.slug', $basket->slug)
            ->where('flash_sale.products.0.price', self::money(8000))
            ->where('flash_sale.products.0.compare_price', self::money(10000)));
    }

    public function test_home_shows_the_sale_ending_soonest(): void
    {
        $this->sale(30, [$this->product('Sac')], '+5 hours');
        $soon = $this->sale(20, [$this->product('Basket')], '+1 hour');

        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('flash_sale.id', $soon->id));
    }

    public function test_home_shows_a_product_with_the_sale_that_sets_its_price(): void
    {
        // Le panier facture la plus forte remise : la vente à 20 % ne fixe le prix
        // d'aucun produit, le bloc montre donc celle à 50 % et son heure de fin.
        $basket = $this->product('Basket', 10000);
        $this->sale(20, [$basket], '+1 hour');
        $best = $this->sale(50, [$basket], '+5 hours');

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('flash_sale.id', $best->id)
            ->where('flash_sale.products.0.price', self::money(5000)));
    }

    // ================================================================
    // Page publique /ventes-flash
    // ================================================================

    public function test_the_flash_sales_page_lists_current_sales_and_their_products(): void
    {
        $basket = $this->product('Basket', 10000);
        $bag = $this->product('Sac', 20000);
        $this->product('Hors vente', 3000);
        $soon = $this->sale(20, [$basket], '+1 hour', name: 'Bientôt fini');
        $later = $this->sale(10, [$bag, $basket], '+5 hours', name: 'Plus tard');

        $this->get(route('flash-sales.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Shop/FlashSales')
            ->has('sales', 2)
            ->where('sales.0.id', $soon->id)
            ->where('sales.0.products.0.id', $basket->id)
            ->where('sales.0.products.0.price', self::money(8000))
            // Le panier facture la meilleure remise : le produit n'est montré qu'une fois, dans la vente qui fixe son prix
            ->where('sales.1.id', $later->id)
            ->has('sales.1.products', 1)
            ->where('sales.1.products.0.id', $bag->id)
            ->where('sales.1.products.0.price', self::money(18000))
            ->where('sales.1.products.0.compare_price', self::money(20000)));
    }

    public function test_the_flash_sales_page_is_empty_without_a_current_sale(): void
    {
        $this->sale(20, [$this->product()], '-1 hour', '-3 hours');

        $this->get(route('flash-sales.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Shop/FlashSales')
            ->has('sales', 0));
    }

    // ================================================================
    // Fiche produit
    // ================================================================

    public function test_the_product_page_shows_the_flash_price_and_the_end_of_the_sale(): void
    {
        $basket = $this->product('Basket', 10000);
        ProductVariant::create([
            'product_id' => $basket->id, 'name' => '44', 'sku' => 'B-44',
            'sale_price' => 12000, 'stock_quantity' => 5, 'is_active' => true,
        ]);
        $this->sale(25, [$basket], name: 'Flash chaussures');

        $this->get('/produit/'.$basket->slug)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('product.price', self::money(7500))
            ->where('product.regular_price', self::money(10000))
            ->where('product.flash_sale.name', 'Flash chaussures')
            ->where('product.flash_sale.discount_percent', 25)
            ->where('product.flash_sale.ends_at', '2026-11-10T12:00:00+00:00')
            ->where('product.variants.0.price', self::money(9000))
            ->where('product.variants.0.regular_price', self::money(12000)));
    }

    public function test_the_product_page_hides_lot_offers_during_a_flash_sale(): void
    {
        Promotion::create([
            'name' => '3 pour 25 000 F', 'lot_qty' => 3, 'lot_price' => 25000, 'is_active' => true,
        ])->categories()->attach($this->category->id);
        $basket = $this->product('Basket', 10000);
        $this->sale(20, [$basket]);

        $this->get('/produit/'.$basket->slug)->assertInertia(fn (Assert $page) => $page->has('promotions', 0));
    }

    public function test_the_product_page_shows_the_normal_price_once_the_sale_has_ended(): void
    {
        $basket = $this->product('Basket', 10000);
        $this->sale(20, [$basket], '-1 hour', '-3 hours');

        $this->get('/produit/'.$basket->slug)->assertInertia(fn (Assert $page) => $page
            ->where('product.price', self::money(10000))
            ->where('product.flash_sale', null));
    }
}
