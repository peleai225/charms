<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Pencil, Trash2, Search, X, Zap } from 'lucide-vue-next'
import { useToast } from '@/Composables/useToast'
import { useConfirm } from '@/Composables/useConfirm'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const props = defineProps({
    sales: Object,
    filters: Object,
})

const toast = useToast()
const confirm = useConfirm()

// ── Filtres ──
const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(), 350)
})
watch(status, () => applyFilters())

function applyFilters() {
    router.get(route('admin.flash-sales.index'), {
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
const deletingId = ref(null)

async function destroy(sale) {
    const ok = await confirm({
        title: `Supprimer « ${sale.name} » ?`,
        message: sale.status === 'running'
            ? 'La vente est en cours : ses produits reviennent tout de suite au prix normal, y compris dans les paniers.'
            : 'Cette action est irréversible.',
        confirmLabel: 'Supprimer',
        variant: 'danger',
    })
    if (!ok) return

    deletingId.value = sale.id
    router.delete(route('admin.flash-sales.destroy', sale.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Vente flash supprimée.'),
        onError: () => toast.error('Impossible de supprimer cette vente flash.'),
        onFinish: () => { deletingId.value = null },
    })
}

// ── Statuts ──
const STATUSES = {
    running: { label: 'En cours', badge: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-500' },
    scheduled: { label: 'Programmée', badge: 'bg-blue-50 text-blue-700 border-blue-200', dot: 'bg-blue-500' },
    ended: { label: 'Terminée', badge: 'bg-gray-100 text-gray-500 border-gray-200', dot: 'bg-gray-400' },
    inactive: { label: 'Désactivée', badge: 'bg-gray-100 text-gray-500 border-gray-200', dot: 'bg-gray-400' },
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Ventes flash</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">
                    Une remise en % sur une sélection de produits, pendant une durée limitée.
                </p>
            </div>
            <a :href="route('admin.flash-sales.create')"
                class="h-11 sm:h-9 px-4 inline-flex items-center justify-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <Plus class="w-4 h-4" />
                Nouvelle vente flash
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-56">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model="search" type="search" placeholder="Rechercher une vente..."
                        class="pl-9 pr-4 h-11 sm:h-9 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full">
                </div>

                <select v-model="status"
                    class="h-11 sm:h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="running">En cours</option>
                    <option value="scheduled">Programmées</option>
                    <option value="ended">Terminées</option>
                    <option value="inactive">Désactivées</option>
                </select>

                <button v-if="hasFilters" type="button" @click="resetFilters"
                    class="h-11 sm:h-9 px-3 inline-flex items-center gap-1.5 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <X class="w-3.5 h-3.5" />
                    Effacer
                </button>
            </div>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Vente</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Remise</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Période</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produits</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- État vide -->
                        <tr v-if="sales.data.length === 0">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <Zap class="w-10 h-10 mx-auto mb-3 text-gray-300" />
                                <p class="text-[13px] font-medium text-gray-500">
                                    {{ hasFilters ? 'Aucune vente ne correspond à ces filtres' : 'Aucune vente flash' }}
                                </p>
                                <p class="text-[12px] text-gray-400 mt-1">
                                    {{ hasFilters
                                        ? 'Essayez d’élargir la recherche ou de changer le statut.'
                                        : 'Programmez une remise limitée dans le temps : elle s’affiche sur l’accueil avec un compte à rebours.' }}
                                </p>
                                <a v-if="!hasFilters" :href="route('admin.flash-sales.create')"
                                    class="mt-4 inline-flex items-center gap-1.5 h-11 sm:h-9 px-4 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <Plus class="w-3.5 h-3.5" />
                                    Créer une vente flash
                                </a>
                                <button v-else type="button" @click="resetFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 h-11 sm:h-9 px-4 border border-gray-200 text-gray-600 text-[12px] font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Effacer les filtres
                                </button>
                            </td>
                        </tr>

                        <tr v-for="sale in sales.data" :key="sale.id" class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 font-medium text-gray-900">{{ sale.name }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900 tabular-nums">-{{ sale.discount_percent }} %</td>
                            <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                {{ sale.starts_at_fmt }} → {{ sale.ends_at_fmt }}
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800 tabular-nums">{{ sale.products_count }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border"
                                    :class="STATUSES[sale.status]?.badge">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="STATUSES[sale.status]?.dot"></span>
                                    {{ STATUSES[sale.status]?.label ?? sale.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a :href="route('admin.flash-sales.edit', sale.id)"
                                        class="h-11 w-11 sm:h-8 sm:w-8 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Modifier" :aria-label="`Modifier ${sale.name}`">
                                        <Pencil class="w-4 h-4" />
                                    </a>
                                    <button type="button" @click="destroy(sale)" :disabled="deletingId === sale.id"
                                        class="h-11 w-11 sm:h-8 sm:w-8 inline-flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-all disabled:opacity-40"
                                        title="Supprimer" :aria-label="`Supprimer ${sale.name}`">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="sales.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ sales.current_page }} / {{ sales.last_page }} · {{ sales.total }} ventes
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="sales.prev_page_url" :href="sales.prev_page_url"
                        class="h-11 sm:h-9 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <a v-if="sales.next_page_url" :href="sales.next_page_url"
                        class="h-11 sm:h-9 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

        <ConfirmModal />
        <ToastContainer />
    </div>
</template>
