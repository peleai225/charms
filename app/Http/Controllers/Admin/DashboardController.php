<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le dashboard admin
     */
    public function index()
    {
        // Statistiques principales
        $stats = $this->getMainStats();
        
        // Ventes des 30 derniers jours
        $salesChart = $this->getSalesChartData();
        
        // Commandes récentes
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(10)
            ->get();
        
        // Produits en rupture ou stock bas
        $lowStockProducts = Product::where('status', 'active')
            ->where('track_stock', true)
            ->where(function ($query) {
                $query->where('stock_quantity', '<=', 0)
                    ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
            })
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();

        // Top produits vendus (CORRIGÉ pour MySQL strict mode)
        $topProducts = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.sale_price',
                'products.status',
                'products.stock_quantity',
                'products.created_at',
                'products.updated_at'
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
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
                'products.updated_at'
            ])
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'salesChart',
            'recentOrders',
            'lowStockProducts',
            'topProducts'
        ));
    }

    /**
     * API : stats filtrées par période (today / week / month)
     */
    public function apiStats(Request $request)
    {
        $period = $request->input('period', 'month');

        [$start, $prevStart, $prevEnd] = match ($period) {
            'today' => [
                Carbon::today(),
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
            ],
            'week' => [
                Carbon::now()->startOfWeek(),
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ],
            default => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
        };

        $revenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', $start)->sum('total');

        $prevRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');

        $growth = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $ordersCount  = Order::where('created_at', '>=', $start)->count();
        $pendingCount = Order::whereIn('status', ['pending', 'confirmed', 'processing'])->count();
        $newCustomers = Customer::where('created_at', '>=', $start)->count();

        // Chart data for the selected period
        $days = match ($period) {
            'today' => 24,   // hours
            'week'  => 7,
            default => 30,
        };

        $chart = $this->getSalesChartData($period);

        return response()->json([
            'revenue'      => $revenue,
            'revenue_fmt'  => number_format($revenue, 0, ',', ' ') . ' F CFA',
            'growth'       => $growth,
            'orders'       => $ordersCount,
            'pending'      => $pendingCount,
            'new_customers'=> $newCustomers,
            'chart'        => $chart,
        ]);
    }

    /**
     * Statistiques principales
     */
    protected function getMainStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Chiffre d'affaires du mois
        $monthlyRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('total');

        // Chiffre d'affaires du mois précédent
        $lastMonthRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total');

        // Évolution du CA
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Nombre de commandes du jour
        $todayOrders = Order::whereDate('created_at', $today)->count();

        // Commandes en attente
        $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'processing'])->count();

        // Nombre de clients
        $totalCustomers = Customer::count();

        // Nouveaux clients ce mois
        $newCustomers = Customer::where('created_at', '>=', $thisMonth)->count();

        // Produits actifs
        $activeProducts = Product::where('status', 'active')->count();

        // Produits en rupture
        $outOfStock = Product::where('status', 'active')
            ->where('track_stock', true)
            ->where('stock_quantity', '<=', 0)
            ->count();

        // Valeur totale du stock
        $stockValue = Product::where('status', 'active')
            ->selectRaw('SUM(stock_quantity * COALESCE(cost_price, purchase_price)) as value')
            ->value('value') ?? 0;

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_growth' => $revenueGrowth,
            'today_orders' => $todayOrders,
            'pending_orders' => $pendingOrders,
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'active_products' => $activeProducts,
            'out_of_stock' => $outOfStock,
            'stock_value' => $stockValue,
        ];
    }

    /**
     * Données pour le graphique des ventes
     */
    protected function getSalesChartData(string $period = 'month'): array
    {
        if ($period === 'today') {
            // Granularité : heure par heure
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
            $days = $period === 'week' ? 7 : 30;
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
}

