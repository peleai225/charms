<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String,
    error: String,
    placeholder: String,
    disabled: Boolean,
    required: Boolean,
    rows: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(['update:modelValue']);

const classes = computed(() => {
    const base = 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition resize-none';
    const error = props.error
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
        : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500';
    const disabled = props.disabled ? 'bg-slate-50 cursor-not-allowed' : 'bg-white';

    return `${base} ${error} ${disabled}`;
});
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <textarea
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :rows="rows"
            :class="classes"
            @input="$emit('update:modelValue', $event.target.value)"
        />

        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
