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
