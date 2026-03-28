<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerTag;
use App\Models\Order;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrmController extends Controller
{
    public function dashboard()
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::active()->count();
        $vipCustomers = Customer::vip()->count();
        $newCustomers = Customer::new()->count();
        $inactiveCustomers = Customer::inactive()->count();

        $avgOrderValue = Customer::where('orders_count', '>', 0)->avg('avg_order_value') ?? 0;
        $avgLifetimeValue = Customer::where('orders_count', '>', 0)->avg('lifetime_value') ?? 0;
        $totalRevenue = Customer::sum('total_spent');

        $topCustomers = Customer::active()
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $tags = CustomerTag::withCount('customers')->orderBy('sort_order')->get();

        $segmentData = [
            'vip' => Customer::vip()->count(),
            'loyal' => Customer::loyal()->count(),
            'new' => Customer::new()->count(),
            'inactive' => Customer::inactive()->count(),
        ];

        return view('admin.crm.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'vipCustomers', 'newCustomers',
            'inactiveCustomers', 'avgOrderValue', 'avgLifetimeValue', 'totalRevenue',
            'topCustomers', 'recentOrders', 'tags', 'segmentData'
        ));
    }

    public function tags()
    {
        $tags = CustomerTag::withCount('customers')->orderBy('sort_order')->get();
        return view('admin.crm.tags', compact('tags'));
    }

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:255',
            'is_auto' => 'boolean',
            'auto_rules' => 'nullable|array',
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $i = 1;
        while (CustomerTag::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }
        $validated['slug'] = $slug;
        CustomerTag::create($validated);

        return back()->with('success', 'Tag cree avec succes.');
    }

    public function destroyTag(CustomerTag $tag)
    {
        $tag->customers()->detach();
        $tag->delete();
        return back()->with('success', 'Tag supprime.');
    }

    public function assignTag(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'tag_id' => 'required|exists:customer_tags,id',
        ]);

        foreach ($request->customer_ids as $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->tags()->syncWithoutDetaching([$request->tag_id]);
            }
        }

        return back()->with('success', 'Tag assigne aux clients selectionnes.');
    }

    public function autoClassifyAll()
    {
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->updateStats();
            CustomerTag::autoClassify($customer);
        }

        return back()->with('success', 'Classification automatique terminee pour ' . $customers->count() . ' clients.');
    }

    public function customerAnalytics(Customer $customer)
    {
        $customer->load(['orders' => fn($q) => $q->latest()->take(20), 'tags', 'whatsappMessages']);

        $monthlyOrders = $customer->orders()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(total) as total')
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderBy('month')
            ->take(12)
            ->get();

        $topProducts = $customer->orders()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.name, SUM(order_items.quantity) as qty, SUM(order_items.total) as revenue')
            ->groupBy('order_items.name')
            ->orderByDesc('qty')
            ->take(5)
            ->get();

        return view('admin.crm.customer-analytics', compact('customer', 'monthlyOrders', 'topProducts'));
    }
}
