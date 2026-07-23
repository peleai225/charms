<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md'].includes(v),
    },
})

const classes = computed(() => {
    const variants = {
        default: 'bg-gray-100 text-gray-700',
        success: 'bg-green-100 text-green-700',
        warning: 'bg-yellow-100 text-yellow-700',
        danger:  'bg-red-100 text-red-700',
        info:    'bg-blue-100 text-blue-700',
    }
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-sm',
    }
    return `inline-flex items-center font-medium rounded-full ${variants[props.variant]} ${sizes[props.size]}`
})
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
