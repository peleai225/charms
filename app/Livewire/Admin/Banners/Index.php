<?php

namespace App\Livewire\Admin\Banners;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Banner;

#[Layout('layouts.admin-livewire')]
#[Title('Bannières')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $position = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPosition(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
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
        $this->position = '';
        $this->type = '';
        $this->status = '';
        $this->resetPage();
    }

    public function toggleActive(int $bannerId): void
    {
        $banner = Banner::findOrFail($bannerId);
        $banner->update(['is_active' => !$banner->is_active]);

        session()->flash('success', 'Bannière ' . ($banner->is_active ? 'activée' : 'désactivée') . '.');
    }

    public function render()
    {
        $query = Banner::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($this->position) {
            $query->where('position', $this->position);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('is_active', $this->status === 'active');
        }

        $banners = $query->orderBy('position')->orderBy('order')->paginate(20);

        return view('livewire.admin.banners.index', [
            'banners' => $banners,
            'positions' => Banner::POSITIONS,
            'types' => Banner::TYPES,
        ]);
    }
}
