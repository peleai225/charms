<script setup>
// « Ventes Flash » : bloc de la vente en cours avec compte à rebours et produits.
// (Base : rien ne s'affiche tant qu'aucune vente n'est fournie.)
defineProps({
    sale: { type: Object, default: null }, // { id, name, ends_at, products: [...] }
})
</script>

<template>
    <section v-if="sale && sale.products?.length" class="py-10 bg-white" />
</template>
