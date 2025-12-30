<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Page principale des rapports
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Rapport des ventes
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        // Format de groupement
        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Ventes par période
        $salesData = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(discount_amount) as discounts'),
                DB::raw('AVG(total) as average_order')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Totaux
        $totals = [
            'orders' => $salesData->sum('orders_count'),
            'revenue' => $salesData->sum('revenue'),
            'discounts' => $salesData->sum('discounts'),
            'average' => $salesData->avg('average_order') ?? 0,
        ];

        // Comparaison période précédente
        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        $previousStart = Carbon::parse($startDate)->subDays($daysDiff + 1)->format('Y-m-d');
        $previousEnd = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $previousTotals = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$previousStart, Carbon::parse($previousEnd)->endOfDay()])
            ->select(
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->first();

        $comparison = [
            'orders' => $this->calculateGrowth($totals['orders'], $previousTotals->orders ?? 0),
            'revenue' => $this->calculateGrowth($totals['revenue'], $previousTotals->revenue ?? 0),
        ];

        return view('admin.reports.sales', compact('salesData', 'totals', 'comparison', 'startDate', 'endDate', 'groupBy'));
    }

    /**
     * Rapport des produits
     */
    public function products(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Top produits vendus
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();

        // Produits sans vente
        $noSalesProducts = Product::active()
            ->whereDoesntHave('orderItems', function ($query) use ($startDate, $endDate) {
                $query->whereHas('order', function ($q) use ($startDate, $endDate) {
                    $q->where('payment_status', 'paid')
                        ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
                });
            })
            ->select('id', 'name', 'sku', 'stock_quantity', 'sale_price')
            ->take(20)
            ->get();

        // Ventes par catégorie
        $categoryStats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.reports.products', compact('topProducts', 'noSalesProducts', 'categoryStats', 'startDate', 'endDate'));
    }

    /**
     * Rapport des clients
     */
    public function customers(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Nouveaux clients
        $newCustomers = Customer::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->count();

        // Clients avec commandes
        $activeCustomers = Customer::whereHas('orders', function ($query) use ($startDate, $endDate) {
            $query->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
        })->count();

        // Top clients
        $topCustomers = Customer::withCount(['orders' => function ($query) use ($startDate, $endDate) {
            $query->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
        }])
            ->withSum(['orders' => function ($query) use ($startDate, $endDate) {
                $query->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
            }], 'total')
            ->having('orders_count', '>', 0)
            ->orderByDesc('orders_sum_total')
            ->take(20)
            ->get();

        // Répartition géographique
        $geoStats = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                'shipping_city',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('shipping_city')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        return view('admin.reports.customers', compact(
            'newCustomers',
            'activeCustomers',
            'topCustomers',
            'geoStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Rapport du stock
     */
    public function stock()
    {
        // Produits en rupture
        $outOfStock = Product::active()
            ->where('stock_quantity', 0)
            ->where('track_stock', true)
            ->select('id', 'name', 'sku', 'stock_quantity')
            ->get();

        // Produits en alerte stock
        $lowStock = Product::active()
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'name', 'sku', 'stock_quantity', 'stock_alert_threshold')
            ->get();

        // Valeur du stock
        $stockValue = Product::active()
            ->select(
                DB::raw('SUM(stock_quantity * cost_price) as cost_value'),
                DB::raw('SUM(stock_quantity * sale_price) as sale_value'),
                DB::raw('SUM(stock_quantity) as total_units')
            )
            ->first();

        // Rotation du stock (derniers 30 jours)
        $stockRotation = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select(
                'products.id',
                'products.name',
                'products.stock_quantity',
                DB::raw('SUM(order_items.quantity) as sold_30d')
            )
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->orderByDesc('sold_30d')
            ->take(20)
            ->get()
            ->map(function ($product) {
                $product->days_of_stock = $product->sold_30d > 0
                    ? round(($product->stock_quantity / ($product->sold_30d / 30)), 1)
                    : null;
                return $product;
            });

        return view('admin.reports.stock', compact('outOfStock', 'lowStock', 'stockValue', 'stockRotation'));
    }

    /**
     * Calcule le pourcentage de croissance
     */
    protected function calculateGrowth($current, $previous): array
    {
        if ($previous == 0) {
            return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'neutral'];
        }

        $percentage = (($current - $previous) / $previous) * 100;

        return [
            'value' => abs(round($percentage, 1)),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
        ];
    }
}

