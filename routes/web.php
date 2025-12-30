<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Front\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Front-office (Site public)
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentification Client
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [CustomerAuthController::class, 'login'])->name('login.post');
    Route::get('/inscription', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/inscription', [CustomerAuthController::class, 'register'])->name('register.post');
    Route::get('/mot-de-passe-oublie', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [CustomerAuthController::class, 'sendResetLink'])->name('password.email');
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
    Route::get('/adresses', function () {
        return view('front.account.addresses');
    })->name('addresses');
});

// Catalogue
Route::get('/boutique', [App\Http\Controllers\Front\ShopController::class, 'index'])->name('shop.index');
Route::get('/categorie/{slug}', [App\Http\Controllers\Front\ShopController::class, 'category'])->name('shop.category');
Route::get('/produit/{slug}', [App\Http\Controllers\Front\ShopController::class, 'product'])->name('shop.product');
Route::get('/produit/{product}/variant', [App\Http\Controllers\Front\ShopController::class, 'getVariant'])->name('shop.variant');

// Panier
Route::get('/panier', [App\Http\Controllers\Front\CartController::class, 'index'])->name('cart.index');
Route::post('/panier/ajouter', [App\Http\Controllers\Front\CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{item}', [App\Http\Controllers\Front\CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{item}', [App\Http\Controllers\Front\CartController::class, 'remove'])->name('cart.remove');
Route::delete('/panier', [App\Http\Controllers\Front\CartController::class, 'clear'])->name('cart.clear');
Route::post('/panier/coupon', [App\Http\Controllers\Front\CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/panier/coupon', [App\Http\Controllers\Front\CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/panier/count', [App\Http\Controllers\Front\CartController::class, 'count'])->name('cart.count');

// Checkout
Route::get('/commander', [App\Http\Controllers\Front\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/commander', [App\Http\Controllers\Front\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/commander/paiement/{order}', [App\Http\Controllers\Front\CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/commander/paiement/{order}', [App\Http\Controllers\Front\CheckoutController::class, 'processPayment'])->name('checkout.process-payment');
Route::get('/checkout/confirmation', [App\Http\Controllers\Front\CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::get('/checkout/annulation', [App\Http\Controllers\Front\CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::get('/commande/succes', [App\Http\Controllers\Front\CheckoutController::class, 'success'])->name('checkout.success');

// Webhook CinetPay (sans CSRF)
Route::post('/webhook/cinetpay', [App\Http\Controllers\Webhook\CinetPayWebhookController::class, 'handle'])->name('webhook.cinetpay')->withoutMiddleware(['web']);
Route::get('/webhook/cinetpay', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'CinetPay Webhook Endpoint',
        'info' => 'Ce endpoint accepte uniquement les requêtes POST de CinetPay.',
    ]);
})->withoutMiddleware(['web']);

// Pages statiques
Route::get('/contact', function () {
    return view('front.pages.contact');
})->name('contact');

Route::get('/a-propos', function () {
    return view('front.pages.about');
})->name('about');

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
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Routes protégées (nécessite rôle admin/manager/staff)
    Route::middleware('admin')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Produits
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->names('products');
        Route::post('products/{product}/variants', [\App\Http\Controllers\Admin\ProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::delete('products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
        Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::post('products/{product}/images/{image}/primary', [\App\Http\Controllers\Admin\ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        
        // Catégories
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->names('categories');
        
        // Commandes
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->names('orders');
        Route::patch('orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/{order}/invoice/view', [\App\Http\Controllers\Admin\OrderController::class, 'viewInvoice'])->name('orders.invoice.view');
        Route::post('orders/{order}/note', [\App\Http\Controllers\Admin\OrderController::class, 'addNote'])->name('orders.note');
        Route::post('orders/{order}/resend', [\App\Http\Controllers\Admin\OrderController::class, 'resendConfirmation'])->name('orders.resend');
        
        // Clients
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->names('customers');
        
        // Stock
        Route::get('/stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/movements', [\App\Http\Controllers\Admin\StockController::class, 'movements'])->name('stock.movements');
        Route::get('/stock/movements/create', [\App\Http\Controllers\Admin\StockController::class, 'createMovement'])->name('stock.create-movement');
        Route::post('/stock/movements', [\App\Http\Controllers\Admin\StockController::class, 'storeMovement'])->name('stock.store-movement');
        Route::get('/stock/reception', [\App\Http\Controllers\Admin\StockController::class, 'reception'])->name('stock.reception');
        Route::post('/stock/reception', [\App\Http\Controllers\Admin\StockController::class, 'storeReception'])->name('stock.store-reception');
        Route::get('/stock/inventory', [\App\Http\Controllers\Admin\StockController::class, 'inventory'])->name('stock.inventory');
        Route::post('/stock/inventory/adjust', [\App\Http\Controllers\Admin\StockController::class, 'adjustInventory'])->name('stock.adjust-inventory');
        Route::get('/stock/alerts', [\App\Http\Controllers\Admin\StockController::class, 'alerts'])->name('stock.alerts');
        
        // Fournisseurs
        Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->names('suppliers');
        
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

        // Rapports
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/products', [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/customers', [\App\Http\Controllers\Admin\ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/stock', [\App\Http\Controllers\Admin\ReportController::class, 'stock'])->name('reports.stock');

        // Codes-barres & QR codes
        Route::get('/barcodes', [\App\Http\Controllers\Admin\BarcodeController::class, 'index'])->name('barcodes.index');
        Route::get('/barcodes/{product}/generate', [\App\Http\Controllers\Admin\BarcodeController::class, 'generateBarcode'])->name('barcodes.generate');
        Route::get('/barcodes/{product}/qrcode', [\App\Http\Controllers\Admin\BarcodeController::class, 'generateQrCode'])->name('barcodes.qrcode');
        Route::get('/barcodes/{product}/qrcode-image', [\App\Http\Controllers\Admin\BarcodeController::class, 'showQrCode'])->name('barcodes.qrcode-image');
        Route::get('/barcodes/print-labels', [\App\Http\Controllers\Admin\BarcodeController::class, 'printLabels'])->name('barcodes.print-labels');
        Route::post('/barcodes/scan', [\App\Http\Controllers\Admin\BarcodeController::class, 'scan'])->name('barcodes.scan');
        Route::post('/barcodes/bulk-generate', [\App\Http\Controllers\Admin\BarcodeController::class, 'bulkGenerate'])->name('barcodes.bulk-generate');

        // Scanner / Mode Caisse (POS)
        Route::get('/scanner', [\App\Http\Controllers\Admin\ScannerController::class, 'index'])->name('scanner.index');
        Route::post('/scanner/scan', [\App\Http\Controllers\Admin\ScannerController::class, 'scan'])->name('scanner.scan');
        Route::get('/scanner/cart', [\App\Http\Controllers\Admin\ScannerController::class, 'getCart'])->name('scanner.cart');
        Route::post('/scanner/cart/add', [\App\Http\Controllers\Admin\ScannerController::class, 'addToCart'])->name('scanner.cart.add');
        Route::patch('/scanner/cart/{key}', [\App\Http\Controllers\Admin\ScannerController::class, 'updateCartItem'])->name('scanner.cart.update');
        Route::delete('/scanner/cart/{key}', [\App\Http\Controllers\Admin\ScannerController::class, 'removeCartItem'])->name('scanner.cart.remove');
        Route::delete('/scanner/cart', [\App\Http\Controllers\Admin\ScannerController::class, 'clearCart'])->name('scanner.cart.clear');
        Route::post('/scanner/checkout', [\App\Http\Controllers\Admin\ScannerController::class, 'checkout'])->name('scanner.checkout');
        Route::post('/scanner/stock-movement', [\App\Http\Controllers\Admin\ScannerController::class, 'stockMovement'])->name('scanner.stock-movement');

        // Coupons / Codes promo
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->names('coupons');
        Route::get('/coupons-generate-code', [\App\Http\Controllers\Admin\CouponController::class, 'generateCode'])->name('coupons.generate-code');

        // Bannières
        Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->names('banners');

        // Paramètres
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/shipping', [\App\Http\Controllers\Admin\SettingsController::class, 'shipping'])->name('settings.shipping');
        Route::post('/settings/shipping', [\App\Http\Controllers\Admin\SettingsController::class, 'updateShipping'])->name('settings.shipping.update');
        Route::get('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'payment'])->name('settings.payment');
        Route::post('/settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePayment'])->name('settings.payment.update');
        Route::get('/settings/emails', [\App\Http\Controllers\Admin\SettingsController::class, 'emails'])->name('settings.emails');
        Route::post('/settings/emails', [\App\Http\Controllers\Admin\SettingsController::class, 'updateEmails'])->name('settings.emails.update');

        // Profil admin
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Utilisateurs admin
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    });
});
