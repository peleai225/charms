<script setup>
import { onMounted, onUnmounted } from 'vue'

// Choix de la taille / couleur vendue quand l'étiquette « parent » d'un produit à variantes est scannée :
// le stock décompté et le prix doivent être ceux de la variante, pas du produit.
const props = defineProps({
    picker: { type: Object, required: true }, // { name, product_id, variants: [{ variant_id, name, price, price_formatted, regular_price, stock }] }
})
const emit = defineEmits(['choose', 'close'])

const onKey = (e) => { if (e.key === 'Escape') emit('close') }
onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="emit('close')">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="variant-picker-title">
                <div class="flex justify-between items-start gap-3">
                    <h3 id="variant-picker-title" class="font-bold text-slate-900">{{ picker.name }}</h3>
                    <button type="button" aria-label="Fermer" @click="emit('close')"
                        class="w-11 h-11 -mt-2 -mr-2 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-slate-500 mb-4">Quelle taille ou couleur est vendue ? Vous pouvez aussi scanner son étiquette.</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-80 overflow-y-auto">
                    <button v-for="v in picker.variants" :key="v.variant_id" type="button" @click="emit('choose', v)"
                        class="min-h-11 rounded-xl border-2 px-3 py-2 text-left transition-colors"
                        :class="v.stock > 0 ? 'border-slate-200 hover:border-blue-500 hover:bg-blue-50' : 'border-slate-100 bg-slate-50'">
                        <span class="block text-sm font-bold text-slate-900">{{ v.name }}</span>
                        <span class="block text-xs font-semibold" :class="v.price < v.regular_price ? 'text-red-600' : 'text-slate-600'">{{ v.price_formatted }}</span>
                        <span class="block text-[11px]" :class="v.stock > 0 ? 'text-slate-400' : 'text-amber-600'">
                            {{ v.stock > 0 ? `Stock : ${v.stock}` : 'Rupture' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
