<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Caisse en magasin (POS) : une vente flash en cours s'applique comme sur le site,
 * et le prix normal revient dès la fin de la vente.
 */
class PosFlashSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-10 10:00:00');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Chaussures', 'slug' => 'chaussures', 'is_active' => true]);
        $this->product = Product::create([
            'name' => 'Basket', 'sku' => 'BASKET-1', 'barcode' => '1234567890123', 'sale_price' => 10000,
            'status' => 'active', 'category_id' => $category->id, 'stock_quantity' => 10,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sale(int $percent, string $end = '+2 hours', string $start = '-1 hour'): FlashSale
    {
        $sale = FlashSale::create([
            'name' => 'Ventes flash', 'discount_percent' => $percent,
            'starts_at' => now()->modify($start), 'ends_at' => now()->modify($end), 'is_active' => true,
        ]);
        $sale->products()->attach($this->product->id);

        return $sale;
    }

    private function addToPosCart(?ProductVariant $variant = null, int $quantity = 1)
    {
        return $this->actingAs($this->admin)->postJson(route('admin.scanner.cart.add'), [
            'product_id' => $this->product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
        ])->assertOk();
    }

    private function checkout()
    {
        return $this->actingAs($this->admin)->postJson(route('admin.scanner.checkout'), [
            'payment_method' => 'cash',
            'amount_received' => 20000,
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_scanning_a_product_on_flash_sale_shows_the_flash_price(): void
    {
        $this->sale(20);

        $this->actingAs($this->admin)->postJson(route('admin.scanner.scan'), ['code' => '1234567890123'])
            ->assertOk()
            ->assertJsonPath('data.price', 8000)
            ->assertJsonPath('data.regular_price', 10000)
            ->assertJsonPath('data.flash.discount_percent', 20)
            ->assertJsonPath('data.price_formatted', '8 000 F CFA');
    }

    public function test_a_variant_gets_the_same_percentage_on_its_own_price(): void
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product->id, 'name' => '44', 'sku' => 'BASKET-44', 'barcode' => '999',
            'sale_price' => 12000, 'stock_quantity' => 5, 'is_active' => true,
        ]);
        $this->sale(25);

        $this->actingAs($this->admin)->postJson(route('admin.scanner.scan'), ['code' => '999'])
            ->assertOk()
            ->assertJsonPath('data.price', 9000)
            ->assertJsonPath('data.regular_price', 12000);

        $this->addToPosCart($variant)->assertJsonPath('cart.total', 9000);
    }

    public function test_a_sale_made_during_the_flash_sale_is_charged_the_flash_price(): void
    {
        $this->sale(20);

        $this->addToPosCart(quantity: 2)
            ->assertJsonPath('cart.items.0.price', 8000)
            ->assertJsonPath('cart.items.0.regular_price', 10000)
            ->assertJsonPath('cart.total', 16000);

        $this->checkout();

        $order = Order::where('source', 'pos')->firstOrFail();
        $this->assertEquals(16000, (float) $order->total);
        $this->assertEquals(8000, (float) $order->items()->first()->unit_price);
    }

    public function test_the_normal_price_comes_back_if_the_flash_sale_ends_before_checkout(): void
    {
        $this->sale(20, '+30 minutes');
        $this->addToPosCart()->assertJsonPath('cart.total', 8000);

        Carbon::setTestNow('2026-11-10 11:00:00');

        $this->actingAs($this->admin)->getJson(route('admin.scanner.cart'))
            ->assertOk()
            ->assertJsonPath('items.0.price', 10000)
            ->assertJsonPath('total', 10000);

        $this->checkout();
        $this->assertEquals(10000, (float) Order::where('source', 'pos')->firstOrFail()->total);
    }

    public function test_an_item_added_just_before_the_sale_starts_gets_the_flash_price_at_checkout(): void
    {
        $this->sale(20, '+2 hours', '+10 minutes');
        $this->addToPosCart()->assertJsonPath('cart.total', 10000);

        Carbon::setTestNow('2026-11-10 10:15:00');

        $this->checkout();
        $this->assertEquals(8000, (float) Order::where('source', 'pos')->firstOrFail()->total);
    }

    public function test_without_flash_sale_the_till_keeps_the_normal_price(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.scanner.scan'), ['code' => 'BASKET-1'])
            ->assertOk()
            ->assertJsonPath('data.price', 10000)
            ->assertJsonPath('data.flash', null);

        $this->addToPosCart()->assertJsonPath('cart.total', 10000);
    }
}
