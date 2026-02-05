<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderCancelled;
use App\Http\Controllers\Controller;
use App\Mail\OrderShipped;
use App\Mail\OrderStatusChanged;
use App\Models\ActivityLog;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Liste des commandes
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items'])
            ->withCount('items');

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('billing_email', 'like', "%{$search}%")
                    ->orWhere('billing_first_name', 'like', "%{$search}%")
                    ->orWhere('billing_last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Statistiques
        $stats = [
            'pending' => Order::pending()->count(),
            'processing' => Order::processing()->count(),
            'shipped' => Order::shipped()->count(),
            'today_total' => Order::whereDate('created_at', today())->sum('total'),
            'today_count' => Order::whereDate('created_at', today())->count(),
        ];

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Détails d'une commande
     */
    public function show(Order $order)
    {
        $order->load([
            'customer',
            'items.product.images',
            'items.productVariant',
            'payments',
        ]);

        // Timeline des événements
        $timeline = ActivityLog::where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.show', compact('order', 'timeline'));
    }

    /**
     * Modifier le statut
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:500',
        ]);

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
            
            // Envoyer l'email de suivi
            if ($order->billing_email) {
                try {
                    // Configurer la connexion mail depuis les paramètres
                    \App\Services\MailConfigService::configureFromSettings();
                    
                    Mail::to($order->billing_email)->send(new OrderShipped($order));
                } catch (\Exception $e) {
                    \Log::error('Failed to send shipping email: ' . $e->getMessage());
                }
            }
        }

        if ($newStatus === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            event(new OrderCancelled($order, $request->admin_notes ?? ''));
        }

        // Log
        ActivityLog::log(
            'order_status_changed',
            "Statut modifié : {$oldStatus} → {$newStatus}",
            $order
        );

        // Envoyer email de changement de statut
        if ($order->billing_email && $newStatus !== 'cancelled') {
            try {
                // Configurer la connexion mail depuis les paramètres
                \App\Services\MailConfigService::configureFromSettings();
                
                Mail::to($order->billing_email)->send(new OrderStatusChanged($order, $oldStatus));
            } catch (\Exception $e) {
                \Log::error('Failed to send status email: ' . $e->getMessage());
            }
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

        return back()->with('success', 'Note ajoutée.');
    }

    /**
     * Renvoyer l'email de confirmation
     */
    public function resendConfirmation(Order $order)
    {
        if ($order->billing_email) {
            try {
                // Configurer la connexion mail depuis les paramètres
                \App\Services\MailConfigService::configureFromSettings();
                
                Mail::to($order->billing_email)->send(new \App\Mail\OrderConfirmation($order));
                return back()->with('success', 'Email de confirmation renvoyé.');
            } catch (\Exception $e) {
                return back()->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Aucune adresse email.');
    }
}
