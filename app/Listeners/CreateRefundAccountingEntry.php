<?php

namespace App\Listeners;

use App\Events\OrderRefunded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateRefundAccountingEntry
{
    /**
     * Crée les écritures comptables pour un remboursement
     */
    public function handle(OrderRefunded $event): void
    {
        $order = $event->order;
        $refund = $event->refund;

        try {
            DB::transaction(function () use ($order, $refund) {
                // Récupérer le journal des ventes (ou créer un journal remboursements)
                $journalVentes = DB::table('accounting_journals')->where('code', 'VE')->first();
                if (!$journalVentes) {
                    Log::warning('Journal des ventes (VE) non trouvé pour remboursement');
                    return;
                }

                // Générer le numéro de pièce
                $entryNumber = 'RMB-' . now()->format('Ymd') . '-' . str_pad($refund->id, 6, '0', STR_PAD_LEFT);

                // Créer l'écriture comptable de remboursement
                $entryId = DB::table('accounting_entries')->insertGetId([
                    'journal_id' => $journalVentes->id,
                    'entry_number' => $entryNumber,
                    'entry_date' => now()->toDateString(),
                    'label' => "Remboursement - Commande #{$order->order_number}",
                    'description' => "Remboursement #{$refund->refund_number} - {$refund->reason_label}",
                    'reference_type' => 'App\\Models\\Refund',
                    'reference_id' => $refund->id,
                    'document_number' => $refund->refund_number,
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
                    Log::warning('Comptes comptables manquants pour remboursement');
                    return;
                }

                // Calculer les montants (proportionnel au remboursement)
                $refundRatio = $refund->amount / $order->total;
                $montantHT = $order->subtotal * $refundRatio;
                $montantTVA = ($order->tax_amount ?? 0) * $refundRatio;
                $montantTTC = $refund->amount;

                // 1. Écriture de remboursement : Crédit Client, Débit Ventes + TVA
                // Crédit 411 - Clients (on rembourse le client)
                DB::table('accounting_entry_lines')->insert([
                    'entry_id' => $entryId,
                    'account_id' => $compteClient->id,
                    'label' => "Remboursement client - {$order->billing_full_name}",
                    'debit' => 0,
                    'credit' => $montantTTC,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Débit 707 - Ventes de marchandises (on annule la vente)
                DB::table('accounting_entry_lines')->insert([
                    'entry_id' => $entryId,
                    'account_id' => $compteVentes->id,
                    'label' => 'Remboursement ventes marchandises',
                    'debit' => $montantHT,
                    'credit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Débit 44571 - TVA collectée (on annule la TVA)
                if ($montantTVA > 0) {
                    DB::table('accounting_entry_lines')->insert([
                        'entry_id' => $entryId,
                        'account_id' => $compteTVA->id,
                        'label' => 'Remboursement TVA collectée',
                        'debit' => $montantTVA,
                        'credit' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 2. Écriture de remboursement bancaire : Crédit Banque, Débit Client
                $journalBanque = DB::table('accounting_journals')->where('code', 'BQ')->first();
                if ($journalBanque && $refund->payment) {
                    $entryNumber2 = 'BQ-RMB-' . now()->format('Ymd') . '-' . str_pad($refund->id, 6, '0', STR_PAD_LEFT);

                    $entryId2 = DB::table('accounting_entries')->insertGetId([
                        'journal_id' => $journalBanque->id,
                        'entry_number' => $entryNumber2,
                        'entry_date' => now()->toDateString(),
                        'label' => "Remboursement - Commande #{$order->order_number}",
                        'description' => "Remboursement #{$refund->refund_number}",
                        'reference_type' => 'App\\Models\\Refund',
                        'reference_id' => $refund->id,
                        'document_number' => $refund->refund_number,
                        'status' => 'validated',
                        'fiscal_year' => now()->format('Y'),
                        'fiscal_period' => now()->format('Y-m'),
                        'created_by' => auth()->id(),
                        'validated_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Crédit 512 - Banque (on sort l'argent)
                    DB::table('accounting_entry_lines')->insert([
                        'entry_id' => $entryId2,
                        'account_id' => $compteBanque->id,
                        'label' => "Remboursement {$refund->refund_number}",
                        'debit' => 0,
                        'credit' => $refund->amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Débit 411 - Clients
                    DB::table('accounting_entry_lines')->insert([
                        'entry_id' => $entryId2,
                        'account_id' => $compteClient->id,
                        'label' => "Client - {$order->billing_full_name}",
                        'debit' => $refund->amount,
                        'credit' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Erreur création écriture comptable remboursement', [
                'refund_id' => $refund->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
