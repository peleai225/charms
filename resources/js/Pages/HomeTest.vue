<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';

const props = defineProps({
    featured_categories: Array,
    featured_products:   Array,
});

const { formatPrice } = useHelpers();
</script>

<template>
    <FrontLayout title="Test">
        <Head>
            <title>Test Home</title>
        </Head>

        <div class="container mx-auto px-4 py-20">
            <h1 class="text-4xl font-bold mb-8">Test Home Page</h1>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <p class="text-lg mb-4">Nombre de produits vedettes : {{ featured_products?.length || 0 }}</p>
                <p class="text-lg mb-4">Nombre de catégories : {{ featured_categories?.length || 0 }}</p>

                <div v-if="featured_products?.length" class="mt-6">
                    <h2 class="text-xl font-semibold mb-3">Premier produit :</h2>
                    <p class="text-lg">Nom : {{ featured_products[0].name }}</p>
                    <p class="text-lg">Prix : {{ formatPrice(featured_products[0].price) }}</p>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
