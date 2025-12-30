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

        // Top produits vendus
        $topProducts = Product::select('products.*')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id')
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
    protected function getSalesChartData(): array
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $sales = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $revenues = [];
        $orders = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i);
            $dateKey = $date->format('Y-m-d');
            
            $labels[] = $date->format('d/m');
            $revenues[] = $sales[$dateKey]->total ?? 0;
            $orders[] = $sales[$dateKey]->count ?? 0;
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'orders' => $orders,
        ];
    }
}

