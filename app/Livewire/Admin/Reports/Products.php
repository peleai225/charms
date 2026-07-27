<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin-livewire')]
#[Title('Rapport produits')]
class Products extends Component
{
    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    public function mount(): void
    {
        if (! $this->startDate) $this->startDate = now()->startOfMonth()->format('Y-m-d');
        if (! $this->endDate)   $this->endDate   = now()->format('Y-m-d');
    }

    public function updated(): void
    {
        $this->dispatch('chart-updated', data: $this->categoryChartData());
    }

    public function render()
    {
        $endOfDay      = Carbon::parse($this->endDate)->endOfDay();
        $topProducts   = $this->getTopProducts($endOfDay);
        $categoryStats = $this->getCategoryStats($endOfDay);
        $noSales       = $this->getNoSalesProducts($endOfDay);

        return view('livewire.admin.reports.products', [
            'topProducts'    => $topProducts,
            'categoryStats'  => $categoryStats,
            'noSalesProducts'=> $noSales,
            'chartData'      => $this->categoryChartData(),
        ]);
    }

    private function getTopProducts($endOfDay)
    {
        return DB::table('order_items')
            ->join('orders',    'order_items.order_id',    '=', 'orders.id')
            ->join('products',  'order_items.product_id',  '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$this->startDate, $endOfDay])
            ->select(
                'products.id', 'products.name', 'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();
    }

    private function getCategoryStats($endOfDay)
    {
        return DB::table('order_items')
            ->join('orders',     'order_items.order_id',    '=', 'orders.id')
            ->join('products',   'order_items.product_id',  '=', 'products.id')
            ->join('categories', 'products.category_id',    '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$this->startDate, $endOfDay])
            ->select(
                'categories.id', 'categories.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();
    }

    private function getNoSalesProducts($endOfDay)
    {
        return Product::active()
            ->whereDoesntHave('orderItems', function ($q) use ($endOfDay) {
                $q->whereHas('order', fn($o) => $o
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$this->startDate, $endOfDay])
                );
            })
            ->select('id', 'name', 'sku', 'stock_quantity', 'sale_price')
            ->take(20)
            ->get();
    }

    private function categoryChartData(): array
    {
        $data = $this->getCategoryStats(Carbon::parse($this->endDate)->endOfDay());
        return [
            'labels'   => $data->pluck('name')->toArray(),
            'revenues' => $data->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
        ];
    }
}
