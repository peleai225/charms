<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const shippingZones = ref(
    (() => {
        try { return JSON.parse(props.settings.shipping_zones ?? '[]') || [] }
        catch { return [] }
    })()
)

const form = useForm({
    shipping_enabled:        props.settings.shipping_enabled !== '0',
    free_shipping_threshold: props.settings.free_shipping_threshold ?? '',
    flat_rate_shipping:      props.settings.flat_rate_shipping ?? '',
    shipping_zones:          shippingZones.value,
})

function addZone() {
    shippingZones.value.push({ name: '', cities: '', price: '' })
}

function removeZone(i) {
    shippingZones.value.splice(i, 1)
}

function submit() {
    form.shipping_zones = shippingZones.value
    form.post(route('admin.settings.shipping.update'), { preserveScroll: true })
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Options de livraison</h3>
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input v-model="form.shipping_enabled" type="checkbox"
                        class="w-4 h-4 text-blue-600 rounded border-gray-300">
                    <span class="text-[13px] text-gray-700">Activer la livraison</span>
                </label>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Livraison gratuite à partir de</label>
                        <div class="relative">
                            <input v-model="form.free_shipping_threshold" type="number" step="100" min="0" placeholder="Ex: 50000"
                                class="w-full h-9 pl-3 pr-16 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-gray-400">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Tarif forfaitaire</label>
                        <div class="relative">
                            <input v-model="form.flat_rate_shipping" type="number" step="100" min="0" placeholder="Ex: 2000"
                                class="w-full h-9 pl-3 pr-16 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-gray-400">F CFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-semibold text-gray-900">Zones de livraison</h3>
                <button type="button" @click="addZone"
                    class="h-7 px-3 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    + Ajouter
                </button>
            </div>
            <div class="space-y-3">
                <p v-if="shippingZones.length === 0" class="text-[13px] text-gray-400">
                    Aucune zone configurée. Utilisez le tarif forfaitaire ou ajoutez des zones.
                </p>
                <div v-for="(zone, i) in shippingZones" :key="i" class="p-4 border border-gray-200 rounded-xl">
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[12px] font-medium text-gray-700 mb-1">Nom de la zone</label>
                            <input v-model="zone.name" type="text" placeholder="Ex: Abidjan"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-gray-700 mb-1">Villes (séparées par virgule)</label>
                            <input v-model="zone.cities" type="text" placeholder="Ex: Cocody, Plateau"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="block text-[12px] font-medium text-gray-700 mb-1">Prix (F CFA)</label>
                                <input v-model="zone.price" type="number"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600">
                            </div>
                            <button type="button" @click="removeZone(i)"
                                class="self-end h-9 w-9 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit"
            :disabled="form.processing"
            class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors disabled:opacity-60">
            <span v-if="form.processing">Enregistrement…</span>
            <span v-else>Enregistrer</span>
        </button>
    </form>
</template>
