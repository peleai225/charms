---
description: Agent Audit — critique technique et fonctionnelle de toutes les pages
---

# 🔍 Agent AUDIT — Chamse E-commerce

## Mission
Auditer en continu la qualité du code, des performances, de la sécurité et de l'expérience utilisateur. Signaler les problèmes et proposer des corrections.

## Checklist de l'audit permanent

### 🔴 Critique (bloquer le lancement)
- [ ] Toutes les routes admin protégées par middleware `auth` + rôle
- [ ] CSRF sur tous les formulaires POST/PUT/DELETE
- [ ] Validation serveur sur toutes les entrées utilisateur
- [ ] Pas de données sensibles exposées dans les réponses JSON
- [ ] Images uploadées : validation type MIME + taille max

### 🟠 Important (corriger avant lancement)
- [ ] N+1 queries : utiliser `with()` pour les relations Eloquent
- [ ] Cache sur les requêtes lourdes (KPIs dashboard, catalogue)
- [ ] Pagination sur toutes les listes (`.paginate()`)
- [ ] Logs d'erreur Laravel fonctionnels
- [ ] Emails transactionnels testés (confirmation, livraison)

### 🟡 Amélioration (post-lancement)
- [ ] Lazy loading des images (`loading="lazy"`)
- [ ] Compression des assets (Vite build)
- [ ] CDN pour les images produits
- [ ] Tests unitaires sur les modèles critiques

## Points de vigilance identifiés

### Bug connu : wishlist.blade.php L.69
```
Argument '2' passed to echo() — pagination links retourne un objet HtmlString
```
**Fix** : `{{ $wishlistItems->links() }}` est correct, l'avertissement vient du linter IDE, pas PHP réel.

### Images produits
- Vérifier que `primary_image_url` est bien un accessor sur le modèle Product
- Fallback image si null

### Dashboard stats
- Les KPIs doivent utiliser le cache (actuellement des requêtes directes)
- Recommandé : `Cache::remember('dashboard.stats', 300, fn() => [...])`

## Rapport d'audit format
```markdown
## Audit #X — [Date]
**Page** : /admin/products
**Score** : 7/10
**Problèmes** :
- [ ] N+1 sur $product->images (corriger avec eager loading)
**Bien** :
- ✓ CSRF présent sur tous les formulaires
```
