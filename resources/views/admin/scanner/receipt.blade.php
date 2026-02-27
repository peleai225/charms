<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $order->order_number }}</title>
    @php
        $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Ma Boutique'));
        $siteAddress = \App\Models\Setting::get('contact_address', '');
        $sitePhone = \App\Models\Setting::get('contact_phone', '');
        $currencySymbol = \App\Models\Setting::get('currency_symbol', 'F CFA');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            line-height: 1.4; 
            color: #000; 
            max-width: 80mm;
            margin: 0 auto;
            padding: 8px;
        }
        .receipt { text-align: center; }
        .receipt h1 { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .receipt .meta { font-size: 10px; color: #333; margin-bottom: 10px; }
        .receipt .meta p { margin: 2px 0; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .items { text-align: left; font-size: 11px; }
        .items table { width: 100%; }
        .items td { padding: 2px 0; vertical-align: top; }
        .items .qty { width: 24px; text-align: left; }
        .items .name { }
        .items .price { text-align: right; white-space: nowrap; }
        .total-line { font-weight: bold; font-size: 13px; margin-top: 12px; padding-top: 8px; border-top: 2px solid #000; text-align: center; }
        .payment-info { font-size: 10px; margin-top: 12px; }
        .footer { font-size: 9px; margin-top: 16px; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h1>{{ $siteName }}</h1>
        @if($siteAddress || $sitePhone)
        <div class="meta">
            @if($siteAddress)<p>{{ $siteAddress }}</p>@endif
            @if($sitePhone)<p>{{ $sitePhone }}</p>@endif
        </div>
        @endif
        <div class="divider"></div>
        <p><strong>REÇU DE VENTE</strong></p>
        <p>N° {{ $order->order_number }}</p>
        <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
        <div class="divider"></div>

        <div class="items">
            <table>
                @foreach($order->items as $item)
                <tr>
                    <td class="qty">{{ $item->quantity }}x</td>
                    <td class="name">
                        {{ $item->name }}
                        @if($item->variant_name)<br><span style="font-size: 10px;">{{ $item->variant_name }}</span>@endif
                    </td>
                    <td class="price">{{ number_format($item->total, 0, ',', ' ') }} {{ $currencySymbol }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="divider"></div>
        <div class="total-line">
            TOTAL: {{ number_format($order->total, 0, ',', ' ') }} {{ $currencySymbol }}
        </div>

        <div class="payment-info">
            <p>Paiement: {{ match($order->payment_method) {
                'cash' => 'Espèces',
                'card' => 'Carte',
                'mobile_money' => 'Mobile Money',
                default => ucfirst($order->payment_method ?? 'N/A')
            } }}</p>
            @if(($change ?? 0) > 0)
            <p>Montant reçu: {{ number_format($amountReceived ?? $order->total, 0, ',', ' ') }} {{ $currencySymbol }}</p>
            <p>Monnaie rendue: {{ number_format($change, 0, ',', ' ') }} {{ $currencySymbol }}</p>
            @endif
        </div>

        <div class="divider"></div>
        <div class="footer">
            <p>Merci pour votre achat !</p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 24px; padding: 20px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
        <p style="font-size: 14px; color: #475569; margin-bottom: 12px; font-weight: 500;">
            Appuyez sur <kbd style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-family: monospace;">Entrée</kbd> pour imprimer
        </p>
        <button onclick="window.print()" style="padding: 12px 28px; font-size: 15px; cursor: pointer; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600;">
            🖨️ Imprimer le reçu
        </button>
    </div>

    @if(request()->query('auto_print'))
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>
