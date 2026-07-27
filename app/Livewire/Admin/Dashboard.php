<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin-livewire')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $period = 'month';

    public function setPeriod(string $period): void
    {
        $allowed = ['today', 'week', 'month'];
        if (! in_array($period, $allowed, true)) {
            return;
        }

        $this->period = $period;

        // Dispatch updated chart data to Alpine so Chart.js can refresh
        $chart = $this->getSalesChartData();
        $this->dispatch('chart-data-updated',
            labels:   $chart['labels'],
            revenues: $chart['revenues'],
            orders:   $chart['orders'],
        );
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats'            => $this->getMainStats(),
            'salesChart'       => $this->getSalesChartData(),
            'recentOrders'     => $this->getRecentOrders(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'topProducts'      => $this->getTopProducts(),
        ]);
    }

    // ── Private methods ──────────────────────────────────────────────────────

    private function getMainStats(): array
    {
        $today        = Carbon::today();
        $thisMonth    = Carbon::now()->startOfMonth();
        $lastMonth    = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $monthlyRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('total');

        $lastMonthRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $todayOrders    = Order::whereDate('created_at', $today)->count();
        $pendingOrders  = Order::whereIn('status', ['pending', 'confirmed', 'processing'])->count();
        $totalCustomers = Customer::count();
        $newCustomers   = Customer::where('created_at', '>=', $thisMonth)->count();
        $activeProducts = Product::where('status', 'active')->count();

        $outOfStock = Product::where('status', 'active')
            ->where('track_stock', true)
            ->where('stock_quantity', '<=', 0)
            ->count();

        $stockValue = Product::where('status', 'active')
            ->selectRaw('SUM(stock_quantity * COALESCE(cost_price, purchase_price)) as value')
            ->value('value') ?? 0;

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_growth'  => $revenueGrowth,
            'today_orders'    => $todayOrders,
            'pending_orders'  => $pendingOrders,
            'total_customers' => $totalCustomers,
            'new_customers'   => $newCustomers,
            'active_products' => $activeProducts,
            'out_of_stock'    => $outOfStock,
            'stock_value'     => $stockValue,
        ];
    }

    private function getSalesChartData(): array
    {
        if ($this->period === 'today') {
            $startDate = Carbon::today();
            $sales = Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', $startDate)
                ->selectRaw('HOUR(created_at) as hour, SUM(total) as total, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->keyBy('hour');

            $labels = $revenues = $orders = [];
            for ($h = 0; $h < 24; $h++) {
                $labels[]   = str_pad($h, 2, '0', STR_PAD_LEFT) . 'h';
                $revenues[] = $sales[$h]->total ?? 0;
                $orders[]   = $sales[$h]->count ?? 0;
            }
        } else {
            $days      = $this->period === 'week' ? 7 : 30;
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

            $sales = Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $labels = $revenues = $orders = [];
            for ($i = 0; $i < $days; $i++) {
                $date       = Carbon::now()->subDays($days - 1 - $i);
                $dateKey    = $date->format('Y-m-d');
                $labels[]   = $date->format('d/m');
                $revenues[] = $sales[$dateKey]->total ?? 0;
                $orders[]   = $sales[$dateKey]->count ?? 0;
            }
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'orders'   => $orders,
        ];
    }

    private function getRecentOrders()
    {
        return Order::with('customer')
            ->latest()
            ->take(10)
            ->get();
    }

    private function getLowStockProducts()
    {
        return Product::where('status', 'active')
            ->where('track_stock', true)
            ->where(function ($query) {
                $query->where('stock_quantity', '<=', 0)
                    ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
            })
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();
    }

    private function getTopProducts()
    {
        return Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.sale_price',
                'products.status',
                'products.stock_quantity',
                'products.created_at',
                'products.updated_at',
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->with('images')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->whereNotIn('orders.status', ['cancelled', 'refunded'])
                    ->where('orders.created_at', '>=', now()->subDays(30));
            })
            ->groupBy([
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.sale_price',
                'products.status',
                'products.stock_quantity',
                'products.created_at',
                'products.updated_at',
            ])
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();
    }
}
