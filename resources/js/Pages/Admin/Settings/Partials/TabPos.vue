<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const form = useForm({
    pos_printer_enabled:   props.settings.pos_printer_enabled   === '1',
    pos_printer_type:      props.settings.pos_printer_type      ?? 'network',
    pos_printer_ip:        props.settings.pos_printer_ip        ?? '',
    pos_printer_port:      props.settings.pos_printer_port      ?? '9100',
    pos_printer_width:     props.settings.pos_printer_width     ?? '48',
    pos_receipt_auto_print:props.settings.pos_receipt_auto_print === '1',
    pos_auto_cut:          props.settings.pos_auto_cut          === '1',
    pos_cash_drawer:       props.settings.pos_cash_drawer       === '1',
})

function save() {
    form.post('/admin/settings/pos', {
        onSuccess: () => {},
    })
}

// Test de connexion imprimante
const testStatus  = ref(null) // null | 'testing' | 'ok' | 'error'
const testMessage = ref('')

async function testPrinter() {
    testStatus.value  = 'testing'
    testMessage.value = ''
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        const res  = await fetch('/admin/settings/pos/test-printer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({
                type: form.pos_printer_type,
                ip:   form.pos_printer_ip,
                port: form.pos_printer_port,
            }),
        })
        const data = await res.json()
        testStatus.value  = data.success ? 'ok' : 'error'
        testMessage.value = data.message
    } catch {
        testStatus.value  = 'error'
        testMessage.value = 'Impossible de joindre le serveur.'
    }
}
</script>

<template>
    <form @submit.prevent="save" class="space-y-6">

        <!-- Activation -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Imprimante thermique</h2>

            <label class="flex items-center justify-between py-3 border-b border-gray-100 cursor-pointer">
                <div>
                    <p class="text-[13px] font-medium text-gray-800">Activer l'impression directe</p>
                    <p class="text-[12px] text-gray-400 mt-0.5">Imprime sans boîte de dialogue navigateur</p>
                </div>
                <div class="relative">
                    <input v-model="form.pos_printer_enabled" type="checkbox" class="sr-only peer">
                    <div class="w-10 h-5 bg-gray-200 peer-checked:bg-blue-600 rounded-full transition-colors"></div>
                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                </div>
            </label>

            <!-- Config réseau (affiché si activé) -->
            <div v-if="form.pos_printer_enabled" class="mt-4 space-y-4">

                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Type de connexion</label>
                    <div class="flex gap-3">
                        <label v-for="opt in [
                            { value: 'network', label: 'Réseau (Wi-Fi / Ethernet)', desc: 'Imprimante avec IP fixe' },
                            { value: 'usb',     label: 'USB / Windows',             desc: 'Imprimante locale partagée' },
                        ]" :key="opt.value"
                            class="flex-1 flex items-start gap-2.5 p-3 border rounded-lg cursor-pointer transition-colors"
                            :class="form.pos_printer_type === opt.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input v-model="form.pos_printer_type" :value="opt.value" type="radio" class="mt-0.5 accent-blue-600">
                            <div>
                                <p class="text-[13px] font-medium text-gray-800">{{ opt.label }}</p>
                                <p class="text-[11px] text-gray-400">{{ opt.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Réseau -->
                <div v-if="form.pos_printer_type === 'network'" class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Adresse IP de l'imprimante
                        </label>
                        <input v-model="form.pos_printer_ip" type="text" placeholder="192.168.1.100"
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-400 mt-1">Trouvez l'IP dans le menu de l'imprimante ou votre routeur</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Port</label>
                        <input v-model="form.pos_printer_port" type="number" placeholder="9100"
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- USB (nom partage Windows) -->
                <div v-if="form.pos_printer_type === 'usb'">
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Nom de l'imprimante Windows
                    </label>
                    <input v-model="form.pos_printer_ip" type="text" placeholder="EPSON_TM_T20"
                        class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-[11px] text-gray-400 mt-1">Panneau de config → Périphériques → nom exact de l'imprimante</p>
                </div>

                <!-- Format papier -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Format papier</label>
                    <div class="flex gap-3">
                        <label v-for="opt in [{ value: '48', label: '80 mm', desc: '48 caractères' }, { value: '32', label: '58 mm', desc: '32 caractères' }]"
                            :key="opt.value"
                            class="flex items-center gap-2 px-4 py-2.5 border rounded-lg cursor-pointer transition-colors"
                            :class="form.pos_printer_width === opt.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input v-model="form.pos_printer_width" :value="opt.value" type="radio" class="accent-blue-600">
                            <div>
                                <p class="text-[13px] font-medium text-gray-800">{{ opt.label }}</p>
                                <p class="text-[11px] text-gray-400">{{ opt.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Bouton test -->
                <div class="flex items-center gap-3 pt-1">
                    <button type="button" @click="testPrinter"
                        :disabled="testStatus === 'testing' || !form.pos_printer_ip"
                        class="h-9 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[13px] font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg v-if="testStatus === 'testing'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17H17.01M5 17H5.01M7 21H17a2 2 0 002-2v-4a2 2 0 00-2-2H7a2 2 0 00-2 2v4a2 2 0 002 2zM7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        {{ testStatus === 'testing' ? 'Test en cours...' : 'Tester la connexion' }}
                    </button>
                    <span v-if="testStatus === 'ok'"    class="text-[13px] text-green-600 font-medium">✓ {{ testMessage }}</span>
                    <span v-if="testStatus === 'error'" class="text-[13px] text-red-500 font-medium">✗ {{ testMessage }}</span>
                </div>
            </div>
        </div>

        <!-- Options POS -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Options caisse</h2>
            <div class="space-y-0 divide-y divide-gray-100">
                <label v-for="opt in [
                    { key: 'pos_receipt_auto_print', label: 'Impression automatique après chaque vente', desc: 'Imprime le reçu dès la validation' },
                    { key: 'pos_auto_cut',           label: 'Coupe automatique du papier',              desc: 'Si votre imprimante supporte la coupe' },
                    { key: 'pos_cash_drawer',        label: 'Ouvrir le tiroir-caisse',                  desc: 'Envoie le signal d\'ouverture après impression' },
                ]" :key="opt.key"
                    class="flex items-center justify-between py-3 cursor-pointer">
                    <div>
                        <p class="text-[13px] font-medium text-gray-800">{{ opt.label }}</p>
                        <p class="text-[12px] text-gray-400">{{ opt.desc }}</p>
                    </div>
                    <div class="relative flex-shrink-0 ml-4">
                        <input v-model="form[opt.key]" type="checkbox" class="sr-only peer">
                        <div class="w-10 h-5 bg-gray-200 peer-checked:bg-blue-600 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Bouton sauvegarder -->
        <div class="flex justify-end">
            <button type="submit" :disabled="form.processing"
                class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
        </div>

    </form>
</template>
