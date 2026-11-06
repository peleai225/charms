<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderListFiltersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-04 15:00:00');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function orderOn(string $date): Order
    {
        static $n = 0;
        $n++;
        $address = ['first_name' => 'Awa', 'last_name' => 'Koné', 'address' => 'Cocody', 'city' => 'Abidjan', 'postal_code' => '00225', 'country' => 'CI'];
        $order = Order::create(array_merge(
            ['order_number' => 'CMD-F-'.$n, 'status' => 'processing', 'payment_status' => 'pending', 'payment_method' => 'cod',
                'subtotal' => 5000, 'total' => 5000, 'billing_email' => 'a@example.com'],
            collect($address)->mapWithKeys(fn ($v, $k) => ["billing_{$k}" => $v, "shipping_{$k}" => $v])->all(),
        ));
        $order->forceFill(['created_at' => $date])->save();

        return $order;
    }

    private function listed(string $query): array
    {
        $ids = [];
        $this->actingAs($this->admin)->get(route('admin.orders.index').'?'.$query)
            ->assertInertia(function (Assert $page) use (&$ids) {
                $ids = collect($page->toArray()['props']['orders']['data'])->pluck('order_number')->sort()->values()->all();
            });

        return $ids;
    }

    public function test_the_period_filter_really_filters_the_orders(): void
    {
        $today = $this->orderOn('2026-11-04 09:00:00');
        $thisWeek = $this->orderOn('2026-11-02 10:00:00'); // lundi
        $lastMonth = $this->orderOn('2026-10-20 10:00:00');

        $this->assertSame([$today->order_number], $this->listed('date_range=today'));
        $this->assertSame(collect([$today, $thisWeek])->pluck('order_number')->sort()->values()->all(), $this->listed('date_range=week'));
        $this->assertNotContains($lastMonth->order_number, $this->listed('date_range=month'));
        $this->assertCount(3, $this->listed(''));
    }

    public function test_the_chosen_period_and_page_size_stay_selected(): void
    {
        $this->actingAs($this->admin)->get(route('admin.orders.index').'?date_range=week&per_page=100')
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.date_range', 'week')
                ->where('filters.per_page', '100'));
    }

    public function test_up_to_100_orders_can_be_shown_per_page(): void
    {
        foreach (range(1, 25) as $i) {
            $this->orderOn('2026-11-04 08:00:00');
        }

        $this->assertCount(20, $this->listed(''));
        $this->assertCount(25, $this->listed('per_page=50'));
        $this->assertCount(20, $this->listed('per_page=9999'));
    }
}
