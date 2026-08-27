<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'
import { useConfirm } from '@/Composables/useConfirm'
import Badge from '@/Components/UI/Badge.vue'
import { Table, TableActions } from '@/Components/UI/Table/index.js'
import DropdownItem from '@/Components/UI/DropdownItem.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import FilterBar from '@/Components/UI/FilterBar.vue'
import FilterSelect from '@/Components/UI/FilterSelect.vue'
import Breadcrumb from '@/Components/UI/Breadcrumb.vue'

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
})

const toast = useToast()
const confirm = useConfirm()

// ── Filtres ──────────────────────────────────────────────────────────────────

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')
const category = ref(props.filters?.category ?? '')
const stock = ref(props.filters?.stock ?? '')

const activeFilterCount = computed(() => {
    return [status.value, category.value, stock.value].filter(Boolean).length
})

function applyFilters() {
    router.get(route('admin.products.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        category: category.value || undefined,
        stock: stock.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = ''
    category.value = ''
    stock.value = ''
    router.get(route('admin.products.index'), {}, { preserveState: false })
}

function goToPage(page) {
    router.get(route('admin.products.index'), {
        page,
        search: search.value || undefined,
        status: status.value || undefined,
        category: category.value || undefined,
        stock: stock.value || undefined,
    }, { preserveState: true, replace: true })
}

let debounceTimer = null
function onSearch(val) {
    search.value = val
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(applyFilters, 300)
}

// ── Colonnes table ────────────────────────────────────────────────────────────

const STATUS_VARIANT = { active: 'success', draft: 'default', archived: 'danger' }
const STATUS_LABEL = { active: 'Actif', draft: 'Brouillon', archived: 'Archivé' }

const columns = [
    {
        key: 'name',
        label: 'Produit',
        sortable: true,
    },
    {
        key: 'sku',
        label: 'SKU',
        format: (val) => val ?? '—',
    },
    {
        key: 'sale_price',
        label: 'Prix',
        align: 'right',
        format: (val) => val != null ? Number(val).toLocaleString('fr-FR') + ' F' : '—',
    },
    {
        key: 'stock_quantity',
        label: 'Stock',
        align: 'center',
    },
    {
        key: 'status',
        label: 'Statut',
        align: 'center',
        component: Badge,
        componentProps: (row) => ({
            variant: STATUS_VARIANT[row.status] ?? 'default',
            label: STATUS_LABEL[row.status] ?? row.status,
        }),
    },
]

// ── Actions ───────────────────────────────────────────────────────────────────

const selected = ref([])

async function deleteProduct(product) {
    const ok = await confirm({
        title: `Supprimer « ${product.name} » ?`,
        message: 'Cette action est irréversible. Les images et variantes associées seront supprimées.',
        confirmLabel: 'Supprimer',
        variant: 'danger',
    })
    if (!ok) return

    router.delete(route('admin.products.destroy', product.id), {
        onSuccess: () => toast.success('Produit supprimé'),
        onError: () => toast.error('Erreur lors de la suppression'),
    })
}

async function deleteSelected() {
    if (!selected.value.length) return
    const ok = await confirm({
        title: `Supprimer ${selected.value.length} produit(s) ?`,
        message: 'Cette action est irréversible.',
        confirmLabel: 'Supprimer',
        variant: 'danger',
    })
    if (!ok) return

    router.post(route('admin.products.bulk-destroy'), {
        ids: selected.value.map((p) => p.id),
    }, {
        onSuccess: () => {
            toast.success(`${selected.value.length} produit(s) supprimé(s)`)
            selected.value = []
        },
        onError: () => toast.error('Erreur lors de la suppression'),
    })
}

// ── Options filtres ───────────────────────────────────────────────────────────

const statusOptions = [
    { label: 'Actif', value: 'active' },
    { label: 'Brouillon', value: 'draft' },
    { label: 'Archivé', value: 'archived' },
]

const stockOptions = [
    { label: 'Stock faible', value: 'low' },
    { label: 'Rupture', value: 'out' },
]

const categoryOptions = computed(() =>
    (props.categories ?? []).map((c) => ({ label: c.name, value: String(c.id) }))
)

const breadcrumbs = [
    { label: 'Admin', href: route('admin.dashboard') },
    { label: 'Produits' },
]
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Breadcrumb -->
        <Breadcrumb :items="breadcrumbs" />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Produits</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ products.meta?.total ?? products.total ?? 0 }} produit(s) au total
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    :href="route('admin.import-export.index')"
                    class="h-9 px-4 flex items-center gap-2 border border-gray-200 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importer
                </a>
                <a
                    :href="route('admin.products.create')"
                    class="h-9 px-4 flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau produit
                </a>
            </div>
        </div>

        <!-- Filters -->
        <FilterBar :active-count="activeFilterCount" @reset="resetFilters">
            <SearchInput
                :model-value="search"
                placeholder="Rechercher par nom, SKU…"
                class="flex-1 min-w-[200px]"
                @update:model-value="onSearch"
            />
            <FilterSelect
                v-model="status"
                label="Statut"
                :options="statusOptions"
                @update:model-value="applyFilters"
            />
            <FilterSelect
                v-model="category"
                label="Catégorie"
                :options="categoryOptions"
                @update:model-value="applyFilters"
            />
            <FilterSelect
                v-model="stock"
                label="Stock"
                :options="stockOptions"
                @update:model-value="applyFilters"
            />
        </FilterBar>

        <!-- Table -->
        <Table
            :data="products.data"
            :columns="columns"
            v-model:selected="selected"
            empty-title="Aucun produit"
            empty-description="Commencez par créer votre premier produit."
            empty-icon="package"
        >
            <!-- Thumbnail dans la colonne nom -->
            <template #cell-name="{ row }">
                <div class="flex items-center gap-3">
                    <img
                        v-if="row.primary_image_url"
                        :src="row.primary_image_url"
                        :alt="row.name"
                        class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0"
                    >
                    <div v-else class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0"></div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ row.name }}</p>
                        <p v-if="row.category" class="text-xs text-gray-400 truncate">{{ row.category.name }}</p>
                    </div>
                </div>
            </template>

            <!-- Stock avec badge coloré -->
            <template #cell-stock_quantity="{ row }">
                <Badge
                    :variant="row.stock_quantity <= 0 ? 'danger' : row.stock_quantity <= (row.stock_alert_threshold ?? 5) ? 'warning' : 'success'"
                    :label="row.stock_quantity <= 0 ? 'Rupture' : row.stock_quantity + ' pcs'"
                />
            </template>

            <!-- Bulk actions -->
            <template #bulk-actions="{ selected: sel }">
                <button
                    class="h-8 px-3 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                    @click="deleteSelected"
                >
                    Supprimer ({{ sel.length }})
                </button>
            </template>

            <!-- Actions par ligne -->
            <template #actions="{ row }">
                <TableActions :row="row">
                    <DropdownItem :href="route('admin.products.edit', row.id)">
                        Modifier
                    </DropdownItem>
                    <DropdownItem :href="route('admin.products.show', row.id)">
                        Voir
                    </DropdownItem>
                    <DropdownItem danger @click="deleteProduct(row)">
                        Supprimer
                    </DropdownItem>
                </TableActions>
            </template>
        </Table>

        <!-- Pagination -->
        <Pagination :meta="products.meta ?? products" :links="products.links" @change="goToPage" />

    </div>
</template>
