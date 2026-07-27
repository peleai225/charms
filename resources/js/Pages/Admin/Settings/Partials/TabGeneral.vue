<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ settings: Object })

const generalForm = useForm({
    site_name:               props.settings.site_name ?? '',
    site_description:        props.settings.site_description ?? '',
    contact_email:           props.settings.contact_email ?? '',
    admin_email:             props.settings.admin_email ?? '',
    contact_phone:           props.settings.contact_phone ?? '',
    contact_address:         props.settings.contact_address ?? '',
    currency:                props.settings.currency ?? 'XOF',
    currency_symbol:         props.settings.currency_symbol ?? 'F CFA',
    tax_rate:                props.settings.tax_rate ?? 0,
    footer_text:             props.settings.footer_text ?? '',
    social_facebook:         props.settings.social_facebook ?? '',
    social_instagram:        props.settings.social_instagram ?? '',
    social_twitter:          props.settings.social_twitter ?? '',
    social_whatsapp:         props.settings.social_whatsapp ?? '',
    social_tiktok:           props.settings.social_tiktok ?? '',
    primary_color:           props.settings.primary_color ?? '#6366f1',
    secondary_color:         props.settings.secondary_color ?? '#8b5cf6',
    accent_color:            props.settings.accent_color ?? '#f59e0b',
    theme_mode:              props.settings.theme_mode ?? 'light',
    pos_receipt_auto_print:  props.settings.pos_receipt_auto_print === '1',
    loyalty_points_per_1000: props.settings.loyalty_points_per_1000 ?? 10,
    loyalty_points_value:    props.settings.loyalty_points_value ?? 500,
    ga4_id:                  props.settings.ga4_id ?? '',
    meta_pixel_id:           props.settings.meta_pixel_id ?? '',
    tiktok_pixel_id:         props.settings.tiktok_pixel_id ?? '',
    logo:    null,
    favicon: null,
})

const logoPreview    = ref(props.settings.logo    ? `/storage/${props.settings.logo}`    : null)
const faviconPreview = ref(props.settings.favicon ? `/storage/${props.settings.favicon}` : null)

function onLogoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    generalForm.logo = file
    logoPreview.value = URL.createObjectURL(file)
}

function onFaviconChange(e) {
    const file = e.target.files[0]
    if (!file) return
    generalForm.favicon = file
    faviconPreview.value = URL.createObjectURL(file)
}

function submit() {
    generalForm.post(route('admin.settings.update'), {
        preserveScroll: true,
        forceFormData: true,
    })
}

const inputCls    = 'w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent'
const textareaCls = 'w-full px-3 py-2 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent resize-none'
</script>

<template>
    <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-5">
        <div class="grid lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations boutique -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Informations de la boutique</h3>
                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom du site *</label>
                                <input v-model="generalForm.site_name" type="text" required :class="inputCls">
                                <p v-if="generalForm.errors.site_name" class="mt-1 text-[12px] text-red-600">{{ generalForm.errors.site_name }}</p>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Email de contact *</label>
                                <input v-model="generalForm.contact_email" type="email" required :class="inputCls">
                                <p v-if="generalForm.errors.contact_email" class="mt-1 text-[12px] text-red-600">{{ generalForm.errors.contact_email }}</p>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Email admin</label>
                                <input v-model="generalForm.admin_email" type="email" placeholder="Par défaut : email de contact" :class="inputCls">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Description du site</label>
                            <textarea v-model="generalForm.site_description" rows="2" :class="textareaCls"></textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Téléphone</label>
                                <input v-model="generalForm.contact_phone" type="text" :class="inputCls">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">WhatsApp</label>
                                <input v-model="generalForm.social_whatsapp" type="text" placeholder="+225 XX XX XX XX XX" :class="inputCls">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Adresse</label>
                            <textarea v-model="generalForm.contact_address" rows="2" :class="textareaCls"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Devise et taxes -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Devise et taxes</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Devise *</label>
                            <select v-model="generalForm.currency" :class="inputCls">
                                <option value="XOF">XOF (Franc CFA)</option>
                                <option value="EUR">EUR (Euro)</option>
                                <option value="USD">USD (Dollar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Symbole *</label>
                            <input v-model="generalForm.currency_symbol" type="text" required :class="inputCls">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Taux de taxe (%)</label>
                            <input v-model="generalForm.tax_rate" type="number" step="0.01" min="0" max="100" :class="inputCls">
                        </div>
                    </div>
                </div>

                <!-- POS -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-3">Caisse POS</h3>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input v-model="generalForm.pos_receipt_auto_print" type="checkbox"
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-[13px] text-gray-700">Ouvrir le reçu et lancer l'impression après validation de vente</span>
                            <p class="text-[12px] text-gray-500 mt-1">Si activé, le reçu s'ouvre dans une nouvelle fenêtre et la boîte de dialogue d'impression se lance.</p>
                        </div>
                    </label>
                </div>

                <!-- Réseaux sociaux -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Réseaux sociaux</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div v-for="social in [
                            { key: 'social_facebook',  label: 'Facebook',    placeholder: 'https://facebook.com/...' },
                            { key: 'social_instagram', label: 'Instagram',   placeholder: 'https://instagram.com/...' },
                            { key: 'social_twitter',   label: 'Twitter / X', placeholder: 'https://x.com/...' },
                            { key: 'social_tiktok',    label: 'TikTok',      placeholder: 'https://tiktok.com/@...' },
                        ]" :key="social.key">
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">{{ social.label }}</label>
                            <input v-model="generalForm[social.key]" type="url" :placeholder="social.placeholder" :class="inputCls">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne sidebar -->
            <div class="space-y-5">

                <!-- Logo & Favicon -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Logo & Favicon</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-2">Logo</label>
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="h-12 mb-2 rounded object-contain">
                            <input @change="onLogoChange" type="file" accept="image/*" class="w-full text-[13px] text-gray-600">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-2">Favicon</label>
                            <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon" class="h-8 mb-2">
                            <input @change="onFaviconChange" type="file" accept="image/*" class="w-full text-[13px] text-gray-600">
                        </div>
                    </div>
                </div>

                <!-- Couleurs du thème -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Couleurs du thème</h3>
                    <div class="space-y-4">
                        <div v-for="color in [
                            { key: 'primary_color',   label: 'Principale',  hint: 'Boutons, liens, éléments principaux' },
                            { key: 'secondary_color', label: 'Secondaire',  hint: '' },
                            { key: 'accent_color',    label: 'Accent',      hint: 'Promotions, alertes, badges' },
                        ]" :key="color.key">
                            <label class="block text-[13px] font-medium text-gray-700 mb-2">{{ color.label }}</label>
                            <div class="flex items-center gap-3">
                                <input v-model="generalForm[color.key]" type="color"
                                    class="w-10 h-9 rounded border border-gray-200 cursor-pointer">
                                <input :value="generalForm[color.key]" type="text" readonly
                                    class="flex-1 h-9 px-3 border border-gray-200 rounded-lg text-[13px] bg-gray-50 font-mono">
                            </div>
                            <p v-if="color.hint" class="text-[11px] text-gray-400 mt-1">{{ color.hint }}</p>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-2">Mode du thème</label>
                            <select v-model="generalForm.theme_mode" :class="inputCls">
                                <option value="light">Clair</option>
                                <option value="dark">Sombre</option>
                                <option value="auto">Auto (préférence système)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Pied de page -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-3">Pied de page</h3>
                    <textarea v-model="generalForm.footer_text" rows="3" placeholder="Texte du pied de page..." :class="textareaCls"></textarea>
                </div>

                <!-- Fidélité -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Programme de fidélité</h3>
                    <p class="text-[12px] text-gray-500 mb-4">Points attribués après chaque paiement confirmé.</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Points par 1 000 F CFA</label>
                            <input v-model="generalForm.loyalty_points_per_1000" type="number" min="0" max="1000" :class="inputCls">
                            <p class="text-[11px] text-gray-400 mt-1">Ex. : 10 pts × 5 000 F = 50 pts</p>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Valeur de 100 points (F CFA)</label>
                            <input v-model="generalForm.loyalty_points_value" type="number" min="0" :class="inputCls">
                            <p class="text-[11px] text-gray-400 mt-1">Ex. : 100 pts = 500 F de réduction</p>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Tracking & Analytics</h3>
                    <p class="text-[12px] text-gray-500 mb-4">Laissez vide pour désactiver un pixel.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Google Analytics 4 — Measurement ID</label>
                            <input v-model="generalForm.ga4_id" type="text" placeholder="G-XXXXXXXXXX"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Meta Pixel ID</label>
                            <input v-model="generalForm.meta_pixel_id" type="text" placeholder="123456789012345"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">TikTok Pixel ID</label>
                            <input v-model="generalForm.tiktok_pixel_id" type="text" placeholder="CXXXXXXXXXXXXXXXXXX"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    :disabled="generalForm.processing"
                    class="w-full h-10 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors disabled:opacity-60">
                    <span v-if="generalForm.processing">Enregistrement…</span>
                    <span v-else>Enregistrer les modifications</span>
                </button>
            </div>
        </div>
    </form>
</template>
