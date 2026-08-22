# Design Spec — Redesign Page d'Accueil
**Date :** 2026-08-22  
**Projet :** Chamse / legrandbazar.ci  
**Objectif :** Maximiser la conversion sur une boutique multi-catégories (Côte d'Ivoire)  
**Priorité :** Confiance → Navigation → Achat  

---

## 1. Principes directeurs

### Philosophie
> "La première impression décide si le visiteur reste ou part en 3 secondes."

- **Confiance avant tout** : le marché CI est méfiant vis-à-vis du e-commerce. Chaque section doit répondre à "est-ce sérieux ?"
- **Mobile-first** : 80%+ des utilisateurs CI naviguent sur mobile — tout doit fonctionner parfaitement à 375px
- **Couleurs dynamiques** : toutes les couleurs d'accent proviennent des paramètres backoffice (`primary_color`, `secondary_color`, `accent_color`) via les props Inertia partagées
- **Zéro donnée fictive** : chaque chiffre, avis, produit vient de la base de données
- **Vitesse perçue** : squelettes de chargement sur les sections produits, images lazy-loaded

### Palette dynamique
```
--color-primary   : settings.primary_color   (défaut #2563EB)
--color-secondary : settings.secondary_color (défaut #8b5cf6)
--color-accent    : settings.accent_color    (défaut #f59e0b)
```
Utilisées via des classes Tailwind générées inline (`:style` Vue) ou des variables CSS injectées dans `<head>`.

---

## 2. Structure globale des sections

```
┌─────────────────────────────────┐
│  [0] Barre d'annonce dynamique  │  ← depuis banners DB (type: announcement_bar)
├─────────────────────────────────┤
│  [1] HERO — Plein écran         │  ← 100vh mobile, 85vh desktop
├─────────────────────────────────┤
│  [2] Bande de réassurance       │  ← 4 piliers, fond neutre
├─────────────────────────────────┤
│  [3] Catégories visuelles       │  ← grille photo + nom, depuis featured_categories
├─────────────────────────────────┤
│  [4] Produits — Tabs            │  ← Sélection / Nouveautés / Promotions
├─────────────────────────────────┤
│  [5] Bannière promotionnelle    │  ← optionnelle, depuis banners DB
├─────────────────────────────────┤
│  [6] Social proof               │  ← stats + avis clients réels
├─────────────────────────────────┤
│  [7] CTA WhatsApp               │  ← si whatsapp_order_enabled + social_whatsapp
└─────────────────────────────────┘
```

---

## 3. Section [0] — Barre d'annonce dynamique

**Source :** `banners` (type `announcement_bar`, depuis HandleInertiaRequests)  
**Condition :** affichée uniquement si des bannières actives existent

### Layout
```
┌─────────────────────────────────────────────────────┐
│  ◀  Livraison gratuite dès 25 000 F CFA  ▶          │
│     [titre] · [sous-titre]     [bouton optionnel]   │
└─────────────────────────────────────────────────────┘
```

### Comportement
- Rotation automatique toutes les 4 secondes si plusieurs bannières
- Couleur de fond : `banner.background_color` (depuis DB)
- Couleur texte : `banner.text_color`
- Dismissible avec localStorage (`announcement_dismissed_[id]`)
- Hauteur fixe `h-9` pour ne pas casser le layout

---

## 4. Section [1] — HERO

**But :** Capter l'attention, établir la marque, déclencher l'action en < 3 secondes.

### Layout desktop (lg+)
```
┌────────────────────────────────────────────────────────────┐
│                                                            │
│   GAUCHE (55%)              │   DROITE (45%)               │
│                             │                              │
│   Badge animé               │   ┌──────────────────────┐  │
│   "Nouvelle collection"     │   │  Image produit 1     │  │
│                             │   │  (card flottante)    │  │
│   H1 — grand, bold          │   └──────────────────────┘  │
│   "Tout ce dont vous        │                              │
│    avez besoin,             │   ┌────────┐  ┌──────────┐  │
│    livré chez vous"         │   │ Prod 2 │  │  Prod 3  │  │
│                             │   └────────┘  └──────────┘  │
│   Sous-titre descriptif     │                              │
│                             │   Badge flottant :           │
│   [Voir la boutique →]      │   "⭐ 4.8 · 200+ clients"   │
│   [WhatsApp vert]           │                              │
│                             │                              │
│   ─────────────────         │                              │
│   XXX clients · ⭐ Y/5      │                              │
│                             │                              │
└────────────────────────────────────────────────────────────┘
```

### Layout mobile
```
┌──────────────────────────┐
│   Badge animé             │
│   H1 (2 lignes max)      │
│   Sous-titre (2 lignes)  │
│   [Voir la boutique]     │
│   [WhatsApp]             │
│                          │
│   Image produit vedette  │
│   (aspect-ratio 16/9)    │
│                          │
│   Statistiques en ligne  │
└──────────────────────────┘
```

### Spécifications visuelles

| Élément | Valeur |
|---------|--------|
| Fond | `bg-slate-950` (très sombre, neutre — laisse les couleurs primaires ressortir) |
| H1 | `font-black`, `text-5xl` desktop / `text-3xl` mobile, `leading-[1.05]` |
| Mot clé coloré | Le mot central de H1 en `primary_color` |
| Badge animé | Fond `primary_color/15`, bordure `primary_color/30`, point vert pulsant |
| CTA principal | Fond blanc, texte slate-900, coin `rounded-xl`, `font-bold` |
| CTA WhatsApp | Fond `#25D366`, icône SVG WhatsApp, visible uniquement si `social_whatsapp` configuré |
| Image produit | Depuis `featured_products[0].primary_image`, fallback fond dégradé |
| Overlay image | Dégradé bas → haut `from-slate-950` pour fondre l'image dans le fond |

### Contenu dynamique
- `H1` : construit depuis `settings.site_name` → "Tout ce que vous aimez, livré par **{siteName}**"
- `Badge` : depuis `banners` (announcement_bar) OU texte fixe "Nouvelles arrivées"
- `Statistiques` : depuis `review_stats` (count, avg), si vide : masqué
- `Images` : depuis `featured_products[0]`, `[1]`, `[2]`

---

## 5. Section [2] — Bande de réassurance

**But :** Répondre immédiatement à "est-ce fiable ?"

### Layout
```
┌────────────┬────────────┬────────────┬────────────┐
│  🚚        │  🔒        │  💬        │  🔄        │
│ Livraison  │ Paiement   │ Support    │  Retours   │
│  rapide    │  sécurisé  │   7j/7     │   7 jours  │
│  "en CI"   │ "SSL/TLS"  │ "WhatsApp" │ "sans frais│
└────────────┴────────────┴────────────┴────────────┘
```

### Spécifications

| Élément | Valeur |
|---------|--------|
| Fond | `bg-white` avec `border-y border-slate-100` |
| Icône | Cercle `bg-primary/10`, SVG `text-primary` (40×40px) |
| Titre | `text-sm font-semibold text-slate-900` |
| Description | `text-xs text-slate-500` |
| Mobile | Grille 2×2 avec séparateurs |
| Desktop | Flex row, `divide-x divide-slate-100` |

---

## 6. Section [3] — Catégories visuelles

**But :** Orientation immédiate, navigation par univers produit.

**Source :** `featured_categories` (depuis le controller)

### Layout desktop
```
┌─────────────────────────────────────────────────────────────┐
│  Nos catégories                          [Voir tout →]       │
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │          │  │          │  │          │  │          │   │
│  │  Photo   │  │  Photo   │  │  Photo   │  │  Photo   │   │
│  │          │  │          │  │          │  │          │   │
│  │  Femme   │  │  Homme   │  │  Enfant  │  │ Maison   │   │
│  │ XXX art. │  │ XXX art. │  │ XXX art. │  │ XXX art. │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### Layout mobile
Scroll horizontal (`overflow-x-auto`, `snap-x`) — les 4 premières catégories visibles à mi-chemin pour signaler le scroll.

### Spécifications

| Élément | Valeur |
|---------|--------|
| Image | `aspect-square` ou `aspect-[4/5]`, `object-cover`, `rounded-2xl` |
| Overlay | Dégradé `from-black/60 via-transparent` sur le bas |
| Nom catégorie | `text-white font-bold text-sm` en bas à gauche |
| Compteur | `text-white/70 text-xs` (ex: "24 articles") |
| Hover | `scale-[1.02]` sur l'image, bordure `primary_color` |
| Fallback | Fond `bg-slate-200` + initiales si pas d'image |
| État vide | Section masquée si `featured_categories` est vide |

---

## 7. Section [4] — Produits avec onglets

**But :** Présenter l'offre, encourager l'exploration et l'achat.

**Sources :** `featured_products`, `new_products`, `sale_products`

### Onglets
```
[ Sélection ]  [ Nouveautés ]  [ Promotions ]
     ↑
  Souligné primary_color, actif
```

### Grille produits

```
Desktop (lg) : 4 colonnes
Tablet (md)  : 3 colonnes
Mobile       : 2 colonnes
```

### Card produit (composant `ProductCard.vue` existant + améliorations)

```
┌─────────────────────┐
│                     │
│    [IMAGE]          │  ← aspect-[3/4], object-cover
│                     │
│  [Badge promo %]    │  ← si compare_price, rouge
│  [Badge "Nouveau"]  │  ← si created < 7j, primary_color
│                     │
├─────────────────────┤
│  Catégorie          │  ← text-xs text-slate-400
│  Nom produit        │  ← text-sm font-semibold, line-clamp-2
│                     │
│  ⭐⭐⭐⭐⭐  (4.8)   │  ← si reviews disponibles
│                     │
│  Prix  ~~ancien~~   │
│                     │
│  [+ Ajouter]        │  ← bouton compact, primary_color
└─────────────────────┘
```

### Spécifications

| Élément | Valeur |
|---------|--------|
| Fond card | `bg-white`, `rounded-2xl`, `shadow-sm`, `border border-slate-100` |
| Hover card | `shadow-lg`, `border-primary/30`, `translateY(-2px)` |
| Image hover | `scale-105` sur l'image, transition `duration-500` |
| Badge promo | `bg-red-500 text-white`, coin supérieur droit |
| Badge nouveau | `bg-primary text-white`, coin supérieur gauche |
| Prix actuel | `font-bold text-slate-900` |
| Prix barré | `text-xs text-slate-400 line-through` |
| Bouton ajout | `h-8 px-3 text-xs bg-primary text-white rounded-lg` |
| État vide onglet | Empty state : "Aucun produit dans cette catégorie" + icône |

### CTA de section
```
[Voir tous les produits →]
```
Centré, lien vers `/boutique`, style secondaire.

---

## 8. Section [5] — Bannière promotionnelle (optionnelle)

**Source :** `banners` de type `promo_banner` ou depuis le backoffice  
**Condition :** Affichée uniquement si une bannière active existe

### Layout
```
┌────────────────────────────────────────────────────────┐
│                                                        │
│  [IMAGE DE FOND]                                       │
│                                                        │
│  GAUCHE                       DROITE                   │
│  ─────────                    ──────                   │
│  "Offre spéciale"  (badge)    Image produit featured   │
│  Titre de la promo            (flottante, ombre)       │
│  Description courte                                    │
│  [CTA → banner.link]                                   │
│                                                        │
└────────────────────────────────────────────────────────┘
```

### Spécifications

| Élément | Valeur |
|---------|--------|
| Fond | `banner.background_color` OU dégradé `primary → secondary` |
| Texte | `banner.text_color` |
| Coin radius | `rounded-3xl` |
| Margin | `mx-4 md:mx-0` (pleine largeur desktop, marge mobile) |
| CTA | Bouton blanc ou `accent_color`, depuis `banner.button_text` |

---

## 9. Section [6] — Social proof

**But :** Prouver que la boutique est utilisée et appréciée par de vraies personnes.

**Source :** `reviews` + `review_stats`  
**Condition :** Masquée si `reviews` est vide

### Layout
```
┌──────────────────────────────────────────────────────────────┐
│  Ce que disent nos clients                                    │
│                                                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐           │
│  │  ⭐⭐⭐⭐⭐  │ │  ⭐⭐⭐⭐⭐  │ │  ⭐⭐⭐⭐⭐  │           │
│  │  "Texte..." │ │  "Texte..." │ │  "Texte..." │           │
│  │             │ │             │ │             │           │
│  │  Prénom N.  │ │  Prénom N.  │ │  Prénom N.  │           │
│  │  Produit    │ │  Produit    │ │  Produit    │           │
│  └─────────────┘ └─────────────┘ └─────────────┘           │
│                                                              │
│          ◉ ○ ○      [← →]  navigation                       │
└──────────────────────────────────────────────────────────────┘
```

### Barre de stats (au-dessus des avis)
```
┌─────────────┬─────────────┬─────────────┐
│  200+       │  ⭐ 4.8/5   │  98%        │
│  commandes  │  note moy.  │  satisfaits │
└─────────────┴─────────────┴─────────────┘
```

### Spécifications

| Élément | Valeur |
|---------|--------|
| Fond section | `bg-slate-50` |
| Card avis | `bg-white`, `rounded-2xl`, `shadow-sm`, `p-5` |
| Étoiles | `text-amber-400`, SVG fill |
| Texte avis | `text-sm text-slate-700`, `line-clamp-4` |
| Auteur | `text-xs font-semibold text-slate-900` |
| Produit | `text-xs text-slate-400` |
| Pas d'avatar | Initiales dans cercle coloré `bg-primary/10 text-primary` |
| Mobile | Scroll horizontal, 1 avis visible + aperçu du suivant |
| Desktop | 3 colonnes |

---

## 10. Section [7] — CTA WhatsApp final

**But :** Dernière chance de convertir via le canal de confiance numéro 1.

**Condition :** Affiché si `social_whatsapp` est configuré ET `whatsapp_order_enabled !== '0'`

### Layout
```
┌──────────────────────────────────────────────────────────┐
│                                                          │
│   📱  Une question sur un article ?                      │
│       Notre équipe répond en moins de 5 minutes.         │
│                                                          │
│       [  Contacter sur WhatsApp  →  ]                   │
│                                                          │
│       ● Réponse rapide  ● 7j/7  ● Sans engagement       │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Spécifications

| Élément | Valeur |
|---------|--------|
| Fond | `bg-slate-900` (contraste maximum) |
| Titre | `text-2xl font-bold text-white` |
| Sous-titre | `text-slate-400` |
| CTA | `bg-[#25D366]` (vert WhatsApp officiel), `text-white`, `font-bold`, `rounded-xl` |
| Icône | SVG WhatsApp blanc, `w-5 h-5` |
| Garanties | 3 badges inline `text-slate-500 text-xs` |
| Lien | `https://wa.me/{social_whatsapp}?text=Bonjour, j'ai une question sur un article.` |

---

## 11. Interactions et micro-animations

> Règle : **aucune animation inutile**. Chaque animation a un but fonctionnel.

| Interaction | Animation | But |
|-------------|-----------|-----|
| Hover card produit | `translateY(-2px)` + ombre | Indiquer la cliquabilité |
| Hover image produit | `scale(1.05)` | Donner envie d'explorer |
| Changement d'onglet produit | Fade + slide 150ms | Transition douce |
| Badge "Nouveau" | Aucune | Pas de distraction |
| Badge "En stock limité" | Point pulsant rouge | Urgence subtile |
| Scroll into view | `opacity: 0 → 1` + `translateY(20px → 0)` | Révélation progressive (IntersectionObserver) |
| CTA WhatsApp | Légère pulsation sur l'icône au hover | Attirer l'œil |

---

## 12. Responsivité

| Breakpoint | Comportement |
|------------|-------------|
| `< 640px` (mobile) | Catégories en scroll horizontal snap · Produits 2 colonnes · Hero texte seul · Avis 1 visible |
| `640–1024px` (tablet) | Catégories 3 colonnes · Produits 3 colonnes · Hero split partiel |
| `> 1024px` (desktop) | Catégories 4–6 colonnes · Produits 4 colonnes · Hero split complet |

---

## 13. Données dynamiques requises (backend)

Le controller `HomeController` doit passer :

| Prop | Source | Utilisée dans |
|------|--------|---------------|
| `featured_categories` | `Category::active()->featured()->with('products_count')` | Section [3] |
| `featured_products` | `Product::featured()->with(...)` | Hero [1] + Onglet |
| `new_products` | `Product::orderByDesc('created_at')->take(8)` | Onglet Nouveautés |
| `sale_products` | `Product::onSale()->take(8)` | Onglet Promotions |
| `reviews` | `Review::approved()->latest()->take(6)` | Section [6] |
| `review_stats` | `['count' => ..., 'avg' => ...]` | Hero + Section [6] |
| `whatsapp_number` | `Setting::get('social_whatsapp')` | Hero + CTA [7] |

Toutes les couleurs et paramètres globaux proviennent de `HandleInertiaRequests::share()` (`settings`).

---

## 14. Composants Vue à créer / modifier

| Fichier | Action |
|---------|--------|
| `Pages/Home.vue` | Refonte complète (sections atomiques) |
| `Components/ProductCard.vue` | Améliorer : badge nouveau, rating inline, bouton ajout |
| `Components/HeroCarousel.vue` | Réutiliser pour les bannières announcement_bar |
| `Components/SectionTitle.vue` | Nouveau : titre + sous-titre + lien "voir tout" |
| `Components/ReviewCard.vue` | Nouveau : card avis avec étoiles, auteur, produit |
| `Components/CategoryCard.vue` | Nouveau : card catégorie photo + overlay |

---

## 15. Ce que le design N'inclut PAS

- Dégradés flashy ou saturés
- Glassmorphism / neumorphism
- Cercles décoratifs abstraits en fond
- Animations continues inutiles
- Données fictives (tout vient de la DB ou est masqué)
- Icônes décoratives dans chaque titre

---

*Spec à valider avant implémentation. Une fois approuvée, l'implémentation sera planifiée section par section.*
