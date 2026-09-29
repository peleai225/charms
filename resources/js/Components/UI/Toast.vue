<script setup>
import { computed } from 'vue'
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next'
import { useToastStore } from '@/Stores/toast'

const props = defineProps({
    toast: { type: Object, required: true },
})

const store = useToastStore()

const config = computed(() => ({
    success: { icon: CheckCircle,   bg: 'bg-white border-success-200',  iconClass: 'text-success-500'  },
    error:   { icon: AlertCircle,   bg: 'bg-white border-danger-200',   iconClass: 'text-danger-500'   },
    warning: { icon: AlertTriangle, bg: 'bg-white border-warning-200',  iconClass: 'text-warning-500'  },
    info:    { icon: Info,          bg: 'bg-white border-primary-200',  iconClass: 'text-primary-500'  },
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
                class="mt-1 text-xs font-medium text-primary-600 hover:text-primary-700"
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
