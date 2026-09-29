<script setup>
const props = defineProps({
    open:  { type: Boolean, default: false },
    query: { type: String,  default: '' },
});

const emit = defineEmits(['update:open', 'update:query', 'submit']);

function close() { emit('update:open', false); }
</script>

<template>
    <Transition name="search-overlay">
        <div v-if="open"
             class="fixed inset-0 z-[60] bg-black/50 flex items-start justify-center pt-20 px-4"
             @click.self="close">
            <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100">
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input
                        :value="query"
                        @input="emit('update:query', $event.target.value)"
                        @keyup.enter="emit('submit')"
                        @keyup.esc="close"
                        type="search"
                        placeholder="Rechercher un produit..."
                        class="flex-1 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none"
                        autofocus
                    />
                    <button @click="close" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-3 text-xs text-slate-400 flex items-center justify-between">
                    <span>Appuyez sur <kbd class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600 font-mono">Entrée</kbd> pour rechercher</span>
                    <button @click="emit('submit')" :disabled="!query.trim()"
                            class="text-xs font-semibold text-slate-700 hover:text-slate-900 disabled:opacity-40 transition">
                        Rechercher →
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.search-overlay-enter-active,
.search-overlay-leave-active {
    transition: opacity 0.2s ease;
}
.search-overlay-enter-from,
.search-overlay-leave-to {
    opacity: 0;
}
</style>
