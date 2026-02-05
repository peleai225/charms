<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\AccountingJournal;
use App\Models\AccountingAccount;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingController extends Controller
{
    /**
     * Tableau de bord comptabilité
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        // Statistiques principales
        $stats = [
            'revenue' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'orders_count' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'average_order' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('total') ?? 0,
            'refunds' => Order::where('status', 'refunded')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
        ];

        // Évolution des revenus par jour/mois
        $revenueChart = $this->getRevenueChart($period, $startDate, $endDate);

        // Revenus par méthode de paiement
        $paymentMethods = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Top produits par CA
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Dernières écritures comptables (simplifié - sans relations qui peuvent ne pas exister)
        $recentEntries = collect();
        try {
            $recentEntries = AccountingEntry::with('journal')
                ->latest()
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            // Table peut ne pas exister ou avoir une structure différente
            \Log::warning('Accounting: Impossible de charger les écritures comptables', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Journaux comptables (simplifié)
        $journals = collect();
        try {
            $journals = AccountingJournal::withCount('entries')->get();
        } catch (\Exception $e) {
            // Table peut ne pas exister
            \Log::warning('Accounting: Impossible de charger les journaux comptables', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return view('admin.accounting.index', compact(
            'stats',
            'revenueChart',
            'paymentMethods',
            'topProducts',
            'recentEntries',
            'journals',
            'period'
        ));
    }

    /**
     * Liste des écritures comptables
     */
    public function entries(Request $request)
    {
        $query = AccountingEntry::with(['journal', 'lines.account']);

        if ($request->filled('journal')) {
            $query->where('journal_id', $request->journal);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }

        $entries = $query->latest('entry_date')->paginate(20);
        $journals = AccountingJournal::all();

        return view('admin.accounting.entries', compact('entries', 'journals'));
    }

    /**
     * Détail d'une écriture
     */
    public function showEntry(AccountingEntry $entry)
    {
        $entry->load(['journal', 'lines.account', 'order']);
        return view('admin.accounting.entry-show', compact('entry'));
    }

    /**
     * Plan comptable
     */
    public function accounts()
    {
        $accounts = AccountingAccount::orderBy('code')->get()->groupBy('type');
        return view('admin.accounting.accounts', compact('accounts'));
    }

    /**
     * Balance générale
     */
    public function balance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $accounts = AccountingAccount::orderBy('code')->get();

        $balances = [];
        foreach ($accounts as $account) {
            $debits = AccountingEntryLine::where('account_id', $account->id)
                ->whereHas('entry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->sum('debit');

            $credits = AccountingEntryLine::where('account_id', $account->id)
                ->whereHas('entry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->sum('credit');

            if ($debits > 0 || $credits > 0) {
                $balances[] = [
                    'account' => $account,
                    'debit' => $debits,
                    'credit' => $credits,
                    'balance' => $debits - $credits,
                ];
            }
        }

        $totals = [
            'debit' => array_sum(array_column($balances, 'debit')),
            'credit' => array_sum(array_column($balances, 'credit')),
        ];

        return view('admin.accounting.balance', compact('balances', 'totals', 'startDate', 'endDate'));
    }

    /**
     * Grand livre
     */
    public function ledger(Request $request)
    {
        $accountId = $request->get('account_id');
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $accounts = collect();
        $entries = collect();
        $account = null;

        try {
            $accounts = AccountingAccount::orderBy('code')->get();

            if ($accountId) {
                $account = AccountingAccount::find($accountId);

                if ($account) {
                    $entries = AccountingEntryLine::with(['entry.journal'])
                        ->where('account_id', $accountId)
                        ->whereHas('entry', function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('entry_date', [$startDate, $endDate]);
                        })
                        ->get()
                        ->sortBy(fn($line) => $line->entry?->entry_date);
                }
            }
        } catch (\Exception $e) {
            // Tables peuvent ne pas exister
        }

        return view('admin.accounting.ledger', compact('accounts', 'account', 'entries', 'startDate', 'endDate', 'accountId'));
    }

    /**
     * Créer une écriture manuelle
     */
    public function createEntry()
    {
        $journals = AccountingJournal::all();
        $accounts = AccountingAccount::orderBy('code')->get();

        return view('admin.accounting.create-entry', compact('journals', 'accounts'));
    }

    /**
     * Enregistrer une écriture manuelle
     */
    public function storeEntry(Request $request)
    {
        $validated = $request->validate([
            'journal_id' => 'required|exists:accounting_journals,id',
            'entry_date' => 'required|date',
            'document_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.label' => 'nullable|string|max:255',
        ]);

        $totalDebit = array_sum(array_filter(array_column($validated['lines'], 'debit')));
        $totalCredit = array_sum(array_filter(array_column($validated['lines'], 'credit')));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'L\'écriture n\'est pas équilibrée. Débit: ' . number_format($totalDebit, 2) . ', Crédit: ' . number_format($totalCredit, 2));
        }

        try {
            DB::transaction(function () use ($validated) {
                // Générer un numéro d'écriture
                $entryNumber = 'EC-' . now()->format('Ymd') . '-' . str_pad(AccountingEntry::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                $entry = AccountingEntry::create([
                    'journal_id' => $validated['journal_id'],
                    'entry_number' => $entryNumber,
                    'entry_date' => $validated['entry_date'],
                    'label' => $validated['description'],
                    'description' => $validated['description'],
                    'document_number' => $validated['document_number'] ?? null,
                    'status' => 'validated',
                    'fiscal_year' => now()->year,
                    'fiscal_period' => now()->month,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['lines'] as $line) {
                    $debit = floatval($line['debit'] ?? 0);
                    $credit = floatval($line['credit'] ?? 0);
                    
                    if ($debit > 0 || $credit > 0) {
                        AccountingEntryLine::create([
                            'entry_id' => $entry->id,
                            'account_id' => $line['account_id'],
                            'debit' => $debit,
                            'credit' => $credit,
                            'label' => $line['label'] ?? $validated['description'],
                        ]);
                    }
                }
            });

            return redirect()
                ->route('admin.accounting.entries')
                ->with('success', 'Écriture comptable créée.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Export comptable (FEC)
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,fec',
        ]);

        $entries = AccountingEntry::with(['journal', 'lines.account'])
            ->whereBetween('entry_date', [$request->start_date, $request->end_date])
            ->orderBy('entry_date')
            ->get();

        $filename = 'FEC_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($entries) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes FEC
            fputcsv($file, [
                'JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate',
                'CompteNum', 'CompteLib', 'CompAuxNum', 'CompAuxLib',
                'PieceRef', 'PieceDate', 'EcritureLib', 'Debit', 'Credit',
                'EcritureLet', 'DateLet', 'ValidDate', 'Montantdevise', 'Idevise'
            ], ';');

            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($file, [
                        $entry->journal?->code ?? 'OD',
                        $entry->journal?->name ?? 'Opérations diverses',
                        $entry->id,
                        $entry->entry_date->format('Ymd'),
                        $line->account?->code ?? '',
                        $line->account?->name ?? '',
                        '', '',
                        $entry->reference ?? '',
                        $entry->entry_date->format('Ymd'),
                        $line->label ?? $entry->description,
                        number_format($line->debit, 2, ',', ''),
                        number_format($line->credit, 2, ',', ''),
                        '', '',
                        $entry->created_at->format('Ymd'),
                        '', 'XOF'
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtenir la date de début selon la période
     */
    protected function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Générer les données du graphique de revenus
     */
    protected function getRevenueChart(string $period, Carbon $startDate, Carbon $endDate): array
    {
        $groupBy = $period === 'week' ? 'DATE(created_at)' : 'DATE(created_at)';
        $format = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';

        $data = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as date"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->toArray(),
            'revenues' => $data->pluck('revenue')->toArray(),
            'orders' => $data->pluck('orders')->toArray(),
        ];
    }
}

