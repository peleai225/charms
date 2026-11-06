<script setup>
import { useForm, router } from '@inertiajs/vue3'
import BannerForm from './Partials/BannerForm.vue'

const props = defineProps({
    banner:    Object,
    positions: Object,
    types:     Object,
})

const b = props.banner

const form = useForm({
    name:                b.name ?? '',
    title:               b.title ?? '',
    subtitle:            b.subtitle ?? '',
    description:         b.description ?? '',
    image:               null,
    image_mobile:        null,
    remove_image_mobile: false,
    link:                b.link ?? '',
    button_text:         b.button_text ?? '',
    background_color:    b.background_color ?? '',
    text_color:          b.text_color ?? '',
    position:            b.position ?? 'home_hero',
    type:                b.type ?? 'hero',
    order:               b.order ?? 0,
    is_active:           !!b.is_active,
    show_countdown:      !!b.show_countdown,
    starts_at:           b.starts_at ?? '',
    ends_at:             b.ends_at ?? '',
})

function submit() {
    form.transform((data) => ({ ...data, _method: 'PUT' }))
    form.post(route('admin.banners.update', b.id), { forceFormData: true })
}

function deleteBanner() {
    if (!confirm('Supprimer cette bannière ?')) return
    router.delete(route('admin.banners.destroy', b.id))
}
</script>

<template>
    <div class="p-6 space-y-5">
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

        <form class="space-y-5" @submit.prevent="submit">
            <BannerForm :form="form" :positions="positions" :types="types"
                :image-url="b.image ? '/storage/' + b.image : null"
                :image-mobile-url="b.image_mobile ? '/storage/' + b.image_mobile : null" />

            <div class="flex items-center justify-end gap-3">
                <a :href="route('admin.banners.index')"
                    class="h-9 px-4 inline-flex items-center text-[13px] text-gray-600 hover:text-gray-900 transition-colors">
                    Annuler
                </a>
                <button type="submit" :disabled="form.processing"
                    class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-[13px] rounded-lg transition-colors disabled:opacity-60">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </div>
        </form>
    </div>
</template>
