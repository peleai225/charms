<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    campaigns:  Object,
    tags:       Array,
    stats:      Object,
})

const showForm = ref(false)

const form = useForm({
    name:             '',
    type:             'whatsapp',
    message_template: '',
    target_tags:      [],
    scheduled_at:     '',
})

function submit() {
    form.post(route('admin.marketing.campaigns.store'), {
        onSuccess: () => {
            form.reset()
            showForm.value = false
        },
    })
}

const confirmDeleteId = ref(null)
function deleteCampaign(id) { confirmDeleteId.value = id }
function confirmDelete() {
    router.delete(route('admin.marketing.campaigns.destroy', confirmDeleteId.value), {
        onFinish: () => { confirmDeleteId.value = null },
    })
}

const TYPE_CLASSES = {
    whatsapp: 'bg-green-50 text-green-700',
    email:    'bg-blue-50 text-blue-700',
    push:     'bg-purple-50 text-purple-700',
    sms:      'bg-gray-100 text-gray-600',
}
const TYPE_LABELS = {
    whatsapp: 'WhatsApp', email: 'Email', push: 'Push', sms: 'SMS',
}
const STATUS_CLASSES = {
    active:    'bg-green-50 text-green-700',
    draft:     'bg-gray-100 text-gray-600',
    completed: 'bg-blue-50 text-blue-700',
    scheduled: 'bg-amber-50 text-amber-700',
    failed:    'bg-red-50 text-red-600',
}
const STATUS_LABELS = {
    active: 'Actif', draft: 'Brouillon', completed: 'Terminé',
    scheduled: 'Planifié', failed: 'Échoué',
}

function toggleTag(id) {
    const idx = form.target_tags.indexOf(id)
    if (idx === -1) form.target_tags.push(id)
    else form.target_tags.splice(idx, 1)
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Campagnes Marketing</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Créez et gérez vos campagnes clients</p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('admin.marketing.whatsapp-history')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                    WhatsApp
                </a>
                <a :href="route('admin.marketing.automations')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                    Automatisations →
                </a>
                <button @click="showForm = !showForm"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle campagne
                </button>
            </div>
        </div>

        <!-- KPI Strip -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div v-for="[label, val] in [
                    ['Campagnes', stats?.total ?? campaigns?.total ?? 0],
                    ['Actives', stats?.active ?? 0],
                    ['Messages envoyés', stats?.sent ?? 0],
                    ['Délivrés', stats?.delivered ?? 0],
                ]" :key="label" class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ label }}</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ Number(val).toLocaleString('fr-FR') }}</p>
                </div>
            </div>
        </div>

        <!-- Formulaire création -->
        <div v-if="showForm" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouvelle Campagne</h3>

            <div v-if="form.hasErrors" class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-[13px] text-red-700">
                <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" required placeholder="Promo Noël 2026…"
                            :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                            class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Canal</label>
                        <select v-model="form.type"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                            <option value="push">Push Notification</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                </div>

                <div v-if="tags?.length">
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2 block">
                        Cibler les tags <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="tag in tags" :key="tag.id"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                            :class="form.target_tags.includes(tag.id)
                                ? 'border-blue-400 bg-blue-50'
                                : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/50'">
                            <input type="checkbox" :value="tag.id" :checked="form.target_tags.includes(tag.id)"
                                @change="toggleTag(tag.id)" class="sr-only">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ background: tag.color }"></span>
                            <span class="text-[12px] font-medium text-gray-700">{{ tag.name }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message <span class="text-red-500">*</span></label>
                    <textarea v-model="form.message_template" rows="4" required
                        placeholder="Bonjour {prenom}, profitez de nos offres…"
                        :class="form.errors.message_template ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full px-3 py-2 text-[13px] border rounded-lg focus:outline-none focus:ring-2 resize-none"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Variables : {prenom}, {nom}, {total_depense}, {nb_commandes}</p>
                </div>

                <div class="flex items-end gap-4 flex-wrap">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Programmer <span class="text-gray-400 font-normal">(optionnel)</span></label>
                        <input v-model="form.scheduled_at" type="datetime-local"
                            class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            Créer la campagne
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Liste campagnes -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[13px] font-semibold text-gray-900">
                    Campagnes <span class="text-gray-400 font-normal">({{ campaigns?.total ?? 0 }})</span>
                </h3>
            </div>

            <div v-if="!campaigns?.data?.length" class="py-16 text-center">
                <p class="text-[13px] text-gray-400 mb-3">Aucune campagne créée</p>
                <button @click="showForm = true"
                    class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Créer la première campagne
                </button>
            </div>

            <div v-else class="divide-y divide-gray-50">
                <div v-for="campaign in campaigns.data" :key="campaign.id"
                    class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-4 min-w-0">
                        <div>
                            <p class="text-[13px] font-medium text-gray-900">{{ campaign.name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span :class="TYPE_CLASSES[campaign.type] ?? 'bg-gray-100 text-gray-500'"
                                    class="text-[10px] font-semibold px-1.5 py-0.5 rounded">
                                    {{ TYPE_LABELS[campaign.type] ?? campaign.type }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ campaign.recipients_count }} destinataires · {{ campaign.sent_count }} envoyés
                                </span>
                                <span v-if="campaign.scheduled_at_fmt" class="text-[11px] text-gray-400">
                                    · {{ campaign.scheduled_at_fmt }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span :class="STATUS_CLASSES[campaign.status] ?? 'bg-gray-100 text-gray-500'"
                            class="text-[11px] font-semibold px-2 py-0.5 rounded-full">
                            {{ STATUS_LABELS[campaign.status] ?? campaign.status }}
                        </span>
                        <button @click="deleteCampaign(campaign.id)"
                            class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="campaigns?.last_page > 1"
                class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                <p class="text-xs text-gray-500">
                    {{ campaigns.from }}–{{ campaigns.to }} sur {{ campaigns.total }}
                </p>
                <div class="flex items-center gap-1">
                    <a v-for="link in campaigns.links" :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-lg border transition',
                            link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50',
                            !link.url ? 'opacity-40 pointer-events-none' : '',
                        ]"
                        v-html="link.label"
                        @click.prevent="link.url && router.visit(link.url, { preserveState: true })"
                    />
                </div>
            </div>
        </div>

        <!-- Modal confirmation suppression -->
        <Teleport to="body">
            <div v-if="confirmDeleteId !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="confirmDeleteId = null">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
                    <h3 class="text-[15px] font-semibold text-gray-900 mb-2">Supprimer la campagne ?</h3>
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
