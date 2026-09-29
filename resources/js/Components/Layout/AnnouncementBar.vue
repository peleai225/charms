<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    announcements: { type: Array, default: () => [] },
    popup: { type: Object, default: null },
});

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role || null);
const hasAdminBar = computed(() =>
    ['admin', 'manager', 'staff', 'superadmin'].includes(userRole.value)
);

const announcementIndex  = ref(0);
const announcementHidden = ref(false);
const popupVisible       = ref(false);
let   announcementTimer  = null;

function dismissAnnouncement() {
    announcementHidden.value = true;
    if (props.announcements[0]) {
        try { localStorage.setItem(`ann_dismissed_${props.announcements[0].id}`, '1'); } catch {}
    }
}

function dismissPopup() {
    popupVisible.value = false;
    if (props.popup) {
        try { localStorage.setItem(`popup_dismissed_${props.popup.id}`, '1'); } catch {}
    }
}

onMounted(() => {
    if (props.announcements[0]) {
        try {
            if (localStorage.getItem(`ann_dismissed_${props.announcements[0].id}`) === '1') {
                announcementHidden.value = true;
            }
        } catch {}
    }
    if (props.announcements.length > 1) {
        announcementTimer = setInterval(() => {
            announcementIndex.value = (announcementIndex.value + 1) % props.announcements.length;
        }, 4000);
    }
    if (props.popup) {
        try {
            if (localStorage.getItem(`popup_dismissed_${props.popup.id}`) !== '1') {
                setTimeout(() => { popupVisible.value = true; }, 1500);
            }
        } catch {
            setTimeout(() => { popupVisible.value = true; }, 1500);
        }
    }
});

onUnmounted(() => {
    if (announcementTimer) clearInterval(announcementTimer);
});
</script>

<template>
    <!-- Barre d'annonce dynamique -->
    <Transition enter-from-class="opacity-0 -translate-y-2" enter-active-class="transition duration-300">
    <div v-if="announcements.length && !announcementHidden"
         class="relative text-sm py-2.5 text-center px-10 shrink-0 overflow-hidden"
         :style="{
             backgroundColor: announcements[announcementIndex]?.background_color || '#2563EB',
             color: announcements[announcementIndex]?.text_color || '#ffffff',
         }">
        <div class="flex items-center justify-center gap-2 flex-wrap">
            <span class="font-medium">{{ announcements[announcementIndex]?.title }}</span>
            <span v-if="announcements[announcementIndex]?.subtitle" class="opacity-80 hidden sm:inline">
                {{ announcements[announcementIndex]?.subtitle }}
            </span>
            <a v-if="announcements[announcementIndex]?.link"
               :href="announcements[announcementIndex].link"
               class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 hover:bg-white/30 transition-colors">
                {{ announcements[announcementIndex]?.button_text || 'Découvrir' }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div v-if="announcements.length > 1" class="flex items-center justify-center gap-1 mt-1">
            <button v-for="(_, i) in announcements" :key="i"
                    @click="announcementIndex = i"
                    class="w-1.5 h-1.5 rounded-full transition-colors"
                    :class="i === announcementIndex ? 'bg-white' : 'bg-white/40'"/>
        </div>
        <button @click="dismissAnnouncement"
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 opacity-60 hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    </Transition>

    <!-- Fallback topbar si aucune annonce -->
    <div v-if="!announcements.length && !hasAdminBar"
         class="bg-slate-900 text-slate-400 text-xs py-2 text-center px-4 hidden sm:block shrink-0">
        <span>Livraison rapide partout en Côte d'Ivoire</span>
        <span class="mx-3 text-slate-700">·</span>
        <span>Paiement sécurisé</span>
        <span class="mx-3 text-slate-700">·</span>
        <span>Support 7j/7 sur WhatsApp</span>
    </div>

    <!-- Popup bannière -->
    <Transition enter-from-class="opacity-0" enter-active-class="transition duration-300"
                leave-to-class="opacity-0" leave-active-class="transition duration-200">
    <div v-if="popup && popupVisible"
         class="fixed inset-0 z-[500] flex items-center justify-center p-4"
         @keydown.escape="dismissPopup" tabindex="-1">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="dismissPopup"/>
        <div class="relative z-10 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl"
             :style="{ backgroundColor: popup.background_color || '#1e293b' }">
            <img v-if="popup.image_url" :src="popup.image_url" class="w-full object-cover max-h-52"/>
            <div class="p-6" :style="{ color: popup.text_color || '#ffffff' }">
                <h3 class="text-xl font-bold leading-tight">{{ popup.title }}</h3>
                <p v-if="popup.subtitle" class="mt-1 opacity-80 text-sm">{{ popup.subtitle }}</p>
                <p v-if="popup.description" class="mt-3 text-sm opacity-70 leading-relaxed">{{ popup.description }}</p>
                <div class="mt-5 flex items-center gap-3">
                    <a v-if="popup.link" :href="popup.link"
                       class="flex-1 text-center px-4 py-2.5 rounded-xl bg-white font-semibold text-sm transition-opacity hover:opacity-90"
                       :style="{ color: popup.background_color || '#1e293b' }"
                       @click="dismissPopup">
                        {{ popup.button_text || 'Découvrir' }}
                    </a>
                    <button @click="dismissPopup"
                            class="px-4 py-2.5 rounded-xl text-sm opacity-60 hover:opacity-100 transition-opacity border border-white/20">
                        Fermer
                    </button>
                </div>
            </div>
            <button @click="dismissPopup" class="absolute top-3 right-3 p-1.5 rounded-full bg-black/20 hover:bg-black/40 transition-colors text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    </Transition>
</template>
