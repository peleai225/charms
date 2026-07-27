<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $groupBy   = in_array($request->get('group_by'), ['day', 'week', 'month'])
            ? $request->get('group_by') : 'day';

        $dateFormat = match ($groupBy) {
            'week'  => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $salesData = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(discount_amount) as discounts'),
                DB::raw('AVG(total) as average_order')
            )
            ->groupBy('period')->orderBy('period')->get();

        $totals = [
            'orders'    => $salesData->sum('orders_count'),
            'revenue'   => $salesData->sum('revenue'),
            'discounts' => $salesData->sum('discounts'),
            'average'   => $salesData->avg('average_order') ?? 0,
        ];

        $daysDiff      = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        $previousStart = Carbon::parse($startDate)->subDays($daysDiff + 1)->format('Y-m-d');
        $previousEnd   = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        $prev = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$previousStart, Carbon::parse($previousEnd)->endOfDay()])
            ->selectRaw('COUNT(*) as orders, SUM(total) as revenue')->first();

        $comparison = [
            'orders'  => $this->calculateGrowth($totals['orders'],  $prev->orders  ?? 0),
            'revenue' => $this->calculateGrowth($totals['revenue'], $prev->revenue ?? 0),
        ];

        $chartData = [
            'labels'      => $salesData->pluck('period')->toArray(),
            'revenues'    => $salesData->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
            'orderCounts' => $salesData->pluck('orders_count')->map(fn($v) => (int) $v)->toArray(),
        ];

        return Inertia::render('Admin/Reports/Sales', [
            'salesData'  => $salesData,
            'totals'     => $totals,
            'comparison' => $comparison,
            'chartData'  => $chartData,
            'filters'    => compact('startDate', 'endDate', 'groupBy'),
        ]);
    }

    public function products(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $endOfDay  = Carbon::parse($endDate)->endOfDay();

        $topProducts = DB::table('order_items')
            ->join('orders',    'order_items.order_id',    '=', 'orders.id')
            ->join('products',  'order_items.product_id',  '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endOfDay])
            ->select(
                'products.id', 'products.name', 'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('revenue')->limit(50)->get();

        $categoryStats = DB::table('order_items')
            ->join('orders',     'order_items.order_id',    '=', 'orders.id')
            ->join('products',   'order_items.product_id',  '=', 'products.id')
            ->join('categories', 'products.category_id',    '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endOfDay])
            ->select(
                'categories.id', 'categories.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('categories.id', 'categories.name')->orderByDesc('revenue')->get();

        $noSalesProducts = Product::active()
            ->whereDoesntHave('orderItems', fn($q) => $q->whereHas('order', fn($o) => $o
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endOfDay])
            ))
            ->select('id', 'name', 'sku', 'stock_quantity', 'sale_price')
            ->take(20)->get();

        $chartData = [
            'labels'   => $categoryStats->pluck('name')->toArray(),
            'revenues' => $categoryStats->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
        ];

        return Inertia::render('Admin/Reports/Products', [
            'topProducts'     => $topProducts,
            'categoryStats'   => $categoryStats,
            'noSalesProducts' => $noSalesProducts,
            'chartData'       => $chartData,
            'filters'         => compact('startDate', 'endDate'),
        ]);
    }

    public function stock()
    {
        Inertia::setRootView('layouts.admin-inertia');

        $outOfStock = Product::active()
            ->where('stock_quantity', 0)->where('track_stock', true)
            ->select('id', 'name', 'sku', 'stock_quantity')->get();

        $lowStock = Product::active()
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'name', 'sku', 'stock_quantity', 'stock_alert_threshold')->get();

        $stockValue = Product::active()
            ->selectRaw('SUM(stock_quantity * cost_price) as cost_value, SUM(stock_quantity * sale_price) as sale_value, SUM(stock_quantity) as total_units')
            ->first();

        $stockRotation = DB::table('order_items')
            ->join('orders',   'order_items.order_id',   '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('products.id', 'products.name', 'products.stock_quantity', DB::raw('SUM(order_items.quantity) as sold_30d'))
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->orderByDesc('sold_30d')->take(20)->get()
            ->map(function ($p) {
                $p->days_of_stock = $p->sold_30d > 0
                    ? round(($p->stock_quantity / ($p->sold_30d / 30)), 1)
                    : null;
                return $p;
            });

        return Inertia::render('Admin/Reports/Stock', compact('outOfStock', 'lowStock', 'stockValue', 'stockRotation'));
    }

    /**
     * Rapport des clients
     */
    public function customers(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date', now()->format('Y-m-d'));
        $endOfDay  = Carbon::parse($endDate)->endOfDay();

        // Nouveaux clients
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endOfDay])->count();

        // Clients avec commandes
        $activeCustomers = Customer::whereHas('orders', function ($query) use ($startDate, $endOfDay) {
            $query->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endOfDay]);
        })->count();

        // Top clients
        $topCustomers = Customer::withCount(['orders' => function ($query) use ($startDate, $endOfDay) {
            $query->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endOfDay]);
        }])
            ->withSum(['orders' => function ($query) use ($startDate, $endOfDay) {
                $query->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$startDate, $endOfDay]);
            }], 'total')
            ->having('orders_count', '>', 0)
            ->orderByDesc('orders_sum_total')
            ->take(20)
            ->get()
            ->map(fn ($c) => [
                'id'               => $c->id,
                'full_name'        => trim($c->first_name . ' ' . $c->last_name),
                'email'            => $c->user?->email,
                'orders_count'     => $c->orders_count,
                'orders_sum_total' => $c->orders_sum_total ?? 0,
            ]);

        // Répartition géographique
        $geoStats = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endOfDay])
            ->select(
                'shipping_city',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('shipping_city')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        $avgRevenue = $activeCustomers > 0 && $topCustomers->sum('orders_sum_total') > 0
            ? round($topCustomers->sum('orders_sum_total') / $activeCustomers)
            : 0;

        $chartData = [
            'labels'   => $geoStats->pluck('shipping_city')->map(fn ($v) => $v ?? 'Non renseigné')->toArray(),
            'revenues' => $geoStats->pluck('revenue')->map(fn ($v) => (float) $v)->toArray(),
        ];

        return Inertia::render('Admin/Reports/Customers', [
            'newCustomers'    => $newCustomers,
            'activeCustomers' => $activeCustomers,
            'avgRevenue'      => $avgRevenue,
            'topCustomers'    => $topCustomers->values(),
            'geoStats'        => $geoStats,
            'chartData'       => $chartData,
            'filters'         => compact('startDate', 'endDate'),
        ]);
    }

    /**
     * Export CSV du rapport ventes
     */
    public function exportSalesCsv(Request $request)
    {
        $data = $this->getSalesDataForExport($request);
        $filename = 'rapport-ventes-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM pour Excel
            fputcsv($out, ['Période', 'Commandes', 'Chiffre d\'affaires', 'Réductions', 'Panier moyen'], ';');
            foreach ($data['rows'] as $row) {
                fputcsv($out, $row, ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['TOTAL', $data['totals']['orders'], $data['totals']['revenue'], $data['totals']['discounts'], $data['totals']['average']], ';');
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export PDF du rapport ventes
     */
    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->getSalesDataForExport($request);

        $pdf = Pdf::loadView('admin.reports.exports.sales-pdf', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->download('rapport-ventes-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function getSalesDataForExport(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

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

        $rows = $salesData->map(fn ($r) => [
            $r->period,
            $r->orders_count,
            number_format($r->revenue ?? 0, 0, ',', ' '),
            number_format($r->discounts ?? 0, 0, ',', ' '),
            number_format($r->average_order ?? 0, 0, ',', ' '),
        ])->toArray();

        return [
            'rows' => $rows,
            'totals' => [
                'orders' => $salesData->sum('orders_count'),
                'revenue' => number_format($salesData->sum('revenue') ?? 0, 0, ',', ' '),
                'discounts' => number_format($salesData->sum('discounts') ?? 0, 0, ',', ' '),
                'average' => number_format($salesData->avg('average_order') ?? 0, 0, ',', ' '),
            ],
        ];
    }

    /**
     * Export CSV du rapport produits
     */
    public function exportProductsCsv(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->select(
                'products.name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        $filename = 'rapport-produits-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($topProducts) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Produit', 'SKU', 'Catégorie', 'Quantité vendue', 'Chiffre d\'affaires'], ';');
            foreach ($topProducts as $p) {
                fputcsv($out, [$p->name, $p->sku ?? '', $p->category_name ?? '', $p->quantity_sold ?? 0, number_format($p->revenue ?? 0, 0, ',', ' ')], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export CSV du rapport stock
     */
    public function exportStockCsv()
    {
        $products = Product::active()
            ->with('category')
            ->select('id', 'name', 'sku', 'stock_quantity', 'stock_alert_threshold', 'cost_price', 'sale_price')
            ->orderBy('stock_quantity')
            ->get();

        $filename = 'rapport-stock-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Produit', 'SKU', 'Catégorie', 'Stock', 'Seuil alerte', 'Valeur stock'], ';');
            foreach ($products as $p) {
                $value = ($p->cost_price ?? 0) * $p->stock_quantity;
                fputcsv($out, [$p->name, $p->sku ?? '', $p->category?->name ?? '', $p->stock_quantity, $p->stock_alert_threshold ?? '-', number_format($value, 0, ',', ' ')], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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

