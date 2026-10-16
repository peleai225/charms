<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({ settings: Object })

const providers = {
    gemini: {
        label: 'Google Gemini (gratuit avec quotas)',
        model: 'gemini-3.8-flash',
        keyUrl: 'https://aistudio.google.com/app/apikey',
        placeholder: 'AIza...',
    },
    openrouter: {
        label: 'OpenRouter (Qwen, DeepSeek…)',
        model: 'qwen/qwen2.5-vl-72b-instruct:free',
        keyUrl: 'https://openrouter.ai/keys',
        placeholder: 'sk-or-...',
    },
}

const form = useForm({
    ai_provider: props.settings.ai_provider ?? 'gemini',
    ai_model: props.settings.ai_model ?? '',
    ai_fallback_model: props.settings.ai_fallback_model ?? '',
    ai_api_key: '',
    ai_remove_key: false,
    ai_image_search_enabled: (props.settings.ai_image_search_enabled ?? '1') === '1',
})

const current = computed(() => providers[form.ai_provider] ?? providers.gemini)
const keySet = computed(() => !!props.settings.ai_api_key_set)

const testing = ref(false)
const testResult = ref(null)

function save() {
    form.post('/admin/settings/ai', {
        preserveScroll: true,
        onSuccess: () => { form.ai_api_key = ''; form.ai_remove_key = false },
    })
}

function removeKey() {
    form.ai_remove_key = true
    save()
}

async function test() {
    testing.value = true
    testResult.value = null
    try {
        const { data } = await axios.post('/admin/settings/ai/test')
        testResult.value = data
    } catch (e) {
        testResult.value = { ok: false, message: e.response?.data?.message ?? 'Erreur de connexion' }
    } finally {
        testing.value = false
    }
}
</script>

<template>
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900">Intelligence artificielle</h2>
                <p class="text-[12px] text-gray-500 mt-0.5">
                    Génère les fiches produits à partir des photos et propose les catégories. Vous validez toujours avant l'enregistrement.
                </p>
            </div>

            <div class="space-y-4 max-w-xl">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fournisseur</label>
                    <select v-model="form.ai_provider"
                        class="w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option v-for="(p, key) in providers" :key="key" :value="key">{{ p.label }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Clé API
                        <a :href="current.keyUrl" target="_blank" rel="noopener" class="ml-1 text-blue-600 hover:underline font-normal">Obtenir une clé →</a>
                    </label>
                    <input v-model="form.ai_api_key" type="password" autocomplete="off"
                        :placeholder="keySet ? '•••••••• Clé enregistrée (laisser vide pour la conserver)' : current.placeholder"
                        class="w-full h-11 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="mt-1.5 flex items-center gap-3 text-[12px]">
                        <span v-if="keySet" class="text-green-700">✓ Une clé est enregistrée</span>
                        <span v-else class="text-amber-700">Aucune clé enregistrée</span>
                        <button v-if="keySet" type="button" class="text-red-600 hover:underline" @click="removeKey">Supprimer la clé</button>
                    </div>
                    <p v-if="form.errors.ai_api_key" class="mt-1 text-[12px] text-red-600">{{ form.errors.ai_api_key }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Modèle <span class="font-normal text-gray-400">(facultatif)</span></label>
                    <input v-model="form.ai_model" type="text" :placeholder="current.model"
                        class="w-full h-11 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-[12px] text-gray-500">Laissez vide pour utiliser le modèle recommandé : <code>{{ current.model }}</code>. Le modèle doit savoir lire les images.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Modèle de secours <span class="font-normal text-gray-400">(facultatif)</span></label>
                    <input v-model="form.ai_fallback_model" type="text" placeholder="ex : un modèle « flash-lite » listé dans votre console"
                        class="w-full h-11 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-[12px] text-gray-500">Utilisé automatiquement si le modèle principal est surchargé ou a atteint son quota. Doit aussi savoir lire les images.</p>
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input v-model="form.ai_image_search_enabled" type="checkbox" class="mt-0.5 w-5 h-5 rounded border-gray-300">
                    <span>
                        <span class="block text-[13px] font-medium text-gray-800">Recherche par photo pour les clients</span>
                        <span class="block text-[12px] text-gray-500">Affiche une icône appareil photo dans la barre de recherche de la boutique. Limité à 6 recherches par heure et par visiteur.</span>
                    </span>
                </label>

                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <button type="button" :disabled="form.processing" @click="save"
                        class="h-11 px-5 text-[13px] font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                    <button type="button" :disabled="testing || !keySet" @click="test"
                        class="h-11 px-5 text-[13px] font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40">
                        {{ testing ? 'Test en cours…' : 'Tester la connexion' }}
                    </button>
                </div>

                <p v-if="testResult" class="text-[13px] rounded-lg px-3 py-2"
                    :class="testResult.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-700'">
                    {{ testResult.message }}
                </p>

                <p class="text-[12px] text-gray-500 border-t border-gray-100 pt-3">
                    Avec les offres gratuites, les photos et textes envoyés peuvent être utilisés par le fournisseur pour améliorer ses modèles. Aucune donnée client n'est envoyée.
                </p>
            </div>
        </div>
    </div>
</template>
