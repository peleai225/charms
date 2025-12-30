<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Affiche le détail d'une commande pour le client
     */
    public function showOrder(Order $order)
    {
        // Vérifier que le client est bien le propriétaire de la commande
        $customer = auth()->user()->customer;
        
        if (!$customer || $order->customer_id !== $customer->id) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        $order->load([
            'items.product.images',
            'items.productVariant.attributeValues',
            'payments'
        ]);

        return view('front.account.orders.show', compact('order'));
    }
}

