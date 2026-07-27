<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    supplier: Object,
})

const form = useForm({
    name:          props.supplier.name,
    code:          props.supplier.code ?? '',
    email:         props.supplier.email ?? '',
    phone:         props.supplier.phone ?? '',
    address:       props.supplier.address ?? '',
    city:          props.supplier.city ?? '',
    postal_code:   props.supplier.postal_code ?? '',
    country:       props.supplier.country ?? '',
    contact_name:  props.supplier.contact_name ?? '',
    payment_terms: props.supplier.payment_terms ?? 30,
    notes:         props.supplier.notes ?? '',
    is_active:     props.supplier.is_active,
})

function submit() {
    form.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('admin.suppliers.update', props.supplier.id))
}

function destroy() {
    if (!confirm(`Supprimer le fournisseur "${props.supplier.name}" ? Cette action est irréversible.`)) return
    useForm({}).delete(route('admin.suppliers.destroy', props.supplier.id))
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-4xl">

        <!-- Header -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a :href="route('admin.suppliers.show', supplier.id)"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-xl font-bold text-gray-900">{{ supplier.name }}</h1>
            </div>
            <button @click="destroy" type="button"
                class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Supprimer
            </button>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations générales -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Informations générales</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Nom du fournisseur <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text"
                                :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Code fournisseur</label>
                            <input v-model="form.code" type="text"
                                :class="form.errors.code ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                            <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Email</label>
                            <input v-model="form.email" type="email"
                                :class="form.errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Téléphone</label>
                            <input v-model="form.phone" type="tel"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Nom du contact</label>
                            <input v-model="form.contact_name" type="text"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Délai de paiement (jours)</label>
                            <input v-model.number="form.payment_terms" type="number" min="0"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Adresse -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Adresse</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Adresse</label>
                            <textarea v-model="form.address" rows="2"
                                class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Ville</label>
                            <input v-model="form.city" type="text"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Code postal</label>
                            <input v-model="form.postal_code" type="text"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Pays</label>
                            <input v-model="form.country" type="text"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-2">
                    <h2 class="text-sm font-semibold text-gray-900">Notes internes</h2>
                    <textarea v-model="form.notes" rows="3" placeholder="Notes visibles uniquement par l'équipe…"
                        class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Statut</h2>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input v-model="form.is_active" type="checkbox"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700">Fournisseur actif</span>
                    </label>
                    <button @click="submit" :disabled="form.processing"
                        class="w-full h-9 flex items-center justify-center bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Enregistrer les modifications
                    </button>
                    <a :href="route('admin.suppliers.show', supplier.id)"
                        class="block text-center text-xs text-gray-500 hover:text-gray-700 transition">Annuler</a>
                </div>
            </div>

        </div>
    </div>
</template>
