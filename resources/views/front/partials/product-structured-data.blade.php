@php
    $sdImage = $product->images->where('is_primary', true)->first();
    $sdImages = $product->images->pluck('path')->map(fn($p) => asset('storage/' . $p))->toArray();

    $sdAvailability = $product->is_in_stock
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock';

    $sdData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => strip_tags($product->description ?? $product->short_description ?? ''),
        'image' => !empty($sdImages) ? $sdImages : null,
        'sku' => $product->sku,
        'brand' => $product->brand ? [
            '@type' => 'Brand',
            'name' => $product->brand,
        ] : null,
        'offers' => [
            '@type' => 'Offer',
            'url' => route('shop.product', $product->slug),
            'priceCurrency' => 'XOF',
            'price' => $product->sale_price,
            'availability' => $sdAvailability,
            'seller' => [
                '@type' => 'Organization',
                'name' => \App\Models\Setting::get('site_name', config('app.name')),
            ],
        ],
    ];

    // Prix barré
    if ($product->compare_price && $product->compare_price > $product->sale_price) {
        $sdData['offers']['priceValidUntil'] = now()->addMonths(3)->format('Y-m-d');
    }

    // Avis agrégés
    if (method_exists($product, 'reviews') && $product->reviews()->approved()->count() > 0) {
        $reviewCount = $product->reviews()->approved()->count();
        $avgRating = round($product->reviews()->approved()->avg('rating'), 1);
        if ($avgRating > 0) {
            $sdData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $avgRating,
                'reviewCount' => $reviewCount,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }
    }
@endphp

<script type="application/ld+json">
{!! json_encode(array_filter($sdData, fn($v) => $v !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
