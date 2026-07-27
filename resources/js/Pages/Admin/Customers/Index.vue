<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    customers: Object,
    stats:     Object,
    filters:   Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

let debounce = null
watch([search, status], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => apply(), 300)
})

function apply() {
    router.get(route('admin.customers.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true })
}

function reset() {
    search.value = ''
    status.value = ''
}

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

const STATUS_CLASSES = {
    active:   'bg-green-100 text-green-700',
    inactive: 'bg-gray-100 text-gray-500',
    blocked:  'bg-red-100 text-red-700',
    vip:      'bg-purple-100 text-purple-700',
}
const STATUS_LABELS = {
    active: 'Actif', inactive: 'Inactif', blocked: 'Bloqué', vip: 'VIP',
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Clients</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ customers.total ?? 0 }} client(s) au total</p>
            </div>
            <a :href="route('admin.customers.create')"
                class="h-9 px-4 inline-flex items-center gap-2 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau client
            </a>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total.toLocaleString('fr-FR') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Actifs</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.active.toLocaleString('fr-FR') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Nouveaux ce mois</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.new_this_month.toLocaleString('fr-FR') }}</p>
            </div>
        </div>

        <!-- Filtres + Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Nom, email, téléphone…"
                        class="w-full pl-9 pr-3 h-9 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <select v-model="status"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                    <option value="blocked">Bloqué</option>
                    <option value="vip">VIP</option>
                </select>
                <button v-if="search || status" @click="reset"
                    class="h-9 px-3 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Réinitialiser
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-[11px] font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-2.5 text-left">Client</th>
                            <th class="px-4 py-2.5 text-left">Téléphone</th>
                            <th class="px-4 py-2.5 text-right">Commandes</th>
                            <th class="px-4 py-2.5 text-right">CA total</th>
                            <th class="px-4 py-2.5 text-left">Statut</th>
                            <th class="px-4 py-2.5 text-left">Inscription</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!customers.data?.length">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <p class="text-sm text-gray-500">Aucun client trouvé</p>
                                <button v-if="search || status" @click="reset"
                                    class="mt-2 text-xs text-blue-600 hover:underline">Réinitialiser les filtres</button>
                            </td>
                        </tr>
                        <tr v-for="c in customers.data" :key="c.id"
                            class="hover:bg-gray-50 transition cursor-pointer"
                            @click="$inertia.visit(route('admin.customers.show', c.id))">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0">
                                        {{ c.initials }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-900">{{ c.full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ c.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-gray-600">{{ c.phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-[13px] font-medium text-gray-900 tabular-nums">{{ c.orders_count }}</td>
                            <td class="px-4 py-3 text-right text-[13px] font-semibold text-gray-900 tabular-nums">{{ fmt(c.total_spent) }}</td>
                            <td class="px-4 py-3">
                                <span :class="STATUS_CLASSES[c.status] ?? 'bg-gray-100 text-gray-500'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold">
                                    {{ STATUS_LABELS[c.status] ?? c.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-gray-500">{{ c.created_at_fmt }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <a :href="route('admin.customers.show', c.id)"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">Voir →</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="customers.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                <p class="text-xs text-gray-500">
                    {{ customers.from }}–{{ customers.to }} sur {{ customers.total }}
                </p>
                <div class="flex items-center gap-1">
                    <a v-for="link in customers.links" :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-lg border transition',
                            link.active
                                ? 'bg-gray-900 text-white border-gray-900'
                                : 'border-gray-200 text-gray-600 hover:bg-gray-50',
                            !link.url ? 'opacity-40 pointer-events-none' : '',
                        ]"
                        v-html="link.label"
                        @click.prevent="link.url && router.visit(link.url, { preserveState: true })"
                    />
                </div>
            </div>
        </div>

    </div>
</template>
