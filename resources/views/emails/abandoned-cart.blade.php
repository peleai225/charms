<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre panier vous attend !</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0 0 8px 0; font-size: 26px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.9; font-size: 15px; }
        .content { padding: 32px 30px; }
        .greeting { font-size: 17px; margin-bottom: 16px; }
        .cart-items { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; margin: 24px 0; }
        .cart-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-bottom: 1px solid #f3f4f6; }
        .cart-item:last-child { border-bottom: none; }
        .item-img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; background: #f3f4f6; }
        .item-img-placeholder { width: 70px; height: 70px; border-radius: 8px; background: linear-gradient(135deg, #e0e7ff, #ede9fe); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .item-info { flex: 1; }
        .item-name { font-weight: 600; color: #1e293b; font-size: 15px; margin: 0 0 4px 0; }
        .item-detail { color: #64748b; font-size: 13px; margin: 0; }
        .item-price { font-weight: 700; color: #6366f1; font-size: 15px; white-space: nowrap; }
        .total-row { background: #f8f7ff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-weight: 600; color: #475569; }
        .total-amount { font-weight: 800; color: #6366f1; font-size: 20px; }
        .cta-section { text-align: center; margin: 32px 0; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white !important; text-decoration: none; padding: 16px 36px; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 20px rgba(99,102,241,0.35); }
        .urgency { background: #fff7ed; border-left: 4px solid #f97316; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 24px 0; }
        .urgency p { margin: 0; color: #9a3412; font-size: 14px; }
        .footer { background: #f9fafb; padding: 24px 30px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer a { color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 48px; margin-bottom: 12px;">🛒</div>
            <h1>Votre panier vous attend !</h1>
            <p>Vous avez laissé des articles qui n'attendent que vous</p>
        </div>

        <div class="content">
            <p class="greeting">Bonjour <strong>{{ $customer->first_name }}</strong>,</p>

            <p>Vous avez commencé vos achats sur <strong>{{ \App\Models\Setting::get('site_name', config('app.name')) }}</strong> mais vous n'avez pas finalisé votre commande.</p>
            <p>Vos articles sont toujours disponibles — mais les stocks s'écoulent vite !</p>

            <!-- Articles du panier -->
            <div class="cart-items">
                @foreach($cart->items->take(4) as $item)
                <div class="cart-item">
                    @php $img = $item->product->images->first()?->url; @endphp
                    @if($img)
                        <img src="{{ $img }}" alt="{{ $item->product->name }}" class="item-img">
                    @else
                        <div class="item-img-placeholder">📦</div>
                    @endif
                    <div class="item-info">
                        <p class="item-name">{{ $item->product->name }}</p>
                        <p class="item-detail">
                            Qté : {{ $item->quantity }}
                            @if($item->variant) — {{ $item->variant->name }} @endif
                        </p>
                    </div>
                    <div class="item-price">{{ number_format($item->total, 0, ',', ' ') }} F</div>
                </div>
                @endforeach

                @if($cart->items->count() > 4)
                <div class="cart-item" style="justify-content:center; color:#6b7280; font-size:13px;">
                    + {{ $cart->items->count() - 4 }} autre(s) article(s) dans votre panier
                </div>
                @endif

                <div class="total-row">
                    <span class="total-label">Total estimé</span>
                    <span class="total-amount">{{ number_format($cart->total, 0, ',', ' ') }} F CFA</span>
                </div>
            </div>

            <!-- Urgence -->
            <div class="urgency">
                <p>⏰ <strong>Les stocks sont limités.</strong> Finalisez votre commande avant que vos articles ne soient épuisés ou que le prix change.</p>
            </div>

            <!-- Bouton CTA -->
            <div class="cta-section">
                <a href="{{ route('cart.index') }}" class="cta-btn">Reprendre mon panier →</a>
            </div>

            <p style="color:#64748b; font-size:14px; text-align:center;">Des questions ? Contactez-nous à <a href="mailto:{{ \App\Models\Setting::get('contact_email') }}" style="color:#6366f1;">{{ \App\Models\Setting::get('contact_email') }}</a></p>
        </div>

        <div class="footer">
            <p>Vous recevez cet email car vous avez un panier en attente sur <strong>{{ \App\Models\Setting::get('site_name', config('app.name')) }}</strong>.</p>
            <p>© {{ date('Y') }} {{ \App\Models\Setting::get('site_name', config('app.name')) }} — Tous droits réservés</p>
        </div>
    </div>
</body>
</html>
