<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('user')
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        // Mapper les totaux
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_spent = $customer->orders_sum_total ?? 0;
            return $customer;
        });

        // Statistiques globales
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => function ($q) {
            $q->latest()->take(10);
        }, 'addresses']);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive,blocked',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Customer $customer)
    {
        // Anonymiser plutôt que supprimer
        $customer->update([
            'email' => 'deleted-' . $customer->id . '@anonymized.local',
            'first_name' => 'Client',
            'last_name' => 'Supprimé',
            'phone' => null,
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Client anonymisé avec succès.');
    }
}

