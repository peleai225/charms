<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Page catalogue / boutique
     */
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['images', 'category', 'variants.attributeValues.attribute']);

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
        $colors = Attribute::where('slug', 'couleur')->first()?->values ?? collect();

        // Prix min/max
        $priceRange = Product::active()->selectRaw('MIN(sale_price) as min, MAX(sale_price) as max')->first();

        return view('front.shop.index', compact('products', 'categories', 'colors', 'priceRange'));
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

        return view('front.shop.category', compact('category', 'products', 'subcategories'));
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
            ->filter(fn($av) => $av->attribute->slug === 'couleur')
            ->unique('id');

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

        // Points de fidélité que le client gagnerait sur cet achat
        $pointsToEarn = (int) floor($product->sale_price / 1000 * \App\Models\Setting::get('loyalty_points_per_1000', 10));

        return view('front.shop.product', compact(
            'product', 'variantsByColor', 'availableColors',
            'relatedProducts', 'upsellProducts', 'pointsToEarn'
        ));
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

