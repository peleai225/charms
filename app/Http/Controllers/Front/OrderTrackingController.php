<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Page de recherche de commande (invités)
     */
    public function index()
    {
        return view('front.order-tracking.index');
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

        return view('front.order-tracking.show', compact('order'));
    }
}
