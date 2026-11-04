<script setup>
import { ref } from 'vue'
import { useHelpers } from '@/Composables/useHelpers'
import CourierFormModal from './Partials/CourierFormModal.vue'

defineProps({
    couriers: { type: Array, default: () => [] },
})

const { formatPrice } = useHelpers()
const editing = ref(null) // null = fermé, {} = nouveau, objet = modification
const open = (courier = {}) => { editing.value = courier }

function balanceText(balance) {
    if (balance > 0) return { text: `Doit ${formatPrice(balance)}`, cls: 'text-amber-700' }
    if (balance < 0) return { text: `On lui doit ${formatPrice(-balance)}`, cls: 'text-blue-700' }
    return { text: 'À jour', cls: 'text-green-700' }
}
</script>

<template>
    <div class="p-4 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Livreurs</h1>
                <p class="text-sm text-gray-500 mt-0.5">Qui transporte quoi, et qui doit combien.</p>
            </div>
            <button type="button" class="h-11 px-4 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700" @click="open()">
                Ajouter un livreur
            </button>
        </div>

        <!-- État vide -->
        <div v-if="!couriers.length" class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="text-base font-medium text-gray-800">Aucun livreur pour l'instant</p>
            <p class="text-sm text-gray-500 mt-1">Ajoutez vos livreurs pour leur confier des commandes et faire le point chaque soir.</p>
            <button type="button" class="mt-4 h-11 px-5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700" @click="open()">
                Ajouter mon premier livreur
            </button>
        </div>

        <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="c in couriers" :key="c.id" class="rounded-xl border bg-white p-4 shadow-sm space-y-3"
                :class="c.is_active ? 'border-gray-200' : 'border-gray-200 opacity-60'">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ c.name }}</p>
                        <p class="text-sm text-gray-500">{{ c.phone }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="c.type === 'company' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700'">
                        {{ c.type === 'company' ? 'Société' : 'Indépendant' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-lg bg-gray-50 p-2.5">
                        <p class="text-xs text-gray-500">En route</p>
                        <p class="font-semibold text-gray-900">{{ c.on_route_count }} colis</p>
                        <p v-if="c.on_route_to_collect" class="text-xs text-gray-600">{{ formatPrice(c.on_route_to_collect) }} à encaisser</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-2.5">
                        <p class="text-xs text-gray-500">Solde</p>
                        <p class="font-semibold" :class="balanceText(c.balance).cls">{{ balanceText(c.balance).text }}</p>
                    </div>
                </div>
                <p v-if="c.notes" class="text-xs text-gray-500">{{ c.notes }}</p>
                <p v-if="!c.is_active" class="text-xs text-gray-500">Inactif : ne reçoit plus de commandes.</p>
                <div class="flex flex-wrap gap-2">
                    <a :href="route('admin.couriers.show', c.id)"
                        class="h-11 px-4 inline-flex items-center text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Faire le point</a>
                    <button type="button" class="h-11 px-3 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" @click="open(c)">Modifier</button>
                </div>
            </div>
        </div>

        <CourierFormModal v-if="editing" :courier="editing.id ? editing : null" @close="editing = null" />
    </div>
</template>
