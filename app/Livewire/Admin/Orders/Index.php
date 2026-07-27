<?php

namespace App\Livewire\Admin\Orders;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Order;

#[Layout('layouts.admin-livewire')]
#[Title('Commandes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $paymentStatus = '';

    #[Url]
    public string $dateRange = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->paymentStatus = '';
        $this->dateRange = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Order::query()
            ->with(['customer', 'items'])
            ->withCount('items');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('billing_email', 'like', "%{$search}%")
                    ->orWhere('billing_first_name', 'like', "%{$search}%")
                    ->orWhere('billing_last_name', 'like', "%{$search}%");
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->paymentStatus) {
            $query->where('payment_status', $this->paymentStatus);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->dateRange) {
            match ($this->dateRange) {
                'today' => $query->whereDate('created_at', today()),
                'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => null,
            };
        }

        $orders = $query->latest()->paginate(20);

        $stats = [
            'pending'     => Order::pending()->count(),
            'processing'  => Order::processing()->count(),
            'shipped'     => Order::shipped()->count(),
            'today_total' => Order::whereDate('created_at', today())->sum('total'),
            'today_count' => Order::whereDate('created_at', today())->count(),
        ];

        return view('livewire.admin.orders.index', [
            'orders' => $orders,
            'stats'  => $stats,
        ]);
    }
}
