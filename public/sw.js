/**
 * Service Worker Chamse — Stratégie cache-first pour assets, network-first pour pages.
 * Version incrémentée à chaque déploiement pour invalider l'ancien cache.
 */

const CACHE_VERSION = 'chamse-v1';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;

// Ressources précachées au moment de l'install
const PRECACHE_URLS = [
    '/offline',
];

// ─── Install ───────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// ─── Activate ──────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => k !== STATIC_CACHE && k !== DYNAMIC_CACHE)
                    .map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ─── Fetch ─────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ne pas intercepter les requêtes non-GET, d'autres origines, ou les requêtes admin
    if (
        request.method !== 'GET' ||
        url.origin !== self.location.origin ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/api') ||
        url.pathname.startsWith('/webhook')
    ) {
        return;
    }

    // Assets statiques (build Vite, images publiques) → Cache First
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/storage/') ||
        /\.(woff2?|ttf|eot|svg|png|jpe?g|gif|webp|ico)$/.test(url.pathname)
    ) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Pages HTML → Network First (affiche offline si réseau coupé)
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithOfflineFallback(request));
        return;
    }
});

// ─── Stratégies ────────────────────────────────────────────────────────────
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 503 });
    }
}

async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Retourner la page offline précachée
        return caches.match('/offline') ?? new Response('Hors ligne', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
}
