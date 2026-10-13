<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

// Bouton « Rechercher par photo » : la photo est réduite dans le navigateur,
// analysée par l'IA puis on affiche la boutique filtrée.
const props = defineProps({
    buttonClass: { type: String, default: '' },
})

const page = usePage()
const enabled = computed(() => !!page.props.settings?.image_search_enabled)

const input = ref(null)
const state = ref(null) // null | 'loading' | 'error'
const errorMessage = ref('')
const previewUrl = ref(null)

function open() {
    input.value?.click()
}

// Réduit la photo (max 1024 px, JPEG) : envoi plus rapide, moins de quota
async function shrink(file) {
    try {
        const bitmap = await createImageBitmap(file)
        const scale = Math.min(1, 1024 / Math.max(bitmap.width, bitmap.height))
        const canvas = document.createElement('canvas')
        canvas.width = Math.round(bitmap.width * scale)
        canvas.height = Math.round(bitmap.height * scale)
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height)
        const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', 0.82))
        return blob ? new File([blob], 'photo.jpg', { type: 'image/jpeg' }) : file
    } catch {
        return file
    }
}

async function onFile(e) {
    const file = e.target.files?.[0]
    e.target.value = ''
    if (!file) return

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = URL.createObjectURL(file)
    state.value = 'loading'

    const data = new FormData()
    data.append('image', await shrink(file))

    try {
        const { data: res } = await axios.post('/recherche-photo', data)
        close()
        router.get('/boutique', {
            terms: res.terms || undefined,
            category: res.category || undefined,
            image_search: res.label || 'votre photo',
        })
    } catch (err) {
        state.value = 'error'
        errorMessage.value = err.response?.status === 429
            ? 'Vous avez fait beaucoup de recherches par photo. Réessayez dans une heure, ou utilisez la recherche par mots.'
            : (err.response?.data?.errors?.image?.[0] ?? err.response?.data?.message ?? 'Une erreur est survenue. Réessayez.')
    }
}

function close() {
    state.value = null
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
}
</script>

<template>
    <template v-if="enabled">
        <button type="button" :class="buttonClass" aria-label="Rechercher par photo" title="Rechercher par photo" @click="open">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>
        <input ref="input" type="file" accept="image/*" class="hidden" @change="onFile">

        <Teleport to="body">
            <div v-if="state" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
                <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl">
                    <img v-if="previewUrl" :src="previewUrl" alt="Votre photo" class="mx-auto mb-4 h-40 w-40 rounded-lg object-cover border border-slate-200">

                    <template v-if="state === 'loading'">
                        <div class="mx-auto mb-3 h-8 w-8 rounded-full border-4 border-slate-200 border-t-blue-600 animate-spin" aria-hidden="true"></div>
                        <p class="text-base font-medium text-slate-900">Recherche de produits similaires…</p>
                        <p class="mt-1 text-sm text-slate-500">Quelques secondes</p>
                    </template>

                    <template v-else>
                        <p class="text-base font-medium text-slate-900">Recherche impossible</p>
                        <p class="mt-1 text-sm text-slate-600">{{ errorMessage }}</p>
                        <div class="mt-5 flex gap-2">
                            <button type="button" class="h-11 flex-1 rounded-lg border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50" @click="close">Fermer</button>
                            <button type="button" class="h-11 flex-1 rounded-lg bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700" @click="close(); open()">Autre photo</button>
                        </div>
                    </template>
                </div>
            </div>
        </Teleport>
    </template>
</template>
