# Spec — Bibliothèque Composants Vue (Chamse)
*2026-07-23*

## Contexte

Le projet Chamse est en migration active du backoffice Blade/Livewire vers Vue 3/Inertia (`feat/inertia-vue-migration`). La bibliothèque Vue actuelle compte 9 composants et 1 composable — insuffisant pour migrer les pages admin. Cette spec définit la bibliothèque complète qui servira de fondation à toute la migration.

## Objectif

Construire une bibliothèque de composants génériques dans `resources/js/Components/UI/` et `resources/js/Composables/`, utilisables indistinctement sur le front-office et le backoffice migré.

## Stack

- Vue 3 + Tailwind CSS 4
- `@headlessui/vue` pour Modal, Drawer, Dropdown (accessibilité : focus trap, ARIA, Escape)
- Pinia pour le store Toast
- Inertia router pour Pagination

---

## Structure des fichiers

```
resources/js/
├── Components/
│   ├── UI/
│   │   ├── Modal.vue
│   │   ├── ConfirmModal.vue
│   │   ├── Toast.vue
│   │   ├── ToastContainer.vue
│   │   ├── EmptyState.vue
│   │   ├── Skeleton.vue
│   │   ├── LoadingSpinner.vue
│   │   ├── Table/
│   │   │   ├── Table.vue
│   │   │   ├── TableHeader.vue
│   │   │   ├── TableRow.vue
│   │   │   ├── TableActions.vue
│   │   │   └── index.js
│   │   ├── Pagination.vue
│   │   ├── SearchInput.vue
│   │   ├── FilterBar.vue
│   │   ├── FilterSelect.vue
│   │   ├── FilterDateRange.vue
│   │   ├── Dropdown.vue
│   │   ├── Drawer.vue
│   │   ├── Breadcrumb.vue
│   │   └── Badge.vue
│   ├── Button.vue          # existant — inchangé
│   ├── Card.vue            # existant — inchangé
│   ├── Input.vue           # existant — inchangé
│   ├── ProductCard.vue     # existant — inchangé
│   └── AccountSidebar.vue  # existant — inchangé
└── Composables/
    ├── useToast.js         # nouveau
    ├── useConfirm.js       # nouveau
    ├── useForm.js          # nouveau
    └── useHelpers.js       # existant — inchangé
```

**Règle :** `UI/` = générique sans opinion métier. Composants métier Chamse restent à la racine de `Components/`.

---

## Composants — Interfaces

### Modal.vue
```vue
<Modal v-model="isOpen" title="Titre" size="lg">
  <template #default><!-- contenu --></template>
  <template #footer>
    <Button variant="ghost" @click="isOpen = false">Annuler</Button>
    <Button @click="save">Enregistrer</Button>
  </template>
</Modal>
```
Props : `v-model` (boolean), `title` (string), `size` ('sm'|'md'|'lg'|'xl'|'full', défaut 'md'), `closable` (boolean, défaut true), `closeOnBackdrop` (boolean, défaut true).

Basé sur `HeadlessUI Dialog`.

---

### ConfirmModal.vue + useConfirm
Usage programmatique via Promise :
```js
const confirm = useConfirm()

const ok = await confirm({
  title: 'Supprimer le produit',
  message: 'Cette action est irréversible.',
  confirmLabel: 'Supprimer',
  variant: 'danger'  // 'danger' | 'warning' | 'default'
})
if (!ok) return
```
`ConfirmModal` est monté une fois dans le layout. `useConfirm` expose une Promise résolue à `true` (confirmé) ou `false` (annulé).

---

### Toast + useToast
```js
const toast = useToast()

toast.success('Produit enregistré')
toast.error('Erreur lors de la sauvegarde')
toast.warning('Stock faible')
toast.info('Synchronisation en cours...')

// Avec action
toast.success('Commande créée', {
  duration: 4000,
  action: { label: 'Voir', onClick: () => router.visit('/orders/123') }
})
```
`ToastContainer` monté une fois dans le layout, écoute `useToastStore` (Pinia). Durée par défaut : 3000ms. Position : bas-droite.

---

### EmptyState.vue
```vue
<EmptyState
  icon="package"
  title="Aucun produit"
  description="Commencez par créer votre premier produit."
>
  <Button @click="router.visit('/admin/products/create')">
    Créer un produit
  </Button>
</EmptyState>
```
Slot par défaut pour le CTA. `icon` = nom d'icône Lucide.

---

### Skeleton.vue
Composant minimal — `div` avec `animate-pulse` Tailwind. Taille définie via classes Tailwind passées directement :
```vue
<Skeleton class="h-4 w-48" />
<Skeleton class="h-32 w-full rounded-lg" />
```

---

### Table/*

**Définition des colonnes :**
```js
import Badge from '@/Components/UI/Badge.vue'

const columns = [
  { key: 'order_number', label: 'Commande', sortable: true, width: 'w-40' },
  { key: 'customer.name', label: 'Client' },         // dot notation
  { key: 'total', label: 'Total', align: 'right', format: (val) => formatPrice(val) },
  { key: 'status', label: 'Statut', component: Badge } // référence directe, pas string
]
```

**Usage :**
```vue
<Table
  :data="orders.data"
  :columns="columns"
  :loading="isLoading"
  v-model:selected="selected"
  empty-title="Aucune commande"
>
  <template #bulk-actions="{ selected }">
    <Button variant="danger" @click="deleteSelected(selected)">
      Supprimer ({{ selected.length }})
    </Button>
  </template>
  <template #actions="{ row }">
    <TableActions :row="row">
      <DropdownItem danger @click="deleteOrder(row)">Supprimer</DropdownItem>
    </TableActions>
  </template>
</Table>
<Pagination :meta="orders.meta" :links="orders.links" />
```

Slots : `#actions` (par ligne), `#bulk-actions` (sélection multiple).

---

### Pagination.vue
```vue
<Pagination
  :meta="orders.meta"
  :links="orders.links"
  @change="(page) => router.visit(route('admin.orders.index', { page }))"
/>
```
Affiche : `Affichage 1–15 sur 180 résultats` + navigation numérotée style Stripe.
`meta` = structure standard Laravel ResourceCollection.

---

### FilterBar.vue
```vue
<FilterBar v-model="filters" @reset="resetFilters">
  <FilterSelect v-model="filters.status" label="Statut" :options="statusOptions" />
  <FilterSelect v-model="filters.category" label="Catégorie" :options="categories" />
  <FilterDateRange v-model="filters.dateRange" label="Période" />
</FilterBar>
```
Conteneur flex. Bouton "Réinitialiser" automatique si filtres actifs. Badge "N filtres actifs" affiché.

---

### Drawer.vue
```vue
<Drawer v-model="isOpen" title="Détails" side="right" size="lg">
  <template #default><!-- contenu --></template>
  <template #footer><Button @click="isOpen = false">Fermer</Button></template>
</Drawer>
```
Props : `side` ('right'|'left', défaut 'right'), `size` ('sm'|'md'|'lg'|'full', défaut 'md').
Basé sur `HeadlessUI TransitionRoot`.

---

### useForm.js
Wrapper autour de `useForm` Inertia avec helpers :
```js
const form = useForm({ name: '', price: 0 })

form.hasError('name')      // boolean
form.errorMessage('name')  // string | null
form.isSubmitting          // alias form.processing
```

---

## Pattern complet — page liste admin

```vue
<script setup>
const props = defineProps({ orders: Object })
const confirm = useConfirm()
const toast = useToast()
const selected = ref([])

async function deleteOrder(order) {
  const ok = await confirm({
    title: `Supprimer la commande ${order.order_number}`,
    variant: 'danger'
  })
  if (!ok) return
  router.delete(route('admin.orders.destroy', order.id), {
    onSuccess: () => toast.success('Commande supprimée')
  })
}
</script>

<template>
  <Table :data="orders.data" :columns="columns" v-model:selected="selected">
    <template #actions="{ row }">
      <TableActions :row="row">
        <DropdownItem danger @click="deleteOrder(row)">Supprimer</DropdownItem>
      </TableActions>
    </template>
  </Table>
  <Pagination :meta="orders.meta" :links="orders.links" />
</template>
```

---

## Installation

```bash
npm install @headlessui/vue
```

`ToastContainer` et `ConfirmModal` enregistrés globalement dans `app.js` (montés une fois dans le layout). Tous les autres composants importés à la demande — tree-shaking optimal. L'alias `@` → `resources/js` est déjà configuré dans Vite.

---

## Inventaire complet

| Composant | Base | Complexité |
|---|---|---|
| Modal | Headless UI Dialog | Moyenne |
| ConfirmModal + useConfirm | Modal + Promise | Moyenne |
| Toast + ToastContainer + useToast | Pinia | Faible |
| EmptyState | Tailwind | Faible |
| Skeleton | Tailwind | Très faible |
| LoadingSpinner | Tailwind | Très faible |
| Table + TableHeader + TableRow + TableActions + index.js | Tailwind | Élevée |
| Pagination | Inertia router | Faible |
| SearchInput | Tailwind | Faible |
| FilterBar + FilterSelect + FilterDateRange | Headless UI | Moyenne |
| Dropdown | Headless UI Menu | Faible |
| Drawer | Headless UI TransitionRoot | Faible |
| Breadcrumb | Tailwind | Très faible |
| Badge | Tailwind | Très faible |
| useToast | Pinia | Faible |
| useConfirm | Promise + Pinia | Moyenne |
| useForm | Inertia wrapper | Faible |
