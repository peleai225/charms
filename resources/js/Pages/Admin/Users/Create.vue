<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    role:                  'staff',
    is_active:             true,
})

function submit() {
    form.post(route('admin.users.store'))
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-2xl">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.users.index')"
                class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Nouvel utilisateur</h1>
                <p class="text-xs text-gray-400">Créer un compte avec accès backoffice</p>
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
                    <input v-model="form.name" type="text" autocomplete="off"
                        :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.email" type="email" autocomplete="off"
                        :class="form.errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.password" type="password" autocomplete="new-password"
                        :class="form.errors.password ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password"
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
                        <option value="">— Choisir un rôle —</option>
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

        <!-- Note rôles -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
            <p class="text-xs font-semibold text-gray-700">Permissions par rôle</p>
            <div class="space-y-1.5">
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 ring-1 ring-red-100 flex-shrink-0 mt-0.5">Admin</span>
                    <span class="text-xs text-gray-500">Accès complet — gestion des utilisateurs, paramètres globaux, toutes les données.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-100 flex-shrink-0 mt-0.5">Manager</span>
                    <span class="text-xs text-gray-500">Gestion produits, commandes, clients et rapports. Ne peut pas gérer les utilisateurs.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 flex-shrink-0 mt-0.5">Staff</span>
                    <span class="text-xs text-gray-500">Consultation commandes et produits. Accès lecture seule sur la majorité des sections.</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button @click="submit" :disabled="form.processing"
                class="h-9 px-6 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Créer l'utilisateur
            </button>
            <a :href="route('admin.users.index')"
                class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Annuler
            </a>
        </div>

    </div>
</template>
