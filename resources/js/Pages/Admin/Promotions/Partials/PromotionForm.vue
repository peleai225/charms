<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, Search } from 'lucide-vue-next'
import CategoryPicker from './CategoryPicker.vue'
import MarginPanel from './MarginPanel.vue'

const props = defineProps({
    categories: { type: Array, required: true },
    products: { type: Array, required: true },
    promotion: { type: Object, default: null },
})

const isEdit = computed(() => props.promotion !== null)

const form = useForm({
    name: props.promotion?.name ?? '',
    description: props.promotion?.description ?? '',
    lot_qty: props.promotion?.lot_qty ?? 3,
    lot_price: props.promotion?.lot_price ?? null,
    price_min: props.promotion?.price_min ?? null,
    price_max: props.promotion?.price_max ?? null,
    max_lots_per_order: props.promotion?.max_lots_per_order ?? null,
    include_descendants: props.promotion?.include_descendants ?? true,
    stackable_with_coupons: props.promotion?.stackable_with_coupons ?? false,
    is_active: props.promotion?.is_active ?? true,
    priority: props.promotion?.priority ?? 0,
    starts_at: props.promotion?.starts_at ?? '',
    expires_at: props.promotion?.expires_at ?? '',
    category_ids: props.promotion?.category_ids ?? [],
    product_ids: props.promotion?.product_ids ?? [],
})

// ── Produits nommés explicitement ──
const productSearch = ref('')

const visibleProducts = computed(() => {
    const term = productSearch.value.trim().toLowerCase()
    const selected = props.products.filter(p => form.product_ids.includes(p.id))

    if (!term) return selected

    const matches = props.products.filter(
        p => p.name.toLowerCase().includes(term) && !form.product_ids.includes(p.id),
    )

    return [...selected, ...matches.slice(0, 20)]
})

function toggleProduct(id) {
    form.product_ids = form.product_ids.includes(id)
        ? form.product_ids.filter(existing => existing !== id)
        : [...form.product_ids, id]
}

// ── Prix unitaire dans le lot, pour que l'exploitant voie ce qu'il annonce ──
const unitPriceInLot = computed(() => {
    if (!form.lot_qty || form.lot_price === null || form.lot_price === '') return null
    return Math.round(Number(form.lot_price) / Number(form.lot_qty))
})

const formatPrice = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' F CFA'

function submit() {
    if (isEdit.value) {
        form.put(route('admin.promotions.update', props.promotion.id))
    } else {
        form.post(route('admin.promotions.store'))
    }
}
</script>

<template>
    <div class="p-6 max-w-4xl space-y-5">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.promotions.index')"
                class="h-11 w-11 sm:h-9 sm:w-9 inline-flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                <ArrowLeft class="w-4 h-4" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ isEdit ? 'Modifier l’offre' : 'Nouvelle offre par lot' }}
                </h1>
                <p class="text-[13px] text-gray-500 mt-0.5">
                    Exemple : 3 t-shirts pour 10 000 F CFA, sur la catégorie T-shirts entre 4 000 et 4 499 F.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Identité -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-gray-900">L’offre</h2>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">
                        Nom affiché au client <span class="text-red-600">*</span>
                    </label>
                    <input v-model="form.name" type="text" placeholder="3 t-shirts pour 10 000 F"
                        class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="form.errors.name ? 'border-red-300' : 'border-gray-200'">
                    <p v-if="form.errors.name" class="mt-1 text-[12px] text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Description (optionnelle)</label>
                    <textarea v-model="form.description" rows="2"
                        placeholder="Précision affichée sous l’offre sur la fiche produit."
                        class="w-full px-3 py-2 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="form.errors.description ? 'border-red-300' : 'border-gray-200'"></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-[12px] text-red-600">{{ form.errors.description }}</p>
                </div>
            </div>

            <!-- Lot -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-gray-900">Le lot</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Articles par lot <span class="text-red-600">*</span>
                        </label>
                        <input v-model.number="form.lot_qty" type="number" min="2" max="999"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.lot_qty ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.lot_qty" class="mt-1 text-[12px] text-red-600">{{ form.errors.lot_qty }}</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">
                            Prix du lot (F CFA) <span class="text-red-600">*</span>
                        </label>
                        <input v-model.number="form.lot_price" type="number" min="0" step="1"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.lot_price ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.lot_price" class="mt-1 text-[12px] text-red-600">{{ form.errors.lot_price }}</p>
                    </div>
                </div>

                <p v-if="unitPriceInLot !== null" class="text-[13px] text-gray-600">
                    Soit <span class="font-semibold text-gray-900">{{ formatPrice(unitPriceInLot) }}</span> par article
                    si le lot ne contient que des articles identiques.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Prix mini de l’article</label>
                        <input v-model.number="form.price_min" type="number" min="0" placeholder="Aucun"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.price_min ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.price_min" class="mt-1 text-[12px] text-red-600">{{ form.errors.price_min }}</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Prix maxi de l’article</label>
                        <input v-model.number="form.price_max" type="number" min="0" placeholder="Aucun"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.price_max ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.price_max" class="mt-1 text-[12px] text-red-600">{{ form.errors.price_max }}</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Lots max / commande</label>
                        <input v-model.number="form.max_lots_per_order" type="number" min="1" placeholder="Illimité"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.max_lots_per_order ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.max_lots_per_order" class="mt-1 text-[12px] text-red-600">{{ form.errors.max_lots_per_order }}</p>
                    </div>
                </div>

                <p class="text-[12px] text-gray-500">
                    La fourchette évite qu’une offre pensée pour des articles à 4 000 F ne s’applique à un article à 20 000 F.
                    Laissez vide pour ne pas contraindre.
                </p>
            </div>

            <!-- Cible -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">
                <h2 class="text-[15px] font-semibold text-gray-900">Ce que l’offre couvre</h2>

                <CategoryPicker
                    :categories="categories"
                    v-model="form.category_ids"
                    v-model:include-descendants="form.include_descendants"
                    :error="form.errors.category_ids"
                />

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">
                        Produits nommés explicitement (optionnel)
                    </label>
                    <div class="relative mb-2">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input v-model="productSearch" type="search" placeholder="Rechercher un produit..."
                            class="w-full pl-9 pr-3 h-11 sm:h-10 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div v-if="visibleProducts.length" class="border border-gray-200 rounded-lg divide-y divide-gray-50 max-h-60 overflow-y-auto">
                        <label v-for="product in visibleProducts" :key="product.id"
                            class="flex items-center justify-between gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" :checked="form.product_ids.includes(product.id)"
                                    @change="toggleProduct(product.id)"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[13px] text-gray-800">{{ product.name }}</span>
                            </span>
                            <span class="text-[12px] text-gray-500 tabular-nums">{{ formatPrice(product.price) }}</span>
                        </label>
                    </div>
                    <p v-else class="text-[12px] text-gray-400 px-1">
                        Tapez quelques lettres pour chercher un produit. Un produit nommé ici entre dans l’offre
                        même s’il n’est pas dans une catégorie ciblée.
                    </p>

                    <p v-if="form.errors.product_ids" class="mt-1 text-[12px] text-red-600">{{ form.errors.product_ids }}</p>
                </div>
            </div>

            <!-- Marge -->
            <MarginPanel
                :category-ids="form.category_ids"
                :product-ids="form.product_ids"
                :include-descendants="form.include_descendants"
                :price-min="form.price_min"
                :price-max="form.price_max"
                :lot-qty="form.lot_qty"
                :lot-price="form.lot_price"
            />

            <!-- Règles -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-gray-900">Validité et règles</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Début</label>
                        <input v-model="form.starts_at" type="date"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.starts_at ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.starts_at" class="mt-1 text-[12px] text-red-600">{{ form.errors.starts_at }}</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Fin</label>
                        <input v-model="form.expires_at" type="date"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.expires_at ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.expires_at" class="mt-1 text-[12px] text-red-600">{{ form.errors.expires_at }}</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Priorité</label>
                        <input v-model.number="form.priority" type="number"
                            class="w-full h-11 sm:h-10 px-3 border rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="form.errors.priority ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="form.errors.priority" class="mt-1 text-[12px] text-red-600">{{ form.errors.priority }}</p>
                    </div>
                </div>

                <p class="text-[12px] text-gray-500">
                    À priorité égale, l’offre au prix de lot le plus bas s’applique d’abord. Une unité n’est jamais
                    remisée deux fois.
                </p>

                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input v-model="form.stackable_with_coupons" type="checkbox"
                        class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">
                        Cumulable avec les codes promo
                        <span class="block text-[12px] text-gray-500">
                            Décoché, les articles en offre sont exclus de l’assiette des codes promo.
                        </span>
                    </span>
                </label>

                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input v-model="form.is_active" type="checkbox"
                        class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">
                        Offre active
                        <span class="block text-[12px] text-gray-500">
                            Décoché, l’offre n’est appliquée nulle part et n’apparaît pas sur la boutique.
                        </span>
                    </span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a :href="route('admin.promotions.index')"
                    class="h-11 sm:h-10 px-4 inline-flex items-center text-[13px] font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" :disabled="form.processing"
                    class="h-11 sm:h-10 px-5 inline-flex items-center text-[13px] font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                    {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer' : 'Créer l’offre') }}
                </button>
            </div>
        </form>
    </div>
</template>
