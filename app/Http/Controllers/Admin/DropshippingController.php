<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSupplier;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DropshippingController extends Controller
{
    /**
     * Liste des commandes fournisseurs
     */
    public function index(Request $request)
    {
        $query = OrderSupplier::with(['order', 'supplier']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        $orderSuppliers = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->get();
        $statuses = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En traitement',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        return view('admin.dropshipping.index', compact('orderSuppliers', 'suppliers', 'statuses'));
    }

    /**
     * Détails d'une commande fournisseur
     */
    public function show(OrderSupplier $orderSupplier)
    {
        $orderSupplier->load(['order.items.product', 'order.customer', 'supplier']);

        return view('admin.dropshipping.show', compact('orderSupplier'));
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus(Request $request, OrderSupplier $orderSupplier)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'shipping_carrier' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $orderSupplier->status;
        $orderSupplier->update($validated);

        // Si expédié, mettre à jour la date
        if ($validated['status'] === 'shipped' && $oldStatus !== 'shipped') {
            $orderSupplier->markAsShipped(
                $validated['tracking_number'] ?? null,
                $validated['shipping_carrier'] ?? null
            );
        }

        // Si livré, mettre à jour la date
        if ($validated['status'] === 'delivered' && $oldStatus !== 'delivered') {
            $orderSupplier->markAsDelivered();
        }

        return back()->with('success', 'Statut mis à jour avec succès.');
    }
}
