<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use App\Services\CourierDispatch;
use App\Services\CourierSettlement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourierController extends Controller
{
    public function index()
    {
        Inertia::setRootView('layouts.admin-inertia');

        $couriers = Courier::query()
            ->withCount('ordersOnRoute as on_route_count')
            ->withSum(['ordersOnRoute as on_route_to_collect' => fn ($q) => $q->where('payment_status', '!=', Order::PAYMENT_PAID)], 'total')
            ->withSum('movements as balance', 'balance_change')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (Courier $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'type' => $c->type,
                'notes' => $c->notes,
                'is_active' => $c->is_active,
                'on_route_count' => (int) $c->on_route_count,
                'on_route_to_collect' => (float) ($c->on_route_to_collect ?? 0),
                'balance' => (float) ($c->balance ?? 0),
            ]);

        return Inertia::render('Admin/Couriers/Index', [
            'pageTitle' => 'Livreurs',
            'couriers' => $couriers,
        ]);
    }

    public function store(Request $request)
    {
        Courier::create($this->validated($request) + ['is_active' => true]);

        return back()->with('success', 'Livreur ajouté.');
    }

    public function update(Request $request, Courier $courier)
    {
        $courier->update($this->validated($request) + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Livreur mis à jour.');
    }

    public function dispatch(Request $request, Courier $courier, CourierDispatch $dispatch)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        $result = $dispatch->dispatch($courier, $validated['ids']);

        $message = "{$result['dispatched']} commande(s) confiée(s) à {$courier->name}.";
        if ($result['skipped']) {
            $message .= " {$result['skipped']} ignorée(s) (pas encore confirmée, déjà chez un livreur, livrée ou annulée).";
        }

        return back()->with('success', $message);
    }

    public function show(Courier $courier, CourierSettlement $settlement)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $movements = $courier->movements()->with('orders:id,order_number')->latest()->take(30)->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'date' => $m->created_at->format('d/m/Y H:i'),
                'amount_collected' => (float) $m->amount_collected,
                'fees' => (float) $m->fees,
                'amount_received' => (float) $m->amount_received,
                'balance_change' => (float) $m->balance_change,
                'note' => $m->note,
                'orders' => $m->orders->map(fn ($o) => [
                    'order_number' => $o->order_number,
                    'outcome' => $o->pivot->outcome,
                    'issue' => $o->pivot->issue,
                ])->values(),
            ]);

        return Inertia::render('Admin/Couriers/Show', [
            'pageTitle' => "Point — {$courier->name}",
            'courier' => $courier->only(['id', 'name', 'phone', 'type', 'is_active']),
            'orders' => $settlement->preview($courier),
            'balance' => $courier->balance(),
            'movements' => $movements,
            'whatsapp_url' => $settlement->whatsappUrl($courier),
            'issues' => CourierSettlement::ISSUES,
        ]);
    }

    public function settle(Request $request, Courier $courier, CourierSettlement $settlement)
    {
        $validated = $request->validate([
            'outcomes' => 'required|array|min:1',
            'outcomes.*.outcome' => 'required|in:delivered,failed',
            'outcomes.*.issue' => 'nullable|required_if:outcomes.*.outcome,failed|in:'.implode(',', CourierSettlement::ISSUES),
            'outcomes.*.fee' => 'nullable|numeric|min:0',
            'amount_received' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ], [
            'outcomes.*.issue.required_if' => 'Indiquez pourquoi la commande n\'a pas été livrée.',
        ]);

        $settlement->settle($courier, $validated['outcomes'], (float) $validated['amount_received'], $validated['note'] ?? null, $request->user()?->id);

        $delivered = collect($validated['outcomes'])->where('outcome', 'delivered')->count();
        $failed = count($validated['outcomes']) - $delivered;

        return back()->with('success', "Point enregistré : {$delivered} livrée(s), {$failed} à relivrer. Solde : ".$this->balanceText($courier->balance()).'.');
    }

    private function balanceText(float $balance): string
    {
        $amount = number_format(abs($balance), 0, ',', ' ').' F CFA';

        return match (true) {
            $balance > 0 => "le livreur doit {$amount}",
            $balance < 0 => "la boutique lui doit {$amount}",
            default => 'tout est réglé',
        };
    }

    public function storePayment(Request $request, Courier $courier, CourierSettlement $settlement)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'direction' => 'nullable|in:'.implode(',', CourierSettlement::DIRECTIONS),
            'note' => 'nullable|required_if:direction,correction_more,correction_less|string|max:500',
        ], [
            'note.required_if' => 'Expliquez la correction (ex : oubli au point du 03/11).',
        ]);

        $direction = $validated['direction'] ?? 'from_courier';
        $settlement->recordPayment($courier, (float) $validated['amount'], $direction, $validated['note'] ?? null, $request->user()?->id);

        $label = str_starts_with($direction, 'correction') ? 'Correction enregistrée' : 'Versement enregistré';

        return back()->with('success', "{$label}. Nouveau solde : ".$this->balanceText($courier->balance()).'.');
    }

    public function withdraw(Order $order, CourierDispatch $dispatch)
    {
        $dispatch->withdraw($order);

        return back()->with('success', "Commande {$order->order_number} reprise au livreur.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'type' => 'required|in:'.Courier::TYPE_INDEPENDENT.','.Courier::TYPE_COMPANY,
            'notes' => 'nullable|string|max:500',
        ], [
            'type.in' => 'Choisissez « indépendant » ou « société ».',
        ]);
    }
}
