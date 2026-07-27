<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    tags: Array,
})

const form = useForm({
    name:        '',
    color:       '#2563eb',
    description: '',
    is_auto:     false,
})

function submit() {
    form.post(route('admin.crm.tags.store'), {
        onSuccess: () => form.reset(),
    })
}

const confirmDeleteId = ref(null)

function deleteTag(tag) {
    confirmDeleteId.value = tag.id
}

function confirmDelete() {
    router.delete(route('admin.crm.tags.destroy', confirmDeleteId.value), {
        onFinish: () => { confirmDeleteId.value = null },
    })
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tags & Étiquettes</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Organisez vos clients avec des étiquettes personnalisées</p>
            </div>
            <a :href="route('admin.crm.dashboard')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                ← CRM
            </a>
        </div>

        <!-- Formulaire création tag -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouveau Tag</h3>

            <!-- Erreurs -->
            <div v-if="form.hasErrors" class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-[13px] text-red-700">
                <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
            </div>

            <form @submit.prevent="submit" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" required placeholder="Ex: VIP, Fidèle…"
                        :class="form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2">
                </div>
                <div class="w-20">
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Couleur</label>
                    <input v-model="form.color" type="color"
                        class="w-full h-9 rounded-lg border border-gray-200 cursor-pointer px-1">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Description</label>
                    <input v-model="form.description" type="text" placeholder="Description optionnelle"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2 pb-0.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.is_auto" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-600">Tag automatique</span>
                    </label>
                </div>
                <button type="submit" :disabled="form.processing"
                    class="h-9 px-4 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 inline-flex items-center gap-2">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Créer
                </button>
            </form>
        </div>

        <!-- Liste des tags -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-gray-900">
                    Tags existants
                    <span class="text-gray-400 font-normal">({{ tags?.length ?? 0 }})</span>
                </h3>
            </div>

            <!-- Empty state -->
            <div v-if="!tags?.length" class="py-16 text-center">
                <p class="text-[13px] text-gray-400 mb-1">Aucun tag créé</p>
                <p class="text-[12px] text-gray-300">Créez votre premier tag ci-dessus</p>
            </div>

            <!-- Tags list -->
            <div v-else class="divide-y divide-gray-50">
                <div v-for="tag in tags" :key="tag.id"
                    class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0"
                            :style="{ background: tag.color }">
                        </span>
                        <div>
                            <p class="text-[13px] font-medium text-gray-900">{{ tag.name }}</p>
                            <p v-if="tag.description" class="text-[11px] text-gray-400">{{ tag.description }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[13px] text-gray-500">{{ tag.customers_count }} client(s)</span>
                        <span v-if="tag.is_auto"
                            class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full tracking-wide">
                            AUTO
                        </span>
                        <button @click="deleteTag(tag)"
                            class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal confirmation suppression -->
        <Teleport to="body">
            <div v-if="confirmDeleteId !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="confirmDeleteId = null">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
                    <h3 class="text-[15px] font-semibold text-gray-900 mb-2">Supprimer le tag ?</h3>
                    <p class="text-[13px] text-gray-500 mb-5">
                        Le tag sera retiré de tous les clients associés. Cette action est irréversible.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="confirmDeleteId = null"
                            class="h-9 px-4 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            Annuler
                        </button>
                        <button @click="confirmDelete"
                            class="h-9 px-4 text-[13px] font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
