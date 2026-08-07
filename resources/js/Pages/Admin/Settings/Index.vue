<script setup>
import { ref } from 'vue'
import TabGeneral       from './Partials/TabGeneral.vue'
import TabShipping      from './Partials/TabShipping.vue'
import TabPayment       from './Partials/TabPayment.vue'
import TabEmails        from './Partials/TabEmails.vue'
import TabPos           from './Partials/TabPos.vue'
import TabNotifications from './Partials/TabNotifications.vue'

const props = defineProps({
    settings:  Object,
    activeTab: { type: String, default: 'general' },
})

const tab = ref(props.activeTab)

const tabs = [
    { key: 'general',  label: 'Général' },
    { key: 'shipping', label: 'Livraison' },
    { key: 'payment',  label: 'Paiement' },
    { key: 'emails',   label: 'Emails' },
    { key: 'pos',           label: 'Caisse / Impression' },
    { key: 'notifications', label: 'Notifications' },
]
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Paramètres</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Configurez votre boutique</p>
        </div>

        <!-- Tab nav -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-1.5 flex flex-wrap gap-1">
            <button v-for="t in tabs" :key="t.key"
                @click="tab = t.key"
                :class="tab === t.key
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                class="px-4 py-2 font-semibold text-[13px] rounded-lg transition-all">
                {{ t.label }}
            </button>
        </div>

        <!-- Tab panels -->
        <TabGeneral  v-show="tab === 'general'"  :settings="settings" />
        <TabShipping v-show="tab === 'shipping'" :settings="settings" />
        <TabPayment  v-show="tab === 'payment'"  :settings="settings" />
        <TabEmails   v-show="tab === 'emails'"   :settings="settings" />
        <TabPos           v-show="tab === 'pos'"           :settings="settings" />
        <TabNotifications v-show="tab === 'notifications'" :settings="settings" />

    </div>
</template>
