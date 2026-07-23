<script setup>
const props = defineProps({
    modelValue: { default: '' },
    label:      { type: String, required: true },
    options:    { type: Array,  default: () => [] },
})
const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-500">{{ label }}</label>
        <select
            :value="modelValue"
            class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="emit('update:modelValue', $event.target.value)"
        >
            <option value="">Tous</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
    </div>
</template>
