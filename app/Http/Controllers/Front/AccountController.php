<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function dashboard()
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Profil client non trouvé.');
        }

        $stats = [
            'orders_count' => Order::where('customer_id', $customer->id)->count(),
            'orders_delivered' => Order::where('customer_id', $customer->id)->where('status', 'delivered')->count(),
            'orders_pending' => Order::where('customer_id', $customer->id)->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
        ];

        return Inertia::render('Account/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function orders()
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return redirect()->route('account.dashboard');
        }

        $orders = Order::where('customer_id', $customer->id)
            ->latest()
            ->paginate(10);

        $ordersData = [
            'data' => $orders->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'items_count' => $order->items()->count(),
                'created_at' => $order->created_at->format('d/m/Y'),
            ])->toArray(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
            'prev_page_url' => $orders->previousPageUrl(),
            'next_page_url' => $orders->nextPageUrl(),
        ];

        return Inertia::render('Account/Orders', [
            'orders' => $ordersData,
        ]);
    }

    public function showOrder(Order $order)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $order->customer_id !== $customer->id) {
            abort(403);
        }

        $order->load(['items.product.images', 'items.productVariant.attributeValues', 'payments']);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount ?? 0,
            'shipping_cost' => $order->shipping_cost ?? 0,
            'total' => $order->total,
            'shipping_first_name' => $order->shipping_first_name,
            'shipping_last_name' => $order->shipping_last_name,
            'shipping_address' => $order->shipping_address,
            'shipping_city' => $order->shipping_city,
            'shipping_postal_code' => $order->shipping_postal_code,
            'shipping_phone' => $order->shipping_phone,
            'created_at' => $order->created_at->format('d/m/Y à H:i'),
            'items' => $order->items->map(function ($item) {
                $primaryImage = $item->product->images->where('is_primary', true)->first()
                    ?? $item->product->images->first();
                return [
                    'id' => $item->id,
                    'name' => $item->product->name,
                    'variant_name' => $item->productVariant
                        ? $item->productVariant->attributeValues->pluck('value')->implode(' / ')
                        : null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'product' => ['primary_image' => $primaryImage?->path],
                ];
            })->toArray(),
        ];

        return Inertia::render('Account/OrderShow', [
            'order' => $orderData,
        ]);
    }

    public function addresses()
    {
        $customer = auth()->user()->customer ?? null;
        $addresses = $customer
            ? $customer->addresses()->where('type', 'shipping')->get()->map(fn($a) => [
                'id' => $a->id,
                'first_name' => $a->first_name,
                'last_name' => $a->last_name,
                'address_line1' => $a->address_line1,
                'postal_code' => $a->postal_code,
                'city' => $a->city,
                'country' => $a->country,
                'phone' => $a->phone,
                'is_default' => (bool) $a->is_default,
            ])->toArray()
            : [];

        return Inertia::render('Account/Addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function storeAddress(Request $request)
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return back()->with('error', 'Profil client non trouvé.');
        }

        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'address'     => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city'        => 'required|string|max:100',
            'country'     => 'required|string|max:2',
            'phone'       => 'nullable|string|max:20',
            'is_default'  => 'nullable|boolean',
        ]);

        $address = CustomerAddress::create([
            'customer_id'  => $customer->id,
            'type'         => 'shipping',
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'address_line1'=> $validated['address'],
            'postal_code'  => $validated['postal_code'] ?? null,
            'city'         => $validated['city'],
            'country'      => $validated['country'],
            'phone'        => $validated['phone'] ?? null,
            'is_default'   => $request->boolean('is_default'),
        ]);

        if ($address->is_default) {
            $address->setAsDefault();
        }

        return back()->with('success', 'Adresse ajoutée.');
    }

    public function destroyAddress(CustomerAddress $address)
    {
        $customer = auth()->user()->customer;
        if (!$customer || $address->customer_id !== $customer->id) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Adresse supprimée.');
    }

    public function loyalty()
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return redirect()->route('account.dashboard');
        }

        $transactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        $transactionsData = [
            'data' => $transactions->map(fn($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'points' => $t->points,
                'description' => $t->description,
                'created_at' => $t->created_at->format('d/m/Y'),
            ])->toArray(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'total' => $transactions->total(),
        ];

        return Inertia::render('Account/Loyalty', [
            'customer' => [
                'id' => $customer->id,
                'points_balance' => $customer->loyalty_points ?? 0,
            ],
            'transactions' => $transactionsData,
        ]);
    }
}
