<script setup>
import { ref, computed } from 'vue'
import BannerImagePicker from './BannerImagePicker.vue'

// Champs communs à la création et à la modification d'une bannière.
const props = defineProps({
    form: { type: Object, required: true },
    positions: { type: Object, required: true },
    types: { type: Object, required: true },
    imageUrl: { type: String, default: null },
    imageMobileUrl: { type: String, default: null },
})

const form = props.form
const preview = ref(props.imageUrl)
const previewMobile = ref(props.imageMobileUrl)

const isAnnouncementBar = computed(() => form.position === 'announcement_bar')
const isPopup = computed(() => form.position === 'popup_center')
const isHero = computed(() => form.position === 'home_hero')
const isHomePromo = computed(() => ['home_middle', 'home_bottom'].includes(form.position))
const isSide = computed(() => form.position === 'home_side')
const usesColors = computed(() => isAnnouncementBar.value || isPopup.value || isHomePromo.value || isSide.value)

function selectImage(file) {
    form.image = file
    preview.value = URL.createObjectURL(file)
}
function removeImage() {
    form.image = null
    preview.value = null
}
function selectMobile(file) {
    form.image_mobile = file
    form.remove_image_mobile = false
    previewMobile.value = URL.createObjectURL(file)
}
function removeMobile() {
    form.image_mobile = null
    form.remove_image_mobile = true
    previewMobile.value = null
}

const input = 'w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
const label = 'block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1'
</script>

<template>
    <div class="space-y-5">
        <!-- Emplacement -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Emplacement</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label :class="label">Position *</label>
                    <select v-model="form.position" required :class="input">
                        <option v-for="(text, key) in positions" :key="key" :value="key">{{ text }}</option>
                    </select>
                    <p v-if="form.errors.position" class="mt-1 text-[12px] text-red-500">{{ form.errors.position }}</p>
                </div>
                <div v-if="!isAnnouncementBar && !isPopup">
                    <label :class="label">Type *</label>
                    <select v-model="form.type" required :class="input">
                        <option v-for="(text, key) in types" :key="key" :value="key">{{ text }}</option>
                    </select>
                </div>
            </div>
            <p v-if="isAnnouncementBar" class="mt-3 text-[12px] text-amber-700">Barre tout en haut du site : seul le titre est obligatoire.</p>
            <p v-if="isPopup" class="mt-3 text-[12px] text-indigo-700">Popup au centre de l'écran, affiché une fois par visiteur. L'image est facultative.</p>
            <p v-if="isSide" class="mt-3 text-[12px] text-gray-600">Petite carte à droite de la grande bannière sur ordinateur, sous la bannière sur téléphone. Deux cartes au maximum sont affichées (les premières dans l'ordre d'affichage).</p>
        </div>

        <!-- Images -->
        <div v-if="!isAnnouncementBar" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Images</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <BannerImagePicker label="Image ordinateur" :preview="preview" :error="form.errors.image"
                    :hint="isHero ? 'Recommandé : 1920 × 600 px, 5 Mo max' : isSide ? 'Recommandé : 600 × 400 px, 5 Mo max' : 'PNG, JPG, WEBP — 5 Mo max'"
                    @select="selectImage" @remove="removeImage" />
                <BannerImagePicker v-if="isHero || isHomePromo" label="Image téléphone (facultative)" :preview="previewMobile"
                    :error="form.errors.image_mobile" hint="Format vertical ou carré, ex. 1080 × 1080 px. Sinon l'image ordinateur est utilisée."
                    @select="selectMobile" @remove="removeMobile" />
            </div>
            <p v-if="isHomePromo" class="mt-3 text-[12px] text-gray-500">
                Votre visuel s'affiche en entier. Si l'image contient déjà le texte de la promo, laissez le titre vide.
            </p>
            <p v-if="isSide" class="mt-3 text-[12px] text-gray-500">
                Sans titre, l'image s'affiche seule et en entier. Avec un titre, le sous-titre, le titre et le bouton s'affichent sur la couleur de fond, l'image en illustration à droite.
            </p>
        </div>

        <!-- Contenu -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Texte</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label :class="label">Titre <span v-if="isAnnouncementBar" class="text-red-500">*</span></label>
                    <input v-model="form.title" type="text" :required="isAnnouncementBar" placeholder="Ex : Soldes de fin d'année" :class="input">
                    <p v-if="form.errors.title" class="mt-1 text-[12px] text-red-500">{{ form.errors.title }}</p>
                </div>
                <div>
                    <label :class="label">Sous-titre</label>
                    <input v-model="form.subtitle" type="text" placeholder="Ex : Jusqu'à -50 % sur la mode" :class="input">
                </div>
                <div v-if="isHero || isPopup" class="md:col-span-2">
                    <label :class="label">Description</label>
                    <textarea v-model="form.description" rows="2" maxlength="500"
                        class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label :class="label">Lien</label>
                    <input v-model="form.link" type="text" placeholder="/categorie/chaussures ou https://…" :class="input">
                    <p v-if="form.errors.link" class="mt-1 text-[12px] text-red-500">{{ form.errors.link }}</p>
                </div>
                <div>
                    <label :class="label">Texte du bouton</label>
                    <input v-model="form.button_text" type="text" placeholder="Découvrir" :class="input">
                </div>
            </div>
        </div>

        <!-- Couleurs -->
        <div v-if="usesColors" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-1">Couleurs</h2>
            <p class="text-[12px] text-gray-500 mb-4">
                {{ isHomePromo ? 'Utilisées quand la bannière n\'a pas d\'image.' : isSide ? 'Fond et texte de la carte quand elle a un titre. Vides = couleurs du thème.' : 'Fond et texte de la barre ou du popup.' }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
                <div v-for="field in [{ key: 'background_color', text: 'Fond', fallback: '#2563EB' }, { key: 'text_color', text: 'Texte', fallback: '#FFFFFF' }]" :key="field.key">
                    <label :class="label">{{ field.text }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" :value="form[field.key] || field.fallback" :aria-label="`Couleur ${field.text}`"
                            class="w-9 h-9 rounded-lg border border-gray-200" @input="form[field.key] = $event.target.value.toUpperCase()">
                        <input v-model="form[field.key]" type="text" maxlength="7" :placeholder="field.fallback" :class="input">
                    </div>
                    <p v-if="form.errors[field.key]" class="mt-1 text-[12px] text-red-500">Format attendu : #RRGGBB</p>
                </div>
            </div>
        </div>

        <!-- Période et compte à rebours -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Période d'affichage</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label :class="label">Début</label>
                    <input v-model="form.starts_at" type="datetime-local" :class="input">
                    <p class="mt-1 text-[11px] text-gray-400">Vide = tout de suite</p>
                </div>
                <div>
                    <label :class="label">Fin</label>
                    <input v-model="form.ends_at" type="datetime-local" :class="input">
                    <p v-if="form.errors.ends_at" class="mt-1 text-[12px] text-red-500">{{ form.errors.ends_at }}</p>
                    <p v-else class="mt-1 text-[11px] text-gray-400">Vide = sans fin. La bannière disparaît toute seule à cette heure.</p>
                </div>
                <label class="md:col-span-2 flex items-start gap-3 cursor-pointer">
                    <input v-model="form.show_countdown" type="checkbox" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block text-[13px] font-medium text-gray-800">Afficher un compte à rebours jusqu'à la fin</span>
                        <span class="block text-[12px] text-gray-500">Ex : « Fin dans 2 j 05 h 12 min ». Il faut une date de fin.</span>
                    </span>
                </label>
                <div>
                    <label :class="label">Ordre d'affichage</label>
                    <input v-model.number="form.order" type="number" min="0" :class="input">
                </div>
                <label class="flex items-center gap-2.5 pt-5 cursor-pointer">
                    <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">Bannière active</span>
                </label>
            </div>
        </div>
    </div>
</template>
