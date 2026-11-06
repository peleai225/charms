<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Setting;
use App\Services\ShippingZones;
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
            ->with(['images', 'category', 'variants'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::active()
                ->with(['images', 'category', 'variants'])
                ->latest()
                ->take(8)
                ->get();
        }

        $newProducts = Product::active()
            ->new()
            ->with(['images', 'category', 'variants'])
            ->latest()
            ->take(8)
            ->get();

        $saleProducts = Product::active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'sale_price')
            ->with(['images', 'category', 'variants'])
            ->take(8)
            ->get();

        $reviews = Review::where('status', Review::STATUS_APPROVED)
            ->where('rating', '>=', 4)
            ->with(['customer', 'product'])
            ->latest()
            ->take(6)
            ->get();

        $reviewAggregate = Review::where('status', Review::STATUS_APPROVED)
            ->selectRaw('COUNT(*) as count, ROUND(AVG(rating), 1) as avg')
            ->first();
        $reviewStats = ($reviewAggregate->count ?? 0) > 0 ? [
            'count' => $reviewAggregate->count,
            'avg'   => $reviewAggregate->avg,
        ] : null;

        $formatProduct = fn($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'price'         => $p->sale_price,
            'compare_price' => $p->compare_price,
            'stock'          => $p->variants->isNotEmpty() ? $p->variants->sum('stock_quantity') : $p->stock_quantity,
            'allow_backorder'=> (bool) $p->allow_backorder,
            'has_variants'   => $p->variants->isNotEmpty(),
            // « Nouveau » sur tout le catalogue ne veut plus rien dire : seulement les 30 derniers jours
            'is_new'        => (bool) $p->is_new && $p->created_at?->gt(now()->subDays(30)),
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
            'ends_at'          => $b->ends_at?->toIso8601String(),
            'show_countdown'   => (bool) $b->show_countdown,
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
                'id'           => $r->id,
                'rating'       => $r->rating,
                'body'         => $r->body,
                'author'       => $r->customer
                    ? $r->customer->first_name . ' ' . mb_substr($r->customer->last_name, 0, 1) . '.'
                    : 'Client',
                'product_name' => $r->product?->name,
                'created_at'   => $r->created_at->diffForHumans(),
            ]),
            'review_stats'    => $reviewStats,
            'whatsapp_number' => $whatsapp ? preg_replace('/\D/', '', $whatsapp) : null,
            'banners'         => $homeBanners,
            'offers'          => $this->currentOffers($formatProduct),
            'shop_facts'      => $this->shopFacts(),
        ];
    }

    /**
     * Infos réelles de la boutique (livraison, paiement, contact), tirées des réglages.
     * Rien n'est affiché pour ce que la boutique n'a pas configuré.
     *
     * @return array<int, array{key: string, title: string, desc: string, href: ?string}>
     */
    private function shopFacts(): array
    {
        $facts = [];

        $zones = collect(app(ShippingZones::class)->all())->filter(fn ($z) => trim((string) ($z['name'] ?? '')) !== '');
        if ($zones->isNotEmpty()) {
            $minPrice = $zones->pluck('price')->filter(fn ($p) => is_numeric($p) && $p > 0)->min();
            $names = $zones->pluck('name')->map(fn ($n) => trim($n));
            $others = $names->count() - 3;
            $facts[] = [
                'key' => 'delivery',
                'title' => $minPrice ? 'Livraison dès '.number_format((float) $minPrice, 0, ',', ' ').' F CFA' : 'Livraison',
                'desc' => $names->take(3)->implode(', ').($others > 0 ? " et {$others} autre".($others > 1 ? 's zones' : ' zone') : ''),
                'href' => null,
            ];
        }

        if (Setting::get('payment_cod_enabled', '1') === '1') {
            $facts[] = ['key' => 'cod', 'title' => 'Paiement à la livraison', 'desc' => 'Vous payez en recevant votre colis', 'href' => null];
        }

        $digits = preg_replace('/\D/', '', (string) Setting::get('social_whatsapp'));
        if ($digits !== '') {
            $facts[] = ['key' => 'whatsapp', 'title' => 'Une question ?', 'desc' => 'WhatsApp '.$this->phoneText($digits), 'href' => "https://wa.me/{$digits}"];
        }

        return $facts;
    }

    // 2250707070707 → +225 07 07 07 07 07
    private function phoneText(string $digits): string
    {
        if (strlen($digits) === 13 && str_starts_with($digits, '225')) {
            return '+225 '.implode(' ', str_split(substr($digits, 3), 2));
        }

        return strlen($digits) === 10 ? implode(' ', str_split($digits, 2)) : '+'.$digits;
    }

    /**
     * Offres par lot en cours, avec les produits qu'elles concernent.
     * Une offre sans produit actif dans sa fourchette de prix n'est pas montrée.
     */
    private function currentOffers(callable $formatProduct): array
    {
        return Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->take(6)
            ->get()
            ->map(function (Promotion $promotion) use ($formatProduct) {
                $categoryIds = $promotion->eligibleCategoryIds();
                $productIds = $promotion->products->pluck('id')->all();
                if (! $categoryIds && ! $productIds) {
                    return null;
                }

                $products = Product::active()
                    ->where(fn ($q) => $q->whereIn('id', $productIds)->orWhereIn('category_id', $categoryIds))
                    ->with(['images', 'category', 'variants'])
                    ->latest()
                    ->take(24)
                    ->get()
                    ->filter(fn (Product $p) => $promotion->matchesPrice((float) $p->sale_price))
                    ->take(8)
                    ->values();

                if ($products->isEmpty()) {
                    return null;
                }

                $short = $promotion->lot_qty.' pour '.number_format((float) $promotion->lot_price, 0, ',', ' ');

                return [
                    'id'            => $promotion->id,
                    'name'          => $promotion->name,
                    'description'   => $promotion->description,
                    'label'         => $short.' F CFA',
                    'ends_at'       => $promotion->expires_at?->toIso8601String(),
                    'category_slug' => ! $productIds && $promotion->categories->count() === 1
                        ? $promotion->categories->first()->slug
                        : null,
                    'products'      => $products->map(fn ($p) => $formatProduct($p) + ['promotion_label' => $short])->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
