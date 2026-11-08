<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Page « Meilleures ventes » : produits classés par quantité réellement vendue
 * sur les 90 derniers jours (commandes payées ou livrées, ni annulées ni remboursées).
 */
class BestSellersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-07 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function product(string $name, string $status = 'active'): Product
    {
        return Product::create(['name' => $name, 'sale_price' => 5000, 'status' => $status]);
    }

    /**
     * @param  array<int, array{0: Product, 1: int, 2?: int}>  $lines  [produit, quantité, quantité remboursée]
     */
    private function order(string $status, string $paymentStatus, array $lines, ?Carbon $createdAt = null): Order
    {
        static $n = 0;
        $n++;
        $address = ['first_name' => 'Awa', 'last_name' => 'Koné', 'address' => 'Cocody', 'city' => 'Abidjan', 'postal_code' => '00225', 'country' => 'CI'];
        $order = Order::create(array_merge([
            'order_number' => 'CMD-BEST-'.$n,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => 'cod',
            'subtotal' => 0,
            'total' => 0,
            'billing_email' => 'awa@example.com',
        ], collect($address)->mapWithKeys(fn ($v, $k) => ["billing_{$k}" => $v, "shipping_{$k}" => $v])->all()));

        foreach ($lines as $line) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $line[0]->id,
                'name' => $line[0]->name,
                'sku' => 'SKU-'.$line[0]->id,
                'quantity' => $line[1],
                'quantity_refunded' => $line[2] ?? 0,
                'unit_price' => 5000,
                'total' => 5000 * $line[1],
            ]);
        }

        if ($createdAt) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }

        return $order;
    }

    public function test_products_are_ranked_by_quantity_sold_over_the_last_90_days(): void
    {
        $pagne = $this->product('Pagne wax');
        $sandales = $this->product('Sandales');
        $sac = $this->product('Sac à main');
        $retire = $this->product('Article retiré', 'archived');
        $ancien = $this->product('Ancien succès');
        $annule = $this->product('Commande annulée');
        $attente = $this->product('Pas encore livré');
        $rembourse = $this->product('Remboursé');

        $this->order('delivered', 'pending', [[$pagne, 2], [$sandales, 5]]);  // livrée, payée à la livraison
        $this->order('processing', 'paid', [[$pagne, 4], [$rembourse, 9, 9]]); // payée en ligne ; une ligne remboursée
        $this->order('delivered', 'paid', [[$sac, 1], [$retire, 10]]);
        $this->order('cancelled', 'paid', [[$annule, 20]]);
        $this->order('refunded', 'paid', [[$annule, 20]]);
        $this->order('confirmed', 'pending', [[$attente, 30]]);
        $this->order('delivered', 'paid', [[$ancien, 50]], now()->subDays(91));

        $this->get('/meilleures-ventes')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Shop/BestSellers')
            ->where('days', 90)
            ->where('products', fn ($products) => collect($products)->pluck('id')->all() === [$pagne->id, $sandales->id, $sac->id])
            ->has('products.0', fn (Assert $p) => $p
                ->where('name', 'Pagne wax')
                ->where('slug', $pagne->slug)
                ->where('price', fn ($price) => (float) $price === 5000.0)
                ->hasAll(['compare_price', 'stock', 'allow_backorder', 'has_variants', 'promotion_label', 'category_name', 'primary_image'])
                ->missing('sold_quantity')
                ->etc()));
    }

    public function test_the_page_is_empty_when_nothing_has_been_sold_yet(): void
    {
        $this->product('Pagne wax');
        $this->order('pending', 'pending', [[$this->product('Sandales'), 3]]);

        $this->get(route('shop.best-sellers'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Shop/BestSellers')
            ->where('products', []));
    }
}
