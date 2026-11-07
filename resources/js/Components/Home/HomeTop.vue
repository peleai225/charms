<script setup>
import HeroBanners from './HeroBanners.vue'

// Haut de l'accueil : menu des catégories à gauche, grande bannière au centre, petites cartes à droite.
// (Base : pour l'instant seule la grande bannière s'affiche.)
defineProps({
    heroBanners: { type: Array, default: () => [] },
    sideBanners: { type: Array, default: () => [] }, // bannières position « home_side »
    menuCategories: { type: Array, default: () => [] }, // [{ id, name, slug, icon, children }]
    siteName: { type: String, default: '' },
    featuredProducts: { type: Array, default: () => [] },
    whatsappUrl: { type: String, default: null },
})
</script>

<template>
    <HeroBanners :banners="heroBanners" :site-name="siteName" :featured-products="featuredProducts" :whatsapp-url="whatsappUrl" />
</template>
