<script setup>
const props = defineProps({
    modelValue: { type: Object, default: () => ({ from: '', to: '' }) },
    label:      { type: String, required: true },
})
const emit = defineEmits(['update:modelValue'])

const update = (key, val) => emit('update:modelValue', { ...props.modelValue, [key]: val })
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-500">{{ label }}</label>
        <div class="flex items-center gap-2">
            <input
                type="date"
                :value="modelValue.from"
                class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="update('from', $event.target.value)"
            />
            <span class="text-gray-400 text-sm">→</span>
            <input
                type="date"
                :value="modelValue.to"
                class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="update('to', $event.target.value)"
            />
        </div>
    </div>
</template>
