<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    suppliers: Array,
    products: Array,
})

const form = useForm({
    supplier_id: '',
    reference: '',
    notes: '',
    items: [{ product_id: '', variant_id: '', quantity: 1, unit_cost: '' }],
})

function addItem() {
    form.items.push({ product_id: '', variant_id: '', quantity: 1, unit_cost: '' })
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

function getVariants(productId) {
    const product = props.products.find(p => String(p.id) === String(productId))
    return product?.variants ?? []
}

function onProductChange(index) {
    form.items[index].variant_id = ''
}

const totalQuantity = computed(() =>
    form.items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)
)

const totalValue = computed(() =>
    form.items.reduce((sum, item) =>
        sum + (Number(item.quantity) || 0) * (Number(item.unit_cost) || 0), 0)
)

function fmtPrice(val) {
    return Number(val ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function submit() {
    form.post(route('admin.stock.store-reception'))
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a :href="route('admin.stock.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Réception de marchandises</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Enregistrez une réception fournisseur</p>
                </div>
            </div>
            <button @click="submit" :disabled="form.processing"
                class="h-9 px-4 flex items-center gap-2 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer la réception
            </button>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <div class="grid lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations de réception -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Informations de réception</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">
                                Fournisseur <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.supplier_id"
                                :class="form.errors.supplier_id ? 'border-red-300' : 'border-gray-200'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="">Sélectionner un fournisseur</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <p v-if="form.errors.supplier_id" class="mt-1 text-xs text-red-600">{{ form.errors.supplier_id }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">N° de référence (BL/Facture)</label>
                            <input v-model="form.reference" type="text" placeholder="Ex: BL-2024-001"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 block mb-1.5">Notes</label>
                        <textarea v-model="form.notes" rows="2" placeholder="Notes additionnelles..."
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 resize-none">
                        </textarea>
                    </div>
                </div>

                <!-- Lignes produits -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-900">Produits à réceptionner</h2>
                        <button type="button" @click="addItem"
                            class="h-8 px-3 inline-flex items-center gap-1.5 text-[12px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une ligne
                        </button>
                    </div>

                    <div v-if="form.errors.items" class="mb-3 text-xs text-red-600">{{ form.errors.items }}</div>

                    <div class="space-y-3">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="p-4 border border-gray-200 rounded-xl bg-gray-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[12px] font-medium text-gray-500">Ligne #{{ index + 1 }}</span>
                                <button v-if="form.items.length > 1" type="button" @click="removeItem(index)"
                                    class="p-1 text-red-500 hover:bg-red-100 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid sm:grid-cols-4 gap-3">
                                <!-- Produit -->
                                <div class="sm:col-span-2">
                                    <label class="text-[11px] font-medium text-gray-600 block mb-1">
                                        Produit <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="item.product_id" @change="onProductChange(index)"
                                        class="w-full h-8 px-2 text-[12px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                        <option value="">Sélectionner</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">
                                            {{ p.name }} (Stock: {{ p.stock_quantity }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Quantité -->
                                <div>
                                    <label class="text-[11px] font-medium text-gray-600 block mb-1">
                                        Quantité <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model.number="item.quantity" type="number" min="1"
                                        class="w-full h-8 px-2 text-[12px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                </div>

                                <!-- Coût unitaire -->
                                <div>
                                    <label class="text-[11px] font-medium text-gray-600 block mb-1">Coût unitaire</label>
                                    <input v-model.number="item.unit_cost" type="number" step="1" min="0" placeholder="F CFA"
                                        class="w-full h-8 px-2 text-[12px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                </div>

                                <!-- Variante (si applicable) -->
                                <div v-if="getVariants(item.product_id).length > 0" class="sm:col-span-2">
                                    <label class="text-[11px] font-medium text-gray-600 block mb-1">Variante</label>
                                    <select v-model="item.variant_id"
                                        class="w-full h-8 px-2 text-[12px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                        <option value="">Produit principal</option>
                                        <option v-for="v in getVariants(item.product_id)" :key="v.id" :value="v.id">
                                            {{ v.sku }} (Stock: {{ v.stock_quantity }})
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar résumé -->
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Résumé</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-[13px]">
                            <dt class="text-gray-500">Lignes</dt>
                            <dd class="font-semibold text-gray-900">{{ form.items.length }}</dd>
                        </div>
                        <div class="flex justify-between text-[13px]">
                            <dt class="text-gray-500">Quantité totale</dt>
                            <dd class="font-semibold text-gray-900">{{ totalQuantity }}</dd>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-100 text-[13px]">
                            <dt class="text-gray-500">Valeur estimée</dt>
                            <dd class="font-bold text-green-600">{{ fmtPrice(totalValue) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-blue-50 rounded-xl border border-blue-100 p-4">
                    <h4 class="text-[12px] font-semibold text-blue-900 mb-2">Informations</h4>
                    <ul class="text-[12px] text-blue-700 space-y-1.5">
                        <li>Vérifiez les quantités avant validation</li>
                        <li>Le coût unitaire met à jour le prix d'achat</li>
                        <li>Le stock sera mis à jour automatiquement</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>
