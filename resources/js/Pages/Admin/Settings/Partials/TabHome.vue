<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
    Truck, ShieldCheck, RotateCcw, Headset, Banknote, Gift, MessageCircle, Package, Clock, MapPin,
    ArrowUp, ArrowDown, Trash2, Plus,
} from 'lucide-vue-next'
import { useConfirm } from '@/Composables/useConfirm'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
import ShopFacts from '@/Components/Home/ShopFacts.vue'

// Paramètres → Accueil : engagements affichés sous la grande bannière (réglage « home_engagements »).
const props = defineProps({
    settings: Object,
    icons: { type: Array, default: () => [] }, // liste autorisée par le serveur (App\Support\HomeEngagements::ICONS)
})

const MAX = 6
const TITLE_MAX = 60
const TEXT_MAX = 120

const ICON_OPTIONS = [
    { key: 'truck', label: 'Livraison', component: Truck },
    { key: 'shield-check', label: 'Garantie, sécurité', component: ShieldCheck },
    { key: 'rotate-ccw', label: 'Retours, échanges', component: RotateCcw },
    { key: 'headset', label: 'Service client', component: Headset },
    { key: 'banknote', label: 'Paiement', component: Banknote },
    { key: 'gift', label: 'Cadeau', component: Gift },
    { key: 'message-circle', label: 'Conseil, message', component: MessageCircle },
    { key: 'package', label: 'Colis, emballage', component: Package },
    { key: 'clock', label: 'Délais, horaires', component: Clock },
    { key: 'map-pin', label: 'Adresse, zone', component: MapPin },
]
const iconOptions = computed(() => (props.icons.length ? ICON_OPTIONS.filter((o) => props.icons.includes(o.key)) : ICON_OPTIONS))

function saved() {
    try {
        const list = JSON.parse(props.settings?.home_engagements || '[]')
        return Array.isArray(list) ? list : []
    } catch {
        return []
    }
}

let uid = 0
const blank = () => ({ uid: ++uid, icon: 'truck', title: '', text: '', link: '' })
const items = ref(saved().map((e) => ({ ...blank(), icon: e.icon || 'truck', title: e.title || '', text: e.text || '', link: e.link || '' })))

const confirm = useConfirm()
const form = useForm({ engagements: [] })
// Les erreurs partagées par HandleInertiaRequests sont des listes de messages : on affiche le premier
const firstError = (key) => {
    const e = form.errors[key]
    return Array.isArray(e) ? e[0] : e
}
const err = (i, field) => firstError(`engagements.${i}.${field}`)

function add() {
    if (items.value.length < MAX) items.value.push(blank())
}

function move(i, step) {
    const j = i + step
    if (j < 0 || j >= items.value.length) return
    items.value.splice(j, 0, items.value.splice(i, 1)[0])
}

async function remove(i) {
    const item = items.value[i]
    if (item.title.trim() || item.text.trim()) {
        const ok = await confirm({
            title: `Retirer « ${item.title.trim() || 'cet engagement'} » ?`,
            message: 'Il disparaîtra de l\'accueil après l\'enregistrement.',
            confirmLabel: 'Retirer',
            variant: 'danger',
        })
        if (!ok) return
    }
    items.value.splice(i, 1)
}

function submit() {
    form.transform(() => ({
        engagements: items.value.map(({ icon, title, text, link }) => ({ icon, title, text, link })),
    })).post(route('admin.settings.home.update'), { preserveScroll: true })
}

// Aperçu du bandeau tel qu'il apparaîtra sur l'accueil
const preview = computed(() => items.value
    .filter((e) => e.title.trim())
    .map((e) => ({ key: e.icon, title: e.title.trim(), desc: e.text.trim(), href: null })))

const input = 'w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent'
const label = 'block text-[12px] font-medium text-gray-700 mb-1'
const iconBtn = 'w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 disabled:opacity-30 disabled:hover:bg-transparent transition-colors'
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Engagements de la boutique</h3>
                    <p class="text-[12px] text-gray-500 mt-0.5 max-w-xl">
                        Bandeau sous la grande bannière de l'accueil ({{ MAX }} au maximum). N'indiquez que ce que la boutique tient vraiment.
                    </p>
                </div>
                <button v-if="items.length" type="button" :disabled="items.length >= MAX" @click="add"
                    class="h-9 px-3 inline-flex items-center gap-1.5 shrink-0 rounded-lg bg-primary-600 text-primary-contrast text-[12px] font-medium hover:bg-primary-700 disabled:opacity-50 transition-colors">
                    <Plus class="w-4 h-4" aria-hidden="true" />
                    Ajouter
                </button>
            </div>

            <!-- Aucun engagement : infos automatiques -->
            <div v-if="!items.length" class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center">
                <p class="text-[13px] font-medium text-gray-900">Aucun engagement personnalisé</p>
                <p class="text-[12px] text-gray-500 mt-1 max-w-md mx-auto">
                    L'accueil affiche automatiquement vos vraies informations : zones de livraison, paiement à la livraison
                    et WhatsApp, selon vos réglages.
                </p>
                <button type="button" @click="add"
                    class="mt-4 h-9 px-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-600 text-primary-contrast text-[13px] font-medium hover:bg-primary-700 transition-colors">
                    <Plus class="w-4 h-4" aria-hidden="true" />
                    Ajouter un engagement
                </button>
            </div>

            <ol v-else class="space-y-3">
                <li v-for="(item, i) in items" :key="item.uid" class="p-4 border border-gray-200 rounded-xl">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-[12px] font-semibold text-gray-500">Engagement {{ i + 1 }}</span>
                        <div class="flex items-center">
                            <button type="button" :class="iconBtn" :disabled="i === 0" :aria-label="`Monter l'engagement ${i + 1}`" @click="move(i, -1)">
                                <ArrowUp class="w-4 h-4" aria-hidden="true" />
                            </button>
                            <button type="button" :class="iconBtn" :disabled="i === items.length - 1" :aria-label="`Descendre l'engagement ${i + 1}`" @click="move(i, 1)">
                                <ArrowDown class="w-4 h-4" aria-hidden="true" />
                            </button>
                            <button type="button" :class="iconBtn" :aria-label="`Retirer l'engagement ${i + 1}`" @click="remove(i)">
                                <Trash2 class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <fieldset class="mb-3">
                        <legend :class="label">Icône</legend>
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="o in iconOptions" :key="o.key" type="button" :title="o.label" :aria-label="o.label"
                                :aria-pressed="String(item.icon === o.key)" @click="item.icon = o.key"
                                class="w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg border transition-colors"
                                :class="item.icon === o.key ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
                                <component :is="o.component" class="w-[18px] h-[18px]" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="err(i, 'icon')" class="mt-1 text-[12px] text-danger-600">{{ err(i, 'icon') }}</p>
                    </fieldset>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label :for="`eng-title-${item.uid}`" :class="label">Titre *</label>
                            <input :id="`eng-title-${item.uid}`" v-model="item.title" type="text" required :maxlength="TITLE_MAX"
                                placeholder="Ex : Paiement à la livraison" :class="input" :aria-invalid="!!err(i, 'title')">
                            <p v-if="err(i, 'title')" class="mt-1 text-[12px] text-danger-600">{{ err(i, 'title') }}</p>
                            <p v-else class="mt-1 text-[11px] text-gray-400">{{ item.title.length }} / {{ TITLE_MAX }}</p>
                        </div>
                        <div>
                            <label :for="`eng-link-${item.uid}`" :class="label">Lien (facultatif)</label>
                            <input :id="`eng-link-${item.uid}`" v-model="item.link" type="text" maxlength="255"
                                placeholder="/legal/livraison ou https://…" :class="input" :aria-invalid="!!err(i, 'link')">
                            <p v-if="err(i, 'link')" class="mt-1 text-[12px] text-danger-600">{{ err(i, 'link') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label :for="`eng-text-${item.uid}`" :class="label">Texte (facultatif)</label>
                            <input :id="`eng-text-${item.uid}`" v-model="item.text" type="text" :maxlength="TEXT_MAX"
                                placeholder="Ex : Vous payez en recevant votre colis" :class="input" :aria-invalid="!!err(i, 'text')">
                            <p v-if="err(i, 'text')" class="mt-1 text-[12px] text-danger-600">{{ err(i, 'text') }}</p>
                            <p v-else class="mt-1 text-[11px] text-gray-400">{{ item.text.length }} / {{ TEXT_MAX }}</p>
                        </div>
                    </div>
                </li>
            </ol>
            <p v-if="firstError('engagements')" class="mt-3 text-[12px] text-danger-600">{{ firstError('engagements') }}</p>
        </div>

        <!-- Aperçu -->
        <div v-if="preview.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900">Aperçu</h3>
            <p class="text-[12px] text-gray-500 mt-0.5 mb-3">Sur le site, les couleurs suivent le thème de la boutique.</p>
            <div class="-mx-4 pointer-events-none">
                <ShopFacts :facts="preview" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" :disabled="form.processing"
                class="h-10 px-6 bg-primary-600 hover:bg-primary-700 text-primary-contrast font-semibold text-[13px] rounded-lg transition-colors disabled:opacity-60">
                <span v-if="form.processing">Enregistrement…</span>
                <span v-else>Enregistrer</span>
            </button>
            <p v-if="form.recentlySuccessful" class="text-[13px] text-success-700" role="status">Modifications enregistrées.</p>
            <p v-else-if="form.hasErrors" class="text-[13px] text-danger-600" role="alert">Corrigez les champs signalés puis enregistrez.</p>
        </div>

        <ConfirmModal />
    </form>
</template>
