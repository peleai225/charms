<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
})

const showDeleteConfirm = ref(false)

function destroy() {
    router.delete(route('admin.users.destroy', props.user.id))
}

function initials(name) {
    return (name ?? '').substring(0, 2).toUpperCase()
}

const ROLE_CLASSES = {
    admin:    'bg-red-50 text-red-700 ring-1 ring-red-100',
    manager:  'bg-blue-50 text-blue-700 ring-1 ring-blue-100',
    staff:    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
    customer: 'bg-gray-50 text-gray-600 ring-1 ring-gray-200',
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-3xl">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a :href="route('admin.users.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                        {{ initials(user.name) }}
                    </div>
                    <div>
                        <h1 class="text-[15px] font-bold text-gray-900">{{ user.name }}</h1>
                        <p class="text-xs text-gray-400">{{ user.email }}</p>
                    </div>
                </div>
            </div>
            <a :href="route('admin.users.edit', user.id)"
                class="h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Modifier
            </a>
        </div>

        <!-- Informations du compte -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations du compte</h2>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Nom</p>
                    <p class="text-[13px] text-gray-900">{{ user.name }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Email</p>
                    <p class="text-[13px] text-gray-900">{{ user.email }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Rôle</p>
                    <span :class="ROLE_CLASSES[user.role] ?? 'bg-gray-50 text-gray-600 ring-1 ring-gray-200'"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold">
                        {{ user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : '—' }}
                    </span>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Statut</p>
                    <span v-if="user.is_active"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 ring-1 ring-green-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        Actif
                    </span>
                    <span v-else
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-50 text-gray-500 ring-1 ring-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                        Inactif
                    </span>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Créé le</p>
                    <p class="text-[13px] text-gray-900">{{ user.created_at }}</p>
                </div>
                <div v-if="user.updated_at">
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Dernière modification</p>
                    <p class="text-[13px] text-gray-900">{{ user.updated_at }}</p>
                </div>
            </div>
        </div>

        <!-- Zone de danger -->
        <div v-if="!user.is_current_user" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-1">Zone de danger</h2>
            <p class="text-xs text-gray-500 mb-4">La suppression de l'utilisateur est définitive.</p>

            <div v-if="!showDeleteConfirm">
                <button @click="showDeleteConfirm = true" type="button"
                    class="h-9 px-4 border border-red-200 text-[13px] font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    Supprimer l'utilisateur
                </button>
            </div>
            <div v-else class="flex items-center gap-3 flex-wrap">
                <p class="text-xs font-semibold text-red-700">Confirmer la suppression ?</p>
                <button @click="destroy" type="button"
                    class="h-9 px-4 bg-red-600 text-white text-[13px] font-semibold rounded-lg hover:bg-red-700 transition-colors">
                    Confirmer
                </button>
                <button @click="showDeleteConfirm = false" type="button"
                    class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
            </div>
        </div>

    </div>
</template>
