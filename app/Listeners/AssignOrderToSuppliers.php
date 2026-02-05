<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\SupplierOrderNotification;
use App\Models\OrderSupplier;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AssignOrderToSuppliers
{
    /**
     * Handle the event.
     * 
     * Attribue automatiquement les produits de la commande aux fournisseurs
     * pour les produits en dropshipping.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        
        // Grouper les items par fournisseur
        $supplierItems = [];
        
        foreach ($order->items as $item) {
            $product = $item->product;
            
            // Ignorer si le produit n'est pas en dropshipping
            if (!$product || !$product->is_dropshipping) {
                continue;
            }
            
            // Trouver le fournisseur principal
            $supplier = $product->suppliers()
                ->wherePivot('is_primary', true)
                ->first();
            
            // Si pas de fournisseur principal, prendre le premier
            if (!$supplier) {
                $supplier = $product->suppliers()->first();
            }
            
            if (!$supplier) {
                Log::warning("Produit {$product->id} en dropshipping sans fournisseur", [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ]);
                continue;
            }
            
            // Grouper par fournisseur
            if (!isset($supplierItems[$supplier->id])) {
                $supplierItems[$supplier->id] = [
                    'supplier' => $supplier,
                    'items' => [],
                    'subtotal' => 0,
                ];
            }
            
            // Récupérer le prix d'achat depuis la relation pivot
            $supplierRelation = $product->suppliers()->where('suppliers.id', $supplier->id)->first();
            $purchasePrice = $supplierRelation?->pivot->purchase_price ?? $product->purchase_price ?? 0;
            
            $supplierItems[$supplier->id]['items'][] = [
                'order_item' => $item,
                'product' => $product,
                'purchase_price' => $purchasePrice,
            ];
            
            $supplierItems[$supplier->id]['subtotal'] += $purchasePrice * $item->quantity;
        }
        
        // Créer les commandes fournisseurs
        foreach ($supplierItems as $supplierId => $data) {
            $supplier = $data['supplier'];
            $subtotal = $data['subtotal'];
            
            // Calculer le coût de livraison (simplifié, à adapter selon vos besoins)
            $shippingCost = 0; // À calculer selon la logique métier
            
            $orderSupplier = OrderSupplier::create([
                'order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'status' => OrderSupplier::STATUS_PENDING,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
            ]);
            
            // Envoyer l'email au fournisseur
            if ($supplier->email || $supplier->contact_email) {
                $email = $supplier->contact_email ?? $supplier->email;
                
                try {
                    // Configurer la connexion mail depuis les paramètres
                    MailConfigService::configureFromSettings();
                    
                    Mail::to($email)->send(new SupplierOrderNotification($orderSupplier, $order, $data['items']));
                } catch (\Exception $e) {
                    Log::error("Erreur envoi email fournisseur", [
                        'supplier_id' => $supplier->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
