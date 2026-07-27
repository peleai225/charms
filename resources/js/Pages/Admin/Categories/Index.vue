<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    categories: Array,
    tree:       Array,
})

// ── Formulaire création ───────────────────────────────────────────────────────
const showCreate = ref(false)
const createForm = useForm({
    name:        '',
    description: '',
    parent_id:   '',
    is_active:   true,
    is_featured: false,
    image:       null,
})

function submitCreate() {
    createForm.post(route('admin.categories.store'), {
        forceFormData: true,
        onSuccess: () => {
            showCreate.value = false
            createForm.reset()
        },
    })
}

// ── Formulaire édition ────────────────────────────────────────────────────────
const editingId  = ref(null)
const editForm   = useForm({
    name:        '',
    description: '',
    parent_id:   '',
    is_active:   true,
    is_featured: false,
    order:       0,
    image:       null,
})

function openEdit(cat) {
    editingId.value = cat.id
    editForm.name        = cat.name
    editForm.description = cat.description ?? ''
    editForm.parent_id   = cat.parent_id ?? ''
    editForm.is_active   = !!cat.is_active
    editForm.is_featured = !!cat.is_featured
    editForm.order       = cat.order ?? 0
    editForm.image       = null
}

function submitEdit(catId) {
    editForm.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('admin.categories.update', catId), {
            forceFormData: true,
            onSuccess: () => { editingId.value = null },
        })
}

function deleteCategory(catId) {
    if (!confirm('Supprimer cette catégorie ?')) return
    router.delete(route('admin.categories.destroy', catId))
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function rootCategories() {
    return (props.categories ?? []).filter(c => !c.parent_id)
}

function indent(level) {
    return level === 0 ? '' : level === 1 ? '└ ' : '&nbsp;&nbsp;└ '
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Catégories</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ categories.length }} catégorie(s) au total</p>
            </div>
            <button type="button" @click="showCreate = true"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle catégorie
            </button>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Catégorie</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Slug</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produits</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="tree.length" class="divide-y divide-gray-50">
                        <template v-for="cat in tree" :key="cat.id">
                            <!-- Niveau 0 -->
                            <tr class="hover:bg-gray-50 group">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <img v-if="cat.image" :src="'/storage/' + cat.image" class="w-7 h-7 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                        <span v-else class="w-7 h-7 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0"></span>
                                        <span class="text-[13px] font-semibold text-gray-900">{{ cat.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-mono text-[12px] text-gray-500">{{ cat.slug }}</td>
                                <td class="px-5 py-3 text-center text-[13px] text-gray-600">{{ cat.products_count ?? 0 }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                        :class="cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                        {{ cat.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openEdit(cat)"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" @click="deleteCategory(cat.id)"
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Niveau 1 -->
                            <template v-for="child in (cat.children ?? [])" :key="child.id">
                                <tr class="hover:bg-gray-50 group bg-gray-50/40">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5 pl-5">
                                            <span class="text-gray-300">└</span>
                                            <img v-if="child.image" :src="'/storage/' + child.image" class="w-6 h-6 rounded object-cover border border-gray-200 flex-shrink-0">
                                            <span class="text-[13px] text-gray-800">{{ child.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-[12px] text-gray-500">{{ child.slug }}</td>
                                    <td class="px-5 py-3 text-center text-[13px] text-gray-600">{{ child.products_count ?? 0 }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                            :class="child.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ child.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="openEdit(child)"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click="deleteCategory(child.id)"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Niveau 2 -->
                                <tr v-for="grandchild in (child.children ?? [])" :key="grandchild.id" class="hover:bg-gray-50 group bg-gray-50/60">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5 pl-10">
                                            <span class="text-gray-300">└</span>
                                            <span class="text-[13px] text-gray-700">{{ grandchild.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-[12px] text-gray-500">{{ grandchild.slug }}</td>
                                    <td class="px-5 py-3 text-center text-[13px] text-gray-600">{{ grandchild.products_count ?? 0 }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                            :class="grandchild.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ grandchild.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="openEdit(grandchild)"
                                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click="deleteCategory(grandchild.id)"
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
                            <td colspan="5" class="px-5 py-16 text-center">
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

        <!-- Modal création -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showCreate = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h2 class="text-base font-semibold text-gray-900">Nouvelle catégorie</h2>
                <form @submit.prevent="submitCreate" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom <span class="text-red-500">*</span></label>
                        <input v-model="createForm.name" type="text" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p v-if="createForm.errors.name" class="mt-1 text-[12px] text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                        <textarea v-model="createForm.description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Catégorie parente</label>
                        <select v-model="createForm.parent_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucune (catégorie racine)</option>
                            <template v-for="cat in tree" :key="cat.id">
                                <option :value="cat.id">{{ cat.name }}</option>
                                <option v-for="child in (cat.children ?? [])" :key="child.id" :value="child.id">&nbsp;&nbsp;└ {{ child.name }}</option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Image</label>
                        <input type="file" accept="image/*" @change="e => createForm.image = e.target.files[0]"
                            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600">
                    </div>
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
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit" :disabled="createForm.processing"
                            class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                            Créer la catégorie
                        </button>
                        <button type="button" @click="showCreate = false"
                            class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal édition -->
        <div v-if="editingId !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="editingId = null"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h2 class="text-base font-semibold text-gray-900">Modifier la catégorie</h2>
                <form @submit.prevent="submitEdit(editingId)" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p v-if="editForm.errors.name" class="mt-1 text-[12px] text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                        <textarea v-model="editForm.description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Catégorie parente</label>
                        <select v-model="editForm.parent_id"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucune (catégorie racine)</option>
                            <template v-for="cat in tree" :key="cat.id">
                                <option :value="cat.id">{{ cat.name }}</option>
                                <option v-for="child in (cat.children ?? [])" :key="child.id" :value="child.id">&nbsp;&nbsp;└ {{ child.name }}</option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Ordre</label>
                        <input v-model.number="editForm.order" type="number" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Image</label>
                        <input type="file" accept="image/*" @change="e => editForm.image = e.target.files[0]"
                            class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600">
                    </div>
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
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit" :disabled="editForm.processing"
                            class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                            Enregistrer
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
</template>
