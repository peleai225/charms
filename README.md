# 🛒 Chamse E-Commerce

Plateforme e-commerce complète développée avec Laravel 12, Tailwind CSS 4, et Alpine.js.

## ✨ Fonctionnalités

- 🛍️ **Gestion des Produits** : Produits avec variantes, images multiples, codes-barres
- 🛒 **Panier & Checkout** : Panier persistant, coupons, calcul automatique
- 💳 **Paiements** : Intégration CinetPay (Orange Money, MTN MoMo, etc.)
- 📦 **Gestion des Stocks** : Alertes de stock bas, mouvements de stock
- 📊 **Tableau de Bord Admin** : Statistiques, rapports, graphiques
- 👥 **Gestion Clients** : Comptes clients, commandes, adresses
- 🏷️ **Coupons & Promotions** : Système de réduction flexible
- 📈 **Comptabilité** : Écritures comptables, journaux, rapports
- 🎨 **Interface Moderne** : Design responsive avec Tailwind CSS 4

## 🚀 Installation

### Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ et npm
- MySQL 8.0+ ou MariaDB

### Installation Locale

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd chamse
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   npm install
   ```

3. **Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Base de données**
   ```bash
   # Configurer .env avec vos identifiants DB
   php artisan migrate
   php artisan db:seed
   ```

5. **Assets**
   ```bash
   npm run build
   # ou pour le développement
   npm run dev
   ```

6. **Démarrer le serveur**
   ```bash
   php artisan serve
   # Dans un autre terminal
   npm run dev  # Pour le développement
   ```

## 📁 Structure du Projet

```
chamse/
├── app/
│   ├── Http/Controllers/    # Contrôleurs (Admin, Front)
│   ├── Models/              # Modèles Eloquent
│   ├── Helpers/             # Helpers personnalisés
│   └── Services/            # Services (CinetPay, etc.)
├── resources/
│   ├── views/               # Vues Blade
│   ├── css/                 # Styles Tailwind
│   └── js/                  # JavaScript Alpine.js
├── routes/
│   ├── web.php              # Routes web
│   └── api.php              # Routes API
├── database/
│   ├── migrations/          # Migrations
│   └── seeders/             # Seeders
└── public/
    └── build/               # Assets compilés (production)
```

## 🔧 Scripts Disponibles

### Développement
```bash
# Démarrer tous les services (serveur, queue, logs, vite)
composer run dev

# Compiler les assets
npm run build

# Démarrer le serveur Vite uniquement
npm run dev
```

### Déploiement
```bash
# Windows
deploy.bat
cleanup-production.bat

# Linux/Mac
./deploy.sh
./cleanup-production.sh
```

## 📚 Documentation

- [Guide de Déploiement](GUIDE_DEPLOIEMENT.md) - Guide complet pour déployer en production
- [Analyse du Projet](ANALYSE_PROJET.md) - Analyse détaillée du projet
- [Fichiers à Supprimer](FICHIERS_A_SUPPRIMER.md) - Liste des fichiers sensibles

## 🔐 Sécurité

### Avant le Déploiement

1. **Supprimer les fichiers sensibles** :
   - `install.php`
   - `public/diagnostic.php`
   - Tous les scripts `fix-*.php`

2. **Vérifier la configuration** :
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL` correct (sans slash final)

3. **Exécuter le script de nettoyage** :
   ```bash
   cleanup-production.bat  # Windows
   ./cleanup-production.sh  # Linux/Mac
   ```

## 🧪 Tests

```bash
php artisan test
```

## 📝 Configuration

### Variables d'Environnement Importantes

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Vite (production)
VITE_DEV_SERVER_RUNNING=false
```

### Base de Données

Le projet utilise MySQL avec le mode strict activé. Toutes les requêtes SQL sont conformes.

## 🛠️ Technologies Utilisées

- **Backend** : Laravel 12
- **Frontend** : Tailwind CSS 4, Alpine.js
- **Build** : Vite 7
- **Graphiques** : Chart.js
- **PDF** : DomPDF
- **Codes-barres** : Picqer PHP Barcode Generator
- **QR Codes** : SimpleSoftwareIO/simple-qrcode

## 📊 Fonctionnalités Admin

- Dashboard avec statistiques en temps réel
- Gestion complète des produits (CRUD)
- Gestion des commandes et paiements
- Rapports de ventes et stocks
- Comptabilité intégrée
- Scanner de codes-barres (POS)
- Import/Export de données
- Gestion des utilisateurs et permissions

## 🛍️ Fonctionnalités Front

- Catalogue de produits avec filtres
- Panier persistant
- Checkout sécurisé
- Compte client
- Suivi de commandes
- Système de coupons
- Recherche de produits

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## 📄 Licence

Ce projet est sous licence MIT.

## 👨‍💻 Auteur

Développé pour Chamse E-Commerce

---

**Note** : Consultez [GUIDE_DEPLOIEMENT.md](GUIDE_DEPLOIEMENT.md) pour un guide complet de déploiement en production.
