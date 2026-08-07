<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    receiptAutoPrint: Boolean,
})

// ── État global ───────────────────────────────────────────────────────────────
const mode           = ref('cart')
const cart           = ref({ items: [], count: 0, total: 0, total_formatted: '0 F CFA' })
const discount       = ref(0)
const paymentMethod  = ref('cash')
const amountReceived = ref(0)
const quickAmounts   = ref([])
const isProcessing   = ref(false)
const lastOrder      = ref(null)
const showSuccess    = ref(false)
const showCamera     = ref(false)
const error          = ref(null)

// Scan
const lastScanned    = ref(null)
const scanHistory    = ref([])
const scanFlashOk    = ref(false)
const scanFlashErr   = ref(false)
const scanInputRef   = ref(null)

// Recherche
const searchQuery    = ref('')
const searchResults  = ref([])
const searchLoading  = ref(false)

// ── Helpers ────────────────────────────────────────────────────────────────────
function csrfToken() {
    return document.querySelector('meta[name=csrf-token]')?.content ?? ''
}

function formatPrice(amount) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(amount ?? 0)) + ' F CFA'
}

function recalcQuickAmounts() {
    const total = Math.max(0, (cart.value?.total ?? 0) - discount.value)
    if (total <= 0) { quickAmounts.value = []; return }
    const base = Math.ceil(total / 500) * 500
    quickAmounts.value = [base, base + 500, base + 1000, base + 2000]
}

const netTotal = computed(() => Math.max(0, (cart.value?.total ?? 0) - discount.value))

// ── Panier ─────────────────────────────────────────────────────────────────────
async function loadCart() {
    try {
        const r = await fetch('/admin/scanner/cart')
        cart.value = await r.json()
        recalcQuickAmounts()
    } catch (e) { console.error(e) }
}

// ── Scan ───────────────────────────────────────────────────────────────────────
function flash(type) {
    const key = type === 'ok' ? scanFlashOk : scanFlashErr
    key.value = true
    setTimeout(() => key.value = false, 450)
}

async function scanCode(code) {
    if (!code.trim()) return
    error.value = null
    try {
        const r = await fetch('/admin/scanner/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({ code: code.trim() }),
        })
        const data = await r.json()
        if (!data.found) {
            error.value = data.message ?? 'Produit non trouvé'
            flash('err')
            return
        }
        lastScanned.value = data.data
        flash('ok')
        const now = new Date()
        scanHistory.value.unshift({
            time: now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
            name: data.data.name,
            code,
            action: mode.value,
            actionLabel: mode.value === 'cart' ? 'Panier' : mode.value === 'stock_in' ? 'Entrée' : 'Sortie',
        })
        if (scanHistory.value.length > 30) scanHistory.value.pop()

        if (mode.value === 'cart') await addToCart(data.data)
        else await processStockMovement(data.data)
    } catch { error.value = 'Erreur de connexion' }
}

function handleScanEnter(e) {
    const val = (e.target.value ?? '').trim()
    e.target.value = ''
    if (val) scanCode(val)
}

// ── Recherche manuelle ─────────────────────────────────────────────────────────
let searchDebounce = null
async function searchProducts() {
    clearTimeout(searchDebounce)
    const q = searchQuery.value.trim()
    if (q.length < 2) { searchResults.value = []; return }
    searchDebounce = setTimeout(async () => {
        searchLoading.value = true
        try {
            const r = await fetch(`/api/admin/search?q=${encodeURIComponent(q)}`)
            const data = await r.json()
            searchResults.value = (data.results ?? [])
                .filter(i => i.type === 'product')
                .slice(0, 8)
                .map(i => ({
                    name:            i.label,
                    sku:             (i.sublabel?.match(/SKU:\s*([^\s·]+)/) ?? [])[1] ?? '—',
                    price_formatted: (i.sublabel?.match(/([0-9\s]+\s*F)/) ?? [])[1] ?? '—',
                    price:           parseInt(((i.sublabel?.match(/([0-9\s]+)\s*F/) ?? [])[1] ?? '0').replace(/\s/g, '')),
                    image:           null,
                    id:              (i.url?.split('/').filter(Boolean).pop()) ?? null,
                }))
        } catch { /* silently ignore */ }
        finally { searchLoading.value = false }
    }, 350)
}

async function addSearchResultToCart(result) {
    if (!result.id) return
    error.value = null
    try {
        const r = await fetch('/admin/scanner/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({ product_id: result.id, quantity: 1 }),
        })
        const data = await r.json()
        if (data.success) {
            cart.value = data.cart
            recalcQuickAmounts()
            searchQuery.value   = ''
            searchResults.value = []
            flash('ok')
        } else {
            error.value = data.message ?? 'Erreur ajout'
        }
    } catch { error.value = 'Erreur de connexion' }
}

// ── Cart operations ────────────────────────────────────────────────────────────
async function addToCart(product) {
    try {
        const r = await fetch('/admin/scanner/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({
                product_id: product.product_id ?? product.id,
                variant_id: product.variant_id ?? null,
                quantity:   1,
            }),
        })
        const data = await r.json()
        if (data.success) { cart.value = data.cart; recalcQuickAmounts() }
    } catch { /* silent */ }
}

async function updateQuantity(key, quantity) {
    if (quantity <= 0) { await removeItem(key); return }
    try {
        const r = await fetch(`/admin/scanner/cart/${key}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({ quantity }),
        })
        const data = await r.json()
        if (data.success) { cart.value = data.cart; recalcQuickAmounts() }
    } catch { /* silent */ }
}

async function removeItem(key) {
    try {
        const r = await fetch(`/admin/scanner/cart/${key}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
        const data = await r.json()
        if (data.success) { cart.value = data.cart; recalcQuickAmounts() }
    } catch { /* silent */ }
}

async function clearCart() {
    try {
        const r = await fetch('/admin/scanner/cart', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
        const data = await r.json()
        if (data.success) {
            cart.value = data.cart
            discount.value = 0
            amountReceived.value = 0
            quickAmounts.value   = []
        }
    } catch { /* silent */ }
}

// ── Checkout ───────────────────────────────────────────────────────────────────
async function processCheckout() {
    if (!cart.value.items?.length || isProcessing.value) return
    isProcessing.value = true
    error.value        = null
    try {
        const r = await fetch('/admin/scanner/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({
                payment_method:  paymentMethod.value,
                amount_received: paymentMethod.value === 'cash' ? amountReceived.value : netTotal.value,
                discount_amount: discount.value,
            }),
        })
        const data = await r.json()
        if (data.success) {
            lastOrder.value = { ...data.order, change: data.change, change_formatted: data.change_formatted, receipt_url: data.receipt_url }
            cart.value       = { items: [], count: 0, total: 0, total_formatted: '0 F CFA' }
            amountReceived.value = 0
            discount.value       = 0
            quickAmounts.value   = []
            showSuccess.value    = true
            if (props.receiptAutoPrint && data.receipt_url) {
                window.open(data.receipt_url, 'pos_receipt', 'width=440,height=700,toolbar=0,scrollbars=1')
            }
        } else {
            error.value = data.message ?? 'Erreur lors de la validation'
        }
    } catch { error.value = 'Erreur de connexion' }
    finally { isProcessing.value = false }
}

function openReceipt() {
    if (lastOrder.value?.receipt_url) {
        window.open(lastOrder.value.receipt_url, 'pos_receipt', 'width=440,height=700,toolbar=0,scrollbars=1')
    }
}

function newSale() {
    showSuccess.value = false
    lastOrder.value   = null
    setTimeout(() => scanInputRef.value?.focus(), 50)
}

// ── Stock movement ─────────────────────────────────────────────────────────────
async function processStockMovement(product) {
    try {
        const r = await fetch('/admin/scanner/stock-movement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({
                product_id: product.product_id ?? product.id,
                variant_id: product.variant_id ?? null,
                type:       mode.value === 'stock_in' ? 'in' : 'out',
                quantity:   1,
            }),
        })
        const data = await r.json()
        if (data.success && lastScanned.value) lastScanned.value.stock = data.new_stock
    } catch { /* silent */ }
}

// ── Caméra ─────────────────────────────────────────────────────────────────────
let codeReader = null

async function openCamera() {
    showCamera.value = true
    await new Promise(r => setTimeout(r, 50))
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        const video  = document.getElementById('camera-preview')
        video.srcObject = stream
        if (window.ZXing) {
            codeReader = new ZXing.BrowserMultiFormatReader()
            codeReader.decodeFromVideoDevice(null, 'camera-preview', (result) => {
                if (result) { scanCode(result.getText()); closeCamera() }
            })
        }
    } catch { error.value = "Impossible d'accéder à la caméra"; showCamera.value = false }
}

function closeCamera() {
    showCamera.value = false
    const video = document.getElementById('camera-preview')
    if (video?.srcObject) { video.srcObject.getTracks().forEach(t => t.stop()); video.srcObject = null }
    if (codeReader) { codeReader.reset(); codeReader = null }
    setTimeout(() => scanInputRef.value?.focus(), 50)
}

function refocusScan() {
    // Ne pas voler le focus si l'utilisateur tape dans un autre champ (recherche, remise, etc.)
    const tag = document.activeElement?.tagName
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return
    scanInputRef.value?.focus()
}

onMounted(() => {
    loadCart()
    setTimeout(() => scanInputRef.value?.focus(), 100)
    document.addEventListener('click', refocusScan)
})

onUnmounted(() => {
    document.removeEventListener('click', refocusScan)
})
</script>

<template>
<div class="p-4 h-[calc(100vh-112px)] grid grid-cols-1 lg:grid-cols-[1fr_420px] gap-4 overflow-hidden">

    <!-- GAUCHE — Scanner + Recherche + Feedback -->
    <div class="flex flex-col gap-4 min-h-0 overflow-y-auto pr-0.5">

        <!-- Bloc scan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex-shrink-0">
            <p class="text-[12px] text-gray-500 mb-3">Champ actif en permanence — scanneur HID ou saisie manuelle</p>

            <div
                class="rounded-xl overflow-hidden transition-colors duration-300"
                :class="{ 'bg-green-50': scanFlashOk, 'bg-red-50': scanFlashErr }"
            >
                <div class="relative">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <input
                        ref="scanInputRef"
                        type="text"
                        @keydown.enter.prevent="handleScanEnter"
                        placeholder="Scanner un code-barres ou taper un SKU..."
                        autocomplete="off"
                        spellcheck="false"
                        class="w-full pl-12 pr-28 py-4 border-2 border-slate-200 rounded-xl font-mono text-slate-900 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-[1.1rem] tracking-wider"
                    >
                    <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                        <kbd class="hidden sm:block px-1.5 py-0.5 bg-slate-100 rounded text-xs text-slate-400 font-mono">↵</kbd>
                        <button
                            @click="openCamera"
                            class="p-1.5 bg-violet-100 hover:bg-violet-200 text-violet-600 rounded-lg transition"
                            title="Caméra"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modes -->
            <div class="flex gap-2 mt-3">
                <button
                    @click="mode = 'cart'"
                    :class="mode === 'cart' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Panier
                </button>
                <button
                    @click="mode = 'stock_in'"
                    :class="mode === 'stock_in' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Entrée stock
                </button>
                <button
                    @click="mode = 'stock_out'"
                    :class="mode === 'stock_out' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Sortie stock
                </button>
            </div>
        </div>

        <!-- Dernier article scanné -->
        <Transition enter-from-class="opacity-0 translate-y-1" enter-active-class="transition duration-200">
            <div v-if="lastScanned" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                        <img v-if="lastScanned.image" :src="lastScanned.image" class="w-full h-full object-cover" :alt="lastScanned.name">
                        <svg v-else class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ lastScanned.name }}</p>
                        <p v-if="lastScanned.variant_name" class="text-xs text-blue-600 font-medium">{{ lastScanned.variant_name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            SKU: {{ lastScanned.sku ?? '—' }}
                            <span v-if="lastScanned.stock !== undefined"> · Stock: {{ lastScanned.stock }}</span>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-bold text-slate-900">{{ lastScanned.price_formatted }}</p>
                        <span v-if="mode === 'cart'" class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Ajouté</span>
                        <span v-else-if="mode === 'stock_in'" class="inline-block mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">+1 stock</span>
                        <span v-else class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">-1 stock</span>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Erreur -->
        <Transition enter-from-class="opacity-0 -translate-y-1" enter-active-class="transition duration-150">
            <div v-if="error" class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl p-4 flex-shrink-0">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 flex-1">{{ error }}</p>
                <button @click="error = null" class="text-red-400 hover:text-red-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Recherche manuelle -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex-shrink-0">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-[13px] font-semibold text-gray-700">Recherche manuelle</h3>
            </div>
            <div class="relative">
                <input
                    v-model="searchQuery"
                    @input="searchProducts"
                    type="text"
                    placeholder="Nom du produit, SKU, code-barres..."
                    class="w-full pl-4 pr-9 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                >
                <div v-if="searchLoading" class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="animate-spin w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            <div v-if="searchResults.length > 0" class="mt-2 border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-50">
                <button
                    v-for="(result, idx) in searchResults"
                    :key="idx"
                    @click="addSearchResultToCart(result)"
                    class="w-full flex items-center gap-3 px-3 py-2.5 text-left hover:bg-blue-50 transition"
                >
                    <div class="w-9 h-9 rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden">
                        <img v-if="result.image" :src="result.image" class="w-full h-full object-cover">
                        <svg v-else class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ result.name }}</p>
                        <p class="text-xs text-slate-400">SKU: {{ result.sku }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-blue-600">{{ result.price_formatted }}</p>
                    </div>
                    <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
            <p v-if="searchQuery.length >= 2 && !searchLoading && searchResults.length === 0" class="mt-2 text-xs text-center text-slate-400 py-2">
                Aucun produit trouvé
            </p>
        </div>

        <!-- Historique scans -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex-1 min-h-0">
            <h3 class="text-[13px] font-semibold text-gray-700 mb-3">Historique des scans</h3>
            <div class="space-y-1.5 max-h-52 overflow-y-auto">
                <div v-for="(s, i) in scanHistory" :key="i" class="flex items-center gap-2 px-2.5 py-2 bg-slate-50 rounded-lg text-xs">
                    <span class="text-slate-400 font-mono w-11 flex-shrink-0">{{ s.time }}</span>
                    <span class="font-medium text-slate-700 flex-1 truncate">{{ s.name }}</span>
                    <span class="font-mono text-slate-400 hidden sm:block truncate max-w-[80px]">{{ s.code }}</span>
                    <span
                        :class="{
                            'bg-blue-100 text-blue-700': s.action === 'cart',
                            'bg-emerald-100 text-emerald-700': s.action === 'stock_in',
                            'bg-red-100 text-red-700': s.action === 'stock_out',
                        }"
                        class="px-1.5 py-0.5 rounded font-medium flex-shrink-0"
                    >{{ s.actionLabel }}</span>
                </div>
                <p v-if="scanHistory.length === 0" class="text-xs text-slate-400 text-center py-4">Aucun scan pour l'instant</p>
            </div>
        </div>
    </div>

    <!-- DROITE — Panier POS -->
    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 min-h-0 overflow-hidden">

        <!-- Header panier -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="font-bold text-gray-900 text-base">Panier</h2>
                <span v-if="cart.count > 0" class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 bg-blue-600 text-white text-xs font-bold rounded-full">
                    {{ cart.count }}
                </span>
            </div>
            <button
                v-if="cart.items?.length > 0"
                @click="clearCart"
                class="flex items-center gap-1 text-xs text-slate-400 hover:text-red-500 transition px-2 py-1 rounded-lg hover:bg-red-50"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Vider
            </button>
        </div>

        <!-- Articles -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            <!-- Vide -->
            <div v-if="!cart.items?.length" class="flex flex-col items-center justify-center py-20 text-slate-300">
                <svg class="w-14 h-14 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-medium">Panier vide</p>
                <p class="text-xs mt-1">Scannez ou recherchez un article</p>
            </div>

            <!-- Items -->
            <div
                v-for="item in cart.items"
                :key="`${item.product_id}-${item.variant_id ?? 0}`"
                class="flex gap-3 p-3 rounded-xl border border-slate-100 bg-white hover:bg-gray-50/60 transition"
            >
                <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                    <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                    <svg v-else class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate leading-tight">{{ item.name }}</p>
                    <p v-if="item.variant_name" class="text-xs text-blue-600 font-medium leading-tight mt-0.5">{{ item.variant_name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ formatPrice(item.price) }} / u</p>
                </div>
                <div class="flex flex-col items-end justify-between gap-1 flex-shrink-0">
                    <div class="flex items-center gap-1.5">
                        <button
                            @click="updateQuantity(`${item.product_id}-${item.variant_id ?? 0}`, item.quantity - 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 transition"
                        >
                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="w-7 text-center text-sm font-bold text-slate-900">{{ item.quantity }}</span>
                        <button
                            @click="updateQuantity(`${item.product_id}-${item.variant_id ?? 0}`, item.quantity + 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 transition"
                        >
                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm font-bold text-slate-900">{{ formatPrice(item.price * item.quantity) }}</p>
                </div>
                <button
                    @click="removeItem(`${item.product_id}-${item.variant_id ?? 0}`)"
                    class="self-start p-1 text-slate-300 hover:text-red-500 transition rounded-lg hover:bg-red-50 flex-shrink-0"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Footer sticky -->
        <div class="border-t border-gray-100 px-5 py-4 space-y-3 flex-shrink-0">

            <!-- Remise -->
            <div v-if="cart.items?.length" class="flex items-center gap-2">
                <label class="text-xs text-slate-500 whitespace-nowrap flex-shrink-0">Remise (F)</label>
                <input
                    v-model.number="discount"
                    type="number"
                    min="0"
                    :max="cart.total ?? 0"
                    placeholder="0"
                    class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 text-right min-w-0"
                >
                <button v-if="discount > 0" @click="discount = 0" class="text-xs text-slate-400 hover:text-red-500 flex-shrink-0">✕</button>
            </div>

            <!-- Totaux -->
            <div v-if="cart.items?.length" class="space-y-1">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Sous-total</span>
                    <span>{{ cart.total_formatted }}</span>
                </div>
                <div v-if="discount > 0" class="flex justify-between text-sm text-emerald-600 font-medium">
                    <span>Remise</span>
                    <span>– {{ formatPrice(discount) }}</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-100">
                    <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Total</span>
                    <span class="text-2xl font-black text-slate-900">{{ formatPrice(netTotal) }}</span>
                </div>
            </div>

            <!-- Mode paiement -->
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Paiement</p>
                <div class="flex gap-2">
                    <button
                        v-for="m in [{ key: 'cash', label: 'Espèces' }, { key: 'card', label: 'Carte' }, { key: 'mobile_money', label: 'Mobile' }]"
                        :key="m.key"
                        @click="paymentMethod = m.key"
                        :class="paymentMethod === m.key ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 bg-slate-50 text-slate-600 hover:border-blue-300'"
                        class="flex-1 py-2 px-2 rounded-xl border-2 cursor-pointer transition text-center text-[12px] font-semibold"
                    >
                        {{ m.label }}
                    </button>
                </div>
            </div>

            <!-- Montant reçu (espèces) -->
            <div v-if="paymentMethod === 'cash'" class="space-y-2">
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Montant reçu</label>
                <div class="relative">
                    <input
                        v-model.number="amountReceived"
                        type="number"
                        min="0"
                        placeholder="0"
                        class="w-full pl-4 pr-16 py-2.5 border border-slate-200 rounded-xl text-right text-base font-bold focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                    <span class="absolute inset-y-0 right-3 flex items-center text-xs text-slate-400 pointer-events-none">F CFA</span>
                </div>

                <!-- Rendu monnaie -->
                <div
                    v-if="amountReceived > 0"
                    :class="amountReceived >= netTotal ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'"
                    class="flex items-center justify-between px-3 py-2 border rounded-xl text-sm font-bold"
                >
                    <span>{{ amountReceived >= netTotal ? 'Monnaie rendue' : 'Montant insuffisant' }}</span>
                    <span>{{ formatPrice(Math.max(0, amountReceived - netTotal)) }}</span>
                </div>

                <!-- Raccourcis montants -->
                <div v-if="(cart.total ?? 0) > 0" class="flex gap-1.5 flex-wrap">
                    <button
                        v-for="amt in quickAmounts"
                        :key="amt"
                        @click="amountReceived = amt"
                        :class="amountReceived === amt ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                    >{{ formatPrice(amt) }}</button>
                </div>
            </div>

            <!-- CTA Valider -->
            <button
                @click="processCheckout"
                :disabled="!cart.items?.length || isProcessing"
                class="w-full py-4 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 text-white font-bold text-base rounded-xl shadow-lg shadow-blue-200/60 disabled:shadow-none transition disabled:cursor-not-allowed"
            >
                <span v-if="!isProcessing" class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Valider la vente
                </span>
                <span v-else class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Traitement en cours...
                </span>
            </button>
        </div>
    </div>

    <!-- MODAL — Vente validée -->
    <Teleport to="body">
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200">
            <div v-if="showSuccess" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                <Transition enter-from-class="opacity-0 scale-95" enter-active-class="transition duration-200">
                    <div v-if="showSuccess" class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center">
                        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-1">Vente validée !</h3>
                        <p class="text-slate-500 text-sm mb-5">
                            Commande <span class="font-mono font-bold text-slate-700">{{ lastOrder?.order_number }}</span>
                        </p>
                        <div class="bg-slate-50 rounded-2xl px-6 py-4 mb-5 text-left space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-500">Total encaissé</span>
                                <span class="text-2xl font-black text-blue-600">{{ lastOrder?.total_formatted }}</span>
                            </div>
                            <div v-if="lastOrder?.change > 0" class="flex justify-between items-center border-t border-slate-200 pt-2">
                                <span class="text-sm text-slate-500">Monnaie rendue</span>
                                <span class="text-lg font-bold text-emerald-600">{{ lastOrder?.change_formatted }}</span>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button
                                v-if="lastOrder?.receipt_url"
                                @click="openReceipt"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition text-sm"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Imprimer
                            </button>
                            <button
                                @click="newSale"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nouvelle vente
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>

    <!-- MODAL — Caméra -->
    <Teleport to="body">
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200">
            <div v-if="showCamera" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="closeCamera">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-900">Scanner avec la caméra</h3>
                        <button @click="closeCamera" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="rounded-xl overflow-hidden bg-black">
                        <video id="camera-preview" autoplay playsinline class="w-full"></video>
                    </div>
                    <p class="text-xs text-center text-slate-500 mt-3">Placez le code-barres devant la caméra</p>
                </div>
            </div>
        </Transition>
    </Teleport>

</div>
</template>
