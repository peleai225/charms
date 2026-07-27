<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reçu {{ $order->order_number }}</title>
    @php
        $order->loadMissing(['items.product']);
        $siteName    = \App\Models\Setting::get('site_name', config('app.name', 'Ma Boutique'));
        $siteAddress = \App\Models\Setting::get('contact_address', '');
        $sitePhone   = \App\Models\Setting::get('contact_phone', '');
        $siteLogo    = \App\Models\Setting::get('logo_url', '');
        $currency    = \App\Models\Setting::get('currency_symbol', 'F CFA');
        $isPOS       = $order->source === 'pos';
        $hasChange   = ($change ?? 0) > 0;
    @endphp
    <style>
        /* ── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Base thermique 80mm ─────────────────────────────── */
        :root {
            --accent: #f97316;
            --muted:  #64748b;
            --border: #e2e8f0;
        }
        html { font-size: 12px; }
        body {
            font-family: 'Courier New', 'DejaVu Sans Mono', monospace;
            font-size: 12px;
            line-height: 1.45;
            color: #0f172a;
            background: #fff;
            max-width: 302px; /* 80mm */
            margin: 0 auto;
            padding: 10px 10px 6px;
        }

        /* ── En-tête ─────────────────────────────────────────── */
        .header { text-align: center; margin-bottom: 8px; }
        .header .logo { max-width: 80px; max-height: 40px; margin: 0 auto 4px; display: block; }
        .header .shop-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .header .shop-meta { font-size: 10px; color: var(--muted); margin-top: 2px; line-height: 1.4; }

        /* ── Séparateurs ─────────────────────────────────────── */
        .sep-dash { border: none; border-top: 1px dashed #999; margin: 7px 0; }
        .sep-solid { border: none; border-top: 2px solid #0f172a; margin: 7px 0; }

        /* ── Titre reçu ──────────────────────────────────────── */
        .receipt-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin: 4px 0 2px;
        }
        .receipt-meta {
            text-align: center;
            font-size: 10px;
            color: var(--muted);
            margin-bottom: 4px;
            line-height: 1.5;
        }
        .order-num {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        /* ── Tableau articles ────────────────────────────────── */
        .items { width: 100%; border-collapse: collapse; font-size: 11px; margin: 4px 0; }
        .items tr td { padding: 2.5px 0; vertical-align: top; }
        .items .col-qty  { width: 22px; font-weight: bold; white-space: nowrap; }
        .items .col-name { padding-right: 4px; word-break: break-word; }
        .items .col-name small { display: block; font-size: 9px; color: var(--muted); }
        .items .col-price { text-align: right; white-space: nowrap; font-weight: 600; width: 72px; }

        /* ── Total ───────────────────────────────────────────── */
        .total-block { text-align: center; margin: 6px 0 4px; }
        .total-label { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
        .total-amount {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: .02em;
            line-height: 1.2;
        }

        /* ── Infos paiement ──────────────────────────────────── */
        .payment-grid { font-size: 10.5px; margin: 4px 0; }
        .payment-grid tr td { padding: 1.5px 0; }
        .payment-grid .label { color: var(--muted); padding-right: 6px; white-space: nowrap; }
        .payment-grid .value { font-weight: 600; text-align: right; }
        .change-row td { color: var(--accent) !important; font-weight: bold !important; font-size: 12px !important; }

        /* ── QR code ─────────────────────────────────────────── */
        .qr-block { text-align: center; margin: 6px 0 4px; }
        .qr-block img { width: 72px; height: 72px; display: inline-block; }
        .qr-label { font-size: 9px; color: var(--muted); margin-top: 2px; }

        /* ── Pied de page ────────────────────────────────────── */
        .footer { text-align: center; font-size: 10px; color: var(--muted); margin-top: 8px; line-height: 1.5; }
        .footer strong { color: #0f172a; font-size: 11px; }

        /* ── Boutons hors impression ─────────────────────────── */
        .no-print {
            margin-top: 28px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .no-print p {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #475569;
            margin-bottom: 14px;
        }
        .no-print kbd {
            background: #e2e8f0;
            padding: 3px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 11px;
        }
        .btn-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primary { background: #f97316; color: #fff; }
        .btn-secondary { background: #16a34a; color: #fff; }
        .btn-tertiary { background: #8b5cf6; color: #fff; }
        .btn-ghost { background: #e2e8f0; color: #475569; }
        .no-print .hint {
            margin-top: 12px;
            font-size: 11px;
            color: #94a3b8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.5;
        }

        /* ── Print media ─────────────────────────────────────── */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            html { font-size: 11px; }
            body {
                max-width: 80mm;
                width: 80mm;
                margin: 0;
                padding: 4px 6px;
            }
            .no-print { display: none !important; }
            .total-amount { font-size: 18px; }
        }

        /* ── 58mm fallback ───────────────────────────────────── */
        @media (max-width: 58mm) {
            body { max-width: 58mm; font-size: 10px; padding: 4px; }
            .header .shop-name { font-size: 13px; }
            .total-amount { font-size: 16px; }
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════
         EN-TÊTE
    ═══════════════════════════════════════════════ --}}
    <div class="header">
        @if($siteLogo)
        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="logo">
        @endif
        <div class="shop-name">{{ $siteName }}</div>
        @if($siteAddress || $sitePhone)
        <div class="shop-meta">
            @if($siteAddress){{ $siteAddress }}<br>@endif
            @if($sitePhone)Tél : {{ $sitePhone }}@endif
        </div>
        @endif
    </div>

    <hr class="sep-dash">

    {{-- Titre + référence --}}
    <div class="receipt-title">Reçu de vente</div>
    <div class="receipt-meta">
        <span class="order-num">N° {{ $order->order_number }}</span><br>
        {{ $order->created_at->format('d/m/Y') }} à {{ $order->created_at->format('H:i') }}
        @if($isPOS)
        · Caisse POS
        @endif
    </div>

    <hr class="sep-dash">

    {{-- ═══════════════════════════════════════════════
         ARTICLES
    ═══════════════════════════════════════════════ --}}
    <table class="items">
        @foreach($order->items as $item)
        <tr>
            <td class="col-qty">{{ $item->quantity }}x</td>
            <td class="col-name">
                {{ mb_strtoupper(mb_substr($item->name, 0, 28)) }}@if(mb_strlen($item->name) > 28)…@endif
                @if(!empty($item->variant_name))
                <small>{{ $item->variant_name }}</small>
                @endif
            </td>
            <td class="col-price">
                {{ number_format($item->total ?? ($item->unit_price * $item->quantity), 0, ',', ' ') }} {{ $currency }}
            </td>
        </tr>
        @if(!$loop->last)
        <tr><td colspan="3"><hr class="sep-dash" style="margin:2px 0"></td></tr>
        @endif
        @endforeach
    </table>

    <hr class="sep-solid">

    {{-- ═══════════════════════════════════════════════
         TOTAL
    ═══════════════════════════════════════════════ --}}
    <div class="total-block">
        <div class="total-label">Total</div>
        <div class="total-amount">{{ number_format($order->total, 0, ',', ' ') }} {{ $currency }}</div>
    </div>

    <hr class="sep-dash">

    {{-- ═══════════════════════════════════════════════
         PAIEMENT
    ═══════════════════════════════════════════════ --}}
    <table class="payment-grid" width="100%">
        <tr>
            <td class="label">Mode de paiement</td>
            <td class="value">
                @php
                $pm = match($order->payment_method ?? '') {
                    'cash'         => 'Espèces',
                    'card'         => 'Carte bancaire',
                    'mobile_money' => 'Mobile Money',
                    default        => ucfirst($order->payment_method ?? 'N/A'),
                };
                @endphp
                {{ $pm }}
            </td>
        </tr>
        @if($hasChange || ($order->payment_method === 'cash'))
        <tr>
            <td class="label">Montant reçu</td>
            <td class="value">{{ number_format($amountReceived ?? $order->total, 0, ',', ' ') }} {{ $currency }}</td>
        </tr>
        @if($hasChange)
        <tr class="change-row">
            <td class="label" style="color: #f97316; font-weight: bold;">Monnaie rendue</td>
            <td class="value" style="color: #f97316; font-size: 13px;">{{ number_format($change, 0, ',', ' ') }} {{ $currency }}</td>
        </tr>
        @endif
        @endif
        @if($order->discount_amount > 0)
        <tr>
            <td class="label">Remise</td>
            <td class="value">- {{ number_format($order->discount_amount, 0, ',', ' ') }} {{ $currency }}</td>
        </tr>
        @endif
    </table>

    <hr class="sep-dash">

    {{-- ═══════════════════════════════════════════════
         QR CODE
    ═══════════════════════════════════════════════ --}}
    @if($order->items->first()?->product_id)
    <div class="qr-block">
        <img src="{{ route('admin.barcodes.qrcode-image', $order->items->first()->product_id) }}"
             alt="QR Code" width="72" height="72">
        <div class="qr-label">{{ $order->order_number }}</div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════
         PIED DE PAGE
    ═══════════════════════════════════════════════ --}}
    <div class="footer">
        <strong>Merci pour votre achat !</strong><br>
        Conservez ce reçu comme preuve d'achat.<br>
        {{ $siteName }} · {{ now()->format('Y') }}
    </div>

    {{-- ═══════════════════════════════════════════════
         BOUTONS HORS IMPRESSION
    ═══════════════════════════════════════════════ --}}
    <div class="no-print">
        <p>
            Appuyez sur <kbd>Entrée</kbd> pour imprimer
        </p>
        <div class="btn-row">
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
            @if(\App\Models\Setting::get('pos_printer_enabled', '0') === '1')
            <button id="btn-print-direct" onclick="printDirect()" class="btn btn-secondary" style="background:#0e7490;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17H17.01M5 17H5.01M7 21H17a2 2 0 002-2v-4a2 2 0 00-2-2H7a2 2 0 00-2 2v4a2 2 0 002 2zM7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Envoyer à l'imprimante
            </button>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('admin.scanner.receipt.thermal'))
            <button onclick="downloadThermalJSON()" class="btn btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Format Thermique (JSON)
            </button>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('admin.scanner.receipt.text'))
            <a href="{{ route('admin.scanner.receipt.text', $order) }}?change={{ $change ?? 0 }}&amount_received={{ $amountReceived ?? $order->total }}"
               target="_blank"
               class="btn btn-tertiary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Texte brut
            </a>
            @endif
            <button onclick="window.close()" class="btn btn-ghost">Fermer</button>
        </div>
        <p class="hint">
            <strong>Format Thermique</strong> : ESC/POS pour imprimantes POS 80mm/58mm USB · Network · Bluetooth<br>
            <strong>Format Texte</strong> : copier-coller ou débogage
        </p>
    </div>

    <script>
        // ── Téléchargement JSON ESC/POS ──────────────────────────
        @if(\Illuminate\Support\Facades\Route::has('admin.scanner.receipt.thermal'))
        async function downloadThermalJSON() {
            try {
                const url = '{{ route("admin.scanner.receipt.thermal", $order) }}?change={{ $change ?? 0 }}&amount_received={{ $amountReceived ?? $order->total }}';
                const resp = await fetch(url);
                const data = await resp.json();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const a = Object.assign(document.createElement('a'), {
                    href: URL.createObjectURL(blob),
                    download: `receipt-${data.order_number || '{{ $order->order_number }}'}-thermal.json`
                });
                a.click();
                URL.revokeObjectURL(a.href);
            } catch (err) {
                alert('Erreur lors de la génération du reçu thermique.\n' + err.message);
            }
        }
        @endif

        // ── Impression directe (réseau/USB via serveur) ──────────
        @if(\App\Models\Setting::get('pos_printer_enabled', '0') === '1')
        async function printDirect() {
            const btn = document.getElementById('btn-print-direct');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '⏳ Envoi...';
            try {
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const url  = '/admin/scanner/receipt/{{ $order->id }}/print-direct?change={{ $change ?? 0 }}&amount_received={{ $amountReceived ?? $order->total }}';
                const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    btn.innerHTML = '✓ Imprimé !';
                    btn.style.background = '#16a34a';
                } else {
                    btn.innerHTML = '✗ Erreur';
                    btn.style.background = '#dc2626';
                    alert('Erreur imprimante : ' + data.message);
                }
            } catch (err) {
                btn.innerHTML = '✗ Erreur réseau';
                btn.style.background = '#dc2626';
                alert('Impossible de contacter le serveur : ' + err.message);
            }
            setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; btn.disabled = false; }, 4000);
        }
        @endif

        // ── Auto-print ───────────────────────────────────────────
        @if(request()->query('auto_print'))
        window.onload = () => window.print();
        @endif

        // ── Raccourci Entrée → imprimer ──────────────────────────
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey && !e.shiftKey && !e.altKey
                && !['INPUT','TEXTAREA','SELECT','BUTTON','A'].includes(document.activeElement?.tagName)) {
                window.print();
            }
        });
    </script>
</body>
</html>
