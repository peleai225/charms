<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

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

        $customers->getCollection()->transform(function ($c) {
            $c->total_spent = $c->orders_sum_total ?? 0;
            return $c;
        });

        $stats = [
            'total'          => Customer::count(),
            'active'         => Customer::where('status', 'active')->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)->count(),
        ];

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers->through(fn($c) => [
                'id'             => $c->id,
                'full_name'      => $c->full_name,
                'initials'       => $c->initials,
                'email'          => $c->email,
                'phone'          => $c->phone,
                'status'         => $c->status,
                'orders_count'   => $c->orders_count,
                'total_spent'    => $c->total_spent,
                'created_at_fmt' => $c->created_at->format('d/m/Y'),
            ]),
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:customers,email',
            'phone'       => 'nullable|string|max:30',
            'birth_date'  => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'status'      => 'required|in:active,inactive',
            'notes'       => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        if ($request->filled('address_line1')) {
            $customer->addresses()->create([
                'type'          => 'shipping',
                'is_default'    => true,
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'address'       => $request->input('address_line1'),
                'city'          => $request->input('city'),
                'postal_code'   => $request->input('postal_code'),
                'country'       => $request->input('country', 'MA'),
                'phone'         => $request->input('address_phone'),
            ]);
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Customer $customer)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $customer->load(['orders' => fn($q) => $q->latest()->take(10), 'addresses']);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => [
                'id'             => $customer->id,
                'full_name'      => $customer->full_name,
                'initials'       => $customer->initials,
                'first_name'     => $customer->first_name,
                'last_name'      => $customer->last_name,
                'email'          => $customer->email,
                'phone'          => $customer->phone,
                'birth_date'     => $customer->birth_date?->format('d/m/Y'),
                'gender'         => $customer->gender,
                'status'         => $customer->status,
                'notes'          => $customer->notes,
                'loyalty_points' => $customer->loyalty_points ?? 0,
                'orders_count'   => $customer->orders->count(),
                'total_spent'    => $customer->orders->where('payment_status', 'paid')->sum('total'),
                'created_at_fmt' => $customer->created_at->format('d/m/Y à H:i'),
                'orders'         => $customer->orders->map(fn($o) => [
                    'id'             => $o->id,
                    'order_number'   => $o->order_number,
                    'total'          => $o->total,
                    'status'         => $o->status,
                    'created_at_fmt' => $o->created_at->format('d/m/Y H:i'),
                ]),
                'addresses' => $customer->addresses->map(fn($a) => [
                    'id'                  => $a->id,
                    'first_name'          => $a->first_name,
                    'last_name'           => $a->last_name,
                    'company'             => $a->company,
                    'address'             => $a->address,
                    'postal_code'         => $a->postal_code,
                    'city'                => $a->city,
                    'country'             => $a->country,
                    'phone'               => $a->phone,
                    'is_default_shipping' => $a->is_default_shipping ?? false,
                ]),
            ],
        ]);
    }

    public function edit(Customer $customer)
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => [
                'id'         => $customer->id,
                'full_name'  => $customer->full_name,
                'first_name' => $customer->first_name,
                'last_name'  => $customer->last_name,
                'email'      => $customer->email,
                'phone'      => $customer->phone,
                'birth_date' => $customer->birth_date?->format('Y-m-d'),
                'gender'     => $customer->gender,
                'status'     => $customer->status,
                'notes'      => $customer->notes,
            ],
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:customers,email,' . $customer->id,
            'phone'      => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|in:male,female,other',
            'status'     => 'required|in:active,inactive,blocked',
            'notes'      => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update([
            'email'      => 'deleted-' . $customer->id . '@anonymized.local',
            'first_name' => 'Client',
            'last_name'  => 'Supprimé',
            'phone'      => null,
            'status'     => 'inactive',
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Client anonymisé avec succès.');
    }
}
