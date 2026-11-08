<script setup>
import FlashSaleForm from './Partials/FlashSaleForm.vue'

defineProps({
    sale: { type: Object, default: null },
    timezone: { type: String, default: null },
})
</script>

<template>
    <FlashSaleForm :sale="sale" :timezone="timezone" />
</template>
