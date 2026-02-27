<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.coupons.create', compact('categories', 'products'));
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
                ->route('admin.coupons.index', ['open_modal' => 'create'])
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

        return view('admin.coupons.edit', compact('coupon', 'categories', 'products'));
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
                ->route('admin.coupons.index', ['open_modal' => 'edit', 'coupon_id' => $coupon->id])
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

        return view('admin.coupons.show', compact('coupon'));
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

