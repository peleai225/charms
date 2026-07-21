<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression étiquettes NIIMBOT</title>

    <style>
        /* ─── Variables format (écrasées par JS) ───────────────────────── */
        :root {
            --lw: 50mm;
            --lh: 30mm;
            --font-name: 7.5pt;
            --font-price: 10pt;
            --font-sku: 6.5pt;
            --barcode-h: 12mm;
        }

        /* ─── Reset général ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
        }

        /* ─── Panneau de contrôle (no-print) ────────────────────────────── */
        #control-panel {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1e293b;
            color: #f8fafc;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        #control-panel .cp-title {
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        #control-panel label { font-size: 12px; color: #94a3b8; white-space: nowrap; }
        #control-panel select,
        #control-panel input[type="number"] {
            background: #334155;
            border: 1px solid #475569;
            color: #f8fafc;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 13px;
        }
        #control-panel .cp-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #control-panel .cp-separator {
            width: 1px;
            height: 32px;
            background: #334155;
        }
        #control-panel .cp-check {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #cbd5e1;
            cursor: pointer;
        }
        #control-panel .cp-check input { accent-color: #6366f1; width: 14px; height: 14px; }
        .btn-print {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            white-space: nowrap;
        }
        .btn-print:hover { opacity: .9; }
        .btn-close {
            background: #475569;
            color: #f8fafc;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
        }
        .label-count-badge {
            background: #6366f1;
            color: #fff;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        /* ─── Zone de prévisualisation ──────────────────────────────────── */
        #preview-zone {
            margin-top: 68px;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            min-height: calc(100vh - 68px);
        }

        /* ─── Étiquette ─────────────────────────────────────────────────── */
        .label {
            width: var(--lw);
            height: var(--lh);
            background: #fff;
            border: 1px dashed #94a3b8;
            padding: 1.5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            position: relative;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1mm;
        }
        .label-name {
            font-size: var(--font-name);
            font-weight: 700;
            line-height: 1.2;
            overflow: hidden;
            flex: 1;
            word-break: break-word;
        }
        .label-sku {
            font-size: var(--font-sku);
            color: #64748b;
            font-family: monospace;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .barcode-wrap {
            text-align: center;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .barcode-wrap img {
            max-width: 100%;
            height: var(--barcode-h);
            object-fit: contain;
        }
        .label-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .barcode-num {
            font-family: monospace;
            font-size: 5.5pt;
            color: #64748b;
        }
        .label-price {
            font-size: var(--font-price);
            font-weight: 800;
            white-space: nowrap;
        }

        /* format horizontal D11 : barcode vertical */
        .label.fmt-d11 { flex-direction: row; padding: 1mm 1.5mm; gap: 1mm; }
        .label.fmt-d11 .barcode-wrap { flex-direction: column; min-width: 60%; }
        .label.fmt-d11 .label-info { display: flex; flex-direction: column; justify-content: space-between; flex: 1; }
        .label.fmt-d11 .label-name { font-size: 6pt; }
        .label.fmt-d11 .label-price { font-size: 8pt; }
        .label.fmt-d11 .label-sku { font-size: 5.5pt; }

        /* ─── IMPRESSION ─────────────────────────────────────────────────── */
        @media print {
            #control-panel { display: none !important; }
            #preview-zone { margin-top: 0; padding: 0; background: #fff; gap: 0; }

            body { background: #fff; }

            .label {
                border: none;
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                margin: 0;
                /* taille exacte via @page */
                width: 100vw;
                height: 100vh;
                max-width: 100%;
                max-height: 100%;
            }
            .label:last-child { page-break-after: avoid; break-after: avoid; }
        }

        /* @page sera injecté par JS selon le format choisi */

        /* ─── Bouton Bluetooth ──────────────────────────────────────────── */
        .btn-bt {
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-bt:hover { opacity: .88; }
        .btn-bt:disabled { opacity: .45; cursor: not-allowed; }

        /* ─── Toast statut BT ───────────────────────────────────────────── */
        #bt-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            min-width: 280px;
            max-width: 420px;
            background: #1e293b;
            color: #f8fafc;
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            z-index: 9999;
            box-shadow: 0 8px 32px rgba(0,0,0,.45);
            border: 1px solid #334155;
        }
        #bt-toast.bt-toast--success  { border-color: #22c55e; }
        #bt-toast.bt-toast--error    { border-color: #ef4444; }
        #bt-toast.bt-toast--printing { border-color: #6366f1; }
        .toast-icon  { font-size: 22px; flex-shrink: 0; margin-top: 1px; }
        .toast-body  { flex: 1; }
        .toast-text  { font-size: 13px; font-weight: 500; }
        .toast-bar-wrap { height: 4px; background: #334155; border-radius: 999px; margin-top: 8px; overflow: hidden; }
        .toast-bar { height: 100%; background: linear-gradient(90deg, #6366f1, #0ea5e9); border-radius: 999px; width: 0; transition: width .3s; }
        .toast-close { background: none; border: none; color: #64748b; font-size: 16px; cursor: pointer; padding: 0; align-self: flex-start; }
        .toast-close:hover { color: #f8fafc; }

        @media print {
            #bt-toast { display: none !important; }
        }
    </style>

    <!-- Feuille de style @page dynamique -->
    <style id="page-style">
        @page { size: 50mm 30mm; margin: 0; }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════════════════════
     PANNEAU DE CONTRÔLE
══════════════════════════════════════════════════════════ -->
<div id="control-panel">
    <div class="cp-title">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Étiquettes NIIMBOT
        <span class="label-count-badge" id="badge-count">0 étiquette(s)</span>
    </div>

    <div class="cp-separator"></div>

    <div class="cp-group">
        <label>Format</label>
        <select id="fmt-select" onchange="applyFormat(this.value)">
            <optgroup label="── NIIMBOT B1 / B21 ──">
                <option value="50x30" selected>50×30 mm (standard B1/B21)</option>
                <option value="40x30">40×30 mm (petit B1/B21)</option>
                <option value="60x40">60×40 mm (grande étiquette)</option>
                <option value="80x50">80×50 mm (étiquette prix)</option>
            </optgroup>
            <optgroup label="── NIIMBOT D11 / D110 ──">
                <option value="40x12">40×12 mm (D11 roll)</option>
                <option value="50x15">50×15 mm (D11 large roll)</option>
            </optgroup>
            <optgroup label="── Autre ──">
                <option value="57x32">57×32 mm (générique)</option>
                <option value="a4">A4 (grille)</option>
            </optgroup>
        </select>
    </div>

    <div class="cp-group">
        <label>Qté / produit</label>
        <input type="number" id="qty-input" value="{{ $quantity ?? 1 }}" min="1" max="999" style="width:64px" onchange="rebuildLabels()">
    </div>

    <div class="cp-separator"></div>

    <label class="cp-check"><input type="checkbox" id="chk-name" checked onchange="rebuildLabels()"> Nom</label>
    <label class="cp-check"><input type="checkbox" id="chk-price" checked onchange="rebuildLabels()"> Prix</label>
    <label class="cp-check"><input type="checkbox" id="chk-sku" onchange="rebuildLabels()"> SKU</label>
    <label class="cp-check"><input type="checkbox" id="chk-barnum" checked onchange="rebuildLabels()"> N° code-barres</label>

    <div class="cp-separator"></div>

    <button id="btn-bt" class="btn-bt" onclick="printBluetooth()" title="Envoyer directement au NIIMBOT via Bluetooth">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l8 5-8 5V7z M16 12h.01"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.343 6.343A8 8 0 1117.657 17.657 8 8 0 016.343 6.343z"/></svg>
        Envoyer NIIMBOT
    </button>
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Fallback
    </button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<!-- Toast statut Bluetooth -->
<div id="bt-toast" style="display:none">
    <span class="toast-icon" id="toast-icon">🔵</span>
    <div class="toast-body">
        <div class="toast-text" id="toast-text">Connexion...</div>
        <div class="toast-bar-wrap" id="toast-bar-wrap" style="display:none">
            <div class="toast-bar" id="toast-bar"></div>
        </div>
    </div>
    <button class="toast-close" onclick="hideToast()">✕</button>
</div>

<!-- ════════════════════════════════════════════════════════
     ZONE D'ÉTIQUETTES
══════════════════════════════════════════════════════════ -->
<div id="preview-zone"></div>

<!-- ════════════════════════════════════════════════════════
     DONNÉES PRODUITS (JSON injecté côté serveur)
══════════════════════════════════════════════════════════ -->
@php
$productsJson = $products->map(function ($p) {
    return [
        'id'          => $p->id,
        'name'        => $p->name,
        'sku'         => $p->sku ?? '',
        'barcode'     => $p->barcode ?? '',
        'barcode_svg' => $p->barcode_svg ?? '',
        'price'       => $p->sale_price,
        'price_fmt'   => number_format($p->sale_price, 0, ',', ' ') . ' F',
    ];
});
@endphp
<script>
const PRODUCTS = @json($productsJson);
const INIT_FORMAT   = '{{ $labelFormat ?? "50x30" }}';
const INIT_QTY      = {{ $quantity ?? 1 }};
</script>

<script>
/* ─── Config formats ──────────────────────────────────────────────────── */
const FORMATS = {
    '50x30': { w:'50mm', h:'30mm', bh:'11mm', fn:'7.5pt', fp:'10pt', fs:'6.5pt', d11: false },
    '40x30': { w:'40mm', h:'30mm', bh:'10mm', fn:'6.5pt', fp:'9pt',  fs:'6pt',   d11: false },
    '60x40': { w:'60mm', h:'40mm', bh:'16mm', fn:'8.5pt', fp:'12pt', fs:'7pt',   d11: false },
    '80x50': { w:'80mm', h:'50mm', bh:'22mm', fn:'9pt',   fp:'14pt', fs:'7.5pt', d11: false },
    '40x12': { w:'40mm', h:'12mm', bh:'7mm',  fn:'6pt',   fp:'7pt',  fs:'5pt',   d11: true  },
    '50x15': { w:'50mm', h:'15mm', bh:'8mm',  fn:'6pt',   fp:'8pt',  fs:'5pt',   d11: true  },
    '57x32': { w:'57mm', h:'32mm', bh:'13mm', fn:'8pt',   fp:'10pt', fs:'7pt',   d11: false },
    'a4':    { w:'50mm', h:'30mm', bh:'11mm', fn:'7.5pt', fp:'10pt', fs:'6.5pt', d11: false, a4: true },
};

/* ─── Helpers ────────────────────────────────────────────────────────── */
function fmt(key) { return FORMATS[key] || FORMATS['50x30']; }

function applyFormat(key) {
    const f = fmt(key);
    const root = document.documentElement;
    root.style.setProperty('--lw',         f.w);
    root.style.setProperty('--lh',         f.h);
    root.style.setProperty('--barcode-h',  f.bh);
    root.style.setProperty('--font-name',  f.fn);
    root.style.setProperty('--font-price', f.fp);
    root.style.setProperty('--font-sku',   f.fs);

    // @page dynamique
    document.getElementById('page-style').textContent =
        `@page { size: ${f.w} ${f.h}; margin: 0; }`;

    rebuildLabels();
}

function getOpts() {
    return {
        qty:     parseInt(document.getElementById('qty-input').value) || 1,
        name:    document.getElementById('chk-name').checked,
        price:   document.getElementById('chk-price').checked,
        sku:     document.getElementById('chk-sku').checked,
        barnum:  document.getElementById('chk-barnum').checked,
        d11:     fmt(document.getElementById('fmt-select').value).d11,
    };
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildLabel(product, opts) {
    const nameHtml  = opts.name  ? `<div class="label-name">${escHtml(product.name)}</div>` : '';
    const skuHtml   = opts.sku   ? `<div class="label-sku">${escHtml(product.sku)}</div>` : '';
    const priceHtml = opts.price ? `<div class="label-price">${escHtml(product.price_fmt)}</div>` : '';
    const barNumHtml= opts.barnum && product.barcode ? `<div class="barcode-num">${escHtml(product.barcode)}</div>` : '';
    const barcodeHtml = product.barcode_svg
        ? `<div class="barcode-wrap"><img src="${product.barcode_svg}" alt="${escHtml(product.barcode)}"></div>`
        : `<div class="barcode-wrap" style="font-size:7pt;color:#94a3b8">Pas de code-barres</div>`;

    if (opts.d11) {
        return `<div class="label fmt-d11">
            ${barcodeHtml}
            <div class="label-info">
                ${nameHtml}
                ${skuHtml}
                ${priceHtml}
                ${barNumHtml}
            </div>
        </div>`;
    }

    return `<div class="label">
        <div class="label-header">${nameHtml}${skuHtml}</div>
        ${barcodeHtml}
        <div class="label-footer">${barNumHtml}${priceHtml}</div>
    </div>`;
}

function rebuildLabels() {
    const opts  = getOpts();
    const zone  = document.getElementById('preview-zone');
    let html    = '';
    let total   = 0;

    PRODUCTS.forEach(product => {
        for (let i = 0; i < opts.qty; i++) {
            html += buildLabel(product, opts);
            total++;
        }
    });

    zone.innerHTML = html;
    document.getElementById('badge-count').textContent = total + ' étiquette' + (total > 1 ? 's' : '');
}

/* ─── Init ──────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fmt-select').value = INIT_FORMAT;
    document.getElementById('qty-input').value  = INIT_QTY;
    applyFormat(INIT_FORMAT);

    // Masquer le bouton BT si Web Bluetooth non disponible
    if (!navigator.bluetooth) {
        const btn = document.getElementById('btn-bt');
        btn.title = 'Web Bluetooth non disponible (Chrome/Edge requis)';
        btn.style.opacity = '0.35';
        btn.style.cursor  = 'not-allowed';
        btn.onclick = () => showToast('error', '⚠️', 'Web Bluetooth non disponible sur ce navigateur. Utilise Chrome ou Edge.');
    }
});
</script>

<!-- ════════════════════════════════════════════════════════
     WEB BLUETOOTH — NIIMBOT ENGINE
══════════════════════════════════════════════════════════ -->
<script>
/* ─── Toast helpers ─────────────────────────────────────────────────── */
let _toastTimer = null;
function showToast(type, icon, text, progress = false) {
    const toast = document.getElementById('bt-toast');
    toast.className = 'bt-toast--' + type;
    document.getElementById('toast-icon').textContent = icon;
    document.getElementById('toast-text').textContent = text;
    document.getElementById('toast-bar-wrap').style.display = progress ? 'block' : 'none';
    document.getElementById('toast-bar').style.width = '0%';
    toast.style.display = 'flex';
    clearTimeout(_toastTimer);
    if (!progress) _toastTimer = setTimeout(hideToast, 4000);
}
function setToastProgress(pct) {
    document.getElementById('toast-bar').style.width = pct + '%';
}
function hideToast() {
    document.getElementById('bt-toast').style.display = 'none';
}

/* ─── niimblue protocol constants ──────────────────────────────────── */
// Tous les profils de service connus (varie selon modèle/firmware)
const NB_PROFILES = [
    // B21 / B3S / B203
    {
        service: '0000ff00-0000-1000-8000-00805f9b34fb',
        write:   '0000ff02-0000-1000-8000-00805f9b34fb',
        notify:  '0000ff01-0000-1000-8000-00805f9b34fb',
    },
    // D11 / D110 (Nordic UART-like)
    {
        service: 'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        write:   'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f',
        notify:  'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f',
    },
    // Variante ancienne firmware
    {
        service: '0000ae30-0000-1000-8000-00805f9b34fb',
        write:   '0000ae01-0000-1000-8000-00805f9b34fb',
        notify:  '0000ae02-0000-1000-8000-00805f9b34fb',
    },
];
// Remplis dynamiquement après découverte
let NB_SERVICE = null, NB_CHAR_WRITE = null, NB_CHAR_NOTIFY = null;

/* Commandes protocole NIIMBOT (reverse-engineered) */
const CMD = {
    GET_INFO:          0x40,
    SET_LABEL_TYPE:    0x23,
    SET_LABEL_DENSITY: 0x21,
    SET_DIMENSIONS:    0x13,
    START_PRINT:       0x01,
    START_PAGE_PRINT:  0x03,
    END_PAGE_PRINT:    0xe3,
    END_PRINT:         0xf3,
    SEND_LINE:         0x85,
};

/* ─── Bluetooth state ───────────────────────────────────────────────── */
let _btDevice    = null;
let _btChar      = null;
let _notifyChar  = null;
let _responseMap = {};

/* ─── Packet builder ─────────────────────────────────────────────────
   Format NIIMBOT : 55 55 | CMD | LEN | DATA... | CHECKSUM | AA AA
   Checksum = CMD ^ LEN ^ DATA[0] ^ DATA[1] ^ ...
──────────────────────────────────────────────────────────────────── */
function buildPacket(cmd, data = []) {
    const len = data.length;
    let checksum = cmd ^ len;
    for (const b of data) checksum ^= b;
    return new Uint8Array([0x55, 0x55, cmd, len, ...data, checksum, 0xAA, 0xAA]);
}

function handleNotification(event) {
    const raw = new Uint8Array(event.target.value.buffer);
    if (raw.length < 6) return;
    if (raw[0] !== 0x55 || raw[1] !== 0x55) return;
    // Format réponse : 55 55 CMD LEN DATA... CS AA AA
    const cmd  = raw[2];
    const data = raw.slice(4, raw.length - 3);
    if (_responseMap[cmd]) {
        const fn = _responseMap[cmd];
        delete _responseMap[cmd];
        fn(data);
    }
}

async function sendCmd(cmd, data = [], waitAck = false, timeoutMs = 4000) {
    const pkt = buildPacket(cmd, data);

    if (waitAck) {
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => {
                delete _responseMap[cmd];
                reject(new Error('Timeout ACK cmd 0x' + cmd.toString(16)));
            }, timeoutMs);
            _responseMap[cmd] = (resp) => { clearTimeout(timer); resolve(resp); };
            const doWrite = _btChar.properties.writeWithoutResponse
                ? _btChar.writeValueWithoutResponse(pkt)
                : _btChar.writeValue(pkt);
            doWrite.catch(reject);
        });
    }

    if (_btChar.properties.writeWithoutResponse) {
        await _btChar.writeValueWithoutResponse(pkt);
    } else {
        await _btChar.writeValue(pkt);
    }
}

/* ─── Connexion BT ──────────────────────────────────────────────────── */
async function selectDevice() {
    showToast('printing', '🔵', 'Sélectionne ton NIIMBOT dans la liste...');
    const device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: NB_PROFILES.map(p => p.service),
    });
    _btDevice = device;
    device.addEventListener('gattserverdisconnected', () => { _btGattConnected = false; });
    return device;
}

let _btGattConnected = false;

async function connectGatt() {
    showToast('printing', '🔵', 'Connexion à ' + (_btDevice.name || 'NIIMBOT') + '...');
    const server = await _btDevice.gatt.connect();

    // Découverte automatique du bon profil de service
    let foundProfile = null;
    for (const profile of NB_PROFILES) {
        try {
            await server.getPrimaryService(profile.service);
            foundProfile = profile;
            break;
        } catch (_) { /* ce profil n'existe pas sur ce device */ }
    }

    if (!foundProfile) {
        // Dernier recours : lister tous les services disponibles
        const services = await server.getPrimaryServices();
        const uuids = services.map(s => s.uuid).join(', ');
        throw new Error('Service NIIMBOT introuvable. UUIDs détectés : ' + uuids);
    }

    const service = await server.getPrimaryService(foundProfile.service);
    _btChar     = await service.getCharacteristic(foundProfile.write);
    _notifyChar = await service.getCharacteristic(foundProfile.notify);

    await _notifyChar.startNotifications();
    _notifyChar.addEventListener('characteristicvaluechanged', handleNotification);
    _btGattConnected = true;

    showToast('success', '✅', 'NIIMBOT prêt : ' + (_btDevice.name || 'Imprimante'));
}

async function ensureConnected() {
    if (!_btDevice) await selectDevice();
    if (!_btGattConnected || !_btDevice.gatt.connected) await connectGatt();
}

/* ─── Conversion label en bitmap 1bit ───────────────────────────────
   On rend chaque étiquette dans un <canvas> hors-écran,
   puis on extrait les données pixel par pixel → bitmap 1 bit.
──────────────────────────────────────────────────────────────────── */
async function labelToCanvas(labelEl, targetW, targetH) {
    if (window.html2canvas) {
        // Taille naturelle de l'élément à l'écran (px)
        const naturalW = labelEl.offsetWidth  || targetW;
        const naturalH = labelEl.offsetHeight || targetH;
        // Facteur d'échelle pour atteindre la résolution cible (203 dpi)
        const scale = Math.max(targetW / naturalW, targetH / naturalH);

        const raw = await html2canvas(labelEl, {
            scale,
            useCORS:         true,
            allowTaint:      true,
            backgroundColor: '#ffffff',
            logging:         false,
        });

        // Recadrer au format exact si légèrement différent
        if (raw.width === targetW && raw.height === targetH) return raw;
        const out = document.createElement('canvas');
        out.width  = targetW;
        out.height = targetH;
        out.getContext('2d').drawImage(raw, 0, 0, targetW, targetH);
        return out;
    }

    // Fallback : SVG foreignObject → canvas
    return new Promise((resolve) => {
        const ns  = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('width',  targetW);
        svg.setAttribute('height', targetH);
        const fo = document.createElementNS(ns, 'foreignObject');
        fo.setAttribute('width',  targetW);
        fo.setAttribute('height', targetH);
        fo.appendChild(labelEl.cloneNode(true));
        svg.appendChild(fo);
        const xml = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = targetW; c.height = targetH;
            c.getContext('2d').drawImage(img, 0, 0);
            resolve(c);
        };
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
    });
}

function canvasToBitmap1bit(canvas) {
    const ctx    = canvas.getContext('2d');
    const w      = canvas.width;
    const h      = canvas.height;
    const pixels = ctx.getImageData(0, 0, w, h).data;
    // 1 bit par pixel, MSB first, lignes de w bits
    const rowBytes = Math.ceil(w / 8);
    const bitmap   = new Uint8Array(rowBytes * h);
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            const i = (y * w + x) * 4;
            const lum = 0.299 * pixels[i] + 0.587 * pixels[i+1] + 0.114 * pixels[i+2];
            if (lum < 128) { // pixel noir → bit 1
                bitmap[y * rowBytes + Math.floor(x / 8)] |= (0x80 >> (x % 8));
            }
        }
    }
    return { bitmap, rowBytes, w, h };
}

/* ─── Envoi d'une étiquette ─────────────────────────────────────────── */
async function printOneLabelBT(labelEl, fmtKey) {
    const DOTS_PER_MM = 8; // 203 dpi ≈ 8 dots/mm
    const f    = FORMATS[fmtKey] || FORMATS['50x30'];
    const wMm  = parseInt(f.w);
    const hMm  = parseInt(f.h);
    const wPx  = wMm * DOTS_PER_MM;
    const hPx  = hMm * DOTS_PER_MM;

    const canvas = await labelToCanvas(labelEl, wPx, hPx);
    const { bitmap, rowBytes, w, h } = canvasToBitmap1bit(canvas);

    await sendCmd(CMD.START_PAGE_PRINT, [0x01], true);

    for (let y = 0; y < h; y++) {
        const line = Array.from(bitmap.slice(y * rowBytes, (y + 1) * rowBytes));
        await sendCmd(CMD.SEND_LINE, [
            (y >> 8) & 0xFF, y & 0xFF,
            0x01,
            ...line,
        ], true);   // attend ACK pour chaque ligne
    }

    await sendCmd(CMD.END_PAGE_PRINT, [0x01], true);
    await new Promise(r => setTimeout(r, 150));
}

/* ─── Entrée principale ─────────────────────────────────────────────── */
async function printBluetooth() {
    if (!navigator.bluetooth) {
        showToast('error', '⚠️', 'Web Bluetooth non disponible. Utilise Chrome ou Edge.');
        return;
    }

    const btnBt = document.getElementById('btn-bt');

    try {
        btnBt.disabled = true;

        // Connexion si nécessaire (picker uniquement la 1ère fois)
        await ensureConnected();

        const labels  = [...document.querySelectorAll('.label')];
        const fmtKey  = document.getElementById('fmt-select').value;
        const total   = labels.length;

        if (total === 0) {
            showToast('error', '⚠️', 'Aucune étiquette à imprimer.');
            return;
        }

        const f      = FORMATS[fmtKey] || FORMATS['50x30'];
        const wDots  = parseInt(f.w) * 8;
        const hDots  = parseInt(f.h) * 8;

        showToast('printing', '🖨️', `Préparation impression…`, true);

        // Séquence d'initialisation B1 — chaque commande attend son ACK
        await sendCmd(CMD.GET_INFO,          [],      true);
        await sendCmd(CMD.SET_LABEL_TYPE,    [0x01],  true);
        await sendCmd(CMD.SET_LABEL_DENSITY, [0x03],  true);
        await sendCmd(CMD.SET_DIMENSIONS, [
            (wDots >> 8) & 0xFF, wDots & 0xFF,
            (hDots >> 8) & 0xFF, hDots & 0xFF,
            0x00, total & 0xFF,
        ], true);
        await sendCmd(CMD.START_PRINT, [0x01], true);
        await new Promise(r => setTimeout(r, 100));

        // Debug : affiche le premier canvas rendu avant envoi
        const debugCanvas = await labelToCanvas(labels[0], wDots, hDots);
        debugCanvas.style.cssText = 'position:fixed;bottom:80px;right:16px;border:2px solid #6366f1;border-radius:8px;z-index:9998;max-width:200px;background:#fff;';
        debugCanvas.title = 'Aperçu bitmap envoyé au NIIMBOT';
        document.body.appendChild(debugCanvas);
        setTimeout(() => debugCanvas.remove(), 8000);

        for (let i = 0; i < labels.length; i++) {
            showToast('printing', '🖨️', `Impression ${i + 1} / ${total}…`, true);
            setToastProgress(Math.round(((i + 1) / total) * 90));
            await printOneLabelBT(labels[i], fmtKey);
        }

        await sendCmd(CMD.END_PRINT, [0x01], true);
        setToastProgress(100);
        showToast('success', '✅', `${total} étiquette${total > 1 ? 's' : ''} envoyée${total > 1 ? 's' : ''} !`);

    } catch (err) {
        console.error('[NIIMBOT BT]', err);
        if (err.name === 'NotFoundError' || err.message?.includes('cancelled')) {
            showToast('error', '❌', 'Sélection annulée.');
        } else if (err.message?.includes('GATT')) {
            _btChar = null;
            showToast('error', '🔌', 'Connexion perdue. Réessaie.');
        } else {
            showToast('error', '❌', 'Erreur : ' + err.message);
        }
    } finally {
        btnBt.disabled = false;
    }
}
</script>

<!-- html2canvas pour rendu fidèle des étiquettes (CDN, ~250 ko) -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

</body>
</html>
