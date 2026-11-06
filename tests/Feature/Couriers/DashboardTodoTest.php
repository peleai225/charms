<?php

namespace Tests\Feature\Couriers;

use App\Models\Courier;
use App\Models\CourierMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTodoTest extends TestCase
{
    use CreatesOrders, RefreshDatabase;

    public function test_the_dashboard_lists_what_needs_attention(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = Courier::create(['name' => 'Koné', 'phone' => '0707070707', 'type' => 'independent']);

        $this->makeOrder(['status' => 'pending']);
        $this->makeOrder(['status' => 'processing']);
        $this->makeOrder(['status' => 'processing', 'delivery_attempts' => 1]);
        $this->makeOrder(['status' => 'delivery_in_progress', 'courier_id' => $courier->id, 'total' => 5000]);
        $this->makeOrder(['status' => 'delivered', 'courier_id' => $courier->id]);
        CourierMovement::create(['courier_id' => $courier->id, 'type' => 'point', 'balance_change' => 2000]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('todo.to_confirm', 1)
                ->where('todo.to_dispatch', 2)
                ->where('todo.on_route', 1)
                ->where('todo.on_route_to_collect', fn ($v) => (float) $v === 5000.0)
                ->where('todo.to_redeliver', 1)
                ->where('todo.couriers_balance', fn ($v) => (float) $v === 2000.0));
    }
}
