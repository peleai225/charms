<script setup>
// Onglet « Avis » de la fiche produit : note globale et liste des avis approuvés.
defineProps({
    product: { type: Object, required: true }, // { review_avg, review_count, reviews: [...] }
})

const stars = (n) => Array.from({ length: 5 }, (_, i) => i < Math.round(n))
</script>

<template>
    <div>
        <!-- Note globale -->
        <div v-if="product.review_count > 0" class="flex items-center gap-6 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200 max-w-sm">
            <div class="text-center">
                <p class="text-5xl font-bold text-slate-900">{{ product.review_avg }}</p>
                <div class="flex gap-0.5 justify-center mt-1">
                    <svg v-for="(full, i) in stars(product.review_avg)" :key="i" class="w-4 h-4" :class="full ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ product.review_count }} avis</p>
            </div>
        </div>

        <!-- Liste avis -->
        <div v-if="product.reviews.length" class="space-y-5">
            <div v-for="review in product.reviews" :key="review.id" class="border-b border-slate-100 pb-5">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-xs font-semibold text-slate-600">
                            {{ review.author[0] }}
                        </div>
                        <p class="text-sm font-semibold text-slate-900">{{ review.author }}</p>
                    </div>
                    <div class="flex gap-0.5">
                        <svg v-for="(full, i) in stars(review.rating)" :key="i" class="w-3.5 h-3.5" :class="full ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">{{ review.body }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ review.created_at }}</p>
            </div>
        </div>
        <div v-else class="text-sm text-slate-500 py-4">Aucun avis pour ce produit.</div>
    </div>
</template>
