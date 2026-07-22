<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import { ref } from 'vue';

const props = defineProps({
    addresses: Array,
});

const showForm = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    address: '',
    postal_code: '',
    city: '',
    country: 'CI',
    phone: '',
    is_default: false,
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
</script>

<template>
    <FrontLayout title="Mes adresses">
        <Head title="Mes adresses" />

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="container mx-auto px-4 max-w-3xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Mes adresses</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Adresses de livraison enregistrées</p>
                    </div>
                    <Link href="/mon-compte" class="text-sm text-slate-500 hover:text-slate-700 transition">← Mon compte</Link>
                </div>

                <!-- Addresses grid -->
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div
                        v-for="address in addresses"
                        :key="address.id"
                        class="bg-white rounded-xl border p-5 relative"
                        :class="address.is_default ? 'border-blue-200 ring-1 ring-blue-100' : 'border-slate-200'"
                    >
                        <div v-if="address.is_default" class="absolute top-3 right-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                Par défaut
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-900 mb-1 pr-20">{{ address.first_name }} {{ address.last_name }}</p>
                        <p class="text-sm text-slate-500">{{ address.address_line1 }}</p>
                        <p class="text-sm text-slate-500">{{ address.postal_code }} {{ address.city }}</p>
                        <p v-if="address.phone" class="text-sm text-slate-500">{{ address.phone }}</p>

                        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100">
                            <button
                                @click="deleteAddress(address.id)"
                                class="text-xs text-red-600 hover:text-red-700 font-medium transition"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <!-- Add card -->
                    <button
                        v-if="!showForm"
                        @click="showForm = true"
                        class="bg-white rounded-xl border-2 border-dashed border-slate-300 hover:border-blue-400 hover:bg-blue-50/30 p-5 text-center transition group"
                    >
                        <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2 transition">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-600 group-hover:text-blue-700">Ajouter une adresse</p>
                    </button>
                </div>

                <!-- Form nouvelle adresse -->
                <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-semibold text-slate-900">Nouvelle adresse</h2>
                        <button @click="showForm = false; form.reset()" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <Input v-model="form.first_name" label="Prénom" required :error="form.errors.first_name" />
                        <Input v-model="form.last_name" label="Nom" required :error="form.errors.last_name" />
                        <div class="sm:col-span-2">
                            <Input v-model="form.address" label="Adresse" required :error="form.errors.address" />
                        </div>
                        <Input v-model="form.postal_code" label="Code postal" :error="form.errors.postal_code" />
                        <Input v-model="form.city" label="Ville" required :error="form.errors.city" />
                        <Input v-model="form.phone" label="Téléphone" type="tel" :error="form.errors.phone" />
                        <div class="sm:col-span-2 flex items-center gap-3">
                            <input
                                v-model="form.is_default"
                                id="is_default"
                                type="checkbox"
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="is_default" class="text-sm text-slate-700">Définir comme adresse par défaut</label>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <Button @click="submit" variant="primary" :loading="form.processing">
                            Enregistrer l'adresse
                        </Button>
                        <Button @click="showForm = false; form.reset()" variant="outline">
                            Annuler
                        </Button>
                    </div>
                </div>

                <!-- Empty state (aucune adresse + pas de form) -->
                <div v-if="addresses.length === 0 && !showForm" class="text-center py-4">
                    <p class="text-sm text-slate-500">Cliquez sur "Ajouter une adresse" pour commencer.</p>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
