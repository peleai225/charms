# Chamse — Instructions Claude

## Projet
Plateforme e-commerce SaaS premium (Côte d'Ivoire / Maroc). Chaque client a sa propre instance (cPanel/LiteSpeed).

## Stack
- **Backend** : PHP 8.2, Laravel 12, Livewire 3, Ziggy
- **Frontend** : Vue 3 (Inertia.js), Alpine.js, Tailwind CSS 4, Pinia, VueUse
- **Temps réel** : Pusher + Laravel Echo
- **DB** : MySQL
- **Build** : Vite 7 + laravel-vite-plugin

## Structure
```
app/Http/Controllers/Admin/    # Contrôleurs backoffice
app/Http/Controllers/Front/    # Contrôleurs vitrine
app/Models/                    # 38 models Eloquent
resources/js/Pages/            # Pages Inertia (Vue 3)
resources/js/Components/       # Composants réutilisables (ProductCard, Card, Alert, Badge…)
resources/js/Layouts/          # FrontLayout, AccountLayout, AuthLayout
resources/views/admin/         # Vues Blade backoffice (migration vers Inertia en cours)
```

## Déploiement
- `public/build/` est commité dans git → **toujours `npm run build` + commit des assets** après tout changement Vue/CSS
- Instances connues : legrandbazar.ci, charms-ci.com
- Deploy via script `deploy-legrandbazar.sh` (git pull + migrate + cache clear)

## Conventions code
- snake_case PHP, camelCase JS/Vue, kebab-case fichiers Vue
- Conventions Laravel standard
- Composants Vue réutilisables avant de dupliquer du code
- Pas de fichier Vue > 500 lignes — extraire en composants

## Design system
- **Police** : Inter
- **Primary** : #2563EB | **Success** : #16A34A | **Warning** : #F59E0B | **Danger** : #DC2626
- **Icônes** : Lucide uniquement, avec parcimonie
- **Typographie** : H1 32px / H2 24px / H3 20px / Body 16px / Caption 14px
- **Inspirations** : Shopify Admin, Stripe Dashboard, Linear, Vercel
- **Responsive** : Desktop first → Tablet → Mobile. Zéro scroll horizontal.
- **Touch targets** : minimum 44px (w-11 h-11) sur mobile

## Interdictions UI
- Dégradés flashy, glassmorphism, neumorphism
- Couleurs excessivement saturées
- Backgrounds abstraits, cercles décoratifs
- Données fictives (stats, graphiques, avis, avatars IA)
- Animations purement décoratives

## États UX obligatoires
Loading, Skeleton, Empty (avec CTA), Error, Success, Validation, Confirmation avant suppression, Toast

## Données
Toujours depuis Laravel. Jamais de données fictives hardcodées. Empty state professionnel si aucune donnée.

## Migration en cours
Blade/Livewire → Vue 3/Inertia. Ne pas casser les routes existantes pendant la migration.

## Monnaie
F CFA (XOF). Format : `number_format($price, 0, ',', ' ') . ' F CFA'`

## Commandes
```bash
composer dev          # Lance server + queue + pail + vite
npm run build         # Build assets production
composer test         # Tests PHPUnit
vendor/bin/pint       # Format PHP
php artisan tinker    # REPL Laravel
```
