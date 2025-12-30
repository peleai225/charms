<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // Journaux comptables
        $journals = [
            ['code' => 'VE', 'name' => 'Journal des ventes', 'description' => 'Enregistrement des ventes clients'],
            ['code' => 'AC', 'name' => 'Journal des achats', 'description' => 'Enregistrement des achats fournisseurs'],
            ['code' => 'BQ', 'name' => 'Journal de banque', 'description' => 'Mouvements bancaires'],
            ['code' => 'CA', 'name' => 'Journal de caisse', 'description' => 'Mouvements en espèces'],
            ['code' => 'OD', 'name' => 'Opérations diverses', 'description' => 'Écritures diverses'],
            ['code' => 'AN', 'name' => 'À nouveaux', 'description' => 'Report à nouveau'],
        ];

        foreach ($journals as $journal) {
            DB::table('accounting_journals')->updateOrInsert(
                ['code' => $journal['code']],
                array_merge($journal, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Comptes comptables de base
        $accounts = [
            // Classe 1 - Capitaux
            ['code' => '101000', 'name' => 'Capital', 'type' => 'equity'],
            ['code' => '120000', 'name' => 'Résultat de l\'exercice', 'type' => 'equity'],
            
            // Classe 2 - Immobilisations
            ['code' => '218000', 'name' => 'Matériel informatique', 'type' => 'asset'],
            ['code' => '281800', 'name' => 'Amort. matériel informatique', 'type' => 'asset'],
            
            // Classe 3 - Stocks
            ['code' => '370000', 'name' => 'Stocks de marchandises', 'type' => 'asset'],
            ['code' => '397000', 'name' => 'Dépréciation stocks', 'type' => 'asset'],
            
            // Classe 4 - Tiers
            ['code' => '401000', 'name' => 'Fournisseurs', 'type' => 'liability'],
            ['code' => '411000', 'name' => 'Clients', 'type' => 'asset'],
            ['code' => '445710', 'name' => 'TVA collectée', 'type' => 'liability'],
            ['code' => '445660', 'name' => 'TVA déductible sur achats', 'type' => 'asset'],
            ['code' => '445500', 'name' => 'TVA à décaisser', 'type' => 'liability'],
            
            // Classe 5 - Financiers
            ['code' => '512000', 'name' => 'Banque', 'type' => 'asset'],
            ['code' => '530000', 'name' => 'Caisse', 'type' => 'asset'],
            ['code' => '511000', 'name' => 'Valeurs à l\'encaissement', 'type' => 'asset'],
            
            // Classe 6 - Charges
            ['code' => '601000', 'name' => 'Achats de marchandises', 'type' => 'expense'],
            ['code' => '607000', 'name' => 'Achats de marchandises (stock)', 'type' => 'expense'],
            ['code' => '613200', 'name' => 'Locations', 'type' => 'expense'],
            ['code' => '626000', 'name' => 'Frais postaux et télécom', 'type' => 'expense'],
            ['code' => '627000', 'name' => 'Services bancaires', 'type' => 'expense'],
            ['code' => '641000', 'name' => 'Rémunérations du personnel', 'type' => 'expense'],
            ['code' => '645000', 'name' => 'Charges sociales', 'type' => 'expense'],
            ['code' => '681100', 'name' => 'Dotations aux amortissements', 'type' => 'expense'],
            
            // Classe 7 - Produits
            ['code' => '701000', 'name' => 'Ventes de produits finis', 'type' => 'revenue'],
            ['code' => '707000', 'name' => 'Ventes de marchandises', 'type' => 'revenue'],
            ['code' => '708500', 'name' => 'Ports et frais accessoires', 'type' => 'revenue'],
            ['code' => '709000', 'name' => 'RRR accordés', 'type' => 'revenue'],
            ['code' => '791000', 'name' => 'Transferts de charges', 'type' => 'revenue'],
        ];

        foreach ($accounts as $account) {
            DB::table('accounting_accounts')->updateOrInsert(
                ['code' => $account['code']],
                array_merge($account, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Créer les périodes comptables pour l'année en cours
        $year = date('Y');
        $months = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars',
            '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
            '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre',
            '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
        ];

        foreach ($months as $month => $name) {
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            DB::table('accounting_periods')->updateOrInsert(
                ['code' => "{$year}-{$month}"],
                [
                    'name' => "{$name} {$year}",
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'fiscal_year' => $year,
                    'is_closed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

