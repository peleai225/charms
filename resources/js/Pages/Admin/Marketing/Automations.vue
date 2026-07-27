<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    automations: Array,
})

const showForm = ref(false)

const form = useForm({
    name:             '',
    trigger:          'abandoned_cart',
    channel:          'whatsapp',
    message_template: '',
    delay_hours:      1,
    is_active:        true,
})

function submit() {
    form.post(route('admin.marketing.automations.store'), {
        onSuccess: () => {
            form.reset({ trigger: 'abandoned_cart', channel: 'whatsapp', delay_hours: 1, is_active: true })
            showForm.value = false
        },
    })
}

function toggle(automation) {
    router.post(route('admin.marketing.automations.toggle', automation.id))
}

const confirmDeleteId = ref(null)
function deleteAutomation(id) { confirmDeleteId.value = id }
function confirmDelete() {
    router.delete(route('admin.marketing.automations.destroy', confirmDeleteId.value), {
        onFinish: () => { confirmDeleteId.value = null },
    })
}

const TRIGGER_LABELS = {
    abandoned_cart:    'Panier abandonné',
    post_purchase:     'Après achat',
    post_delivery:     'Après livraison',
    inactive_customer: 'Client inactif',
    birthday:          'Anniversaire',
    loyalty_milestone: 'Palier fidélité',
    new_customer:      'Nouveau client',
    vip_upgrade:       'Passage VIP',
    custom:            'Personnalisé',
}

const CHANNEL_LABELS = {
    whatsapp: 'WhatsApp', email: 'Email', push: 'Push', sms: 'SMS',
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Automatisations</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Messages envoyés automatiquement selon des déclencheurs</p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('admin.marketing.campaigns')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                    ← Campagnes
                </a>
                <button @click="showForm = !showForm"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle automatisation
                </button>
            </div>
        </div>

        <!-- Formulaire -->
        <div v-if="showForm" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouvelle Automatisation</h3>

            <div v-if="form.hasErrors" class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-[13px] text-red-700">
                <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" required placeholder="Relance panier…"
                            :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                            class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Déclencheur</label>
                        <select v-model="form.trigger"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option v-for="(label, val) in TRIGGER_LABELS" :key="val" :value="val">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Canal</label>
                        <select v-model="form.channel"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                            <option value="push">Push Notification</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Délai (heures)</label>
                        <input v-model.number="form.delay_hours" type="number" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-400 mt-1">0 = immédiat</p>
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-gray-700 font-medium">Activer immédiatement</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message <span class="text-red-500">*</span></label>
                    <textarea v-model="form.message_template" rows="3" required
                        placeholder="Bonjour {prenom} ! Votre panier vous attend…"
                        :class="form.errors.message_template ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full px-3 py-2 text-[13px] border rounded-lg focus:outline-none focus:ring-2 resize-none"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Variables : {prenom}, {nom}, {total_depense}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="showForm = false"
                        class="h-9 px-4 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                        Annuler
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 inline-flex items-center gap-2">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Créer l'automatisation
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[13px] font-semibold text-gray-900">
                    Automatisations <span class="text-gray-400 font-normal">({{ automations?.length ?? 0 }})</span>
                </h3>
            </div>

            <div v-if="!automations?.length" class="py-16 text-center">
                <p class="text-[13px] text-gray-400 mb-3">Aucune automatisation configurée</p>
                <button @click="showForm = true"
                    class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Créer la première automatisation
                </button>
            </div>

            <div v-else class="divide-y divide-gray-50">
                <div v-for="auto in automations" :key="auto.id"
                    class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                            :class="auto.is_active ? 'bg-green-500' : 'bg-gray-300'">
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-medium text-gray-900">{{ auto.name }}</p>
                            <p class="text-[11px] text-gray-400">
                                {{ TRIGGER_LABELS[auto.trigger] ?? auto.trigger }}
                                · {{ CHANNEL_LABELS[auto.channel] ?? auto.channel }}
                                · Délai : {{ auto.delay_hours }}h
                                · {{ auto.sent_count ?? 0 }} envoyés
                                <template v-if="auto.conversion_rate > 0">
                                    · {{ auto.conversion_rate }}% conversion
                                </template>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="toggle(auto)"
                            class="h-7 px-3 text-[12px] font-medium rounded-lg transition-colors"
                            :class="auto.is_active
                                ? 'bg-green-50 text-green-700 hover:bg-green-100'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            {{ auto.is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                        <button @click="deleteAutomation(auto.id)"
                            class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal confirmation suppression -->
        <Teleport to="body">
            <div v-if="confirmDeleteId !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="confirmDeleteId = null">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
                    <h3 class="text-[15px] font-semibold text-gray-900 mb-2">Supprimer l'automatisation ?</h3>
                    <p class="text-[13px] text-gray-500 mb-5">Cette action est irréversible.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="confirmDeleteId = null"
                            class="h-9 px-4 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            Annuler
                        </button>
                        <button @click="confirmDelete"
                            class="h-9 px-4 text-[13px] font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
