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
