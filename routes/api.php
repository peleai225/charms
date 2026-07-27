<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| API Temps Réel
|--------------------------------------------------------------------------
*/

// Vérifier le statut d'une commande par ID (checkout) - protégé par session web
Route::get('/orders/{order}/status', function (Order $order) {
    $allowed = false;
    if (auth()->check() && $order->customer?->user_id === auth()->id()) $allowed = true;
    if (in_array($order->id, session('checkout_order_ids', []))) $allowed = true;
    if (auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff'])) $allowed = true;
    if (!$allowed) return response()->json(['error' => 'Unauthorized'], 403);
    return response()->json([
        'order_number'  => $order->order_number,
        'status'        => $order->status,
        'payment_status'=> $order->payment_status,
        'paid_at'       => $order->paid_at?->toISOString(),
        'total'         => $order->total,
        'is_paid'       => $order->payment_status === 'paid',
        'is_failed'     => $order->payment_status === 'failed',
        'is_pending'    => $order->payment_status === 'pending',
        'redirect_url'  => $order->payment_status === 'paid'
            ? route('checkout.success', ['order' => $order->id])
            : null,
    ]);
})->middleware('web')->name('api.orders.status');

// Polling statut commande par order_number (espace client)
Route::get('/account/orders/{orderNumber}/status', function (string $orderNumber) {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 403);
    $order = Order::where('order_number', $orderNumber)
        ->where('customer_id', auth()->user()->customer?->id)
        ->first();
    if (!$order) return response()->json(['error' => 'Not found'], 404);
    return response()->json([
        'status'         => $order->status,
        'status_label'   => $order->status_label,
        'shipped_at'     => $order->shipped_at?->format('d/m/Y H:i'),
        'delivered_at'   => $order->delivered_at?->format('d/m/Y H:i'),
        'tracking_number'=> $order->tracking_number,
    ]);
})->middleware('web')->name('api.account.order.status');

// Calculer les frais de livraison
Route::post('/shipping-cost', function (Request $request) {
    $validated = $request->validate([
        'country' => 'required|string|size:2',
        'city' => 'nullable|string|max:100',
        'cart_subtotal' => 'required|numeric|min:0',
    ]);

    // Créer un objet Cart minimal pour le calcul
    $cart = new \App\Models\Cart();
    $cart->subtotal = $validated['cart_subtotal'];
    
    $checkoutController = app(\App\Http\Controllers\Front\CheckoutController::class);
    
    $shippingCost = $checkoutController->calculateShipping($cart, [
        'shipping_country' => $validated['country'],
        'shipping_city' => $validated['city'] ?? '',
    ]);

    return response()->json([
        'shipping_cost' => $shippingCost,
        'formatted' => number_format($shippingCost, 0, ',', ' ') . ' F CFA',
    ]);
})->middleware('web')->name('api.shipping-cost');

// Récupérer le contenu du panier (pour synchronisation)
Route::get('/cart', function (Request $request) {
    $customer = null;
    if (auth()->check()) {
        $customer = Customer::where('user_id', auth()->id())->first();
    }

    $cart = Cart::getOrCreate(session()->getId(), $customer);
    $cart->load(['items.product.images', 'items.variant', 'coupon']);

    return response()->json([
        'items_count' => $cart->items_count,
        'subtotal' => $cart->subtotal,
        'subtotal_formatted' => format_price($cart->subtotal),
        'discount_amount' => $cart->discount_amount,
        'discount_amount_formatted' => format_price($cart->discount_amount),
        'coupon_code' => $cart->coupon_code,
        'total' => $cart->total,
        'total_formatted' => format_price($cart->total),
        'is_empty' => $cart->is_empty,
        'items' => $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->product_variant_id,
                'name' => $item->product->name,
                'variant_name' => $item->variant?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_price_formatted' => format_price($item->unit_price),
                'total' => $item->total,
                'total_formatted' => format_price($item->total),
                'image' => $item->product->images->first()?->url,
            ];
        }),
    ]);
})->middleware('web')->name('api.cart');

// Mettre à jour la quantité d'un article
Route::patch('/cart/items/{item}', function (Request $request, $itemId) {
    $customer = null;
    if (auth()->check()) {
        $customer = Customer::where('user_id', auth()->id())->first();
    }

    $cart = Cart::getOrCreate(session()->getId(), $customer);
    $item = $cart->items()->findOrFail($itemId);

    $quantity = (int) $request->input('quantity', 1);
    
    if ($quantity <= 0) {
        $item->delete();
    } else {
        $item->update(['quantity' => $quantity]);
    }

    $cart->refresh();

    return response()->json([
        'success' => true,
        'items_count' => $cart->items_count,
        'subtotal_formatted' => format_price($cart->subtotal),
        'total_formatted' => format_price($cart->subtotal - $cart->discount_amount),
    ]);
})->middleware('web')->name('api.cart.update');

// Supprimer un article du panier
Route::delete('/cart/items/{item}', function (Request $request, $itemId) {
    $customer = null;
    if (auth()->check()) {
        $customer = Customer::where('user_id', auth()->id())->first();
    }

    $cart = Cart::getOrCreate(session()->getId(), $customer);
    $cart->items()->where('id', $itemId)->delete();

    $cart->refresh();

    return response()->json([
        'success' => true,
        'items_count' => $cart->items_count,
        'subtotal_formatted' => format_price($cart->subtotal),
        'total_formatted' => format_price($cart->subtotal - $cart->discount_amount),
    ]);
})->middleware('web')->name('api.cart.remove');

/*
|--------------------------------------------------------------------------
| Polling admin temps réel (sans Pusher)
|--------------------------------------------------------------------------
*/
Route::get('/admin/poll-stats', function (Request $request) {
    // Vérifier que l'utilisateur est admin
    if (!auth()->check() || !in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count();

    $stockAlerts = \App\Models\Product::where('status', 'active')
        ->where('track_stock', true)
        ->where(function ($q) {
            $q->where('stock_quantity', 0)
              ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
        })->count();

    // Liste des commandes en attente pour la cloche (toujours retournée)
    $pendingOrderList = \App\Models\Order::whereIn('status', ['pending', 'confirmed'])
        ->with('customer:id,first_name,last_name')
        ->latest()
        ->take(10)
        ->get(['id', 'order_number', 'total', 'status', 'billing_first_name', 'billing_last_name', 'created_at'])
        ->map(fn($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'total'        => number_format($o->total, 0, ',', ' ') . ' F',
            'status'       => $o->status,
            'customer_name'=> trim($o->billing_first_name . ' ' . $o->billing_last_name),
            'time_ago'     => $o->created_at->diffForHumans(),
            'url'          => route('admin.orders.show', $o->id),
        ]);

    // Nouvelles commandes depuis le dernier check (passé en paramètre)
    $since = $request->input('since');
    $newOrders = [];
    if ($since) {
        $newOrders = \App\Models\Order::where('created_at', '>', $since)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'total', 'status', 'created_at'])
            ->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'total'        => number_format($o->total, 0, ',', ' ') . ' F CFA',
                'status'       => $o->status,
                'url'          => route('admin.orders.show', $o->id),
            ]);
    }

    return response()->json([
        'pending_orders'     => $pendingOrders,
        'stock_alerts'       => $stockAlerts,
        'new_orders'         => $newOrders,
        'pending_order_list' => $pendingOrderList,
        'server_time'        => now()->toISOString(),
    ]);
})->middleware('web')->name('api.admin.poll-stats');


// Détail commande pour le drawer AJAX
Route::get('/admin/order-detail/{order}', function (\App\Models\Order $order) {
    if (!auth()->check() || !in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    $order->load(['items.product.images', 'items.productVariant']);
    $items = $order->items->map(function($item) {
        $img = $item->product?->images->where('is_primary', true)->first() ?? $item->product?->images->first();
        return [
            'id'      => $item->id,
            'name'    => $item->product_name,
            'variant' => $item->variant_name,
            'quantity'=> $item->quantity,
            'total'   => number_format($item->total, 0, ',', ' ') . ' F',
            'image'   => $img ? asset('storage/' . $img->path) : null,
        ];
    });
    $paymentLabels = ['pending' => 'En attente', 'paid' => 'Payée', 'failed' => 'Échouée', 'refunded' => 'Remboursée'];
    $billingAddress = collect([
        $order->billing_address,
        $order->billing_city,
        $order->billing_state,
        $order->billing_country,
    ])->filter()->implode(', ');

    return response()->json([
        'id'              => $order->id,
        'order_number'    => $order->order_number,
        'status'          => $order->status,
        'payment_status'  => $order->payment_status,
        'payment_label'   => $paymentLabels[$order->payment_status] ?? $order->payment_status,
        'payment_method'  => $order->payment_method,
        'created_at'      => $order->created_at->format('d/m/Y à H:i'),
        'customer_name'   => trim($order->billing_first_name . ' ' . $order->billing_last_name),
        'billing_email'   => $order->billing_email,
        'billing_phone'   => $order->billing_phone,
        'billing_address' => $billingAddress ?: null,
        'items'           => $items,
        'subtotal_fmt'    => number_format($order->subtotal ?? $order->total, 0, ',', ' ') . ' F',
        'total_fmt'       => number_format($order->total, 0, ',', ' ') . ' F',
        'discount_amount' => $order->discount_amount,
        'discount_fmt'    => $order->discount_amount > 0 ? number_format($order->discount_amount, 0, ',', ' ') . ' F' : null,
        'shipping_fmt'    => $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite',
        'notes'           => $order->notes,
        'show_url'        => route('admin.orders.show', $order->id),
        'invoice_url'     => route('admin.orders.invoice.view', $order->id),
        'receipt_url'     => route('admin.scanner.receipt', $order->id),
    ]);
})->middleware('web')->name('api.admin.order-detail');

// Recherche globale admin (Command Palette Ctrl+K)
Route::get('/admin/search', function (Illuminate\Http\Request $request) {
    if (!auth()->check() || !in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $q = trim($request->input('q', ''));
    if (strlen($q) < 2) {
        return response()->json(['results' => []]);
    }

    $results = [];

    // Commandes — par numéro ou nom client
    $orders = \App\Models\Order::where('order_number', 'like', "%{$q}%")
        ->orWhere('billing_first_name', 'like', "%{$q}%")
        ->orWhere('billing_last_name', 'like', "%{$q}%")
        ->orWhere('billing_email', 'like', "%{$q}%")
        ->latest()
        ->take(4)
        ->get(['id', 'order_number', 'billing_first_name', 'billing_last_name', 'total', 'status']);

    foreach ($orders as $o) {
        $results[] = [
            'type'     => 'order',
            'label'    => '# ' . $o->order_number . ' — ' . trim($o->billing_first_name . ' ' . $o->billing_last_name),
            'sublabel' => number_format($o->total, 0, ',', ' ') . ' F · ' . $o->status,
            'url'      => route('admin.orders.show', $o->id),
            'icon'     => 'order',
        ];
    }

    // Produits — par nom ou SKU
    $products = \App\Models\Product::with('images')
        ->where('name', 'like', "%{$q}%")
        ->orWhere('sku', 'like', "%{$q}%")
        ->orWhere('barcode', 'like', "%{$q}%")
        ->take(4)
        ->get(['id', 'name', 'sku', 'price', 'status']);

    foreach ($products as $p) {
        $results[] = [
            'type'     => 'product',
            'label'    => $p->name,
            'sublabel' => 'SKU: ' . ($p->sku ?? '—') . ' · ' . number_format($p->price, 0, ',', ' ') . ' F',
            'url'      => route('admin.products.edit', $p->id),
            'icon'     => 'product',
        ];
    }

    // Clients — par nom ou email
    $customers = \App\Models\Customer::where('first_name', 'like', "%{$q}%")
        ->orWhere('last_name', 'like', "%{$q}%")
        ->orWhere('email', 'like', "%{$q}%")
        ->orWhere('phone', 'like', "%{$q}%")
        ->take(3)
        ->get(['id', 'first_name', 'last_name', 'email']);

    foreach ($customers as $c) {
        $results[] = [
            'type'     => 'customer',
            'label'    => trim($c->first_name . ' ' . $c->last_name),
            'sublabel' => $c->email,
            'url'      => route('admin.customers.show', $c->id),
            'icon'     => 'customer',
        ];
    }

    return response()->json(['results' => $results]);
})->middleware('web')->name('api.admin.search');
