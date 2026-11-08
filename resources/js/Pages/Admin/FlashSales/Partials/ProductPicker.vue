<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { Search, Plus, X, ImageOff } from 'lucide-vue-next'

// Sélection des produits d'une vente flash : recherche côté serveur (produits actifs),
// liste des produits retenus avec leur prix normal et leur prix flash.
const props = defineProps({
    modelValue: { type: Array, required: true }, // [{ id, name, sku, price, status, image }]
    discountPercent: { type: [Number, String], default: null },
    error: { type: String, default: null },
})
const emit = defineEmits(['update:modelValue'])

const query = ref('')
const results = ref([])
const loading = ref(false)
const failed = ref(false)
const loaded = ref(false)

let timer = null
let controller = null

async function load() {
    controller?.abort()
    controller = new AbortController()
    loading.value = true
    failed.value = false

    try {
        const url = route('admin.flash-sales.products', { q: query.value.trim() || undefined })
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            signal: controller.signal,
        })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        results.value = (await res.json()).products ?? []
        loaded.value = true
    } catch (e) {
        if (e.name === 'AbortError') return
        failed.value = true
    } finally {
        loading.value = false
    }
}

watch(query, () => {
    clearTimeout(timer)
    timer = setTimeout(load, 300)
})
onUnmounted(() => {
    clearTimeout(timer)
    controller?.abort()
})

const selectedIds = computed(() => new Set(props.modelValue.map(p => p.id)))
const suggestions = computed(() => results.value.filter(p => !selectedIds.value.has(p.id)))

function add(product) {
    emit('update:modelValue', [...props.modelValue, product])
}

function remove(id) {
    emit('update:modelValue', props.modelValue.filter(p => p.id !== id))
}

const pct = computed(() => {
    const n = Number(props.discountPercent)
    return Number.isInteger(n) && n >= 1 && n <= 90 ? n : null
})

const flashPrice = (price) => Math.round(price * (100 - pct.value) / 100)
const formatPrice = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' F CFA'
</script>

<template>
    <div class="space-y-4">
        <!-- Produits retenus -->
        <div>
            <p class="text-[13px] font-medium text-gray-700 mb-1.5">
                Produits en vente flash ({{ modelValue.length }}) <span class="text-red-600">*</span>
            </p>

            <ul v-if="modelValue.length" class="border rounded-lg divide-y divide-gray-50"
                :class="error ? 'border-red-300' : 'border-gray-200'">
                <li v-for="product in modelValue" :key="product.id" class="flex items-center gap-3 px-3 py-2">
                    <img v-if="product.image" :src="product.image" :alt="product.name"
                        class="w-10 h-10 rounded object-cover bg-gray-100 shrink-0" loading="lazy">
                    <span v-else class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center shrink-0">
                        <ImageOff class="w-4 h-4 text-gray-300" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] text-gray-900 truncate">{{ product.name }}</p>
                        <p class="text-[12px] text-gray-500 tabular-nums">
                            <template v-if="pct">
                                <span class="line-through">{{ formatPrice(product.price) }}</span>
                                → <span class="font-semibold text-gray-900">{{ formatPrice(flashPrice(product.price)) }}</span>
                            </template>
                            <template v-else>{{ formatPrice(product.price) }}</template>
                            <span v-if="product.status && product.status !== 'active'" class="ml-2 text-amber-700">
                                · non publié, invisible en boutique
                            </span>
                        </p>
                    </div>

                    <button type="button" @click="remove(product.id)"
                        class="h-11 w-11 sm:h-8 sm:w-8 inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition shrink-0"
                        :aria-label="`Retirer ${product.name}`" title="Retirer">
                        <X class="w-4 h-4" />
                    </button>
                </li>
            </ul>
            <p v-else class="text-[12px] px-3 py-3 border border-dashed rounded-lg"
                :class="error ? 'border-red-300 text-red-600' : 'border-gray-200 text-gray-500'">
                Aucun produit pour l’instant. Cherchez-les ci-dessous et ajoutez-les un par un.
            </p>
            <p v-if="error" class="mt-1 text-[12px] text-red-600">{{ error }}</p>
        </div>

        <!-- Recherche -->
        <div>
            <label for="flash-product-search" class="block text-[13px] font-medium text-gray-700 mb-1.5">Ajouter des produits</label>
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input id="flash-product-search" v-model="query" type="search" autocomplete="off"
                    placeholder="Nom ou SKU d’un produit actif…" @focus="!loaded && !loading && load()"
                    class="w-full pl-9 pr-3 h-11 sm:h-10 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div v-if="loading" class="mt-2 border border-gray-200 rounded-lg divide-y divide-gray-50" aria-busy="true">
                <div v-for="n in 3" :key="n" class="flex items-center gap-3 px-3 py-2.5">
                    <span class="w-9 h-9 rounded bg-gray-100 animate-pulse"></span>
                    <span class="h-3 w-1/2 rounded bg-gray-100 animate-pulse"></span>
                </div>
            </div>

            <div v-else-if="failed" class="mt-2 flex items-center justify-between gap-3 px-3 py-2.5 border border-red-200 bg-red-50 rounded-lg">
                <p class="text-[12px] text-red-700">Impossible de charger les produits.</p>
                <button type="button" @click="load"
                    class="h-11 sm:h-8 px-3 text-[12px] font-medium text-red-700 border border-red-200 rounded-lg hover:bg-white transition">
                    Réessayer
                </button>
            </div>

            <ul v-else-if="suggestions.length" class="mt-2 border border-gray-200 rounded-lg divide-y divide-gray-50 max-h-72 overflow-y-auto">
                <li v-for="product in suggestions" :key="product.id" class="flex items-center gap-3 px-3 py-2">
                    <img v-if="product.image" :src="product.image" :alt="product.name"
                        class="w-9 h-9 rounded object-cover bg-gray-100 shrink-0" loading="lazy">
                    <span v-else class="w-9 h-9 rounded bg-gray-100 flex items-center justify-center shrink-0">
                        <ImageOff class="w-4 h-4 text-gray-300" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] text-gray-800 truncate">{{ product.name }}</p>
                        <p class="text-[12px] text-gray-500 tabular-nums">{{ formatPrice(product.price) }}</p>
                    </div>
                    <button type="button" @click="add(product)"
                        class="h-11 sm:h-8 px-3 inline-flex items-center gap-1 text-[12px] font-medium text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-50 transition shrink-0">
                        <Plus class="w-3.5 h-3.5" />
                        Ajouter
                    </button>
                </li>
            </ul>

            <p v-else-if="loaded" class="mt-2 text-[12px] text-gray-500 px-1">
                {{ query.trim()
                    ? `Aucun autre produit actif ne correspond à « ${query.trim()} ».`
                    : 'Tous les produits proposés sont déjà dans la vente. Cherchez par nom ou SKU.' }}
            </p>
            <p v-else class="mt-2 text-[12px] text-gray-400 px-1">
                Cliquez dans le champ pour voir les derniers produits, ou tapez un nom.
            </p>
        </div>
    </div>
</template>
