<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::query()->with('categories');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->valid(),
                'expired' => $query->where('expires_at', '<', now()),
                'scheduled' => $query->where('starts_at', '>', now()),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        $promotions = $query
            ->withCount('orderItems')
            ->withSum('orderItems', 'discount_amount')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $promotions->getCollection()->transform(fn (Promotion $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'lot_qty' => $p->lot_qty,
            'lot_price' => (float) $p->lot_price,
            'lot_price_fmt' => number_format((float) $p->lot_price, 0, ',', ' ').' F CFA',
            'band_fmt' => $this->formatBand($p),
            'categories' => $p->categories->pluck('name')->all(),
            'max_lots_per_order' => $p->max_lots_per_order,
            'starts_at_fmt' => $p->starts_at?->format('d/m/Y'),
            'expires_at_fmt' => $p->expires_at?->format('d/m/Y'),
            'is_active' => $p->is_active,
            'status' => $p->status,
            'lines_count' => $p->order_items_count,
            'discount_total_fmt' => number_format((float) ($p->order_items_sum_discount_amount ?? 0), 0, ',', ' ').' F CFA',
        ]);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Index', [
            'promotions' => $promotions,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $promotion = Promotion::create($this->attributes($validated));
        $promotion->categories()->sync($validated['category_ids'] ?? []);
        $promotion->products()->sync($validated['product_ids'] ?? []);

        ActivityLog::logCreated($promotion, "Offre « {$promotion->name} » créée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre créée.');
    }

    public function edit(Promotion $promotion)
    {
        $promotion->load(['categories', 'products']);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Edit', array_merge($this->formOptions(), [
            'promotion' => [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
                'lot_qty' => $promotion->lot_qty,
                'lot_price' => (float) $promotion->lot_price,
                'price_min' => $promotion->price_min !== null ? (float) $promotion->price_min : null,
                'price_max' => $promotion->price_max !== null ? (float) $promotion->price_max : null,
                'max_lots_per_order' => $promotion->max_lots_per_order,
                'include_descendants' => $promotion->include_descendants,
                'stackable_with_coupons' => $promotion->stackable_with_coupons,
                'is_active' => $promotion->is_active,
                'priority' => $promotion->priority,
                'starts_at' => $promotion->starts_at?->format('Y-m-d'),
                'expires_at' => $promotion->expires_at?->format('Y-m-d'),
                'category_ids' => $promotion->categories->pluck('id')->all(),
                'product_ids' => $promotion->products->pluck('id')->all(),
            ],
        ]));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate($this->rules());
        $oldValues = $promotion->getOriginal();

        $promotion->update($this->attributes($validated));
        $promotion->categories()->sync($validated['category_ids'] ?? []);
        $promotion->products()->sync($validated['product_ids'] ?? []);

        ActivityLog::logUpdated($promotion, $oldValues, "Offre « {$promotion->name} » modifiée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        $name = $promotion->name;
        $promotion->delete();

        ActivityLog::logDeleted($promotion, "Offre « {$name} » supprimée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre supprimée.');
    }

    /**
     * Impact d'une offre en cours de saisie sur la marge, calculé depuis la base.
     * Les produits dont purchase_price n'est pas renseigné sont comptés à part :
     * leur marge est inconnue, pas estimée.
     */
    public function marginPreview(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => 'array',
            'category_ids.*' => 'integer',
            'product_ids' => 'array',
            'product_ids.*' => 'integer',
            'include_descendants' => 'boolean',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'lot_qty' => 'required|integer|min:2',
            'lot_price' => 'required|numeric|min:0',
        ]);

        $categoryIds = $this->expandCategories(
            $validated['category_ids'] ?? [],
            $validated['include_descendants'] ?? true
        );

        $products = Product::where('status', 'active')
            ->where(function ($query) use ($categoryIds, $validated) {
                $query->whereIn('category_id', $categoryIds ?: [0]);

                if (! empty($validated['product_ids'])) {
                    $query->orWhereIn('id', $validated['product_ids']);
                }
            })
            ->when($validated['price_min'] ?? null, fn ($q, $min) => $q->where('sale_price', '>=', $min))
            ->when($validated['price_max'] ?? null, fn ($q, $max) => $q->where('sale_price', '<=', $max))
            ->get(['id', 'name', 'sale_price', 'purchase_price']);

        $unitPriceInLot = (float) $validated['lot_price'] / (int) $validated['lot_qty'];

        $costed = $products->filter(fn (Product $p) => (float) $p->purchase_price > 0);
        $unknown = $products->count() - $costed->count();

        $catalogMargin = $costed->isEmpty() ? null : round($costed->avg(
            fn (Product $p) => (float) $p->sale_price - (float) $p->purchase_price
        ));

        $lotMargin = $costed->isEmpty() ? null : round($costed->avg(
            fn (Product $p) => $unitPriceInLot - (float) $p->purchase_price
        ));

        return response()->json([
            'eligible_count' => $products->count(),
            'without_purchase_price' => $unknown,
            'unit_price_in_lot' => round($unitPriceInLot),
            'catalog_margin' => $catalogMargin,
            'lot_margin' => $lotMargin,
            'below_cost' => $costed->contains(
                fn (Product $p) => $unitPriceInLot < (float) $p->purchase_price
            ),
            'products' => $products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sale_price' => (float) $p->sale_price,
                'purchase_price' => (float) $p->purchase_price > 0 ? (float) $p->purchase_price : null,
                'lot_margin' => (float) $p->purchase_price > 0
                    ? round($unitPriceInLot - (float) $p->purchase_price)
                    : null,
            ])->values(),
        ]);
    }

    // ========== INTERNE ==========

    /**
     * @param  int[]  $ids
     * @return int[]
     */
    private function expandCategories(array $ids, bool $includeDescendants): array
    {
        if ($ids === []) {
            return [];
        }

        if (! $includeDescendants) {
            return $ids;
        }

        $expanded = [];

        foreach (Category::whereIn('id', $ids)->get() as $category) {
            $expanded = array_merge($expanded, $category->getAllChildrenIds());
        }

        return array_values(array_unique($expanded));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lot_qty' => 'required|integer|min:2|max:999',
            'lot_price' => 'required|numeric|min:0',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'max_lots_per_order' => 'nullable|integer|min:1',
            'include_descendants' => 'boolean',
            'stackable_with_coupons' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            // Une offre sans cible n'est jamais éligible : on la refuse ici
            // plutôt que de la laisser dormir sans effet en production.
            'category_ids' => 'array|required_without:product_ids',
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')],
            'product_ids' => 'array',
            'product_ids.*' => ['integer', Rule::exists('products', 'id')],
        ];
    }

    private function attributes(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => 'lot',
            'lot_qty' => $validated['lot_qty'],
            'lot_price' => $validated['lot_price'],
            'price_min' => $validated['price_min'] ?? null,
            'price_max' => $validated['price_max'] ?? null,
            'max_lots_per_order' => $validated['max_lots_per_order'] ?? null,
            'include_descendants' => $validated['include_descendants'] ?? true,
            'stackable_with_coupons' => $validated['stackable_with_coupons'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'priority' => $validated['priority'] ?? 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ];
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::active()
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'parent_id' => $c->parent_id,
                ]),
            'products' => Product::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'sale_price'])
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->sale_price,
                ]),
        ];
    }

    private function formatBand(Promotion $promotion): string
    {
        if ($promotion->price_min === null && $promotion->price_max === null) {
            return 'Tous les prix';
        }

        $min = $promotion->price_min !== null ? number_format((float) $promotion->price_min, 0, ',', ' ') : '0';
        $max = $promotion->price_max !== null ? number_format((float) $promotion->price_max, 0, ',', ' ') : '∞';

        return $min.' – '.$max.' F';
    }
}
