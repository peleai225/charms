import './bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

// Plugins Alpine
Alpine.plugin(collapse);
Alpine.plugin(focus);

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

Alpine.data('notification', () => ({
    notifications: [],
    
    add(message, type = 'info', duration = 5000) {
        const id = Date.now();
        this.notifications.push({ id, message, type });
        
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }
    },
    
    remove(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },
    
    success(message) {
        this.add(message, 'success');
    },
    
    error(message) {
        this.add(message, 'error');
    },
    
    warning(message) {
        this.add(message, 'warning');
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
