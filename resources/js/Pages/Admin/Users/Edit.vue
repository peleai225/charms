<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
})

const form = useForm({
    name:                  props.user.name,
    email:                 props.user.email,
    password:              '',
    password_confirmation: '',
    role:                  props.user.role ?? 'staff',
    is_active:             props.user.is_active,
})

function submit() {
    form.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('admin.users.update', props.user.id))
}

const showDeleteConfirm = ref(false)

function destroy() {
    router.delete(route('admin.users.destroy', props.user.id))
}

function initials(name) {
    return (name ?? '').substring(0, 2).toUpperCase()
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-2xl">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a :href="route('admin.users.show', user.id)"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold">
                        {{ initials(user.name) }}
                    </div>
                    <div>
                        <h1 class="text-[15px] font-bold text-gray-900">{{ user.name }}</h1>
                        <p class="text-xs text-gray-400">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <!-- Informations du compte -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Informations du compte</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.name" type="text"
                        :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.email" type="email"
                        :class="form.errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Nouveau mot de passe</label>
                    <input v-model="form.password" type="password" autocomplete="new-password"
                        placeholder="Laisser vide pour ne pas changer"
                        :class="form.errors.password ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Confirmer le nouveau mot de passe</label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password"
                        placeholder="Laisser vide pour ne pas changer"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Rôle <span class="text-red-500">*</span>
                    </label>
                    <select v-model="form.role"
                        :class="form.errors.role ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                        <option value="customer">Client</option>
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.is_active" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700">Compte actif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions formulaire -->
        <div class="flex items-center justify-between gap-3">
            <a :href="route('admin.users.show', user.id)"
                class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button @click="submit" :disabled="form.processing"
                class="h-9 px-6 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Mettre à jour
            </button>
        </div>

        <!-- Zone de danger -->
        <div v-if="!user.is_current_user" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-1">Zone de danger</h2>
            <p class="text-xs text-gray-500 mb-4">La suppression de l'utilisateur est définitive et irréversible.</p>

            <div v-if="!showDeleteConfirm">
                <button @click="showDeleteConfirm = true" type="button"
                    class="h-9 px-4 border border-red-200 text-[13px] font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    Supprimer l'utilisateur
                </button>
            </div>
            <div v-else class="flex items-center gap-3">
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
