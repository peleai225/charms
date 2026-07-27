import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useUserStore = defineStore('user', () => {
    const user = ref(null);
    const isAuthenticated = computed(() => user.value !== null);
    const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'manager');

    function setUser(userData) {
        user.value = userData;
    }

    function clearUser() {
        user.value = null;
    }

    return {
        user,
        isAuthenticated,
        isAdmin,
        setUser,
        clearUser,
    };
});
