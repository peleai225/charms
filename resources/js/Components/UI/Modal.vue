<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    modelValue:       { type: Boolean, required: true },
    title:            { type: String,  default: '' },
    size:             { type: String,  default: 'md', validator: (v) => ['sm','md','lg','xl','full'].includes(v) },
    closable:         { type: Boolean, default: true },
    closeOnBackdrop:  { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])
const close = () => { if (props.closable) emit('update:modelValue', false) }
const onBackdropClick = () => { if (props.closeOnBackdrop) close() }

const panelSize = computed(() => ({
    sm:   'max-w-sm',
    md:   'max-w-md',
    lg:   'max-w-lg',
    xl:   'max-w-xl',
    full: 'max-w-full mx-4',
}[props.size]))
</script>

<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="onBackdropClick">
            <TransitionChild
                enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150"  leave-from="opacity-100" leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        enter="ease-out duration-200" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
                        leave="ease-in duration-150"  leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel :class="['w-full bg-white rounded-xl shadow-xl', panelSize]">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
                                <button
                                    v-if="closable"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                    @click="close"
                                    aria-label="Fermer"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="px-6 py-4">
                                <slot />
                            </div>
                            <div v-if="$slots.footer" class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                                <slot name="footer" />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
