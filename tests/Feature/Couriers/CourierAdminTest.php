<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\CourierMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourierAdminTest extends TestCase
{
    use CreatesOrders, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_adds_a_courier(): void
    {
        $this->actingAs($this->admin)->post(route('admin.couriers.store'), [
            'name' => 'Koné', 'phone' => '0707070707', 'type' => 'independent',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('couriers', ['name' => 'Koné', 'phone' => '0707070707', 'type' => 'independent', 'is_active' => true]);
    }

    public function test_a_courier_needs_a_name_and_a_known_type(): void
    {
        $this->actingAs($this->admin)->post(route('admin.couriers.store'), [
            'name' => '', 'phone' => '0707070707', 'type' => 'moto',
        ])->assertSessionHasErrors(['name', 'type']);
    }

    public function test_the_list_shows_what_each_courier_carries_and_owes(): void
    {
        $courier = Courier::create(['name' => 'Koné', 'phone' => '0707070707', 'type' => 'independent']);
        $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $courier->id, 'total' => 5000]);
        $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $courier->id, 'total' => 7500]);
        $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $courier->id, 'total' => 9000, 'payment_status' => 'paid']);
        CourierMovement::create(['courier_id' => $courier->id, 'type' => 'point', 'balance_change' => 3000]);

        $this->actingAs($this->admin)->get(route('admin.couriers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Couriers/Index')
                ->where('couriers.0.name', 'Koné')
                ->where('couriers.0.on_route_count', 3)
                ->where('couriers.0.on_route_to_collect', fn ($v) => (float) $v === 12500.0)
                ->where('couriers.0.balance', fn ($v) => (float) $v === 3000.0));
    }

    public function test_a_courier_can_be_deactivated(): void
    {
        $courier = Courier::create(['name' => 'Koné', 'phone' => '0707070707', 'type' => 'company']);

        $this->actingAs($this->admin)->put(route('admin.couriers.update', $courier), [
            'name' => 'Koné', 'phone' => '0707070707', 'type' => 'company', 'is_active' => false,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($courier->fresh()->is_active);
    }
}
