<?php

namespace App\Livewire\Admin\Refunds;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Refund;

#[Layout('layouts.admin-livewire')]
#[Title('Remboursements')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Refund::query()
            ->with(['order', 'payment', 'processedBy']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('order', fn($q) => $q->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $refunds = $query->latest()->paginate(20);

        return view('livewire.admin.refunds.index', [
            'refunds' => $refunds,
        ]);
    }
}
