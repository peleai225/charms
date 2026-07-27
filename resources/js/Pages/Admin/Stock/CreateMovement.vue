<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    products: Array,
    suppliers: Array,
    prefillProductId: {
        type: [String, Number],
        default: null,
    },
})

const form = useForm({
    product_id: props.prefillProductId ? String(props.prefillProductId) : '',
    variant_id: '',
    type: 'in',
    quantity: 1,
    reason: '',
    reference: '',
    supplier_id: '',
    unit_cost: '',
    notes: '',
})

// Variants du produit sélectionné
const selectedProduct = computed(() =>
    props.products.find(p => String(p.id) === String(form.product_id)) ?? null
)

const variants = computed(() => selectedProduct.value?.variants ?? [])

function onProductChange() {
    form.variant_id = ''
}

function submit() {
    form.post(route('admin.stock.store-movement'))
}

const MOVEMENT_TYPES = [
    { value: 'in',         label: 'Entrée (réception)' },
    { value: 'out',        label: 'Sortie' },
    { value: 'adjustment', label: 'Ajustement (inventaire)' },
    { value: 'return',     label: 'Retour client' },
    { value: 'transfer',   label: 'Transfert' },
]

const isIncoming = computed(() => ['in', 'return'].includes(form.type))
</script>

<template>
    <div class="p-6 space-y-5 max-w-2xl">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.stock.movements')"
                class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nouveau mouvement de stock</h1>
                <p class="text-sm text-gray-500 mt-0.5">Enregistrez une entrée, sortie ou ajustement</p>
            </div>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">

            <!-- Produit -->
            <div>
                <label class="text-xs font-medium text-gray-700 block mb-1.5">
                    Produit <span class="text-red-500">*</span>
                </label>
                <select v-model="form.product_id" @change="onProductChange"
                    :class="form.errors.product_id ? 'border-red-300' : 'border-gray-200'"
                    class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Sélectionner un produit</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.name }} (Stock: {{ p.stock_quantity }})
                    </option>
                </select>
                <p v-if="form.errors.product_id" class="mt-1 text-xs text-red-600">{{ form.errors.product_id }}</p>
            </div>

            <!-- Variante (si le produit en a) -->
            <div v-if="variants.length > 0">
                <label class="text-xs font-medium text-gray-700 block mb-1.5">Variante</label>
                <select v-model="form.variant_id"
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Produit principal</option>
                    <option v-for="v in variants" :key="v.id" :value="v.id">
                        {{ v.sku }} (Stock: {{ v.stock_quantity }})
                    </option>
                </select>
                <p v-if="form.errors.variant_id" class="mt-1 text-xs text-red-600">{{ form.errors.variant_id }}</p>
            </div>

            <!-- Type + Quantité -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Type de mouvement <span class="text-red-500">*</span>
                    </label>
                    <select v-model="form.type"
                        :class="form.errors.type ? 'border-red-300' : 'border-gray-200'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option v-for="t in MOVEMENT_TYPES" :key="t.value" :value="t.value">
                            {{ t.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Quantité <span class="text-red-500">*</span>
                    </label>
                    <input v-model.number="form.quantity" type="number" min="1"
                        :class="form.errors.quantity ? 'border-red-300' : 'border-gray-200'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <p v-if="form.errors.quantity" class="mt-1 text-xs text-red-600">{{ form.errors.quantity }}</p>
                    <p v-if="!isIncoming" class="mt-1 text-[11px] text-amber-600">
                        Stock diminué de {{ form.quantity }} unités
                    </p>
                </div>
            </div>

            <!-- Raison -->
            <div>
                <label class="text-xs font-medium text-gray-700 block mb-1.5">
                    Raison <span class="text-red-500">*</span>
                </label>
                <input v-model="form.reason" type="text" placeholder="Ex: Réception commande #123"
                    :class="form.errors.reason ? 'border-red-300' : 'border-gray-200'"
                    class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <p v-if="form.errors.reason" class="mt-1 text-xs text-red-600">{{ form.errors.reason }}</p>
            </div>

            <!-- Référence + Fournisseur -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Référence</label>
                    <input v-model="form.reference" type="text" placeholder="N° BL, facture..."
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Fournisseur</label>
                    <select v-model="form.supplier_id"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Aucun</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Coût unitaire -->
            <div>
                <label class="text-xs font-medium text-gray-700 block mb-1.5">Coût unitaire (F CFA)</label>
                <input v-model.number="form.unit_cost" type="number" step="1" min="0" placeholder="0"
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <!-- Notes -->
            <div>
                <label class="text-xs font-medium text-gray-700 block mb-1.5">Notes</label>
                <textarea v-model="form.notes" rows="2" placeholder="Notes additionnelles..."
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 resize-none">
                </textarea>
            </div>

            <!-- Actions -->
            <div class="pt-2 border-t border-gray-100 flex items-center gap-3">
                <button @click="submit" :disabled="form.processing"
                    class="h-9 px-5 flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Enregistrer le mouvement
                </button>
                <a :href="route('admin.stock.movements')"
                    class="text-[13px] text-gray-500 hover:text-gray-700 transition">Annuler</a>
            </div>
        </div>

    </div>
</template>
