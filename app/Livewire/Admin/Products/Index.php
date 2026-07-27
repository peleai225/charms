<?php

namespace App\Livewire\Admin\Products;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

#[Layout('layouts.admin-livewire')]
#[Title('Produits')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $stock = ''; // 'low' | 'out' | ''

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedStock(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['images', 'category', 'variants'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->stock === 'out', fn($q) => $q->where('stock_quantity', '<=', 0))
            ->when($this->stock === 'low', fn($q) => $q->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')->where('stock_quantity', '>', 0))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.admin.products.index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
