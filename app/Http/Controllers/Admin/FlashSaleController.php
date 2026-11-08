<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Back-office des ventes flash : une remise en % sur une liste de produits,
 * entre deux dates-heures. Le prix est appliqué par App\Services\FlashSalePricing.
 */
class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = FlashSale::query()->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        match ($request->input('status')) {
            FlashSale::STATUS_RUNNING => $query->current(),
            FlashSale::STATUS_SCHEDULED => $query->where('is_active', true)->where('starts_at', '>', now()),
            FlashSale::STATUS_ENDED => $query->where('is_active', true)->where('ends_at', '<=', now()),
            FlashSale::STATUS_INACTIVE => $query->where('is_active', false),
            default => null,
        };

        $sales = $query->latest('starts_at')->orderByDesc('id')->paginate(20)->withQueryString();

        $sales->getCollection()->transform(fn (FlashSale $sale) => [
            'id' => $sale->id,
            'name' => $sale->name,
            'discount_percent' => $sale->discount_percent,
            'starts_at_fmt' => $sale->starts_at->format('d/m/Y H:i'),
            'ends_at_fmt' => $sale->ends_at->format('d/m/Y H:i'),
            'status' => $sale->status,
            'products_count' => $sale->products_count,
        ]);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/FlashSales/Index', [
            'sales' => $sales,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/FlashSales/Create', ['sale' => null, 'timezone' => config('app.timezone')]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $sale = FlashSale::create($this->attributes($validated));
        $sale->products()->sync($validated['product_ids']);

        ActivityLog::logCreated($sale, "Vente flash « {$sale->name} » créée");

        return redirect()
            ->route('admin.flash-sales.index')
            ->with('success', 'Vente flash créée.');
    }

    public function edit(FlashSale $flashSale)
    {
        $flashSale->load(['products.images']);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/FlashSales/Edit', [
            'timezone' => config('app.timezone'),
            'sale' => [
                'id' => $flashSale->id,
                'name' => $flashSale->name,
                'discount_percent' => $flashSale->discount_percent,
                'starts_at' => $flashSale->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => $flashSale->ends_at->format('Y-m-d\TH:i'),
                'is_active' => $flashSale->is_active,
                'status' => $flashSale->status,
                'products' => $flashSale->products->map(fn (Product $p) => $this->productOption($p))->values(),
            ],
        ]);
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $validated = $this->validated($request);
        $oldValues = $flashSale->getOriginal();

        $flashSale->update($this->attributes($validated));
        $flashSale->products()->sync($validated['product_ids']);

        ActivityLog::logUpdated($flashSale, $oldValues, "Vente flash « {$flashSale->name} » modifiée");

        return redirect()
            ->route('admin.flash-sales.index')
            ->with('success', 'Vente flash mise à jour.');
    }

    /**
     * Les paniers qui portent un prix de cette vente reviennent au prix normal
     * à leur prochain calcul (cart_items.flash_sale_id n'a pas de clé étrangère).
     */
    public function destroy(FlashSale $flashSale)
    {
        $name = $flashSale->name;
        $flashSale->delete();

        ActivityLog::logDeleted($flashSale, "Vente flash « {$name} » supprimée");

        return redirect()
            ->route('admin.flash-sales.index')
            ->with('success', 'Vente flash supprimée.');
    }

    /**
     * Recherche de produits actifs pour le formulaire (nom ou SKU). Sans terme,
     * rend les derniers produits modifiés.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        $products = Product::active()
            ->with('images')
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")))
            ->latest('updated_at')
            ->take(20)
            ->get();

        return response()->json([
            'products' => $products->map(fn (Product $p) => $this->productOption($p))->values(),
        ]);
    }

    // ========== INTERNE ==========

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'discount_percent' => 'required|integer|min:1|max:90',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => ['integer', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
        ], [
            'name.required' => 'Donnez un nom à la vente.',
            'discount_percent.required' => 'Indiquez la remise.',
            'discount_percent.integer' => 'La remise est un nombre entier de pourcents.',
            'discount_percent.min' => 'La remise doit être d’au moins 1 %.',
            'discount_percent.max' => 'La remise ne peut pas dépasser 90 %.',
            'starts_at.required' => 'Indiquez le début de la vente.',
            'starts_at.date' => 'Date de début invalide.',
            'ends_at.required' => 'Indiquez la fin de la vente.',
            'ends_at.date' => 'Date de fin invalide.',
            'ends_at.after' => 'La fin doit être après le début.',
            'product_ids.required' => 'Ajoutez au moins un produit.',
            'product_ids.min' => 'Ajoutez au moins un produit.',
            'product_ids.*.exists' => 'Ce produit n’existe plus.',
            'product_ids.*.distinct' => 'Ce produit est en double.',
        ]);
    }

    private function attributes(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'discount_percent' => (int) $validated['discount_percent'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_active' => $validated['is_active'] ?? true,
        ];
    }

    private function productOption(Product $product): array
    {
        $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->sale_price,
            'status' => $product->status,
            'image' => $image ? asset('storage/'.$image->path) : null,
        ];
    }
}
