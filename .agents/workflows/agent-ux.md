---
description: Agent UX — optimisation du parcours utilisateur, SEO, et rétention
---

# 🧭 Agent UX — Chamse E-commerce

## Mission
Optimiser le parcours utilisateur de A à Z pour maximiser le taux de conversion et réduire l'abandon. S'assurer que le SEO est optimal sur toutes les pages publiques.

## Périmètre d'action

### 1. SEO Technique (PRIORITÉ HAUTE)
- Meta title/description dynamiques depuis `Setting::get()`
- Open Graph tags pour partage réseaux sociaux
- Schema.org JSON-LD pour les produits (rich snippets Google)
- Sitemap XML dynamique
- Canonical URLs correctes

### 2. Parcours d'achat
- Mini-cart drawer (panier slide-in sans quitter la page)
- Breadcrumbs sur toutes les pages produit/catégorie
- "Récemment consultés" en bas de page produit
- Cross-sell : "Les clients ont aussi acheté"
- Progress bar de livraison gratuite dans le header

### 3. Engagement & Rétention
- Popup exit-intent : offre spéciale quand l'utilisateur quitte
- Session de navigation : "Reprendre où vous en étiez"
- Wishlist accessible depuis les cards produit (bouton cœur)
- Notification toaster pour ajout panier/wishlist

### 4. Accessibilité
- Focus visible sur tous les éléments interactifs
- Textes alternatifs sur toutes les images
- Contraste des couleurs suffisant (WCAG AA)
- Navigation au clavier fonctionnelle

## SEO par page

### Page d'accueil
```blade
<title>{{ Setting::get('site_name') }} — {{ Setting::get('site_tagline', 'Boutique en ligne') }}</title>
<meta name="description" content="{{ Setting::get('site_description') }}">
<meta property="og:title" content="{{ Setting::get('site_name') }}">
<meta property="og:image" content="{{ asset('storage/' . Setting::get('site_og_image', Setting::get('site_logo'))) }}">
```

### Page produit
```blade
<title>{{ $product->name }} — {{ Setting::get('site_name') }}</title>
<meta name="description" content="{{ Str::limit($product->description, 160) }}">
```

### Schema.org produit
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "offers": { "@type": "Offer", "price": "{{ $product->sale_price }}" }
}
```

## KPIs à suivre
- Taux de rebond (objectif : < 45%)
- Temps moyen sur site (objectif : > 3 min)
- Pages par session (objectif : > 4)
- Taux de conversion (objectif : > 2%)
