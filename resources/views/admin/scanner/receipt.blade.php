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
            font-family: 'Courier New', 'DejaVu Sans Mono', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            max-width: 80mm;
            margin: 0 auto;
            padding: 8px;
        }
        .receipt { text-align: center; }
        .receipt h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; }
        .receipt .meta { font-size: 10px; color: #333; margin-bottom: 10px; }
        .receipt .meta p { margin: 2px 0; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .items { text-align: left; font-size: 11px; }
        .items table { width: 100%; border-collapse: collapse; }
        .items td { padding: 3px 0; vertical-align: top; }
        .items .qty { width: 24px; text-align: left; font-weight: bold; }
        .items .name { padding-right: 4px; }
        .items .price { text-align: right; white-space: nowrap; font-weight: 600; }
        .total-line { font-weight: bold; font-size: 14px; margin-top: 12px; padding-top: 8px; border-top: 2px solid #000; text-align: center; }
        .payment-info { font-size: 10px; margin-top: 12px; text-align: left; }
        .payment-info p { margin: 3px 0; }
        .footer { font-size: 9px; margin-top: 16px; text-align: center; }

        /* Optimisation impression thermique */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                padding: 0;
                margin: 0;
                width: 80mm;
            }
            .no-print { display: none !important; }
        }

        /* Support imprimantes 58mm */
        @media (max-width: 58mm) {
            body { max-width: 58mm; font-size: 10px; }
            .receipt h1 { font-size: 13px; }
            .total-line { font-size: 12px; }
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
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <button onclick="window.print()" style="padding: 12px 28px; font-size: 15px; cursor: pointer; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">
                🖨️ Imprimer (Navigateur)
            </button>
            <button onclick="downloadThermalReceipt()" style="padding: 12px 28px; font-size: 15px; cursor: pointer; background: #16a34a; color: white; border: none; border-rounded: 8px; font-weight: 600; box-shadow: 0 2px 4px rgba(22,163,74,0.2);">
                📄 Format Thermique (JSON)
            </button>
            <a href="{{ route('admin.scanner.receipt.text', $order) }}?change={{ $change }}&amount_received={{ $amountReceived }}" target="_blank" style="padding: 12px 28px; font-size: 15px; cursor: pointer; background: #8b5cf6; color: white; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; box-shadow: 0 2px 4px rgba(139,92,246,0.2);">
                📝 Format Texte
            </a>
        </div>
        <p style="font-size: 12px; color: #64748b; margin-top: 12px;">
            <strong>Format Thermique</strong> : pour imprimantes POS ESC/POS (80mm/58mm USB/Network)<br>
            <strong>Format Texte</strong> : pour copier-coller ou debug
        </p>
    </div>

    <script>
        async function downloadThermalReceipt() {
            try {
                const response = await fetch('{{ route("admin.scanner.receipt.thermal", $order) }}?change={{ $change }}&amount_received={{ $amountReceived }}');
                const data = await response.json();

                // Télécharger le JSON
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `receipt-${data.order_number}-thermal.json`;
                a.click();
                URL.revokeObjectURL(url);

                alert('✅ Fichier téléchargé !\n\nUtilisez un driver ESC/POS pour envoyer ces commandes à votre imprimante thermique.');
            } catch (error) {
                alert('❌ Erreur lors de la génération du reçu thermique.');
                console.error(error);
            }
        }

        // Auto-print si paramètre présent
        @if(request()->query('auto_print'))
        window.onload = function() {
            window.print();
        };
        @endif

        // Raccourci clavier Entrée pour imprimer
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey && !e.shiftKey && !e.altKey) {
                window.print();
            }
        });
    </script>
</body>
</html>
