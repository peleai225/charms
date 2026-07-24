import collapse from '@alpinejs/collapse';
import focus    from '@alpinejs/focus';

// Livewire 4 bundle Alpine et le démarre via DOMContentLoaded.
// admin-alpine.js est un ES module (defer) — s'exécute après DOMContentLoaded,
// donc window.Alpine est déjà défini par Livewire quand ce code tourne.
function _registerAdminAlpine() {
    const Alpine = window.Alpine;
    if (!Alpine) return;

    // Éviter de re-enregistrer les plugins si déjà fait
    if (!Alpine._adminPluginsLoaded) {
        Alpine.plugin(collapse);
        Alpine.plugin(focus);
        Alpine._adminPluginsLoaded = true;
    }

    if (!Alpine.store('notify')) {
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
    }

    Alpine.data('notification', () => ({
        get notifications() { return Alpine.store('notify')?.notifications ?? []; },
        remove(id)          { Alpine.store('notify')?.remove(id); },
    }));
}

// Alpine est déjà démarré (ES module s'exécute après DOMContentLoaded)
_registerAdminAlpine();

window.addEventListener('show-notification', (e) => {
    window.Alpine?.store('notify')?.add(e.detail.message, e.detail.type ?? 'info');
});

window.showNotification = function(message, type = 'info') {
    window.Alpine?.store('notify')?.add(message, type);
};
