<script setup>
import { computed } from 'vue'
import { useHelpers } from '@/Composables/useHelpers'

// « À traiter » : ce qui attend la gérante aujourd'hui
const props = defineProps({
    todo: { type: Object, required: true },
})

const { formatPrice } = useHelpers()

const cards = computed(() => [
    { label: 'À confirmer', value: props.todo.to_confirm, hint: 'commandes en attente', href: route('admin.orders.index', { status: 'pending' }), warn: props.todo.to_confirm > 0 },
    { label: 'À confier à un livreur', value: props.todo.to_dispatch, hint: 'confirmées ou en préparation', href: route('admin.orders.index', { status: 'processing' }), warn: props.todo.to_dispatch > 0 },
    { label: 'Chez les livreurs', value: props.todo.on_route, hint: `${formatPrice(props.todo.on_route_to_collect)} à encaisser`, href: route('admin.couriers.index') },
    { label: 'À relivrer', value: props.todo.to_redeliver, hint: 'livraisons ratées', href: route('admin.orders.index', { status: 'processing' }), warn: props.todo.to_redeliver > 0 },
    {
        label: 'Soldes livreurs',
        value: formatPrice(Math.abs(props.todo.couriers_balance)),
        hint: props.todo.couriers_balance > 0 ? 'à récupérer auprès des livreurs' : props.todo.couriers_balance < 0 ? 'à payer aux livreurs' : 'tout est réglé',
        href: route('admin.couriers.index'),
        warn: props.todo.couriers_balance !== 0,
    },
])
</script>

<template>
    <section aria-labelledby="todo-title" class="space-y-2">
        <h2 id="todo-title" class="text-sm font-semibold text-gray-900">À traiter</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
            <a v-for="c in cards" :key="c.label" :href="c.href"
                class="block rounded-xl border bg-white px-4 py-3 shadow-sm hover:border-blue-300 transition-colors min-h-[44px]"
                :class="c.warn ? 'border-amber-200' : 'border-gray-200'">
                <p class="text-xs font-medium text-gray-500">{{ c.label }}</p>
                <p class="text-xl font-bold tabular-nums mt-1" :class="c.warn ? 'text-amber-700' : 'text-gray-900'">{{ c.value }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ c.hint }}</p>
            </a>
        </div>
    </section>
</template>
