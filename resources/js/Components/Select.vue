<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    placeholder: String,
    options: {
        type: Array,
        required: true,
    },
    disabled: Boolean,
    required: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const classes = computed(() => {
    const base = 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition';
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

        <select
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :class="classes"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="">{{ placeholder || 'Sélectionner...' }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
