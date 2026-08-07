<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    categories: Array,
    attributes: Array,
})

const form = useForm({
    name: '',
    short_description: '',
    description: '',
    sku: '',
    barcode: '',
    purchase_price: '',
    sale_price: '',
    compare_price: '',
    tax_rate: '20',
    stock_quantity: '0',
    stock_alert_threshold: '5',
    weight: '',
    category_id: '',
    status: 'draft',
    is_featured: false,
    is_new: false,
    has_variants: false,
    track_stock: true,
    images: [],
})

function submit() {
    form.post(route('admin.products.store'), {
        forceFormData: true,
    })
}

const imagePreviews = ref([])

function handleFiles(e) {
    const files = Array.from(e.target.files)
    form.images = files
    imagePreviews.value = files.map(f => URL.createObjectURL(f))
}

function removeFile(i) {
    const files = Array.from(form.images)
    files.splice(i, 1)
    imagePreviews.value.splice(i, 1)
    form.images = files
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Nouveau produit</h1>
                <p class="text-sm text-gray-500 mt-0.5">Créer un nouveau produit dans le catalogue</p>
            </div>
            <a :href="route('admin.products.index')"
                class="h-9 px-4 inline-flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                ← Retour
            </a>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Colonne principale -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Informations générales -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations générales</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nom du produit <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required
                                    class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.name ? 'border-red-300' : 'border-gray-200'">
                                <p v-if="form.errors.name" class="mt-1 text-[11px] text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description courte</label>
                                <textarea v-model="form.short_description" rows="2"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description complète</label>
                                <textarea v-model="form.description" rows="5"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Prix -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Prix</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat HT <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="form.purchase_price" type="number" step="0.01" min="0" required
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix de vente TTC <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="form.sale_price" type="number" step="0.01" min="0" required
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Prix barré</label>
                                <div class="relative">
                                    <input v-model="form.compare_price" type="number" step="0.01" min="0"
                                        class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Taux de TVA</label>
                            <select v-model="form.tax_rate"
                                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="20">20% — Standard</option>
                                <option value="10">10% — Intermédiaire</option>
                                <option value="5.5">5.5% — Réduit</option>
                                <option value="2.1">2.1% — Super réduit</option>
                                <option value="0">0% — Exonéré</option>
                            </select>
                        </div>
                    </div>

                    <!-- Stock & Identifiants -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock & Identifiants</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                                <input v-model="form.sku" type="text" required
                                    class="w-full h-9 px-3 text-[13px] font-mono border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.sku ? 'border-red-300' : 'border-gray-200'">
                                <p v-if="form.errors.sku" class="mt-1 text-[11px] text-red-500">{{ form.errors.sku }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Code-barres</label>
                                <input v-model="form.barcode" type="text"
                                    class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Poids (kg)</label>
                                <input v-model="form.weight" type="number" step="0.001" min="0"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Quantité en stock <span class="text-red-500">*</span></label>
                                <input v-model="form.stock_quantity" type="number" min="0" required
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                                <input v-model="form.stock_alert_threshold" type="number" min="0"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input v-model="form.track_stock" type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[13px] text-gray-700">Suivre le stock de ce produit</span>
                            </label>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Images du produit</h2>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition-colors"
                             @click="$refs.fileInput.click()">
                            <svg class="w-7 h-7 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-[13px] font-medium text-gray-600">Cliquez pour ajouter des images</p>
                            <p class="text-[11px] text-gray-400 mt-1">JPEG, PNG, WEBP — max 5 Mo par image</p>
                            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="handleFiles">
                        </div>
                        <div v-if="imagePreviews.length" class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-3">
                            <div v-for="(src, i) in imagePreviews" :key="i" class="relative group aspect-square">
                                <img :src="src" class="w-full h-full object-cover rounded-lg border border-gray-200">
                                <button type="button" @click="removeFile(i)"
                                    class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="space-y-5">

                    <!-- Publication -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Publication</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                                <select v-model="form.status"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="draft">Brouillon</option>
                                    <option value="active">Actif</option>
                                    <option value="archived">Archivé</option>
                                </select>
                            </div>
                            <div class="space-y-2 pt-1">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.is_featured" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Mis en avant</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.is_new" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Nouveauté</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input v-model="form.has_variants" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-[13px] text-gray-700">Produit avec variantes</span>
                                </label>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex gap-2">
                                <button type="submit" :disabled="form.processing"
                                    class="flex-1 h-9 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                                    {{ form.processing ? 'Création...' : 'Créer le produit' }}
                                </button>
                                <a :href="route('admin.products.index')"
                                    class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Catégorie -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Catégorie</h2>
                        <select v-model="form.category_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sans catégorie</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.full_path }}
                            </option>
                        </select>
                    </div>

                </div>
            </div>
        </form>
    </div>
</template>
