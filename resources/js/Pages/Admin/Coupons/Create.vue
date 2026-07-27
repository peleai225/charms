<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    categories: Array,
    products:   Array,
})

const form = useForm({
    code:                  '',
    name:                  '',
    description:           '',
    type:                  'percentage',
    value:                 '',
    min_order_amount:      '',
    max_discount_amount:   '',
    usage_limit:           '',
    usage_limit_per_user:  '',
    starts_at:             '',
    expires_at:            '',
    is_active:             true,
    first_order_only:      false,
})

const showValueField = computed(() => form.type !== 'free_shipping')

async function generateCode() {
    try {
        const r = await fetch(route('admin.coupons.generate-code'))
        const data = await r.json()
        form.code = data.code ?? ''
    } catch (e) {
        console.error('Erreur génération code', e)
    }
}

function submit() {
    form.post(route('admin.coupons.store'))
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.coupons.index')"
                class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">Nouveau code promo</h1>
        </div>

        <!-- Erreurs globales -->
        <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <div class="grid lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Informations</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Code <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input v-model="form.code" type="text"
                                    :class="form.errors.code ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                    class="flex-1 h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 uppercase font-mono">
                                <button type="button" @click="generateCode"
                                    class="h-9 px-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                                    title="Générer un code aléatoire">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Nom <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text"
                                :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 block mb-1.5">Description</label>
                        <textarea v-model="form.description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <!-- Réduction -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Réduction</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Type <span class="text-red-500">*</span></label>
                            <select v-model="form.type"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="percentage">Pourcentage (%)</option>
                                <option value="fixed">Montant fixe</option>
                                <option value="free_shipping">Livraison gratuite</option>
                            </select>
                            <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                        </div>
                        <div v-show="showValueField">
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">
                                Valeur <span class="text-red-500">*</span>
                                <span class="text-gray-400 font-normal ml-1">{{ form.type === 'percentage' ? '(%)' : '(F)' }}</span>
                            </label>
                            <input v-model="form.value" type="number" step="0.01" min="0"
                                :class="form.errors.value ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                            <p v-if="form.errors.value" class="mt-1 text-xs text-red-600">{{ form.errors.value }}</p>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Montant min. de commande</label>
                            <input v-model="form.min_order_amount" type="number" step="100" min="0" placeholder="Illimité"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p v-if="form.errors.min_order_amount" class="mt-1 text-xs text-red-600">{{ form.errors.min_order_amount }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Réduction maximale</label>
                            <input v-model="form.max_discount_amount" type="number" step="100" min="0" placeholder="Illimitée"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p v-if="form.errors.max_discount_amount" class="mt-1 text-xs text-red-600">{{ form.errors.max_discount_amount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Limites d'utilisation -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Limites d'utilisation</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Limite totale</label>
                            <input v-model="form.usage_limit" type="number" min="1" placeholder="Illimitée"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Limite par client</label>
                            <input v-model="form.usage_limit_per_user" type="number" min="1" placeholder="Illimitée"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-5">

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3">
                    <button @click="submit" :disabled="form.processing" type="button"
                        class="w-full h-9 flex items-center justify-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Créer le code promo
                    </button>
                    <a :href="route('admin.coupons.index')"
                        class="block text-center text-xs text-gray-500 hover:text-gray-700 transition">Annuler</a>
                </div>

                <!-- Validité -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Validité</h2>
                    <div>
                        <label class="text-xs font-medium text-gray-700 block mb-1.5">Date de début</label>
                        <input v-model="form.starts_at" type="date"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 block mb-1.5">Date d'expiration</label>
                        <input v-model="form.expires_at" type="date"
                            :class="form.errors.expires_at ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                            class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                        <p v-if="form.errors.expires_at" class="mt-1 text-xs text-red-600">{{ form.errors.expires_at }}</p>
                    </div>
                </div>

                <!-- Options -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Options</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input v-model="form.is_active" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700">Actif</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input v-model="form.first_order_only" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700">Première commande uniquement</span>
                    </label>
                </div>

            </div>
        </div>
    </div>
</template>
