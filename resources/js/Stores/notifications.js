import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([]);

    function add(message, type = 'info', duration = 5000) {
        const id = Date.now() + Math.random();
        notifications.value.push({ id, message, type });

        if (duration > 0) {
            setTimeout(() => remove(id), duration);
        }
    }

    function remove(id) {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }

    function success(message, duration = 5000) {
        add(message, 'success', duration);
    }

    function error(message, duration = 6000) {
        add(message, 'error', duration);
    }

    function warning(message, duration = 5000) {
        add(message, 'warning', duration);
    }

    function info(message, duration = 5000) {
        add(message, 'info', duration);
    }

    return {
        notifications,
        add,
        remove,
        success,
        error,
        warning,
        info,
    };
});
