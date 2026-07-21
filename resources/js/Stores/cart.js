import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const count = ref(0);
    const isLoading = ref(false);

    const isEmpty = computed(() => count.value === 0);

    async function sync() {
        if (isLoading.value) return;
        isLoading.value = true;

        try {
            const response = await fetch('/panier/count', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                count.value = data.count ?? data.items_count ?? count.value;
            }
        } catch (error) {
            console.error('Cart sync error:', error);
        } finally {
            isLoading.value = false;
        }
    }

    function increment() {
        count.value++;
    }

    function decrement() {
        if (count.value > 0) count.value--;
    }

    function setCount(newCount) {
        count.value = newCount;
    }

    return {
        count,
        isLoading,
        isEmpty,
        sync,
        increment,
        decrement,
        setCount,
    };
});
