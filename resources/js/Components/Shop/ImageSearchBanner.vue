<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    label: { type: String, required: true },
    fallback: { type: Boolean, default: false },
})
</script>

<template>
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-900">Résultats pour votre photo : « {{ label }} »</p>
            <p v-if="fallback" class="text-sm text-slate-600 mt-0.5">
                Aucun article identique trouvé. Voici des articles de la même catégorie.
            </p>
        </div>
        <Link href="/boutique" class="h-11 px-4 inline-flex items-center rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">
            Effacer
        </Link>
    </div>
</template>
