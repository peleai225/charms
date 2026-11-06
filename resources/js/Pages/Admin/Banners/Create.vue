<script setup>
import { useForm } from '@inertiajs/vue3'
import BannerForm from './Partials/BannerForm.vue'

const props = defineProps({
    positions: Object,
    types:     Object,
})

// Pré-sélectionner la position si passée via query string
const prePosition = new URLSearchParams(window.location.search).get('position')

const form = useForm({
    name:             '',
    title:            '',
    subtitle:         '',
    description:      '',
    image:            null,
    image_mobile:     null,
    link:             '',
    button_text:      'Découvrir',
    background_color: '',
    text_color:       '',
    position:         prePosition && props.positions[prePosition] ? prePosition : 'home_hero',
    type:             'hero',
    order:            0,
    is_active:        true,
    show_countdown:   false,
    starts_at:        '',
    ends_at:          '',
})

function submit() {
    form.post(route('admin.banners.store'), { forceFormData: true })
}
</script>

<template>
    <div class="p-6 space-y-5">
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

        <form class="space-y-5" @submit.prevent="submit">
            <BannerForm :form="form" :positions="positions" :types="types" />

            <div class="flex items-center justify-end gap-3">
                <a :href="route('admin.banners.index')"
                    class="h-9 px-4 inline-flex items-center text-[13px] text-gray-600 hover:text-gray-900 transition-colors">
                    Annuler
                </a>
                <button type="submit" :disabled="form.processing"
                    class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-[13px] rounded-lg transition-colors disabled:opacity-60">
                    {{ form.processing ? 'Enregistrement…' : 'Créer la bannière' }}
                </button>
            </div>
        </form>
    </div>
</template>
