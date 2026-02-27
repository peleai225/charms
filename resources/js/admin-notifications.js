/**
 * Notifications temps réel admin - Pusher / Laravel Echo
 * Son + voix "Nouvelle commande" lors d'une nouvelle commande
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const initAdminNotifications = () => {
    // Uniquement sur les pages admin
    if (!window.location.pathname.startsWith('/admin')) return;

    const key = import.meta.env.VITE_PUSHER_APP_KEY;
    if (!key) return;

    const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1';

    // Demander la permission pour les notifications navigateur (optionnel)
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    window.adminEcho = new Echo({
        broadcaster: 'pusher',
        key,
        cluster,
        forceTLS: true,
    });

    window.adminEcho.channel('admin-notifications')
        .listen('.new-order', (e) => {
            const message = e.message || `Nouvelle commande #${e.order_number}`;
            const orderUrl = `/admin/orders/${e.order_id}`;

            // Toast notification (Alpine store)
            if (window.Alpine?.store('notify')) {
                window.Alpine.store('notify').add(message, 'info', 8000);
            }

            // Son de notification (beep)
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.frequency.value = 880;
                oscillator.type = 'sine';
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (err) {
                console.debug('Audio non disponible:', err);
            }

            // Voix "Nouvelle commande"
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance('Nouvelle commande');
                utterance.lang = 'fr-FR';
                utterance.rate = 0.9;
                utterance.volume = 1;
                window.speechSynthesis.speak(utterance);
            }

            // Mettre à jour le badge des commandes en attente
            const countBadge = document.querySelector('[data-pending-orders-count]');
            const dotBadge = document.querySelector('[data-notification-dot]');
            if (countBadge) {
                const count = parseInt(countBadge.textContent || '0', 10) + 1;
                countBadge.textContent = count;
                countBadge.classList.remove('hidden');
            }
            if (dotBadge) dotBadge.classList.remove('hidden');

            // Notification navigateur (optionnel)
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Nouvelle commande', {
                    body: message,
                    icon: '/favicon.ico',
                });
            }
        });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminNotifications);
} else {
    initAdminNotifications();
}
