<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->valid();
                    break;
                case 'expired':
                    $query->where('expires_at', '<', now());
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        $coupons = $query->withCount('usages')->latest()->paginate(20)->withQueryString();

        // Map to flat arrays for Inertia
        $coupons->getCollection()->transform(fn($c) => [
            'id'                    => $c->id,
            'code'                  => $c->code,
            'name'                  => $c->name,
            'type'                  => $c->type,
            'type_label'            => $c->type_label,
            'value'                 => $c->value,
            'min_order_amount'      => $c->min_order_amount,
            'min_order_amount_fmt'  => $c->min_order_amount ? number_format($c->min_order_amount, 0, ',', ' ') . ' F' : null,
            'usage_limit'           => $c->usage_limit,
            'usage_count'           => $c->usage_count,
            'usages_count'          => $c->usages_count,
            'starts_at_fmt'         => $c->starts_at?->format('d/m/Y'),
            'expires_at_fmt'        => $c->expires_at?->format('d/m/Y'),
            'is_active'             => $c->is_active,
            'status'                => $c->status,
        ]);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get(['id', 'name']);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Coupons/Create', [
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'products'   => $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,free_shipping',
            'value' => 'required_unless:type,free_shipping|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'first_order_only' => 'boolean',
            'applicable_categories' => 'nullable|array',
            'applicable_products' => 'nullable|array',
            'excluded_products' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.coupons.create')
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();
        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['first_order_only'] = $request->boolean('first_order_only', false);

        Coupon::create($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Code promo créé avec succès.');
    }

    public function edit(Coupon $coupon)
    {
        $categories = Category::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get(['id', 'name']);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Coupons/Edit', [
            'coupon' => [
                'id'                   => $coupon->id,
                'code'                 => $coupon->code,
                'name'                 => $coupon->name,
                'description'          => $coupon->description,
                'type'                 => $coupon->type,
                'value'                => $coupon->value,
                'min_order_amount'     => $coupon->min_order_amount,
                'max_discount_amount'  => $coupon->max_discount_amount,
                'usage_limit'          => $coupon->usage_limit,
                'usage_limit_per_user' => $coupon->usage_limit_per_user,
                'usage_count'          => $coupon->usage_count,
                'starts_at_input'      => $coupon->starts_at?->format('Y-m-d'),
                'expires_at_input'     => $coupon->expires_at?->format('Y-m-d'),
                'is_active'            => $coupon->is_active,
                'first_order_only'     => $coupon->first_order_only,
            ],
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'products'   => $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,free_shipping',
            'value' => 'required_unless:type,free_shipping|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'first_order_only' => 'boolean',
            'applicable_categories' => 'nullable|array',
            'applicable_products' => 'nullable|array',
            'excluded_products' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.coupons.edit', $coupon)
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();
        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['first_order_only'] = $request->boolean('first_order_only', false);

        $coupon->update($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Code promo mis à jour.');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->usage_count > 0) {
            $coupon->update(['is_active' => false]);
            return back()->with('warning', 'Code promo désactivé (déjà utilisé).');
        }

        $coupon->delete();
        return back()->with('success', 'Code promo supprimé.');
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['usages.order', 'usages.customer']);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Coupons/Show', [
            'coupon' => [
                'id'                       => $coupon->id,
                'code'                     => $coupon->code,
                'name'                     => $coupon->name,
                'description'              => $coupon->description,
                'type'                     => $coupon->type,
                'type_label'               => $coupon->type_label,
                'value'                    => $coupon->value,
                'min_order_amount'         => $coupon->min_order_amount,
                'min_order_amount_fmt'     => $coupon->min_order_amount ? number_format($coupon->min_order_amount, 0, ',', ' ') . ' F' : null,
                'max_discount_amount'      => $coupon->max_discount_amount,
                'max_discount_amount_fmt'  => $coupon->max_discount_amount ? number_format($coupon->max_discount_amount, 0, ',', ' ') . ' F' : null,
                'usage_limit'              => $coupon->usage_limit,
                'usage_limit_per_user'     => $coupon->usage_limit_per_user,
                'usage_count'              => $coupon->usages->count(),
                'starts_at'                => $coupon->starts_at?->format('d/m/Y'),
                'expires_at'               => $coupon->expires_at?->format('d/m/Y'),
                'is_active'                => $coupon->is_active,
                'first_order_only'         => $coupon->first_order_only,
                'status'                   => $coupon->status,
                'usages' => $coupon->usages->map(fn($u) => [
                    'id'                  => $u->id,
                    'order_id'            => $u->order?->id,
                    'order_number'        => $u->order?->order_number,
                    'customer_name'       => $u->customer?->full_name ?? $u->customer?->name,
                    'discount_amount'     => $u->discount_amount,
                    'discount_amount_fmt' => $u->discount_amount ? number_format($u->discount_amount, 0, ',', ' ') . ' F' : null,
                    'used_at'             => $u->created_at->format('d/m/Y H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Générer un code aléatoire
     */
    public function generateCode()
    {
        $code = Str::upper(Str::random(8));

        while (Coupon::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }

        return response()->json(['code' => $code]);
    }
}

