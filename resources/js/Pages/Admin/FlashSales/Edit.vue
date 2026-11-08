<script setup>
import FlashSaleForm from './Partials/FlashSaleForm.vue'

defineProps({
    sale: { type: Object, required: true },
    timezone: { type: String, default: null },
})
</script>

<template>
    <FlashSaleForm :sale="sale" :timezone="timezone" />
</template>
