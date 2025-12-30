<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Support\Facades\DB;

class CreateAccountingEntryOnPayment
{
    /**
     * Crée les écritures comptables à la réception du paiement
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        $payment = $event->payment;

        DB::transaction(function () use ($order, $payment) {
            // Récupérer le journal des ventes
            $journalVentes = DB::table('accounting_journals')->where('code', 'VE')->first();
            if (!$journalVentes) {
                return;
            }

            // Générer le numéro de pièce
            $entryNumber = 'VE-' . now()->format('Ymd') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);

            // Créer l'écriture comptable
            $entryId = DB::table('accounting_entries')->insertGetId([
                'journal_id' => $journalVentes->id,
                'entry_number' => $entryNumber,
                'entry_date' => now()->toDateString(),
                'label' => "Vente - Commande #{$order->order_number}",
                'description' => "Paiement reçu - {$payment->method_label}",
                'reference_type' => 'App\\Models\\Order',
                'reference_id' => $order->id,
                'document_number' => $order->order_number,
                'status' => 'validated',
                'fiscal_year' => now()->format('Y'),
                'fiscal_period' => now()->format('Y-m'),
                'created_by' => auth()->id(),
                'validated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Récupérer les comptes nécessaires
            $compteClient = DB::table('accounting_accounts')->where('code', '411000')->first();
            $compteVentes = DB::table('accounting_accounts')->where('code', '707000')->first();
            $compteTVA = DB::table('accounting_accounts')->where('code', '445710')->first();
            $compteBanque = DB::table('accounting_accounts')->where('code', '512000')->first();

            if (!$compteClient || !$compteVentes || !$compteTVA || !$compteBanque) {
                return;
            }

            $montantHT = $order->subtotal;
            $montantTVA = $order->tax_amount;
            $montantTTC = $order->total;

            // 1. Écriture de vente : Débit Client, Crédit Ventes + TVA
            // Débit 411 - Clients
            DB::table('accounting_entry_lines')->insert([
                'entry_id' => $entryId,
                'account_id' => $compteClient->id,
                'label' => "Client - {$order->billing_full_name}",
                'debit' => $montantTTC,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crédit 707 - Ventes de marchandises
            DB::table('accounting_entry_lines')->insert([
                'entry_id' => $entryId,
                'account_id' => $compteVentes->id,
                'label' => 'Ventes marchandises',
                'debit' => 0,
                'credit' => $montantHT,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crédit 44571 - TVA collectée
            if ($montantTVA > 0) {
                DB::table('accounting_entry_lines')->insert([
                    'entry_id' => $entryId,
                    'account_id' => $compteTVA->id,
                    'label' => 'TVA collectée',
                    'debit' => 0,
                    'credit' => $montantTVA,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Écriture de règlement : Débit Banque, Crédit Client
            // Récupérer le journal de banque
            $journalBanque = DB::table('accounting_journals')->where('code', 'BQ')->first();
            if ($journalBanque) {
                $entryNumber2 = 'BQ-' . now()->format('Ymd') . '-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

                $entryId2 = DB::table('accounting_entries')->insertGetId([
                    'journal_id' => $journalBanque->id,
                    'entry_number' => $entryNumber2,
                    'entry_date' => now()->toDateString(),
                    'label' => "Règlement - Commande #{$order->order_number}",
                    'description' => "Paiement par {$payment->method_label}",
                    'reference_type' => 'App\\Models\\Payment',
                    'reference_id' => $payment->id,
                    'document_number' => $payment->transaction_id,
                    'status' => 'validated',
                    'fiscal_year' => now()->format('Y'),
                    'fiscal_period' => now()->format('Y-m'),
                    'created_by' => auth()->id(),
                    'validated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Débit 512 - Banque
                DB::table('accounting_entry_lines')->insert([
                    'entry_id' => $entryId2,
                    'account_id' => $compteBanque->id,
                    'label' => "Encaissement {$payment->method_label}",
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Crédit 411 - Clients
                DB::table('accounting_entry_lines')->insert([
                    'entry_id' => $entryId2,
                    'account_id' => $compteClient->id,
                    'label' => "Client - {$order->billing_full_name}",
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}

