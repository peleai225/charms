<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    products:        Object,
    totalProducts:   Number,
    withBarcode:     Number,
    withoutBarcode:  Number,
    totalVariants:   Number,
    filters:         Object,
})

// ── Recherche ─────────────────────────────────────────────────────────────────
const search = ref(props.filters?.search ?? '')
let debounce = null
function doSearch() {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('admin.barcodes.index'), { search: search.value }, {
            preserveState: true, replace: true,
        })
    }, 350)
}

// ── Sélection ─────────────────────────────────────────────────────────────────
const selected      = ref([])
const printFormat   = ref('50x30')
const printQty      = ref(1)

const productIds    = computed(() => props.products?.data?.map(p => p.id) ?? [])

function toggleAll(e) {
    selected.value = e.target.checked ? [...productIds.value.map(String)] : []
}

// ── Modal barcode/QR ──────────────────────────────────────────────────────────
const modal = ref({ open: false, title: '', loading: false, content: '', code: '' })

async function showBarcode(productId, name) {
    modal.value = { open: true, title: 'Code-barres — ' + name, loading: true, content: '', code: '' }
    try {
        const res  = await fetch(`/admin/barcodes/${productId}/generate`)
        const data = await res.json()
        modal.value.content = `<img src="${data.barcode_svg}" class="mx-auto" style="height:70px">`
        modal.value.code    = data.barcode
    } catch {
        modal.value.content = '<p class="text-red-500 text-sm">Erreur lors de la génération</p>'
    } finally {
        modal.value.loading = false
    }
}

async function showQrCode(productId, name) {
    modal.value = { open: true, title: 'QR Code — ' + name, loading: true, content: '', code: '' }
    try {
        const res  = await fetch(`/admin/barcodes/${productId}/qrcode`)
        const data = await res.json()
        if (data.success) {
            modal.value.content = `<img src="${data.qr_code}" class="w-40 h-40 mx-auto"><p class="text-xs text-gray-400 mt-2 break-all">${data.qr_url}</p>`
            modal.value.code    = data.product.sku ?? ''
        }
    } catch {
        modal.value.content = '<p class="text-red-500 text-sm">Erreur lors de la génération</p>'
    } finally {
        modal.value.loading = false
    }
}

function showVariantBarcode(barcode, name) {
    modal.value = { open: true, title: name, loading: false, code: barcode, content: `<p class="font-mono text-lg text-gray-800">${barcode}</p>` }
}

function closeModal() { modal.value.open = false }

// ── Impression ────────────────────────────────────────────────────────────────
function printOne(productId) {
    const url = route('admin.barcodes.print-labels') + `?products=${productId}&format=${encodeURIComponent(printFormat.value)}&quantity=${printQty.value}`
    window.open(url, '_blank')
}

function printSelected() {
    const ids = selected.value.filter(v => !String(v).startsWith('v:'))
    if (!ids.length) { alert('Sélectionnez au moins un produit.'); return }
    const url = route('admin.barcodes.print-labels') + `?products=${ids.join(',')}&format=${encodeURIComponent(printFormat.value)}&quantity=${printQty.value}`
    window.open(url, '_blank')
}

// ── Génération en masse ───────────────────────────────────────────────────────
function bulkGenerate() {
    const ids = selected.value.filter(v => !String(v).startsWith('v:')).map(Number)
    if (!ids.length) return
    router.post(route('admin.barcodes.bulk-generate'), { product_ids: ids })
}

// ── Scanner ───────────────────────────────────────────────────────────────────
const scannerOpen   = ref(false)
const scanCode      = ref('')
const scanResult    = ref(null)
const scanInputRef  = ref(null)

function openScanner() {
    scannerOpen.value = true
    setTimeout(() => scanInputRef.value?.focus(), 50)
}

async function doScan() {
    const code = scanCode.value.trim()
    if (!code) return
    try {
        const res = await fetch('/admin/barcodes/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({ code }),
        })
        scanResult.value = await res.json()
    } catch {
        scanResult.value = { found: false }
    }
    scanCode.value = ''
}

// ── Pagination ────────────────────────────────────────────────────────────────
function goPage(url) {
    if (url) router.visit(url, { preserveState: true })
}
</script>

<template>
<div class="p-6 space-y-5">

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
            v-for="kpi in [
                { label: 'Produits total',    value: totalProducts,  bg: 'bg-orange-50 text-orange-600' },
                { label: 'Avec code-barres',  value: withBarcode,    bg: 'bg-green-50 text-green-600'  },
                { label: 'Sans code-barres',  value: withoutBarcode, bg: 'bg-red-50 text-red-600'      },
                { label: 'Variantes total',   value: totalVariants,  bg: 'bg-blue-50 text-blue-600'    },
            ]"
            :key="kpi.label"
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3"
        >
            <div :class="kpi.bg" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-black text-gray-900">{{ Number(kpi.value).toLocaleString('fr-FR') }}</p>
                <p class="text-[11px] text-gray-400 font-medium">{{ kpi.label }}</p>
            </div>
        </div>
    </div>

    <!-- Barre d'outils -->
    <div class="flex flex-wrap items-center gap-3">
        <!-- Recherche -->
        <div class="flex items-center gap-2 flex-1 min-w-[280px]">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="search"
                    @input="doSearch"
                    type="search"
                    placeholder="Nom, SKU, code-barres…"
                    class="w-full pl-9 pr-4 h-9 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>

        <!-- Générer manquants -->
        <button
            @click="router.post(route('admin.barcodes.bulk-generate'), { product_ids: [] })"
            class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition inline-flex items-center gap-2"
        >
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Générer les manquants
        </button>

        <!-- Scanner -->
        <button
            @click="openScanner"
            class="h-9 px-4 bg-violet-600 text-white text-[13px] font-semibold rounded-lg hover:bg-violet-700 transition inline-flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            Scanner
        </button>

        <!-- Format + qté + imprimer sélection -->
        <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 h-9 bg-white">
            <select v-model="printFormat" class="text-[12px] text-gray-700 bg-transparent border-0 focus:ring-0 pr-1">
                <option value="50x30">50×30 mm (B21)</option>
                <option value="40x30">40×30 mm</option>
                <option value="60x40">60×40 mm</option>
                <option value="80x50">80×50 mm</option>
                <option value="40x12">40×12 mm (D11)</option>
                <option value="57x32">57×32 mm</option>
            </select>
            <span class="text-gray-300 text-xs">×</span>
            <input type="number" v-model.number="printQty" min="1" max="99" class="w-10 text-[12px] text-center border-0 focus:ring-0 p-0">
            <button @click="printSelected" class="h-6 px-2.5 bg-green-600 text-white text-[11px] font-semibold rounded-md hover:bg-green-700 transition">
                Imprimer
            </button>
        </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Header sélection -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    id="select-all"
                    @change="toggleAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
                >
                <label for="select-all" class="text-[12px] text-gray-500 cursor-pointer">Tout sélectionner</label>
                <span v-if="selected.length > 0" class="text-[12px] font-semibold text-blue-600">
                    {{ selected.length }} sélectionné(s)
                </span>
            </div>
            <div v-if="selected.length > 0" class="flex items-center gap-2">
                <button
                    @click="bulkGenerate"
                    class="h-7 px-3 text-[12px] font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition"
                >
                    Générer barcodes
                </button>
                <button
                    @click="printSelected"
                    class="h-7 px-3 text-[12px] font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition"
                >
                    Imprimer étiquettes
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Code-barres</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <!-- Empty state -->
                    <template v-if="!products?.data?.length">
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-gray-400 text-[13px]">Aucun produit trouvé</p>
                                <p v-if="filters?.search" class="text-[12px] text-gray-400 mt-1">
                                    Essayez un autre terme de recherche.
                                </p>
                            </td>
                        </tr>
                    </template>

                    <template v-for="product in products?.data" :key="product.id">
                        <!-- Ligne produit -->
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :value="String(product.id)"
                                    v-model="selected"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img
                                        v-if="product.primary_image_url"
                                        :src="product.primary_image_url"
                                        class="w-9 h-9 rounded-lg object-cover border border-gray-100 flex-shrink-0"
                                    >
                                    <div v-else class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate max-w-[200px]">{{ product.name }}</p>
                                        <p v-if="product.variants_count > 0" class="text-[11px] text-gray-400">{{ product.variants_count }} variante(s)</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-[12px] text-gray-500">{{ product.sku ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span v-if="product.barcode" class="font-mono text-[12px] text-gray-800">{{ product.barcode }}</span>
                                <span v-else class="inline-flex items-center gap-1 text-[11px] text-red-500 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/>
                                    </svg>
                                    Non généré
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums">{{ product.sale_price_formatted }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="showBarcode(product.id, product.name)"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Voir code-barres"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="showQrCode(product.id, product.name)"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition"
                                        title="Voir QR Code"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="printOne(product.id)"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                        title="Imprimer étiquette"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Variantes (sous-lignes) -->
                        <tr
                            v-for="variant in product.variants"
                            :key="`v-${variant.id}`"
                            class="bg-gray-50/40 group/v"
                        >
                            <td class="px-4 py-2 pl-10">
                                <input
                                    type="checkbox"
                                    :value="`v:${variant.id}`"
                                    v-model="selected"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 scale-90"
                                >
                            </td>
                            <td class="px-4 py-2 pl-10">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-gray-400">└</span>
                                    <span
                                        v-if="variant.color_code"
                                        class="w-3.5 h-3.5 rounded-full border border-gray-200 flex-shrink-0"
                                        :style="{ background: variant.color_code }"
                                    ></span>
                                    <span class="text-[12px] text-gray-600">{{ variant.label }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 font-mono text-[11px] text-gray-400">{{ variant.sku ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span v-if="variant.barcode" class="font-mono text-[11px] text-gray-600">{{ variant.barcode }}</span>
                                <span v-else class="text-[11px] text-gray-400 italic">—</span>
                            </td>
                            <td class="px-4 py-2 text-[12px] text-gray-500 tabular-nums">{{ variant.sale_price_formatted ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover/v:opacity-100 transition-opacity">
                                    <button
                                        v-if="variant.barcode"
                                        @click="showVariantBarcode(variant.barcode, variant.label)"
                                        class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                        title="Voir code-barres"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="products?.links?.length > 3" class="px-5 py-4 border-t border-gray-100 flex items-center gap-1">
            <template v-for="link in products.links" :key="link.label">
                <button
                    @click="goPage(link.url)"
                    :disabled="!link.url"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1.5 text-[12px] rounded-lg transition',
                        link.active ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100 disabled:opacity-40',
                    ]"
                ></button>
            </template>
        </div>
    </div>

    <!-- Modal code-barres / QR -->
    <Teleport to="body">
        <div
            v-if="modal.open"
            class="fixed inset-0 z-[9990] flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <p class="text-[14px] font-bold text-gray-900">{{ modal.title }}</p>
                    <button @click="closeModal" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 text-center">
                    <div v-if="modal.loading" class="py-8 flex justify-center">
                        <svg class="w-8 h-8 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    <div v-else>
                        <div v-html="modal.content" class="mb-3"></div>
                        <p class="font-mono text-[13px] text-gray-600 mt-2">{{ modal.code }}</p>
                    </div>
                </div>
                <div class="flex gap-2 px-5 pb-5">
                    <button @click="window.print()" class="flex-1 h-9 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition">
                        Imprimer
                    </button>
                    <button @click="closeModal" class="h-9 px-4 border border-gray-200 text-[13px] text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Modal Scanner -->
    <Teleport to="body">
        <div
            v-if="scannerOpen"
            class="fixed inset-0 z-[9990] flex items-center justify-center"
            @keydown.esc="scannerOpen = false"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="scannerOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <p class="text-[14px] font-bold text-gray-900">Scanner un code</p>
                    <button @click="scannerOpen = false" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <input
                        ref="scanInputRef"
                        v-model="scanCode"
                        @keydown.enter.prevent="doScan"
                        type="text"
                        placeholder="Scannez ou tapez le code…"
                        class="w-full px-4 py-3 text-[14px] font-mono border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500"
                    >

                    <div
                        v-if="scanResult"
                        :class="scanResult.found ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                        class="p-4 rounded-xl border"
                    >
                        <template v-if="scanResult.found">
                            <p class="text-[13px] font-bold text-green-700 mb-2">Produit trouvé</p>
                            <div class="space-y-1">
                                <p class="text-[13px] font-semibold text-gray-900">{{ scanResult.data?.name }}</p>
                                <p class="text-[12px] text-gray-500">SKU : <span class="font-mono">{{ scanResult.data?.sku ?? 'N/A' }}</span></p>
                                <p class="text-[12px] text-gray-500">Stock : <span class="font-semibold text-gray-700">{{ scanResult.data?.stock }} pcs</span></p>
                                <p class="text-[13px] font-bold text-blue-600">{{ Number(scanResult.data?.price ?? 0).toLocaleString('fr-FR') }} F CFA</p>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-[13px] font-medium text-red-600">Aucun produit trouvé pour ce code.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

</div>
</template>
