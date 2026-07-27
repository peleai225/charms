<script setup>
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    attributes: Array,
})

// ── Formulaire nouvel attribut ────────────────────────────────────────────────
const attrForm = useForm({ name: '', type: 'size' })

function submitAttr() {
    attrForm.post(route('admin.attributes.store'), {
        onSuccess: () => attrForm.reset(),
    })
}

// ── Formulaire nouvelle valeur ────────────────────────────────────────────────
const valueFormMap = ref({})
const valueImageMap = ref({})   // fichier image temporaire par attrId
const valuePreviewMap = ref({}) // preview URL par attrId

function getValueForm(attrId) {
    if (!valueFormMap.value[attrId]) {
        valueFormMap.value[attrId] = useForm({ value: '', color_code: '#000000', image: null })
    }
    return valueFormMap.value[attrId]
}

function onValueImageChange(attrId, e) {
    const file = e.target.files?.[0]
    if (!file) return
    valueImageMap.value[attrId] = file
    valuePreviewMap.value[attrId] = URL.createObjectURL(file)
    getValueForm(attrId).image = file
}

function submitValue(attr) {
    const form = getValueForm(attr.id)
    form.post(route('admin.attributes.values.store', attr.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
            valueImageMap.value[attr.id] = null
            valuePreviewMap.value[attr.id] = null
        },
    })
}

// ── Formulaire bulk ───────────────────────────────────────────────────────────
const bulkFormMap = ref({})
const showBulkMap = ref({})

function getBulkForm(attrId) {
    if (!bulkFormMap.value[attrId]) {
        bulkFormMap.value[attrId] = useForm({ values: '' })
    }
    return bulkFormMap.value[attrId]
}

function submitBulk(attr) {
    const form = getBulkForm(attr.id)
    form.post(route('admin.attributes.values.bulk', attr.id), {
        onSuccess: () => { form.reset(); showBulkMap.value[attr.id] = false },
    })
}

// ── Suppression ───────────────────────────────────────────────────────────────
function deleteAttribute(attr) {
    if (!confirm(`Supprimer l'attribut "${attr.name}" ?`)) return
    router.delete(route('admin.attributes.destroy', attr.id))
}

function deleteValue(attr, val) {
    if (!confirm('Supprimer cette valeur ?')) return
    router.delete(route('admin.attributes.values.destroy', [attr.id, val.id]))
}

// ── Drawer édition valeur ─────────────────────────────────────────────────────
const drawer = reactive({
    open:      false,
    attr:      null,
    val:       null,
    colorCode: '',
    imageFile: null,
    preview:   null,
    removeImg: false,
    saving:    false,
})

function openDrawer(attr, val) {
    drawer.attr      = attr
    drawer.val       = val
    drawer.colorCode = val.color_code ?? '#000000'
    drawer.imageFile = null
    drawer.preview   = val.image_url ?? null
    drawer.removeImg = false
    drawer.saving    = false
    drawer.open      = true
}

function closeDrawer() {
    drawer.open = false
}

function onDrawerImageChange(e) {
    const file = e.target.files?.[0]
    if (!file) return
    drawer.imageFile = file
    drawer.preview   = URL.createObjectURL(file)
    drawer.removeImg = false
}

function removeDrawerImage() {
    drawer.imageFile = null
    drawer.preview   = null
    drawer.removeImg = true
}

async function saveDrawer() {
    if (drawer.saving) return
    drawer.saving = true

    const data = new FormData()
    data.append('color_code', drawer.colorCode)
    if (drawer.imageFile) {
        data.append('image', drawer.imageFile)
    } else if (drawer.removeImg) {
        data.append('remove_image', '1')
    }

    try {
        const res = await fetch(route('admin.attributes.values.update', [drawer.attr.id, drawer.val.id]), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: data,
        })
        if (res.ok) {
            drawer.open = false
            router.reload({ only: ['attributes'] })
        }
    } finally {
        drawer.saving = false
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const typeBadge = {
    color: { bg: 'bg-pink-100',  text: 'text-pink-600',  label: 'C' },
    size:  { bg: 'bg-blue-100',  text: 'text-blue-600',  label: 'T' },
    text:  { bg: 'bg-gray-200',  text: 'text-gray-600',  label: 'A' },
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Attributs produits</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Gérez les attributs et leurs valeurs (Taille, Couleur, etc.)</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne gauche : créer un attribut -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Nouvel attribut</h2>
                    <form @submit.prevent="submitAttr" class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom *</label>
                            <input v-model="attrForm.name" type="text" required placeholder="Ex: Taille, Couleur..."
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p v-if="attrForm.errors.name" class="mt-1 text-[12px] text-red-500">{{ attrForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</label>
                            <select v-model="attrForm.type"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="size">Taille / Texte</option>
                                <option value="color">Couleur (avec code hex)</option>
                                <option value="text">Texte libre</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="attrForm.processing"
                            class="w-full h-9 bg-blue-600 hover:bg-blue-700 text-white font-medium text-[13px] rounded-lg transition-colors disabled:opacity-60">
                            Créer l'attribut
                        </button>
                    </form>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-[12px] text-blue-800 space-y-1.5">
                    <p class="font-semibold text-[13px]">Comment ça marche ?</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                        <li><strong>Taille</strong> — âges, lettres (S/M/L), chiffres</li>
                        <li><strong>Couleur</strong> — code HEX + image optionnelle</li>
                        <li><strong>Texte</strong> — matière, style, etc.</li>
                    </ul>
                    <p class="text-blue-600 mt-2">💡 Pour les couleurs : ajoutez une image par valeur pour que la boutique affiche la bonne photo quand le client choisit une couleur.</p>
                </div>
            </div>

            <!-- Colonne droite : liste des attributs -->
            <div class="lg:col-span-2 space-y-4">

                <template v-if="attributes.length > 0">
                    <div v-for="attr in attributes" :key="attr.id"
                        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                        <!-- En-tête attribut -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded flex items-center justify-center text-[10px] font-bold"
                                    :class="[typeBadge[attr.type]?.bg ?? 'bg-gray-200', typeBadge[attr.type]?.text ?? 'text-gray-600']">
                                    {{ typeBadge[attr.type]?.label ?? 'A' }}
                                </span>
                                <div>
                                    <h3 class="text-[13px] font-semibold text-gray-900">{{ attr.name }}</h3>
                                    <span class="text-[11px] text-gray-400">{{ attr.values.length }} valeur(s) · {{ attr.slug }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="showBulkMap[attr.id] = !showBulkMap[attr.id]"
                                    class="h-7 px-2.5 text-[12px] font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 rounded transition-colors">
                                    + Ajout multiple
                                </button>
                                <button type="button" @click="deleteAttribute(attr)"
                                    class="p-1.5 text-gray-300 hover:text-red-500 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Ajout multiple -->
                        <div v-if="showBulkMap[attr.id]" class="border-b border-gray-100 bg-amber-50/50 px-5 py-4">
                            <form @submit.prevent="submitBulk(attr)">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                    Plusieurs valeurs (séparées par virgule, point-virgule ou saut de ligne)
                                </label>
                                <textarea v-model="getBulkForm(attr.id).values" rows="3" required
                                    placeholder="4 ans, 6 ans, 8 ans, 10 ans, 12 ans"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                <div class="flex gap-2 mt-2">
                                    <button type="submit" :disabled="getBulkForm(attr.id).processing"
                                        class="h-8 px-3 bg-amber-600 hover:bg-amber-700 text-white font-medium text-[12px] rounded-lg transition-colors disabled:opacity-60">
                                        Ajouter tout
                                    </button>
                                    <button type="button" @click="showBulkMap[attr.id] = false"
                                        class="h-8 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[12px] rounded-lg transition-colors">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Valeurs existantes -->
                        <div class="p-5">
                            <div v-if="attr.values.length > 0" class="flex flex-wrap gap-1.5 mb-4">
                                <span v-for="val in attr.values" :key="val.id"
                                    class="inline-flex items-center gap-1.5 pl-1.5 pr-1 py-1 bg-gray-100 rounded text-[12px] group">

                                    <!-- Miniature image ou pastille couleur -->
                                    <template v-if="attr.type === 'color'">
                                        <img v-if="val.image_url" :src="val.image_url" :alt="val.value"
                                            class="w-5 h-5 rounded-sm object-cover border border-gray-300 flex-shrink-0">
                                        <span v-else-if="val.color_code"
                                            class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0"
                                            :style="{ background: val.color_code }"></span>
                                    </template>

                                    <span class="font-medium text-gray-700">{{ val.value }}</span>

                                    <!-- Bouton éditer -->
                                    <button type="button" @click="openDrawer(attr, val)"
                                        class="p-0.5 text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100 rounded"
                                        title="Modifier">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    <!-- Bouton supprimer -->
                                    <button type="button" @click="deleteValue(attr, val)"
                                        class="p-0.5 text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100 rounded"
                                        title="Supprimer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                            <p v-else class="text-[12px] text-gray-400 italic mb-3">Aucune valeur. Ajoutez-en ci-dessous.</p>

                            <!-- Formulaire ajout valeur unitaire -->
                            <form @submit.prevent="submitValue(attr)" class="flex gap-2 items-end flex-wrap">
                                <div class="flex-1 min-w-36">
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Nouvelle valeur</label>
                                    <input v-model="getValueForm(attr.id).value" type="text" required
                                        :placeholder="attr.type === 'color' ? 'Ex: Rouge' : 'Valeur...'"
                                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p v-if="getValueForm(attr.id).errors.value" class="mt-1 text-[12px] text-red-500">{{ getValueForm(attr.id).errors.value }}</p>
                                </div>

                                <!-- Couleur + image (type color uniquement) -->
                                <template v-if="attr.type === 'color'">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Couleur</label>
                                        <input v-model="getValueForm(attr.id).color_code" type="color"
                                            class="h-9 w-12 px-1 py-1 border border-gray-200 rounded-lg cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Image</label>
                                        <label class="relative flex items-center justify-center h-9 w-16 border border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 overflow-hidden bg-gray-50">
                                            <img v-if="valuePreviewMap[attr.id]" :src="valuePreviewMap[attr.id]"
                                                class="absolute inset-0 w-full h-full object-cover">
                                            <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <input type="file" accept="image/*" class="hidden" @change="onValueImageChange(attr.id, $event)">
                                        </label>
                                    </div>
                                </template>

                                <button type="submit" :disabled="getValueForm(attr.id).processing"
                                    class="h-9 px-3 bg-green-600 hover:bg-green-700 text-white font-medium text-[12px] rounded-lg transition-colors disabled:opacity-60">
                                    + Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </template>

                <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center text-gray-400">
                    <p class="text-[13px] font-medium mb-1">Aucun attribut</p>
                    <p class="text-[12px]">Créez votre premier attribut (Taille, Couleur...) à gauche.</p>
                </div>
            </div>
        </div>

        <!-- ── Drawer édition valeur ─────────────────────────────────────────── -->
        <teleport to="body">
            <!-- Overlay -->
            <div v-if="drawer.open" class="fixed inset-0 bg-black/40 z-40" @click="closeDrawer"></div>

            <!-- Panel -->
            <div v-if="drawer.open"
                class="fixed right-0 top-0 h-full w-80 bg-white shadow-2xl z-50 flex flex-col">

                <!-- Header drawer -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-[14px] font-semibold text-gray-900">Modifier la valeur</h3>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ drawer.attr?.name }} · {{ drawer.val?.value }}</p>
                    </div>
                    <button @click="closeDrawer" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Corps drawer -->
                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-5">

                    <!-- Code couleur (si type color) -->
                    <div v-if="drawer.attr?.type === 'color'">
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">Code couleur</label>
                        <div class="flex items-center gap-3">
                            <input v-model="drawer.colorCode" type="color"
                                class="h-10 w-16 px-1 py-1 border border-gray-200 rounded-lg cursor-pointer">
                            <input v-model="drawer.colorCode" type="text"
                                class="flex-1 h-10 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="#000000">
                        </div>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Image de la valeur
                            <span class="normal-case font-normal text-gray-400 ml-1">(affichée en boutique)</span>
                        </label>

                        <!-- Preview -->
                        <div v-if="drawer.preview && !drawer.removeImg"
                            class="relative mb-3 rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                            <img :src="drawer.preview" alt="" class="w-full h-40 object-cover">
                            <button type="button" @click="removeDrawerImage"
                                class="absolute top-2 right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                title="Supprimer l'image">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Zone upload -->
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-colors bg-gray-50">
                            <svg class="w-7 h-7 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[12px] text-gray-400">Cliquer pour changer l'image</span>
                            <span class="text-[11px] text-gray-300 mt-0.5">JPG, PNG, WebP — max 5 Mo</span>
                            <input type="file" accept="image/*" class="hidden" @change="onDrawerImageChange">
                        </label>

                        <p v-if="drawer.removeImg" class="mt-2 text-[12px] text-red-500">Image supprimée à la sauvegarde.</p>
                    </div>
                </div>

                <!-- Footer drawer -->
                <div class="px-5 py-4 border-t border-gray-100 flex gap-2">
                    <button @click="closeDrawer"
                        class="flex-1 h-10 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[13px] font-medium rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button @click="saveDrawer" :disabled="drawer.saving"
                        class="flex-1 h-10 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                        <svg v-if="drawer.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ drawer.saving ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </div>
        </teleport>

    </div>
</template>
