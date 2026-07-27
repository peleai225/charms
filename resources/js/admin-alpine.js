import collapse from '@alpinejs/collapse';
import focus    from '@alpinejs/focus';

function _registerAdminAlpine(Alpine) {
    if (!Alpine || Alpine._adminPluginsLoaded) return;
    Alpine._adminPluginsLoaded = true;

    Alpine.plugin(collapse);
    Alpine.plugin(focus);

    Alpine.store('notify', {
        notifications: [],
        add(message, type = 'info', duration = 5000) {
            const id = Date.now() + Math.random();
            this.notifications.push({ id, message, type });
            if (duration > 0) setTimeout(() => this.remove(id), duration);
        },
        remove(id)             { this.notifications = this.notifications.filter(n => n.id !== id); },
        success(msg, d = 5000) { this.add(msg, 'success', d); },
        error(msg, d = 6000)   { this.add(msg, 'error', d); },
        warning(msg, d = 5000) { this.add(msg, 'warning', d); },
        info(msg, d = 5000)    { this.add(msg, 'info', d); },
    });

    Alpine.data('notification', () => ({
        get notifications() { return Alpine.store('notify')?.notifications ?? []; },
        remove(id)          { Alpine.store('notify')?.remove(id); },
    }));
}

// Livewire 4 expose Alpine via alpine:init AVANT Alpine.start()
document.addEventListener('alpine:init', () => {
    _registerAdminAlpine(window.Alpine);
});

// Fallback si Alpine est déjà démarré
if (window.Alpine) {
    _registerAdminAlpine(window.Alpine);
}

window.addEventListener('show-notification', (e) => {
    window.Alpine?.store('notify')?.add(e.detail.message, e.detail.type ?? 'info');
});

window.showNotification = function(message, type = 'info') {
    window.Alpine?.store('notify')?.add(message, type);
};
