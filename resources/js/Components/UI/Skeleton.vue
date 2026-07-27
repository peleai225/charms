<script setup>
defineProps({
    rounded: { type: Boolean, default: false },
})
</script>

<template>
    <div
        class="animate-pulse bg-gray-200"
        :class="{ 'rounded-full': rounded }"
    />
</template>
