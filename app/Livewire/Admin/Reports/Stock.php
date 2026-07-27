<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin-livewire')]
#[Title('Rapport stock')]
class Stock extends Component
{
    public function render()
    {
        $outOfStock = Product::active()
            ->where('stock_quantity', 0)
            ->where('track_stock', true)
            ->select('id', 'name', 'sku', 'stock_quantity')
            ->get();

        $lowStock = Product::active()
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'name', 'sku', 'stock_quantity', 'stock_alert_threshold')
            ->get();

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
            ->orderByDesc('sold_30d')
            ->take(20)
            ->get()
            ->map(function ($p) {
                $p->days_of_stock = $p->sold_30d > 0
                    ? round(($p->stock_quantity / ($p->sold_30d / 30)), 1)
                    : null;
                return $p;
            });

        return view('livewire.admin.reports.stock', compact('outOfStock', 'lowStock', 'stockValue', 'stockRotation'));
    }
}
