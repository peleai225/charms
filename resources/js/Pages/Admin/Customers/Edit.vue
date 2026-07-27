<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    customer: Object,
})

const form = useForm({
    first_name: props.customer.first_name,
    last_name:  props.customer.last_name,
    email:      props.customer.email,
    phone:      props.customer.phone ?? '',
    birth_date: props.customer.birth_date ?? '',
    gender:     props.customer.gender ?? '',
    status:     props.customer.status,
    notes:      props.customer.notes ?? '',
})

function submit() {
    form.put(route('admin.customers.update', props.customer.id))
}

function destroy() {
    if (!confirm(`Supprimer le client ${props.customer.full_name} ? Cette action est irréversible.`)) return
    useForm({}).delete(route('admin.customers.destroy', props.customer.id))
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-3xl">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.customers.show', customer.id)"
                class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">{{ customer.full_name }}</h1>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <!-- Informations personnelles -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Informations personnelles</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Prénom <span class="text-red-500">*</span></label>
                    <input v-model="form.first_name" type="text"
                        :class="form.errors.first_name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-orange-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-600">{{ form.errors.first_name }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Nom <span class="text-red-500">*</span></label>
                    <input v-model="form.last_name" type="text"
                        :class="form.errors.last_name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-orange-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-600">{{ form.errors.last_name }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input v-model="form.email" type="email"
                        :class="form.errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-orange-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Téléphone</label>
                    <input v-model="form.phone" type="tel"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Date de naissance</label>
                    <input v-model="form.birth_date" type="date"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 block mb-1.5">Genre</label>
                    <select v-model="form.gender"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Non précisé</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statut -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900">Statut du compte</h2>
            <select v-model="form.status"
                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
                <option value="blocked">Bloqué</option>
            </select>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-2">
            <h2 class="text-sm font-semibold text-gray-900">Notes internes</h2>
            <textarea v-model="form.notes" rows="3" placeholder="Notes visibles uniquement par l'équipe…"
                class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between gap-3">
            <button @click="destroy" type="button"
                class="h-9 px-4 text-[13px] font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                Supprimer le client
            </button>
            <div class="flex items-center gap-2">
                <a :href="route('admin.customers.show', customer.id)"
                    class="h-9 px-4 text-[13px] text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition flex items-center">
                    Annuler
                </a>
                <button @click="submit" :disabled="form.processing"
                    class="h-9 px-4 flex items-center gap-2 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-50">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Enregistrer
                </button>
            </div>
        </div>

    </div>
</template>
