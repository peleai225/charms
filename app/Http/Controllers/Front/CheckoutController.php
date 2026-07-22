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
use App\Models\Setting;
use App\Services\MoneyFusionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    protected MoneyFusionService $moneyFusion;

    public function __construct(MoneyFusionService $moneyFusion)
    {
        $this->moneyFusion = $moneyFusion;
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

        // Format cart data
        $cartData = [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                $primaryImage = $item->product->images->where('is_primary', true)->first()
                    ?? $item->product->images->first();

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->unit_price * $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'primary_image' => $primaryImage?->path,
                    ],
                ];
            })->toArray(),
            'subtotal' => $cart->subtotal,
            'discount' => $cart->discount_amount ?? 0,
            'total' => $cart->total,
        ];

        // Récupérer les paramètres de paiement
        $settings = [
            'payment_moneyfusion_enabled' => Setting::get('payment_moneyfusion_enabled', '0'),
            'payment_cod_enabled' => Setting::get('payment_cod_enabled', '1'),
        ];

        $customerData = $customer ? [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ] : null;

        $addressesData = $addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'address_line1' => $address->address_line1,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'phone' => $address->phone,
                'is_default' => $address->is_default,
            ];
        })->toArray();

        return Inertia::render('Checkout/Index', [
            'cart' => $cartData,
            'customer' => $customerData,
            'addresses' => $addressesData,
            'settings' => $settings,
        ]);
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
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',

            // Adresse de livraison
            'shipping_first_name' => 'required|string|max:100',
            'shipping_last_name' => 'required|string|max:100',
            'shipping_address' => 'required|string|max:255',
            'shipping_address_2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'shipping_country' => 'required|string|size:2',

            // Adresse de facturation
            'same_billing' => 'boolean',
            'billing_first_name' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_last_name' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_address' => 'required_if:same_billing,false|nullable|string|max:255',
            'billing_address_2' => 'nullable|string|max:255',
            'billing_city' => 'required_if:same_billing,false|nullable|string|max:100',
            'billing_postal_code' => 'nullable|string|max:20',
            'billing_country' => 'required_if:same_billing,false|nullable|string|size:2',

            // Options
            'notes' => 'nullable|string|max:500',
            'save_address' => 'boolean',
            'newsletter' => 'boolean',
            'payment_method' => [
                'required',
                function ($attribute, $value, $fail) {
                    $allowedMethods = [];
                    if (Setting::get('payment_moneyfusion_enabled', '0') === '1') {
                        $allowedMethods[] = 'moneyfusion';
                    }
                    if (Setting::get('payment_cod_enabled', '1') === '1') {
                        $allowedMethods[] = 'cod';
                    }

                    if (empty($allowedMethods)) {
                        $fail('Aucune méthode de paiement n\'est configurée.');
                    }

                    if (!in_array($value, $allowedMethods)) {
                        $fail('La méthode de paiement sélectionnée n\'est pas disponible.');
                    }
                },
            ],
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
            $total = $subtotal - $discount + $shippingCost + $taxAmount;
            
            // Log pour déboguer
            \Log::info('Checkout: Calcul du total', [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shippingCost,
                'tax' => $taxAmount,
                'total' => $total,
                'cart_items_count' => $cart->items->count(),
            ]);

            // Créer la commande
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer?->id,

                // Adresse de livraison
                'shipping_first_name' => $validated['shipping_first_name'],
                'shipping_last_name' => $validated['shipping_last_name'],
                'shipping_email' => $validated['email'] ?? auth()->user()?->email ?? 'noreply@chamse.ci',
                'shipping_phone' => $validated['phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_address_2' => $validated['shipping_address_2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? '',
                'shipping_country' => $validated['shipping_country'],
                
                // Adresse de facturation
                'billing_first_name' => $sameBilling ? $validated['shipping_first_name'] : $validated['billing_first_name'],
                'billing_last_name' => $sameBilling ? $validated['shipping_last_name'] : $validated['billing_last_name'],
                'billing_email' => $validated['email'] ?? auth()->user()?->email ?? 'noreply@chamse.ci',
                'billing_phone' => $validated['phone'],
                'billing_address' => $sameBilling ? $validated['shipping_address'] : $validated['billing_address'],
                'billing_address_2' => $sameBilling ? ($validated['shipping_address_2'] ?? null) : ($validated['billing_address_2'] ?? null),
                'billing_city' => $sameBilling ? $validated['shipping_city'] : $validated['billing_city'],
                'billing_postal_code' => $sameBilling ? ($validated['shipping_postal_code'] ?? '') : ($validated['billing_postal_code'] ?? ''),
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
                'currency' => 'XOF',
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
                    \App\Services\MailConfigService::configureFromSettings();
                    Mail::to($order->billing_email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    \Log::error('Failed to send order confirmation email: ' . $e->getMessage());
                }
            }

            // Envoyer la confirmation WhatsApp
            try {
                \App\Services\WhatsAppService::sendOrderConfirmation($order);
            } catch (\Exception $e) {
                \Log::error('Failed to send WhatsApp order confirmation: ' . $e->getMessage());
            }

            // Pour COD, mettre à jour le statut avant le commit
            if ($validated['payment_method'] === 'cod') {
                $order->update([
                    'payment_status' => 'pending',
                    'status'         => 'confirmed',
                ]);
            }

            DB::commit();

            // Vider le panier uniquement après le commit réussi
            $cart->clear();

            // Stocker l'ID de commande en session pour vérification d'accès (guest + auth)
            session()->push('checkout_order_ids', $order->id);

            // Rediriger vers le paiement en ligne
            if ($validated['payment_method'] === 'moneyfusion') {
                return $this->redirectToPayment($order);
            }

            // Paiement à la livraison → page de succès
            return redirect()->route('checkout.success', ['order' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Erreur lors de la création de la commande : ' . $e->getMessage());
        }
    }

    /**
     * Rediriger vers le service de paiement
     */
    protected function redirectToPayment(Order $order)
    {
        if (!$this->moneyFusion->isConfigured()) {
            return redirect()->route('checkout.payment', ['order' => $order->id])
                ->with('error', 'MoneyFusion n\'est pas configuré.');
        }

        $result = $this->moneyFusion->initializePayment($order, [
            'name' => $order->billing_first_name,
            'surname' => $order->billing_last_name,
            'phone' => $order->billing_phone ?? $order->shipping_phone,
        ]);

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return redirect()->route('checkout.payment', ['order' => $order->id])
            ->with('error', $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.');
    }

    /**
     * Page de paiement (fallback si erreur)
     */
    public function payment(Order $order)
    {
        $this->authorizeOrderAccess($order);

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', ['order' => $order->id]);
        }

        return view('front.checkout.payment', compact('order'));
    }

    /**
     * Retour de paiement - Confirmation
     */
    public function confirmation(Request $request)
    {
        $orderId = $request->get('order');
        $order = Order::findOrFail($orderId);
        $this->authorizeOrderAccess($order);

        // Vérifier le statut du paiement via l'API
        if ($order->payment_status === 'pending' && $order->payment_method === 'moneyfusion') {
            $payment = $order->payments()->latest()->first();
            if ($payment && $payment->transaction_id) {
                $status = $this->moneyFusion->checkPaymentStatus($payment->transaction_id);

                if ($status['success'] && $status['status'] === 'paid') {
                    $payment->update([
                        'status' => 'completed',
                        'paid_at' => now(),
                    ]);
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'paid_at' => now(),
                    ]);

                    event(new \App\Events\OrderPaid($order, $payment));
                }
            }
        }

        // Si déjà payé, rediriger directement vers la page succès
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', ['order' => $order->id]);
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
        if ($order) {
            $this->authorizeOrderAccess($order);
        }

        return Inertia::render('Checkout/Cancel', [
            'order' => $order ? ['order_number' => $order->order_number, 'id' => $order->id] : null,
        ]);
    }

    /**
     * Page de succès
     */
    public function success(Request $request)
    {
        $orderId = $request->get('order');
        $order = Order::with(['items.product.images', 'items.variant'])->findOrFail($orderId);
        $this->authorizeOrderAccess($order);

        // Retirer la commande de la session
        $orderIds = array_filter(session('checkout_order_ids', []), fn($id) => $id !== $order->id);
        session(['checkout_order_ids' => array_values($orderIds)]);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'total' => $order->total,
            'shipping_first_name' => $order->shipping_first_name,
            'shipping_last_name' => $order->shipping_last_name,
            'shipping_address' => $order->shipping_address,
            'shipping_city' => $order->shipping_city,
            'shipping_phone' => $order->shipping_phone,
            'items' => $order->items->map(function ($item) {
                $primaryImage = $item->product->images->where('is_primary', true)->first()
                    ?? $item->product->images->first();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'variant_name' => $item->variant_name ?? null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'product' => ['primary_image' => $primaryImage?->path],
                ];
            })->toArray(),
        ];

        return Inertia::render('Checkout/Success', [
            'order' => $orderData,
        ]);
    }

    /**
     * Récupérer ou créer le client
     */
    protected function getOrCreateCustomer(array $data): ?Customer
    {
        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
            if ($customer) {
                return $customer;
            }
            if (!empty($data['email'])) {
                $customer = Customer::where('email', $data['email'])->first();
                if ($customer) {
                    $customer->update(['user_id' => auth()->id()]);
                    return $customer;
                }
            }
            return Customer::create([
                'user_id' => auth()->id(),
                'first_name' => $data['shipping_first_name'],
                'last_name' => $data['shipping_last_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'status' => 'active',
                'type' => 'individual',
            ]);
        }

        // Client invité : chercher par email ou téléphone
        if (!empty($data['email'])) {
            return Customer::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['shipping_first_name'],
                    'last_name' => $data['shipping_last_name'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                    'type' => 'individual',
                ]
            );
        }

        // Sans email, chercher par téléphone
        return Customer::firstOrCreate(
            ['phone' => $data['phone']],
            [
                'first_name' => $data['shipping_first_name'],
                'last_name' => $data['shipping_last_name'],
                'email' => null,
                'status' => 'active',
                'type' => 'individual',
            ]
        );
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
    public function calculateShipping(Cart $cart, array $data): float
    {
        // Vérifier si la livraison est activée
        $shippingEnabled = Setting::get('shipping_enabled', '1') === '1';
        if (!$shippingEnabled) {
            return 0;
        }

        // Récupérer le seuil de livraison gratuite
        $freeShippingThreshold = (float) Setting::get('free_shipping_threshold', 50000);
        
        // Livraison gratuite au-dessus du seuil configuré
        if ($freeShippingThreshold > 0 && $cart->subtotal >= $freeShippingThreshold) {
            return 0;
        }

        // Vérifier les zones de livraison configurées
        $shippingZones = json_decode(Setting::get('shipping_zones', '[]'), true) ?: [];
        $city = strtolower(trim($data['shipping_city'] ?? ''));
        
        // Chercher dans les zones par ville
        foreach ($shippingZones as $zone) {
            $cities = array_map('trim', explode(',', strtolower($zone['cities'] ?? '')));
            if (in_array($city, $cities)) {
                return (float) ($zone['price'] ?? 0);
            }
        }

        // Frais par pays (fallback si pas de zones configurées)
        $countryRates = [
            'CI' => 2000, // Côte d'Ivoire
            'SN' => 3000, // Sénégal
            'ML' => 3500, // Mali
            'BF' => 3500, // Burkina Faso
            'TG' => 3000, // Togo
            'BJ' => 3000, // Bénin
            'FR' => 15000, // France
        ];

        $country = $data['shipping_country'] ?? 'CI';
        
        // Utiliser le tarif forfaitaire si configuré, sinon utiliser les tarifs par pays
        $flatRate = Setting::get('flat_rate_shipping');
        if ($flatRate && $flatRate > 0) {
            return (float) $flatRate;
        }

        return $countryRates[$country] ?? 5000;
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
        $this->authorizeOrderAccess($order);

        $validated = $request->validate([
            'method' => 'nullable|string|in:moneyfusion,cod',
        ]);
        $method = $validated['method'] ?? 'moneyfusion';

        if ($method === 'cod') {
            $order->update([
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'confirmed',
            ]);

            return redirect()->route('checkout.success', ['order' => $order->id]);
        }

        return $this->redirectToPayment($order);
    }

    /**
     * Vérifier que l'utilisateur/session peut accéder à cette commande
     */
    protected function authorizeOrderAccess(Order $order): void
    {
        // Admin peut tout voir
        if (auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff'])) {
            return;
        }

        // Client connecté : vérifier customer->user_id
        if (auth()->check() && $order->customer?->user_id === auth()->id()) {
            return;
        }

        // Session : commande créée pendant ce checkout
        $orderIds = session('checkout_order_ids', []);
        if (in_array($order->id, $orderIds)) {
            return;
        }

        // Token de paiement dans l'URL (retour depuis MoneyFusion)
        // MoneyFusion peut ajouter plusieurs paramètres token — on teste tous
        $tokens = request()->query('token');
        if (is_string($tokens)) {
            $tokens = [$tokens];
        }
        if (!empty($tokens)) {
            foreach ((array) $tokens as $token) {
                if ($token && $order->payments()->where('transaction_id', $token)->exists()) {
                    session()->push('checkout_order_ids', $order->id);
                    return;
                }
            }
        }

        // Fallback : si la commande a un paiement et qu'on vient de la page MoneyFusion (referer)
        if (request()->has('token') && $order->payments()->exists()) {
            session()->push('checkout_order_ids', $order->id);
            return;
        }

        abort(403, 'Vous n\'avez pas accès à cette commande.');
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

