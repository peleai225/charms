<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Tableau de bord stock
     */
    public function index()
    {
        // Statistiques globales
        $stats = [
            'total_products' => Product::active()->count(),
            'total_units' => Product::active()->sum('stock_quantity'),
            'stock_value' => Product::active()->selectRaw('SUM(stock_quantity * COALESCE(cost_price, sale_price)) as value')->value('value') ?? 0,
            'out_of_stock' => Product::active()->where('stock_quantity', 0)->where('track_stock', true)->count(),
            'low_stock' => Product::active()->where('track_stock', true)->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')->where('stock_quantity', '>', 0)->count(),
        ];

        // Produits en alerte
        $alertProducts = Product::active()
            ->where('track_stock', true)
            ->where(function ($q) {
                $q->where('stock_quantity', 0)
                    ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
            })
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();

        // Derniers mouvements
        $recentMovements = StockMovement::with(['product', 'variant', 'user'])
            ->latest()
            ->take(15)
            ->get();

        return view('admin.stock.index', compact('stats', 'alertProducts', 'recentMovements'));
    }

    /**
     * Liste des mouvements de stock
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'variant', 'user', 'supplier']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->latest()->paginate(30)->withQueryString();
        $products = Product::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.stock.movements', compact('movements', 'products'));
    }

    /**
     * Formulaire d'ajout de mouvement
     */
    public function createMovement()
    {
        $products = Product::active()->with('variants')->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.stock.create-movement', compact('products', 'suppliers'));
    }

    /**
     * Enregistrer un mouvement de stock
     */
    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment,return,transfer',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $variant = isset($validated['variant_id']) ? ProductVariant::find($validated['variant_id']) : null;

            // Calculer la quantité signée
            $signedQty = in_array($validated['type'], ['in', 'return']) 
                ? $validated['quantity'] 
                : -$validated['quantity'];

            // Créer le mouvement
            StockMovement::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'type' => $validated['type'],
                'quantity' => $signedQty,
                'quantity_before' => $variant ? $variant->stock_quantity : $product->stock_quantity,
                'quantity_after' => ($variant ? $variant->stock_quantity : $product->stock_quantity) + $signedQty,
                'reason' => $validated['reason'],
                'reference' => $validated['reference'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'unit_cost' => $validated['unit_cost'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Mettre à jour le stock
            if ($variant) {
                $variant->increment('stock_quantity', $signedQty);
            } else {
                $product->increment('stock_quantity', $signedQty);
            }
        });

        return redirect()
            ->route('admin.stock.movements')
            ->with('success', 'Mouvement de stock enregistré.');
    }

    /**
     * Page de réception fournisseur
     */
    public function reception()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()->with('variants')->orderBy('name')->get();

        return view('admin.stock.reception', compact('suppliers', 'products'));
    }

    /**
     * Enregistrer une réception
     */
    public function storeReception(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

                StockMovement::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'quantity_before' => $variant ? $variant->stock_quantity : $product->stock_quantity,
                    'quantity_after' => ($variant ? $variant->stock_quantity : $product->stock_quantity) + $item['quantity'],
                    'reason' => 'Réception fournisseur',
                    'reference' => $validated['reference'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);

                if ($variant) {
                    $variant->increment('stock_quantity', $item['quantity']);
                } else {
                    $product->increment('stock_quantity', $item['quantity']);
                }

                // Mettre à jour le prix d'achat si fourni
                if (!empty($item['unit_cost'])) {
                    if ($variant) {
                        $variant->update(['cost_price' => $item['unit_cost']]);
                    } else {
                        $product->update(['cost_price' => $item['unit_cost']]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.stock.index')
            ->with('success', 'Réception enregistrée avec succès.');
    }

    /**
     * Page d'inventaire
     */
    public function inventory(Request $request)
    {
        $query = Product::active()->with('variants');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(50)->withQueryString();

        return view('admin.stock.inventory', compact('products'));
    }

    /**
     * Ajustement d'inventaire
     */
    public function adjustInventory(Request $request)
    {
        $validated = $request->validate([
            'adjustments' => 'required|array',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.variant_id' => 'nullable|exists:product_variants,id',
            'adjustments.*.new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['adjustments'] as $adj) {
                $product = Product::findOrFail($adj['product_id']);
                $variant = isset($adj['variant_id']) ? ProductVariant::find($adj['variant_id']) : null;

                $currentQty = $variant ? $variant->stock_quantity : $product->stock_quantity;
                $newQty = $adj['new_quantity'];
                $diff = $newQty - $currentQty;

                if ($diff !== 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'type' => 'adjustment',
                        'quantity' => $diff,
                        'quantity_before' => $currentQty,
                        'quantity_after' => $newQty,
                        'reason' => $validated['reason'],
                        'user_id' => auth()->id(),
                    ]);

                    if ($variant) {
                        $variant->update(['stock_quantity' => $newQty]);
                    } else {
                        $product->update(['stock_quantity' => $newQty]);
                    }
                }
            }
        });

        return back()->with('success', 'Inventaire ajusté avec succès.');
    }

    /**
     * Alertes de stock
     */
    public function alerts()
    {
        $outOfStock = Product::active()
            ->where('track_stock', true)
            ->where('stock_quantity', 0)
            ->with('category')
            ->get();

        $lowStock = Product::active()
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->get();

        return view('admin.stock.alerts', compact('outOfStock', 'lowStock'));
    }
}

