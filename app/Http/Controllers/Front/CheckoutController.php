<?php

namespace App\Http\Controllers\Front;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CinetPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected CinetPayService $cinetPay;

    public function __construct(CinetPayService $cinetPay)
    {
        $this->cinetPay = $cinetPay;
    }

    /**
     * Étape 1 : Afficher le formulaire d'adresses
     */
    public function index()
    {
        $cart = $this->getCart();

        if ($cart->is_empty) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        $cart->load(['items.product.images', 'items.variant.attributeValues.attribute']);

        // Récupérer le client et ses adresses si connecté
        $customer = null;
        $addresses = collect();

        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
            if ($customer) {
                $addresses = $customer->addresses;
            }
        }

        return view('front.checkout.index', compact('cart', 'customer', 'addresses'));
    }

    /**
     * Étape 2 : Traiter les informations et créer la commande
     */
    public function store(Request $request)
    {
        $cart = $this->getCart();

        if ($cart->is_empty) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        // Valider les données
        $validated = $request->validate([
            // Informations client
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            
            // Adresse de livraison
            'shipping_first_name' => 'required|string|max:100',
            'shipping_last_name' => 'required|string|max:100',
            'shipping_address' => 'required|string|max:255',
            'shipping_address_2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|size:2',
            
            // Adresse de facturation
            'same_billing' => 'boolean',
            'billing_first_name' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_last_name' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_address' => 'required_if:same_billing,false|nullable|string|max:255',
            'billing_address_2' => 'nullable|string|max:255',
            'billing_city' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_postal_code' => 'required_if:same_billing,false|nullable|string|max:20',
            'billing_country' => 'required_if:same_billing,false|nullable|string|size:2',
            
            // Options
            'notes' => 'nullable|string|max:500',
            'save_address' => 'boolean',
            'newsletter' => 'boolean',
            'payment_method' => 'required|in:cinetpay,cod',
        ]);

        $cart->load(['items.product', 'items.variant']);

        // Vérifier le stock
        foreach ($cart->items as $item) {
            $stockAvailable = $item->variant 
                ? $item->variant->stock_quantity 
                : $item->product->stock_quantity;

            if ($stockAvailable < $item->quantity && !$item->product->allow_backorder) {
                return back()->with('error', "Stock insuffisant pour {$item->product->name}");
            }
        }

        DB::beginTransaction();

        try {
            // Créer ou récupérer le client
            $customer = $this->getOrCreateCustomer($validated);

            // Adresse de facturation
            $sameBilling = $request->boolean('same_billing', true);
            
            // Calculer les totaux
            $subtotal = $cart->subtotal;
            $discount = $cart->discount_amount;
            $shippingCost = $this->calculateShipping($cart, $validated);
            $taxAmount = $this->calculateTax($subtotal - $discount);
            $total = $subtotal - $discount + $shippingCost;

            // Créer la commande
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer?->id,
                
                // Adresse de livraison
                'shipping_first_name' => $validated['shipping_first_name'],
                'shipping_last_name' => $validated['shipping_last_name'],
                'shipping_email' => $validated['email'],
                'shipping_phone' => $validated['phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_address_2' => $validated['shipping_address_2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_country' => $validated['shipping_country'],
                
                // Adresse de facturation
                'billing_first_name' => $sameBilling ? $validated['shipping_first_name'] : $validated['billing_first_name'],
                'billing_last_name' => $sameBilling ? $validated['shipping_last_name'] : $validated['billing_last_name'],
                'billing_email' => $validated['email'],
                'billing_phone' => $validated['phone'],
                'billing_address' => $sameBilling ? $validated['shipping_address'] : $validated['billing_address'],
                'billing_address_2' => $sameBilling ? ($validated['shipping_address_2'] ?? null) : ($validated['billing_address_2'] ?? null),
                'billing_city' => $sameBilling ? $validated['shipping_city'] : $validated['billing_city'],
                'billing_postal_code' => $sameBilling ? $validated['shipping_postal_code'] : $validated['billing_postal_code'],
                'billing_country' => $sameBilling ? $validated['shipping_country'] : $validated['billing_country'],
                
                // Montants
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total' => $total,
                
                // Infos
                'coupon_code' => $cart->coupon_code,
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'currency' => config('cinetpay.currency', 'XOF'),
            ]);

            // Créer les lignes de commande
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'tax_rate' => $item->product->tax_rate ?? 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]);
            }

            // Sauvegarder l'adresse si demandé
            if ($request->boolean('save_address') && $customer) {
                $this->saveAddress($customer, $validated, 'shipping');
                if (!$sameBilling) {
                    $this->saveAddress($customer, $validated, 'billing');
                }
            }

            // Déclencher l'événement de création
            event(new OrderCreated($order));

            // Envoyer l'email de confirmation
            if ($order->billing_email) {
                try {
                    Mail::to($order->billing_email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    \Log::error('Failed to send order confirmation: ' . $e->getMessage());
                }
            }

            // Vider le panier
            $cart->clear();

            DB::commit();

            // Rediriger vers le paiement
            if ($validated['payment_method'] === 'cinetpay') {
                return $this->redirectToPayment($order);
            }

            // Paiement à la livraison
            $order->update([
                'payment_status' => 'cod',
                'status' => 'processing',
            ]);

            return redirect()->route('checkout.success', ['order' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création de la commande. Veuillez réessayer.');
        }
    }

    /**
     * Rediriger vers CinetPay
     */
    protected function redirectToPayment(Order $order)
    {
        if (!$this->cinetPay->isConfigured()) {
            // Mode démo sans CinetPay configuré
            return redirect()->route('checkout.payment', ['order' => $order->id]);
        }

        $result = $this->cinetPay->initializePayment($order, [
            'name' => $order->billing_first_name,
            'surname' => $order->billing_last_name,
            'email' => $order->billing_email,
            'phone' => $order->billing_phone,
            'address' => $order->billing_address,
            'city' => $order->billing_city,
            'country' => $order->billing_country,
            'zip' => $order->billing_postal_code,
        ]);

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return redirect()->route('checkout.payment', ['order' => $order->id])
            ->with('error', $result['message'] ?? 'Erreur de paiement');
    }

    /**
     * Page de paiement (si CinetPay non configuré ou erreur)
     */
    public function payment(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', ['order' => $order->id]);
        }

        return view('front.checkout.payment', compact('order'));
    }

    /**
     * Retour de CinetPay - Confirmation
     */
    public function confirmation(Request $request)
    {
        $orderId = $request->get('order');
        $order = Order::findOrFail($orderId);

        // Vérifier le statut du paiement
        if ($order->payment_status === 'pending') {
            // Essayer de vérifier le statut via l'API
            $payment = $order->payments()->latest()->first();
            if ($payment && $payment->transaction_id) {
                $status = $this->cinetPay->checkPaymentStatus($payment->transaction_id);
                
                if ($status['success'] && $status['status'] === 'ACCEPTED') {
                    $payment->update([
                        'status' => 'completed',
                        'paid_at' => now(),
                    ]);
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'paid_at' => now(),
                    ]);
                }
            }
        }

        return view('front.checkout.confirmation', compact('order'));
    }

    /**
     * Page d'annulation
     */
    public function cancel(Request $request)
    {
        $orderId = $request->get('order');
        $order = Order::find($orderId);

        return view('front.checkout.cancel', compact('order'));
    }

    /**
     * Page de succès
     */
    public function success(Request $request)
    {
        $orderId = $request->get('order');
        $order = Order::with(['items.product', 'items.variant'])->findOrFail($orderId);

        return view('front.checkout.success', compact('order'));
    }

    /**
     * Récupérer ou créer le client
     */
    protected function getOrCreateCustomer(array $data): ?Customer
    {
        if (auth()->check()) {
            $customer = Customer::firstOrCreate(
                ['user_id' => auth()->id()],
                [
                    'first_name' => $data['shipping_first_name'],
                    'last_name' => $data['shipping_last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]
            );
            return $customer;
        }

        // Client invité - créer un enregistrement sans user_id
        return Customer::create([
            'first_name' => $data['shipping_first_name'],
            'last_name' => $data['shipping_last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => 'active',
            'type' => 'guest',
        ]);
    }

    /**
     * Sauvegarder une adresse
     */
    protected function saveAddress(Customer $customer, array $data, string $type): void
    {
        $prefix = $type === 'shipping' ? 'shipping_' : 'billing_';

        CustomerAddress::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'type' => $type,
                'is_default' => true,
            ],
            [
                'first_name' => $data[$prefix . 'first_name'],
                'last_name' => $data[$prefix . 'last_name'],
                'address_line1' => $data[$prefix . 'address'],
                'address_line2' => $data[$prefix . 'address_2'] ?? null,
                'city' => $data[$prefix . 'city'],
                'postal_code' => $data[$prefix . 'postal_code'],
                'country' => $data[$prefix . 'country'],
                'phone' => $data['phone'] ?? null,
            ]
        );
    }

    /**
     * Calculer les frais de livraison
     */
    protected function calculateShipping(Cart $cart, array $data): float
    {
        // Livraison gratuite au-dessus d'un certain montant
        if ($cart->subtotal >= 50000) { // 50 000 XOF
            return 0;
        }

        // Frais fixes selon le pays
        $shippingRates = [
            'CI' => 2000, // Côte d'Ivoire
            'SN' => 3000, // Sénégal
            'ML' => 3500, // Mali
            'BF' => 3500, // Burkina Faso
            'TG' => 3000, // Togo
            'BJ' => 3000, // Bénin
            'FR' => 15000, // France
        ];

        return $shippingRates[$data['shipping_country']] ?? 5000;
    }

    /**
     * Calculer la TVA
     */
    protected function calculateTax(float $amount): float
    {
        // TVA incluse dans les prix en général pour le B2C
        // Si TVA séparée : return $amount * 0.18; (18% TVA Côte d'Ivoire)
        return 0;
    }

    /**
     * Générer un numéro de commande unique
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Traiter le choix de paiement
     */
    public function processPayment(Request $request, Order $order)
    {
        $method = $request->input('method', 'cinetpay');

        if ($method === 'cod') {
            $order->update([
                'payment_method' => 'cod',
                'payment_status' => 'cod',
                'status' => 'processing',
            ]);

            return redirect()->route('checkout.success', ['order' => $order->id]);
        }

        // CinetPay
        return $this->redirectToPayment($order);
    }

    /**
     * Récupérer le panier
     */
    protected function getCart(): Cart
    {
        $customer = null;
        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
        }

        return Cart::getOrCreate(session()->getId(), $customer);
    }
}

