<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useToast } from '@/Composables/useToast'
import Breadcrumb from '@/Components/UI/Breadcrumb.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import ImportCard from '@/Components/Admin/PhotoImport/ImportCard.vue'
import {
    makePhoto, makeCard, nameFromFile, applyColorSuggestion, applyTextSuggestion,
    isVariantMode, variantRows, cardProblems,
} from '@/Components/Admin/PhotoImport/photoImport.js'

const props = defineProps({
    aiConfigured: Boolean,
    categories: Array,
    colors: { type: Array, default: () => [] },
    optionAttributes: { type: Array, default: () => [] },
    categoryOptions: { type: [Object, Array], default: () => ({}) },
})

const toast = useToast()
const cards = ref([])
const status = ref('draft')
const useAi = ref(props.aiConfigured)
const analyzing = ref(false)
const creating = ref(false)
const fileInput = ref(null)

// Un tableau PHP vide arrive en [] au lieu de {}
const categoryOptions = computed(() => (Array.isArray(props.categoryOptions) ? {} : props.categoryOptions))

const MAX_SIZE = 5 * 1024 * 1024
const sleep = (ms) => new Promise((r) => setTimeout(r, ms))
const isBusyPhoto = (p) => ['pending', 'analyzing'].includes(p.status)

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

function addFiles(fileList) {
    for (const file of Array.from(fileList)) {
        if (!file.type.startsWith('image/')) continue
        if (file.size > MAX_SIZE) {
            toast.error(`« ${file.name} » dépasse 5 Mo`)
            continue
        }
        const card = makeCard([makePhoto(file, useAi.value ? 'pending' : 'skipped')], { name: nameFromFile(file) })
        if (!useAi.value) card.autofilled = true
        cards.value.push(card)
    }
    if (fileInput.value) fileInput.value.value = ''
    if (useAi.value) runAnalysis()
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

function nextPendingPhoto() {
    for (const card of cards.value) {
        const photo = card.photos.find((p) => p.status === 'pending')
        if (photo) return photo
    }
    return null
}

async function runAnalysis() {
    if (analyzing.value) return
    analyzing.value = true
    try {
        let photo
        while ((photo = nextPendingPhoto())) {
            await analyzePhoto(photo)
            await sleep(4000) // espacement pour rester dans les quotas gratuits
        }
    } finally {
        analyzing.value = false
    }
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
    const fresh = makeCard([photo], { name: nameFromFile(photo.file) })
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
                Déposez vos photos : chaque photo devient une fiche. L'IA prépare le nom, la description, la catégorie et repère la couleur.
                Même article en plusieurs couleurs ? Cochez ses fiches puis « Regrouper » : vous obtenez un seul produit avec une variante par couleur.
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
        <p v-else class="text-center text-sm text-gray-500 py-6">Aucune photo pour l'instant.</p>

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
