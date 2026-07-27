<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['info', 'success', 'warning', 'danger'].includes(value),
    },
    title: String,
    dismissible: Boolean,
});

const emit = defineEmits(['dismiss']);

const classes = computed(() => {
    const base = 'border-l-4 p-4 rounded-lg';

    const types = {
        info: 'bg-blue-50 border-blue-500 text-blue-900',
        success: 'bg-success-50 border-success-500 text-success-900',
        warning: 'bg-warning-50 border-warning-500 text-warning-900',
        danger: 'bg-red-50 border-red-500 text-red-900',
    };

    return `${base} ${types[props.type]}`;
});

const iconPath = computed(() => {
    const paths = {
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        danger: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return paths[props.type];
});
</script>

<template>
    <div :class="classes">
        <div class="flex items-start">
            <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
            </svg>

            <div class="flex-1">
                <h3 v-if="title" class="font-semibold mb-1">{{ title }}</h3>
                <div class="text-sm">
                    <slot />
                </div>
            </div>

            <button
                v-if="dismissible"
                @click="$emit('dismiss')"
                class="ml-3 text-current opacity-50 hover:opacity-100 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>
