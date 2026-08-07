<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    banner:    Object,
    positions: Object,
    types:     Object,
})

const form = useForm({
    name:        props.banner.name ?? '',
    title:       props.banner.title ?? '',
    subtitle:    props.banner.subtitle ?? '',
    image:       null,
    link:        props.banner.link ?? '',
    button_text: props.banner.button_text ?? '',
    position:    props.banner.position ?? 'home_hero',
    type:        props.banner.type ?? 'hero',
    order:       props.banner.order ?? 0,
    is_active:   !!props.banner.is_active,
    starts_at:   props.banner.starts_at ?? '',
    ends_at:     props.banner.ends_at ?? '',
})

const preview = ref(props.banner.image ? '/storage/' + props.banner.image : null)

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

function submit() {
    form.transform(data => ({ ...data, _method: 'PUT' }))
    form.post(route('admin.banners.update', props.banner.id), {
        forceFormData: true,
    })
}

function deleteBanner() {
    if (!confirm('Supprimer cette bannière ?')) return
    router.delete(route('admin.banners.destroy', props.banner.id))
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a :href="route('admin.banners.index')"
                    class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Bannières
                </a>
                <span class="text-gray-300">/</span>
                <h1 class="text-[15px] font-semibold text-gray-900">Modifier la bannière</h1>
            </div>
            <button type="button" @click="deleteBanner"
                class="inline-flex items-center gap-2 h-9 px-3 text-[13px] text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Image -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
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
                            <input type="file" accept="image/*" class="hidden" @change="onFileChange">
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
                <p class="text-[11px] text-gray-400 mt-2">Laissez vide pour conserver l'image actuelle.</p>
                <p v-if="form.errors.image" class="mt-1 text-[12px] text-red-500">{{ form.errors.image }}</p>
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
                    Enregistrer
                </button>
            </div>

        </form>

    </div>
</template>
