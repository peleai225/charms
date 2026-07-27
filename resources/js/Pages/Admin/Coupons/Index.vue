<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    coupons: Object,
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
    router.get(route('admin.coupons.index'), {
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
const confirmId  = ref(null)
const deleteForm = useForm({})

function confirmDelete(id) {
    confirmId.value = id
}

function cancelDelete() {
    confirmId.value = null
}

function doDelete() {
    if (!confirmId.value) return
    deleteForm.delete(route('admin.coupons.destroy', confirmId.value), {
        onFinish: () => { confirmId.value = null },
    })
}

// ── Helpers ──
const STATUS_LABELS = {
    active: 'Actif',
    inactive: 'Inactif',
    expired: 'Expiré',
    scheduled: 'Programmé',
    exhausted: 'Épuisé',
}

const STATUS_CLASSES = {
    active:    'bg-green-50 text-green-700 border-green-200',
    inactive:  'bg-gray-100 text-gray-500 border-gray-200',
    expired:   'bg-red-50 text-red-700 border-red-200',
    scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
    exhausted: 'bg-amber-50 text-amber-700 border-amber-200',
}

const TYPE_LABELS = {
    percentage:    'Pourcentage',
    fixed:         'Montant fixe',
    free_shipping: 'Livraison offerte',
}

const TYPE_CLASSES = {
    percentage:    'bg-purple-50 text-purple-700',
    fixed:         'bg-blue-50 text-blue-700',
    free_shipping: 'bg-teal-50 text-teal-700',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s)  { return (STATUS_CLASSES[s] ?? 'bg-gray-100 text-gray-500 border-gray-200') + ' inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border' }
function typeLabel(t)    { return TYPE_LABELS[t] ?? t }
function typeClass(t)    { return (TYPE_CLASSES[t] ?? 'bg-gray-100 text-gray-500') + ' px-2 py-0.5 text-[11px] font-medium rounded' }
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Codes promo</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ coupons.total }} code(s)</p>
            </div>
            <a :href="route('admin.coupons.create')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau code promo
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Rechercher un code..."
                        class="pl-9 pr-4 h-9 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-52">
                </div>

                <select v-model="status"
                    class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actifs</option>
                    <option value="expired">Expirés</option>
                    <option value="inactive">Inactifs</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
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
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Nom</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Réduction</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Utilisations</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Expiration</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty state -->
                        <tr v-if="coupons.data.length === 0">
                            <td colspan="8" class="px-5 py-16 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucun code promo</p>
                                <p class="text-[12px] text-gray-400 mt-1">Créez des codes promo pour stimuler vos ventes</p>
                                <a :href="route('admin.coupons.create')"
                                    class="mt-4 inline-flex items-center gap-1.5 h-8 px-4 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    Créer un code promo
                                </a>
                            </td>
                        </tr>

                        <tr v-for="coupon in coupons.data" :key="coupon.id"
                            class="group hover:bg-gray-50/50 transition-colors">

                            <td class="px-5 py-4">
                                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg text-[12px]">
                                    {{ coupon.code }}
                                </span>
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-900">{{ coupon.name }}</td>

                            <td class="px-5 py-4">
                                <span :class="typeClass(coupon.type)">{{ typeLabel(coupon.type) }}</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-semibold text-green-600">{{ coupon.type_label }}</span>
                                <p v-if="coupon.min_order_amount" class="text-[11px] text-gray-400 mt-0.5">
                                    Min: {{ coupon.min_order_amount_fmt }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold text-gray-800">{{ coupon.usages_count }}</span>
                                <span v-if="coupon.usage_limit" class="text-[12px] text-gray-400"> / {{ coupon.usage_limit }}</span>
                            </td>

                            <td class="px-5 py-4 text-gray-500">
                                <template v-if="coupon.starts_at_fmt && coupon.expires_at_fmt">
                                    {{ coupon.starts_at_fmt }} - {{ coupon.expires_at_fmt }}
                                </template>
                                <template v-else-if="coupon.expires_at_fmt">
                                    Jusqu'au {{ coupon.expires_at_fmt }}
                                </template>
                                <span v-else class="text-green-600 font-medium">Illimité</span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span :class="statusClass(coupon.status)">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-green-500': coupon.status === 'active',
                                            'bg-gray-400':  coupon.status === 'inactive',
                                            'bg-red-500':   coupon.status === 'expired',
                                            'bg-blue-500':  coupon.status === 'scheduled',
                                            'bg-amber-500': coupon.status === 'exhausted',
                                        }"></span>
                                    {{ statusLabel(coupon.status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a :href="route('admin.coupons.show', coupon.id)"
                                        class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a :href="route('admin.coupons.edit', coupon.id)"
                                        class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button type="button" @click="confirmDelete(coupon.id)"
                                        class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-all"
                                        title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="coupons.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ coupons.current_page }} / {{ coupons.last_page }}
                    &nbsp;·&nbsp; {{ coupons.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="coupons.prev_page_url" :href="coupons.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in coupons.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="coupons.next_page_url" :href="coupons.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal confirmation suppression -->
        <Teleport to="body">
            <div v-if="confirmId !== null"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @keydown.escape.window="cancelDelete">
                <div class="absolute inset-0 bg-black/40" @click="cancelDelete"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
                    <h3 class="text-[15px] font-semibold text-gray-900">Supprimer ce code promo ?</h3>
                    <p class="text-[13px] text-gray-500">
                        Cette action est irréversible. Si le code a déjà été utilisé, il sera désactivé à la place.
                    </p>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="cancelDelete" type="button"
                            class="h-9 px-4 text-[13px] font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button @click="doDelete" type="button" :disabled="deleteForm.processing"
                            class="h-9 px-4 text-[13px] font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                            <svg v-if="deleteForm.processing" class="inline w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
