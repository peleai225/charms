<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crée un produit minimal valide.
     */
    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name'           => 'Produit Test',
            'sale_price'     => 5000,
            'stock_quantity' => 10,
            'track_stock'    => true,
            'allow_backorder' => false,
            'status'         => 'active',
        ], $overrides));
    }

    // ================================================================
    // Stock épuisé, backorder désactivé → erreur
    // ================================================================

    public function test_add_to_cart_fails_when_stock_is_zero_and_backorder_disabled(): void
    {
        $product = $this->makeProduct([
            'stock_quantity'  => 0,
            'track_stock'     => true,
            'allow_backorder' => false,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        // Le contrôleur renvoie back() avec erreur → redirection + message session
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ================================================================
    // Stock épuisé, backorder activé → succès
    // ================================================================

    public function test_add_to_cart_succeeds_when_allow_backorder_is_true(): void
    {
        $product = $this->makeProduct([
            'stock_quantity'  => 0,
            'track_stock'     => true,
            'allow_backorder' => true,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        // Succès → redirection vers le panier sans message d'erreur
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionMissing('error');
    }

    // ================================================================
    // Produit inactif → erreur
    // ================================================================

    public function test_add_to_cart_fails_when_product_is_inactive(): void
    {
        $product = $this->makeProduct([
            'stock_quantity' => 50,
            'status'         => 'inactive',
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ================================================================
    // Produit actif avec stock suffisant → succès (contrôle positif)
    // ================================================================

    public function test_add_to_cart_succeeds_for_active_product_with_sufficient_stock(): void
    {
        $product = $this->makeProduct([
            'stock_quantity'  => 10,
            'track_stock'     => true,
            'allow_backorder' => false,
            'status'          => 'active',
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionMissing('error');
    }
}
