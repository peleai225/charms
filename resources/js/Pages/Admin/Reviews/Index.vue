<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    reviews: Object,
    filters: Object,
    products: Array,
})

const status    = ref(props.filters?.status ?? '')
const productId = ref(props.filters?.product_id ?? '')

watch([status, productId], () => applyFilters())

function applyFilters() {
    router.get(route('admin.reviews.index'), {
        status:     status.value || undefined,
        product_id: productId.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    status.value    = ''
    productId.value = ''
    applyFilters()
}

const hasFilters = computed(() => status.value || productId.value)

// ── Actions inline ──
const actionLoading = ref(null)

async function patchReview(reviewId, action) {
    if (actionLoading.value) return
    actionLoading.value = `${action}-${reviewId}`
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content
        await fetch(`/admin/reviews/${reviewId}/${action}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        router.reload({ preserveScroll: true })
    } finally {
        actionLoading.value = null
    }
}

// ── Répondre ──
const respondModal   = ref(false)
const respondReview  = ref(null)
const respondForm    = useForm({ admin_response: '' })

function openRespond(review) {
    respondReview.value         = review
    respondForm.admin_response  = review.admin_response ?? ''
    respondModal.value          = true
}

function closeRespond() {
    respondModal.value  = false
    respondReview.value = null
    respondForm.reset()
}

function submitRespond() {
    if (!respondReview.value) return
    respondForm.post(route('admin.reviews.respond', respondReview.value.id), {
        onSuccess: () => closeRespond(),
        preserveScroll: true,
    })
}

// ── Helpers ──
const STATUS_LABELS = { pending: 'En attente', approved: 'Approuvé', rejected: 'Rejeté' }
const STATUS_CLASSES = {
    pending:  'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-green-50 text-green-700 border-green-200',
    rejected: 'bg-red-50 text-red-700 border-red-200',
}
function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s) { return STATUS_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }

function truncate(str, len = 60) {
    if (!str) return ''
    return str.length > len ? str.substring(0, len) + '…' : str
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Avis clients</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Modérez et répondez aux avis de vos clients</p>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">
                <select v-model="status"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="approved">Approuvés</option>
                    <option value="rejected">Rejetés</option>
                </select>

                <select v-if="products && products.length" v-model="productId"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">Tous les produits</option>
                    <option v-for="p in products" :key="p.id" :value="String(p.id)">{{ truncate(p.name, 40) }}</option>
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
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Auteur</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Note</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Avis</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty state -->
                        <tr v-if="reviews.data.length === 0">
                            <td colspan="7" class="px-5 py-16 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucun avis</p>
                                <p class="text-[12px] text-gray-400 mt-1">Les avis de vos clients apparaîtront ici</p>
                            </td>
                        </tr>

                        <tr v-for="review in reviews.data" :key="review.id"
                            class="hover:bg-gray-50/50 transition-colors">

                            <!-- Produit -->
                            <td class="px-5 py-4">
                                <a v-if="review.product_id"
                                    :href="route('admin.products.edit', review.product_id)"
                                    class="text-[13px] text-blue-600 font-medium hover:underline">
                                    {{ truncate(review.product_name, 30) }}
                                </a>
                                <span v-else class="text-[13px] text-gray-400">N/A</span>
                            </td>

                            <!-- Auteur -->
                            <td class="px-5 py-4">
                                <p class="text-[13px] font-medium text-gray-900">{{ review.author_name }}</p>
                                <p class="text-[11px] text-gray-400">{{ review.author_email }}</p>
                                <span v-if="review.is_verified_purchase" class="text-[10px] text-green-600 font-medium">
                                    ✓ Achat vérifié
                                </span>
                            </td>

                            <!-- Note (étoiles) -->
                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center gap-0.5">
                                    <svg v-for="i in 5" :key="i"
                                        :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'"
                                        class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </td>

                            <!-- Avis -->
                            <td class="px-5 py-4 max-w-xs">
                                <p v-if="review.title" class="text-[13px] font-medium text-gray-900">{{ truncate(review.title, 40) }}</p>
                                <p class="text-[12px] text-gray-500">{{ truncate(review.content, 80) }}</p>
                                <div v-if="review.admin_response" class="mt-1.5 pl-2 border-l-2 border-blue-200">
                                    <p class="text-[11px] text-blue-600 italic">{{ truncate(review.admin_response, 50) }}</p>
                                </div>
                            </td>

                            <!-- Statut -->
                            <td class="px-5 py-4 text-center">
                                <span :class="statusClass(review.status)"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-amber-500': review.status === 'pending',
                                            'bg-green-500': review.status === 'approved',
                                            'bg-red-500':   review.status === 'rejected',
                                        }"></span>
                                    {{ statusLabel(review.status) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-5 py-4 text-[12px] text-gray-400 whitespace-nowrap">{{ review.created_at_fmt }}</td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <template v-if="review.status === 'pending'">
                                        <button @click="patchReview(review.id, 'approve')"
                                            :disabled="actionLoading === `approve-${review.id}`"
                                            class="h-7 px-2.5 bg-green-50 text-green-700 text-[11px] font-semibold rounded hover:bg-green-100 transition-colors disabled:opacity-50">
                                            Approuver
                                        </button>
                                        <button @click="patchReview(review.id, 'reject')"
                                            :disabled="actionLoading === `reject-${review.id}`"
                                            class="h-7 px-2.5 bg-red-50 text-red-700 text-[11px] font-semibold rounded hover:bg-red-100 transition-colors disabled:opacity-50">
                                            Rejeter
                                        </button>
                                    </template>
                                    <button v-if="!review.admin_response"
                                        @click="openRespond(review)"
                                        class="h-7 px-2.5 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded hover:bg-blue-100 transition-colors">
                                        Répondre
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="reviews.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ reviews.current_page }} / {{ reviews.last_page }} &nbsp;·&nbsp; {{ reviews.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="reviews.prev_page_url" :href="reviews.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in reviews.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="reviews.next_page_url" :href="reviews.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal Répondre -->
        <Teleport to="body">
            <div v-if="respondModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @keydown.escape.window="closeRespond">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="closeRespond"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-5" @click.stop>
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Répondre à l'avis</h3>
                    <p v-if="respondReview" class="text-[12px] text-gray-500 mb-4">
                        Répondre à <strong>{{ respondReview.author_name }}</strong> — {{ respondReview.rating }}★
                    </p>
                    <textarea v-model="respondForm.admin_response"
                        rows="4" required
                        class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent resize-none"
                        placeholder="Votre réponse publique..."></textarea>
                    <p v-if="respondForm.errors.admin_response" class="mt-1 text-[12px] text-red-600">
                        {{ respondForm.errors.admin_response }}
                    </p>
                    <div class="mt-4 flex gap-2">
                        <button @click="submitRespond"
                            :disabled="respondForm.processing"
                            class="h-9 px-4 bg-blue-600 text-white font-semibold text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                            <span v-if="respondForm.processing">Envoi…</span>
                            <span v-else>Envoyer la réponse</span>
                        </button>
                        <button @click="closeRespond"
                            class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
