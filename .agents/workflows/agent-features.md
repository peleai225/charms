---
description: Agent Fonctionnalités — développement des features métier et intégrations
---

# ⚙️ Agent FEATURES — Chamse E-commerce

## Mission
Développer et intégrer les fonctionnalités métier prioritaires : notifications temps réel, optimisation panier, et intégrations tierces.

## Fonctionnalités DÉJÀ TERMINÉES (à ne pas re-développer)
✅ Notifications admin temps réel (polling 15s, son double beep, voix TTS)
✅ Bouton toggle son dans le header admin
✅ Ajout rapide au panier depuis les cartes produit
✅ Dashboard auto-refresh sur nouvelle commande
✅ API commandes récentes pour AJAX

## Fonctionnalités EN COURS / À DÉVELOPPER

### 1. Notifications temps réel (amélioration)
- Ajouter badge count sur l'icône cloche dans le header
- Grouper les notifications par type (commandes, stock, avis)
- Marquer comme lu avec animation

### 2. Page d'accueil — sections dynamiques
- **Compteur de visiteurs en ligne** (approximatif via session)
- **Derniers acheteurs** : "Marie à Dakar vient d'acheter [produit]" (social proof)
- **Progress bar livraison gratuite** dans le header/mini-cart

### 3. Wishlist améliorée
- Bouton cœur animé sur les cartes produit (déjà partiellement fait)
- Page wishlist avec bouton "Ajouter tout au panier"
- Partage de wishlist via lien

### 4. Checkout optimisé
- Sauvegarde automatique des infos de livraison
- "Payer en 1 clic" pour clients connectés avec adresse sauvegardée
- Estimation du délai de livraison selon la ville

### 5. Programme de fidélité (existant — à améliorer)
- Widget de points dans le header quand connecté
- Jauge de progression vers le prochain palier
- Notification quand points crédités

## Conventions de code

### API endpoints admin
```php
// Format standard pour les réponses AJAX
return response()->json([
    'success' => true,
    'data'    => $data,
    'message' => 'OK',
]);
```

### Alpine.js composants
```js
// Toujours initialiser avec x-data et x-init
// Utiliser $dispatch pour les événements entre composants
// Stocker les préférences UI dans localStorage
```

### Gestion des erreurs
```php
// Toujours wrapper dans try/catch pour les opérations critiques
// Logger les erreurs : Log::error('Context', ['error' => $e->getMessage()])
```
