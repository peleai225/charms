<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'
import { useConfirm } from '@/Composables/useConfirm'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const props = defineProps({
    categories: Array,
    tree:       Array,
})

const toast   = useToast()
const confirm = useConfirm()

// ── Formulaire création ───────────────────────────────────────────────────────
const showCreate      = ref(false)
const createImagePrev = ref(null)
const createForm = useForm({
    name:             '',
    description:      '',
    parent_id:        '',
    is_active:        true,
    is_featured:      false,
    order:            0,
    image:            null,
    meta_title:       '',
    meta_description: '',
})

function onCreateImage(e) {
    const file = e.target.files[0]
    if (!file) return
    createForm.image  = file
    createImagePrev.value = URL.createObjectURL(file)
}

function submitCreate() {
    createForm.post(route('admin.categories.store'), {
        forceFormData: true,
        onSuccess: () => {
            showCreate.value    = false
            createImagePrev.value = null
            createForm.reset()
            toast.success('Catégorie créée.')
        },
    })
}

// ── Formulaire édition ────────────────────────────────────────────────────────
const editingId       = ref(null)
const editingCat      = ref(null)
const editImagePrev   = ref(null)
const showSeoCreate   = ref(false)
const showSeoEdit     = ref(false)

const editForm = useForm({
    name:             '',
    description:      '',
    parent_id:        '',
    is_active:        true,
    is_featured:      false,
    order:            0,
    image:            null,
    meta_title:       '',
    meta_description: '',
})

function openEdit(cat) {
    editingId.value           = cat.id
    editingCat.value          = cat
    editImagePrev.value       = null
    editForm.name             = cat.name
    editForm.description      = cat.description ?? ''
    editForm.parent_id        = cat.parent_id ?? ''
    editForm.is_active        = !!cat.is_active
    editForm.is_featured      = !!cat.is_featured
    editForm.order            = cat.order ?? 0
    editForm.image            = null
    editForm.meta_title       = cat.meta_title ?? ''
    editForm.meta_description = cat.meta_description ?? ''
    showSeoEdit.value = !!(cat.meta_title || cat.meta_description)
}

function onEditImage(e) {
    const file = e.target.files[0]
    if (!file) return
    editForm.image      = file
    editImagePrev.value = URL.createObjectURL(file)
}

function submitEdit(catId) {
    editForm.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('admin.categories.update', catId), {
            forceFormData: true,
            onSuccess: () => {
                editingId.value   = null
                editingCat.value  = null
                editImagePrev.value = null
                toast.success('Catégorie mise à jour.')
            },
        })
}

async function deleteCategory(cat) {
    const ok = await confirm({
        title:        `Supprimer « ${cat.name} » ?`,
        message:      'Cette catégorie sera supprimée définitivement. Impossible si elle contient des produits ou sous-catégories.',
        confirmLabel: 'Supprimer',
        variant:      'danger',
    })
    if (!ok) return
    router.delete(route('admin.categories.destroy', cat.id), {
        onSuccess: () => toast.success('Catégorie supprimée.'),
        onError:   () => toast.error('Impossible de supprimer cette catégorie.'),
    })
}

// Chemin breadcrumb calculé côté Vue (évite les requêtes SQL N+1 côté PHP)
const categoryPath = computed(() => {
    if (!editingCat.value) return ''
    const parts = [editingCat.value.name]
    let parentId = editingCat.value.parent_id
    let depth = 0
    while (parentId && depth < 5) {
        const parent = (props.categories ?? []).find(c => c.id === parentId)
        if (!parent) break
        parts.unshift(parent.name)
        parentId = parent.parent_id
        depth++
    }
    return parts.join(' › ')
})

// Catégories parentes valides (exclut la catégorie en cours d'édition et ses descendants)
const editableParents = computed(() => {
    if (!editingId.value) return props.tree ?? []
    const excludeIds = getDescendantIds(editingId.value)
    excludeIds.add(editingId.value)
    return (props.tree ?? []).filter(c => !excludeIds.has(c.id))
})

function getDescendantIds(id, set = new Set()) {
    const cat = (props.categories ?? []).find(c => c.id === id)
    for (const child of cat?.children ?? []) {
        set.add(child.id)
        getDescendantIds(child.id, set)
    }
    return set
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Catégories</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ categories.length }} catégorie(s)</p>
            </div>
            <button type="button" @click="showCreate = true"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle catégorie
            </button>
        </div>

        <!-- Tableau arborescent -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-px">#</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Catégorie</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Slug</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produits</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>

                    <tbody v-if="tree.length" class="divide-y divide-gray-50">
                        <template v-for="(cat, i) in tree" :key="cat.id">

                            <!-- Niveau 0 — racine -->
                            <tr class="hover:bg-gray-50/60 group">
                                <td class="px-5 py-3 text-[12px] text-gray-400 font-mono">{{ i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <img v-if="cat.image" :src="'/storage/' + cat.image"
                                            class="w-8 h-8 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                        <span v-else class="w-8 h-8 rounded-lg bg-gray-100 border border-dashed border-gray-300 flex-shrink-0 flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </span>
                                        <div>
                                            <span class="text-[13px] font-semibold text-gray-900">{{ cat.name }}</span>
                                            <span v-if="cat.is_featured" class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">En vedette</span>
                                            <p v-if="cat.children?.length" class="text-[11px] text-gray-400 mt-0.5">{{ cat.children.length }} sous-catégorie(s)</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-mono text-[12px] text-gray-400 hidden md:table-cell">{{ cat.slug }}</td>
                                <td class="px-5 py-3 text-center text-[13px] text-gray-600 font-medium">{{ cat.products_count ?? 0 }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                        :class="cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                        {{ cat.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openEdit(cat)" title="Modifier"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteCategory(cat)" title="Supprimer"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Niveau 1 -->
                            <template v-for="child in (cat.children ?? [])" :key="child.id">
                                <tr class="hover:bg-blue-50/30 group bg-gray-50/30">
                                    <td class="px-5 py-2.5"></td>
                                    <td class="px-5 py-2.5">
                                        <div class="flex items-center gap-2 pl-5">
                                            <span class="text-gray-300 text-[11px]">└</span>
                                            <img v-if="child.image" :src="'/storage/' + child.image"
                                                class="w-6 h-6 rounded object-cover border border-gray-200 flex-shrink-0">
                                            <span v-else class="w-6 h-6 rounded bg-gray-100 border border-dashed border-gray-200 flex-shrink-0"></span>
                                            <span class="text-[13px] text-gray-800">{{ child.name }}</span>
                                            <span v-if="child.is_featured" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">En vedette</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-2.5 font-mono text-[12px] text-gray-400 hidden md:table-cell">{{ child.slug }}</td>
                                    <td class="px-5 py-2.5 text-center text-[13px] text-gray-600">{{ child.products_count ?? 0 }}</td>
                                    <td class="px-5 py-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                            :class="child.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ child.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="openEdit(child)" title="Modifier"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click="deleteCategory(child)" title="Supprimer"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Niveau 2 -->
                                <tr v-for="grand in (child.children ?? [])" :key="grand.id"
                                    class="hover:bg-blue-50/20 group bg-gray-50/50">
                                    <td class="px-5 py-2"></td>
                                    <td class="px-5 py-2">
                                        <div class="flex items-center gap-2 pl-10">
                                            <span class="text-gray-200 text-[11px]">└</span>
                                            <img v-if="grand.image" :src="'/storage/' + grand.image"
                                                class="w-5 h-5 rounded object-cover border border-gray-200 flex-shrink-0">
                                            <span v-else class="w-5 h-5 rounded bg-gray-100 border border-dashed border-gray-200 flex-shrink-0"></span>
                                            <span class="text-[12px] text-gray-700">{{ grand.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-2 font-mono text-[12px] text-gray-400 hidden md:table-cell">{{ grand.slug }}</td>
                                    <td class="px-5 py-2 text-center text-[12px] text-gray-600">{{ grand.products_count ?? 0 }}</td>
                                    <td class="px-5 py-2 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                            :class="grand.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ grand.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="openEdit(grand)" title="Modifier"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click="deleteCategory(grand)" title="Supprimer"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                        </template>
                    </tbody>

                    <tbody v-else>
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-[13px] text-gray-400 mb-1">Aucune catégorie</p>
                                <p class="text-[12px] text-gray-300 mb-4">Créez votre première catégorie pour organiser vos produits</p>
                                <button type="button" @click="showCreate = true"
                                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mx-auto">
                                    Nouvelle catégorie
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Modal création ─────────────────────────────────────────────────── -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showCreate = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 space-y-4">
                <h2 class="text-base font-semibold text-gray-900">Nouvelle catégorie</h2>

                <form @submit.prevent="submitCreate" enctype="multipart/form-data" class="space-y-4">

                    <!-- Nom + Ordre -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom <span class="text-red-500">*</span></label>
                            <input v-model="createForm.name" type="text" required
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p v-if="createForm.errors.name" class="mt-1 text-[12px] text-red-500">{{ createForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Ordre</label>
                            <input v-model.number="createForm.order" type="number" min="0"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                        <textarea v-model="createForm.description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <!-- Catégorie parente -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Catégorie parente</label>
                        <select v-model="createForm.parent_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Aucune (catégorie racine)</option>
                            <template v-for="cat in tree" :key="cat.id">
                                <option :value="cat.id">{{ cat.name }}</option>
                                <option v-for="child in (cat.children ?? [])" :key="child.id" :value="child.id">&nbsp;&nbsp;└ {{ child.name }}</option>
                            </template>
                        </select>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Image</label>
                        <div v-if="createImagePrev" class="mb-2 relative w-20 h-20">
                            <img :src="createImagePrev" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                            <button type="button" @click="createForm.image = null; createImagePrev = null"
                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] flex items-center justify-center hover:bg-red-600">✕</button>
                        </div>
                        <input type="file" accept="image/*" @change="onCreateImage"
                            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600 cursor-pointer">
                    </div>

                    <!-- Statuts -->
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="createForm.is_active" type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="createForm.is_featured" type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-gray-700">Mise en avant</span>
                        </label>
                    </div>

                    <!-- SEO (accordéon) -->
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <button type="button" @click="showSeoCreate = !showSeoCreate"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-[12px] font-semibold text-gray-500 uppercase tracking-wide hover:bg-gray-50 transition-colors">
                            <span>SEO (optionnel)</span>
                            <svg class="w-4 h-4 transition-transform" :class="showSeoCreate ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div v-if="showSeoCreate" class="px-3 pb-3 space-y-3 border-t border-gray-100">
                            <div class="pt-3">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Titre SEO</label>
                                <input v-model="createForm.meta_title" type="text" placeholder="Laisser vide pour utiliser le nom"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-0.5 text-[11px] text-gray-400">{{ (createForm.meta_title || '').length }}/255</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description SEO</label>
                                <textarea v-model="createForm.meta_description" rows="2" placeholder="Résumé pour Google (160 car. recommandés)"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                <p class="mt-0.5 text-[11px]" :class="(createForm.meta_description || '').length > 160 ? 'text-amber-500' : 'text-gray-400'">
                                    {{ (createForm.meta_description || '').length }}/500
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Erreurs globales -->
                    <div v-if="Object.keys(createForm.errors).length" class="rounded-lg bg-red-50 border border-red-100 px-3 py-2.5 space-y-1">
                        <p v-for="(msg, field) in createForm.errors" :key="field" class="text-[12px] text-red-600">• {{ msg }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit" :disabled="createForm.processing"
                            class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                            {{ createForm.processing ? 'Création…' : 'Créer la catégorie' }}
                        </button>
                        <button type="button" @click="showCreate = false"
                            class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Modal édition ──────────────────────────────────────────────────── -->
        <div v-if="editingId !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="editingId = null"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 space-y-4">

                <!-- En-tête avec breadcrumb -->
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Modifier la catégorie</h2>
                    <p v-if="categoryPath" class="text-[12px] text-gray-400 mt-0.5">{{ categoryPath }}</p>
                </div>

                <form @submit.prevent="submitEdit(editingId)" enctype="multipart/form-data" class="space-y-4">

                    <!-- Nom + Ordre -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom <span class="text-red-500">*</span></label>
                            <input v-model="editForm.name" type="text" required
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p v-if="editForm.errors.name" class="mt-1 text-[12px] text-red-500">{{ editForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Ordre</label>
                            <input v-model.number="editForm.order" type="number" min="0"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                        <textarea v-model="editForm.description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <!-- Catégorie parente -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Catégorie parente</label>
                        <select v-model="editForm.parent_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Aucune (catégorie racine)</option>
                            <template v-for="cat in editableParents" :key="cat.id">
                                <option :value="cat.id">{{ cat.name }}</option>
                                <option v-for="child in (cat.children ?? [])" :key="child.id" :value="child.id">&nbsp;&nbsp;└ {{ child.name }}</option>
                            </template>
                        </select>
                    </div>

                    <!-- Image avec aperçu de l'image actuelle -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Image</label>

                        <!-- Aperçu : nouvelle image choisie OU image actuelle -->
                        <div v-if="editImagePrev || editingCat?.image" class="mb-2 flex items-center gap-3">
                            <img :src="editImagePrev || ('/storage/' + editingCat.image)"
                                class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                            <div>
                                <p class="text-[12px] text-gray-500">
                                    {{ editImagePrev ? 'Nouvelle image sélectionnée' : 'Image actuelle' }}
                                </p>
                                <p v-if="!editImagePrev" class="text-[11px] text-gray-400">Choisissez un fichier pour la remplacer</p>
                            </div>
                        </div>
                        <div v-else class="mb-2 flex items-center gap-2">
                            <span class="w-16 h-16 rounded-lg bg-gray-50 border border-dashed border-gray-200 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <p class="text-[12px] text-gray-400">Aucune image définie</p>
                        </div>

                        <input type="file" accept="image/*" @change="onEditImage"
                            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600 cursor-pointer">
                    </div>

                    <!-- Statuts -->
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="editForm.is_active" type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="editForm.is_featured" type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-gray-700">Mise en avant</span>
                        </label>
                    </div>

                    <!-- SEO (accordéon) -->
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <button type="button" @click="showSeoEdit = !showSeoEdit"
                            class="w-full flex items-center justify-between px-3 py-2.5 text-[12px] font-semibold text-gray-500 uppercase tracking-wide hover:bg-gray-50 transition-colors">
                            <span>SEO (optionnel)</span>
                            <svg class="w-4 h-4 transition-transform" :class="showSeoEdit ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div v-if="showSeoEdit" class="px-3 pb-3 space-y-3 border-t border-gray-100">
                            <div class="pt-3">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Titre SEO</label>
                                <input v-model="editForm.meta_title" type="text" placeholder="Laisser vide pour utiliser le nom"
                                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-0.5 text-[11px] text-gray-400">{{ (editForm.meta_title || '').length }}/255</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description SEO</label>
                                <textarea v-model="editForm.meta_description" rows="2" placeholder="Résumé pour Google (160 car. recommandés)"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                <p class="mt-0.5 text-[11px]" :class="(editForm.meta_description || '').length > 160 ? 'text-amber-500' : 'text-gray-400'">
                                    {{ (editForm.meta_description || '').length }}/500
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Erreurs globales -->
                    <div v-if="Object.keys(editForm.errors).length" class="rounded-lg bg-red-50 border border-red-100 px-3 py-2.5 space-y-1">
                        <p v-for="(msg, field) in editForm.errors" :key="field" class="text-[12px] text-red-600">• {{ msg }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit" :disabled="editForm.processing"
                            class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                            {{ editForm.processing ? 'Enregistrement…' : 'Enregistrer' }}
                        </button>
                        <button type="button" @click="editingId = null"
                            class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <ConfirmModal />
    <ToastContainer />
</template>
