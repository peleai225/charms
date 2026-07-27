import { useConfirmStore } from '@/Stores/confirm'

export function useConfirm() {
    const store = useConfirmStore()
    return (opts) => store.open(opts)
}
