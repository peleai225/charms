<script setup>
import { useForm } from '@inertiajs/vue3'

// Ajouter ou modifier un livreur
const props = defineProps({
    courier: { type: Object, default: null }, // null = nouveau livreur
})
const emit = defineEmits(['close'])

const form = useForm({
    name: props.courier?.name ?? '',
    phone: props.courier?.phone ?? '',
    type: props.courier?.type ?? 'independent',
    notes: props.courier?.notes ?? '',
    is_active: props.courier?.is_active ?? true,
})

function submit() {
    const options = { preserveScroll: true, onSuccess: () => emit('close') }
    if (props.courier) form.put(route('admin.couriers.update', props.courier.id), options)
    else form.post(route('admin.couriers.store'), options)
}

const input = 'w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
        <form class="w-full max-w-md rounded-xl bg-white shadow-xl p-5 space-y-4" @submit.prevent="submit">
            <h2 class="text-lg font-semibold text-gray-900">{{ courier ? 'Modifier le livreur' : 'Nouveau livreur' }}</h2>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                <input v-model="form.name" type="text" maxlength="100" required :class="input" placeholder="Ex : Koné Ibrahim ou Yango">
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Téléphone (WhatsApp) *</label>
                <input v-model="form.phone" type="tel" maxlength="30" required :class="input" placeholder="Ex : 0707070707">
                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
            </div>
            <div>
                <p class="block text-xs font-medium text-gray-700 mb-1">Type *</p>
                <div class="grid grid-cols-2 gap-2">
                    <label v-for="t in [{ v: 'independent', l: 'Indépendant', d: 'Rend l\'argent au point' }, { v: 'company', l: 'Société', d: 'Reverse plus tard' }]" :key="t.v"
                        class="flex flex-col gap-0.5 rounded-lg border p-3 cursor-pointer"
                        :class="form.type === t.v ? 'border-blue-600 bg-blue-50' : 'border-gray-200'">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-900">
                            <input v-model="form.type" type="radio" :value="t.v" class="w-4 h-4"> {{ t.l }}
                        </span>
                        <span class="text-xs text-gray-500">{{ t.d }}</span>
                    </label>
                </div>
                <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Note</label>
                <textarea v-model="form.notes" rows="2" maxlength="500" placeholder="Zone, moto, horaires…"
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <label v-if="courier" class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded border-gray-300"> Livreur actif
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="h-11 px-4 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" @click="emit('close')">Annuler</button>
                <button type="submit" :disabled="form.processing"
                    class="h-11 px-5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </div>
        </form>
    </div>
</template>
