<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use RuntimeException;

class AiProductController extends Controller
{
    /**
     * Page : créer des produits à partir de plusieurs photos
     */
    public function importImages(AiService $ai)
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Products/ImportImages', [
            'pageTitle' => 'Créer des produits par photos',
            'aiConfigured' => $ai->isConfigured(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Analyse une photo envoyée → suggestion (nom, descriptions, catégorie)
     */
    public function analyzeImage(Request $request, AiService $ai)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');

        return $this->respond(fn () => $ai->suggestProduct(
            file_get_contents($file->getRealPath()),
            $file->getMimeType(),
            $request->input('name')
        ));
    }

    /**
     * Suggestion pour un produit existant (photo principale + nom)
     */
    public function suggestForProduct(Product $product, AiService $ai)
    {
        $product->load('images');
        $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        $binary = null;
        $mime = null;
        if ($image && Storage::disk('public')->exists($image->path)) {
            $binary = Storage::disk('public')->get($image->path);
            $mime = Storage::disk('public')->mimeType($image->path);
        }

        return $this->respond(fn () => $ai->suggestProduct($binary, $mime, $product->name)
            + ['product_id' => $product->id, 'current_category_id' => $product->category_id]);
    }

    /**
     * Appliquer des catégories différentes à plusieurs produits
     */
    public function applyCategories(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.id' => 'required|integer|exists:products,id',
            'assignments.*.category_id' => 'required|integer|exists:categories,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['assignments'] as $a) {
                Product::where('id', $a['id'])->update(['category_id' => $a['category_id']]);
            }
        });

        return back()->with('success', count($validated['assignments']).' produit(s) rangé(s).');
    }

    /**
     * Créer un produit (brouillon par défaut) à partir d'une photo + champs validés
     */
    public function storeFromImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,active',
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'produit';
        $slug = $baseSlug;
        $i = 1;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        do {
            $sku = 'PRD-'.strtoupper(Str::random(8));
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        $product = DB::transaction(function () use ($validated, $request, $slug, $sku) {
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'sku' => $sku,
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'sale_price' => $validated['sale_price'],
                'purchase_price' => $validated['purchase_price'] ?? 0,
                'tax_rate' => 0,
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'stock_alert_threshold' => 5,
                'track_stock' => true,
                'status' => $validated['status'],
            ]);

            $path = $request->file('image')->store('products/'.$product->id, 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_primary' => true,
                'position' => 0,
            ]);

            return $product;
        });

        ActivityLog::logCreated($product, "Produit {$product->name} créé par photo (IA)");

        return response()->json([
            'id' => $product->id,
            'edit_url' => route('admin.products.edit', $product),
        ]);
    }

    protected function respond(callable $fn)
    {
        try {
            return response()->json($fn());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['message' => "Le service IA ne répond pas. Réessayez dans un instant."], 504);
        }
    }

    protected function categoryOptions()
    {
        return Category::with('parent.parent')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => str_replace(' > ', ' › ', $c->full_path)])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
