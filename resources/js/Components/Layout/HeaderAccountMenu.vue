<script setup>
import { computed, ref, watch, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { onClickOutside } from '@vueuse/core'
import { User, ChevronDown } from 'lucide-vue-next'
import { useUserStore } from '@/Stores/user'

// Raccourci « Mon compte » de l'en-tête : lien de connexion, ou menu du compte une fois connecté.
const page = usePage()
const userStore = useUserStore()
const firstName = computed(() => (userStore.user?.name || '').trim().split(/\s+/)[0] || 'Mon compte')

const open = ref(false)
const root = ref(null)
let timer = null

function show() {
    clearTimeout(timer)
    open.value = true
}
function hideSoon() {
    clearTimeout(timer)
    timer = setTimeout(() => { open.value = false }, 150)
}

onClickOutside(root, () => { open.value = false })
watch(() => page.url, () => { open.value = false })
onUnmounted(() => clearTimeout(timer))

const links = [
    { href: '/mon-compte', label: 'Tableau de bord' },
    { href: '/mon-compte/commandes', label: 'Mes commandes' },
    { href: '/mon-compte/favoris', label: 'Mes favoris' },
    { href: '/mon-compte/fidelite', label: 'Programme fidélité' },
]

const item = 'flex h-11 items-center gap-2 rounded-lg px-2 text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900'
</script>

<template>
    <!-- Invité -->
    <Link v-if="!userStore.isAuthenticated" href="/connexion" :class="item" aria-label="Mon compte : se connecter">
        <User class="h-[22px] w-[22px] shrink-0" aria-hidden="true" />
        <span class="hidden flex-col text-left leading-tight xl:flex">
            <span class="text-[13px] font-semibold text-slate-900">Mon compte</span>
            <span class="text-xs text-slate-500">Se connecter</span>
        </span>
    </Link>

    <!-- Connecté -->
    <div v-else ref="root" class="relative" @mouseenter="show" @mouseleave="hideSoon" @keydown.esc="open = false">
        <button type="button" :class="item" :aria-expanded="String(open)" aria-haspopup="menu"
            :aria-label="`Mon compte (${firstName})`" @click="open = !open">
            <User class="h-[22px] w-[22px] shrink-0" aria-hidden="true" />
            <span class="hidden flex-col text-left leading-tight xl:flex">
                <span class="text-[13px] font-semibold text-slate-900">Mon compte</span>
                <span class="max-w-[7rem] truncate text-xs text-slate-500">{{ firstName }}</span>
            </span>
            <ChevronDown class="hidden h-4 w-4 text-slate-400 xl:block" aria-hidden="true" />
        </button>

        <div v-if="open" role="menu"
            class="absolute right-0 top-full z-50 mt-1 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-100 px-4 py-3">
                <p class="truncate text-sm font-semibold text-slate-900">{{ userStore.user?.name }}</p>
                <p class="truncate text-xs text-slate-500">{{ userStore.user?.email }}</p>
            </div>
            <div class="py-1">
                <Link v-for="l in links" :key="l.href" :href="l.href" role="menuitem"
                    class="flex h-11 items-center px-4 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                    {{ l.label }}
                </Link>
            </div>
            <div class="border-t border-slate-100 py-1">
                <Link href="/deconnexion" method="post" as="button" role="menuitem"
                    class="flex h-11 w-full items-center px-4 text-left text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                    Se déconnecter
                </Link>
            </div>
        </div>
    </div>
</template>
