<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { LoaderCircle } from 'lucide-vue-next'
import { useToast } from '@/Composables/useToast'
import Breadcrumb from '@/Components/UI/Breadcrumb.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import ImportCard from '@/Components/Admin/PhotoImport/ImportCard.vue'
import ArrivalDefaults from '@/Components/Admin/PhotoImport/ArrivalDefaults.vue'
import {
    makePhoto, makeCard, nameFromFile, applyColorSuggestion, applyTextSuggestion,
    isVariantMode, variantRows, cardProblems,
    emptyArrivalDefaults, applyArrivalDefault, applyArrivalDefaults, makeThumbnail, groupsToPhotos,
} from '@/Components/Admin/PhotoImport/photoImport.js'

const props = defineProps({
    aiConfigured: Boolean,
    aiProvider: { type: String, default: '' },
    categories: Array,
    colors: { type: Array, default: () => [] },
    optionAttributes: { type: Array, default: () => [] },
    categoryOptions: { type: [Object, Array], default: () => ({}) },
})

const toast = useToast()
const cards = ref([])
const status = ref('draft')
const useAi = ref(props.aiConfigured)
const autoGroup = ref(true)
const sorting = ref(0) // photos en cours de tri par l'IA
const analyzing = ref(false)
const creating = ref(false)
const fileInput = ref(null)
const defaults = reactive(emptyArrivalDefaults())

// Un tableau PHP vide arrive en [] au lieu de {}
const categoryOptions = computed(() => (Array.isArray(props.categoryOptions) ? {} : props.categoryOptions))

const MAX_SIZE = 5 * 1024 * 1024
const SORT_LIMIT = 40
// Gemini gratuit : une photo à la fois, espacées ; fournisseurs payants : trois en parallèle
const PACE = props.aiProvider === 'gemini' ? { workers: 1, pause: 4000 } : { workers: 3, pause: 300 }
const sleep = (ms) => new Promise((r) => setTimeout(r, ms))
const isBusyPhoto = (p) => ['pending', 'analyzing'].includes(p.status)
const editingCards = () => cards.value.filter((c) => c.status === 'editing')

// Un réglage commun modifié → recopié dans toutes les fiches pas encore créées
const DEFAULT_SOURCES = {
    sale_price: () => defaults.sale_price,
    purchase_price: () => defaults.purchase_price,
    stock: () => defaults.stock,
    sizes: () => [defaults.optionAttributeId, ...defaults.optionValueIds],
}
Object.entries(DEFAULT_SOURCES).forEach(([field, source]) => {
    watch(source, () => editingCards().forEach((c) => applyArrivalDefault(c, field, defaults, categoryOptions.value)))
})

const counts = computed(() => {
    const photos = cards.value.flatMap((c) => c.photos)
    return {
        cards: cards.value.length,
        photos: photos.length,
        pending: photos.filter(isBusyPhoto).length,
        ready: cards.value.filter((c) => c.status === 'editing' && !c.photos.some(isBusyPhoto)).length,
        created: cards.value.filter((c) => c.status === 'created').length,
        selected: cards.value.filter((c) => c.selected && c.status === 'editing').length,
    }
})

function newCard(photos) {
    const card = makeCard(photos, { name: nameFromFile(photos[0].file) })
    if (!useAi.value) card.autofilled = true
    applyArrivalDefaults(card, defaults, categoryOptions.value)
    return card
}

async function addFiles(fileList) {
    const photos = []
    for (const file of Array.from(fileList)) {
        if (!file.type.startsWith('image/')) continue
        if (file.size > MAX_SIZE) {
            toast.error(`« ${file.name} » dépasse 5 Mo`)
            continue
        }
        photos.push(makePhoto(file, useAi.value ? 'pending' : 'skipped'))
    }
    if (fileInput.value) fileInput.value.value = ''
    if (!photos.length) return

    const groups = useAi.value && autoGroup.value && photos.length > 1 ? await sortByArticle(photos) : photos.map((p) => [p])
    cards.value.push(...groups.map(newCard))
    if (useAi.value) runAnalysis()
}

// ── Mode arrivage : l'IA regroupe les photos du même article ────────────────

async function sortChunk(chunk) {
    const images = await Promise.all(chunk.map((p) => makeThumbnail(p.file)))
    const { data: res } = await axios.post(route('admin.ai.group-photos'), { images }, { timeout: 150000 })
    return groupsToPhotos(res.groups, chunk)
}

async function sortByArticle(photos) {
    sorting.value += photos.length
    const groups = []
    let failure = null
    try {
        for (let start = 0; start < photos.length; start += SORT_LIMIT) {
            const chunk = photos.slice(start, start + SORT_LIMIT)
            if (chunk.length < 2) {
                groups.push(chunk)
                continue
            }
            try {
                groups.push(...await sortChunk(chunk))
            } catch (e) {
                failure = e.response?.data?.message ?? "Le tri automatique n'a pas abouti."
                chunk.forEach((p) => groups.push([p]))
            }
        }
    } finally {
        sorting.value -= photos.length
    }
    if (failure) toast.error(`${failure} Regroupez les fiches à la main si besoin.`)
    else toast.success(`${photos.length} photos → ${groups.length} article(s). Une photo mal placée ? Bouton « Séparer ».`)
    return groups
}

function onDrop(e) {
    addFiles(e.dataTransfer.files)
}

// ── Analyse IA, photo par photo ──────────────────────────────────────────────

const ownerOf = (photo) => cards.value.find((c) => c.photos.includes(photo))

async function analyzePhoto(photo, retry = true) {
    photo.status = 'analyzing'
    photo.error = null
    const data = new FormData()
    data.append('image', photo.file)
    try {
        const { data: s } = await axios.post(route('admin.ai.analyze-image'), data)
        photo.suggestion = s
        applyColorSuggestion(photo, s)
        const card = ownerOf(photo)
        if (card && card.photos[0] === photo && !card.autofilled) applyTextSuggestion(card, s, categoryOptions.value)
        photo.status = 'done'
    } catch (e) {
        if (e.response?.status === 429 && retry) {
            photo.error = 'Quota atteint, nouvelle tentative dans 30 s…'
            await sleep(30000)
            return analyzePhoto(photo, false)
        }
        photo.status = 'error'
        photo.error = e.response?.data?.message ?? "L'IA n'a pas pu analyser cette photo. Remplissez la fiche à la main."
        const card = ownerOf(photo)
        if (card && card.photos[0] === photo) card.autofilled = true
    }
}

// La première photo de chaque fiche d'abord : c'est elle qui donne le texte
function nextPendingPhoto() {
    return cards.value.map((c) => c.photos[0]).find((p) => p?.status === 'pending')
        ?? cards.value.flatMap((c) => c.photos).find((p) => p.status === 'pending')
        ?? null
}

async function analysisWorker() {
    let photo
    while ((photo = nextPendingPhoto())) {
        await analyzePhoto(photo) // passe la photo en « analyzing » tout de suite : un autre ouvrier ne la prendra pas
        await sleep(PACE.pause)
    }
}

async function runAnalysis() {
    if (analyzing.value) return
    analyzing.value = true
    try {
        await Promise.all(Array.from({ length: PACE.workers }, analysisWorker))
    } finally {
        analyzing.value = false
    }
    // Photos ajoutées pendant que les derniers ouvriers s'arrêtaient
    if (nextPendingPhoto()) runAnalysis()
}

function reanalyze(card) {
    card.autofilled = false
    card.photos.forEach((p) => { p.status = 'pending'; p.colorPick = ''; p.error = null })
    runAnalysis()
}

// ── Regrouper / séparer ──────────────────────────────────────────────────────

function groupSelected() {
    const selected = cards.value.filter((c) => c.selected && c.status === 'editing')
    if (selected.length < 2) return
    const [target, ...others] = selected
    others.forEach((o) => target.photos.push(...o.photos))
    cards.value = cards.value.filter((c) => !others.includes(c))
    target.selected = false
    target.missing = false
    target.error = null
    toast.success(`${target.photos.length} photos regroupées : choisissez la couleur de chacune`)
}

function separatePhoto(card, photo) {
    card.photos.splice(card.photos.indexOf(photo), 1)
    const fresh = newCard([photo])
    if (photo.suggestion) applyTextSuggestion(fresh, photo.suggestion, categoryOptions.value)
    else if (!isBusyPhoto(photo)) fresh.autofilled = true
    cards.value.splice(cards.value.indexOf(card) + 1, 0, fresh)
}

function removeCard(card) {
    card.photos.forEach((p) => URL.revokeObjectURL(p.preview))
    cards.value = cards.value.filter((c) => c !== card)
}

// ── Création ─────────────────────────────────────────────────────────────────

function appendFields(data, card, keys) {
    keys.forEach((k) => {
        const v = card.fields[k]
        if (v !== '' && v !== null && v !== undefined) data.append(k, v)
    })
}

function variantPayload(card) {
    const data = new FormData()
    data.append('status', status.value)
    appendFields(data, card, ['name', 'short_description', 'description', 'category_id', 'sale_price', 'purchase_price'])

    card.photos.forEach((p, i) => {
        data.append(`images[${i}]`, p.file)
        if (p.colorPick === 'new') {
            const name = p.newColorName.trim()
            if (name) {
                data.append(`colors[${i}][name]`, name)
                data.append(`colors[${i}][hex]`, p.newColorHex.toUpperCase())
            }
        } else if (p.colorPick !== '') {
            data.append(`colors[${i}][id]`, p.colorPick)
        }
    })

    const rows = variantRows(card, props.colors, props.optionAttributes)
    if (rows.some((r) => r.value)) data.append('option_attribute_id', card.optionAttributeId)
    rows.forEach((r, j) => {
        if (r.color) data.append(`variants[${j}][color_index]`, r.color.index)
        if (r.value) data.append(`variants[${j}][value_id]`, r.value.id)
        data.append(`variants[${j}][stock]`, parseInt(card.stock[r.key]) || 0)
    })
    return data
}

function simplePayload(card) {
    const data = new FormData()
    data.append('image', card.photos[0].file)
    data.append('status', status.value)
    appendFields(data, card, Object.keys(card.fields))
    return data
}

async function createCard(card) {
    card.status = 'creating'
    card.error = null
    try {
        if (isVariantMode(card)) {
            const { data: res } = await axios.post(route('admin.ai.store-variant-product'), variantPayload(card))
            card.variantCount = res.variants
            card.editUrl = res.edit_url
        } else {
            const { data: res } = await axios.post(route('admin.ai.store-from-image'), simplePayload(card))
            card.editUrl = res.edit_url
        }
        card.status = 'created'
        card.selected = false
        return true
    } catch (e) {
        card.status = 'editing'
        const errors = e.response?.data?.errors
        card.error = errors ? [...new Set(Object.values(errors).flat())].join(' ') : (e.response?.data?.message ?? 'Erreur lors de la création')
        card.missing = true
        return false
    }
}

async function createAll() {
    const toCreate = cards.value.filter((c) => c.status === 'editing' && !c.photos.some(isBusyPhoto))
    let blocked = 0
    toCreate.forEach((c) => {
        const problems = cardProblems(c, props.colors, props.optionAttributes)
        c.missing = problems.length > 0
        c.error = problems.join(' ') || null
        if (problems.length) blocked++
    })
    if (blocked) {
        toast.error(`${blocked} fiche(s) à compléter avant la création`)
        return
    }

    creating.value = true
    let ok = 0
    for (const card of toCreate) {
        if (await createCard(card)) ok++
    }
    creating.value = false
    if (ok) toast.success(`${ok} produit(s) créé(s)`)
}

onBeforeUnmount(() => cards.value.forEach((c) => c.photos.forEach((p) => URL.revokeObjectURL(p.preview))))

const breadcrumbs = [
    { label: 'Admin', href: route('admin.dashboard') },
    { label: 'Produits', href: route('admin.products.index') },
    { label: 'Créer par photos' },
]
</script>

<template>
    <div class="p-4 sm:p-6 space-y-5">
        <Breadcrumb :items="breadcrumbs" />

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Créer des produits par photos</h1>
            <p class="text-sm text-gray-500 mt-1">
                Déposez toutes les photos de l'arrivage : l'IA regroupe les photos du même article (une variante par couleur), rédige les fiches et choisit la catégorie.
                Indiquez le prix, le stock et les tailles une seule fois dans les réglages communs, puis créez tout d'un clic.
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

        <div v-if="aiConfigured" class="flex flex-wrap gap-x-6 text-sm text-gray-700">
            <label class="min-h-11 flex items-center gap-2 cursor-pointer">
                <input v-model="useAi" type="checkbox" class="w-4 h-4 rounded border-gray-300">
                Remplir automatiquement avec l'IA
            </label>
            <label class="min-h-11 flex items-center gap-2" :class="useAi ? 'cursor-pointer' : 'opacity-50'">
                <input v-model="autoGroup" type="checkbox" :disabled="!useAi" class="w-4 h-4 rounded border-gray-300">
                Regrouper les photos du même article
            </label>
        </div>

        <ArrivalDefaults :defaults="defaults" :option-attributes="optionAttributes" :disabled="creating" />

        <div v-if="sorting" role="status" class="flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <LoaderCircle class="w-5 h-5 flex-shrink-0 animate-spin" aria-hidden="true" />
            L'IA regarde vos {{ sorting }} photos et regroupe celles du même article…
        </div>

        <!-- Barre d'actions -->
        <div v-if="cards.length" class="sticky z-10 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm"
            style="top: env(safe-area-inset-top, 0px)">
            <p class="text-sm text-gray-600 mr-auto">
                {{ counts.cards }} fiche(s) · {{ counts.photos }} photo(s)
                <span v-if="counts.pending"> · {{ counts.pending }} en analyse</span>
                <span v-if="counts.created" class="text-green-700"> · {{ counts.created }} créé(s)</span>
            </p>
            <button v-if="counts.selected >= 2" type="button" :disabled="creating"
                class="h-11 px-4 text-sm font-medium text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-50 disabled:opacity-40"
                @click="groupSelected">
                Regrouper {{ counts.selected }} fiches en un produit
            </button>
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
        <p v-else-if="!sorting" class="text-center text-sm text-gray-500 py-6">Aucune photo pour l'instant.</p>

        <!-- Fiches -->
        <div class="space-y-3">
            <ImportCard
                v-for="card in cards"
                :key="card.id"
                :card="card"
                :categories="categories"
                :colors="colors"
                :option-attributes="optionAttributes"
                :category-options="categoryOptions"
                :ai-configured="aiConfigured"
                :use-ai="useAi"
                @remove="removeCard(card)"
                @separate="(photo) => separatePhoto(card, photo)"
                @reanalyze="reanalyze(card)"
            />
        </div>
    </div>

    <ToastContainer />
</template>
