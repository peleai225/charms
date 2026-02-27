import './bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

// Plugins Alpine
Alpine.plugin(collapse);
Alpine.plugin(focus);

// Store global pour les notifications (toasts sans rechargement)
Alpine.store('notify', {
    notifications: [],
    add(message, type = 'info', duration = 5000) {
        const id = Date.now() + Math.random();
        this.notifications.push({ id, message, type });
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }
    },
    remove(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },
    success(message, duration = 5000) {
        this.add(message, 'success', duration);
    },
    error(message, duration = 6000) {
        this.add(message, 'error', duration);
    },
    warning(message, duration = 5000) {
        this.add(message, 'warning', duration);
    },
    info(message, duration = 5000) {
        this.add(message, 'info', duration);
    }
});

// Composants Alpine globaux
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    show: false,
    open() {
        this.show = true;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.show = false;
        document.body.style.overflow = '';
    }
}));

Alpine.data('tabs', (defaultTab = 0) => ({
    activeTab: defaultTab,
    setTab(index) {
        this.activeTab = index;
    },
    isActive(index) {
        return this.activeTab === index;
    }
}));

Alpine.data('sidebar', () => ({
    expanded: true,
    mobileOpen: false,
    toggle() {
        this.expanded = !this.expanded;
    },
    toggleMobile() {
        this.mobileOpen = !this.mobileOpen;
    },
    closeMobile() {
        this.mobileOpen = false;
    }
}));

// Fonction utilitaire pour gérer localStorage de manière sécurisée
const safeLocalStorage = {
    getItem(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            console.warn('localStorage non disponible:', e.message);
            return null;
        }
    },
    setItem(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            console.warn('localStorage non disponible:', e.message);
        }
    },
    removeItem(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.warn('localStorage non disponible:', e.message);
        }
    },
    isAvailable() {
        try {
            const test = '__localStorage_test__';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch (e) {
            return false;
        }
    }
};

Alpine.data('cart', () => ({
    items: [],
    open: false,
    
    init() {
        // Charger le panier depuis localStorage (si disponible)
        if (safeLocalStorage.isAvailable()) {
            const saved = safeLocalStorage.getItem('cart');
            if (saved) {
                try {
                    this.items = JSON.parse(saved);
                } catch (e) {
                    console.warn('Erreur lors du parsing du panier:', e);
                    this.items = [];
                }
            }
        }
    },
    
    addItem(product, quantity = 1) {
        const existing = this.items.find(item => item.id === product.id);
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({ ...product, quantity });
        }
        this.save();
    },
    
    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
    },
    
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, quantity);
            this.save();
        }
    },
    
    get total() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },
    
    get count() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    },
    
    save() {
        if (safeLocalStorage.isAvailable()) {
            safeLocalStorage.setItem('cart', JSON.stringify(this.items));
        }
    },
    
    clear() {
        this.items = [];
        this.save();
    }
}));

// Composant notification : affiche les toasts du store global
Alpine.data('notification', () => ({
    get notifications() {
        return Alpine.store('notify')?.notifications ?? [];
    },
    remove(id) {
        Alpine.store('notify')?.remove(id);
    }
}));

// Fonction utilitaire globale pour localStorage (disponible dans les templates)
window.safeLocalStorage = {
    getItem(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    },
    setItem(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            // Ignorer silencieusement si localStorage n'est pas disponible
        }
    },
    removeItem(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            // Ignorer silencieusement
        }
    }
};

// Initialiser Alpine
window.Alpine = Alpine;
Alpine.start();

// Utilitaires globaux
window.formatPrice = (price, currency = '€') => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

window.formatDate = (date) => {
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(new Date(date));
};

window.formatDateTime = (date) => {
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
};

// Interception des formulaires pour soumission AJAX (sans rechargement)
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form');
        if (!form || form.classList.contains('no-ajax') || (!form.classList.contains('ajax-form') && !form.dataset.ajax)) return;

        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>En cours...</span>';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(form.querySelector('[name="_token"]')?.value && {
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                    })
                }
            });

            const data = await response.json().catch(() => ({}));
            const notify = window.Alpine?.store('notify');

            if (response.ok && data.redirect) {
                if (notify && data.message) notify.add(data.message, data.type || 'success');
                window.location.href = data.redirect;
                return;
            }

            if (!response.ok) {
                const msg = data.message || data.errors?.[Object.keys(data.errors || {})[0]]?.[0] || 'Une erreur est survenue.';
                if (notify) notify.error(msg);
            }
        } catch (err) {
            console.error(err);
            if (window.Alpine?.store('notify')) Alpine.store('notify').error('Erreur de connexion.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    });
});
