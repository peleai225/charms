<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    movements: Object,
    products: Array,
    filters: Object,
})

const productId = ref(props.filters?.product_id ?? '')
const type = ref(props.filters?.type ?? '')
const startDate = ref(props.filters?.start_date ?? '')
const endDate = ref(props.filters?.end_date ?? '')

const hasFilters = computed(() =>
    productId.value || type.value || startDate.value || endDate.value
)

function applyFilters() {
    router.get(route('admin.stock.movements'), {
        product_id: productId.value || undefined,
        type: type.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    productId.value = ''
    type.value = ''
    startDate.value = ''
    endDate.value = ''
    router.get(route('admin.stock.movements'), {}, { preserveState: false })
}

watch([productId, type, startDate, endDate], () => applyFilters())

const TYPE_LABELS = {
    in: 'Entrée',
    out: 'Sortie',
    adjustment: 'Ajustement',
    return: 'Retour',
    transfer: 'Transfert',
}

const TYPE_CLASSES = {
    in: 'bg-green-50 text-green-700',
    out: 'bg-red-50 text-red-700',
    adjustment: 'bg-purple-50 text-purple-700',
    return: 'bg-blue-50 text-blue-700',
    transfer: 'bg-amber-50 text-amber-700',
}

function typeLabel(t) { return TYPE_LABELS[t] ?? t }
function typeClass(t) { return TYPE_CLASSES[t] ?? 'bg-gray-50 text-gray-700' }
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
                    <h1 class="text-2xl font-bold text-gray-900">Mouvements de stock</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ movements.total ?? 0 }} mouvement(s) au total
                    </p>
                </div>
            </div>
            <a :href="route('admin.stock.create-movement')"
                class="h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau mouvement
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                <!-- Produit -->
                <select v-model="productId"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 min-w-[180px]">
                    <option value="">Tous les produits</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <!-- Type -->
                <select v-model="type"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Tous les types</option>
                    <option value="in">Entrée</option>
                    <option value="out">Sortie</option>
                    <option value="adjustment">Ajustement</option>
                    <option value="return">Retour</option>
                    <option value="transfer">Transfert</option>
                </select>

                <!-- Dates -->
                <input v-model="startDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <input v-model="endDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">

                <!-- Reset -->
                <button v-if="hasFilters" @click="resetFilters"
                    class="h-9 px-3 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Quantité</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Avant</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Après</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Raison</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="movements.data.length === 0">
                            <td colspan="8" class="px-5 py-12 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucun mouvement trouvé</p>
                                <p class="text-[12px] text-gray-400 mt-1">Modifiez vos filtres ou créez un nouveau mouvement</p>
                            </td>
                        </tr>
                        <tr v-for="movement in movements.data" :key="movement.id"
                            class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ movement.created_at }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ movement.product_name ?? 'N/A' }}</p>
                                <p v-if="movement.variant_sku" class="text-[11px] text-gray-400">{{ movement.variant_sku }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="typeClass(movement.type)"
                                    class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full">
                                    {{ typeLabel(movement.type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-bold tabular-nums"
                                :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                            </td>
                            <td class="px-5 py-3 text-center text-gray-500 tabular-nums">{{ movement.quantity_before }}</td>
                            <td class="px-5 py-3 text-center text-gray-700 font-medium tabular-nums">{{ movement.quantity_after }}</td>
                            <td class="px-5 py-3 text-gray-500 max-w-[180px] truncate">{{ movement.reason }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ movement.user_name ?? 'Système' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="movements.last_page > 1"
                class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ movements.current_page }} / {{ movements.last_page }}
                    &nbsp;·&nbsp; {{ movements.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="movements.prev_page_url" :href="movements.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in movements.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="movements.next_page_url" :href="movements.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>
