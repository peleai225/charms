<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\ThermalPrinterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThermalPrinterTest extends TestCase
{
    use RefreshDatabase;

    private ThermalPrinterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ThermalPrinterService::class);
    }

    /** @test */
    public function it_generates_receipt_data()
    {
        $order = Order::factory()->create([
            'order_number' => 'CMD-TEST-001',
            'total' => 15000,
            'source' => 'pos',
            'payment_method' => 'cash',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'name' => 'Produit Test',
            'quantity' => 2,
            'price' => 5000,
            'total' => 10000,
        ]);

        $receiptData = $this->service->generateReceipt($order);

        $this->assertIsArray($receiptData);
        $this->assertNotEmpty($receiptData);

        // Vérifier présence commandes essentielles
        $commands = array_column($receiptData, 'cmd');
        $this->assertContains('text', $commands);
        $this->assertContains('align', $commands);
        $this->assertContains('feed', $commands);
        $this->assertContains('cut', $commands);
    }

    /** @test */
    public function it_converts_to_plain_text()
    {
        $order = Order::factory()->create([
            'order_number' => 'CMD-TEST-002',
            'total' => 8500,
            'source' => 'pos',
        ]);

        $receiptData = $this->service->generateReceipt($order);
        $plainText = $this->service->toPlainText($receiptData);

        $this->assertIsString($plainText);
        $this->assertStringContainsString('CMD-TEST-002', $plainText);
        $this->assertStringContainsString('TOTAL', $plainText);
    }

    /** @test */
    public function thermal_receipt_api_returns_json()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create([
            'source' => 'pos',
            'total' => 12000,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.scanner.receipt.thermal', $order));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'order_number',
                'receipt',
                'plain_text',
                'instructions',
            ]);
    }

    /** @test */
    public function text_receipt_api_returns_plain_text()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create([
            'source' => 'pos',
            'order_number' => 'CMD-TXT-999',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.scanner.receipt.text', $order));

        $response->assertOk();
        $this->assertEquals('text/plain; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('CMD-TXT-999', $response->content());
    }

    /** @test */
    public function it_respects_width_parameter()
    {
        $order = Order::factory()->create(['source' => 'pos']);

        // Test 80mm (48 chars)
        $receipt80 = $this->service->generateReceipt($order, ['width' => 48]);
        $text80 = $this->service->toPlainText($receipt80);
        $lines80 = explode("\n", $text80);
        $longestLine80 = max(array_map('strlen', $lines80));
        $this->assertLessThanOrEqual(48, $longestLine80);

        // Test 58mm (32 chars)
        $receipt58 = $this->service->generateReceipt($order, ['width' => 32]);
        $text58 = $this->service->toPlainText($receipt58);
        $lines58 = explode("\n", $text58);
        $longestLine58 = max(array_map('strlen', $lines58));
        $this->assertLessThanOrEqual(32, $longestLine58);
    }
}
