# Chamse – E-commerce & Caisse POS

Solution e-commerce complète avec boutique en ligne, caisse POS (point de vente) et gestion multi-canal.

## Fonctionnalités

### Boutique en ligne
- Catalogue produits avec variantes, images, codes-barres
- Panier et checkout
- Paiement : CinetPay, Lygos Pay, paiement à la livraison (COD)
- Compte client, adresses, historique des commandes
- Newsletter, codes promo

### Caisse POS
- Scanner codes-barres / QR
- Mode panier, entrée/sortie stock
- Paiement espèces, carte, mobile money
- Impression reçus (imprimante thermique)
- Voir [Guide configuration imprimante](docs/CAISSE_POS_IMPRIMANTE.md)

### Administration
- Gestion produits, catégories, commandes
- Rapports ventes, stock, clients
- Paramètres : général, livraison, paiement, emails
- Import/Export produits

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8 ou MariaDB
- Node.js 18+ (pour les assets)

## Installation

```bash
# Cloner le projet
git clone <repo> chamse
cd chamse

# Installer les dépendances
composer install
npm install && npm run build

# Copier la configuration
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env
# DB_DATABASE=chamse
# DB_USERNAME=...
# DB_PASSWORD=...

# Migrations
php artisan migrate

# Lien storage (images)
php artisan storage:link
```

## Configuration

### Variables d'environnement

Voir `.env.example` pour la liste complète. Principales variables :

- **Base** : `APP_URL`, `APP_DEBUG`, `APP_LOCALE`
- **Base de données** : `DB_*`
- **CinetPay** : `CINETPAY_SITE_ID`, `CINETPAY_API_KEY`, `CINETPAY_SECRET_KEY`, `CINETPAY_SANDBOX`
- **Lygos Pay** : `LYGOS_API_KEY`
- **Email** : `MAIL_*` (ou configuration via Admin → Paramètres → Emails)

### Paramètres admin

La plupart des paramètres (nom du site, paiement, emails, devise) se configurent dans **Admin → Paramètres**.

## Démarrage

```bash
php artisan serve
```

Accès : http://localhost:8000

- **Site** : /
- **Admin** : /admin/login

## Tâches planifiées

Pour les commandes expirées et alertes stock :

```bash
# Ajouter au crontab (Linux) ou Planificateur de tâches (Windows)
* * * * * cd /chemin/vers/chamse && php artisan schedule:run >> /dev/null 2>&1
```

## Documentation

- [Configuration imprimante caisse POS](docs/CAISSE_POS_IMPRIMANTE.md)
- [Configuration Gmail](CONFIGURATION_GMAIL.md)
- [Intégration Lygos Pay](INTEGRATION_LYGOS_PAY.md)

## Licence

Propriétaire – Tous droits réservés.
