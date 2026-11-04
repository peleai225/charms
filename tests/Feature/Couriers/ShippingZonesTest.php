<?php

namespace Tests\Feature\Couriers;

use App\Models\Setting;
use App\Models\User;
use App\Services\ShippingZones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingZonesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('shipping_zones', json_encode([
            ['name' => 'Cocody', 'cities' => 'Cocody, Plateau', 'price' => 1500, 'courier_fee' => 1000],
            ['name' => 'Yopougon', 'cities' => 'Yopougon', 'price' => 2000],
        ]));
        Setting::clearCache();
    }

    public function test_a_zone_is_found_by_its_name(): void
    {
        $this->assertSame('Cocody', app(ShippingZones::class)->findForCity('cocody')['name']);
    }

    public function test_a_zone_is_found_by_one_of_its_cities(): void
    {
        $this->assertSame('Cocody', app(ShippingZones::class)->findForCity(' Plateau ')['name']);
        $this->assertSame(1000.0, app(ShippingZones::class)->courierFeeForCity('plateau'));
    }

    public function test_an_unknown_city_has_no_courier_fee(): void
    {
        $zones = app(ShippingZones::class);

        $this->assertNull($zones->courierFeeForCity('Bouaké'));
        $this->assertNull($zones->courierFeeForCity(null));
        $this->assertNull($zones->courierFeeForCity('Yopougon'), 'zone sans frais livreur réglés');
    }

    public function test_the_courier_fee_is_saved_with_the_zones(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.shipping.update'), [
            'shipping_enabled' => true,
            'shipping_zones' => [['name' => 'Cocody', 'cities' => 'Cocody', 'price' => 1500, 'courier_fee' => 1500]],
        ])->assertSessionHasNoErrors();

        Setting::clearCache();
        $this->assertSame(1500.0, app(ShippingZones::class)->courierFeeForCity('Cocody'));
    }
}
