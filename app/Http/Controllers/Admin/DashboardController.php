<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $period = in_array($request->period, ['today', 'week', 'month'])
            ? $request->period
            : 'month';

        return Inertia::render('Admin/Dashboard/Index', [
            'stats'         => $this->getMainStats(),
            'salesChart'    => $this->getSalesChartData($period),
            'recentOrders'  => $this->getRecentOrders(),
            'lowStock'      => $this->getLowStockProducts(),
            'topProducts'   => $this->getTopProducts(),
            'currentPeriod' => $period,
        ]);
    }

    private function getMainStats(): array
    {
        $today        = Carbon::today();
        $thisMonth    = Carbon::now()->startOfMonth();
        $lastMonth    = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $monthlyRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', $thisMonth)->sum('total');

        $lastMonthRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_growth'  => $revenueGrowth,
            'today_orders'    => Order::whereDate('created_at', $today)->count(),
            'pending_orders'  => Order::whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'new_customers'   => Customer::where('created_at', '>=', $thisMonth)->count(),
            'active_products' => Product::where('status', 'active')->count(),
            'out_of_stock'    => Product::where('status', 'active')->where('track_stock', true)->where('stock_quantity', '<=', 0)->count(),
            'stock_value'     => Product::where('status', 'active')->selectRaw('SUM(stock_quantity * COALESCE(cost_price, purchase_price)) as value')->value('value') ?? 0,
        ];
    }

    private function getSalesChartData(string $period): array
    {
        if ($period === 'today') {
            $sales = Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', Carbon::today())
                ->selectRaw('HOUR(created_at) as hour, SUM(total) as total, COUNT(*) as count')
                ->groupBy('hour')->orderBy('hour')->get()->keyBy('hour');

            $labels = $revenues = $orders = [];
            for ($h = 0; $h < 24; $h++) {
                $labels[]   = str_pad($h, 2, '0', STR_PAD_LEFT) . 'h';
                $revenues[] = (float) ($sales[$h]->total ?? 0);
                $orders[]   = (int) ($sales[$h]->count ?? 0);
            }
        } else {
            $days      = $period === 'week' ? 7 : 30;
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

            $sales = Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
                ->groupBy('date')->orderBy('date')->get()->keyBy('date');

            $labels = $revenues = $orders = [];
            for ($i = 0; $i < $days; $i++) {
                $date       = Carbon::now()->subDays($days - 1 - $i);
                $dateKey    = $date->format('Y-m-d');
                $labels[]   = $date->format('d/m');
                $revenues[] = (float) ($sales[$dateKey]->total ?? 0);
                $orders[]   = (int) ($sales[$dateKey]->count ?? 0);
            }
        }

        return compact('labels', 'revenues', 'orders');
    }

    private function getRecentOrders(): array
    {
        return Order::with('customer')->latest()->take(10)->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'total'          => $o->total,
                'status'         => $o->status,
                'customer_name'  => trim(($o->customer?->first_name ?? $o->billing_first_name) . ' ' . ($o->customer?->last_name ?? $o->billing_last_name)),
                'created_at_fmt' => $o->created_at->format('d/m H:i'),
            ])->toArray();
    }

    private function getLowStockProducts(): array
    {
        return Product::where('status', 'active')->where('track_stock', true)
            ->where(fn($q) => $q->where('stock_quantity', '<=', 0)->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold'))
            ->orderBy('stock_quantity')->take(10)->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'stock_quantity' => $p->stock_quantity,
            ])->toArray();
    }

    private function getTopProducts(): array
    {
        $products = Product::select([
                'products.id', 'products.name', 'products.slug',
                'products.sku', 'products.sale_price', 'products.status',
                'products.stock_quantity', 'products.created_at', 'products.updated_at',
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->with('images')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->whereNotIn('orders.status', ['cancelled', 'refunded'])
                    ->where('orders.created_at', '>=', now()->subDays(30));
            })
            ->groupBy(['products.id','products.name','products.slug','products.sku','products.sale_price','products.status','products.stock_quantity','products.created_at','products.updated_at'])
            ->orderByDesc('total_sold')->take(5)->get();

        $maxSold = $products->first()?->total_sold ?? 1;

        return $products->map(fn($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'total_sold' => $p->total_sold,
            'pct'        => $maxSold > 0 ? round($p->total_sold / $maxSold * 100) : 0,
            'image_url'  => $p->images->first() ? asset('storage/' . $p->images->first()->path) : null,
        ])->toArray();
    }
}
