<script setup>
import { computed } from 'vue'
import {
    Shirt, Footprints, Baby, House, Sofa, Utensils, Laptop, Smartphone, Tv, Headphones, Watch, Gem,
    Sparkles, Pill, Dumbbell, Bike, Car, ShoppingBasket, BookOpen, Hammer, Gift, PawPrint, Package, LayoutGrid,
} from 'lucide-vue-next'

// Icône d'une catégorie, choisie dans l'admin (clés : App\Support\CategoryIcons).
// Sans icône choisie : une icône neutre.
const props = defineProps({
    icon: { type: String, default: null },
})

const ICONS = {
    shirt: Shirt, footprints: Footprints, baby: Baby, house: House, sofa: Sofa, utensils: Utensils,
    laptop: Laptop, smartphone: Smartphone, tv: Tv, headphones: Headphones, watch: Watch, gem: Gem,
    sparkles: Sparkles, pill: Pill, dumbbell: Dumbbell, bike: Bike, car: Car, 'shopping-basket': ShoppingBasket,
    'book-open': BookOpen, hammer: Hammer, gift: Gift, 'paw-print': PawPrint, package: Package,
}

const component = computed(() => ICONS[props.icon] ?? LayoutGrid)
</script>

<template>
    <component :is="component" aria-hidden="true" />
</template>
