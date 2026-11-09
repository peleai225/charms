<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next'
import { useToast } from '@/Composables/useToast'
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import ProductPicker from './ProductPicker.vue'

const props = defineProps({
    sale: { type: Object, default: null },
    timezone: { type: String, default: null },
})

const toast = useToast()
const isEdit = computed(() => props.sale !== null)

const form = useForm({
    name: props.sale?.name ?? '',
    discount_percent: props.sale?.discount_percent ?? null,
    starts_at: props.sale?.starts_at ?? '',
    ends_at: props.sale?.ends_at ?? '',
    is_active: props.sale?.is_active ?? true,
})

// Produits retenus, avec nom / prix / image pour l'affichage ; seuls les ids partent au serveur
const products = ref(props.sale?.products ?? [])

// Les mêmes règles que le serveur, vérifiées avant l'envoi
function validate() {
    form.clearErrors()
    const pct = Number(form.discount_percent)

    if (!form.name.trim()) form.setError('name', 'Donnez un nom à la vente.')
    if (form.discount_percent === null || form.discount_percent === '') {
        form.setError('discount_percent', 'Indiquez la remise.')
    } else if (!Number.isInteger(pct) || pct < 1 || pct > 90) {
        form.setError('discount_percent', 'La remise doit être un nombre entier entre 1 et 90 %.')
    }
    if (!form.starts_at) form.setError('starts_at', 'Indiquez le début de la vente.')
    if (!form.ends_at) {
        form.setError('ends_at', 'Indiquez la fin de la vente.')
    } else if (form.starts_at && form.ends_at <= form.starts_at) {
        form.setError('ends_at', 'La fin doit être après le début.')
    }
    if (products.value.length === 0) form.setError('product_ids', 'Ajoutez au moins un produit.')

    return !form.hasErrors
}

// Une erreur serveur sur un produit précis (product_ids.3) s'affiche sous la liste
const productsError = computed(() =>
    form.errors.product_ids
    ?? Object.entries(form.errors).find(([key]) => key.startsWith('product_ids.'))?.[1]
    ?? null,
)

function submit() {
    if (!validate()) {
        toast.error('Vérifiez les champs signalés.')
        return
    }

    const options = {
        onSuccess: () => toast.success(isEdit.value ? 'Vente flash mise à jour.' : 'Vente flash créée.'),
        onError: () => toast.error('Vérifiez les champs signalés.'),
    }

    form.transform(data => ({ ...data, product_ids: products.value.map(p => p.id) }))

    if (isEdit.value) {
        form.put(route('admin.flash-sales.update', props.sale.id), options)
    } else {
        form.post(route('admin.flash-sales.store'), options)
    }
}

const inputClass = (field) => [
    'w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
    form.errors[field] ? 'border-red-300' : 'border-gray-200',
]
</script>

<template>
    <div class="p-6 max-w-4xl space-y-5">

        <!-- En-tête -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.flash-sales.index')" aria-label="Retour aux ventes flash"
                class="h-11 w-11 sm:h-9 sm:w-9 inline-flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                <ArrowLeft class="w-4 h-4" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ isEdit ? 'Modifier la vente flash' : 'Nouvelle vente flash' }}
                </h1>
                <p class="text-[13px] text-gray-500 mt-0.5">
                    Le prix remisé s’applique au panier pendant la vente, puis le prix normal revient tout seul.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5" novalidate>

            <!-- La vente -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-gray-900">La vente</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="flash-name" class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Nom <span class="text-red-600">*</span>
                        </label>
                        <input id="flash-name" v-model="form.name" type="text" maxlength="255"
                            placeholder="Ventes flash du week-end" :class="inputClass('name')">
                        <p v-if="form.errors.name" class="mt-1 text-[12px] text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="flash-pct" class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Remise (%) <span class="text-red-600">*</span>
                        </label>
                        <input id="flash-pct" v-model.number="form.discount_percent" type="number" min="1" max="90" step="1"
                            placeholder="20" :class="inputClass('discount_percent')">
                        <p v-if="form.errors.discount_percent" class="mt-1 text-[12px] text-red-600">{{ form.errors.discount_percent }}</p>
                        <p v-else class="mt-1 text-[12px] text-gray-500">Entre 1 et 90 %, variantes comprises.</p>
                    </div>
                </div>
            </div>

            <!-- Période -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-gray-900">Période</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="flash-start" class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Début <span class="text-red-600">*</span>
                        </label>
                        <input id="flash-start" v-model="form.starts_at" type="datetime-local" :class="inputClass('starts_at')">
                        <p v-if="form.errors.starts_at" class="mt-1 text-[12px] text-red-600">{{ form.errors.starts_at }}</p>
                    </div>

                    <div>
                        <label for="flash-end" class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Fin <span class="text-red-600">*</span>
                        </label>
                        <input id="flash-end" v-model="form.ends_at" type="datetime-local" :class="inputClass('ends_at')">
                        <p v-if="form.errors.ends_at" class="mt-1 text-[12px] text-red-600">{{ form.errors.ends_at }}</p>
                    </div>
                </div>

                <p class="text-[12px] text-gray-500">
                    Si plusieurs ventes en cours couvrent un même produit, la plus forte remise s’applique.
                    Les articles en vente flash n’entrent pas dans les offres par lot et les codes promo ne s’y appliquent pas.
                    <template v-if="timezone">Heures du fuseau {{ timezone }}.</template>
                </p>

                <label class="flex items-start gap-2.5 cursor-pointer min-h-11">
                    <input v-model="form.is_active" type="checkbox"
                        class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">
                        Vente active
                        <span class="block text-[12px] text-gray-500">
                            Décochée, la vente ne s’applique nulle part, même pendant sa période.
                        </span>
                    </span>
                </label>
            </div>

            <!-- Produits -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <ProductPicker v-model="products" :discount-percent="form.discount_percent" :error="productsError" />
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a :href="route('admin.flash-sales.index')"
                    class="h-11 sm:h-10 px-4 inline-flex items-center text-[13px] font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" :disabled="form.processing"
                    class="h-11 sm:h-10 px-5 inline-flex items-center text-[13px] font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                    {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer' : 'Créer la vente flash') }}
                </button>
            </div>
        </form>

        <ToastContainer />
    </div>
</template>
