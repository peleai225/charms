<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    addresses: Array,
});

const showForm = ref(false);

const form = useForm({
    first_name:  '',
    last_name:   '',
    address:     '',
    postal_code: '',
    city:        '',
    country:     'CI',
    phone:       '',
    is_default:  false,
});

const submit = () => {
    form.post('/mon-compte/adresses', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
};

const deleteAddress = (id) => {
    if (confirm('Supprimer cette adresse ?')) {
        router.delete(`/mon-compte/adresses/${id}`, { preserveScroll: true });
    }
};

const field = (model, label, required = false, type = 'text', span2 = false) =>
    ({ model, label, required, type, span2 });

const fields = [
    field('first_name', 'Prénom', true),
    field('last_name',  'Nom',    true),
    field('address',    'Adresse complète', true, 'text', true),
    field('postal_code','Code postal'),
    field('city',       'Ville', true),
    field('phone',      'Téléphone', false, 'tel'),
];
</script>

<template>
    <AccountLayout title="Mes adresses">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Mes adresses</h1>
                <p class="text-sm text-slate-500 mt-0.5">Adresses de livraison enregistrées</p>
            </div>
            <button
                v-if="!showForm"
                @click="showForm = true"
                class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter
            </button>
        </div>

        <!-- Grille adresses -->
        <div class="grid sm:grid-cols-2 gap-4 mb-5">
            <div
                v-for="address in addresses"
                :key="address.id"
                class="bg-white rounded-xl border p-5 relative"
                :class="address.is_default ? 'border-primary-200 ring-1 ring-primary-100' : 'border-slate-200'"
            >
                <div v-if="address.is_default" class="absolute top-3 right-3">
                    <span class="text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-200 px-2 py-0.5 rounded-full">Par défaut</span>
                </div>
                <p class="text-sm font-semibold text-slate-900 pr-20 mb-1">{{ address.first_name }} {{ address.last_name }}</p>
                <p class="text-sm text-slate-500 leading-relaxed">{{ address.address }}</p>
                <p class="text-sm text-slate-500">{{ address.postal_code ? address.postal_code + ' ' : '' }}{{ address.city }}</p>
                <p v-if="address.phone" class="text-sm text-slate-500 mt-0.5">{{ address.phone }}</p>

                <div class="flex items-center gap-4 mt-4 pt-3 border-t border-slate-100">
                    <button @click="deleteAddress(address.id)" class="text-xs text-red-500 hover:text-red-700 font-medium transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Card "ajouter" -->
            <button
                v-if="!showForm && addresses.length < 5"
                @click="showForm = true"
                class="bg-white rounded-xl border-2 border-dashed border-slate-300 hover:border-slate-400 hover:bg-slate-50 p-5 text-center transition group min-h-[120px] flex flex-col items-center justify-center gap-2"
            >
                <div class="w-10 h-10 bg-slate-100 group-hover:bg-slate-200 rounded-full flex items-center justify-center transition">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 group-hover:text-slate-700">Ajouter une adresse</p>
            </button>
        </div>

        <!-- État vide -->
        <div v-if="addresses.length === 0 && !showForm" class="text-center py-4">
            <p class="text-sm text-slate-500">Cliquez sur "Ajouter" pour enregistrer votre première adresse.</p>
        </div>

        <!-- Formulaire nouvelle adresse -->
        <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold text-slate-900">Nouvelle adresse</h2>
                <button @click="showForm = false; form.reset()" class="text-slate-400 hover:text-slate-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div v-for="f in fields" :key="f.model" :class="f.span2 ? 'sm:col-span-2' : ''">
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        {{ f.label }} <span v-if="f.required" class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form[f.model]"
                        :type="f.type"
                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                        :class="form.errors[f.model] ? 'border-red-400' : 'border-slate-200'"
                    />
                    <p v-if="form.errors[f.model]" class="text-xs text-red-600 mt-1">{{ form.errors[f.model] }}</p>
                </div>

                <div class="sm:col-span-2 flex items-center gap-3">
                    <input v-model="form.is_default" id="is_default" type="checkbox"
                        class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500 cursor-pointer" />
                    <label for="is_default" class="text-sm text-slate-700 cursor-pointer select-none">Définir comme adresse par défaut</label>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-5 pt-4 border-t border-slate-100">
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 disabled:opacity-50 transition flex items-center gap-2"
                >
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer l\'adresse' }}
                </button>
                <button @click="showForm = false; form.reset()" class="px-5 py-2.5 border border-slate-200 text-sm font-medium text-slate-700 rounded-xl hover:bg-slate-50 transition">
                    Annuler
                </button>
            </div>
        </div>
    </AccountLayout>
</template>
