<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
    messages: Object,
    stats:    Object,
})

const STATUS_CLASSES = {
    delivered: 'bg-green-50 text-green-700',
    sent:      'bg-blue-50 text-blue-700',
    failed:    'bg-red-50 text-red-700',
    pending:   'bg-gray-100 text-gray-500',
    read:      'bg-teal-50 text-teal-700',
}
const STATUS_LABELS = {
    delivered: 'Délivré', sent: 'Envoyé', failed: 'Échoué', pending: 'En attente', read: 'Lu',
}

function fmtType(t) {
    return (t ?? '').replace(/_/g, ' ')
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Historique WhatsApp</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Tous les messages envoyés via WhatsApp</p>
            </div>
            <a :href="route('admin.marketing.campaigns')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                ← Campagnes
            </a>
        </div>

        <!-- KPI Strip -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div v-for="[label, val] in [
                    ['Total messages', stats?.total ?? 0],
                    ['Envoyés', stats?.sent ?? 0],
                    ['Délivrés', stats?.delivered ?? 0],
                    ['En attente', stats?.pending ?? 0],
                ]" :key="label" class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ label }}</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ Number(val).toLocaleString('fr-FR') }}</p>
                </div>
            </div>
        </div>

        <!-- Tableau messages -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[13px] font-semibold text-gray-900">Messages</h3>
            </div>

            <div v-if="!messages?.data?.length" class="py-16 text-center">
                <p class="text-[13px] text-gray-400">Aucun message WhatsApp enregistré</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Destinataire</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Message</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="msg in messages.data" :key="msg.id"
                                class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-[13px] font-medium text-gray-900">{{ msg.customer_name ?? msg.phone }}</p>
                                            <p class="text-[11px] text-gray-400">{{ msg.phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 max-w-xs">
                                    <p class="text-[13px] text-gray-600 line-clamp-2">{{ msg.message }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-500">
                                        {{ fmtType(msg.type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="STATUS_CLASSES[msg.status] ?? 'bg-gray-100 text-gray-500'"
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded">
                                        {{ STATUS_LABELS[msg.status] ?? msg.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-[11px] text-gray-400 whitespace-nowrap">
                                    {{ msg.created_at_fmt }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="messages?.last_page > 1"
                    class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                    <p class="text-xs text-gray-500">
                        {{ messages.from }}–{{ messages.to }} sur {{ messages.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <a v-for="link in messages.links" :key="link.label"
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
        </div>

    </div>
</template>
