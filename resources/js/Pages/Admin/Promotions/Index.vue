<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Plus, Pencil, Trash2, Search, X, Tag } from 'lucide-vue-next'

const props = defineProps({
    promotions: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(), 350)
})
watch(status, () => applyFilters())

function applyFilters() {
    router.get(route('admin.promotions.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = ''
    applyFilters()
}

const hasFilters = computed(() => search.value || status.value)

// ── Suppression ──
const confirmTarget = ref(null)
const deleteForm = useForm({})

function confirmDelete(promotion) {
    confirmTarget.value = promotion
}

function cancelDelete() {
    confirmTarget.value = null
}

function doDelete() {
    if (!confirmTarget.value) return
    deleteForm.delete(route('admin.promotions.destroy', confirmTarget.value.id), {
        onFinish: () => { confirmTarget.value = null },
    })
}

// ── Statuts ──
const STATUS_LABELS = {
    active: 'Active',
    scheduled: 'Programmée',
    expired: 'Expirée',
    inactive: 'Inactive',
}

const STATUS_CLASSES = {
    active: 'bg-green-50 text-green-700 border-green-200',
    scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
    expired: 'bg-gray-100 text-gray-500 border-gray-200',
    inactive: 'bg-gray-100 text-gray-500 border-gray-200',
}

const DOT_CLASSES = {
    active: 'bg-green-500',
    scheduled: 'bg-blue-500',
    expired: 'bg-gray-400',
    inactive: 'bg-gray-400',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s) {
    return (STATUS_CLASSES[s] ?? 'bg-gray-100 text-gray-500 border-gray-200')
        + ' inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border'
}
</script>

<template>
    <div class="p-6 space-y-5" data-tour-page="promotions.index">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Offres par lot</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ promotions.total }} offre(s)</p>
            </div>
            <a :href="route('admin.promotions.create')" data-tour="promo-new"
                class="h-11 sm:h-9 px-4 inline-flex items-center justify-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <Plus class="w-4 h-4" />
                Nouvelle offre
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4" data-tour="promo-filters">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model="search" type="search" placeholder="Rechercher une offre..."
                        class="pl-9 pr-4 h-11 sm:h-9 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full sm:w-56">
                </div>

                <select v-model="status"
                    class="h-11 sm:h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actives</option>
                    <option value="scheduled">Programmées</option>
                    <option value="expired">Expirées</option>
                    <option value="inactive">Inactives</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters"
                    class="h-11 sm:h-9 px-3 inline-flex items-center gap-1.5 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <X class="w-3.5 h-3.5" />
                    Effacer
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Offre</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Lot</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Fourchette</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Cible</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Période</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Lignes vendues</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Remise cumulée</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty state -->
                        <tr v-if="promotions.data.length === 0">
                            <td colspan="9" class="px-5 py-16 text-center">
                                <Tag class="w-10 h-10 mx-auto mb-3 text-gray-300" />
                                <p class="text-[13px] font-medium text-gray-500">
                                    {{ hasFilters ? 'Aucune offre ne correspond à ces filtres' : 'Aucune offre par lot' }}
                                </p>
                                <p class="text-[12px] text-gray-400 mt-1">
                                    {{ hasFilters
                                        ? 'Essayez d’élargir la recherche ou de changer le statut.'
                                        : 'Créez une offre « 3 pour 10 000 F » et le panier l’appliquera tout seul.' }}
                                </p>
                                <a v-if="!hasFilters" :href="route('admin.promotions.create')"
                                    class="mt-4 inline-flex items-center gap-1.5 h-9 px-4 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <Plus class="w-3.5 h-3.5" />
                                    Créer une offre
                                </a>
                                <button v-else type="button" @click="resetFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 h-9 px-4 border border-gray-200 text-gray-600 text-[12px] font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Effacer les filtres
                                </button>
                            </td>
                        </tr>

                        <tr v-for="promotion in promotions.data" :key="promotion.id"
                            class="group hover:bg-gray-50/50 transition-colors">

                            <td class="px-5 py-4 font-medium text-gray-900">{{ promotion.name }}</td>

                            <td class="px-5 py-4">
                                <span class="font-semibold text-blue-600">
                                    {{ promotion.lot_qty }} pour {{ promotion.lot_price_fmt }}
                                </span>
                                <p v-if="promotion.max_lots_per_order" class="text-[11px] text-gray-400 mt-0.5">
                                    Max {{ promotion.max_lots_per_order }} lot(s) / commande
                                </p>
                            </td>

                            <td class="px-5 py-4 text-gray-600">{{ promotion.band_fmt }}</td>

                            <td class="px-5 py-4 text-gray-600">
                                <span v-if="promotion.categories.length">{{ promotion.categories.join(', ') }}</span>
                                <span v-else class="text-gray-400">Produits listés</span>
                            </td>

                            <td class="px-5 py-4 text-gray-500">
                                <template v-if="promotion.starts_at_fmt && promotion.expires_at_fmt">
                                    {{ promotion.starts_at_fmt }} – {{ promotion.expires_at_fmt }}
                                </template>
                                <template v-else-if="promotion.expires_at_fmt">
                                    Jusqu’au {{ promotion.expires_at_fmt }}
                                </template>
                                <span v-else class="text-green-600 font-medium">Illimitée</span>
                            </td>

                            <td class="px-5 py-4 text-center font-semibold text-gray-800">{{ promotion.lines_count }}</td>

                            <td class="px-5 py-4 text-right font-semibold text-gray-800 tabular-nums">
                                {{ promotion.discount_total_fmt }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span :class="statusClass(promotion.status)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="DOT_CLASSES[promotion.status]"></span>
                                    {{ statusLabel(promotion.status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                    <a :href="route('admin.promotions.edit', promotion.id)"
                                        class="h-11 w-11 sm:h-7 sm:w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Modifier">
                                        <Pencil class="w-4 h-4" />
                                    </a>
                                    <button type="button" @click="confirmDelete(promotion)"
                                        class="h-11 w-11 sm:h-7 sm:w-7 inline-flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-all"
                                        title="Supprimer">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="promotions.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ promotions.current_page }} / {{ promotions.last_page }}
                    &nbsp;·&nbsp; {{ promotions.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="promotions.prev_page_url" :href="promotions.prev_page_url"
                        class="h-9 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <a v-if="promotions.next_page_url" :href="promotions.next_page_url"
                        class="h-9 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

        <!-- Confirmation avant suppression -->
        <Teleport to="body">
            <div v-if="confirmTarget !== null"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @keydown.escape.window="cancelDelete">
                <div class="absolute inset-0 bg-black/40" @click="cancelDelete"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
                    <h3 class="text-[15px] font-semibold text-gray-900">
                        Supprimer l’offre « {{ confirmTarget.name }} » ?
                    </h3>
                    <p class="text-[13px] text-gray-500">
                        Cette action est irréversible. Les commandes passées gardent la remise et le nom
                        de l’offre déjà enregistrés.
                    </p>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="cancelDelete" type="button"
                            class="h-11 sm:h-9 px-4 text-[13px] font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button @click="doDelete" type="button" :disabled="deleteForm.processing"
                            class="h-11 sm:h-9 px-4 text-[13px] font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                            {{ deleteForm.processing ? 'Suppression…' : 'Supprimer' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
