<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'

// Propose une accroche + une description par produit (IA), l'admin relit avant enregistrement.
const props = defineProps({
    products: { type: Array, required: true }, // [{ id, name, primary_image_url, short_description, description }]
})
const emit = defineEmits(['close', 'done'])

const toast = useToast()
const isEmpty = (v) => !v || !String(v).trim()

const rows = ref(props.products.map((p) => ({
    id: p.id,
    name: p.name,
    image: p.primary_image_url,
    currentShort: p.short_description ?? '',
    incomplete: isEmpty(p.short_description) || isEmpty(p.description),
    short: p.short_description ?? '',
    long: p.description ?? '',
    checked: false,
    status: 'pending', // pending | loading | done | error
    error: null,
})))
const running = ref(true)
const applying = ref(false)

const sleep = (ms) => new Promise((r) => setTimeout(r, ms))
const toApply = computed(() => rows.value.filter((r) => r.checked && r.status === 'done'))

async function run() {
    for (const row of rows.value) {
        if (!running.value) break
        row.status = 'loading'
        try {
            const { data } = await axios.post(route('admin.ai.products.suggest', row.id))
            if (data.short_description) row.short = data.short_description
            if (data.description) row.long = data.description
            row.checked = row.incomplete
            row.status = 'done'
        } catch (e) {
            row.status = 'error'
            row.error = e.response?.data?.message ?? "L'IA n'a pas pu analyser ce produit."
            if (e.response?.status === 429) await sleep(30000)
        }
        await sleep(4000) // quotas gratuits
    }
    running.value = false
}

function stop() {
    running.value = false
}

function apply() {
    if (!toApply.value.length) return
    const count = toApply.value.length
    applying.value = true
    router.post(route('admin.ai.apply-descriptions'), {
        updates: toApply.value.map((r) => ({ id: r.id, short_description: r.short.trim(), description: r.long.trim() })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${count} fiche(s) complétée(s)`)
            emit('done')
        },
        onError: () => toast.error("Erreur lors de l'enregistrement"),
        onFinish: () => { applying.value = false },
    })
}

run()
onBeforeUnmount(() => { running.value = false })
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
        <div class="w-full max-w-3xl max-h-[90vh] flex flex-col rounded-xl bg-white shadow-xl">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">Compléter les fiches avec l'IA</h2>
                <p class="text-sm text-gray-500 mt-1">
                    L'IA rédige une accroche et une description à partir de la photo et du nom. Relisez, corrigez si besoin, puis enregistrez.
                    Seules les fiches cochées sont modifiées.
                    <span v-if="running">Analyse en cours (quelques secondes par produit)…</span>
                </p>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                <div v-for="row in rows" :key="row.id" class="px-5 py-4 space-y-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <input v-model="row.checked" type="checkbox" :disabled="row.status !== 'done'"
                            :aria-label="`Enregistrer la fiche de ${row.name}`"
                            class="w-5 h-5 rounded border-gray-300 flex-shrink-0">
                        <img v-if="row.image" :src="row.image" :alt="row.name" class="w-11 h-11 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ row.name }}</p>
                            <p class="text-xs text-gray-400 truncate">
                                Accroche actuelle : {{ row.currentShort.trim() ? row.currentShort : 'vide' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="row.status === 'pending' || row.status === 'loading'" class="space-y-2">
                        <div class="h-11 rounded-lg bg-gray-100 animate-pulse"></div>
                        <div class="h-20 rounded-lg bg-gray-100 animate-pulse"></div>
                    </div>
                    <p v-else-if="row.status === 'error'" class="text-xs text-red-600">{{ row.error }}</p>
                    <div v-else class="space-y-2">
                        <label class="block">
                            <span class="block text-xs font-medium text-gray-700 mb-1">Accroche</span>
                            <input v-model="row.short" type="text" maxlength="500" @input="row.checked = true"
                                class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-medium text-gray-700 mb-1">Description</span>
                            <textarea v-model="row.long" rows="3" @input="row.checked = true"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 p-4 border-t border-gray-100">
                <button v-if="running" type="button" class="h-11 px-4 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" @click="stop">
                    Arrêter l'analyse
                </button>
                <button type="button" class="h-11 px-4 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" @click="emit('close')">
                    Fermer
                </button>
                <button type="button" :disabled="!toApply.length || applying"
                    class="h-11 px-5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed"
                    @click="apply">
                    {{ applying ? 'Enregistrement…' : `Enregistrer (${toApply.length})` }}
                </button>
            </div>
        </div>
    </div>
</template>
