<script setup>
import { useToastStore } from '@/Stores/toast'
import Toast from './Toast.vue'

const store = useToastStore()
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
            aria-live="polite"
        >
            <TransitionGroup
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="translate-x-4 opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-4 opacity-0"
            >
                <div v-for="toast in store.toasts" :key="toast.id" class="pointer-events-auto">
                    <Toast :toast="toast" />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
