<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    title:      { type: String,  default: '' },
    side:       { type: String,  default: 'right', validator: (v) => ['right','left'].includes(v) },
    size:       { type: String,  default: 'md',    validator: (v) => ['sm','md','lg','full'].includes(v) },
})
const emit = defineEmits(['update:modelValue'])
const close = () => emit('update:modelValue', false)

const panelSize = computed(() => ({
    sm:   'max-w-sm',
    md:   'max-w-md',
    lg:   'max-w-lg',
    full: 'max-w-full',
}[props.size]))

const enterFrom = computed(() => props.side === 'right' ? 'translate-x-full' : '-translate-x-full')
</script>

<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild
                enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150"  leave-from="opacity-100" leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div :class="['absolute inset-y-0 flex max-w-full', side === 'right' ? 'right-0' : 'left-0']">
                    <TransitionChild
                        enter="transform transition ease-out duration-200"
                        :enter-from="enterFrom" enter-to="translate-x-0"
                        leave="transform transition ease-in duration-150"
                        leave-from="translate-x-0" :leave-to="enterFrom"
                    >
                        <DialogPanel :class="['flex flex-col h-full bg-white shadow-xl', panelSize, 'w-screen']">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
                                <button class="text-gray-400 hover:text-gray-600 transition-colors" @click="close" aria-label="Fermer">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="flex-1 overflow-y-auto px-6 py-4">
                                <slot />
                            </div>
                            <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100">
                                <slot name="footer" />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
