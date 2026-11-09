<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useUserStore } from '@/Stores/user'
import CategoryIcon from '@/Components/Storefront/CategoryIcon.vue'
import { SHOP_LINKS, isActiveLink } from './shopLinks'

// Menu déroulant téléphone / tablette : rubriques, catégories, compte et assistance.
defineProps({
    open: { type: Boolean, default: false },
})

const page = usePage()
const userStore = useUserStore()

const links = computed(() => [
    ...SHOP_LINKS.map((l) => ({ ...l, active: isActiveLink(l, page.url) })),
    { href: '/boutique', label: 'Toute la boutique', active: page.url === '/boutique' },
])
const categories = computed(() => page.props.nav_categories || [])
const whatsappDigits = computed(() => String(page.props.settings?.social_whatsapp || '').replace(/\D/g, ''))

const row = 'flex h-11 items-center rounded-lg px-3 text-sm font-medium transition-colors'
</script>

<template>
    <div v-if="open" class="max-h-[80vh] overflow-y-auto border-t border-slate-100 bg-white lg:hidden">
        <div class="container mx-auto space-y-3 px-4 py-3">
            <nav aria-label="Rubriques de la boutique" class="space-y-0.5">
                <Link v-for="l in links" :key="l.href" :href="l.href" :class="[row, l.active ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50']"
                    :aria-current="l.active ? 'page' : undefined">
                    {{ l.label }}
                </Link>
            </nav>

            <nav v-if="categories.length" aria-label="Catégories" class="border-t border-slate-100 pt-3">
                <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Catégories</p>
                <div class="grid grid-cols-1 gap-0.5 sm:grid-cols-2">
                    <Link v-for="cat in categories" :key="cat.id" :href="`/categorie/${cat.slug}`"
                        :class="[row, 'gap-3 font-normal text-slate-700 hover:bg-slate-50']">
                        <CategoryIcon v-if="cat.icon" :icon="cat.icon" class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                        <span class="truncate">{{ cat.name }}</span>
                    </Link>
                </div>
            </nav>

            <div class="space-y-0.5 border-t border-slate-100 pt-3">
                <Link :href="userStore.isAuthenticated ? '/mon-compte' : '/connexion'" :class="[row, 'text-slate-700 hover:bg-slate-50']">
                    {{ userStore.isAuthenticated ? 'Mon compte' : 'Se connecter' }}
                </Link>
                <Link href="/mon-compte/favoris" :class="[row, 'text-slate-700 hover:bg-slate-50']">Mes favoris</Link>
                <Link href="/suivi-commande" :class="[row, 'text-slate-700 hover:bg-slate-50']">Suivi de commande</Link>
                <Link href="/contact" :class="[row, 'text-slate-700 hover:bg-slate-50']">Contact</Link>
                <a v-if="whatsappDigits" :href="`https://wa.me/${whatsappDigits}`" target="_blank" rel="noopener"
                    :class="[row, 'text-slate-700 hover:bg-slate-50']">
                    Assistance WhatsApp
                </a>
            </div>
        </div>
    </div>
</template>
