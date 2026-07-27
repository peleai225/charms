<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    banners: {
        type: Array,
        default: () => [],
    },
    autoplayInterval: {
        type: Number,
        default: 5000,
    },
});

const currentIndex = ref(0);
let timer = null;

const currentBanner = computed(() => props.banners[currentIndex.value] || null);

const next = () => {
    if (!props.banners.length) return;
    currentIndex.value = (currentIndex.value + 1) % props.banners.length;
};

const prev = () => {
    if (!props.banners.length) return;
    currentIndex.value = (currentIndex.value - 1 + props.banners.length) % props.banners.length;
};

const goTo = (index) => {
    currentIndex.value = index;
    resetAutoplay();
};

const startAutoplay = () => {
    if (props.banners.length > 1) {
        timer = setInterval(next, props.autoplayInterval);
    }
};

const stopAutoplay = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

const resetAutoplay = () => {
    stopAutoplay();
    startAutoplay();
};

// Touch swipe support
let touchStartX = 0;
let touchEndX = 0;

const onTouchStart = (e) => {
    touchStartX = e.changedTouches[0].screenX;
    stopAutoplay();
};

const onTouchEnd = (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
    startAutoplay();
};

const handleSwipe = () => {
    const diff = touchEndX - touchStartX;
    if (Math.abs(diff) > 50) {
        if (diff < 0) next();
        else prev();
    }
};

onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <div
        v-if="banners.length"
        class="relative overflow-hidden group bg-slate-900 text-white min-h-[380px] md:min-h-[480px] flex items-center"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
        @touchstart="onTouchStart"
        @touchend="onTouchEnd"
    >
        <!-- Slides -->
        <div class="w-full h-full relative">
            <TransitionGroup name="carousel-slide">
                <div
                    v-for="(b, idx) in banners"
                    v-show="idx === currentIndex"
                    :key="b.id || idx"
                    class="absolute inset-0 w-full h-full flex items-center justify-center p-6 md:p-12 text-center bg-cover bg-center"
                    :style="b.background_color ? { backgroundColor: b.background_color } : {}"
                >
                    <!-- Background image overlay -->
                    <img
                        v-if="b.image"
                        :src="b.image.startsWith('/') || b.image.startsWith('http') ? b.image : `/storage/${b.image}`"
                        :alt="b.title || 'Bannière'"
                        class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-700"
                    />

                    <!-- Banner Content -->
                    <div
                        class="relative z-10 max-w-3xl mx-auto space-y-4"
                        :style="{ color: b.text_color || '#ffffff' }"
                    >
                        <span
                            v-if="b.subtitle"
                            class="inline-block px-3.5 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider mb-2"
                        >
                            {{ b.subtitle }}
                        </span>

                        <h2
                            v-if="b.title"
                            class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight drop-shadow-md"
                        >
                            {{ b.title }}
                        </h2>

                        <div v-if="b.button_text && b.link" class="pt-4">
                            <a
                                :href="b.link"
                                class="inline-flex items-center gap-2 px-7 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-primary-500/25 hover:-translate-y-0.5 transition-all duration-200"
                            >
                                {{ b.button_text }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Arrows (hidden on small mobile, visible on hover/desktop) -->
        <template v-if="banners.length > 1">
            <button
                @click="prev"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-900/40 hover:bg-slate-900/80 backdrop-blur-md text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20 focus:outline-none"
                aria-label="Bannière précédente"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <button
                @click="next"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-900/40 hover:bg-slate-900/80 backdrop-blur-md text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20 focus:outline-none"
                aria-label="Bannière suivante"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Dots -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                <button
                    v-for="(_, idx) in banners"
                    :key="idx"
                    @click="goTo(idx)"
                    class="h-2 rounded-full transition-all duration-300 focus:outline-none"
                    :class="idx === currentIndex ? 'w-8 bg-primary-500' : 'w-2 bg-white/50 hover:bg-white/80'"
                    :aria-label="`Aller à la bannière ${idx + 1}`"
                />
            </div>
        </template>
    </div>
</template>

<style scoped>
.carousel-slide-enter-active,
.carousel-slide-leave-active {
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.carousel-slide-enter-from {
    opacity: 0;
    transform: scale(1.03);
}

.carousel-slide-leave-to {
    opacity: 0;
    transform: scale(0.98);
}
</style>
