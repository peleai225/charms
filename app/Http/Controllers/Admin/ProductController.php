<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Liste des produits
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->withCount('variants');

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->lowStock();
            } elseif ($request->stock === 'out') {
                $query->outOfStock();
            }
        }

        // Tri (whitelist pour éviter l'injection)
        $allowedSort = ['name', 'sku', 'sale_price', 'stock_quantity', 'created_at', 'updated_at', 'status'];
        $sortBy = in_array($request->get('sort'), $allowedSort) ? $request->get('sort') : 'created_at';
        $sortDir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $attributes = Attribute::with('values')->ordered()->get();
        $colors = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'couleur'))->get();

        return view('admin.products.create', compact('categories', 'attributes', 'colors'));
    }

    /**
     * Enregistrer un nouveau produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products',
            'barcode' => 'nullable|string|unique:products',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert_threshold' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,active,archived',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
            'allow_backorder' => 'boolean',
            'is_dropshipping' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Nettoyer les descriptions (supprimer les espaces multiples et les répétitions)
        if (!empty($validated['description'])) {
            $validated['description'] = preg_replace('/\s+/', ' ', trim($validated['description']));
        }
        if (!empty($validated['short_description'])) {
            $validated['short_description'] = preg_replace('/\s+/', ' ', trim($validated['short_description']));
        }
        
        // Générer un slug unique
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;
        
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new'] = $request->boolean('is_new');
        $validated['has_variants'] = $request->boolean('has_variants');
        $validated['track_stock'] = $request->boolean('track_stock', true);
        $validated['allow_backorder'] = $request->boolean('allow_backorder');
        $validated['is_dropshipping'] = $request->boolean('is_dropshipping');

        DB::beginTransaction();

        try {
            $product = Product::create($validated);

            // Upload des images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products/' . $product->id, 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'is_primary' => $index === 0,
                        'position' => $index,
                    ]);
                }
            }

            DB::commit();

            ActivityLog::logCreated($product, "Produit {$product->name} créé");

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Produit créé avec succès. Vous pouvez maintenant ajouter les variantes.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un produit
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images', 'variants.attributeValues.attribute', 'stockMovements' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Product $product)
    {
        $product->load(['images' => fn($q) => $q->orderBy('position'), 'variants.attributeValues.attribute', 'attributes']);
        $categories = Category::active()->ordered()->get();
        $attributes = Attribute::with('values')->ordered()->get();
        $colors = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'couleur'))->get();

        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'colors'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert_threshold' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,active,archived',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
            'allow_backorder' => 'boolean',
            'is_dropshipping' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Nettoyer les descriptions (supprimer les espaces multiples et les répétitions)
        if (!empty($validated['description'])) {
            $validated['description'] = preg_replace('/\s+/', ' ', trim($validated['description']));
        }
        if (!empty($validated['short_description'])) {
            $validated['short_description'] = preg_replace('/\s+/', ' ', trim($validated['short_description']));
        }
        
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new'] = $request->boolean('is_new');
        $validated['has_variants'] = $request->boolean('has_variants');
        $validated['track_stock'] = $request->boolean('track_stock', true);
        $validated['allow_backorder'] = $request->boolean('allow_backorder');
        $validated['is_dropshipping'] = $request->boolean('is_dropshipping');
        
        // Mettre à jour le slug si le nom a changé
        if ($validated['name'] !== $product->name) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            // Vérifier l'unicité en excluant le produit actuel
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $oldValues = $product->toArray();

        DB::beginTransaction();

        try {
            $product->update($validated);

            // Upload des nouvelles images
            if ($request->hasFile('images')) {
                $lastPosition = $product->images()->max('position') ?? -1;
                
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products/' . $product->id, 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'is_primary' => !$product->images()->exists() && $index === 0,
                        'position' => $lastPosition + $index + 1,
                    ]);
                }
            }

            DB::commit();

            ActivityLog::logUpdated($product, $oldValues, "Produit {$product->name} modifié");

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Produit mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un produit (ou l'archiver s'il a des commandes)
     */
    public function destroy(Product $product)
    {
        // Si le produit a des commandes, archiver au lieu de supprimer
        $hasOrders = $product->orderItems()->exists();
        
        if ($hasOrders) {
            $product->update(['status' => 'archived']);
            return back()->with('success', 'Le produit a été archivé (impossible de le supprimer car il est associé à des commandes).');
        }

        DB::beginTransaction();

        try {
            // Supprimer les images des variantes
            foreach ($product->variants as $variant) {
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }
            }

            // Supprimer les images du produit
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            // Supprimer le produit (les variantes seront supprimées en cascade)
            $productName = $product->name;
            $productId = $product->id;
            
            ActivityLog::logDeleted($product, "Produit {$productName} supprimé");
            
            $product->delete();

            // Supprimer le dossier des images du produit s'il existe
            $productImagesDir = 'products/' . $productId;
            if (Storage::disk('public')->exists($productImagesDir)) {
                Storage::disk('public')->deleteDirectory($productImagesDir);
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produit supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la suppression du produit', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Ajouter une variante
     */
    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'color_id' => 'required|exists:attribute_values,id',
            'size_id' => 'nullable|exists:attribute_values,id',
            'sku' => 'required|string|unique:product_variants',
            'stock_quantity' => 'required|integer|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // Créer la variante
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $validated['sku'],
                'stock_quantity' => $validated['stock_quantity'],
                'sale_price' => $validated['sale_price'],
                'is_active' => true,
            ]);

            // Associer la couleur
            $colorAttribute = Attribute::where('slug', 'couleur')->first();
            if ($colorAttribute) {
                DB::table('product_variant_values')->insert([
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $colorAttribute->id,
                    'attribute_value_id' => $validated['color_id'],
                ]);
            }

            // Associer la taille si fournie
            if (!empty($validated['size_id'])) {
                $sizeAttribute = Attribute::where('slug', 'taille')->first();
                if ($sizeAttribute) {
                    DB::table('product_variant_values')->insert([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $validated['size_id'],
                    ]);
                }
            }

            // Générer le nom de la variante
            $variant->generateName();

            // Upload de l'image de la variante
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products/' . $product->id . '/variants', 'public');
                $variant->update(['image' => $path]);
            }

            // Marquer le produit comme ayant des variantes
            if (!$product->has_variants) {
                $product->update(['has_variants' => true]);
            }

            DB::commit();

            return back()->with('success', 'Variante ajoutée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une variante
     */
    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
        }

        $variant->delete();

        // Vérifier s'il reste des variantes
        if ($product->variants()->count() === 0) {
            $product->update(['has_variants' => false]);
        }

        return back()->with('success', 'Variante supprimée.');
    }

    /**
     * Supprimer une image
     */
    public function destroyImage(Product $product, ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $wasPrimary = $image->is_primary;
        $image->delete();

        // Si c'était l'image principale, en définir une nouvelle
        if ($wasPrimary) {
            $newPrimary = $product->images()->orderBy('position')->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return back()->with('success', 'Image supprimée.');
    }

    /**
     * Définir l'image principale
     */
    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        $image->setAsPrimary();
        return back()->with('success', 'Image principale mise à jour.');
    }
}
