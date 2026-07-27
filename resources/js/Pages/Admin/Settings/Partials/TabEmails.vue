<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const form = useForm({
    mail_from_name:    props.settings.mail_from_name ?? '',
    mail_from_address: props.settings.mail_from_address ?? '',
    mail_driver:       props.settings.mail_driver ?? 'smtp',
    mail_host:         props.settings.mail_host ?? '',
    mail_port:         props.settings.mail_port ?? 587,
    mail_username:     props.settings.mail_username ?? '',
    mail_password:     props.settings.mail_password ?? '',
    mail_encryption:   props.settings.mail_encryption ?? 'tls',
})

function submit() {
    form.post(route('admin.settings.emails.update'), { preserveScroll: true })
}

// Test email
const testEmail = ref(props.settings.mail_from_address ?? '')
const testForm  = useForm({ test_email: '' })

function submitTest() {
    testForm.test_email = testEmail.value
    testForm.post(route('admin.settings.emails.test'), { preserveScroll: true })
}

const inputCls = 'w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent'
</script>

<template>
    <div class="space-y-5">
        <form @submit.prevent="submit" class="space-y-5">

            <!-- Expéditeur -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Expéditeur</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom d'expéditeur *</label>
                        <input v-model="form.mail_from_name" type="text" required :class="inputCls">
                        <p v-if="form.errors.mail_from_name" class="mt-1 text-[12px] text-red-600">{{ form.errors.mail_from_name }}</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Email d'expéditeur *</label>
                        <input v-model="form.mail_from_address" type="email" required :class="inputCls">
                        <p v-if="form.errors.mail_from_address" class="mt-1 text-[12px] text-red-600">{{ form.errors.mail_from_address }}</p>
                    </div>
                </div>
            </div>

            <!-- SMTP -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Configuration SMTP</h3>
                <div class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-xl text-[13px] text-blue-800">
                    <p class="font-medium mb-1">Configuration Gmail</p>
                    <p class="text-[12px]">Serveur : <code class="bg-blue-100 px-1 rounded">smtp.gmail.com</code> · Port <code class="bg-blue-100 px-1 rounded">587</code> (TLS) ou <code class="bg-blue-100 px-1 rounded">465</code> (SSL)<br>
                    <strong>Important :</strong> Utilisez un <strong>mot de passe d'application</strong> Gmail.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Driver</label>
                        <select v-model="form.mail_driver" :class="inputCls">
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="mailgun">Mailgun</option>
                        </select>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Serveur SMTP</label>
                            <input v-model="form.mail_host" type="text" placeholder="smtp.gmail.com" :class="inputCls">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Port</label>
                            <input v-model="form.mail_port" type="number" :class="inputCls">
                            <p class="text-[11px] text-gray-400 mt-1">587 (TLS) ou 465 (SSL)</p>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                            <input v-model="form.mail_username" type="text" :class="inputCls">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Mot de passe</label>
                            <input v-model="form.mail_password" type="password" placeholder="••••••••" :class="inputCls">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1">Chiffrement</label>
                        <select v-model="form.mail_encryption" :class="inputCls">
                            <option value="tls">TLS (Recommandé pour Gmail)</option>
                            <option value="ssl">SSL</option>
                            <option value="null">Aucun</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">TLS pour port 587, SSL pour port 465</p>
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

        <!-- Test email -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Tester la configuration</h3>
            <div class="flex gap-3">
                <input v-model="testEmail" type="email" placeholder="Email de test" required
                    class="flex-1 h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                <button @click="submitTest"
                    :disabled="testForm.processing"
                    class="h-9 px-4 bg-green-600 text-white font-medium text-[13px] rounded-lg hover:bg-green-700 transition-colors disabled:opacity-60">
                    Envoyer un test
                </button>
            </div>
            <p class="text-[12px] text-gray-400 mt-2">Un email de test sera envoyé pour vérifier la configuration.</p>
        </div>
    </div>
</template>
