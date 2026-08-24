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
    @endphp

    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%232563EB' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
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

        window.trackPixel = {
            viewContent: function(p) {
                if (!window.fbq) return;
                fbq('track', 'ViewContent', { content_ids:[p.id], content_name:p.name, content_type:'product', value:p.price, currency:'XOF' });
            },
            addToCart: function(p, qty) {
                if (!window.fbq) return;
                fbq('track', 'AddToCart', { content_ids:[p.id], content_name:p.name, content_type:'product', value:p.price * (qty||1), currency:'XOF' });
            },
            initiateCheckout: function(total, count) {
                if (!window.fbq) return;
                fbq('track', 'InitiateCheckout', { value:total, num_items:count, currency:'XOF' });
            },
            purchase: function(orderId, value) {
                if (!window.fbq) return;
                var key = 'px_purchase_' + orderId;
                if (sessionStorage.getItem(key)) return;
                sessionStorage.setItem(key, '1');
                fbq('track', 'Purchase', { value:value, currency:'XOF', order_id:orderId });
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
