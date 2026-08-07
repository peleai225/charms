<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const form = useForm({
    elevenlabs_api_key:  props.settings.elevenlabs_api_key  ?? '',
    elevenlabs_voice_id: props.settings.elevenlabs_voice_id ?? '',
})

function save() {
    form.post('/admin/settings/notifications', {
        onSuccess: () => {},
    })
}

const testStatus  = { value: null }
async function testVoice() {
    const key     = form.elevenlabs_api_key.trim()
    const voiceId = form.elevenlabs_voice_id.trim() || '21m00Tcm4TlvDq8ikWAM'
    if (!key) return

    try {
        const res = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${voiceId}/stream`, {
            method: 'POST',
            headers: {
                'xi-api-key':   key,
                'Content-Type': 'application/json',
                'Accept':       'audio/mpeg',
            },
            body: JSON.stringify({
                text:          'Nouvelle commande reçue.',
                model_id:      'eleven_multilingual_v2',
                voice_settings: { stability: 0.5, similarity_boost: 0.8 },
            }),
        })
        if (!res.ok) { alert('Erreur ElevenLabs : ' + res.status + ' — vérifiez la clé API et l\'ID de voix.'); return }
        const buf    = await res.arrayBuffer()
        const ctx    = new (window.AudioContext || window.webkitAudioContext)()
        const decoded = await ctx.decodeAudioData(buf)
        const src    = ctx.createBufferSource()
        src.buffer  = decoded
        src.connect(ctx.destination)
        src.start(0)
    } catch (e) {
        alert('Impossible de joindre ElevenLabs : ' + e.message)
    }
}
</script>

<template>
    <div class="space-y-5">

        <!-- ElevenLabs -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3.536-9.536a5 5 0 000 7.072M19.07 4.93a9 9 0 010 14.14M4.93 4.93a9 9 0 000 14.14"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Voix off — ElevenLabs</h2>
                    <p class="text-[12px] text-gray-500 mt-0.5">Annonce vocale à chaque nouvelle commande. Sans clé, la voix robot du navigateur est utilisée.</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Clé API -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Clé API ElevenLabs
                        <a href="https://elevenlabs.io/app/settings/api-keys" target="_blank"
                           class="ml-1 text-blue-500 hover:underline font-normal">Obtenir →</a>
                    </label>
                    <input v-model="form.elevenlabs_api_key"
                           type="password"
                           placeholder="sk_..."
                           autocomplete="off"
                           class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p v-if="form.errors.elevenlabs_api_key" class="mt-1 text-[11px] text-red-500">{{ form.errors.elevenlabs_api_key }}</p>
                </div>

                <!-- Voice ID -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        ID de la voix
                        <a href="https://elevenlabs.io/app/voice-library" target="_blank"
                           class="ml-1 text-blue-500 hover:underline font-normal">Bibliothèque de voix →</a>
                    </label>
                    <input v-model="form.elevenlabs_voice_id"
                           type="text"
                           placeholder="21m00Tcm4TlvDq8ikWAM  (Rachel — défaut)"
                           class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-[11px] text-gray-400">Copiez l'ID depuis la bibliothèque ElevenLabs. Laissez vide pour utiliser Rachel (voix anglaise multilingue).</p>
                </div>

                <!-- Bouton test -->
                <div class="pt-1">
                    <button type="button" @click="testVoice"
                            :disabled="!form.elevenlabs_api_key"
                            class="h-8 px-4 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tester la voix
                    </button>
                </div>
            </div>
        </div>

        <!-- Enregistrer -->
        <div class="flex justify-end">
            <button @click="save" :disabled="form.processing"
                    class="h-9 px-6 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
        </div>

    </div>
</template>
