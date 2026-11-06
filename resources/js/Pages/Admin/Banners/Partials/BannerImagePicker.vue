<script setup>
// Choix d'une image avec aperçu
defineProps({
    label: String,
    hint: String,
    preview: String,
    error: String,
})
const emit = defineEmits(['select', 'remove'])

function onChange(e) {
    const file = e.target.files[0]
    if (file) emit('select', file)
    e.target.value = ''
}
</script>

<template>
    <div>
        <p class="text-[12px] font-medium text-gray-700 mb-2">{{ label }}</p>
        <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors"
            :class="preview ? 'border-blue-400' : 'border-gray-200'">
            <div v-if="!preview" class="space-y-2">
                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-medium rounded-lg transition-colors">
                    Choisir une image
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onChange">
                </label>
                <p class="text-[11px] text-gray-400">{{ hint }}</p>
            </div>
            <div v-else class="relative">
                <img :src="preview" alt="" class="max-h-56 mx-auto rounded-lg">
                <button type="button" aria-label="Retirer l'image" @click="emit('remove')"
                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <p v-if="error" class="mt-1.5 text-[12px] text-red-500">{{ error }}</p>
    </div>
</template>
