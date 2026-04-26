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

## Déploiement & mise à jour

### Workflow recommandé (avec accès SSH)

Le projet fournit un script `deploy.sh` qui automatise tout le pipeline post-pull :

```bash
# Sur le serveur, dans le dossier du projet :
chmod +x deploy.sh
./deploy.sh
```

Ce qu'il fait :
1. Met le site en mode maintenance (`php artisan down`)
2. `git pull --rebase`
3. `composer install --no-dev --optimize-autoloader`
4. Lance `php artisan deploy:after-pull` qui s'occupe de :
   - Vider et regénérer tous les caches Laravel (route, view, config, event)
   - Supprimer les fichiers cache résiduels (bootstrap/cache/*.php)
   - Exécuter les migrations en attente
   - Vérifier le lien `public/storage`
   - Réinitialiser OPcache si disponible
5. Sort du mode maintenance (`php artisan up`)

**Options utiles :**
```bash
./deploy.sh --no-pull          # Si tu as déjà pull manuellement
./deploy.sh --no-composer      # Pas de mise à jour composer
./deploy.sh --no-maintenance   # Sans mode maintenance
```

### Déploiement depuis l'admin (sans SSH)

Si tu n'as pas SSH (hébergement mutualisé), un panneau admin est disponible :

**Admin → Système** (`/admin/system`)

Tu y trouveras :
- L'état complet du système (PHP, OPcache, caches Laravel, stockage)
- La version Git actuellement déployée
- Un bouton **"Lancer le déploiement"** qui exécute `deploy:after-pull` directement

> ⚠️ Avant de cliquer sur le bouton, fais d'abord ton `git pull` côté FTP/SSH/cPanel.
> Le bouton ne pull pas le code, il ne fait que les tâches post-pull.

### Commande Artisan dédiée

```bash
# Tout faire d'un coup
php artisan deploy:after-pull

# Sans migrations (déconseillé en prod après un pull qui en contient)
php artisan deploy:after-pull --skip-migrate

# Sans regénérer les caches de prod (utile en debug)
php artisan deploy:after-pull --skip-cache
```

### OPcache

OPcache est **fortement recommandé** en production (gain de perfs ~3x à 5x).
Sans lui, chaque requête recompile tous les fichiers PHP.

Vérifie son état dans **Admin → Système**. Si désactivé, demande à ton hébergeur d'activer
l'extension `opcache` dans `php.ini` :

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

## Documentation

- [Configuration imprimante caisse POS](docs/CAISSE_POS_IMPRIMANTE.md)
- [Configuration Gmail](CONFIGURATION_GMAIL.md)
- [Intégration Lygos Pay](INTEGRATION_LYGOS_PAY.md)

## Licence

Propriétaire – Tous droits réservés.
