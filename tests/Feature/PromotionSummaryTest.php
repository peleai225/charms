<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 6 a changé la sémantique de `Cart::subtotal` : il porte le total catalogue
 * et non plus un montant déjà remisé. Tout écran qui affiche sous-total et total
 * côte à côte doit donc aussi afficher la remise de lot, sinon l'arithmétique
 * montrée au client ne tombe plus juste.
 */
class PromotionSummaryTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        // Le harnais ne transporte pas le cookie de session entre deux requêtes.
        // Les requêtes passent par get()/post() avec un en-tête Accept plutôt que
        // par getJson()/postJson() : ces derniers envoient les cookies en clair,
        // que le middleware de chiffrement rejette — la session serait perdue.
        $this->withCookie(config('session.cookie'), str_repeat('a', 40));

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);

        Promotion::create([
            'name' => '3 t-shirts pour 10 000 F',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ])->categories()->attach($this->tshirts->id);
    }

    private function fillCart(): void
    {
        $product = Product::create([
            'name' => 'T-shirt',
            'sku' => 'SKU-SUM-1',
            'sale_price' => 4000,
            'status' => 'active',
            'category_id' => $this->tshirts->id,
            'stock_quantity' => 50,
        ]);

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_cart_drawer_json_exposes_the_bundle_discount(): void
    {
        $this->fillCart();

        $response = $this->get(route('cart.drawer'), ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('subtotal_fmt', '12 000 F CFA')
            ->assertJsonPath('bundle_discount_fmt', '2 000 F CFA')
            ->assertJsonPath('total_fmt', '10 000 F CFA');
    }

    public function test_cart_drawer_json_has_no_bundle_line_without_offer(): void
    {
        $accessories = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);

        $product = Product::create([
            'name' => 'Casquette',
            'sku' => 'SKU-SUM-2',
            'sale_price' => 3000,
            'status' => 'active',
            'category_id' => $accessories->id,
            'stock_quantity' => 10,
        ]);

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 2]);

        $this->get(route('cart.drawer'), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('bundle_discount_fmt', null)
            ->assertJsonPath('total_fmt', '6 000 F CFA');
    }

    public function test_checkout_page_exposes_the_bundle_discount(): void
    {
        $this->fillCart();

        $response = $this->get(route('checkout.index'));

        $response->assertOk();

        $cart = $response->viewData('page')['props']['cart'];

        $this->assertSame(12000.0, $cart['subtotal']);
        $this->assertSame(2000.0, $cart['bundle_discount']);
        $this->assertSame(10000.0, $cart['payable_subtotal']);
        $this->assertSame(10000.0, $cart['total']);
    }

    public function test_coupon_minimum_is_checked_against_what_the_client_pays(): void
    {
        $this->fillCart();

        // Minimum de 11 000 F : le panier vaut 12 000 au catalogue mais 10 000 à
        // payer. Le coupon doit être refusé sur ce que le client paie.
        \App\Models\Coupon::create([
            'code' => 'MIN11000',
            'name' => 'Minimum 11 000',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 11000,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $this->post(route('cart.coupon.apply'), ['coupon_code' => 'MIN11000'], ['Accept' => 'application/json'])
            ->assertStatus(400);

        $this->assertNull(\App\Models\Cart::firstOrFail()->coupon_code);
    }
}
