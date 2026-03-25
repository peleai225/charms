@php
    $sdSiteName = \App\Models\Setting::get('site_name', config('app.name', 'Chamse'));
    $sdDescription = \App\Models\Setting::get('site_description', 'Boutique en ligne de produits de qualité');
    $sdLogo = \App\Models\Setting::get('logo');
    $sdPhone = \App\Models\Setting::get('contact_phone');
    $sdEmail = \App\Models\Setting::get('contact_email');
    $sdAddress = \App\Models\Setting::get('contact_address');
    $sdFacebook = \App\Models\Setting::get('social_facebook');
    $sdInstagram = \App\Models\Setting::get('social_instagram');
    $sdTwitter = \App\Models\Setting::get('social_twitter');

    $sameAs = array_filter([
        $sdFacebook,
        $sdInstagram,
        $sdTwitter,
    ]);
@endphp

{{-- Organization Schema --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $sdSiteName,
    'url' => url('/'),
    'logo' => $sdLogo ? asset('storage/' . $sdLogo) : null,
    'description' => $sdDescription,
    'contactPoint' => array_filter([
        $sdPhone ? [
            '@type' => 'ContactPoint',
            'telephone' => $sdPhone,
            'contactType' => 'customer service',
            'availableLanguage' => 'French',
        ] : null,
        $sdEmail ? [
            '@type' => 'ContactPoint',
            'email' => $sdEmail,
            'contactType' => 'customer service',
        ] : null,
    ]),
    'address' => $sdAddress ? [
        '@type' => 'PostalAddress',
        'streetAddress' => $sdAddress,
        'addressCountry' => 'CI',
    ] : null,
    'sameAs' => !empty($sameAs) ? array_values($sameAs) : null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

{{-- WebSite Schema with SearchAction --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $sdSiteName,
    'url' => url('/'),
    'description' => $sdDescription,
    'inLanguage' => 'fr-FR',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => route('shop.index') . '?search={search_term_string}',
        ],
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

{{-- BreadcrumbList Schema --}}
@php
    $breadcrumbs = [];
    $segments = request()->segments();
    $currentUrl = url('/');

    $breadcrumbs[] = [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Accueil',
        'item' => url('/'),
    ];

    $routeName = request()->route()?->getName();
    $position = 2;

    if ($routeName === 'shop.index') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position, 'name' => 'Boutique', 'item' => route('shop.index')];
    } elseif ($routeName === 'shop.category') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Boutique', 'item' => route('shop.index')];
        if (isset($category)) {
            $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position, 'name' => $category->name, 'item' => route('shop.category', $category->slug)];
        }
    } elseif ($routeName === 'shop.product') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => 'Boutique', 'item' => route('shop.index')];
        if (isset($product)) {
            $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position, 'name' => $product->name, 'item' => route('shop.product', $product->slug)];
        }
    } elseif ($routeName === 'contact') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position, 'name' => 'Contact', 'item' => route('contact')];
    } elseif ($routeName === 'about') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $position, 'name' => 'À propos', 'item' => route('about')];
    }
@endphp

@if(count($breadcrumbs) > 1)
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbs,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
