<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin-livewire')]
#[Title('Rapport des ventes')]
class Sales extends Component
{
    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    #[Url]
    public string $groupBy = 'day';

    public function mount(): void
    {
        if (! $this->startDate) $this->startDate = now()->startOfMonth()->format('Y-m-d');
        if (! $this->endDate)   $this->endDate   = now()->format('Y-m-d');
    }

    public function updated(): void
    {
        $this->dispatch('chart-updated', chart: $this->chartData());
    }

    public function render()
    {
        $salesData = $this->getSalesData();
        $totals    = $this->getTotals($salesData);
        $comparison = $this->getComparison($totals);

        return view('livewire.admin.reports.sales', [
            'salesData'  => $salesData,
            'totals'     => $totals,
            'comparison' => $comparison,
            'chartData'  => $this->chartData(),
        ]);
    }

    private function getSalesData()
    {
        $dateFormat = match ($this->groupBy) {
            'week'  => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$this->startDate, Carbon::parse($this->endDate)->endOfDay()])
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
    }

    private function getTotals($salesData): array
    {
        return [
            'orders'    => $salesData->sum('orders_count'),
            'revenue'   => $salesData->sum('revenue'),
            'discounts' => $salesData->sum('discounts'),
            'average'   => $salesData->avg('average_order') ?? 0,
        ];
    }

    private function getComparison(array $totals): array
    {
        $daysDiff      = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate));
        $previousStart = Carbon::parse($this->startDate)->subDays($daysDiff + 1)->format('Y-m-d');
        $previousEnd   = Carbon::parse($this->startDate)->subDay()->format('Y-m-d');

        $prev = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$previousStart, Carbon::parse($previousEnd)->endOfDay()])
            ->selectRaw('COUNT(*) as orders, SUM(total) as revenue')
            ->first();

        return [
            'orders'  => $this->growth($totals['orders'],  $prev->orders  ?? 0),
            'revenue' => $this->growth($totals['revenue'], $prev->revenue ?? 0),
        ];
    }

    private function chartData(): array
    {
        $data = $this->getSalesData();
        return [
            'labels'      => $data->pluck('period')->toArray(),
            'revenues'    => $data->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
            'orderCounts' => $data->pluck('orders_count')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    private function growth($current, $previous): array
    {
        if ($previous == 0) {
            return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'neutral'];
        }
        $pct = (($current - $previous) / $previous) * 100;
        return ['value' => abs(round($pct, 1)), 'direction' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral')];
    }
}
