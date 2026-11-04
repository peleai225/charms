<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useConfirm } from '@/Composables/useConfirm'
import { useToast } from '@/Composables/useToast'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

// Faire avancer plusieurs commandes d'un coup. Aucun message n'est envoyé aux clients.
const props = defineProps({
    selectedIds: { type: Array, required: true },
})
const emit = defineEmits(['clear'])

const confirm = useConfirm()
const toast = useToast()
const status = ref('')
const sending = ref(false)

const choices = [
    { value: 'processing', label: 'En préparation' },
    { value: 'shipped', label: 'Expédiée (remise au livreur)' },
    { value: 'delivered', label: 'Livrée (argent encaissé)' },
    { value: 'cancelled', label: 'Annulée (remet le stock)' },
]

async function apply() {
    if (!status.value || !props.selectedIds.length) return
    const choice = choices.find((c) => c.value === status.value)
    const count = props.selectedIds.length
    const ok = await confirm({
        title: `Passer ${count} commande(s) en « ${choice.label} » ?`,
        message: "Les commandes annulées et celles déjà livrées ne seront pas modifiées. Aucun message n'est envoyé aux clients.",
        confirmLabel: 'Appliquer',
        variant: status.value === 'cancelled' ? 'danger' : 'primary',
    })
    if (!ok) return

    sending.value = true
    router.post(route('admin.orders.bulk-status'), { ids: props.selectedIds, status: status.value }, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success(page.props.flash?.success ?? `${count} commande(s) mise(s) à jour`)
            status.value = ''
            emit('clear')
        },
        onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Erreur lors de la mise à jour'),
        onFinish: () => { sending.value = false },
    })
}
</script>

<template>
    <div v-if="selectedIds.length"
        class="sticky top-0 z-20 flex flex-wrap items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
        <p class="text-[13px] font-semibold text-blue-900 mr-auto">{{ selectedIds.length }} commande(s) sélectionnée(s)</p>
        <select v-model="status" aria-label="Nouveau statut"
            class="h-11 px-3 text-[13px] border border-blue-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Passer en…</option>
            <option v-for="c in choices" :key="c.value" :value="c.value">{{ c.label }}</option>
        </select>
        <button type="button" :disabled="!status || sending"
            class="h-11 px-4 text-[13px] font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40"
            @click="apply">{{ sending ? 'Mise à jour…' : 'Appliquer' }}</button>
        <button type="button" class="h-11 px-3 text-[13px] text-blue-800 hover:underline" @click="emit('clear')">Tout désélectionner</button>
    </div>
    <ConfirmModal />
    <ToastContainer />
</template>
