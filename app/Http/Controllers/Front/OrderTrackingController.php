<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderTrackingController extends Controller
{
    /**
     * Page de recherche de commande (invités)
     */
    public function index()
    {
        return Inertia::render('OrderTracking/Index', [
            'whatsapp_number' => config('app.whatsapp_number', '2250506805382'),
        ]);
    }

    /**
     * Afficher le suivi d'une commande (accès par numéro + email)
     */
    public function show(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
            'email' => 'required|email',
        ]);

        $order = Order::where('order_number', $request->order_number)->first();

        if (!$order) {
            return back()->with('error', 'Aucune commande trouvée avec ce numéro.');
        }

        // Vérifier que l'email correspond (billing ou shipping)
        $email = strtolower(trim($request->email));
        $orderEmail = strtolower(trim($order->billing_email ?? ''));
        $shippingEmail = strtolower(trim($order->shipping_email ?? ''));

        if ($orderEmail !== $email && $shippingEmail !== $email) {
            return back()->with('error', 'L\'email ne correspond pas à cette commande.');
        }

        // Client connecté : vérifier qu'il a le droit (optionnel, on a déjà vérifié l'email)
        if (auth()->check() && auth()->user()->customer && $order->customer_id === auth()->user()->customer->id) {
            // OK, c'est son compte
        }

        $order->load([
            'items.product.images',
            'items.productVariant.attributeValues',
        ]);

        // Format data for Inertia
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'total' => $order->total,
            'subtotal' => $order->subtotal,
            'shipping_amount' => $order->shipping_amount,
            'discount_amount' => $order->discount_amount,
            'created_at' => $order->created_at->toISOString(),
            'paid_at' => $order->paid_at?->toISOString(),
            'shipped_at' => $order->shipped_at?->toISOString(),
            'delivered_at' => $order->delivered_at?->toISOString(),
            'cancellation_reason' => $order->cancellation_reason,
            'shipping_first_name' => $order->shipping_first_name,
            'shipping_last_name' => $order->shipping_last_name,
            'shipping_address' => $order->shipping_address,
            'shipping_city' => $order->shipping_city,
            'shipping_country' => $order->shipping_country,
            'shipping_phone' => $order->shipping_phone,
            'items_count' => $order->items->sum('quantity'),
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'image' => $item->product?->images->where('is_primary', true)->first()?->path ?? $item->product?->images->first()?->path,
                ];
            }),
        ];

        return Inertia::render('OrderTracking/Show', [
            'order' => $orderData,
            'whatsapp_number' => config('app.whatsapp_number', '2250506805382'),
        ]);
    }
}
