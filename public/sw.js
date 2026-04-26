/**
 * KILL-SWITCH SERVICE WORKER
 *
 * Cette version se désinscrit elle-même et supprime tous les caches.
 * Elle remplace une ancienne version qui mettait en cache trop agressivement
 * et empêchait les nouveaux assets de se charger.
 *
 * Quand les navigateurs des visiteurs vont fetch ce fichier (ce qu'ils font
 * automatiquement quand le SW expire ou périodiquement), il va :
 *   1. Supprimer tous les caches stockés
 *   2. Se désinscrire lui-même
 *   3. Recharger les onglets ouverts pour servir les assets frais depuis le réseau
 *
 * Une fois que tous les visiteurs sont passés, on pourra remettre un vrai SW.
 */

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        // 1) Supprimer tous les caches
        const keys = await caches.keys();
        await Promise.all(keys.map((key) => caches.delete(key)));

        // 2) Se désinscrire
        await self.registration.unregister();

        // 3) Recharger tous les clients ouverts pour récupérer les nouveaux assets
        const clients = await self.clients.matchAll({ type: 'window' });
        clients.forEach((client) => {
            try { client.navigate(client.url); } catch (e) {}
        });
    })());
});

// Pas de gestionnaire fetch : toutes les requêtes passent en direct au réseau.
