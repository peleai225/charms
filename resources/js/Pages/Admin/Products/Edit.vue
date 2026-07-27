<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    product:    Object,
    categories: Array,
    attributes: Array,
})

const form = useForm({
    name:                   props.product.name,
    short_description:      props.product.short_description ?? '',
    description:            props.product.description ?? '',
    sku:                    props.product.sku,
    barcode:                props.product.barcode ?? '',
    purchase_price:         props.product.purchase_price,
    sale_price:             props.product.sale_price,
    compare_price:          props.product.compare_price ?? '',
    tax_rate:               String(props.product.tax_rate ?? '20'),
    stock_quantity:         props.product.stock_quantity,
    stock_alert_threshold:  props.product.stock_alert_threshold,
    weight:                 props.product.weight ?? '',
    category_id:            props.product.category_id ?? '',
    status:                 props.product.status,
    is_featured:            !!props.product.is_featured,
    is_new:                 !!props.product.is_new,
    has_variants:           !!props.product.has_variants,
    track_stock:            props.product.track_stock !== false,
    images:                 [],
})

const newFiles = ref([])
const newFilePreviews = ref([])
const confirmDelete = ref(false)

function storageUrl(path) {
    if (!path) return null
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path
    return '/storage/' + path
}

// ── Variants ─────────────────────────────────────────────────────────────────
const variants      = ref(props.product.variants ?? [])
const variantForm   = ref({ sku: '', stock_quantity: 0, sale_price: props.product.sale_price ?? '', attributes: {} })
const variantImageFile = ref(null)
const variantImagePreview = ref(null)
const addVariantOpen = ref(false)

function variantLabel(v) {
    if (!v.attribute_values?.length) return v.name || 'Variante'
    return v.attribute_values.map(av => av.value).join(' / ')
}

function stockClass(qty) {
    if (qty <= 0)  return 'bg-red-100 text-red-700'
    if (qty <= 5)  return 'bg-amber-100 text-amber-700'
    return 'bg-green-100 text-green-700'
}

const editingStock = ref({})

function startEditStock(id, current) {
    editingStock.value[id] = current
}

async function saveStock(variantId) {
    const qty = editingStock.value[variantId]
    await fetch(`/admin/products/${props.product.id}/variants/${variantId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json' },
        body: JSON.stringify({ stock_quantity: qty }),
    })
    const v = variants.value.find(v => v.id === variantId)
    if (v) v.stock_quantity = qty
    delete editingStock.value[variantId]
    notify('Stock mis à jour.')
}

async function deleteVariant(variantId) {
    if (!confirm('Supprimer cette variante ?')) return
    await fetch(`/admin/products/${props.product.id}/variants/${variantId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json' },
    })
    variants.value = variants.value.filter(v => v.id !== variantId)
    notify('Variante supprimée.')
}

function handleVariantImage(e) {
    const file = e.target.files[0]
    variantImageFile.value = file || null
    variantImagePreview.value = file ? URL.createObjectURL(file) : null
}

function clearVariantImage() {
    variantImageFile.value = null
    variantImagePreview.value = null
}

function notify(msg, type = 'success') {
    window.showNotification?.(msg, type)
}

async function submitVariant() {
    if (!variantForm.value.sku.trim()) { notify('Le SKU est obligatoire.', 'error'); return }

    const body = new FormData()
    body.append('sku', variantForm.value.sku)
    body.append('stock_quantity', variantForm.value.stock_quantity)
    if (variantForm.value.sale_price) body.append('sale_price', variantForm.value.sale_price)
    Object.entries(variantForm.value.attributes).forEach(([aid, vid]) => {
        if (vid) body.append(`attributes[${aid}]`, vid)
    })
    if (variantImageFile.value) body.append('image', variantImageFile.value)

    try {
        const res = await fetch(`/admin/products/${props.product.id}/variants`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body,
        })
        const data = await res.json()
        if (data.success) {
            variants.value.push(data.variant)
            addVariantOpen.value = false
            variantForm.value = { sku: '', stock_quantity: 0, sale_price: props.product.sale_price ?? '', attributes: {} }
            clearVariantImage()
            notify('Variante ajoutée avec succès.')
        } else {
            notify(data.message || 'Erreur lors de la création.', 'error')
        }
    } catch (e) {
        notify('Erreur réseau.', 'error')
    }
}

// ── Générateur de combinaisons ────────────────────────────────────────────────
const showGenerator   = ref(false)
const selectedAttrs   = ref([])
const selectedValues  = ref({})
const generatedRows   = ref([])
const bulkSubmitting  = ref(false)
const bulkError       = ref(null)

function toggleAttr(attrId) {
    const idx = selectedAttrs.value.indexOf(attrId)
    if (idx >= 0) {
        selectedAttrs.value.splice(idx, 1)
        delete selectedValues.value[attrId]
    } else {
        selectedAttrs.value.push(attrId)
        selectedValues.value[attrId] = []
    }
    generatedRows.value = []
}

function isAttrSelected(attrId) {
    return selectedAttrs.value.includes(attrId)
}

function toggleValue(attrId, valueId) {
    if (!selectedValues.value[attrId]) selectedValues.value[attrId] = []
    const idx = selectedValues.value[attrId].indexOf(valueId)
    if (idx >= 0) selectedValues.value[attrId].splice(idx, 1)
    else selectedValues.value[attrId].push(valueId)
    generatedRows.value = []
}

function isValueSelected(attrId, valueId) {
    return (selectedValues.value[attrId] || []).includes(valueId)
}

function attrById(id) {
    return props.attributes.find(a => a.id === id)
}

function valueById(attrId, valueId) {
    const attr = attrById(attrId)
    return attr ? attr.values.find(v => v.id === valueId) : null
}

const canGenerate = computed(() => {
    if (selectedAttrs.value.length === 0) return false
    return selectedAttrs.value.every(aid => (selectedValues.value[aid] || []).length > 0)
})

function generate() {
    const pools = selectedAttrs.value.map(aid => {
        return (selectedValues.value[aid] || []).map(vid => {
            const val = valueById(aid, vid) || {}
            return { attrId: aid, valueId: vid, label: val.value || vid, color: val.color_code || null }
        })
    })
    let combos = [[]]
    for (const pool of pools) {
        combos = combos.flatMap(c => pool.map(v => [...c, v]))
    }
    const sku = (props.product.sku || '').toUpperCase().replace(/[^A-Z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
    generatedRows.value = combos.map(combo => {
        const suffix = combo.map(v => v.label.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 6)).join('-')
        const attrs = {}
        combo.forEach(v => { attrs[v.attrId] = v.valueId })
        return {
            label: combo.map(v => v.label).join(' / '),
            colors: combo.filter(v => v.color).map(v => v.color),
            sku: sku + '-' + suffix,
            stock: 0,
            price: props.product.sale_price ?? '',
            purchase_price: props.product.purchase_price ?? '',
            attrs,
            remove: false,
        }
    })
}

async function submitBulk() {
    const rows = generatedRows.value.filter(r => !r.remove && r.sku.trim())
    if (!rows.length) return

    const skus = rows.map(r => r.sku.trim().toUpperCase())
    if (skus.length !== new Set(skus).size) {
        bulkError.value = 'Certaines lignes ont des SKU identiques.'
        setTimeout(() => { bulkError.value = null }, 6000)
        return
    }

    bulkSubmitting.value = true
    bulkError.value = null
    try {
        const res = await fetch(`/admin/products/${props.product.id}/variants/bulk`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                rows: rows.map(r => ({
                    sku: r.sku,
                    stock_quantity: parseInt(r.stock) || 0,
                    sale_price: r.price !== '' ? parseFloat(r.price) : null,
                    purchase_price: r.purchase_price !== '' ? parseFloat(r.purchase_price) : null,
                    attributes: r.attrs,
                }))
            }),
        })
        const data = await res.json()
        if (data.success) {
            generatedRows.value = []
            selectedAttrs.value = []
            selectedValues.value = {}
            showGenerator.value = false
            notify(data.message || 'Variantes créées avec succès.')
            router.reload()
        } else {
            bulkError.value = data.message || 'Erreur lors de la création'
        }
    } catch (e) {
        bulkError.value = 'Erreur réseau.'
    }
    bulkSubmitting.value = false
}

// ── Images ───────────────────────────────────────────────────────────────────
function handleFiles(e) {
    const files = Array.from(e.target.files)
    newFiles.value = files
    newFilePreviews.value = files.map(f => URL.createObjectURL(f))
    form.images = files
}

async function deleteProdImage(imageId) {
    if (!confirm('Supprimer cette image ?')) return
    await fetch(`/admin/products/${props.product.id}/images/${imageId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    router.reload({ only: ['product'] })
}

async function setPrimary(imageId) {
    await fetch(`/admin/products/${props.product.id}/images/${imageId}/primary`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    router.reload({ only: ['product'] })
}

// ── Submit ────────────────────────────────────────────────────────────────────
function submit() {
    form.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('admin.products.update', props.product.id), {
            forceFormData: true,
            onSuccess: () => notify('Produit enregistré.'),
            onError: () => notify('Erreur lors de la sauvegarde.', 'error'),
        })
}

function deleteProduct() {
    router.delete(route('admin.products.destroy', props.product.id))
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ product.name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Modifier le produit</p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('admin.products.show', product.id)"
                    class="h-9 px-4 inline-flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Voir
                </a>
                <a :href="route('admin.products.index')"
                    class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    ← Retour
                </a>
            </div>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Colonne principale -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Informations générales -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations générales</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nom du produit <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required
                                    class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.name ? 'border-red-300' : 'border-gray-200'">
                                <p v-if="form.errors.name" class="mt-1 text-[11px] text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description courte</label>
                                <textarea v-model="form.short_description" rows="2"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description complète</label>
                                <textarea v-model="form.description" rows="5"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Prix -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Prix</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat HT <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="form.purchase_price" type="number" step="0.01" min="0" required
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix de vente TTC <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="form.sale_price" type="number" step="0.01" min="0" required
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix barré</label>
                                <div class="relative">
                                    <input v-model="form.compare_price" type="number" step="0.01" min="0"
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Taux de TVA</label>
                            <select v-model="form.tax_rate"
                                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="20">20% — Standard</option>
                                <option value="10">10% — Intermédiaire</option>
                                <option value="5.5">5.5% — Réduit</option>
                                <option value="2.1">2.1% — Super réduit</option>
                                <option value="0">0% — Exonéré</option>
                            </select>
                        </div>
                    </div>

                    <!-- Stock & Identifiants -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock & Identifiants</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                                <input v-model="form.sku" type="text" required
                                    class="w-full h-9 px-3 text-[13px] font-mono border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.sku ? 'border-red-300' : 'border-gray-200'">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Code-barres</label>
                                <input v-model="form.barcode" type="text"
                                    class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Poids (kg)</label>
                                <input v-model="form.weight" type="number" step="0.001" min="0"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <template v-if="!product.has_variants">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantité en stock</label>
                                    <input v-model="form.stock_quantity" type="number" min="0"
                                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                                    <input v-model="form.stock_alert_threshold" type="number" min="0"
                                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </template>
                        <div v-else class="mt-3 flex items-start gap-2.5 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2.5">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[12px] text-blue-700">Le stock est géré par variante. Consultez la section variantes ci-dessous.</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input v-model="form.track_stock" type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[13px] text-gray-700">Suivre le stock de ce produit</span>
                            </label>
                        </div>
                    </div>

                    <!-- Images existantes + nouvelles -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Images du produit</h2>

                        <div v-if="product.images?.length" class="mb-4">
                            <p class="text-xs font-medium text-gray-700 mb-2">Images existantes</p>
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                                <div v-for="img in product.images" :key="img.id" class="relative group aspect-square">
                                    <img :src="storageUrl(img.path)" class="w-full h-full object-cover rounded-lg border-2"
                                        :class="img.is_primary ? 'border-blue-400' : 'border-gray-200'">
                                    <span v-if="img.is_primary" class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-blue-500 text-white text-[10px] font-semibold rounded">Principale</span>
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-1.5">
                                        <button v-if="!img.is_primary" type="button" @click="setPrimary(img.id)"
                                            class="p-1.5 bg-white rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Définir comme principale">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteProdImage(img.id)"
                                            class="p-1.5 bg-white rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Supprimer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-5 text-center cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition-colors"
                             @click="$refs.fileInput.click()">
                            <p class="text-[13px] font-medium text-gray-600">Ajouter de nouvelles images</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">JPEG, PNG, WEBP — max 5 Mo par image</p>
                            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="handleFiles">
                        </div>
                        <div v-if="newFilePreviews.length" class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-3">
                            <div v-for="(src, i) in newFilePreviews" :key="i" class="aspect-square">
                                <img :src="src" class="w-full h-full object-cover rounded-lg border border-gray-200">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="space-y-5">

                    <!-- Publication -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Publication</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                                <select v-model="form.status"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="draft">Brouillon</option>
                                    <option value="active">Actif</option>
                                    <option value="archived">Archivé</option>
                                </select>
                            </div>
                            <div class="space-y-2 pt-1">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.is_featured" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Mis en avant</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.is_new" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Nouveauté</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.has_variants" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Produit avec variantes</span>
                                </label>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex gap-2">
                                <button type="submit" :disabled="form.processing"
                                    class="flex-1 h-9 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                                </button>
                                <a :href="route('admin.products.index')"
                                    class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Catégorie -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Catégorie</h2>
                        <select v-model="form.category_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sans catégorie</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.full_path }}
                            </option>
                        </select>
                    </div>

                    <!-- Statistiques -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Statistiques</h2>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                                <span class="text-[12px] text-gray-500">Stock total</span>
                                <span class="text-[13px] font-semibold text-gray-800">
                                    {{ product.has_variants
                                        ? (product.variants ?? []).reduce((s, v) => s + (v.stock_quantity ?? 0), 0)
                                        : product.stock_quantity }} pcs
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                                <span class="text-[12px] text-gray-500">Variantes</span>
                                <span class="text-[13px] font-semibold text-gray-800">{{ (product.variants ?? []).length }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5">
                                <span class="text-[12px] text-gray-500">Ventes</span>
                                <span class="text-[13px] font-semibold text-gray-800">{{ Number(product.sales_count ?? 0).toLocaleString('fr-FR') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de danger -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Zone de danger</h2>
                        <p class="text-[12px] text-gray-500 mb-3">La suppression est irréversible. Toutes les images et variantes seront supprimées.</p>
                        <div v-if="!confirmDelete">
                            <button type="button" @click="confirmDelete = true"
                                class="w-full h-9 border border-red-200 text-[13px] font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                Supprimer le produit
                            </button>
                        </div>
                        <div v-else class="space-y-2">
                            <p class="text-[12px] font-semibold text-red-700 text-center">Êtes-vous sûr ?</p>
                            <div class="flex gap-2">
                                <button type="button" @click="deleteProduct"
                                    class="flex-1 h-9 bg-red-600 text-white text-[13px] font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Confirmer
                                </button>
                                <button type="button" @click="confirmDelete = false"
                                    class="flex-1 h-9 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

        <!-- Variantes -->
        <div v-if="product.has_variants" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Variantes</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                        :class="variants.length > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                        {{ variants.length }} variante(s)
                    </span>
                </div>
                <span v-if="variants.length" class="text-[12px] text-gray-400">
                    {{ variants.reduce((s, v) => s + (v.stock_quantity ?? 0), 0) }} pcs au total
                </span>
            </div>

            <div v-if="variants.length" class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Stock</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Prix vente</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="v in variants" :key="v.id" class="hover:bg-gray-50/60 group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img v-if="v.image" :src="storageUrl(v.image)" class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                    <span v-else class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 inline-block"></span>
                                    <span class="font-medium text-gray-900">{{ variantLabel(v) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-[12px] text-gray-700">{{ v.sku }}</td>
                            <td class="px-4 py-3 text-center">
                                <div v-if="editingStock[v.id] === undefined" class="flex items-center justify-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold" :class="stockClass(v.stock_quantity)">
                                        {{ v.stock_quantity <= 0 ? 'Rupture' : v.stock_quantity + ' pcs' }}
                                    </span>
                                    <button type="button" @click="startEditStock(v.id, v.stock_quantity)"
                                        class="opacity-0 group-hover:opacity-100 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-blue-500 transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                </div>
                                <div v-else class="flex items-center justify-center gap-1">
                                    <input v-model.number="editingStock[v.id]" type="number" min="0"
                                        class="w-16 h-7 px-2 text-[12px] border border-blue-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 text-center"
                                        @keydown.enter.prevent="saveStock(v.id)"
                                        @keydown.escape="delete editingStock[v.id]">
                                    <button type="button" @click="saveStock(v.id)"
                                        class="w-7 h-7 flex items-center justify-center bg-blue-600 text-white rounded hover:bg-blue-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums text-gray-700">
                                {{ v.sale_price ? Number(v.sale_price).toLocaleString('fr-FR') + ' F' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" @click="deleteVariant(v.id)"
                                    class="opacity-0 group-hover:opacity-100 w-7 h-7 flex items-center justify-center text-red-500 hover:bg-red-50 rounded transition-opacity ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-8 text-center">
                <p class="text-[13px] text-gray-400">Aucune variante — ajoutez-en une ci-dessous</p>
            </div>

            <!-- Générateur de combinaisons -->
            <div class="border-t border-gray-100">
                <button type="button" @click="showGenerator = !showGenerator"
                    class="w-full flex items-center justify-between px-5 py-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h8m-8 4h8m6-4l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-900">Générer des variantes</span>
                            <span class="ml-2 text-[11px] text-gray-400">Combinaisons automatiques</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showGenerator ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div v-if="showGenerator" class="p-5 space-y-5">
                    <!-- Étape 1 : choisir les attributs -->
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">1 — Choisir les dimensions</p>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="attr in attributes" :key="attr.id" type="button"
                                @click="toggleAttr(attr.id)"
                                :class="isAttrSelected(attr.id) ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-600 border-gray-200 hover:border-orange-300'"
                                class="h-8 px-4 text-[13px] font-medium border rounded-full transition-all flex items-center gap-2">
                                <span v-if="attr.type === 'color'" class="w-3 h-3 rounded-full bg-gradient-to-br from-orange-400 to-pink-400 flex-shrink-0"></span>
                                {{ attr.name }}
                                <span v-if="isAttrSelected(attr.id)" class="text-[10px] opacity-70">({{ (selectedValues[attr.id] || []).length }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- Étape 2 : valeurs par attribut -->
                    <div v-for="attrId in selectedAttrs" :key="attrId">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2.5">
                            2 — {{ attrById(attrId)?.name }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="val in (attrById(attrId)?.values || [])" :key="val.id" type="button"
                                @click="toggleValue(attrId, val.id)"
                                :class="isValueSelected(attrId, val.id) ? 'ring-2 ring-orange-500 ring-offset-1' : 'ring-1 ring-gray-200 hover:ring-orange-300'"
                                class="relative transition-all rounded-lg overflow-hidden">
                                <span v-if="attrById(attrId)?.type === 'color' && val.color_code"
                                    class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 text-[12px] font-medium text-gray-700">
                                    <span class="w-5 h-5 rounded flex-shrink-0 border border-black/10" :style="'background:' + val.color_code"></span>
                                    {{ val.value }}
                                    <svg v-if="isValueSelected(attrId, val.id)" class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span v-else class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-medium"
                                    :class="isValueSelected(attrId, val.id) ? 'text-orange-700 bg-orange-50' : 'text-gray-700 bg-white'">
                                    {{ val.value }}
                                    <svg v-if="isValueSelected(attrId, val.id)" class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Bouton générer -->
                    <div v-if="selectedAttrs.length > 0" class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <button type="button" @click="generate" :disabled="!canGenerate"
                            class="h-9 px-5 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Générer
                            <span v-if="canGenerate" class="text-[11px] text-gray-300">
                                ({{ selectedAttrs.reduce((t, aid) => t * (selectedValues[aid] || []).length, 1) }} combinaisons)
                            </span>
                        </button>
                        <p v-if="!canGenerate" class="text-[12px] text-gray-400">Sélectionnez au moins une valeur par dimension</p>
                    </div>

                    <!-- Tableau des combinaisons générées -->
                    <div v-if="generatedRows.length > 0">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                                3 — Ajuster et créer
                                <span class="normal-case font-normal ml-1">{{ generatedRows.filter(r => !r.remove).length }} variante(s)</span>
                            </p>
                        </div>

                        <div class="border border-gray-200 rounded-xl overflow-x-auto">
                            <table class="w-full text-[13px] min-w-[700px]">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase">Variante</th>
                                        <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase w-36">SKU</th>
                                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase w-20">Stock</th>
                                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase w-28">Achat (F)</th>
                                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase w-28">Vente (F)</th>
                                        <th class="px-3 py-2.5 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(row, i) in generatedRows" :key="i"
                                        :class="row.remove ? 'opacity-30 line-through' : ''">
                                        <td class="px-3 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <div v-if="row.colors.length" class="flex -space-x-1 flex-shrink-0">
                                                    <span v-for="c in row.colors" :key="c" class="w-4 h-4 rounded-full border border-white" :style="'background:' + c"></span>
                                                </div>
                                                <span class="font-medium text-gray-800">{{ row.label }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="text" v-model="row.sku" :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" v-model.number="row.stock" min="0" :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" v-model="row.purchase_price" min="0" step="1" placeholder="—" :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" v-model="row.price" min="0" step="1" placeholder="—" :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5 text-center">
                                            <button type="button" @click="row.remove = !row.remove"
                                                :class="row.remove ? 'text-orange-500' : 'text-gray-300 hover:text-red-500'"
                                                class="transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Erreur -->
                        <div v-if="bulkError" class="mt-3 flex items-start gap-2 px-3 py-2.5 bg-red-50 border border-red-200 rounded-lg text-[12px] text-red-700">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <span>{{ bulkError }}</span>
                        </div>

                        <!-- Bouton créer -->
                        <div class="flex items-center gap-3 mt-4">
                            <button type="button" @click="submitBulk"
                                :disabled="bulkSubmitting || generatedRows.filter(r => !r.remove).length === 0"
                                class="h-9 px-6 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <svg v-if="bulkSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                {{ bulkSubmitting ? 'Création...' : 'Créer ' + generatedRows.filter(r => !r.remove).length + ' variante(s)' }}
                            </button>
                            <button type="button" @click="generatedRows = []; bulkError = null" :disabled="bulkSubmitting"
                                class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition disabled:opacity-50">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire ajout variante -->
            <div class="border-t border-gray-100">
                <button type="button" @click="addVariantOpen = !addVariantOpen"
                    class="w-full flex items-center justify-between px-5 py-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                    <span class="text-sm font-semibold text-gray-900">Ajouter une variante</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="addVariantOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div v-if="addVariantOpen" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">SKU <span class="text-red-500">*</span></label>
                            <input v-model="variantForm.sku" type="text" required
                                class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Stock initial</label>
                            <input v-model.number="variantForm.stock_quantity" type="number" min="0"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Prix de vente (F)</label>
                            <input v-model="variantForm.sale_price" type="number" min="0" step="1"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div v-if="attributes.length">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Attributs</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div v-for="attr in attributes" :key="attr.id">
                                <label class="block text-[11px] font-medium text-gray-600 mb-1">{{ attr.name }}</label>
                                <select v-model="variantForm.attributes[attr.id]"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Non défini —</option>
                                    <option v-for="val in attr.values" :key="val.id" :value="val.id">{{ val.value }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Image variante -->
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Image (optionnel)</p>
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer hover:border-blue-300 transition-colors"
                                 @click="$refs.variantImgInput.click()">
                                <img v-if="variantImagePreview" :src="variantImagePreview" class="w-full h-full object-cover rounded-lg">
                                <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <input ref="variantImgInput" type="file" accept="image/*" class="hidden" @change="handleVariantImage">
                                <p class="text-[12px] text-gray-500">Cliquez pour choisir une image</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">JPEG, PNG, WEBP — max 5 Mo</p>
                                <button v-if="variantImagePreview" type="button" @click="clearVariantImage(); $refs.variantImgInput.value = ''"
                                    class="mt-1 text-[11px] text-red-500 hover:text-red-600">Supprimer</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="submitVariant"
                            class="h-9 px-5 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                            Ajouter la variante
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
