<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Headset, Heart, ShoppingCart } from 'lucide-vue-next'
import { useCartStore } from '@/Stores/cart'
import HeaderAccountMenu from './HeaderAccountMenu.vue'

// Raccourcis de l'en-tête : Assistance (si un WhatsApp est réglé), favoris, compte, panier.
// Libellés visibles sur grand écran, icônes seules en dessous.
const emit = defineEmits(['open-cart'])

const page = usePage()
const cartStore = useCartStore()

const whatsappDigits = computed(() => String(page.props.settings?.social_whatsapp || '').replace(/\D/g, ''))

const item = 'flex h-11 items-center gap-2 rounded-lg px-2 text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900'
const label = 'hidden flex-col text-left leading-tight xl:flex'
</script>

<template>
    <div class="flex items-center gap-0.5 xl:gap-1">
        <a v-if="whatsappDigits" :href="`https://wa.me/${whatsappDigits}`" target="_blank" rel="noopener"
            :class="[item, 'hidden lg:flex']" aria-label="Assistance par WhatsApp">
            <Headset class="h-[22px] w-[22px] shrink-0" aria-hidden="true" />
            <span :class="label">
                <span class="text-[13px] font-semibold text-slate-900">Assistance</span>
                <span class="text-xs text-slate-500">WhatsApp</span>
            </span>
        </a>

        <Link href="/mon-compte/favoris" :class="[item, 'hidden md:flex']" aria-label="Mes favoris">
            <Heart class="h-[22px] w-[22px] shrink-0" aria-hidden="true" />
            <span :class="label">
                <span class="text-[13px] font-semibold text-slate-900">Mes favoris</span>
            </span>
        </Link>

        <div class="hidden md:block">
            <HeaderAccountMenu />
        </div>

        <button type="button" :class="item" :aria-label="`Panier (${cartStore.count} article${cartStore.count > 1 ? 's' : ''})`"
            @click="emit('open-cart')">
            <span class="relative">
                <ShoppingCart class="h-[22px] w-[22px]" aria-hidden="true" />
                <span v-if="cartStore.count > 0"
                    class="absolute -right-2 -top-2 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-primary-600 px-1 text-[10px] font-bold leading-none text-primary-contrast">
                    {{ cartStore.count > 99 ? '99+' : cartStore.count }}
                </span>
            </span>
            <span :class="label">
                <span class="text-[13px] font-semibold text-slate-900">Panier</span>
            </span>
        </button>
    </div>
</template>
