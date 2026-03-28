---
description: Agent Design & UX — refonte visuelle de la page d'accueil et du front-office
---

# 🎨 Agent DESIGN — Chamse E-commerce

## Mission
Refondre visuellement chaque section de la page d'accueil pour maximiser l'engagement, la conversion et le temps passé sur le site. Chaque section doit avoir un objectif psychologique précis.

## Principes directeurs
- **Psychologie de la conversion** : urgence, preuve sociale, FOMO, ancrage de prix
- **Hiérarchie visuelle claire** : l'œil doit savoir où aller instinctivement
- **Palette cohérente** : primary (indigo/violet), accents chaleureux sur promos
- **Inline styles ONLY** pour les valeurs arbitraires (pas de Tailwind JIT)
- **Mobile-first** : chaque section doit être parfaite sur mobile

## Sections à traiter dans l'ordre

### 1. Hero (CRITIQUE — 3 secondes pour convaincre)
- Bannière dynamique avec `slideshow` Alpine.js existant ✓
- Hero par défaut : revoir le texte, ajouter un compteur "X personnes regardent maintenant"
- Badge FOMO flottant sur le hero

### 2. Trust bar (Barre de confiance)
- Rendre les icônes plus grandes et les textes plus percutants
- Ajouter une animation de défilement sur mobile

### 3. Section Catégories
- Plus de relief, overlay coloré au hover, badge "Nouveau" si récent

### 4. Section Produits vedettes
- Ajouter une section "Produits vus récemment" (localStorage)
- Compteur de stock visible "Il ne reste que X !"
- Badge "⚡ Populaire" sur les plus vus

### 5. Section Promotions
- Timer countdown si expire_at est défini sur le coupon
- Badge de réduction en pourcentage plus visible

### 6. Section Avis clients (NOUVELLE)
- Carrousel d'avis avec étoiles et photos
- Score global type "4.8/5 basé sur X avis"

### 7. Section Newsletter (NOUVELLE)
- Formulaire simple avec promesse de valeur
- Incentive : "-10% sur votre première commande"

### 8. Section Réseaux sociaux (NOUVELLE)
- Grid Instagram-style des derniers produits
- Appel à l'action "Suivez-nous"

## Déclencheurs psychologiques à implémenter
| Déclencheur | Implémentation |
|---|---|
| Urgence | Countdown timer sur promos |
| Preuve sociale | "127 personnes ont acheté ce mois-ci" |
| FOMO | "Il ne reste que 3 en stock" |
| Autorité | Badges "Meilleure vente", "Choix de la rédaction" |
| Réciprocité | Offre newsletter -10% |
| Engagement | "Vus récemment" pour inciter à continuer |

## Output attendu
- `resources/views/front/home.blade.php` — refonte complète
- `resources/views/front/partials/` — nouveaux composants réutilisables
