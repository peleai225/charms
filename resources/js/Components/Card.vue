<script setup>
import { computed } from 'vue';

const props = defineProps({
    padding: {
        type: String,
        default: 'default',
        validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value),
    },
    shadow: {
        type: String,
        default: 'default',
        validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value),
    },
});

const classes = computed(() => {
    const base = 'bg-white border border-slate-200 rounded-lg';

    const paddings = {
        none: '',
        sm: 'p-4',
        default: 'p-6',
        lg: 'p-8',
    };

    const shadows = {
        none: '',
        sm: 'shadow-sm',
        default: 'shadow',
        lg: 'shadow-lg',
    };

    return `${base} ${paddings[props.padding]} ${shadows[props.shadow]}`;
});
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
