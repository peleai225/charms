/**
 * Notifications temps réel admin
 * Polling /api/admin/poll-stats toutes les 15s
 * Son + voix "Nouvelle commande" + notification navigateur
 * Fallback Pusher si configuré
 */

const POLL_INTERVAL = 15000; // 15 secondes
const POLL_URL = '/api/admin/poll-stats';

let lastCheckTime = new Date().toISOString();
let pollTimer = null;
let audioCtx = null;
let soundEnabled = true;

// --- Son de notification ---
function playNotificationSound() {
    if (!soundEnabled) return;
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const now = audioCtx.currentTime;

        // Double beep agréable
        [0, 0.2].forEach((delay) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.frequency.value = delay === 0 ? 880 : 1100;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.25, now + delay);
            gain.gain.exponentialRampToValueAtTime(0.01, now + delay + 0.25);
            osc.start(now + delay);
            osc.stop(now + delay + 0.25);
        });
    } catch (err) {
        console.debug('[Admin] Audio non disponible:', err);
    }
}

// --- Voix "Nouvelle commande" ---
function speakNotification(text) {
    if (!soundEnabled) return;
    try {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            utterance.rate = 0.9;
            utterance.volume = 0.8;
            window.speechSynthesis.speak(utterance);
        }
    } catch (err) {
        console.debug('[Admin] Speech non disponible:', err);
    }
}

// --- Notification navigateur ---
function showBrowserNotification(title, body, url) {
    if ('Notification' in window && Notification.permission === 'granted') {
        const n = new Notification(title, {
            body: body,
            icon: '/favicon.ico',
            tag: 'new-order-' + Date.now(),
        });
        if (url) n.onclick = () => { window.focus(); window.location.href = url; };
    }
}

// --- Mettre à jour le badge ---
function updateBadge(count) {
    const countBadge = document.querySelector('[data-pending-orders-count]');
    const dotBadge = document.querySelector('[data-notification-dot]');
    if (countBadge) {
        countBadge.textContent = count;
        if (count > 0) countBadge.classList.remove('hidden');
        else countBadge.classList.add('hidden');
    }
    if (dotBadge) {
        if (count > 0) dotBadge.classList.remove('hidden');
        else dotBadge.classList.add('hidden');
    }
}

// --- Gérer les nouvelles commandes ---
function handleNewOrders(orders) {
    if (!orders || orders.length === 0) return;

    // Son
    playNotificationSound();

    orders.forEach((order, index) => {
        const message = `Nouvelle commande ${order.order_number} - ${order.total}`;

        // Toast Alpine (décalé pour chaque commande)
        setTimeout(() => {
            if (window.Alpine?.store('notify')) {
                window.Alpine.store('notify').add(message, 'info', 10000);
            }
        }, index * 500);

        // Notification navigateur
        showBrowserNotification('Nouvelle commande', message, order.url);
    });

    // Voix pour la première commande
    if (orders.length === 1) {
        speakNotification(`Nouvelle commande numéro ${orders[0].order_number}`);
    } else {
        speakNotification(`${orders.length} nouvelles commandes`);
    }

    // Dispatcher un événement pour que le dashboard se rafraîchisse
    window.dispatchEvent(new CustomEvent('admin:new-orders', { detail: { orders } }));
}

// --- Polling ---
async function pollStats() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(`${POLL_URL}?since=${encodeURIComponent(lastCheckTime)}`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });

        if (!response.ok) return;

        const data = await response.json();

        // Mettre à jour le badge
        updateBadge(data.pending_orders || 0);

        // Mettre à jour le badge stock
        const stockBadge = document.querySelector('[data-stock-alerts-count]');
        if (stockBadge) {
            stockBadge.textContent = data.stock_alerts || 0;
            if (data.stock_alerts > 0) stockBadge.classList.remove('hidden');
            else stockBadge.classList.add('hidden');
        }

        // Nouvelles commandes
        if (data.new_orders && data.new_orders.length > 0) {
            handleNewOrders(data.new_orders);
        }

        // Mettre à jour le timestamp
        if (data.server_time) lastCheckTime = data.server_time;

    } catch (err) {
        console.debug('[Admin] Erreur polling:', err);
    }
}

// --- Init ---
function initAdminNotifications() {
    if (!window.location.pathname.startsWith('/admin')) return;

    // Demander la permission navigateur
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Récupérer la préférence son
    try {
        soundEnabled = localStorage.getItem('admin_sound_enabled') !== 'false';
    } catch (e) {}

    // Exposer le toggle son
    window.adminToggleSound = () => {
        soundEnabled = !soundEnabled;
        try { localStorage.setItem('admin_sound_enabled', soundEnabled); } catch (e) {}
        if (window.Alpine?.store('notify')) {
            window.Alpine.store('notify').add(
                soundEnabled ? 'Son activé' : 'Son désactivé',
                'info', 2000
            );
        }
        return soundEnabled;
    };
    window.adminIsSoundEnabled = () => soundEnabled;

    // Premier poll immédiat
    pollStats();

    // Polling régulier
    pollTimer = setInterval(pollStats, POLL_INTERVAL);

    // Pause quand l'onglet est caché, reprendre quand visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer);
            pollTimer = null;
        } else {
            // Re-poll immédiatement puis relancer l'intervalle
            pollStats();
            pollTimer = setInterval(pollStats, POLL_INTERVAL);
        }
    });

    console.log('[Admin] Notifications temps réel activées (polling ' + (POLL_INTERVAL / 1000) + 's)');
}

// Lancer
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminNotifications);
} else {
    initAdminNotifications();
}
