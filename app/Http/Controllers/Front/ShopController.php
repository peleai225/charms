<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Page catalogue / boutique
     */
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['images', 'category']);

        // Filtres
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = $category->getAllChildrenIds();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        if ($request->filled('color')) {
            $query->whereHas('variants.attributeValues', function ($q) use ($request) {
                $q->where('slug', $request->color);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filtre promotions
        if ($request->filled('on_sale')) {
            $query->onSale();
        }

        // Filtre produits en vedette
        if ($request->filled('featured')) {
            $query->featured();
        }

        // Tri
        switch ($request->get('sort', 'newest')) {
            case 'price_asc':
                $query->orderBy('sale_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sale_price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
                $query->orderBy('sales_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        // Données pour les filtres
        $categories = Category::active()->roots()->with('children')->ordered()->get();

        // Format data for Inertia
        $productsData = [
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->sale_price,
                    'compare_price' => $product->compare_price,
                    'stock' => $product->stock_quantity,
                    'primary_image' => $product->images->where('is_primary', true)->first()?->path ?? $product->images->first()?->path,
                ];
            }),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'prev_page_url' => $products->previousPageUrl(),
            'next_page_url' => $products->nextPageUrl(),
        ];

        $categoriesData = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ];
        });

        return Inertia::render('Shop/Index', [
            'products' => $productsData,
            'categories' => $categoriesData,
            'filters' => $request->only(['category', 'search', 'min_price', 'max_price', 'sort', 'on_sale', 'featured']),
            'currentCategory' => isset($category) ? ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug] : null,
        ]);
    }

    /**
     * Page catégorie
     */
    public function category(string $slug, Request $request)
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();
        
        $categoryIds = $category->getAllChildrenIds();
        
        $query = Product::active()
            ->whereIn('category_id', $categoryIds)
            ->with(['images', 'category', 'variants.attributeValues.attribute']);

        // Tri
        switch ($request->get('sort', 'newest')) {
            case 'price_asc':
                $query->orderBy('sale_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sale_price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
                $query->orderBy('sales_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $subcategories = $category->children()->active()->ordered()->get();

        $formatProduct = fn($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'price'         => $p->sale_price,
            'compare_price' => $p->compare_price,
            'stock'         => $p->stock_quantity,
            'has_variants'  => $p->variants->isNotEmpty(),
            'category_name' => $p->category?->name,
            'primary_image' => $p->images->where('is_primary', true)->first()?->path ?? $p->images->first()?->path,
        ];

        return Inertia::render('Shop/Category', [
            'category' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
                'image'       => $category->image,
            ],
            'subcategories' => $subcategories->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'slug'  => $s->slug,
                'image' => $s->image,
            ])->toArray(),
            'products' => [
                'data'          => $products->map($formatProduct)->toArray(),
                'current_page'  => $products->currentPage(),
                'last_page'     => $products->lastPage(),
                'total'         => $products->total(),
                'prev_page_url' => $products->previousPageUrl(),
                'next_page_url' => $products->nextPageUrl(),
            ],
            'filters' => $request->only(['sort']),
        ]);
    }

    /**
     * Page produit avec sélecteur de couleurs
     */
    public function product(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with([
                'images' => fn($q) => $q->orderBy('position'),
                'category',
                'variants' => fn($q) => $q->active()->with('attributeValues.attribute'),
                'reviews' => fn($q) => $q->approved()->latest()->take(5),
            ])
            ->firstOrFail();

        // Incrémenter les vues
        $product->increment('views_count');

        // Organiser les variantes par couleur
        $variantsByColor = $product->variants->groupBy(function ($variant) {
            $colorAttr = $variant->attributeValues->firstWhere('attribute.slug', 'couleur');
            return $colorAttr?->id ?? 'default';
        });

        // Récupérer les couleurs disponibles
        $availableColors = $product->variants
            ->pluck('attributeValues')
            ->flatten()
            ->filter(fn($av) => $av->attribute && $av->attribute->slug === 'couleur')
            ->unique('id');

        // Détecter l'attribut secondaire : parmi les attributs non-couleur,
        // prendre celui qui a le plus de valeurs distinctes sur ce produit.
        // Ex: coloring book → design(17 valeurs) > âge(1 valeur) → design est retenu.
        $secondaryAttribute = $product->variants
            ->pluck('attributeValues')
            ->flatten()
            ->filter(fn($av) => $av->attribute && $av->attribute->slug !== 'couleur')
            ->groupBy('attribute_id')
            ->sortByDesc(fn($avs) => $avs->unique('id')->count())
            ->map(fn($avs) => $avs->first()->attribute)
            ->first();
        $secondaryAttributeSlug = $secondaryAttribute?->slug;
        $secondaryAttributeName = $secondaryAttribute?->name ?? 'Taille';

        // Cross-sell : produits de la même catégorie (prix similaire ±30%)
        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('sale_price', '>=', $product->sale_price * 0.7)
            ->where('sale_price', '<=', $product->sale_price * 1.3)
            ->with(['images'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Upsell : produit plus premium (même catégorie, prix 20-100% plus élevé)
        $upsellProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('sale_price', '>', $product->sale_price * 1.2)
            ->where('sale_price', '<=', $product->sale_price * 2)
            ->with(['images'])
            ->orderBy('sale_price')
            ->take(2)
            ->get();

        // Format colors
        $colorsData = $availableColors->map(function ($attrValue) {
            return [
                'id'    => $attrValue->id,
                'name'  => $attrValue->value,
                'hex'   => $attrValue->color_code ?? null,
                'image' => $attrValue->image_url,
            ];
        })->values()->toArray();

        // Format secondary attribute values (uniquement l'attribut secondaire retenu)
        $secondaryValues = $secondaryAttribute
            ? $product->variants
                ->pluck('attributeValues')
                ->flatten()
                ->filter(fn($av) => $av->attribute && $av->attribute->id === $secondaryAttribute->id)
                ->unique('id')
                ->map(fn($attrValue) => [
                    'id'    => $attrValue->id,
                    'value' => $attrValue->value,
                ])
                ->values()
                ->toArray()
            : [];

        // Format variants
        $variantsData = $product->variants->map(function ($variant) use ($secondaryAttribute) {
            $colorAttr     = $variant->attributeValues->firstWhere('attribute.slug', 'couleur');
            $secondaryAttr = $secondaryAttribute
                ? $variant->attributeValues->firstWhere(fn($av) => $av->attribute?->id === $secondaryAttribute->id)
                : null;

            return [
                'id'           => $variant->id,
                'sku'          => $variant->sku,
                'price'        => $variant->sale_price ?? $variant->product->sale_price,
                'stock'        => $variant->stock_quantity,
                'color_id'     => $colorAttr?->id,
                'secondary_id' => $secondaryAttr?->id,
                'image'        => $variant->image ? asset('storage/' . $variant->image) : null,
            ];
        })->toArray();

        $formatSmallProduct = fn($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'price'         => $p->sale_price,
            'compare_price' => $p->compare_price,
            'stock'         => $p->stock_quantity,
            'has_variants'  => false,
            'primary_image' => $p->images->where('is_primary', true)->first()?->path ?? $p->images->first()?->path,
        ];

        $reviewsData = $product->reviews->map(function ($r) {
            return [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'body'       => $r->body,
                'author'     => $r->customer
                    ? $r->customer->first_name . ' ' . mb_substr($r->customer->last_name ?? '', 0, 1) . '.'
                    : 'Client',
                'created_at' => $r->created_at->format('d/m/Y'),
            ];
        })->toArray();

        $reviewAvg   = $product->reviews->avg('rating');
        $reviewCount = $product->reviews->count();

        $whatsapp = \App\Models\Setting::get('social_whatsapp');
        $whatsappNumber = $whatsapp ? preg_replace('/\D/', '', $whatsapp) : null;

        // Format data for Inertia
        $productData = [
            'id'                 => $product->id,
            'name'               => $product->name,
            'slug'               => $product->slug,
            'sku'                => $product->sku,
            'price'              => $product->sale_price,
            'compare_price'      => $product->compare_price,
            'stock'              => $product->stock_quantity,
            'short_description'  => $product->short_description,
            'description'        => $product->description,
            'weight'             => $product->weight,
            'images'             => $product->images->pluck('path')->toArray(),
            'category'           => $product->category ? [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'has_variants'       => $product->variants->isNotEmpty(),
            'variants'           => $variantsData,
            'colors'             => $colorsData,
            'secondary_attribute'=> $secondaryAttribute ? [
                'slug'   => $secondaryAttribute->slug,
                'name'   => $secondaryAttribute->name,
                'values' => $secondaryValues,
            ] : null,
            'reviews'            => $reviewsData,
            'review_avg'         => $reviewAvg ? round($reviewAvg, 1) : null,
            'review_count'       => $reviewCount,
        ];

        return Inertia::render('Shop/Product', [
            'product'          => $productData,
            'related_products' => $relatedProducts->map($formatSmallProduct),
            'whatsapp_number'  => $whatsappNumber,
        ]);
    }

    /**
     * API : Récupérer les données d'une variante (pour AJAX)
     */
    public function getVariant(Product $product, Request $request)
    {
        $colorId = $request->get('color_id');
        $sizeId = $request->get('size_id');

        $variant = $product->variants()
            ->whereHas('attributeValues', function ($q) use ($colorId) {
                $q->where('attribute_values.id', $colorId);
            })
            ->when($sizeId, function ($q) use ($sizeId) {
                $q->whereHas('attributeValues', function ($q2) use ($sizeId) {
                    $q2->where('attribute_values.id', $sizeId);
                });
            })
            ->with('attributeValues')
            ->first();

        if (!$variant) {
            return response()->json(['error' => 'Variante non trouvée'], 404);
        }

        return response()->json([
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => $variant->sale_price ?? $product->sale_price,
            'price_formatted' => number_format($variant->sale_price ?? $product->sale_price, 2, ',', ' ') . ' €',
            'stock' => $variant->stock_quantity,
            'in_stock' => $variant->stock_quantity > 0 || $product->allow_backorder,
            'image' => $variant->image ? asset('storage/' . $variant->image) : null,
        ]);
    }
}

