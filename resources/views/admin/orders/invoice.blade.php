<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $order->order_number }}</title>
    @php
        $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Ma Boutique'));
        $siteLogo = \App\Models\Setting::get('logo');
        $siteAddress = \App\Models\Setting::get('contact_address', 'Adresse non définie');
        $sitePhone = \App\Models\Setting::get('contact_phone', '');
        $siteEmail = \App\Models\Setting::get('contact_email', '');
        $primaryColor = \App\Models\Setting::get('primary_color', '#2563eb');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .container { padding: 40px; }
        
        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company { }
        .company h1 { font-size: 24px; color: {{ $primaryColor }}; margin-bottom: 5px; }
        .company p { color: #666; font-size: 11px; }
        .company-logo { max-height: 60px; max-width: 200px; margin-bottom: 10px; }
        
        .invoice-info { text-align: right; }
        .invoice-info h2 { font-size: 28px; color: #333; margin-bottom: 10px; }
        .invoice-info table { margin-left: auto; }
        .invoice-info table td { padding: 3px 0; }
        .invoice-info table td:first-child { color: #666; padding-right: 15px; }
        
        /* Addresses */
        .addresses { display: table; width: 100%; margin-bottom: 30px; }
        .address-box { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
        .address-box:last-child { padding-right: 0; padding-left: 20px; }
        .address-box h3 { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 8px; letter-spacing: 1px; }
        .address-box p { margin-bottom: 3px; }
        .address-box .name { font-weight: bold; font-size: 13px; }
        
        /* Table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background: #f3f4f6; padding: 12px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #666; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; }
        table.items td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items .qty { text-align: center; }
        table.items .price, table.items .total { text-align: right; }
        table.items tbody tr:last-child td { border-bottom: 2px solid #e5e7eb; }
        
        /* Totals */
        .totals { width: 300px; margin-left: auto; }
        .totals table { width: 100%; }
        .totals td { padding: 8px 0; }
        .totals td:first-child { color: #666; }
        .totals td:last-child { text-align: right; font-weight: 500; }
        .totals tr.total { border-top: 2px solid #333; }
        .totals tr.total td { font-size: 16px; font-weight: bold; padding-top: 12px; }
        
        /* Footer */
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #666; font-size: 10px; }
        
        /* Payment info */
        .payment-info { background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .payment-info h4 { font-size: 11px; text-transform: uppercase; color: #666; margin-bottom: 8px; }
        
        /* Status badge */
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status.paid { background: #dcfce7; color: #166534; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.cod { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="company">
                        @if($siteLogo)
                            <img src="{{ public_path('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="company-logo" style="max-height: 60px; max-width: 200px; margin-bottom: 10px;">
                        @else
                            <h1>{{ $siteName }}</h1>
                        @endif
                        <p>
                            {{ $siteAddress }}<br>
                            @if($sitePhone)Téléphone: {{ $sitePhone }}<br>@endif
                            @if($siteEmail)Email: {{ $siteEmail }}@endif
                        </p>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <h2 style="font-size: 28px; color: #333; margin-bottom: 10px;">FACTURE</h2>
                    <table style="margin-left: auto;">
                        <tr>
                            <td style="color: #666; padding-right: 15px;">N° Facture</td>
                            <td style="font-weight: bold;">{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666; padding-right: 15px;">Date</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666; padding-right: 15px;">Statut</td>
                            <td>
                                @if($order->payment_status === 'paid')
                                    <span class="status paid">Payée</span>
                                @elseif($order->payment_status === 'cod')
                                    <span class="status cod">À la livraison</span>
                                @else
                                    <span class="status pending">En attente</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- Addresses -->
        <table style="width: 100%; margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                    <h3 style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 8px; letter-spacing: 1px;">Facturer à</h3>
                    <p class="name" style="font-weight: bold; font-size: 13px;">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                    <p>{{ $order->billing_address }}</p>
                    @if($order->billing_address_2)<p>{{ $order->billing_address_2 }}</p>@endif
                    <p>{{ $order->billing_postal_code }} {{ $order->billing_city }}</p>
                    <p>{{ $order->billing_country }}</p>
                    <p style="margin-top: 8px;">{{ $order->billing_email }}</p>
                    @if($order->billing_phone)<p>{{ $order->billing_phone }}</p>@endif
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <h3 style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 8px; letter-spacing: 1px;">Livrer à</h3>
                    <p class="name" style="font-weight: bold; font-size: 13px;">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    @if($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
                    <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                    <p>{{ $order->shipping_country }}</p>
                </td>
            </tr>
        </table>
        
        <!-- Items -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="qty" style="width: 15%;">Quantité</th>
                    <th class="price" style="width: 17%;">Prix unitaire</th>
                    <th class="total" style="width: 18%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if($item->variant_name)
                            <br><span style="color: #666; font-size: 11px;">{{ $item->variant_name }}</span>
                        @endif
                        @if($item->sku)
                            <br><span style="color: #999; font-size: 10px;">SKU: {{ $item->sku }}</span>
                        @endif
                    </td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="price">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="total">{{ number_format($item->total, 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td>Sous-total</td>
                    <td>{{ number_format($order->subtotal, 0, ',', ' ') }} F</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td>Réduction @if($order->coupon_code)({{ $order->coupon_code }})@endif</td>
                    <td style="color: #10b981;">-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</td>
                </tr>
                @endif
                <tr>
                    <td>Livraison</td>
                    <td>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</td>
                </tr>
                @if($order->tax_amount > 0)
                <tr>
                    <td>TVA</td>
                    <td>{{ number_format($order->tax_amount, 0, ',', ' ') }} F</td>
                </tr>
                @endif
                <tr class="total">
                    <td>Total</td>
                    <td>{{ number_format($order->total, 0, ',', ' ') }} F CFA</td>
                </tr>
            </table>
        </div>
        
        <!-- Payment info -->
        <div class="payment-info">
            <h4>Informations de paiement</h4>
            <p>
                <strong>Mode de paiement :</strong> 
                @if($order->payment_method === 'cinetpay')
                    CinetPay (Mobile Money / Carte)
                @elseif($order->payment_method === 'cod')
                    Paiement à la livraison
                @else
                    {{ $order->payment_method ?? 'Non spécifié' }}
                @endif
            </p>
            @if($order->paid_at)
                <p><strong>Payée le :</strong> {{ $order->paid_at->format('d/m/Y à H:i') }}</p>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Merci pour votre confiance !</p>
            <p style="margin-top: 10px;">
                {{ $siteName }}<br>
                Cette facture est générée automatiquement et fait foi.
            </p>
        </div>
    </div>
</body>
</html>

