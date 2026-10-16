<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'

// Propose une catégorie par produit (IA), l'admin valide avant application.
const props = defineProps({
    products: { type: Array, required: true }, // [{ id, name, primary_image_url, category }]
    categories: { type: Array, required: true }, // [{ id, name }]
})
const emit = defineEmits(['close', 'done'])

const toast = useToast()
const rows = ref(props.products.map((p) => ({
    id: p.id,
    name: p.name,
    image: p.primary_image_url,
    current: p.category?.name ?? '—',
    categoryId: '',
    checked: false,
    status: 'pending', // pending | loading | done | error
    error: null,
})))
const running = ref(true)
const applying = ref(false)

const sleep = (ms) => new Promise((r) => setTimeout(r, ms))
const toApply = computed(() => rows.value.filter((r) => r.checked && r.categoryId))

async function run() {
    for (const row of rows.value) {
        if (!running.value) break
        row.status = 'loading'
        try {
            const { data } = await axios.post(route('admin.ai.products.suggest', row.id))
            if (data.category_id) {
                row.categoryId = data.category_id
                row.checked = data.category_id !== data.current_category_id
            }
            row.status = 'done'
        } catch (e) {
            row.status = 'error'
            row.error = e.response?.data?.message ?? 'Erreur'
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
    applying.value = true
    router.post(route('admin.ai.apply-categories'), {
        assignments: toApply.value.map((r) => ({ id: r.id, category_id: r.categoryId })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${toApply.value.length} produit(s) rangé(s)`)
            emit('done')
        },
        onError: () => toast.error('Erreur lors de l\'enregistrement'),
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
                <h2 class="text-xl font-semibold text-gray-900">Ranger avec l'IA</h2>
                <p class="text-sm text-gray-500 mt-1">
                    L'IA propose une catégorie pour chaque produit. Vérifiez, corrigez si besoin, puis appliquez.
                    <span v-if="running">Analyse en cours (quelques secondes par produit)…</span>
                </p>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                <div v-for="row in rows" :key="row.id" class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-3">
                    <div class="flex items-center gap-3 sm:w-64 min-w-0">
                        <input v-model="row.checked" type="checkbox" :disabled="!row.categoryId" class="w-5 h-5 rounded border-gray-300 flex-shrink-0">
                        <img v-if="row.image" :src="row.image" :alt="row.name" class="w-11 h-11 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ row.name }}</p>
                            <p class="text-xs text-gray-400 truncate">Actuelle : {{ row.current }}</p>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div v-if="row.status === 'pending' || row.status === 'loading'" class="h-11 rounded-lg bg-gray-100 animate-pulse"></div>
                        <template v-else>
                            <select v-model="row.categoryId" @change="row.checked = !!row.categoryId"
                                class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Choisir —</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="row.error" class="mt-1 text-xs text-red-600">{{ row.error }}</p>
                        </template>
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
                    {{ applying ? 'Enregistrement…' : `Appliquer (${toApply.length})` }}
                </button>
            </div>
        </div>
    </div>
</template>
