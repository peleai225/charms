# Chamse — Instructions Claude

## Projet
Plateforme e-commerce SaaS premium (Maroc). Laravel 12 + Vue 3 + Inertia.js + Livewire 3 + Alpine.js + Tailwind CSS 4.

## Stack technique
- **Backend** : PHP 8.2, Laravel 12, Livewire 3, Ziggy
- **Frontend** : Vue 3 (Inertia), Alpine.js, Tailwind CSS 4, Pinia, VueUse
- **Temps réel** : Pusher + Laravel Echo
- **PDF/Print** : DomPDF, ESC/POS, QR/Barcode
- **DB** : MySQL (`chamse`)
- **Build** : Vite 7 + laravel-vite-plugin

## Structure
```
app/
  Http/Controllers/Admin/   # Contrôleurs backoffice
  Models/                   # Eloquent models (20+)
  Helpers/helpers.php       # Fonctions globales
resources/
  js/
    Pages/                  # Pages Inertia (Vue)
    Components/             # Composants réutilisables Vue
    Layouts/                # Layouts Vue
    Stores/                 # Pinia stores
  views/
    admin/                  # Vues Blade backoffice (migration vers Inertia en cours)
    components/             # Composants Blade réutilisables
```

## Conventions UI (OBLIGATOIRES)
- **Police** : Inter
- **Primary** : #2563EB | **Success** : #16A34A | **Warning** : #F59E0B | **Danger** : #DC2626
- **Icônes** : Lucide uniquement, avec parcimonie
- **Inspirations** : Shopify Admin, Stripe Dashboard, Linear, Vercel, GitHub
- **Typographie** : H1 32px / H2 24px / H3 20px / Body 16px / Caption 14px
- **Responsive** : Desktop first → Tablet → Mobile. Zéro scroll horizontal.

## Interdictions absolues
- Dégradés flashy
- Glassmorphism / Neumorphism
- Couleurs saturées
- Backgrounds abstraits / cercles décoratifs
- Icônes partout
- Données fictives (stats, graphiques, avis, avatars IA)
- Animations inutiles
- Vues de plus de 500 lignes

## Composants obligatoires (backoffice)
Sidebar, Navbar, Breadcrumb, Card, Table, Search, Filter, Modal, Drawer, Dropdown, Toast, Pagination, Empty State, Loading State, Skeleton

## États UX obligatoires
Loading, Skeleton, Error, Empty, Success, Validation, Confirmation avant suppression, Toast, Accessibilité, Navigation clavier, Dark Mode compatible

## Données
- Toujours depuis Laravel (jamais de données fictives hardcodées)
- Empty state professionnel si aucune donnée (ex: "Aucune commande" + bouton CTA)

## Conventions code
- SOLID + conventions Laravel standard
- Composants Blade réutilisables (jamais 500 lignes dans une vue)
- View Components pour la logique complexe
- Nommage : snake_case PHP, camelCase JS/Vue, kebab-case fichiers Vue

## Migration en cours
La branche `feat/inertia-vue-migration` migre le backoffice de Blade/Livewire vers Vue 3/Inertia.
- Ne pas casser les routes existantes pendant la migration
- Tester chaque page migrée avec chrome-devtools avant de valider

## Commandes utiles
```bash
composer dev          # Lance tout (server + queue + pail + vite)
composer test         # Tests PHPUnit
vendor/bin/pint       # Format PHP
php artisan tinker    # REPL Laravel
```
