<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Courier;
use App\Models\CourierMovement;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Le « point » avec un livreur : commandes livrées ou non, argent encaissé, frais, montant remis.
 * Chaque point et chaque versement devient un mouvement figé ; le solde du livreur en est la somme.
 */
class CourierSettlement
{
    public const ISSUES = ['absent', 'injoignable', 'refus', 'reportee', 'autre'];

    /**
     * Commandes en route chez le livreur, avec ce qu'il doit encaisser et ses frais.
     */
    public function preview(Courier $courier): array
    {
        return $courier->ordersOnRoute()->orderBy('dispatched_at')->get()->map(fn (Order $o) => [
            'order_id' => $o->id,
            'order_number' => $o->order_number,
            'customer' => trim($o->shipping_first_name.' '.$o->shipping_last_name),
            'phone' => $o->shipping_phone ?: $o->billing_phone,
            'city' => $o->shipping_city,
            'address' => $o->shipping_address,
            'to_collect' => $this->toCollect($o),
            'fee' => $o->courier_fee === null ? null : (float) $o->courier_fee,
            'attempts' => (int) $o->delivery_attempts,
        ])->values()->all();
    }

    /**
     * @param  array<int, array{outcome: string, issue?: ?string, fee?: mixed}>  $outcomes  indexé par id de commande
     */
    public function settle(Courier $courier, array $outcomes, float $amountReceived, ?string $note, ?int $userId): CourierMovement
    {
        return DB::transaction(function () use ($courier, $outcomes, $amountReceived, $note, $userId) {
            // Verrou : deux envois du même point (double appui, réseau) ne peuvent pas passer tous les deux
            $orders = Order::whereIn('id', array_keys($outcomes))->lockForUpdate()->get()->keyBy('id');

            foreach (array_keys($outcomes) as $id) {
                $order = $orders->get($id);
                if (! $order || (int) $order->courier_id !== $courier->id || $order->status !== Order::STATUS_DELIVERY_IN_PROGRESS) {
                    $label = $order?->order_number ?? "#{$id}";
                    throw ValidationException::withMessages([
                        'outcomes' => "La commande {$label} n'est plus chez ce livreur : ce point a peut-être déjà été enregistré.",
                    ]);
                }
            }

            $collected = 0.0;
            $fees = 0.0;
            $lines = [];

            foreach ($outcomes as $id => $result) {
                $order = $orders[$id];
                $delivered = $result['outcome'] === 'delivered';
                $fee = isset($result['fee']) && $result['fee'] !== '' ? (float) $result['fee'] : (float) ($order->courier_fee ?? 0);
                $toCollect = $delivered ? $this->toCollect($order) : 0.0;

                $collected += $toCollect;
                $fees += $fee;
                $lines[$id] = [
                    'outcome' => $result['outcome'],
                    'issue' => $delivered ? null : ($result['issue'] ?? null),
                    'amount_collected' => $toCollect,
                    'fee' => $fee,
                ];

                $delivered ? $this->markDelivered($order) : $this->markFailed($order, $result['issue'] ?? null);
            }

            $movement = CourierMovement::create([
                'courier_id' => $courier->id,
                'type' => CourierMovement::TYPE_POINT,
                'amount_collected' => $collected,
                'fees' => $fees,
                'amount_received' => $amountReceived,
                'balance_change' => $collected - $fees - $amountReceived,
                'note' => $note,
                'user_id' => $userId,
            ]);
            $movement->orders()->attach($lines);

            return $movement;
        });
    }

    /** Sens d'un versement ou d'une correction (montant toujours positif). */
    public const DIRECTIONS = ['from_courier', 'to_courier', 'correction_more', 'correction_less'];

    /**
     * from_courier : le livreur remet de l'argent ; to_courier : la boutique paie le livreur ;
     * correction_more / correction_less : il doit plus / moins (erreur d'un point, avec une note).
     */
    public function recordPayment(Courier $courier, float $amount, string $direction, ?string $note, ?int $userId): CourierMovement
    {
        [$type, $received, $change] = match ($direction) {
            'from_courier' => [CourierMovement::TYPE_PAYMENT, $amount, -$amount],
            'to_courier' => [CourierMovement::TYPE_PAYMENT, -$amount, $amount],
            'correction_more' => [CourierMovement::TYPE_ADJUSTMENT, 0, $amount],
            'correction_less' => [CourierMovement::TYPE_ADJUSTMENT, 0, -$amount],
        };

        return CourierMovement::create([
            'courier_id' => $courier->id,
            'type' => $type,
            'amount_received' => $received,
            'balance_change' => $change,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }

    /**
     * Liste à envoyer au livreur par WhatsApp.
     */
    public function summaryText(Courier $courier): string
    {
        $orders = $this->preview($courier);
        $lines = ["Livraisons {$courier->name} – ".now()->format('d/m/Y')];
        foreach ($orders as $o) {
            $lines[] = "#{$o['order_number']} – {$o['customer']} – {$o['phone']} – {$o['city']} – à encaisser ".$this->money($o['to_collect']);
        }
        $lines[] = 'Total à encaisser : '.$this->money(array_sum(array_column($orders, 'to_collect')));

        return implode("\n", $lines);
    }

    public function whatsappUrl(Courier $courier): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $courier->phone);
        if ($digits === '' || ! $courier->ordersOnRoute()->exists()) {
            return null;
        }
        // Numéro ivoirien saisi sans indicatif (10 chiffres) : on ajoute 225
        if (strlen($digits) === 10) {
            $digits = '225'.$digits;
        }

        return "https://wa.me/{$digits}?text=".rawurlencode($this->summaryText($courier));
    }

    private function toCollect(Order $order): float
    {
        return $order->payment_status === Order::PAYMENT_PAID ? 0.0 : (float) $order->total;
    }

    private function markDelivered(Order $order): void
    {
        $updates = ['status' => Order::STATUS_DELIVERED, 'delivered_at' => now()];
        if ($order->payment_status !== Order::PAYMENT_PAID) {
            $updates += ['payment_status' => Order::PAYMENT_PAID, 'paid_at' => now()];
        }
        $order->update($updates);
        ActivityLog::log('order_delivered', 'Livrée (point livreur)', $order);
    }

    private function markFailed(Order $order, ?string $issue): void
    {
        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'courier_id' => null,
            'dispatched_at' => null,
            'courier_fee' => null,
            'delivery_attempts' => $order->delivery_attempts + 1,
            'last_delivery_issue' => $issue,
        ]);
        ActivityLog::log('order_delivery_failed', 'Pas livrée (point livreur) : '.($issue ?? 'sans motif'), $order);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' F CFA';
    }
}
