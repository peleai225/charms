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
                width: var(--lw);
                height: var(--lh);
                overflow: hidden;
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
        @page { size: 50mm 30mm landscape; margin: 0; }
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

    <div class="cp-group">
        <label>Modèle</label>
        <select id="model-select" title="Choisir le modèle exact de votre NIIMBOT">
            <option value="B1">B1 / B1 Pro / M2-H</option>
            <option value="B21_V1">B21 / B21 Pro</option>
            <option value="D11_V1">D11 / D11S</option>
            <option value="D110">D110 / D110S</option>
            <option value="D110M_V4">D110M / B3S Pro (300 dpi)</option>
        </select>
    </div>

    <button id="btn-bt" class="btn-bt" onclick="printBluetooth()" title="Envoyer directement au NIIMBOT via Bluetooth">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l8 5-8 5V7z M16 12h.01"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.343 6.343A8 8 0 1117.657 17.657 8 8 0 016.343 6.343z"/></svg>
        Envoyer NIIMBOT
    </button>
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Imprimer (PDF)
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

    // @page dynamique — dimensions seules (pas de mot-clé orientation, invalide avec des valeurs explicites)
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
     WEB BLUETOOTH — NIIMBOT ENGINE (niimbluelib @0.0.1-alpha.39)
══════════════════════════════════════════════════════════ -->

<!-- html2canvas : rendu DOM → canvas bitmap -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<!-- niimbluelib : driver NIIMBOT officiel (reverse-engineered, MIT) -->
<script src="https://unpkg.com/@mmote/niimbluelib@0.0.1-alpha.39/dist/umd/niimbluelib.min.js"></script>

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

/* ─── État client niimbluelib ───────────────────────────────────────── */
let _client = null;

function getClient() {
    if (!_client) {
        _client = new niimbluelib.NiimbotBluetoothClient();

        _client.on('connect', () => {
            showToast('success', '✅', 'NIIMBOT connecté — prêt à imprimer');
        });
        _client.on('disconnect', () => {
            showToast('error', '🔌', 'NIIMBOT déconnecté');
            _client = null;
        });
        _client.on('printprogress', (e) => {
            const pct = Math.round((e.page / e.pageCount) * 80 + (e.pagePrintProgress / 100) * 20);
            setToastProgress(pct);
        });
    }
    return _client;
}

/* ─── Rendu étiquette → canvas bitmap ──────────────────────────────── */
async function labelToCanvas(labelEl, targetW, targetH) {
    const naturalW = labelEl.offsetWidth  || targetW;
    const naturalH = labelEl.offsetHeight || targetH;
    const scale    = Math.max(targetW / naturalW, targetH / naturalH);

    const raw = await html2canvas(labelEl, {
        scale,
        useCORS:         true,
        allowTaint:      true,
        backgroundColor: '#ffffff',
        logging:         false,
    });

    if (raw.width === targetW && raw.height === targetH) return raw;

    const out = document.createElement('canvas');
    out.width  = targetW;
    out.height = targetH;
    out.getContext('2d').drawImage(raw, 0, 0, targetW, targetH);
    return out;
}

/* ─── Entrée principale ─────────────────────────────────────────────── */
async function printBluetooth() {
    if (!navigator.bluetooth) {
        showToast('error', '⚠️', 'Web Bluetooth indisponible — utilise Chrome ou Edge.');
        return;
    }

    const btnBt    = document.getElementById('btn-bt');
    const fmtKey   = document.getElementById('fmt-select').value;
    const taskName = document.getElementById('model-select').value;
    const labels   = [...document.querySelectorAll('.label')];
    const total    = labels.length;

    if (total === 0) {
        showToast('error', '⚠️', 'Aucune étiquette à imprimer.');
        return;
    }

    btnBt.disabled = true;

    try {
        const client = getClient();

        // Connexion (ouvre le picker Bluetooth si pas encore connecté)
        if (!client.isConnected?.()) {
            showToast('printing', '🔵', 'Sélectionne ton NIIMBOT dans la liste...', false);
            await client.connect();
        }

        const f       = FORMATS[fmtKey] || FORMATS['50x30'];
        const DPI     = (taskName === 'D110M_V4') ? 12 : 8; // 300 dpi ≈ 12 dots/mm, 203 dpi ≈ 8
        const wPx     = parseInt(f.w) * DPI;
        const hPx     = parseInt(f.h) * DPI;

        showToast('printing', '🖨️', `Préparation de ${total} étiquette${total > 1 ? 's' : ''}…`, true);

        const printTask = client.abstraction.newPrintTask(taskName, {
            totalPages:          total,
            statusPollIntervalMs: 150,
            statusTimeoutMs:     12_000,
        });

        await printTask.printInit();

        for (let i = 0; i < labels.length; i++) {
            showToast('printing', '🖨️', `Impression ${i + 1} / ${total}…`, true);
            setToastProgress(Math.round((i / total) * 80));

            const canvas  = await labelToCanvas(labels[i], wPx, hPx);
            const encoded = niimbluelib.ImageEncoder.encodeCanvas(canvas, 'left');

            await printTask.printPage(encoded, 1);
            await printTask.waitForPageFinished();
        }

        await printTask.waitForFinished();
        await printTask.printEnd();

        setToastProgress(100);
        showToast('success', '✅', `${total} étiquette${total > 1 ? 's' : ''} envoyée${total > 1 ? 's' : ''} avec succès !`);

    } catch (err) {
        console.error('[NIIMBOT]', err);
        _client = null; // force reconnexion au prochain essai

        if (err.name === 'NotFoundError' || err.message?.includes('cancelled')) {
            showToast('error', '❌', 'Sélection annulée.');
        } else if (err.name === 'NetworkError' || err.message?.includes('GATT')) {
            showToast('error', '🔌', 'Connexion perdue — réessaie.');
        } else {
            showToast('error', '❌', 'Erreur : ' + err.message);
        }
    } finally {
        btnBt.disabled = false;
    }
}

/* ─── Init ──────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (!navigator.bluetooth) {
        const btn = document.getElementById('btn-bt');
        btn.title   = 'Web Bluetooth non disponible (Chrome/Edge requis)';
        btn.style.opacity = '0.35';
        btn.style.cursor  = 'not-allowed';
        btn.onclick = () => showToast('error', '⚠️', 'Web Bluetooth non disponible. Utilise Chrome ou Edge.');
    }
});
</script>

</body>
</html>
