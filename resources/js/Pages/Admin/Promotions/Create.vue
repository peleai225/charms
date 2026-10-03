<script setup>
import PromotionForm from './Partials/PromotionForm.vue'

const props = defineProps({
    categories: { type: Array, required: true },
    products:   { type: Array, required: true },
    promotion:  { type: Object, default: null },
})
</script>

<template>
    <PromotionForm
        :categories="categories"
        :products="products"
        :promotion="promotion"
    />
</template>
