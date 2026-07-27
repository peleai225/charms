<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    suppliers: Object,
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
    router.get(route('admin.suppliers.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true })
}

function reset() {
    search.value = ''
    status.value = ''
}

function initials(name) {
    return (name ?? '').substring(0, 2).toUpperCase()
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Fournisseurs</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ suppliers.total ?? 0 }} fournisseur(s)</p>
            </div>
            <a :href="route('admin.suppliers.create')"
                class="h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau fournisseur
            </a>
        </div>

        <!-- Filtres + Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            <!-- Barre de filtres -->
            <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Nom, email, code…"
                        class="w-full pl-9 pr-3 h-9 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <select v-model="status"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
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
                            <th class="px-4 py-2.5 text-left">Fournisseur</th>
                            <th class="px-4 py-2.5 text-left">Contact</th>
                            <th class="px-4 py-2.5 text-left hidden md:table-cell">Ville</th>
                            <th class="px-4 py-2.5 text-center hidden md:table-cell">Mouvements</th>
                            <th class="px-4 py-2.5 text-center">Statut</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!suppliers.data?.length">
                            <td colspan="6" class="px-4 py-14 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="text-[13px] text-gray-500 mb-1">Aucun fournisseur trouvé</p>
                                <button v-if="search || status" @click="reset"
                                    class="text-xs text-blue-600 hover:underline">Réinitialiser les filtres</button>
                                <a v-else :href="route('admin.suppliers.create')"
                                    class="mt-3 h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition">
                                    Ajouter un fournisseur
                                </a>
                            </td>
                        </tr>
                        <tr v-for="s in suppliers.data" :key="s.id"
                            class="group hover:bg-gray-50 transition cursor-pointer"
                            @click="$inertia.visit(route('admin.suppliers.show', s.id))">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">
                                        {{ initials(s.name) }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ s.name }}</p>
                                        <p v-if="s.code" class="text-[11px] text-gray-400 font-mono">{{ s.code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-[13px] text-gray-700">{{ s.email ?? '—' }}</p>
                                <p class="text-[11px] text-gray-400">{{ s.phone ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-gray-600 hidden md:table-cell">{{ s.city ?? '—' }}</td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-[13px] font-medium text-gray-600 tabular-nums">{{ s.stock_movements_count ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="s.is_active"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <a :href="route('admin.suppliers.edit', s.id)"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="suppliers.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                <p class="text-xs text-gray-500">
                    {{ suppliers.from }}–{{ suppliers.to }} sur {{ suppliers.total }}
                </p>
                <div class="flex items-center gap-1">
                    <a v-for="link in suppliers.links" :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-lg border transition',
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
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
