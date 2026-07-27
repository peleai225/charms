<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    users:   Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const role   = ref(props.filters?.role   ?? '')

let debounce = null
watch([search, role], () => {
    clearTimeout(debounce)
    debounce = setTimeout(apply, 300)
})

function apply() {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role:   role.value   || undefined,
    }, { preserveState: true, replace: true })
}

function reset() {
    search.value = ''
    role.value   = ''
}

function confirmDelete(user) {
    if (!confirm(`Supprimer l'utilisateur « ${user.name} » ? Cette action est irréversible.`)) return
    router.delete(route('admin.users.destroy', user.id))
}

const ROLE_CLASSES = {
    admin:    'bg-red-50 text-red-700',
    manager:  'bg-blue-50 text-blue-700',
    staff:    'bg-green-50 text-green-700',
    customer: 'bg-gray-100 text-gray-600',
}
const ROLE_DOT = {
    admin:    'bg-red-500',
    manager:  'bg-blue-500',
    staff:    'bg-green-500',
    customer: 'bg-gray-400',
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
                <h1 class="text-xl font-bold text-gray-900">Utilisateurs</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ users.total ?? 0 }} utilisateur(s)</p>
            </div>
            <a :href="route('admin.users.create')"
                class="h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvel utilisateur
            </a>
        </div>

        <!-- Filtres + Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            <!-- Barre de filtres -->
            <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Nom, email…"
                        class="w-full pl-9 pr-3 h-9 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <select v-model="role"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les rôles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                    <option value="customer">Client</option>
                </select>
                <button v-if="search || role" @click="reset"
                    class="h-9 px-3 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Effacer
                </button>
            </div>

            <!-- Tableau desktop -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Utilisateur</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Rôle</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Créé le</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="!users.data?.length">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-sm text-gray-400 mb-1">Aucun utilisateur</p>
                                <p class="text-xs text-gray-300">Ajoutez des utilisateurs pour gérer votre boutique</p>
                                <button v-if="search || role" @click="reset"
                                    class="mt-3 text-xs text-blue-600 hover:underline">
                                    Réinitialiser les filtres
                                </button>
                            </td>
                        </tr>
                        <tr v-for="u in users.data" :key="u.id"
                            class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">
                                        {{ initials(u.name) }}
                                    </div>
                                    <a :href="route('admin.users.show', u.id)"
                                        class="text-[13px] font-medium text-gray-900 group-hover:text-blue-700 transition-colors">
                                        {{ u.name }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-[13px] text-gray-500">{{ u.email }}</td>
                            <td class="px-5 py-4 text-center">
                                <span :class="ROLE_CLASSES[u.role] ?? 'bg-gray-100 text-gray-500'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full">
                                    <span :class="ROLE_DOT[u.role] ?? 'bg-gray-400'" class="w-1.5 h-1.5 rounded-full"></span>
                                    {{ u.role ? (u.role.charAt(0).toUpperCase() + u.role.slice(1)) : '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span v-if="u.is_active"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                </span>
                            </td>
                            <td class="px-5 py-4 text-[12px] text-gray-400">{{ u.created_at }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a :href="route('admin.users.edit', u.id)"
                                        class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button v-if="!u.is_current_user" @click="confirmDelete(u)"
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

            <!-- Cartes mobile -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-if="!users.data?.length" class="px-4 py-12 text-center">
                    <p class="text-sm text-gray-400">Aucun utilisateur</p>
                </div>
                <div v-for="u in users.data" :key="u.id" class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 font-bold flex-shrink-0">
                            {{ initials(u.name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-900 truncate">{{ u.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ u.email }}</p>
                        </div>
                        <span :class="ROLE_CLASSES[u.role] ?? 'bg-gray-100 text-gray-500'"
                            class="text-[11px] font-semibold px-2 py-0.5 rounded-full">
                            {{ u.role ? (u.role.charAt(0).toUpperCase() + u.role.slice(1)) : '—' }}
                        </span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[11px] text-gray-400">Créé le {{ u.created_at }}</span>
                        <a :href="route('admin.users.edit', u.id)"
                            class="h-8 px-3 inline-flex items-center text-[13px] text-blue-600 hover:bg-blue-50 rounded transition-colors">
                            Modifier →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                <p class="text-xs text-gray-500">
                    {{ users.from }}–{{ users.to }} sur {{ users.total }}
                </p>
                <div class="flex items-center gap-1">
                    <a v-for="link in users.links" :key="link.label"
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
