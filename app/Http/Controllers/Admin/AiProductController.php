<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AiService;
use App\Services\VariantProductFromPhotos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        $colorAttribute = Attribute::colorAttribute();
        $optionAttributes = Attribute::with('values')
            ->when($colorAttribute, fn ($q) => $q->where('id', '!=', $colorAttribute->id))
            ->orderBy('order')->orderBy('id')
            ->get();

        return Inertia::render('Admin/Products/ImportImages', [
            'pageTitle' => 'Créer des produits par photos',
            'aiConfigured' => $ai->isConfigured(),
            'aiProvider' => $ai->provider(),
            'categories' => $this->categoryOptions(),
            'colors' => $colorAttribute
                ? $colorAttribute->values()->get()->map(fn ($v) => ['id' => $v->id, 'value' => $v->value, 'color_code' => $v->color_code])->values()
                : [],
            'optionAttributes' => $optionAttributes->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'values' => $a->values->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->values(),
            ])->values(),
            'categoryOptions' => $this->categoryOptionSuggestions($optionAttributes->pluck('id')->all()),
        ]);
    }

    /**
     * Créer un produit à variantes à partir de plusieurs photos (une couleur par photo)
     */
    public function storeVariantProduct(Request $request, VariantProductFromPhotos $builder)
    {
        $validated = $request->validate([
            'images' => 'required|array|min:1|max:12',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'colors' => 'nullable|array',
            'colors.*.id' => 'nullable|integer',
            'colors.*.name' => 'nullable|string|max:50',
            'colors.*.hex' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'option_attribute_id' => 'nullable|integer|exists:attributes,id',
            'variants' => 'required|array|min:1|max:200',
            'variants.*.color_index' => 'nullable|integer|min:0',
            'variants.*.value_id' => 'nullable|integer',
            'variants.*.stock' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,active',
        ], [
            'images.*.max' => 'Une photo dépasse 5 Mo.',
            'variants.*.stock.required' => 'Indiquez le stock de chaque variante (0 si aucun).',
        ]);

        $product = $builder->create($validated, $request->file('images'));

        return response()->json([
            'id' => $product->id,
            'edit_url' => route('admin.products.edit', $product),
            'variants' => $product->variants()->count(),
        ]);
    }

    /**
     * Par catégorie : la déclinaison (taille, pointure…) la plus utilisée par les produits existants,
     * avec les valeurs déjà employées. Sert à pré-cocher les tailles d'un nouveau produit.
     *
     * @param  array<int>  $attributeIds
     * @return array<int, array{attribute_id: int, value_ids: array<int>}>
     */
    protected function categoryOptionSuggestions(array $attributeIds): array
    {
        if (! $attributeIds) {
            return [];
        }

        $rows = DB::table('product_variant_values as pvv')
            ->join('product_variants as v', 'v.id', '=', 'pvv.product_variant_id')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->whereNotNull('p.category_id')
            ->whereNull('p.deleted_at')
            ->whereIn('pvv.attribute_id', $attributeIds)
            ->select('p.category_id', 'pvv.attribute_id', 'pvv.attribute_value_id', DB::raw('COUNT(*) as uses'))
            ->groupBy('p.category_id', 'pvv.attribute_id', 'pvv.attribute_value_id')
            ->get();

        return $rows->groupBy('category_id')->map(function ($rows) {
            $attributeId = $rows->groupBy('attribute_id')->sortByDesc(fn ($r) => $r->sum('uses'))->keys()->first();

            return [
                'attribute_id' => (int) $attributeId,
                'value_ids' => $rows->where('attribute_id', $attributeId)->pluck('attribute_value_id')->map(fn ($id) => (int) $id)->values()->all(),
            ];
        })->all();
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
     * Mode arrivage : l'IA regroupe les photos du même article.
     * Les miniatures arrivent en JSON (data URL) : un envoi de fichiers serait coupé à 20 par PHP (max_file_uploads).
     */
    public function groupPhotos(Request $request, AiService $ai)
    {
        $request->validate([
            'images' => 'required|array|min:2|max:40',
            'images.*' => ['required', 'string', 'max:3000000', 'regex:/^data:image\/(jpeg|png|webp);base64,/'],
        ], [
            'images.max' => '40 photos maximum à la fois pour le tri automatique.',
            'images.*.regex' => 'Une des photos envoyées n\'est pas une image.',
            'images.*.max' => 'Une des photos envoyées est trop lourde.',
        ]);

        $images = [];
        foreach ($request->input('images') as $dataUrl) {
            $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
            $info = $binary === false ? false : @getimagesizefromstring($binary);
            if (! $info) {
                throw ValidationException::withMessages(['images' => 'Une des photos envoyées n\'a pas pu être lue.']);
            }
            $images[] = ['data' => $binary, 'mime' => $info['mime']];
        }

        return $this->respond(fn () => ['groups' => $ai->groupPhotos($images)]);
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
     * Enregistrer les descriptions relues par l'admin sur plusieurs produits
     */
    public function applyDescriptions(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array|min:1',
            'updates.*.id' => 'required|integer|exists:products,id',
            'updates.*.short_description' => 'nullable|string|max:500',
            'updates.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['updates'] as $u) {
                Product::where('id', $u['id'])->update([
                    'short_description' => $u['short_description'] ?? null,
                    'description' => $u['description'] ?? null,
                ]);
            }
        });

        return back()->with('success', count($validated['updates']).' fiche(s) complétée(s).');
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
            return response()->json(['message' => 'Le service IA ne répond pas. Réessayez dans un instant.'], 504);
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
