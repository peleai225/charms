<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderRefunded;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RefundController extends Controller
{
    /**
     * Liste des remboursements
     */
    public function index(Request $request)
    {
        $query = Refund::with(['order', 'payment', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $refunds = $query->latest()->paginate(20)->withQueryString();

        $refundsData = $refunds->through(fn ($refund) => [
            'id'                => $refund->id,
            'refund_number'     => $refund->refund_number,
            'order_id'          => $refund->order_id,
            'order_number'      => $refund->order?->order_number,
            'amount'            => $refund->amount,
            'reason'            => $refund->reason,
            'reason_label'      => $refund->reason_label,
            'notes'             => $refund->notes,
            'status'            => $refund->status,
            'processed_by_name' => $refund->processedBy?->name,
            'created_at_fmt'    => $refund->created_at->format('d/m/Y H:i'),
        ]);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => $refundsData,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Créer un remboursement pour une commande
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|in:customer_request,product_defective,wrong_item,not_delivered,duplicate,other',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$order->is_refundable) {
            return back()->with('error', 'Cette commande n\'est pas remboursable.');
        }

        $amount = (float) $request->amount;
        $maxRefundable = $this->getMaxRefundableAmount($order);

        if ($amount > $maxRefundable) {
            return back()->with('error', "Le montant maximum remboursable est de " . number_format($maxRefundable, 0, ',', ' ') . " F.");
        }

        DB::beginTransaction();

        try {
            $payment = $order->payments()->where('status', Payment::STATUS_COMPLETED)->first();

            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_id' => $payment?->id,
                'amount' => $amount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => Refund::STATUS_PROCESSED,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            if ($payment && $payment->is_refundable) {
                $refundAmount = min($amount, $payment->remaining_refundable);
                if ($refundAmount > 0) {
                    $payment->refund($refundAmount);
                }
            }

            $totalRefunded = $order->refunds()->sum('amount');
            if ($totalRefunded >= $order->total) {
                $order->update([
                    'status' => Order::STATUS_REFUNDED,
                    'payment_status' => Order::PAYMENT_REFUNDED,
                ]);
            }

            OrderRefunded::dispatch($order, $refund);

            DB::commit();

            return back()->with('success', "Remboursement {$refund->refund_number} créé avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Refund error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du remboursement: ' . $e->getMessage());
        }
    }

    protected function getMaxRefundableAmount(Order $order): float
    {
        $totalRefunded = $order->refunds()->sum('amount');
        return max(0, (float) $order->total - $totalRefunded);
    }
}
