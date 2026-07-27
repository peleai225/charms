# Vue Component Library Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construire une bibliothèque de composants Vue génériques dans `resources/js/Components/UI/` et `resources/js/Composables/` pour débloquer la migration Inertia du backoffice Chamse.

**Architecture:** Composants génériques sans opinion métier dans `UI/`, composables dans `Composables/`. Headless UI gère l'accessibilité (focus trap, ARIA, Escape) pour Modal/Drawer/Dropdown. Un store Pinia `useToastStore` alimente `ToastContainer` monté une fois dans le layout. `useConfirm` expose une API Promise programmatique via un store Pinia partagé avec `ConfirmModal`.

**Tech Stack:** Vue 3, Tailwind CSS 4, `@headlessui/vue`, Pinia, Inertia.js (`@inertiajs/vue3`), Ziggy (`route()`).

## Global Constraints

- Tailwind CSS 4 uniquement — pas de CSS custom sauf cas exceptionnel justifié
- Couleurs : Primary `#2563EB`, Success `#16A34A`, Warning `#F59E0B`, Danger `#DC2626`
- Icônes Lucide uniquement (`lucide-vue-next`) — pas d'autre librairie d'icônes
- Police Inter (déjà chargée globalement)
- Composants sans données fictives hardcodées
- Tous les composants : `<script setup>` + Composition API
- Alias `@` → `resources/js` (configuré dans Vite, ne pas modifier)
- Pas de `console.log` en production

---

## Fichiers créés / modifiés

| Fichier | Action | Tâche |
|---|---|---|
| `resources/js/Components/UI/Badge.vue` | Créer | T1 |
| `resources/js/Components/UI/Skeleton.vue` | Créer | T1 |
| `resources/js/Components/UI/LoadingSpinner.vue` | Créer | T1 |
| `resources/js/Components/UI/EmptyState.vue` | Créer | T1 |
| `resources/js/Stores/toast.js` | Créer | T2 |
| `resources/js/Composables/useToast.js` | Créer | T2 |
| `resources/js/Components/UI/Toast.vue` | Créer | T2 |
| `resources/js/Components/UI/ToastContainer.vue` | Créer | T2 |
| `resources/js/Stores/confirm.js` | Créer | T3 |
| `resources/js/Composables/useConfirm.js` | Créer | T3 |
| `resources/js/Components/UI/Modal.vue` | Créer | T3 |
| `resources/js/Components/UI/ConfirmModal.vue` | Créer | T3 |
| `resources/js/Composables/useForm.js` | Créer | T4 |
| `resources/js/Components/UI/Dropdown.vue` | Créer | T5 |
| `resources/js/Components/UI/Table/TableHeader.vue` | Créer | T6 |
| `resources/js/Components/UI/Table/TableRow.vue` | Créer | T6 |
| `resources/js/Components/UI/Table/TableActions.vue` | Créer | T6 |
| `resources/js/Components/UI/Table/Table.vue` | Créer | T6 |
| `resources/js/Components/UI/Table/index.js` | Créer | T6 |
| `resources/js/Components/UI/Pagination.vue` | Créer | T7 |
| `resources/js/Components/UI/SearchInput.vue` | Créer | T7 |
| `resources/js/Components/UI/FilterSelect.vue` | Créer | T8 |
| `resources/js/Components/UI/FilterDateRange.vue` | Créer | T8 |
| `resources/js/Components/UI/FilterBar.vue` | Créer | T8 |
| `resources/js/Components/UI/Drawer.vue` | Créer | T9 |
| `resources/js/Components/UI/Breadcrumb.vue` | Créer | T9 |
| `resources/js/Layouts/FrontLayout.vue` | Modifier | T10 |
| `package.json` | Modifier | T0 |

---

## Task 0 : Installation de @headlessui/vue

**Files:**
- Modify: `package.json`

**Interfaces:**
- Produces: `@headlessui/vue` disponible dans tous les composants suivants via `import { Dialog, ... } from '@headlessui/vue'`

- [ ] **Step 1 : Installer la dépendance**

```bash
cd c:/laragon/www/chamse && npm install @headlessui/vue
```
Résultat attendu : `added N packages` sans erreur.

- [ ] **Step 2 : Vérifier l'installation**

```bash
cd c:/laragon/www/chamse && node -e "import('@headlessui/vue').then(m => console.log('OK', Object.keys(m).slice(0,5)))"
```
Résultat attendu : `OK [ 'Combobox', 'ComboboxButton', ... ]`

- [ ] **Step 3 : Commit**

```bash
cd c:/laragon/www/chamse && git add package.json package-lock.json && git commit -m "chore: install @headlessui/vue"
```

---

## Task 1 : Composants de base — Badge, Skeleton, LoadingSpinner, EmptyState

**Files:**
- Create: `resources/js/Components/UI/Badge.vue`
- Create: `resources/js/Components/UI/Skeleton.vue`
- Create: `resources/js/Components/UI/LoadingSpinner.vue`
- Create: `resources/js/Components/UI/EmptyState.vue`

**Interfaces:**
- Consumes: rien (composants autonomes Tailwind)
- Produces:
  - `Badge` : prop `variant` ('default'|'success'|'warning'|'danger'|'info', défaut 'default'), prop `size` ('sm'|'md', défaut 'md'), slot default
  - `Skeleton` : accepte classes Tailwind via `class`, prop `rounded` (boolean, défaut false)
  - `LoadingSpinner` : prop `size` ('sm'|'md'|'lg', défaut 'md'), prop `color` (string, défaut 'text-blue-600')
  - `EmptyState` : prop `icon` (string, nom Lucide), prop `title` (string), prop `description` (string), slot default (CTA)

- [ ] **Step 1 : Créer Badge.vue**

```vue
<!-- resources/js/Components/UI/Badge.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md'].includes(v),
    },
})

const classes = computed(() => {
    const variants = {
        default: 'bg-gray-100 text-gray-700',
        success: 'bg-green-100 text-green-700',
        warning: 'bg-yellow-100 text-yellow-700',
        danger:  'bg-red-100 text-red-700',
        info:    'bg-blue-100 text-blue-700',
    }
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-sm',
    }
    return `inline-flex items-center font-medium rounded-full ${variants[props.variant]} ${sizes[props.size]}`
})
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
```

- [ ] **Step 2 : Créer Skeleton.vue**

```vue
<!-- resources/js/Components/UI/Skeleton.vue -->
<script setup>
defineProps({
    rounded: { type: Boolean, default: false },
})
</script>

<template>
    <div
        class="animate-pulse bg-gray-200"
        :class="{ 'rounded-full': rounded }"
    />
</template>
```

- [ ] **Step 3 : Créer LoadingSpinner.vue**

```vue
<!-- resources/js/Components/UI/LoadingSpinner.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    color: { type: String, default: 'text-blue-600' },
})

const sizeClass = computed(() => ({ sm: 'h-4 w-4', md: 'h-6 w-6', lg: 'h-8 w-8' }[props.size]))
</script>

<template>
    <svg
        :class="[sizeClass, color, 'animate-spin']"
        fill="none"
        viewBox="0 0 24 24"
        aria-hidden="true"
    >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
    </svg>
</template>
```

- [ ] **Step 4 : Créer EmptyState.vue**

Installe d'abord `lucide-vue-next` si pas déjà présent :
```bash
cd c:/laragon/www/chamse && npm list lucide-vue-next 2>/dev/null || npm install lucide-vue-next
```

```vue
<!-- resources/js/Components/UI/EmptyState.vue -->
<script setup>
import { computed } from 'vue'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
    icon:        { type: String,  default: 'inbox' },
    title:       { type: String,  required: true },
    description: { type: String,  default: '' },
})

const IconComponent = computed(() => {
    const name = props.icon.charAt(0).toUpperCase() + props.icon.slice(1).replace(/-([a-z])/g, (_, c) => c.toUpperCase())
    return LucideIcons[name] || LucideIcons['Inbox']
})
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
            <component :is="IconComponent" class="w-6 h-6 text-gray-400" />
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ title }}</h3>
        <p v-if="description" class="text-sm text-gray-500 mb-6 max-w-sm">{{ description }}</p>
        <div v-if="$slots.default" class="mt-2">
            <slot />
        </div>
    </div>
</template>
```

- [ ] **Step 5 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -20
```
Résultat attendu : `✓ built in` sans erreur.

- [ ] **Step 6 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/ package.json package-lock.json && git commit -m "feat(ui): Badge, Skeleton, LoadingSpinner, EmptyState"
```

---

## Task 2 : Toast + useToast + ToastContainer

**Files:**
- Create: `resources/js/Stores/toast.js`
- Create: `resources/js/Composables/useToast.js`
- Create: `resources/js/Components/UI/Toast.vue`
- Create: `resources/js/Components/UI/ToastContainer.vue`

**Interfaces:**
- Consumes: Pinia (`createPinia()` déjà dans `app.js`)
- Produces:
  - `useToastStore()` : `{ toasts, add(toast), remove(id) }`
  - `useToast()` : `{ success(msg, opts?), error(msg, opts?), warning(msg, opts?), info(msg, opts?) }`
  - `ToastContainer` : composant sans props, à monter une fois dans le layout

- [ ] **Step 1 : Créer le store Pinia**

```js
// resources/js/Stores/toast.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])
    let nextId = 0

    function add({ type = 'info', message, duration = 3000, action = null }) {
        const id = ++nextId
        toasts.value.push({ id, type, message, duration, action })
        if (duration > 0) {
            setTimeout(() => remove(id), duration)
        }
        return id
    }

    function remove(id) {
        const idx = toasts.value.findIndex(t => t.id === id)
        if (idx !== -1) toasts.value.splice(idx, 1)
    }

    return { toasts, add, remove }
})
```

- [ ] **Step 2 : Créer useToast.js**

```js
// resources/js/Composables/useToast.js
import { useToastStore } from '@/Stores/toast'

export function useToast() {
    const store = useToastStore()

    return {
        success: (message, opts = {}) => store.add({ type: 'success', message, ...opts }),
        error:   (message, opts = {}) => store.add({ type: 'error',   message, ...opts }),
        warning: (message, opts = {}) => store.add({ type: 'warning', message, ...opts }),
        info:    (message, opts = {}) => store.add({ type: 'info',    message, ...opts }),
    }
}
```

- [ ] **Step 3 : Créer Toast.vue** (composant individuel)

```vue
<!-- resources/js/Components/UI/Toast.vue -->
<script setup>
import { computed } from 'vue'
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next'
import { useToastStore } from '@/Stores/toast'

const props = defineProps({
    toast: { type: Object, required: true },
})

const store = useToastStore()

const config = computed(() => ({
    success: { icon: CheckCircle,   bg: 'bg-white border-green-200',  iconClass: 'text-green-500'  },
    error:   { icon: AlertCircle,   bg: 'bg-white border-red-200',    iconClass: 'text-red-500'    },
    warning: { icon: AlertTriangle, bg: 'bg-white border-yellow-200', iconClass: 'text-yellow-500' },
    info:    { icon: Info,          bg: 'bg-white border-blue-200',   iconClass: 'text-blue-500'   },
}[props.toast.type]))
</script>

<template>
    <div
        :class="['flex items-start gap-3 p-4 rounded-lg border shadow-md min-w-72 max-w-sm', config.bg]"
        role="alert"
    >
        <component :is="config.icon" :class="['w-5 h-5 mt-0.5 shrink-0', config.iconClass]" />
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">{{ toast.message }}</p>
            <button
                v-if="toast.action"
                class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-700"
                @click="toast.action.onClick"
            >
                {{ toast.action.label }}
            </button>
        </div>
        <button
            class="shrink-0 text-gray-400 hover:text-gray-600"
            @click="store.remove(toast.id)"
            aria-label="Fermer"
        >
            <X class="w-4 h-4" />
        </button>
    </div>
</template>
```

- [ ] **Step 4 : Créer ToastContainer.vue**

```vue
<!-- resources/js/Components/UI/ToastContainer.vue -->
<script setup>
import { useToastStore } from '@/Stores/toast'
import Toast from './Toast.vue'

const store = useToastStore()
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
            aria-live="polite"
        >
            <TransitionGroup
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="translate-x-4 opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-4 opacity-0"
            >
                <div v-for="toast in store.toasts" :key="toast.id" class="pointer-events-auto">
                    <Toast :toast="toast" />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
```

- [ ] **Step 5 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```
Résultat attendu : `✓ built in` sans erreur.

- [ ] **Step 6 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Stores/toast.js resources/js/Composables/useToast.js resources/js/Components/UI/Toast.vue resources/js/Components/UI/ToastContainer.vue && git commit -m "feat(ui): Toast system — store, useToast, Toast, ToastContainer"
```

---

## Task 3 : Modal + ConfirmModal + useConfirm

**Files:**
- Create: `resources/js/Stores/confirm.js`
- Create: `resources/js/Composables/useConfirm.js`
- Create: `resources/js/Components/UI/Modal.vue`
- Create: `resources/js/Components/UI/ConfirmModal.vue`

**Interfaces:**
- Consumes: `@headlessui/vue` (Dialog, DialogPanel, TransitionRoot, TransitionChild), Pinia
- Produces:
  - `Modal` : `v-model` (boolean), `title` (string), `size` ('sm'|'md'|'lg'|'xl'|'full'), `closable` (boolean), `closeOnBackdrop` (boolean), slots `#default` + `#footer`
  - `useConfirm()` : fonction `confirm({ title, message?, confirmLabel?, cancelLabel?, variant? })` → `Promise<boolean>`
  - `ConfirmModal` : composant sans props, à monter une fois dans le layout

- [ ] **Step 1 : Créer le store confirm**

```js
// resources/js/Stores/confirm.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConfirmStore = defineStore('confirm', () => {
    const isOpen   = ref(false)
    const options  = ref({})
    let resolver   = null

    function open(opts) {
        options.value = opts
        isOpen.value  = true
        return new Promise((resolve) => { resolver = resolve })
    }

    function resolve(result) {
        isOpen.value = false
        if (resolver) { resolver(result); resolver = null }
    }

    return { isOpen, options, open, resolve }
})
```

- [ ] **Step 2 : Créer useConfirm.js**

```js
// resources/js/Composables/useConfirm.js
import { useConfirmStore } from '@/Stores/confirm'

export function useConfirm() {
    const store = useConfirmStore()
    return (opts) => store.open(opts)
}
```

- [ ] **Step 3 : Créer Modal.vue**

```vue
<!-- resources/js/Components/UI/Modal.vue -->
<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    modelValue:       { type: Boolean, required: true },
    title:            { type: String,  default: '' },
    size:             { type: String,  default: 'md', validator: (v) => ['sm','md','lg','xl','full'].includes(v) },
    closable:         { type: Boolean, default: true },
    closeOnBackdrop:  { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])
const close = () => { if (props.closable) emit('update:modelValue', false) }
const onBackdropClick = () => { if (props.closeOnBackdrop) close() }

const panelSize = computed(() => ({
    sm:   'max-w-sm',
    md:   'max-w-md',
    lg:   'max-w-lg',
    xl:   'max-w-xl',
    full: 'max-w-full mx-4',
}[props.size]))
</script>

<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="onBackdropClick">
            <TransitionChild
                enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150"  leave-from="opacity-100" leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        enter="ease-out duration-200" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
                        leave="ease-in duration-150"  leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel :class="['w-full bg-white rounded-xl shadow-xl', panelSize]">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
                                <button
                                    v-if="closable"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                    @click="close"
                                    aria-label="Fermer"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="px-6 py-4">
                                <slot />
                            </div>
                            <div v-if="$slots.footer" class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                                <slot name="footer" />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
```

- [ ] **Step 4 : Créer ConfirmModal.vue**

```vue
<!-- resources/js/Components/UI/ConfirmModal.vue -->
<script setup>
import { computed } from 'vue'
import { useConfirmStore } from '@/Stores/confirm'
import Modal from './Modal.vue'
import Button from '@/Components/Button.vue'

const store = useConfirmStore()

const opts = computed(() => ({
    title:        store.options.title        || 'Confirmer',
    message:      store.options.message      || '',
    confirmLabel: store.options.confirmLabel || 'Confirmer',
    cancelLabel:  store.options.cancelLabel  || 'Annuler',
    variant:      store.options.variant      || 'default',
}))

const confirmVariant = computed(() => opts.value.variant === 'danger' ? 'danger' : opts.value.variant === 'warning' ? 'secondary' : 'primary')
</script>

<template>
    <Modal
        :model-value="store.isOpen"
        :title="opts.title"
        size="sm"
        :close-on-backdrop="false"
        :closable="false"
        @update:model-value="store.resolve(false)"
    >
        <p v-if="opts.message" class="text-sm text-gray-600">{{ opts.message }}</p>

        <template #footer>
            <Button variant="ghost" @click="store.resolve(false)">{{ opts.cancelLabel }}</Button>
            <Button :variant="confirmVariant" @click="store.resolve(true)">{{ opts.confirmLabel }}</Button>
        </template>
    </Modal>
</template>
```

- [ ] **Step 5 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```
Résultat attendu : `✓ built in` sans erreur.

- [ ] **Step 6 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Stores/confirm.js resources/js/Composables/useConfirm.js resources/js/Components/UI/Modal.vue resources/js/Components/UI/ConfirmModal.vue && git commit -m "feat(ui): Modal, ConfirmModal, useConfirm"
```

---

## Task 4 : useForm composable

**Files:**
- Create: `resources/js/Composables/useForm.js`

**Interfaces:**
- Consumes: `useForm` de `@inertiajs/vue3`
- Produces: `useForm(defaults)` → objet Inertia form enrichi de `hasError(field)`, `errorMessage(field)`, `isSubmitting`

- [ ] **Step 1 : Créer useForm.js**

```js
// resources/js/Composables/useForm.js
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useForm(defaults) {
    const form = useInertiaForm(defaults)

    const hasError = (field) => !!form.errors[field]
    const errorMessage = (field) => form.errors[field] || null

    Object.defineProperty(form, 'hasError',    { value: hasError })
    Object.defineProperty(form, 'errorMessage',{ value: errorMessage })
    Object.defineProperty(form, 'isSubmitting', {
        get: () => form.processing,
        enumerable: true,
    })

    return form
}
```

- [ ] **Step 2 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```

- [ ] **Step 3 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Composables/useForm.js && git commit -m "feat(ui): useForm composable — wrapper Inertia avec hasError/errorMessage/isSubmitting"
```

---

## Task 5 : Dropdown

**Files:**
- Create: `resources/js/Components/UI/Dropdown.vue`

**Interfaces:**
- Consumes: `@headlessui/vue` (Menu, MenuButton, MenuItems, MenuItem)
- Produces:
  - `Dropdown` : slot `#trigger` (bouton déclencheur), slot `#default` (items)
  - `DropdownItem` : prop `href` (string, optionnel), prop `danger` (boolean), slot default, emit `click`

- [ ] **Step 1 : Créer Dropdown.vue**

```vue
<!-- resources/js/Components/UI/Dropdown.vue -->
<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton as="template">
            <slot name="trigger" />
        </MenuButton>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <MenuItems class="absolute right-0 z-10 mt-1 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                <div class="py-1">
                    <slot />
                </div>
            </MenuItems>
        </Transition>
    </Menu>
</template>
```

- [ ] **Step 2 : Créer DropdownItem (dans le même fichier en tant que second composant exporté)**

Créer un fichier séparé `DropdownItem.vue` :

```vue
<!-- resources/js/Components/UI/DropdownItem.vue -->
<script setup>
import { MenuItem } from '@headlessui/vue'

const props = defineProps({
    href:   { type: String,  default: null },
    danger: { type: Boolean, default: false },
})
const emit = defineEmits(['click'])
</script>

<template>
    <MenuItem v-slot="{ active }">
        <component
            :is="href ? 'a' : 'button'"
            :href="href"
            :class="[
                'flex w-full items-center px-4 py-2 text-sm transition-colors',
                danger
                    ? (active ? 'bg-red-50 text-red-700' : 'text-red-600')
                    : (active ? 'bg-gray-50 text-gray-900' : 'text-gray-700'),
            ]"
            @click="emit('click', $event)"
        >
            <slot />
        </component>
    </MenuItem>
</template>
```

- [ ] **Step 3 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```

- [ ] **Step 4 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/Dropdown.vue resources/js/Components/UI/DropdownItem.vue && git commit -m "feat(ui): Dropdown + DropdownItem (Headless UI)"
```

---

## Task 6 : Table (Table, TableHeader, TableRow, TableActions, index.js)

**Files:**
- Create: `resources/js/Components/UI/Table/TableHeader.vue`
- Create: `resources/js/Components/UI/Table/TableRow.vue`
- Create: `resources/js/Components/UI/Table/TableActions.vue`
- Create: `resources/js/Components/UI/Table/Table.vue`
- Create: `resources/js/Components/UI/Table/index.js`

**Interfaces:**
- Consumes: `EmptyState`, `LoadingSpinner`, `Skeleton`, `Dropdown`, `DropdownItem` (Tasks 1 et 5)
- Produces:
  - `columns` shape: `{ key: string, label: string, sortable?: boolean, width?: string, align?: 'left'|'center'|'right', format?: (val, row) => string, component?: Component }`
  - `Table` : props `data` (Array), `columns` (Array), `loading` (boolean), `v-model:selected` (Array), `emptyTitle` (string), `emptyDescription` (string) ; slots `#actions="{ row }"`, `#bulk-actions="{ selected }"`
  - `TableActions` : slot default (DropdownItems)

- [ ] **Step 1 : Créer TableHeader.vue**

```vue
<!-- resources/js/Components/UI/Table/TableHeader.vue -->
<script setup>
const props = defineProps({
    columns:    { type: Array,   required: true },
    selectable: { type: Boolean, default: false },
    allSelected:{ type: Boolean, default: false },
    hasActions: { type: Boolean, default: false },
})
const emit = defineEmits(['toggle-all', 'sort'])
</script>

<template>
    <thead class="bg-gray-50 border-b border-gray-100">
        <tr>
            <th v-if="selectable" class="w-10 px-4 py-3">
                <input
                    type="checkbox"
                    :checked="allSelected"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @change="emit('toggle-all', $event.target.checked)"
                />
            </th>
            <th
                v-for="col in columns"
                :key="col.key"
                :class="[
                    'px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide',
                    col.align === 'right'  ? 'text-right'  : '',
                    col.align === 'center' ? 'text-center' : 'text-left',
                    col.width || '',
                    col.sortable ? 'cursor-pointer select-none hover:text-gray-700' : '',
                ]"
                @click="col.sortable && emit('sort', col.key)"
            >
                {{ col.label }}
            </th>
            <th v-if="hasActions" class="w-12 px-4 py-3" />
        </tr>
    </thead>
</template>
```

- [ ] **Step 2 : Créer TableRow.vue**

```vue
<!-- resources/js/Components/UI/Table/TableRow.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    row:        { type: Object,  required: true },
    columns:    { type: Array,   required: true },
    selectable: { type: Boolean, default: false },
    selected:   { type: Boolean, default: false },
    hasActions: { type: Boolean, default: false },
    index:      { type: Number,  required: true },
})
const emit = defineEmits(['toggle'])

function getNestedValue(obj, key) {
    return key.split('.').reduce((o, k) => o?.[k], obj)
}
</script>

<template>
    <tr :class="['border-b border-gray-50 hover:bg-gray-50/50 transition-colors', selected ? 'bg-blue-50/30' : '']">
        <td v-if="selectable" class="w-10 px-4 py-3">
            <input
                type="checkbox"
                :checked="selected"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @change="emit('toggle', row)"
            />
        </td>
        <td
            v-for="col in columns"
            :key="col.key"
            :class="['px-4 py-3 text-sm text-gray-700', col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : '']"
        >
            <component
                v-if="col.component"
                :is="col.component"
                v-bind="{ [col.key]: getNestedValue(row, col.key) }"
            >
                {{ getNestedValue(row, col.key) }}
            </component>
            <template v-else>
                {{ col.format ? col.format(getNestedValue(row, col.key), row) : getNestedValue(row, col.key) }}
            </template>
        </td>
        <td v-if="hasActions" class="w-12 px-4 py-3 text-right">
            <slot name="actions" :row="row" />
        </td>
    </tr>
</template>
```

- [ ] **Step 3 : Créer TableActions.vue**

```vue
<!-- resources/js/Components/UI/Table/TableActions.vue -->
<script setup>
import { MoreHorizontal } from 'lucide-vue-next'
import Dropdown from '@/Components/UI/Dropdown.vue'
import Button from '@/Components/Button.vue'
</script>

<template>
    <Dropdown>
        <template #trigger>
            <Button variant="ghost" size="sm" aria-label="Actions">
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </template>
        <slot />
    </Dropdown>
</template>
```

- [ ] **Step 4 : Créer Table.vue**

```vue
<!-- resources/js/Components/UI/Table/Table.vue -->
<script setup>
import { computed } from 'vue'
import TableHeader from './TableHeader.vue'
import TableRow from './TableRow.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import Skeleton from '@/Components/UI/Skeleton.vue'

const props = defineProps({
    data:             { type: Array,   default: () => [] },
    columns:          { type: Array,   required: true },
    loading:          { type: Boolean, default: false },
    selected:         { type: Array,   default: () => [] },
    emptyTitle:       { type: String,  default: 'Aucun résultat' },
    emptyDescription: { type: String,  default: '' },
    emptyIcon:        { type: String,  default: 'inbox' },
})

const emit = defineEmits(['update:selected'])

const selectable  = computed(() => props.selected !== undefined)
const hasActions  = computed(() => !!slots.actions)
const allSelected = computed(() => props.data.length > 0 && props.selected.length === props.data.length)

const slots = defineSlots()

function toggleAll(checked) {
    emit('update:selected', checked ? [...props.data] : [])
}

function toggleRow(row) {
    const idx = props.selected.findIndex(r => r === row)
    const next = [...props.selected]
    idx === -1 ? next.push(row) : next.splice(idx, 1)
    emit('update:selected', next)
}

function isSelected(row) {
    return props.selected.includes(row)
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <!-- Bulk actions bar -->
        <div
            v-if="selectable && selected.length > 0 && $slots['bulk-actions']"
            class="flex items-center gap-3 px-4 py-3 bg-blue-50 border-b border-blue-100"
        >
            <span class="text-sm font-medium text-blue-700">{{ selected.length }} sélectionné(s)</span>
            <slot name="bulk-actions" :selected="selected" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <TableHeader
                    :columns="columns"
                    :selectable="selectable"
                    :all-selected="allSelected"
                    :has-actions="hasActions"
                    @toggle-all="toggleAll"
                />
                <tbody>
                    <!-- Loading skeletons -->
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="i" class="border-b border-gray-50">
                            <td v-if="selectable" class="px-4 py-3"><Skeleton class="h-4 w-4 rounded" /></td>
                            <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                                <Skeleton class="h-4 rounded" :class="col.width || 'w-full'" />
                            </td>
                            <td v-if="hasActions" class="px-4 py-3"><Skeleton class="h-6 w-6 rounded ml-auto" /></td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr v-else-if="data.length === 0">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + (hasActions ? 1 : 0)">
                            <EmptyState :icon="emptyIcon" :title="emptyTitle" :description="emptyDescription">
                                <slot name="empty" />
                            </EmptyState>
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <TableRow
                        v-else
                        v-for="(row, i) in data"
                        :key="row.id ?? i"
                        :row="row"
                        :columns="columns"
                        :selectable="selectable"
                        :selected="isSelected(row)"
                        :has-actions="hasActions"
                        :index="i"
                        @toggle="toggleRow"
                    >
                        <template v-if="$slots.actions" #actions="{ row: r }">
                            <slot name="actions" :row="r" />
                        </template>
                    </TableRow>
                </tbody>
            </table>
        </div>
    </div>
</template>
```

- [ ] **Step 5 : Créer Table/index.js**

```js
// resources/js/Components/UI/Table/index.js
export { default as Table }        from './Table.vue'
export { default as TableHeader }  from './TableHeader.vue'
export { default as TableRow }     from './TableRow.vue'
export { default as TableActions } from './TableActions.vue'
```

- [ ] **Step 6 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -15
```
Résultat attendu : `✓ built in` sans erreur.

- [ ] **Step 7 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/Table/ && git commit -m "feat(ui): Table system — Table, TableHeader, TableRow, TableActions"
```

---

## Task 7 : Pagination + SearchInput

**Files:**
- Create: `resources/js/Components/UI/Pagination.vue`
- Create: `resources/js/Components/UI/SearchInput.vue`

**Interfaces:**
- Consumes: `router` de `@inertiajs/vue3`, `route()` de Ziggy
- Produces:
  - `Pagination` : props `meta` (Object: `{current_page, last_page, per_page, total, from, to}`), `links` (Array), emit `change(page)`
  - `SearchInput` : props `modelValue` (string), `placeholder` (string), emit `update:modelValue`

- [ ] **Step 1 : Créer Pagination.vue**

```vue
<!-- resources/js/Components/UI/Pagination.vue -->
<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    meta:  { type: Object, required: true },
    links: { type: Array,  default: () => [] },
})
const emit = defineEmits(['change'])

const pages = computed(() => {
    const total   = props.meta.last_page
    const current = props.meta.current_page
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

    const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
    const sorted = [...pages].sort((a, b) => a - b)

    const withEllipsis = []
    for (let i = 0; i < sorted.length; i++) {
        withEllipsis.push(sorted[i])
        if (i < sorted.length - 1 && sorted[i + 1] - sorted[i] > 1) {
            withEllipsis.push('...')
        }
    }
    return withEllipsis
})
</script>

<template>
    <div class="flex items-center justify-between px-1 py-3">
        <p class="text-sm text-gray-500">
            Affichage <span class="font-medium">{{ meta.from }}</span>–<span class="font-medium">{{ meta.to }}</span>
            sur <span class="font-medium">{{ meta.total }}</span> résultats
        </p>

        <div class="flex items-center gap-1">
            <button
                class="p-1.5 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                :disabled="meta.current_page === 1"
                @click="emit('change', meta.current_page - 1)"
                aria-label="Page précédente"
            >
                <ChevronLeft class="w-4 h-4" />
            </button>

            <template v-for="page in pages" :key="page">
                <span v-if="page === '...'" class="px-2 text-gray-400 text-sm">…</span>
                <button
                    v-else
                    :class="[
                        'min-w-[32px] h-8 px-2 rounded text-sm font-medium transition-colors',
                        page === meta.current_page
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100',
                    ]"
                    @click="emit('change', page)"
                >
                    {{ page }}
                </button>
            </template>

            <button
                class="p-1.5 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                :disabled="meta.current_page === meta.last_page"
                @click="emit('change', meta.current_page + 1)"
                aria-label="Page suivante"
            >
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>
```

- [ ] **Step 2 : Créer SearchInput.vue**

```vue
<!-- resources/js/Components/UI/SearchInput.vue -->
<script setup>
import { Search, X } from 'lucide-vue-next'

const props = defineProps({
    modelValue:  { type: String, default: '' },
    placeholder: { type: String, default: 'Rechercher...' },
})
const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
        <input
            type="text"
            :value="modelValue"
            :placeholder="placeholder"
            class="w-full pl-9 pr-8 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @input="emit('update:modelValue', $event.target.value)"
        />
        <button
            v-if="modelValue"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            @click="emit('update:modelValue', '')"
            aria-label="Effacer"
        >
            <X class="w-4 h-4" />
        </button>
    </div>
</template>
```

- [ ] **Step 3 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```

- [ ] **Step 4 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/Pagination.vue resources/js/Components/UI/SearchInput.vue && git commit -m "feat(ui): Pagination + SearchInput"
```

---

## Task 8 : FilterBar + FilterSelect + FilterDateRange

**Files:**
- Create: `resources/js/Components/UI/FilterSelect.vue`
- Create: `resources/js/Components/UI/FilterDateRange.vue`
- Create: `resources/js/Components/UI/FilterBar.vue`

**Interfaces:**
- Consumes: `Badge` (Task 1)
- Produces:
  - `FilterSelect` : props `modelValue` (any), `label` (string), `options` (Array `{value, label}`), emit `update:modelValue`
  - `FilterDateRange` : props `modelValue` (Object `{from, to}`), `label` (string), emit `update:modelValue`
  - `FilterBar` : prop `modelValue` (Object), emit `update:modelValue`, emit `reset` ; slot default

- [ ] **Step 1 : Créer FilterSelect.vue**

```vue
<!-- resources/js/Components/UI/FilterSelect.vue -->
<script setup>
const props = defineProps({
    modelValue: { default: '' },
    label:      { type: String, required: true },
    options:    { type: Array,  default: () => [] },
})
const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-500">{{ label }}</label>
        <select
            :value="modelValue"
            class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="emit('update:modelValue', $event.target.value)"
        >
            <option value="">Tous</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
    </div>
</template>
```

- [ ] **Step 2 : Créer FilterDateRange.vue**

```vue
<!-- resources/js/Components/UI/FilterDateRange.vue -->
<script setup>
const props = defineProps({
    modelValue: { type: Object, default: () => ({ from: '', to: '' }) },
    label:      { type: String, required: true },
})
const emit = defineEmits(['update:modelValue'])

const update = (key, val) => emit('update:modelValue', { ...props.modelValue, [key]: val })
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-500">{{ label }}</label>
        <div class="flex items-center gap-2">
            <input
                type="date"
                :value="modelValue.from"
                class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="update('from', $event.target.value)"
            />
            <span class="text-gray-400 text-sm">→</span>
            <input
                type="date"
                :value="modelValue.to"
                class="text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="update('to', $event.target.value)"
            />
        </div>
    </div>
</template>
```

- [ ] **Step 3 : Créer FilterBar.vue**

```vue
<!-- resources/js/Components/UI/FilterBar.vue -->
<script setup>
import { computed } from 'vue'
import Badge from './Badge.vue'

const props = defineProps({
    modelValue: { type: Object, default: () => ({}) },
    defaults:   { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:modelValue', 'reset'])

const activeCount = computed(() => {
    return Object.keys(props.modelValue).filter(k => {
        const v = props.modelValue[k]
        const d = props.defaults[k]
        if (typeof v === 'object' && v !== null) return v.from || v.to
        return v !== '' && v !== null && v !== undefined && v !== d
    }).length
})
</script>

<template>
    <div class="flex flex-wrap items-end gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
        <slot />

        <div class="flex items-center gap-2 ml-auto">
            <Badge v-if="activeCount > 0" variant="info">{{ activeCount }} filtre{{ activeCount > 1 ? 's' : '' }}</Badge>
            <button
                v-if="activeCount > 0"
                class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                @click="emit('reset')"
            >
                Réinitialiser
            </button>
        </div>
    </div>
</template>
```

- [ ] **Step 4 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```

- [ ] **Step 5 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/FilterBar.vue resources/js/Components/UI/FilterSelect.vue resources/js/Components/UI/FilterDateRange.vue && git commit -m "feat(ui): FilterBar, FilterSelect, FilterDateRange"
```

---

## Task 9 : Drawer + Breadcrumb

**Files:**
- Create: `resources/js/Components/UI/Drawer.vue`
- Create: `resources/js/Components/UI/Breadcrumb.vue`

**Interfaces:**
- Consumes: `@headlessui/vue` (Dialog, DialogPanel, TransitionRoot, TransitionChild)
- Produces:
  - `Drawer` : `v-model` (boolean), `title` (string), `side` ('right'|'left'), `size` ('sm'|'md'|'lg'|'full'), slots `#default` + `#footer`
  - `Breadcrumb` : prop `items` (Array `{label, href?}`), dernier item = courant (non cliquable)

- [ ] **Step 1 : Créer Drawer.vue**

```vue
<!-- resources/js/Components/UI/Drawer.vue -->
<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    title:      { type: String,  default: '' },
    side:       { type: String,  default: 'right', validator: (v) => ['right','left'].includes(v) },
    size:       { type: String,  default: 'md',    validator: (v) => ['sm','md','lg','full'].includes(v) },
})
const emit = defineEmits(['update:modelValue'])
const close = () => emit('update:modelValue', false)

const panelSize = computed(() => ({
    sm:   'max-w-sm',
    md:   'max-w-md',
    lg:   'max-w-lg',
    full: 'max-w-full',
}[props.size]))

const enterFrom = computed(() => props.side === 'right' ? 'translate-x-full' : '-translate-x-full')
</script>

<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild
                enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150"  leave-from="opacity-100" leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div :class="['absolute inset-y-0 flex max-w-full', side === 'right' ? 'right-0' : 'left-0']">
                    <TransitionChild
                        enter="transform transition ease-out duration-200"
                        :enter-from="enterFrom" enter-to="translate-x-0"
                        leave="transform transition ease-in duration-150"
                        leave-from="translate-x-0" :leave-to="enterFrom"
                    >
                        <DialogPanel :class="['flex flex-col h-full bg-white shadow-xl', panelSize, 'w-screen']">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
                                <button class="text-gray-400 hover:text-gray-600 transition-colors" @click="close" aria-label="Fermer">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="flex-1 overflow-y-auto px-6 py-4">
                                <slot />
                            </div>
                            <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100">
                                <slot name="footer" />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
```

- [ ] **Step 2 : Créer Breadcrumb.vue**

```vue
<!-- resources/js/Components/UI/Breadcrumb.vue -->
<script setup>
import { Link } from '@inertiajs/vue3'
import { ChevronRight } from 'lucide-vue-next'

defineProps({
    items: { type: Array, required: true },
})
</script>

<template>
    <nav aria-label="Fil d'Ariane">
        <ol class="flex items-center gap-1 text-sm">
            <li v-for="(item, i) in items" :key="i" class="flex items-center gap-1">
                <ChevronRight v-if="i > 0" class="w-3.5 h-3.5 text-gray-300 shrink-0" />
                <Link
                    v-if="item.href && i < items.length - 1"
                    :href="item.href"
                    class="text-gray-500 hover:text-gray-700 transition-colors"
                >
                    {{ item.label }}
                </Link>
                <span v-else :class="i === items.length - 1 ? 'text-gray-900 font-medium' : 'text-gray-500'">
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
```

- [ ] **Step 3 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```

- [ ] **Step 4 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Components/UI/Drawer.vue resources/js/Components/UI/Breadcrumb.vue && git commit -m "feat(ui): Drawer (Headless UI) + Breadcrumb"
```

---

## Task 10 : Intégration dans les layouts

**Files:**
- Modify: `resources/js/Layouts/FrontLayout.vue`

**Interfaces:**
- Consumes: `ToastContainer` (Task 2), `ConfirmModal` (Task 3)
- Produces: `ToastContainer` et `ConfirmModal` montés une fois pour toute l'application

- [ ] **Step 1 : Ajouter les imports dans FrontLayout.vue**

Ouvrir `resources/js/Layouts/FrontLayout.vue`. Dans le bloc `<script setup>`, ajouter après les imports existants :

```js
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
```

- [ ] **Step 2 : Monter les composants dans le template**

Dans le `<template>` de `FrontLayout.vue`, ajouter juste avant la balise fermante `</template>` :

```vue
<ToastContainer />
<ConfirmModal />
```

- [ ] **Step 3 : Vérifier le build**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1 | tail -10
```
Résultat attendu : `✓ built in` sans erreur.

- [ ] **Step 4 : Commit**

```bash
cd c:/laragon/www/chamse && git add resources/js/Layouts/FrontLayout.vue && git commit -m "feat(ui): monter ToastContainer + ConfirmModal dans FrontLayout"
```

---

## Task 11 : Vérification finale

- [ ] **Step 1 : Build de production propre**

```bash
cd c:/laragon/www/chamse && npm run build 2>&1
```
Résultat attendu : `✓ built in` sans warning ni erreur.

- [ ] **Step 2 : Vérifier la structure des fichiers créés**

```bash
find c:/laragon/www/chamse/resources/js/Components/UI -type f | sort
find c:/laragon/www/chamse/resources/js/Composables -type f | sort
find c:/laragon/www/chamse/resources/js/Stores -type f | sort
```

Résultat attendu :
```
Components/UI/Badge.vue
Components/UI/Breadcrumb.vue
Components/UI/ConfirmModal.vue
Components/UI/Drawer.vue
Components/UI/Dropdown.vue
Components/UI/DropdownItem.vue
Components/UI/EmptyState.vue
Components/UI/FilterBar.vue
Components/UI/FilterDateRange.vue
Components/UI/FilterSelect.vue
Components/UI/LoadingSpinner.vue
Components/UI/Modal.vue
Components/UI/Pagination.vue
Components/UI/SearchInput.vue
Components/UI/Skeleton.vue
Components/UI/Table/Table.vue
Components/UI/Table/TableActions.vue
Components/UI/Table/TableHeader.vue
Components/UI/Table/TableRow.vue
Components/UI/Table/index.js
Components/UI/Toast.vue
Components/UI/ToastContainer.vue
Composables/useConfirm.js
Composables/useForm.js
Composables/useHelpers.js
Composables/useToast.js
Stores/confirm.js
Stores/toast.js
```

- [ ] **Step 3 : Commit final**

```bash
cd c:/laragon/www/chamse && git add -A && git status
```
Vérifier qu'il n'y a pas de fichiers non voulus. Puis :

```bash
cd c:/laragon/www/chamse && git commit -m "feat(ui): bibliothèque composants Vue complète — fondation migration Inertia" --allow-empty
```
