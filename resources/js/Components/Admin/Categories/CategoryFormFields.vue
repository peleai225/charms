<script setup>
import { ref } from 'vue'
import CategoryIconPicker from './CategoryIconPicker.vue'

// Champs communs aux fenêtres « Nouvelle catégorie » et « Modifier la catégorie ».
// `form` est le useForm() de la page : les champs le modifient directement.
const props = defineProps({
    form:         { type: Object, required: true },
    parents:      { type: Array, default: () => [] },   // arbre des catégories parentes possibles
    iconOptions:  { type: Array, default: () => [] },   // [{ key, label }]
    currentImage: { type: String, default: null },      // image déjà enregistrée (édition)
    idPrefix:     { type: String, required: true },     // 'create' | 'edit' : ids uniques
})

const input = 'w-full h-11 sm:h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500'
const label = 'block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1'

// ── Image : aperçu du fichier choisi (gardé si la fenêtre est refermée puis rouverte) ──
const preview   = ref(props.form.image instanceof File ? URL.createObjectURL(props.form.image) : null)
const fileInput = ref(null)

function onImage(e) {
    const file = e.target.files[0]
    if (!file) return
    props.form.image = file
    preview.value = URL.createObjectURL(file)
}

function clearImage() {
    props.form.image = null
    preview.value = null
    if (fileInput.value) fileInput.value.value = ''
}

const showSeo = ref(!!(props.form.meta_title || props.form.meta_description))
</script>

<template>
    <!-- Nom + Ordre -->
    <div class="grid grid-cols-3 gap-3">
        <div class="col-span-2">
            <label :for="`${idPrefix}-name`" :class="label">Nom <span class="text-red-500">*</span></label>
            <input :id="`${idPrefix}-name`" v-model="form.name" type="text" required :class="input">
            <p v-if="form.errors.name" class="mt-1 text-[12px] text-red-500">{{ form.errors.name }}</p>
        </div>
        <div>
            <label :for="`${idPrefix}-order`" :class="label">Ordre</label>
            <input :id="`${idPrefix}-order`" v-model.number="form.order" type="number" min="0" :class="input">
        </div>
    </div>

    <!-- Description -->
    <div>
        <label :for="`${idPrefix}-description`" :class="label">Description</label>
        <textarea :id="`${idPrefix}-description`" v-model="form.description" rows="2"
            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
    </div>

    <!-- Catégorie parente -->
    <div>
        <label :for="`${idPrefix}-parent`" :class="label">Catégorie parente</label>
        <select :id="`${idPrefix}-parent`" v-model="form.parent_id" :class="input">
            <option value="">— Aucune (catégorie racine)</option>
            <template v-for="cat in parents" :key="cat.id">
                <option :value="cat.id">{{ cat.name }}</option>
                <option v-for="child in (cat.children ?? [])" :key="child.id" :value="child.id">&nbsp;&nbsp;└ {{ child.name }}</option>
            </template>
        </select>
    </div>

    <!-- Image : nouvelle image choisie, sinon image actuelle -->
    <div>
        <label :for="`${idPrefix}-image`" :class="label">Image</label>
        <div v-if="preview || currentImage" class="mb-2 flex items-center gap-3">
            <img :src="preview || ('/storage/' + currentImage)" alt=""
                class="w-16 h-16 rounded-lg object-contain bg-gray-50 border border-gray-200">
            <div>
                <p class="text-[12px] text-gray-500">{{ preview ? 'Nouvelle image sélectionnée' : 'Image actuelle' }}</p>
                <button v-if="preview" type="button" @click="clearImage"
                    class="h-11 sm:h-auto text-[12px] font-medium text-gray-600 hover:text-gray-900 underline">
                    Annuler ce choix
                </button>
                <p v-else class="text-[11px] text-gray-400">Choisissez un fichier pour la remplacer</p>
            </div>
        </div>
        <p v-else class="mb-2 text-[12px] text-gray-400">Aucune image définie</p>
        <input :id="`${idPrefix}-image`" ref="fileInput" type="file" accept="image/*" @change="onImage"
            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600 cursor-pointer">
        <p class="mt-1 text-[11px] text-gray-400">De préférence un produit détouré (fond blanc ou transparent), carré.</p>
    </div>

    <!-- Icône -->
    <CategoryIconPicker v-model="form.icon" :options="iconOptions" :name="`${idPrefix}-icon`" />

    <!-- Statuts -->
    <div>
        <div class="flex gap-5">
            <label class="min-h-11 sm:min-h-0 flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-[13px] text-gray-700">Active</span>
            </label>
            <label class="min-h-11 sm:min-h-0 flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_featured" type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-[13px] text-gray-700">Mise en avant</span>
            </label>
        </div>
        <p class="mt-1.5 text-[12px] text-gray-500 leading-snug">
            « Mise en avant » place la catégorie dans « Nos univers populaires » sur l'accueil : 12 au plus,
            dans l'ordre ci-dessus, et seulement si elle a des produits en ligne. Si aucune n'est cochée,
            l'accueil montre les catégories principales.
        </p>
    </div>

    <!-- SEO (accordéon) -->
    <div class="border border-gray-100 rounded-lg overflow-hidden">
        <button type="button" @click="showSeo = !showSeo" :aria-expanded="showSeo"
            class="w-full min-h-11 flex items-center justify-between px-3 py-2.5 text-[12px] font-semibold text-gray-500 uppercase tracking-wide hover:bg-gray-50 transition-colors">
            <span>SEO (optionnel)</span>
            <svg class="w-4 h-4" :class="showSeo ? 'rotate-180' : ''" aria-hidden="true"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div v-if="showSeo" class="px-3 pb-3 space-y-3 border-t border-gray-100">
            <div class="pt-3">
                <label :for="`${idPrefix}-meta-title`" :class="label">Titre SEO</label>
                <input :id="`${idPrefix}-meta-title`" v-model="form.meta_title" type="text" placeholder="Laisser vide pour utiliser le nom" :class="input">
                <p class="mt-0.5 text-[11px] text-gray-400">{{ (form.meta_title || '').length }}/255</p>
            </div>
            <div>
                <label :for="`${idPrefix}-meta-description`" :class="label">Description SEO</label>
                <textarea :id="`${idPrefix}-meta-description`" v-model="form.meta_description" rows="2" placeholder="Résumé pour Google (160 car. recommandés)"
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                <p class="mt-0.5 text-[11px]" :class="(form.meta_description || '').length > 160 ? 'text-amber-600' : 'text-gray-400'">
                    {{ (form.meta_description || '').length }}/500
                </p>
            </div>
        </div>
    </div>

    <!-- Erreurs -->
    <div v-if="Object.keys(form.errors).length" class="rounded-lg bg-red-50 border border-red-100 px-3 py-2.5 space-y-1" role="alert">
        <p v-for="(msg, field) in form.errors" :key="field" class="text-[12px] text-red-600">• {{ msg }}</p>
    </div>
</template>
