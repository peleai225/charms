<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
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

    /**
     * Afficher les adresses du client
     */
    public function addresses()
    {
        $customer = auth()->user()->customer ?? null;
        $addresses = $customer ? $customer->addresses()->where('type', 'shipping')->get() : collect();

        return view('front.account.addresses', compact('customer', 'addresses'));
    }

    /**
     * Enregistrer une nouvelle adresse
     */
    public function storeAddress(Request $request)
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return back()->with('error', 'Profil client non trouvé.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'type' => 'shipping',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'address_line1' => $validated['address'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'phone' => $validated['phone'] ?? null,
            'is_default' => $request->boolean('is_default'),
        ]);

        if ($address->is_default) {
            $address->setAsDefault();
        }

        return back()->with('success', 'Adresse ajoutée avec succès.');
    }
}

