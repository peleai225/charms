<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use App\Services\CourierDispatch;
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
