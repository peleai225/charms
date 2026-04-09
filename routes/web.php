<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Front-office (Site public)
|--------------------------------------------------------------------------
*/

// Servir les fichiers build (CSS/JS) quand public_html/build/ absent — URLs /build/xxx
Route::get('build/{path}', function (string $path) {
    try {
        if ($path === '' || strpos($path, '..') !== false) {
            abort(404);
        }
        $buildPath = base_path('public/build/' . $path);
        $realPath = realpath($buildPath);
        $allowedRoot = realpath(base_path('public/build'));
        if (!$allowedRoot || !$realPath || strpos($realPath, $allowedRoot) !== 0 || !File::isFile($realPath)) {
            abort(404);
        }
        $mime = File::mimeType($realPath);
        return response()->file($realPath, ['Content-Type' => $mime]);
    } catch (\Throwable $e) {
        abort(404);
    }
})->where('path', '.*')->name('build.serve');

// Servir les fichiers storage (images) quand symlink impossible — uniquement pour les URLs /storage/xxx
Route::get('storage/{path}', function (string $path) {
    try {
        if ($path === '' || strpos($path, '..') !== false) {
            abort(404);
        }
        $storagePath = storage_path('app/public/' . $path);
        $realPath = realpath($storagePath);
        $allowedRoot = realpath(storage_path('app/public'));
        if (!$allowedRoot || !$realPath || strpos($realPath, $allowedRoot) !== 0 || !File::isFile($realPath)) {
            abort(404);
        }
        return response()->file($realPath, ['Content-Type' => File::mimeType($realPath)]);
    } catch (\Throwable $e) {
        abort(404);
    }
})->where('path', '.*')->name('storage.serve');

// Setup sans terminal/SSH — pour hébergement mutualisé
// Utiliser : /setup?token=VOTRE_CLE_SECRETE (définir DEPLOY_TOKEN dans .env)
Route::get('/setup', function () {
    $token = request('token');
    $expected = config('app.deploy_token');
    if (!$expected || $token !== $expected) {
        abort(404);
    }
    $results = [];

    // Créer le lien storage dans public_html (cPanel : public_html != app/public)
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? public_path();
    $storageLink = rtrim($docRoot, '/') . '/storage';
    $storageTarget = storage_path('app/public');
    if (!file_exists($storageLink) && is_dir($storageTarget)) {
        $ok = @symlink($storageTarget, $storageLink);
        $results[] = $ok ? '✓ Lien storage créé dans public_html' : '✗ Échec symlink public_html/storage';
    } elseif (is_link($storageLink)) {
        $results[] = '✓ Lien storage existe déjà dans public_html';
    } else {
        $results[] = 'ℹ storage existe dans public_html (type: ' . filetype($storageLink) . ')';
    }

    // Aussi créer dans app/public (standard Laravel)
    try {
        Artisan::call('storage:link');
        $results[] = '✓ Lien storage Laravel créé';
    } catch (\Throwable $e) {
        $results[] = 'Storage Laravel : ' . ($e->getMessage());
    }

    // Migrations
    try {
        Artisan::call('migrate', ['--force' => true]);
        $results[] = '✓ Migrations exécutées';
    } catch (\Throwable $e) {
        $results[] = 'Migrations : ' . $e->getMessage();
    }

    // Vider tous les caches
    try {
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $results[] = '✓ Caches vidés (route, view, config, cache)';
    } catch (\Throwable $e) {
        $results[] = 'Cache : ' . $e->getMessage();
    }

    return '<pre style="font-family:sans-serif;padding:20px;">' . implode("\n", $results) . '</pre>';
});

// Ancienne route (rétrocompatibilité local)
Route::get('/setup-storage', function () {
    if (!app()->environment('local')) {
        abort(404);
    }
    try {
        Artisan::call('storage:link');
        return 'Le lien storage a été créé avec succès !';
    } catch (\Throwable $e) {
        return 'Erreur : ' . $e->getMessage();
    }
});

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Sitemap XML
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Authentification Client
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/inscription', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/inscription', [CustomerAuthController::class, 'register'])->name('register.post');
    Route::get('/mot-de-passe-oublie', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [CustomerAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/mot-de-passe/reset/{token}', [CustomerAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/mot-de-passe/reset', [CustomerAuthController::class, 'reset'])->name('password.update');
});

Route::post('/deconnexion', [CustomerAuthController::class, 'logout'])->name('logout');

// Espace client (protégé)
Route::middleware('customer')->prefix('mon-compte')->name('account.')->group(function () {
    Route::get('/', function () {
        return view('front.account.dashboard');
    })->name('dashboard');
    Route::get('/commandes', function () {
        return view('front.account.orders');
    })->name('orders');
    Route::get('/commandes/{order}', [App\Http\Controllers\Front\AccountController::class, 'showOrder'])->name('orders.show');
    Route::get('/adresses', [App\Http\Controllers\Front\AccountController::class, 'addresses'])->name('addresses');
    Route::post('/adresses', [App\Http\Controllers\Front\AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::get('/fidelite', [App\Http\Controllers\Front\AccountController::class, 'loyalty'])->name('loyalty');
    Route::get('/favoris', [App\Http\Controllers\Front\WishlistController::class, 'index'])->name('wishlist.index');
});

// Wishlist toggle (accessible même hors espace client pour rediriger)
Route::post('/favoris/{product}/toggle', [App\Http\Controllers\Front\WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Catalogue
Route::get('/boutique', [App\Http\Controllers\Front\ShopController::class, 'index'])->name('shop.index');
Route::get('/categorie/{slug}', [App\Http\Controllers\Front\ShopController::class, 'category'])->name('shop.category');
Route::get('/produit/{slug}', [App\Http\Controllers\Front\ShopController::class, 'product'])->name('shop.product');
Route::post('/produit/avis', [App\Http\Controllers\Front\ReviewController::class, 'store'])->name('review.store')->middleware('throttle:3,1');
Route::get('/produit/{product}/variant', [App\Http\Controllers\Front\ShopController::class, 'getVariant'])->name('shop.variant');

// Panier
Route::get('/panier', [App\Http\Controllers\Front\CartController::class, 'index'])->name('cart.index');
Route::post('/panier/ajouter', [App\Http\Controllers\Front\CartController::class, 'add'])->middleware('throttle:30,1')->name('cart.add');
Route::patch('/panier/{item}', [App\Http\Controllers\Front\CartController::class, 'update'])->middleware('throttle:60,1')->name('cart.update');
Route::delete('/panier/{item}', [App\Http\Controllers\Front\CartController::class, 'remove'])->middleware('throttle:60,1')->name('cart.remove');
Route::delete('/panier', [App\Http\Controllers\Front\CartController::class, 'clear'])->middleware('throttle:10,1')->name('cart.clear');
Route::post('/panier/coupon', [App\Http\Controllers\Front\CartController::class, 'applyCoupon'])->middleware('throttle:10,1')->name('cart.coupon.apply');
Route::delete('/panier/coupon', [App\Http\Controllers\Front\CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/panier/count', [App\Http\Controllers\Front\CartController::class, 'count'])->name('cart.count');
// Endpoint AJAX pour le panier drawer (sans rechargement)
Route::get('/panier/drawer', [App\Http\Controllers\Front\CartController::class, 'drawer'])->name('cart.drawer');

// Checkout
Route::get('/commander', [App\Http\Controllers\Front\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/commander', [App\Http\Controllers\Front\CheckoutController::class, 'store'])->middleware('throttle:8,1')->name('checkout.store');
Route::get('/commander/paiement/{order}', [App\Http\Controllers\Front\CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/commander/paiement/{order}', [App\Http\Controllers\Front\CheckoutController::class, 'processPayment'])->middleware('throttle:5,1')->name('checkout.process-payment');
Route::get('/checkout/confirmation', [App\Http\Controllers\Front\CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::get('/checkout/annulation', [App\Http\Controllers\Front\CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::get('/commande/succes', [App\Http\Controllers\Front\CheckoutController::class, 'success'])->name('checkout.success');

// Suivi de commande (invités)
Route::get('/suivi-commande', [App\Http\Controllers\Front\OrderTrackingController::class, 'index'])->name('order-tracking.index');
Route::get('/suivi-commande/resultat', [App\Http\Controllers\Front\OrderTrackingController::class, 'show'])->name('order-tracking.show');

// Webhook CinetPay (sans CSRF, throttle anti-abus)
Route::post('/webhook/cinetpay', [App\Http\Controllers\Webhook\CinetPayWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhook.cinetpay')
    ->withoutMiddleware(['web']);
Route::get('/webhook/cinetpay', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'CinetPay Webhook Endpoint',
        'info' => 'Ce endpoint accepte uniquement les requêtes POST de CinetPay.',
    ]);
})->withoutMiddleware(['web']);

// Webhook Lygos Pay (sans CSRF, throttle anti-abus)
Route::post('/webhook/lygos', [App\Http\Controllers\Webhook\LygosPayWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhook.lygos')
    ->withoutMiddleware(['web']);
Route::get('/webhook/lygos', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Lygos Pay Webhook Endpoint',
        'info' => 'Ce endpoint accepte uniquement les requêtes POST de Lygos Pay.',
    ]);
})->withoutMiddleware(['web']);

// Pages statiques
Route::get('/contact', [\App\Http\Controllers\Front\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\Front\ContactController::class, 'store'])->middleware('throttle:5,5')->name('contact.store');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Front\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/a-propos', function () {
    return view('front.pages.about');
})->name('about');

Route::get('/offline', function () {
    return view('front.pages.offline');
})->name('offline');

Route::get('/manifest.json', function () {
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Le Grand Bazar'));
    $siteDescription = \App\Models\Setting::get('site_description', 'Votre boutique en ligne de confiance');
    $primaryColor = \App\Models\Setting::get('primary_color', '#6366f1');
    $favicon = \App\Models\Setting::get('favicon');

    $iconBase = $favicon ? asset('storage/' . $favicon) : '/favicon.ico';

    return response()->json([
        'name' => $siteName,
        'short_name' => \Illuminate\Support\Str::limit($siteName, 20, ''),
        'description' => $siteDescription,
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#f8fafc',
        'theme_color' => $primaryColor,
        'orientation' => 'any',
        'scope' => '/',
        'lang' => 'fr',
        'categories' => ['shopping', 'business'],
        'icons' => [
            ['src' => $iconBase, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => $iconBase, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ], 200, ['Content-Type' => 'application/manifest+json'])->setCache(['public' => true, 'max_age' => 86400]);
})->name('manifest');

Route::get('/legal/{slug}', function (string $slug) {
    $pages = [
        'conditions-generales' => ['title' => 'Conditions Générales de Vente', 'key' => 'legal_cgv'],
        'politique-de-confidentialite' => ['title' => 'Politique de Confidentialité', 'key' => 'legal_privacy'],
        'retours-remboursements' => ['title' => 'Retours & Remboursements', 'key' => 'legal_returns'],
        'livraison' => ['title' => 'Politique de Livraison', 'key' => 'legal_shipping'],
        'faq' => ['title' => 'Foire Aux Questions', 'key' => 'legal_faq'],
    ];
    if (!isset($pages[$slug])) abort(404);
    $page = $pages[$slug];
    $content = \App\Models\Setting::get($page['key'], '<p class="text-slate-500 italic">Cette page sera bientôt disponible.</p>');
    return view('front.pages.legal', ['title' => $page['title'], 'content' => $content]);
})->name('legal');

/*
|--------------------------------------------------------------------------
| Routes Admin (Back-office)
|--------------------------------------------------------------------------
*/

// Authentification Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Login (accessible sans auth)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    });

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Routes protégées (nécessite rôle admin/manager/staff)
    Route::middleware('admin')->group(function () {
        // Dashboard — tous les rôles
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Profil admin — tous les rôles
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Commandes — tous les rôles (consultation + mise à jour statut)
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->names('orders');
        Route::patch('orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/{order}/invoice/view', [\App\Http\Controllers\Admin\OrderController::class, 'viewInvoice'])->name('orders.invoice.view');
        Route::post('orders/{order}/note', [\App\Http\Controllers\Admin\OrderController::class, 'addNote'])->name('orders.note');
        Route::post('orders/{order}/resend', [\App\Http\Controllers\Admin\OrderController::class, 'resendConfirmation'])->name('orders.resend');

        // Clients — tous les rôles (consultation)
        Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');

        // Stock — tous les rôles
        Route::get('/stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/movements', [\App\Http\Controllers\Admin\StockController::class, 'movements'])->name('stock.movements');
        Route::get('/stock/movements/create', [\App\Http\Controllers\Admin\StockController::class, 'createMovement'])->name('stock.create-movement');
        Route::post('/stock/movements', [\App\Http\Controllers\Admin\StockController::class, 'storeMovement'])->name('stock.store-movement');
        Route::get('/stock/reception', [\App\Http\Controllers\Admin\StockController::class, 'reception'])->name('stock.reception');
        Route::post('/stock/reception', [\App\Http\Controllers\Admin\StockController::class, 'storeReception'])->name('stock.store-reception');
        Route::get('/stock/inventory', [\App\Http\Controllers\Admin\StockController::class, 'inventory'])->name('stock.inventory');
        Route::post('/stock/inventory/adjust', [\App\Http\Controllers\Admin\StockController::class, 'adjustInventory'])->name('stock.adjust-inventory');
        Route::get('/stock/alerts', [\App\Http\Controllers\Admin\StockController::class, 'alerts'])->name('stock.alerts');

        // Scanner / Mode Caisse (POS) — tous les rôles
        Route::get('/scanner', [\App\Http\Controllers\Admin\ScannerController::class, 'index'])->name('scanner.index');
        Route::post('/scanner/scan', [\App\Http\Controllers\Admin\ScannerController::class, 'scan'])->name('scanner.scan');
        Route::get('/scanner/cart', [\App\Http\Controllers\Admin\ScannerController::class, 'getCart'])->name('scanner.cart');
        Route::post('/scanner/cart/add', [\App\Http\Controllers\Admin\ScannerController::class, 'addToCart'])->name('scanner.cart.add');
        Route::patch('/scanner/cart/{key}', [\App\Http\Controllers\Admin\ScannerController::class, 'updateCartItem'])->name('scanner.cart.update');
        Route::delete('/scanner/cart/{key}', [\App\Http\Controllers\Admin\ScannerController::class, 'removeCartItem'])->name('scanner.cart.remove');
        Route::delete('/scanner/cart', [\App\Http\Controllers\Admin\ScannerController::class, 'clearCart'])->name('scanner.cart.clear');
        Route::post('/scanner/checkout', [\App\Http\Controllers\Admin\ScannerController::class, 'checkout'])->name('scanner.checkout');
        Route::get('/scanner/receipt/{order}', [\App\Http\Controllers\Admin\ScannerController::class, 'receipt'])->name('scanner.receipt');
        Route::post('/scanner/stock-movement', [\App\Http\Controllers\Admin\ScannerController::class, 'stockMovement'])->name('scanner.stock-movement');

        // Codes-barres — tous les rôles
        Route::get('/barcodes', [\App\Http\Controllers\Admin\BarcodeController::class, 'index'])->name('barcodes.index');
        Route::get('/barcodes/{product}/generate', [\App\Http\Controllers\Admin\BarcodeController::class, 'generateBarcode'])->name('barcodes.generate');
        Route::get('/barcodes/{product}/qrcode', [\App\Http\Controllers\Admin\BarcodeController::class, 'generateQrCode'])->name('barcodes.qrcode');
        Route::get('/barcodes/{product}/qrcode-image', [\App\Http\Controllers\Admin\BarcodeController::class, 'showQrCode'])->name('barcodes.qrcode-image');
        Route::get('/barcodes/print-labels', [\App\Http\Controllers\Admin\BarcodeController::class, 'printLabels'])->name('barcodes.print-labels');
        Route::post('/barcodes/scan', [\App\Http\Controllers\Admin\BarcodeController::class, 'scan'])->name('barcodes.scan');
        Route::post('/barcodes/bulk-generate', [\App\Http\Controllers\Admin\BarcodeController::class, 'bulkGenerate'])->name('barcodes.bulk-generate');

        // Rapports — tous les rôles (consultation)
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/export-csv', [\App\Http\Controllers\Admin\ReportController::class, 'exportSalesCsv'])->name('reports.sales.export-csv');
        Route::get('/reports/sales/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportSalesPdf'])->name('reports.sales.export-pdf');
        Route::get('/reports/products', [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/products/export-csv', [\App\Http\Controllers\Admin\ReportController::class, 'exportProductsCsv'])->name('reports.products.export-csv');
        Route::get('/reports/customers', [\App\Http\Controllers\Admin\ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/stock', [\App\Http\Controllers\Admin\ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/stock/export-csv', [\App\Http\Controllers\Admin\ReportController::class, 'exportStockCsv'])->name('reports.stock.export-csv');

        // Avis clients — tous les rôles
        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
        Route::post('/reviews/{review}/respond', [\App\Http\Controllers\Admin\ReviewController::class, 'respond'])->name('reviews.respond');

// Documentation — tous les rôles
        Route::get('/docs/caisse-pos-imprimante', fn () => view('admin.docs.caisse-pos-imprimante'))->name('docs.caisse-pos-imprimante');

        // WhatsApp Business — tous les rôles
        Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::get('/whatsapp/catalog', [\App\Http\Controllers\Admin\WhatsAppController::class, 'exportCatalog'])->name('whatsapp.catalog');
        Route::post('/whatsapp/product-link', [\App\Http\Controllers\Admin\WhatsAppController::class, 'productLink'])->name('whatsapp.product-link');

        // ===== ADMIN + MANAGER uniquement =====
        Route::middleware('admin:admin,manager')->group(function () {
            // Produits (CRUD complet)
            Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->names('products');
            Route::post('products/{product}/variants', [\App\Http\Controllers\Admin\ProductController::class, 'storeVariant'])->name('products.variants.store');
            Route::post('products/{product}/variants/bulk', [\App\Http\Controllers\Admin\ProductController::class, 'bulkStoreVariants'])->name('products.variants.bulk');
            Route::patch('products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductController::class, 'updateVariant'])->name('products.variants.update');
            Route::delete('products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
            Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
            Route::post('products/{product}/images/{image}/primary', [\App\Http\Controllers\Admin\ProductController::class, 'setPrimaryImage'])->name('products.images.primary');

            // Attributs (tailles, couleurs, matières...)
            Route::get('attributes', [\App\Http\Controllers\Admin\AttributeController::class, 'index'])->name('attributes.index');
            Route::post('attributes', [\App\Http\Controllers\Admin\AttributeController::class, 'storeAttribute'])->name('attributes.store');
            Route::delete('attributes/{attribute}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroyAttribute'])->name('attributes.destroy');
            Route::post('attributes/{attribute}/values', [\App\Http\Controllers\Admin\AttributeController::class, 'storeValue'])->name('attributes.values.store');
            Route::post('attributes/{attribute}/values/bulk', [\App\Http\Controllers\Admin\AttributeController::class, 'bulkStoreValues'])->name('attributes.values.bulk');
            Route::delete('attributes/{attribute}/values/{value}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

            // Catégories
            Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->names('categories');
            Route::post('categories/reorder', [\App\Http\Controllers\Admin\CategoryController::class, 'reorder'])->name('categories.reorder');

            // Clients (modification + suppression)
            Route::get('customers/create', [\App\Http\Controllers\Admin\CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{customer}/edit', [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('customers.update');
            Route::delete('customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'destroy'])->name('customers.destroy');

            // Remboursements
            Route::get('/refunds', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('refunds.index');
            Route::post('orders/{order}/refunds', [\App\Http\Controllers\Admin\RefundController::class, 'store'])->name('refunds.store');

            // Coupons / Codes promo
            Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->names('coupons');
            Route::get('/coupons-generate-code', [\App\Http\Controllers\Admin\CouponController::class, 'generateCode'])->name('coupons.generate-code');

            // Bannières
            Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->names('banners');
            Route::patch('banners/{banner}/toggle', [\App\Http\Controllers\Admin\BannerController::class, 'toggle'])->name('banners.toggle');

            // Fournisseurs
            Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->names('suppliers');

            // Dropshipping
            Route::get('/dropshipping', [\App\Http\Controllers\Admin\DropshippingController::class, 'index'])->name('dropshipping.index');
            Route::get('/dropshipping/{orderSupplier}', [\App\Http\Controllers\Admin\DropshippingController::class, 'show'])->name('dropshipping.show');
            Route::patch('/dropshipping/{orderSupplier}/status', [\App\Http\Controllers\Admin\DropshippingController::class, 'updateStatus'])->name('dropshipping.update-status');
        });

        // ===== ADMIN uniquement =====
        Route::middleware('admin:admin')->group(function () {
            // Utilisateurs admin
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');

            // Paramètres
            Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::get('/settings/shipping', [\App\Http\Controllers\Admin\SettingsController::class, 'shipping'])->name('settings.shipping');
            Route::post('/settings/shipping', [\App\Http\Controllers\Admin\SettingsController::class, 'updateShipping'])->name('settings.shipping.update');
            Route::get('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'payment'])->name('settings.payment');
            Route::post('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePayment'])->name('settings.payment.update');
            Route::get('/settings/emails', [\App\Http\Controllers\Admin\SettingsController::class, 'emails'])->name('settings.emails');
            Route::post('/settings/emails', [\App\Http\Controllers\Admin\SettingsController::class, 'updateEmails'])->name('settings.emails.update');
            Route::post('/settings/emails/test', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.emails.test');
            Route::post('/settings/payment/test-lygos', [\App\Http\Controllers\Admin\SettingsController::class, 'testLygosPay'])->name('settings.payment.test-lygos');

            // Comptabilité
            Route::get('/accounting', [\App\Http\Controllers\Admin\AccountingController::class, 'index'])->name('accounting.index');
            Route::get('/accounting/entries', [\App\Http\Controllers\Admin\AccountingController::class, 'entries'])->name('accounting.entries');
            Route::get('/accounting/entries/create', [\App\Http\Controllers\Admin\AccountingController::class, 'createEntry'])->name('accounting.entries.create');
            Route::post('/accounting/entries', [\App\Http\Controllers\Admin\AccountingController::class, 'storeEntry'])->name('accounting.entries.store');
            Route::get('/accounting/entries/{entry}', [\App\Http\Controllers\Admin\AccountingController::class, 'showEntry'])->name('accounting.entries.show');
            Route::get('/accounting/accounts', [\App\Http\Controllers\Admin\AccountingController::class, 'accounts'])->name('accounting.accounts');
            Route::get('/accounting/balance', [\App\Http\Controllers\Admin\AccountingController::class, 'balance'])->name('accounting.balance');
            Route::get('/accounting/ledger', [\App\Http\Controllers\Admin\AccountingController::class, 'ledger'])->name('accounting.ledger');
            Route::post('/accounting/export', [\App\Http\Controllers\Admin\AccountingController::class, 'export'])->name('accounting.export');

            // Import/Export
            Route::get('/import-export', [\App\Http\Controllers\Admin\ImportExportController::class, 'index'])->name('import-export.index');
            Route::get('/import-export/export-products', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportProducts'])->name('import-export.export-products');
            Route::get('/import-export/export-categories', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportCategories'])->name('import-export.export-categories');
            Route::get('/import-export/template', [\App\Http\Controllers\Admin\ImportExportController::class, 'downloadTemplate'])->name('import-export.template');
            Route::post('/import-export/import-products', [\App\Http\Controllers\Admin\ImportExportController::class, 'importProducts'])->name('import-export.import-products');
        });
    });
});
