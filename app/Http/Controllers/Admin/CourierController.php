<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
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
