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
