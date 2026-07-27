<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home', $this->buildData());
    }

    public function indexV2()
    {
        return Inertia::render('HomeV2', $this->buildData());
    }

    private function buildData(): array
    {
        $featuredCategories = Category::active()
            ->featured()
            ->roots()
            ->ordered()
            ->take(6)
            ->withCount(['products' => fn($q) => $q->active()])
            ->get();

        if ($featuredCategories->isNotEmpty()) {
            $minByCategory = Product::active()
                ->whereIn('category_id', $featuredCategories->pluck('id'))
                ->groupBy('category_id')
                ->selectRaw('category_id, MIN(sale_price) as min_sale_price')
                ->pluck('min_sale_price', 'category_id');

            $featuredCategories->each(fn(Category $c) =>
                $c->setAttribute('min_product_price', $minByCategory[$c->id] ?? null)
            );
        }

        $featuredProducts = Product::active()
            ->featured()
            ->with(['images', 'category'])
            ->withCount('variants')
            ->latest('updated_at')
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::active()
                ->with(['images', 'category'])
                ->withCount('variants')
                ->latest()
                ->take(8)
                ->get();
        }

        $newProducts = Product::active()
            ->new()
            ->with(['images', 'category'])
            ->withCount('variants')
            ->latest()
            ->take(8)
            ->get();

        $saleProducts = Product::active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'sale_price')
            ->with(['images', 'category'])
            ->withCount('variants')
            ->take(8)
            ->get();

        $reviews = Review::where('status', Review::STATUS_APPROVED)
            ->where('rating', '>=', 4)
            ->with('customer')
            ->latest()
            ->take(4)
            ->get();

        $approvedCount = Review::where('status', Review::STATUS_APPROVED)->count();
        $reviewStats = $approvedCount > 0 ? [
            'count' => $approvedCount,
            'avg'   => round(Review::where('status', Review::STATUS_APPROVED)->avg('rating'), 1),
        ] : null;

        $formatProduct = fn($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'price'         => $p->sale_price,
            'compare_price' => $p->compare_price,
            'stock'         => $p->stock_quantity,
            'has_variants'  => $p->variants_count > 0,
            'is_new'        => (bool) $p->is_new,
            'category_name' => $p->category?->name,
            'primary_image' => $p->images->where('is_primary', true)->first()?->path
                ?? $p->images->first()?->path,
        ];

        $mapBanner = fn($b) => [
            'id'               => $b->id,
            'title'            => $b->title,
            'subtitle'         => $b->subtitle,
            'description'      => $b->description,
            'image'            => $b->image ? asset('storage/' . $b->image) : null,
            'image_mobile'     => $b->image_mobile ? asset('storage/' . $b->image_mobile) : null,
            'link'             => $b->link,
            'button_text'      => $b->button_text,
            'background_color' => $b->background_color,
            'text_color'       => $b->text_color,
        ];

        $whatsapp = \App\Models\Setting::get('social_whatsapp');

        $homeBanners = \App\Models\Banner::active()
            ->whereIn('position', ['home_hero', 'home_middle', 'home_bottom'])
            ->orderBy('order')
            ->get()
            ->groupBy('position')
            ->map(fn($group) => $group->map($mapBanner)->values()->all());

        return [
            'featured_categories' => $featuredCategories->map(fn($c) => [
                'id'                => $c->id,
                'name'              => $c->name,
                'slug'              => $c->slug,
                'image'             => $c->image,
                'products_count'    => $c->products_count,
                'min_product_price' => $c->min_product_price,
            ]),
            'featured_products' => $featuredProducts->map($formatProduct),
            'new_products'      => $newProducts->map($formatProduct),
            'sale_products'     => $saleProducts->map($formatProduct),
            'reviews'           => $reviews->map(fn($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'body'       => $r->body,
                'author'     => $r->customer
                    ? $r->customer->first_name . ' ' . mb_substr($r->customer->last_name, 0, 1) . '.'
                    : 'Client',
                'created_at' => $r->created_at->diffForHumans(),
            ]),
            'review_stats'    => $reviewStats,
            'whatsapp_number' => $whatsapp ? preg_replace('/\D/', '', $whatsapp) : null,
            'banners'         => $homeBanners,
        ];
    }
}
