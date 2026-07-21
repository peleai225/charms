<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    products: Array,
    featured_categories: Array,
});
</script>

<template>
    <FrontLayout title="Accueil">
        <Head title="Accueil" />

        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary-600 to-primary-700 text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl font-bold mb-6">
                    Bienvenue sur {{ $page.props.settings?.site_name }}
                </h1>
                <p class="text-xl mb-8 text-primary-100">
                    Découvrez notre sélection de produits de qualité
                </p>
                <a
                    href="/boutique"
                    class="inline-block bg-white text-primary-700 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition"
                >
                    Découvrir la boutique
                </a>
            </div>
        </section>

        <!-- Featured Categories -->
        <section v-if="featured_categories?.length" class="py-16">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-slate-900 mb-8">Catégories populaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a
                        v-for="category in featured_categories"
                        :key="category.id"
                        :href="`/categorie/${category.slug}`"
                        class="group relative overflow-hidden rounded-xl bg-slate-200 aspect-[4/3] hover:shadow-xl transition"
                    >
                        <img
                            v-if="category.image"
                            :src="`/storage/${category.image}`"
                            :alt="category.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-6">
                            <h3 class="text-white text-xl font-bold">{{ category.name }}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="products?.length" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-slate-900 mb-8">Produits populaires</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a
                        v-for="product in products"
                        :key="product.id"
                        :href="`/produit/${product.slug}`"
                        class="group bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition"
                    >
                        <div class="aspect-square bg-slate-100 overflow-hidden">
                            <img
                                v-if="product.primary_image"
                                :src="`/storage/${product.primary_image}`"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            >
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900 mb-2 line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-primary-600">
                                    {{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(product.price) }}
                                </span>
                                <span v-if="product.stock > 0" class="text-xs text-success-600 font-medium">
                                    En stock
                                </span>
                                <span v-else class="text-xs text-danger-600 font-medium">
                                    Rupture
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
