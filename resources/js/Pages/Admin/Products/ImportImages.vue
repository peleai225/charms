<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useToast } from '@/Composables/useToast'
import Breadcrumb from '@/Components/UI/Breadcrumb.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const props = defineProps({
    aiConfigured: Boolean,
    categories: Array,
})

const toast = useToast()
const items = ref([])
const status = ref('draft')
const useAi = ref(props.aiConfigured)
const analyzing = ref(false)
const creating = ref(false)
const fileInput = ref(null)
let nextId = 1

const MAX_SIZE = 5 * 1024 * 1024
const sleep = (ms) => new Promise((r) => setTimeout(r, ms))

const counts = computed(() => ({
    total: items.value.length,
    ready: items.value.filter((i) => i.status === 'ready').length,
    created: items.value.filter((i) => i.status === 'created').length,
    pending: items.value.filter((i) => ['pending', 'analyzing'].includes(i.status)).length,
}))

function addFiles(fileList) {
    for (const file of Array.from(fileList)) {
        if (!file.type.startsWith('image/')) continue
        if (file.size > MAX_SIZE) {
            toast.error(`« ${file.name} » dépasse 5 Mo`)
            continue
        }
        items.value.push({
            id: nextId++,
            file,
            preview: URL.createObjectURL(file),
            status: useAi.value ? 'pending' : 'ready',
            error: null,
            missing: false,
            editUrl: null,
            fields: {
                name: file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' '),
                short_description: '',
                description: '',
                category_id: '',
                sale_price: '',
                purchase_price: '',
                stock_quantity: '',
            },
        })
    }
    if (fileInput.value) fileInput.value.value = ''
    if (useAi.value) runAnalysis()
}

function onDrop(e) {
    addFiles(e.dataTransfer.files)
}

function removeItem(item) {
    URL.revokeObjectURL(item.preview)
    items.value = items.value.filter((i) => i.id !== item.id)
}

async function analyze(item, retry = true) {
    item.status = 'analyzing'
    item.error = null
    const data = new FormData()
    data.append('image', item.file)
    try {
        const { data: s } = await axios.post(route('admin.ai.analyze-image'), data)
        if (s.name) item.fields.name = s.name
        item.fields.short_description = s.short_description || item.fields.short_description
        item.fields.description = s.description || item.fields.description
        if (s.category_id) item.fields.category_id = s.category_id
        item.status = 'ready'
    } catch (e) {
        const code = e.response?.status
        if (code === 429 && retry) {
            item.error = 'Quota atteint, nouvelle tentative dans 30 s…'
            await sleep(30000)
            return analyze(item, false)
        }
        item.status = 'ready'
        item.error = e.response?.data?.message ?? "L'IA n'a pas pu analyser cette photo. Remplissez la fiche à la main."
    }
}

async function runAnalysis() {
    if (analyzing.value) return
    analyzing.value = true
    try {
        let item
        while ((item = items.value.find((i) => i.status === 'pending'))) {
            await analyze(item)
            await sleep(4000) // espacement pour rester dans les quotas gratuits
        }
    } finally {
        analyzing.value = false
    }
}

function reanalyze(item) {
    item.status = 'pending'
    runAnalysis()
}

async function createAll() {
    const toCreate = items.value.filter((i) => i.status === 'ready')
    let missing = 0
    toCreate.forEach((i) => {
        i.missing = !i.fields.name.trim() || i.fields.sale_price === ''
        if (i.missing) missing++
    })
    if (missing) {
        toast.error(`${missing} produit(s) sans nom ou sans prix de vente`)
        return
    }

    creating.value = true
    let ok = 0
    for (const item of toCreate) {
        item.status = 'creating'
        const data = new FormData()
        data.append('image', item.file)
        data.append('status', status.value)
        Object.entries(item.fields).forEach(([k, v]) => {
            if (v !== '' && v !== null) data.append(k, v)
        })
        try {
            const { data: res } = await axios.post(route('admin.ai.store-from-image'), data)
            item.status = 'created'
            item.editUrl = res.edit_url
            ok++
        } catch (e) {
            item.status = 'ready'
            const errors = e.response?.data?.errors
            item.error = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message ?? 'Erreur lors de la création')
        }
    }
    creating.value = false
    if (ok) toast.success(`${ok} produit(s) créé(s)`)
}

onBeforeUnmount(() => items.value.forEach((i) => URL.revokeObjectURL(i.preview)))

const breadcrumbs = [
    { label: 'Admin', href: route('admin.dashboard') },
    { label: 'Produits', href: route('admin.products.index') },
    { label: 'Créer par photos' },
]

const inputClass = 'w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <div class="p-4 sm:p-6 space-y-5">
        <Breadcrumb :items="breadcrumbs" />

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Créer des produits par photos</h1>
            <p class="text-sm text-gray-500 mt-1">
                Déposez vos photos : une photo = un produit. L'IA prépare le nom, la description et la catégorie, vous complétez le prix et le stock.
            </p>
        </div>

        <div v-if="!aiConfigured" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            L'IA n'est pas configurée : vous pourrez remplir les fiches à la main.
            <a :href="route('admin.settings.ai')" class="font-semibold underline">Configurer l'IA →</a>
        </div>

        <!-- Zone de dépôt -->
        <label
            class="block rounded-xl border-2 border-dashed border-gray-300 bg-white p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
            @dragover.prevent
            @drop.prevent="onDrop"
        >
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="addFiles($event.target.files)">
            <p class="text-base font-medium text-gray-700">Cliquez ou glissez vos photos ici</p>
            <p class="text-sm text-gray-500 mt-1">JPG, PNG ou WEBP, 5 Mo maximum par photo</p>
        </label>

        <div v-if="aiConfigured" class="flex items-center gap-2 text-sm text-gray-700">
            <input id="use-ai" v-model="useAi" type="checkbox" class="w-4 h-4 rounded border-gray-300">
            <label for="use-ai">Remplir automatiquement avec l'IA</label>
        </div>

        <!-- Barre d'actions -->
        <div v-if="items.length" class="sticky z-10 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm"
            style="top: env(safe-area-inset-top, 0px)">
            <p class="text-sm text-gray-600 mr-auto">
                {{ counts.total }} photo(s)
                <span v-if="counts.pending"> · {{ counts.pending }} en analyse</span>
                <span v-if="counts.created" class="text-green-700"> · {{ counts.created }} créé(s)</span>
            </p>
            <select v-model="status" class="h-11 px-3 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="draft">Créer en brouillon</option>
                <option value="active">Créer et publier</option>
            </select>
            <button type="button" :disabled="!counts.ready || creating"
                class="h-11 px-5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed"
                @click="createAll">
                {{ creating ? 'Création…' : `Créer ${counts.ready} produit(s)` }}
            </button>
        </div>

        <!-- État vide -->
        <p v-else class="text-center text-sm text-gray-500 py-6">Aucune photo pour l'instant.</p>

        <!-- Fiches -->
        <div class="space-y-3">
            <div v-for="item in items" :key="item.id"
                class="rounded-xl border bg-white p-4 shadow-sm flex flex-col sm:flex-row gap-4"
                :class="item.missing ? 'border-red-300' : item.status === 'created' ? 'border-green-300' : 'border-gray-200'">

                <img :src="item.preview" :alt="item.fields.name" class="w-full sm:w-32 h-48 sm:h-32 object-cover rounded-lg border border-gray-100 flex-shrink-0">

                <!-- Créé -->
                <div v-if="item.status === 'created'" class="flex-1 flex flex-col justify-center gap-1">
                    <p class="text-sm font-semibold text-green-700">✓ {{ item.fields.name }} créé</p>
                    <a :href="item.editUrl" class="text-sm text-blue-600 hover:underline">Compléter la fiche (variantes, autres photos…) →</a>
                </div>

                <!-- Analyse en cours : skeleton -->
                <div v-else-if="['pending', 'analyzing'].includes(item.status)" class="flex-1 space-y-3 animate-pulse">
                    <p class="text-sm text-gray-500">{{ item.status === 'analyzing' ? 'Analyse de la photo…' : 'En attente…' }}</p>
                    <div class="h-11 bg-gray-100 rounded-lg"></div>
                    <div class="h-16 bg-gray-100 rounded-lg"></div>
                    <p v-if="item.error" class="text-xs text-amber-700">{{ item.error }}</p>
                </div>

                <!-- Formulaire -->
                <div v-else class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="item.fields.name" type="text" :class="inputClass" :disabled="item.status === 'creating'">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Accroche</label>
                        <input v-model="item.fields.short_description" type="text" :class="inputClass" :disabled="item.status === 'creating'">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="item.fields.description" rows="3" :disabled="item.status === 'creating'"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie</label>
                        <select v-model="item.fields.category_id" :class="inputClass" :disabled="item.status === 'creating'">
                            <option value="">— Sans catégorie —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Prix de vente (F CFA) *</label>
                        <input v-model="item.fields.sale_price" type="number" min="0" step="1" :class="inputClass" :disabled="item.status === 'creating'">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat (F CFA)</label>
                        <input v-model="item.fields.purchase_price" type="number" min="0" step="1" :class="inputClass" :disabled="item.status === 'creating'">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Stock</label>
                        <input v-model="item.fields.stock_quantity" type="number" min="0" step="1" :class="inputClass" :disabled="item.status === 'creating'">
                    </div>
                    <div class="flex items-end gap-2">
                        <button v-if="aiConfigured" type="button" :disabled="item.status === 'creating'"
                            class="h-11 px-3 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40"
                            @click="reanalyze(item)">Relancer l'IA</button>
                        <button type="button" :disabled="item.status === 'creating'"
                            class="h-11 px-3 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 disabled:opacity-40"
                            @click="removeItem(item)">Retirer</button>
                    </div>
                    <p v-if="item.error" class="md:col-span-2 text-xs text-red-600">{{ item.error }}</p>
                    <p v-if="item.missing" class="md:col-span-2 text-xs text-red-600">Le nom et le prix de vente sont obligatoires.</p>
                </div>
            </div>
        </div>
    </div>

    <ToastContainer />
</template>
