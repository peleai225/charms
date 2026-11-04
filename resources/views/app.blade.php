<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName      = \App\Models\Setting::get('site_name', config('app.name', 'Chamse'));
        $siteFavicon   = \App\Models\Setting::get('favicon');
        $primaryColor  = \App\Models\Setting::get('primary_color', '#2563EB');
        $secondaryColor= \App\Models\Setting::get('secondary_color', '#8b5cf6');
        $accentColor   = \App\Models\Setting::get('accent_color', '#f59e0b');
        $metaPixelId   = \App\Models\Setting::get('meta_pixel_id');
        $tiktokPixelId = \App\Models\Setting::get('tiktok_pixel_id');
        $ga4Id         = \App\Models\Setting::get('ga4_id');

        // OG / Social preview — rendered server-side so WhatsApp/Facebook crawlers see them
        $ogTitle  = $siteName;
        $ogDesc   = \App\Models\Setting::get('meta_description', '');
        $ogImage  = $siteFavicon ? asset('storage/' . $siteFavicon) : '';
        $ogUrl    = url()->current();
        $ogType   = 'website';

        try {
            if (request()->routeIs('shop.product')) {
                $ogSlug = request()->route('slug');
                if ($ogSlug) {
                    $ogProduct = \App\Models\Product::where('slug', $ogSlug)
                        ->select(['id', 'name', 'slug', 'short_description'])
                        ->with(['images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('position')->limit(1)])
                        ->first();
                    if ($ogProduct) {
                        $ogTitle = $ogProduct->name . ' — ' . $siteName;
                        $ogDesc  = $ogProduct->short_description ?? $ogProduct->name;
                        $ogType  = 'product';
                        $ogImg   = $ogProduct->images->first();
                        if ($ogImg) {
                            // Priorité : version og/ JPEG (compatible Facebook/WhatsApp)
                            // Dérivée de medium/uuid.webp → og/uuid.jpg
                            $ogJpegPath = preg_replace('#/medium/(.+)\.webp$#', '/og/$1.jpg', $ogImg->path);
                            if ($ogJpegPath !== $ogImg->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ogJpegPath)) {
                                $ogImage = asset('storage/' . $ogJpegPath);
                            } else {
                                $ogImage = asset('storage/' . $ogImg->path);
                            }
                        }
                    }
                }
            } elseif (request()->routeIs('shop.category')) {
                $ogSlug = request()->route('slug');
                if ($ogSlug) {
                    $ogCat = \App\Models\Category::where('slug', $ogSlug)
                        ->select(['id', 'name', 'description', 'image'])
                        ->first();
                    if ($ogCat) {
                        $ogTitle = $ogCat->name . ' — ' . $siteName;
                        $ogDesc  = $ogCat->description ?? '';
                        if ($ogCat->image) {
                            $ogImage = asset('storage/' . $ogCat->image);
                        } else {
                            // Pas d'image catégorie → utiliser la photo du 1er produit actif
                            $firstImg = \App\Models\ProductImage::query()
                                ->whereHas('product', fn($q) => $q->where('status', 'active')
                                    ->where('category_id', $ogCat->id))
                                ->where('is_primary', true)
                                ->select(['path'])
                                ->first();
                            if ($firstImg) {
                                $ogJpeg = preg_replace('#/medium/(.+)\.webp$#', '/og/$1.jpg', $firstImg->path);
                                $ogImage = asset('storage/' . (
                                    $ogJpeg !== $firstImg->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ogJpeg)
                                        ? $ogJpeg
                                        : $firstImg->path
                                ));
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently degrade — OG defaults already set above
        }
    @endphp

    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%232563EB' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif

    {{-- OG / Social preview tags — lisibles par WhatsApp, Facebook, etc. --}}
    <meta property="og:site_name" content="{{ $siteName }}" />
    <meta property="og:type"      content="{{ $ogType }}" />
    <meta property="og:title"     content="{{ $ogTitle }}" />
    <meta property="og:url"       content="{{ $ogUrl }}" />
    @if($ogDesc)
    <meta property="og:description" content="{{ $ogDesc }}" />
    <meta name="description"        content="{{ $ogDesc }}" />
    @endif
    @if($ogImage)
    <meta property="og:image"        content="{{ $ogImage }}" />
    <meta property="og:image:width"  content="1200" />
    <meta property="og:image:height" content="630" />
    <meta name="twitter:card"        content="summary_large_image" />
    <meta name="twitter:image"       content="{{ $ogImage }}" />
    @endif
    <meta name="twitter:title"       content="{{ $ogTitle }}" />

    {{-- JSON-LD schema.org Product (sku = id produit = content_ids du pixel = id du flux Meta) --}}
    @if(($page['component'] ?? null) === 'Shop/Product' && !empty($page['props']['product']))
        @php
            $ldP = $page['props']['product'];
            $ldDesc = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) ($ldP['description'] ?? '')), ENT_QUOTES, 'UTF-8')))
                ?: trim((string) ($ldP['short_description'] ?? ''))
                ?: $ldP['name'];
            $ldInStock = ((int) ($ldP['stock'] ?? 0)) > 0 || !empty($ldP['allow_backorder']);
            $ldJson = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $ldP['name'],
                'image' => array_values(array_map(fn ($path) => asset('storage/' . $path), $ldP['images'] ?? [])),
                'description' => mb_substr($ldDesc, 0, 5000),
                'sku' => (string) $ldP['id'],
                'brand' => ['@type' => 'Brand', 'name' => $siteName],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => url('/produit/' . $ldP['slug']),
                    'price' => (string) round((float) $ldP['price']),
                    'priceCurrency' => 'XOF',
                    'availability' => $ldInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'itemCondition' => 'https://schema.org/NewCondition',
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($ldJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Tailwind v4 : on écrase les variables --color-primary-* directement dans :root.
           Toutes les classes bg-primary-600, text-primary-600, etc. lisent ces vars nativement. */
        :root {
            --color-primary-50:  color-mix(in srgb, {{ $primaryColor }}  6%, white);
            --color-primary-100: color-mix(in srgb, {{ $primaryColor }} 12%, white);
            --color-primary-200: color-mix(in srgb, {{ $primaryColor }} 22%, white);
            --color-primary-300: color-mix(in srgb, {{ $primaryColor }} 40%, white);
            --color-primary-400: color-mix(in srgb, {{ $primaryColor }} 65%, white);
            --color-primary-500: color-mix(in srgb, {{ $primaryColor }} 90%, white);
            --color-primary-600: {{ $primaryColor }};
            --color-primary-700: color-mix(in srgb, {{ $primaryColor }} 85%, black);
            --color-primary-800: color-mix(in srgb, {{ $primaryColor }} 70%, black);
            --color-primary-900: color-mix(in srgb, {{ $primaryColor }} 55%, black);
            --color-primary-950: color-mix(in srgb, {{ $primaryColor }} 40%, black);

            --color-secondary-50:  color-mix(in srgb, {{ $secondaryColor }}  6%, white);
            --color-secondary-100: color-mix(in srgb, {{ $secondaryColor }} 12%, white);
            --color-secondary-500: color-mix(in srgb, {{ $secondaryColor }} 90%, white);
            --color-secondary-600: {{ $secondaryColor }};
            --color-secondary-700: color-mix(in srgb, {{ $secondaryColor }} 85%, black);

            --color-accent-50:  color-mix(in srgb, {{ $accentColor }}  8%, white);
            --color-accent-100: color-mix(in srgb, {{ $accentColor }} 14%, white);
            --color-accent-400: color-mix(in srgb, {{ $accentColor }} 70%, white);
            --color-accent-500: color-mix(in srgb, {{ $accentColor }} 90%, white);
            --color-accent-600: {{ $accentColor }};
            --color-accent-700: color-mix(in srgb, {{ $accentColor }} 85%, black);
            --color-accent-800: color-mix(in srgb, {{ $accentColor }} 70%, black);
        }
    </style>

    @inertiaHead

    {{-- ── Google Analytics 4 ── --}}
    @if($ga4Id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $ga4Id }}');
    </script>
    @endif

    {{-- ── Meta (Facebook) Pixel ── --}}
    @if($metaPixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $metaPixelId }}');
        fbq('track', 'PageView');

        // content_ids = id produit en TEXTE, identique à la colonne id du flux /feed/meta.csv
        window.trackPixel = {
            viewContent: function(p) {
                if (!window.fbq) return;
                fbq('track', 'ViewContent', { content_ids:[String(p.id)], content_name:p.name, content_type:'product', value:Number(p.price), currency:'XOF' });
            },
            addToCart: function(p, qty) {
                if (!window.fbq) return;
                qty = qty || 1;
                fbq('track', 'AddToCart', { content_ids:[String(p.id)], contents:[{ id:String(p.id), quantity:qty, item_price:Number(p.price) }], content_name:p.name, content_type:'product', value:Number(p.price) * qty, currency:'XOF' });
            },
            initiateCheckout: function(total, count, productIds) {
                if (!window.fbq) return;
                var ids = (productIds || []).map(String).filter(function(v, i, a) { return a.indexOf(v) === i; });
                fbq('track', 'InitiateCheckout', { content_ids:ids, content_type:'product', value:Number(total), num_items:count, currency:'XOF' });
            },
            // items : [{ id, quantity, item_price }] — une seule fois par commande (event_id = numéro de commande)
            purchase: function(orderNumber, value, items) {
                if (!window.fbq) return;
                var key = 'px_purchase_' + orderNumber;
                try { if (localStorage.getItem(key)) return; localStorage.setItem(key, '1'); } catch (e) {
                    try { if (sessionStorage.getItem(key)) return; sessionStorage.setItem(key, '1'); } catch (e2) {}
                }
                items = items || [];
                var contents = items.map(function(i) { return { id:String(i.id), quantity:Number(i.quantity), item_price:Number(i.item_price) }; });
                var numItems = contents.reduce(function(n, c) { return n + c.quantity; }, 0);
                fbq('track', 'Purchase', {
                    content_ids: contents.map(function(c) { return c.id; }).filter(function(v, i, a) { return a.indexOf(v) === i; }),
                    content_type: 'product',
                    contents: contents,
                    value: Number(value),
                    currency: 'XOF',
                    num_items: numItems
                }, { eventID: String(orderNumber) });
            }
        };
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- ── TikTok Pixel ── --}}
    @if($tiktokPixelId)
    <script>
        !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
        ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
        ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
        ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
        ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;
        ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");
        o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
        var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
        ttq.load('{{ $tiktokPixelId }}');ttq.page();}(window,document,'ttq');
    </script>
    @endif
</head>
<body class="antialiased">
    @inertia
</body>
</html>
