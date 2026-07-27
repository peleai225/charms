<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const form = useForm({
    payment_cod_enabled:         props.settings.payment_cod_enabled !== '0',
    payment_moneyfusion_enabled: props.settings.payment_moneyfusion_enabled === '1',
    moneyfusion_api_url:         props.settings.moneyfusion_api_url ?? '',
    moneyfusion_api_key:         props.settings.moneyfusion_api_key ?? '',
    payment_jeko_enabled:        props.settings.payment_jeko_enabled === '1',
    jeko_api_key:                props.settings.jeko_api_key ?? '',
    jeko_api_key_id:             props.settings.jeko_api_key_id ?? '',
    jeko_store_id:               props.settings.jeko_store_id ?? '',
    jeko_webhook_secret:         props.settings.jeko_webhook_secret ?? '',
})

// Test de connexion Jeko
const jekoTestStatus  = ref(null)
const jekoTestMessage = ref('')

async function testJeko() {
    jekoTestStatus.value  = 'testing'
    jekoTestMessage.value = ''
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        const res  = await fetch('/admin/settings/payment/test-jeko', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({
                api_key:    form.jeko_api_key,
                api_key_id: form.jeko_api_key_id,
                store_id:   form.jeko_store_id,
            }),
        })
        const data = await res.json()
        jekoTestStatus.value  = data.success ? 'ok' : 'error'
        jekoTestMessage.value = data.message
    } catch {
        jekoTestStatus.value  = 'error'
        jekoTestMessage.value = 'Erreur réseau.'
    }
}

function submit() {
    form.post(route('admin.settings.payment.update'), { preserveScroll: true })
}

const inputCls = 'w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent'
</script>

<template>
    <div class="space-y-5">
        <form @submit.prevent="submit" class="space-y-5">

            <!-- COD -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-semibold text-gray-900">Paiement à la livraison</h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input v-model="form.payment_cod_enabled" type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <p class="text-[13px] text-gray-500">Permettre aux clients de payer en espèces à la réception.</p>
            </div>

            <!-- MoneyFusion -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-[11px]">MF</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-gray-900">MoneyFusion</h3>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input v-model="form.payment_moneyfusion_enabled" type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>
                <p class="text-[13px] text-gray-500 mb-4">Orange Money, MTN, Wave, Moov — Afrique.</p>
                <div v-show="form.payment_moneyfusion_enabled" class="space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">URL API</label>
                        <input v-model="form.moneyfusion_api_url" type="text" placeholder="https://..." :class="inputCls">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Clé API (optionnel)</label>
                        <input v-model="form.moneyfusion_api_key" type="password" placeholder="••••••••" :class="inputCls">
                    </div>
                </div>
            </div>

            <!-- Jeko Africa -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden bg-amber-500">
                            <span class="text-white font-bold text-[11px]">JK</span>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900">Jeko Africa</h3>
                            <p class="text-[11px] text-gray-400">Wave, Orange Money, MTN, Moov, Djamo</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input v-model="form.payment_jeko_enabled" type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div v-show="form.payment_jeko_enabled" class="space-y-4 mt-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">X-API-KEY</label>
                            <input v-model="form.jeko_api_key" type="password" placeholder="••••••••" :class="inputCls">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">X-API-KEY-ID</label>
                            <input v-model="form.jeko_api_key_id" type="text" placeholder="abc123" :class="inputCls">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Store ID</label>
                        <input v-model="form.jeko_store_id" type="text" placeholder="UUID de votre magasin Jeko" :class="inputCls">
                        <p class="text-[11px] text-gray-400 mt-1">Jeko Cockpit → Paramètres → Magasin → identifiant UUID</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Secret Webhook <span class="text-gray-400 font-normal">(optionnel mais recommandé)</span></label>
                        <input v-model="form.jeko_webhook_secret" type="password" placeholder="••••••••" :class="inputCls">
                        <p class="text-[11px] text-gray-400 mt-1">
                            URL webhook à configurer dans Jeko Cockpit :
                            <code class="bg-gray-100 px-1 rounded text-[11px]">{{ location?.origin ?? '' }}/webhook/jeko</code>
                        </p>
                    </div>

                    <!-- Bouton test connexion -->
                    <div class="flex items-center gap-3 pt-1">
                        <button type="button" @click="testJeko"
                            :disabled="jekoTestStatus === 'testing' || !form.jeko_api_key || !form.jeko_api_key_id"
                            class="h-9 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[13px] font-medium rounded-lg transition-colors disabled:opacity-50">
                            {{ jekoTestStatus === 'testing' ? 'Test en cours...' : 'Tester la connexion' }}
                        </button>
                        <span v-if="jekoTestStatus === 'ok'"    class="text-[13px] text-green-600 font-medium">✓ {{ jekoTestMessage }}</span>
                        <span v-if="jekoTestStatus === 'error'" class="text-[13px] text-red-500 font-medium">✗ {{ jekoTestMessage }}</span>
                    </div>
                </div>
            </div>

            <button type="submit"
                :disabled="form.processing"
                class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors disabled:opacity-60">
                <span v-if="form.processing">Enregistrement…</span>
                <span v-else>Enregistrer</span>
            </button>
        </form>

        <!-- Pusher info -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-3">Notifications temps réel (Pusher)</h3>
            <p class="text-[13px] text-gray-600 mb-4">Configurez Pusher pour recevoir des notifications en direct sans recharger le backoffice.</p>
            <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl">
                <p class="text-[13px] text-gray-700 mb-2">Ajoutez dans votre fichier <code class="bg-gray-200 px-1 rounded text-[12px]">.env</code> :</p>
                <pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">PUSHER_APP_ID=votre_app_id
PUSHER_APP_KEY=votre_cle
PUSHER_APP_SECRET=votre_secret
PUSHER_APP_CLUSTER=mt1
BROADCAST_CONNECTION=pusher

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"</pre>
            </div>
        </div>
    </div>
</template>
