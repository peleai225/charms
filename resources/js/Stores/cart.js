import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const count = ref(0);
    const productIds = ref(new Set());
    const variantIds = ref(new Set());
    const isLoading = ref(false);

    const isEmpty = computed(() => count.value === 0);

    function hasProduct(productId) {
        return productIds.value.has(Number(productId));
    }

    function hasVariant(variantId) {
        return variantId ? variantIds.value.has(Number(variantId)) : false;
    }

    async function sync() {
        if (isLoading.value) return;
        isLoading.value = true;
        try {
            const response = await fetch('/panier/drawer', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) {
                const data = await response.json();
                count.value = data.count ?? count.value;
                productIds.value = new Set((data.items || []).map(i => Number(i.product_id)));
                variantIds.value = new Set((data.items || []).filter(i => i.variant_id).map(i => Number(i.variant_id)));
            }
        } catch (e) {
            console.error('Cart sync error:', e);
        } finally {
            isLoading.value = false;
        }
    }

    function increment() { count.value++; }
    function decrement() { if (count.value > 0) count.value--; }
    function setCount(newCount) { count.value = newCount; }

    function addProductId(productId, variantId) {
        productIds.value = new Set([...productIds.value, Number(productId)]);
        if (variantId) variantIds.value = new Set([...variantIds.value, Number(variantId)]);
        count.value++;
    }

    return {
        count, productIds, variantIds, isLoading, isEmpty,
        hasProduct, hasVariant, sync, increment, decrement, setCount, addProductId,
    };
});
