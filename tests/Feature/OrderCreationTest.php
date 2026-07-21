<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Attributs minimaux pour créer une commande valide.
     */
    private function orderData(array $overrides = []): array
    {
        return array_merge([
            'status'             => Order::STATUS_PENDING,
            'payment_status'     => Order::PAYMENT_PENDING,
            'total'              => 15000,
            'billing_first_name' => 'Aminata',
            'billing_last_name'  => 'Diallo',
            'billing_email'      => 'aminata@example.com',
            'billing_phone'      => '+221700000000',
            'billing_address'    => 'Rue 10 Dakar',
            'billing_city'       => 'Dakar',
            'billing_country'    => 'SN',
        ], $overrides);
    }

    // ================================================================
    // Une commande créée a un order_number non vide
    // ================================================================

    public function test_created_order_has_non_empty_order_number(): void
    {
        $order = Order::create($this->orderData());

        $this->assertNotNull($order->order_number);
        $this->assertNotEmpty($order->order_number);
    }

    // ================================================================
    // Le statut initial est 'pending'
    // ================================================================

    public function test_new_order_has_pending_status(): void
    {
        $order = Order::create($this->orderData());

        $this->assertEquals(Order::STATUS_PENDING, $order->status);
    }

    // ================================================================
    // Deux commandes créées consécutivement ont des order_number distincts
    // ================================================================

    public function test_two_orders_have_different_order_numbers(): void
    {
        $order1 = Order::create($this->orderData(['billing_email' => 'client1@example.com']));
        $order2 = Order::create($this->orderData(['billing_email' => 'client2@example.com']));

        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    // ================================================================
    // L'order_number suit le format CMD-YYMMDD-XXXX
    // ================================================================

    public function test_order_number_matches_expected_format(): void
    {
        $order = Order::create($this->orderData());

        // Format : CMD-YYMMDD-4 caractères alphanumériques majuscules
        $this->assertMatchesRegularExpression('/^CMD-\d{6}-[A-Z0-9]{4}$/', $order->order_number);
    }

    // ================================================================
    // Un order_number explicitement fourni est conservé
    // ================================================================

    public function test_explicit_order_number_is_preserved(): void
    {
        $order = Order::create($this->orderData(['order_number' => 'CMD-CUSTOM-0001']));

        $this->assertEquals('CMD-CUSTOM-0001', $order->order_number);
    }
}
