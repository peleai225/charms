<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

const props = defineProps({
    product: Object,
});

const { formatPrice } = useHelpers();

const selectedImage = ref(0);
const quantity = ref(1);
const selectedColorId = ref(null);
const selectedSecondaryId = ref(null);

// Variante sélectionnée
const selectedVariant = computed(() => {
    if (!props.product.has_variants) return null;

    return props.product.variants.find(v =>
        v.color_id === selectedColorId.value &&
        v.secondary_id === selectedSecondaryId.value
    );
});

// Prix et stock dynamiques
const currentPrice = computed(() => {
    return selectedVariant.value?.price ?? props.product.price;
});

const currentStock = computed(() => {
    return selectedVariant.value?.stock ?? props.product.stock;
});

// Valeurs secondaires disponibles pour la couleur sélectionnée
const availableSecondaryValues = computed(() => {
    if (!selectedColorId.value || !props.product.secondary_attribute) return [];

    const variantsForColor = props.product.variants.filter(v => v.color_id === selectedColorId.value);
    const secondaryIds = variantsForColor.map(v => v.secondary_id);

    return props.product.secondary_attribute.values.filter(val =>
        secondaryIds.includes(val.id)
    );
});

// Auto-sélection de la première couleur
if (props.product.has_variants && props.product.colors.length > 0) {
    selectedColorId.value = props.product.colors[0].id;

    // Auto-sélection de la première valeur secondaire disponible
    if (props.product.secondary_attribute && availableSecondaryValues.value.length > 0) {
        selectedSecondaryId.value = availableSecondaryValues.value[0].id;
    }
}

// Changer l'image quand on change de couleur
const selectColor = (colorId) => {
    selectedColorId.value = colorId;

    // Réinitialiser la sélection secondaire
    if (props.product.secondary_attribute) {
        const available = props.product.variants
            .filter(v => v.color_id === colorId)
            .map(v => v.secondary_id);

        if (available.length > 0) {
            selectedSecondaryId.value = available[0];
        }
    }

    // Changer l'image si la variante a une image
    const variant = props.product.variants.find(v => v.color_id === colorId);
    if (variant?.image) {
        const imageIndex = props.product.images.indexOf(variant.image);
        if (imageIndex !== -1) {
            selectedImage.value = imageIndex;
        }
    }
};

const form = useForm({
    product_id: props.product.id,
    variant_id: null,
    quantity: 1,
});

const addToCart = () => {
    if (props.product.has_variants && !selectedVariant.value) {
        return; // Validation - variante requise
    }

    form.variant_id = selectedVariant.value?.id ?? null;
    form.quantity = quantity.value;

    form.post('/panier/ajouter', {
        preserveScroll: true,
        onSuccess: () => {
            quantity.value = 1;
        },
    });
};
</script>

<template>
    <FrontLayout :title="product.name">
        <Head>
            <title>{{ product.name }}</title>
            <meta name="description" :content="product.short_description" />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-white border-b border-slate-100 py-4">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm text-slate-400">
                    <Link href="/" class="hover:text-slate-700 transition-colors">Accueil</Link>
                    <span class="text-slate-300">/</span>
                    <Link href="/boutique" class="hover:text-slate-700 transition-colors">Boutique</Link>
                    <template v-if="product.category">
                        <span class="text-slate-300">/</span>
                        <Link :href="`/categorie/${product.category.slug}`" class="hover:text-slate-700 transition-colors">
                            {{ product.category.name }}
                        </Link>
                    </template>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Images -->
                <div>
                    <!-- Image principale -->
                    <div class="aspect-square bg-slate-100 rounded-lg overflow-hidden mb-4">
                        <img
                            v-if="product.images[selectedImage]"
                            :src="`/storage/${product.images[selectedImage]}`"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <!-- Miniatures -->
                    <div v-if="product.images.length > 1" class="grid grid-cols-4 gap-2">
                        <button
                            v-for="(image, index) in product.images"
                            :key="index"
                            @click="selectedImage = index"
                            class="aspect-square bg-slate-100 rounded-lg overflow-hidden border-2 transition"
                            :class="selectedImage === index ? 'border-primary-600' : 'border-transparent hover:border-slate-300'"
                        >
                            <img :src="`/storage/${image}`" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Infos produit -->
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 mb-3">{{ product.name }}</h1>

                    <!-- Prix -->
                    <div class="flex items-baseline gap-3 mb-4">
                        <span class="text-3xl font-bold text-primary-600">
                            {{ formatPrice(currentPrice) }}
                        </span>
                        <span v-if="product.compare_price" class="text-xl text-slate-400 line-through">
                            {{ formatPrice(product.compare_price) }}
                        </span>
                        <Badge v-if="product.compare_price" variant="danger">
                            -{{ Math.round((1 - currentPrice / product.compare_price) * 100) }}%
                        </Badge>
                    </div>

                    <!-- Stock -->
                    <div class="mb-6">
                        <Badge v-if="currentStock > 0" variant="success">
                            En stock ({{ currentStock }} disponibles)
                        </Badge>
                        <Badge v-else variant="danger">
                            Rupture de stock
                        </Badge>
                    </div>

                    <!-- Sélecteur de couleur -->
                    <div v-if="product.has_variants && product.colors.length > 0" class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-3">
                            Couleur
                        </label>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="color in product.colors"
                                :key="color.id"
                                @click="selectColor(color.id)"
                                class="w-10 h-10 rounded-full border-2 transition-all relative"
                                :class="selectedColorId === color.id ? 'border-primary-600 scale-110' : 'border-slate-300 hover:border-slate-400'"
                                :style="{ backgroundColor: color.hex || '#e2e8f0' }"
                                :title="color.name"
                            >
                                <span v-if="selectedColorId === color.id" class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <p class="text-sm text-slate-500 mt-2">
                            {{ product.colors.find(c => c.id === selectedColorId)?.name }}
                        </p>
                    </div>

                    <!-- Sélecteur attribut secondaire (taille, pointure, etc.) -->
                    <div v-if="product.has_variants && product.secondary_attribute" class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-3">
                            {{ product.secondary_attribute.name }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="value in availableSecondaryValues"
                                :key="value.id"
                                @click="selectedSecondaryId = value.id"
                                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-all"
                                :class="selectedSecondaryId === value.id
                                    ? 'border-primary-600 bg-primary-50 text-primary-700'
                                    : 'border-slate-300 text-slate-700 hover:border-slate-400'"
                            >
                                {{ value.value }}
                            </button>
                        </div>
                    </div>

                    <!-- Description courte -->
                    <div v-if="product.short_description" class="mb-6">
                        <p class="text-slate-600 leading-relaxed">{{ product.short_description }}</p>
                    </div>

                    <!-- Quantité + Ajout panier -->
                    <Card padding="default" shadow="sm" class="mb-6">
                        <div class="flex items-center gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Quantité</label>
                                <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                                    <button
                                        @click="quantity = Math.max(1, quantity - 1)"
                                        class="px-4 py-2 bg-slate-50 hover:bg-slate-100 transition"
                                        :disabled="form.processing"
                                    >
                                        -
                                    </button>
                                    <input
                                        v-model.number="quantity"
                                        type="number"
                                        min="1"
                                        :max="currentStock"
                                        class="w-16 text-center border-0 focus:ring-0"
                                        :disabled="form.processing"
                                    />
                                    <button
                                        @click="quantity = Math.min(currentStock, quantity + 1)"
                                        class="px-4 py-2 bg-slate-50 hover:bg-slate-100 transition"
                                        :disabled="form.processing"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-slate-700 mb-2">&nbsp;</label>
                                <Button
                                    @click="addToCart()"
                                    variant="primary"
                                    size="lg"
                                    :loading="form.processing"
                                    :disabled="currentStock === 0 || form.processing || (product.has_variants && !selectedVariant)"
                                    class="w-full"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Ajouter au panier
                                </Button>
                            </div>
                        </div>
                    </Card>

                    <!-- Infos supplémentaires -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <span>SKU: {{ product.sku }}</span>
                        </div>
                        <div v-if="product.category" class="flex items-center gap-2 text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Catégorie: {{ product.category.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description longue -->
            <div v-if="product.description" class="mt-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Description</h2>
                <Card padding="lg">
                    <div class="prose max-w-none" v-html="product.description"></div>
                </Card>
            </div>
        </div>
    </FrontLayout>
</template>
