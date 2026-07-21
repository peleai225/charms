import './bootstrap';

// ─── Alpine + Livewire ─────────────────────────────────────────────────────────
// Livewire (inject_assets=true) charge son propre bundle qui embarque Alpine.
// Il dispatch 'alpine:init' juste avant Alpine.start() — c'est le seul bon
// endroit pour enregistrer plugins/stores/composants sans créer une 2e instance.
// On N'importe PAS alpinejs depuis npm : Livewire en est le seul propriétaire.
import collapse from '@alpinejs/collapse';
import focus    from '@alpinejs/focus';

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // ── Plugins ──────────────────────────────────────────────────────────────
    Alpine.plugin(collapse);
    Alpine.plugin(focus);

    // ── Store : notifications ─────────────────────────────────────────────────
    Alpine.store('notify', {
        notifications: [],
        add(message, type = 'info', duration = 5000) {
            const id = Date.now() + Math.random();
            this.notifications.push({ id, message, type });
            if (duration > 0) setTimeout(() => this.remove(id), duration);
        },
        remove(id)               { this.notifications = this.notifications.filter(n => n.id !== id); },
        success(msg, d = 5000)   { this.add(msg, 'success', d); },
        error(msg, d = 6000)     { this.add(msg, 'error', d); },
        warning(msg, d = 5000)   { this.add(msg, 'warning', d); },
        info(msg, d = 5000)      { this.add(msg, 'info', d); },
    });

    // ── Store : panier (compteur + sync) ──────────────────────────────────────
    Alpine.store('cart', {
        count: parseInt(document.querySelector('meta[name="cart-count"]')?.content ?? '0'),
        isLoading: false,
        async sync() {
            if (this.isLoading) return;
            this.isLoading = true;
            try {
                const res = await fetch('/api/cart', {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.count = data.items_count ?? data.count ?? this.count;
                }
            } catch (e) { console.error('Cart sync error:', e); }
            finally { this.isLoading = false; }
        },
    });

    // ── Composant : notifications toast ──────────────────────────────────────
    Alpine.data('notification', () => ({
        get notifications() { return window.Alpine?.store('notify')?.notifications ?? []; },
        remove(id)          { window.Alpine?.store('notify')?.remove(id); },
    }));

    // ── Composant : recherche suggestion ─────────────────────────────────────
    Alpine.data('searchSuggest', () => ({
        query: '', results: [], showResults: false, loading: false,
        async search() {
            if (this.query.length < 2) { this.results = []; this.showResults = false; return; }
            this.loading = true;
            try {
                const res = await fetch(`/api/search/suggest?q=${encodeURIComponent(this.query)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.results    = data.results ?? data ?? [];
                    this.showResults = this.results.length > 0;
                }
            } catch (e) { this.results = []; }
            finally { this.loading = false; }
        },
    }));

    // ── Composant : dropdown ──────────────────────────────────────────────────
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close()  { this.open = false; },
    }));

    // ── Composant : modal ─────────────────────────────────────────────────────
    Alpine.data('modal', () => ({
        show: false,
        open()  { this.show = true;  document.body.style.overflow = 'hidden'; },
        close() { this.show = false; document.body.style.overflow = ''; },
    }));

    // ── Composant : tabs ──────────────────────────────────────────────────────
    Alpine.data('tabs', (defaultTab = 0) => ({
        activeTab: defaultTab,
        setTab(i)  { this.activeTab = i; },
        isActive(i){ return this.activeTab === i; },
    }));

    // ── Composant : sidebar ───────────────────────────────────────────────────
    Alpine.data('sidebar', () => ({
        expanded: true, mobileOpen: false,
        toggle()       { this.expanded    = !this.expanded; },
        toggleMobile() { this.mobileOpen  = !this.mobileOpen; },
        closeMobile()  { this.mobileOpen  = false; },
    }));
});

// ── Listeners globaux (hors Alpine) ──────────────────────────────────────────
window.addEventListener('cart-count-updated', (e) => {
    if (window.Alpine?.store('cart')) window.Alpine.store('cart').count = e.detail.count ?? 0;
});
window.addEventListener('show-notification', (e) => {
    window.Alpine?.store('notify')?.add(e.detail.message, e.detail.type ?? 'info');
});
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') window.Alpine?.store('cart')?.sync();
});

// ── Utilitaire localStorage ───────────────────────────────────────────────────
window.safeLocalStorage = {
    getItem(k)    { try { return localStorage.getItem(k); }    catch { return null; } },
    setItem(k, v) { try { localStorage.setItem(k, v); }        catch {} },
    removeItem(k) { try { localStorage.removeItem(k); }        catch {} },
};

// ── Utilitaires formatage ─────────────────────────────────────────────────────
window.formatPrice = (price) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(price);

window.formatDate = (date) =>
    new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(date));

// ── Interception formulaires AJAX ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form');
        if (!form || form.classList.contains('no-ajax') || (!form.classList.contains('ajax-form') && !form.dataset.ajax)) return;
        e.preventDefault();
        const btn = form.querySelector('[type="submit"]');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<svg class="animate-spin inline h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> En cours…'; }
        try {
            const res  = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            });
            const data = await res.json().catch(() => ({}));
            const notify = window.Alpine?.store('notify');
            if (res.ok && data.redirect) { if (notify && data.message) notify.add(data.message, data.type ?? 'success'); window.location.href = data.redirect; return; }
            if (!res.ok) { const msg = data.message || data.errors?.[Object.keys(data.errors ?? {})[0]]?.[0] || 'Une erreur est survenue.'; notify?.error(msg); }
        } catch (err) { console.error(err); window.Alpine?.store('notify')?.error('Erreur de connexion.'); }
        finally { if (btn) { btn.disabled = false; btn.innerHTML = orig; } }
    });
});

// ── PWA Install ───────────────────────────────────────────────────────────────
let _pwaPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); _pwaPrompt = e; window.dispatchEvent(new CustomEvent('pwa-installable')); });
window.pwaInstall = function() {
    return {
        showBanner: false, platform: 'android',
        init() {
            if (window.matchMedia('(display-mode: standalone)').matches || navigator.standalone) return;
            const dismissed = localStorage.getItem('pwa-dismiss');
            if (dismissed && (Date.now() - parseInt(dismissed)) < 3 * 86400000) return;
            const ua = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (isIOS && /Safari/.test(ua) && !/Chrome/.test(ua)) {
                this.platform = 'ios'; setTimeout(() => { this.showBanner = true; }, 3000);
            } else {
                this.platform = 'android';
                if (_pwaPrompt) setTimeout(() => { this.showBanner = true; }, 2000);
                window.addEventListener('pwa-installable', () => setTimeout(() => { this.showBanner = true; }, 2000));
            }
        },
        async installApp() {
            if (!_pwaPrompt) return;
            _pwaPrompt.prompt();
            const { outcome } = await _pwaPrompt.userChoice;
            if (outcome === 'accepted') { this.showBanner = false; localStorage.setItem('pwa-installed', '1'); }
            _pwaPrompt = null;
        },
        dismiss() { this.showBanner = false; localStorage.setItem('pwa-dismiss', Date.now().toString()); },
    };
};
