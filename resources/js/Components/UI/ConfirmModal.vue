<script setup>
import { computed } from 'vue'
import { useConfirmStore } from '@/Stores/confirm'
import Modal from './Modal.vue'
import Button from '@/Components/Button.vue'

const store = useConfirmStore()

const opts = computed(() => ({
    title:        store.options.title        || 'Confirmer',
    message:      store.options.message      || '',
    confirmLabel: store.options.confirmLabel || 'Confirmer',
    cancelLabel:  store.options.cancelLabel  || 'Annuler',
    variant:      store.options.variant      || 'default',
}))

const confirmVariant = computed(() => opts.value.variant === 'danger' ? 'danger' : opts.value.variant === 'warning' ? 'secondary' : 'primary')
</script>

<template>
    <Modal
        :model-value="store.isOpen"
        :title="opts.title"
        size="sm"
        :close-on-backdrop="false"
        :closable="false"
        @update:model-value="store.resolve(false)"
    >
        <p v-if="opts.message" class="text-sm text-gray-600">{{ opts.message }}</p>

        <template #footer>
            <Button variant="ghost" @click="store.resolve(false)">{{ opts.cancelLabel }}</Button>
            <Button :variant="confirmVariant" @click="store.resolve(true)">{{ opts.confirmLabel }}</Button>
        </template>
    </Modal>
</template>
