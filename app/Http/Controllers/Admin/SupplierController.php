<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('stockMovements');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->latest()->paginate(20)->withQueryString();

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters'   => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Suppliers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:suppliers',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'payment_terms' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Supplier::create($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['stockMovements.product']);

        $movements = $supplier->stockMovements->map(fn ($m) => [
            'id'             => $m->id,
            'type'           => $m->type,
            'quantity'       => $m->quantity,
            'product_name'   => $m->product?->name,
            'created_at_fmt' => $m->created_at->format('d/m/Y H:i'),
        ])->values()->all();

        $entries = $supplier->stockMovements->filter(fn ($m) => in_array($m->type, [
            'purchase', 'return_in', 'adjustment_in', 'transfer_in', 'inventory',
        ]));

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Suppliers/Show', [
            'supplier' => [
                'id'              => $supplier->id,
                'name'            => $supplier->name,
                'code'            => $supplier->code,
                'email'           => $supplier->email,
                'phone'           => $supplier->phone,
                'address'         => $supplier->address,
                'city'            => $supplier->city,
                'postal_code'     => $supplier->postal_code,
                'country'         => $supplier->country,
                'contact_name'    => $supplier->contact_name,
                'payment_terms'   => $supplier->payment_terms,
                'notes'           => $supplier->notes,
                'is_active'       => $supplier->is_active,
                'created_at_fmt'  => $supplier->created_at->format('d/m/Y'),
            ],
            'movements' => $movements,
            'stats' => [
                'total_movements' => $supplier->stockMovements->count(),
                'total_in'        => $entries->sum('quantity'),
            ],
        ]);
    }

    public function edit(Supplier $supplier)
    {
        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Suppliers/Edit', [
            'supplier' => [
                'id'            => $supplier->id,
                'name'          => $supplier->name,
                'code'          => $supplier->code,
                'email'         => $supplier->email,
                'phone'         => $supplier->phone,
                'address'       => $supplier->address,
                'city'          => $supplier->city,
                'postal_code'   => $supplier->postal_code,
                'country'       => $supplier->country,
                'contact_name'  => $supplier->contact_name,
                'payment_terms' => $supplier->payment_terms,
                'notes'         => $supplier->notes,
                'is_active'     => $supplier->is_active,
            ],
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:suppliers,code,' . $supplier->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'payment_terms' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Fournisseur supprimé.');
    }
}

