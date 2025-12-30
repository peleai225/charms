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

// Vérifier le statut d'une commande (pour le polling)
Route::get('/orders/{order}/status', function (Order $order) {
    return response()->json([
        'order_number' => $order->order_number,
        'status' => $order->status,
        'payment_status' => $order->payment_status,
        'paid_at' => $order->paid_at?->toISOString(),
        'total' => $order->total,
        'is_paid' => $order->payment_status === 'paid',
        'is_failed' => $order->payment_status === 'failed',
        'is_pending' => $order->payment_status === 'pending',
        'redirect_url' => $order->payment_status === 'paid' 
            ? route('checkout.success', ['order' => $order->id])
            : null,
    ]);
})->name('api.orders.status');

// Récupérer le contenu du panier (pour synchronisation)
Route::get('/cart', function (Request $request) {
    $customer = null;
    if (auth()->check()) {
        $customer = Customer::where('user_id', auth()->id())->first();
    }

    $cart = Cart::getOrCreate(session()->getId(), $customer);
    $cart->load(['items.product.images', 'items.variant']);

    return response()->json([
        'items_count' => $cart->items_count,
        'subtotal' => $cart->subtotal,
        'subtotal_formatted' => format_price($cart->subtotal),
        'discount_amount' => $cart->discount_amount,
        'total' => $cart->subtotal - $cart->discount_amount,
        'total_formatted' => format_price($cart->subtotal - $cart->discount_amount),
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
