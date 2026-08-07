<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    positions: Object,
    types:     Object,
})

// Pré-sélectionner la position si passée via query string
const urlParams  = new URLSearchParams(window.location.search)
const prePosition = urlParams.get('position') ?? 'home_hero'

const form = useForm({
    name:        '',
    title:       '',
    subtitle:    '',
    image:       null,
    link:        '',
    button_text: 'Découvrir',
    position:    prePosition,
    type:        'hero',
    order:       0,
    is_active:   true,
    starts_at:   '',
    ends_at:     '',
})

const preview = ref(null)

function onFileChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.image = file
    preview.value = URL.createObjectURL(file)
}

function removeImage() {
    form.image = null
    preview.value = null
}

const isAnnouncementBar = computed(() => form.position === 'announcement_bar')
const isPopup = computed(() => form.position === 'popup_center')

function submit() {
    form.post(route('admin.banners.store'), {
        forceFormData: true,
    })
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.banners.index')"
                class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Bannières
            </a>
            <span class="text-gray-300">/</span>
            <h1 class="text-[15px] font-semibold text-gray-900">Nouvelle bannière</h1>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Aide contextuelle -->
            <div v-if="isAnnouncementBar"
                class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-[13px] font-medium text-amber-800">Barre d'annonce</p>
                    <p class="text-[12px] text-amber-700 mt-0.5">Remplissez le Titre uniquement. L'image n'est pas obligatoire pour ce type de bannière.</p>
                </div>
            </div>

            <div v-if="isPopup"
                class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <div>
                    <p class="text-[13px] font-medium text-indigo-800">Popup centre écran</p>
                    <p class="text-[12px] text-indigo-700 mt-0.5">S'affiche au centre après un court délai. L'image est optionnelle.</p>
                </div>
            </div>

            <!-- Image -->
            <div v-if="!isAnnouncementBar" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Image de la bannière</h2>
                <div class="border-2 border-dashed rounded-xl p-6 text-center transition-colors"
                    :class="preview ? 'border-blue-400' : 'border-gray-200'">
                    <div v-if="!preview" class="space-y-2">
                        <svg class="w-10 h-10 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-[13px] text-gray-500">Glissez-déposez une image ou</p>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-medium rounded-lg transition-colors">
                            Choisir un fichier
                            <input type="file" accept="image/*" class="hidden"
                                @change="onFileChange">
                        </label>
                        <p class="text-[11px] text-gray-400">PNG, JPG, WEBP — max 5 Mo. Recommandé : 1920×600px</p>
                    </div>
                    <div v-else class="relative">
                        <img :src="preview" class="max-h-56 mx-auto rounded-lg">
                        <button type="button" @click="removeImage"
                            class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.image" class="mt-1.5 text-[12px] text-red-500">{{ form.errors.image }}</p>
            </div>

            <!-- Contenu -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Contenu</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Titre <span v-if="isAnnouncementBar" class="text-red-500">*</span>
                        </label>
                        <input v-model="form.title" type="text"
                            :required="isAnnouncementBar"
                            placeholder="Texte de la bannière"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p v-if="form.errors.title" class="mt-1 text-[12px] text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Sous-titre</label>
                        <input v-model="form.subtitle" type="text"
                            placeholder="Sous-titre optionnel"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Lien</label>
                        <input v-model="form.link" type="text" placeholder="https://..."
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Texte du bouton</label>
                        <input v-model="form.button_text" type="text" placeholder="Découvrir"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Paramètres -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Paramètres</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Position *</label>
                        <select v-model="form.position" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option v-for="(label, key) in positions" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.position" class="mt-1 text-[12px] text-red-500">{{ form.errors.position }}</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Type *</label>
                        <select v-model="form.type" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-[12px] text-red-500">{{ form.errors.type }}</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Ordre d'affichage</label>
                        <input v-model.number="form.order" type="number" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex items-center gap-2.5 pt-5">
                        <input v-model="form.is_active" type="checkbox" id="is_active"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-[13px] text-gray-700">Bannière active</label>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Date de début</label>
                        <input v-model="form.starts_at" type="date"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Date de fin</label>
                        <input v-model="form.ends_at" type="date"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p v-if="form.errors.ends_at" class="mt-1 text-[12px] text-red-500">{{ form.errors.ends_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a :href="route('admin.banners.index')"
                    class="h-9 px-4 inline-flex items-center text-[13px] text-gray-600 hover:text-gray-900 transition-colors">
                    Annuler
                </a>
                <button type="submit" :disabled="form.processing"
                    class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-[13px] rounded-lg transition-colors disabled:opacity-60">
                    Créer la bannière
                </button>
            </div>

        </form>

    </div>
</template>
