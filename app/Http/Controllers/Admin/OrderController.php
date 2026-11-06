<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderCancelled;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Listeners\RestoreStockOnCancel;
use App\Mail\OrderShipped;
use App\Mail\OrderStatusChanged;
use App\Models\ActivityLog;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $query = Order::with(['customer', 'items', 'courier:id,name'])
            ->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($payment = $request->payment_status) {
            $query->where('payment_status', $payment);
        }

        if ($from = $request->date_from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->date_to) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Période choisie dans la liste (« Aujourd'hui », « Cette semaine », « Ce mois »)
        $periodStart = match ($request->date_range) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
        if ($periodStart) {
            $query->where('created_at', '>=', $periodStart);
        }

        $perPage = in_array((int) $request->per_page, [20, 50, 100], true) ? (int) $request->per_page : 20;
        $orders = $query->paginate($perPage)->withQueryString();

        $stats = [
            'pending'     => Order::where('status', 'pending')->count(),
            'processing'  => Order::whereIn('status', ['confirmed', 'processing'])->count(),
            'shipped'     => Order::where('status', 'shipped')->count(),
            'today_count' => Order::whereDate('created_at', today())->count(),
            'today_total' => Order::whereDate('created_at', today())->sum('total'),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders'  => $orders->through(fn ($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'status'         => $o->status,
                'payment_status' => $o->payment_status,
                'total'          => $o->total,
                'items_count'    => $o->items->sum('quantity'),
                'customer_name'  => trim($o->billing_first_name . ' ' . $o->billing_last_name),
                'billing_email'  => $o->billing_email,
                'billing_phone'  => $o->billing_phone,
                'created_at_fmt' => $o->created_at->format('d/m/Y H:i'),
                'courier_name'   => $o->status === Order::STATUS_DELIVERY_IN_PROGRESS ? $o->courier?->name : null,
            ]),
            'stats'   => $stats,
            'couriers' => \App\Models\Courier::active()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'payment_status', 'date_from', 'date_to', 'date_range', 'per_page']),
        ]);
    }

    /**
     * Détails d'une commande
     */
    public function show(Order $order)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $order->load([
            'customer',
            'items.product.images',
            'items.productVariant',
            'payments',
            'refunds',
        ]);

        // Timeline des événements
        $timeline = ActivityLog::where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $orderData = [
            'id'                  => $order->id,
            'order_number'        => $order->order_number,
            'status'              => $order->status,
            'payment_status'      => $order->payment_status,
            'payment_status_label'=> $order->payment_status_label,
            'payment_method'      => $order->payment_method,
            'payment_method_label'=> $order->payment_method_label,
            'subtotal'            => $order->subtotal,
            'tax_amount'          => $order->tax_amount,
            'shipping_amount'     => $order->shipping_amount,
            'discount_amount'     => $order->discount_amount,
            'total'               => $order->total,
            'coupon_code'         => $order->coupon_code,
            'tracking_number'     => $order->tracking_number,
            'shipping_carrier'    => $order->shipping_carrier,
            'customer_id'         => $order->customer_id,
            'billing_first_name'  => $order->billing_first_name,
            'billing_last_name'   => $order->billing_last_name,
            'billing_email'       => $order->billing_email,
            'billing_phone'       => $order->billing_phone,
            'billing_address'     => $order->billing_address,
            'billing_address_2'   => $order->billing_address_2,
            'billing_city'        => $order->billing_city,
            'billing_postal_code' => $order->billing_postal_code,
            'billing_country'     => $order->billing_country,
            'shipping_first_name' => $order->shipping_first_name,
            'shipping_last_name'  => $order->shipping_last_name,
            'shipping_phone'      => $order->shipping_phone,
            'shipping_address'    => $order->shipping_address,
            'shipping_address_2'  => $order->shipping_address_2,
            'shipping_city'       => $order->shipping_city,
            'shipping_postal_code'=> $order->shipping_postal_code,
            'shipping_country'    => $order->shipping_country,
            'customer_notes'      => $order->customer_notes,
            'admin_notes'         => $order->admin_notes,
            'created_at_fmt'      => $order->created_at->format('d/m/Y H:i'),
            'shipped_at_fmt'      => $order->shipped_at?->format('d/m/Y H:i'),
            'delivered_at_fmt'    => $order->delivered_at?->format('d/m/Y H:i'),
            'paid_at_fmt'         => $order->paid_at?->format('d/m/Y H:i'),
            'items'               => $order->items->map(fn ($item) => [
                'id'           => $item->id,
                'name'         => $item->name ?? $item->product?->name,
                'variant_name' => $item->product_variant?->name ?? $item->variant_name,
                'sku'          => $item->sku,
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
                'total'        => $item->total,
                'image_url'    => $item->product?->images->first()
                    ? asset('storage/' . $item->product->images->first()->path)
                    : null,
            ]),
        ];

        return Inertia::render('Admin/Orders/Show', [
            'order'    => $orderData,
            'timeline' => $timeline->map(fn ($log) => [
                'id'          => $log->id,
                'action'      => $log->action,
                'description' => $log->description,
                'created_at'  => $log->created_at->format('d/m/Y H:i'),
            ]),
        ]);
    }

    /**
     * Modifier le statut
     */
    /**
     * Faire avancer plusieurs commandes d'un coup (rattrapage du suivi, journée de livraison).
     * Aucun message n'est envoyé aux clients : on régularise un statut, on ne les prévient pas en masse.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer|exists:orders,id',
            'status' => 'required|in:processing,shipped,delivered,cancelled',
        ], [
            'status.in' => 'Ce statut ne peut pas être appliqué en masse.',
        ]);

        $status = $validated['status'];
        $changed = 0;
        $skipped = 0;
        $withCourier = 0;

        foreach (Order::with('items.product', 'items.productVariant')->whereIn('id', $validated['ids'])->get() as $order) {
            $old = $order->status;

            // Un colis chez un livreur ne sort de chez lui que par le point (ou la reprise) : l'argent doit passer par son solde
            if ($old === Order::STATUS_DELIVERY_IN_PROGRESS) {
                $withCourier++;

                continue;
            }

            // Une commande annulée/remboursée ne revit pas, une commande livrée ne s'annule pas ici (c'est un retour)
            $locked = in_array($old, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)
                || ($old === Order::STATUS_DELIVERED && $status === Order::STATUS_CANCELLED);
            if ($old === $status || $locked) {
                $skipped++;

                continue;
            }

            $updates = ['status' => $status];
            if ($status === Order::STATUS_SHIPPED && ! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
            if ($status === Order::STATUS_DELIVERED) {
                $updates['delivered_at'] = $order->delivered_at ?? now();
                // Paiement à la livraison : l'argent est encaissé à la remise du colis
                if ($order->payment_method === 'cod' && $order->payment_status !== Order::PAYMENT_PAID) {
                    $updates['payment_status'] = Order::PAYMENT_PAID;
                    $updates['paid_at'] = now();
                }
            }

            $order->update($updates);

            if ($status === Order::STATUS_CANCELLED) {
                // Remise en stock sans l'événement d'annulation, qui notifierait le client
                app(RestoreStockOnCancel::class)->handle(new OrderCancelled($order, 'Annulation groupée'));
            }

            ActivityLog::log('order_status_changed', "Statut modifié en masse : {$old} → {$status}", $order);
            $changed++;
        }

        Cache::forget('admin_pending_orders_count');

        $message = "{$changed} commande(s) mise(s) à jour. Aucun message n'a été envoyé aux clients.";
        if ($skipped) {
            $message .= " {$skipped} ignorée(s) (déjà dans ce statut, annulée, ou livrée).";
        }
        if ($withCourier) {
            $message .= " {$withCourier} chez un livreur : faites le point depuis la page Livreurs.";
        }

        return back()->with('success', $message);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            // « Livreur en route » se pose uniquement en confiant la commande à un livreur
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:500',
        ], [
            'status.in' => 'Pour mettre une commande « Livreur en route », confiez-la à un livreur depuis la liste des commandes.',
        ]);

        if ($order->status === Order::STATUS_DELIVERY_IN_PROGRESS) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => 'Cette commande est chez '.($order->courier?->name ?? 'un livreur').' : faites le point ou reprenez-la depuis la page Livreurs.',
            ]);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Mise à jour
        $order->update([
            'status' => $newStatus,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
            'shipping_carrier' => $request->shipping_carrier ?? $order->shipping_carrier,
            'admin_notes' => $request->admin_notes ?? $order->admin_notes,
        ]);

        // Actions spécifiques selon le statut
        if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
            $order->update(['shipped_at' => now()]);

            if ($order->billing_email) {
                try {
                    Mail::to($order->billing_email)->queue(new OrderShipped($order));
                } catch (\Exception $e) {
                    \Log::error('Failed to queue shipping email: ' . $e->getMessage());
                }
            }
        }

        if ($newStatus === 'delivered') {
            $order->update(['delivered_at' => now()]);

            // COD : marquer automatiquement comme payé à la livraison
            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
            }
        }

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            event(new OrderCancelled($order, $request->admin_notes ?? ''));
        }

        // Broadcast temps réel au client
        event(new OrderStatusUpdated($order, $oldStatus));

        // Log
        ActivityLog::log(
            'order_status_changed',
            "Statut modifié : {$oldStatus} → {$newStatus}",
            $order
        );

        // Email de changement de statut (en queue pour ne pas bloquer la réponse)
        if ($order->billing_email && $newStatus !== 'cancelled') {
            try {
                Mail::to($order->billing_email)->queue(new OrderStatusChanged($order, $oldStatus));
            } catch (\Exception $e) {
                \Log::error('Failed to queue status email: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'message'          => 'Statut mis à jour',
                'status'           => $order->status,
                'status_label'     => $order->status_label,
                'status_color'     => $order->status_color,
                'shipped_at'       => $order->shipped_at?->format('d/m/Y H:i'),
                'delivered_at'     => $order->delivered_at?->format('d/m/Y H:i'),
                'tracking_number'  => $order->tracking_number,
                'shipping_carrier' => $order->shipping_carrier,
            ]);
        }

        return back()->with('success', 'Statut mis à jour avec succès.');
    }

    /**
     * Générer la facture PDF
     */
    public function invoice(Order $order)
    {
        $order->load(['customer', 'items.product', 'items.productVariant']);

        $pdf = Pdf::loadView('admin.orders.invoice', compact('order'));
        
        return $pdf->download("facture-{$order->order_number}.pdf");
    }

    /**
     * Voir la facture en ligne
     */
    public function viewInvoice(Order $order)
    {
        $order->load(['customer', 'items.product', 'items.productVariant']);

        $pdf = Pdf::loadView('admin.orders.invoice', compact('order'));
        
        return $pdf->stream("facture-{$order->order_number}.pdf");
    }

    /**
     * Ajouter une note
     */
    public function addNote(Request $request, Order $order)
    {
        $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $notes = $order->admin_notes ? $order->admin_notes . "\n\n" : '';
        $notes .= "[" . now()->format('d/m/Y H:i') . "] " . $request->note;

        $order->update(['admin_notes' => $notes]);

        ActivityLog::log('order_note_added', "Note ajoutée", $order);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'admin_notes' => $order->fresh()->admin_notes]);
        }

        return back()->with('success', 'Note ajoutée.');
    }

    /**
     * Renvoyer l'email de confirmation
     */
    public function resendConfirmation(Order $order)
    {
        $isAjax = request()->wantsJson() || request()->ajax();

        if ($order->billing_email) {
            try {
                // Configurer la connexion mail depuis les paramètres
                \App\Services\MailConfigService::configureFromSettings();

                Mail::to($order->billing_email)->send(new \App\Mail\OrderConfirmation($order));
                if ($isAjax) return response()->json(['success' => true, 'message' => 'Email envoyé.']);
                return back()->with('success', 'Email de confirmation renvoyé.');
            } catch (\Exception $e) {
                if ($isAjax) return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 422);
                return back()->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
            }
        }

        if ($isAjax) return response()->json(['success' => false, 'message' => 'Aucune adresse email.'], 422);
        return back()->with('error', 'Aucune adresse email.');
    }
}
