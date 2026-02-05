# 🚀 Guide de Déploiement - Chamse E-Commerce

## 📋 Prérequis

- PHP 8.2+
- Composer installé
- Node.js et npm installés
- Accès au serveur (FTP/cPanel ou SSH)

---

## 🔧 Étape 1 : Préparation Locale

### 1.1 Compiler les Assets

```bash
# Windows
deploy.bat

# Linux/Mac
chmod +x deploy.sh
./deploy.sh
```

Ou manuellement :
```bash
npm run build
composer dump-autoload --optimize
```

### 1.2 Vérifier la Configuration

Vérifiez votre fichier `.env` :
```env
APP_NAME="Chamse E-Commerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votre_base
DB_USERNAME=votre_user
DB_PASSWORD=votre_password

# Vite (optionnel, mais recommandé)
VITE_DEV_SERVER_RUNNING=false
```

**⚠️ IMPORTANT** :
- `APP_ENV` doit être `production`
- `APP_DEBUG` doit être `false`
- `APP_URL` ne doit **PAS** avoir de slash final

---

## 📤 Étape 2 : Upload sur le Serveur

### 2.1 Fichiers à Uploader

**À uploader :**
- ✅ Tout le projet **SAUF** :
  - `node_modules/` (pas nécessaire)
  - `.git/` (pas nécessaire)
  - `.env` (créer manuellement sur le serveur)
  - `storage/logs/*.log` (sera créé automatiquement)

**Structure recommandée sur cPanel :**
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── build/          ← IMPORTANT : doit contenir manifest.json
│   ├── index.php
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env                ← Créer manuellement
├── artisan
├── composer.json
└── ...
```

### 2.2 Permissions des Dossiers

Sur le serveur, définissez les permissions :
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/build
```

---

## 🔐 Étape 3 : Configuration sur le Serveur

### 3.1 Créer le fichier .env

1. Connectez-vous à votre serveur (cPanel File Manager ou FTP)
2. Créez le fichier `.env` à la racine du projet
3. Copiez le contenu de votre `.env` local et adaptez :
   - `APP_URL` avec votre domaine réel
   - Les identifiants de base de données
   - Les clés API (CinetPay, etc.)

### 3.2 Exécuter les Commandes Laravel

Si vous avez accès SSH :
```bash
cd /chemin/vers/votre/projet
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Sans SSH (cPanel)** :
1. Utilisez le script `install.php` (temporairement)
2. Accédez à : `https://votre-domaine.com/install.php?password=VOTRE_MOT_DE_PASSE`
3. **SUPPRIMEZ** `install.php` après utilisation

---

## 🧹 Étape 4 : Nettoyage

### 4.1 Supprimer les Fichiers Sensibles

```bash
# Windows
cleanup-production.bat

# Linux/Mac
chmod +x cleanup-production.sh
./cleanup-production.sh
```

**Fichiers à supprimer manuellement si nécessaire :**
- `install.php`
- `public/diagnostic.php`
- `public/fix-*.php`
- Tous les scripts de debug

### 4.2 Vérifier les Caches

Videz les caches si nécessaire :
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

Puis recréez-les :
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ Étape 5 : Vérification

### 5.1 Tests à Effectuer

- [ ] Le site charge correctement
- [ ] Les styles CSS s'affichent
- [ ] Le JavaScript fonctionne
- [ ] La page admin `/admin` est accessible
- [ ] La connexion admin fonctionne
- [ ] Les produits s'affichent
- [ ] Le panier fonctionne
- [ ] Les commandes peuvent être créées

### 5.2 Vérifier les Assets

Ouvrez la console du navigateur (F12) et vérifiez :
- ✅ Aucune erreur 404 pour les fichiers CSS/JS
- ✅ Les fichiers sont chargés depuis `/build/assets/`
- ✅ Aucune tentative de connexion à `localhost:5173`

---

## 🐛 Résolution de Problèmes

### Problème : Les styles ne s'affichent pas

**Solution :**
1. Vérifiez que `public/build/manifest.json` existe
2. Vérifiez que `public/build/assets/` contient les fichiers CSS/JS
3. Videz les caches : `php artisan view:clear`
4. Vérifiez `APP_ENV=production` dans `.env`

### Problème : Erreur 500

**Solution :**
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez les permissions des dossiers
3. Vérifiez la configuration de la base de données
4. Vérifiez que toutes les migrations ont été exécutées

### Problème : Erreur SQL GROUP BY

**Solution :**
1. Vérifiez que vous avez la dernière version du code
2. Le problème a été corrigé dans `DashboardController.php`
3. Si le problème persiste, vérifiez les autres contrôleurs

### Problème : Assets 404

**Solution :**
1. Vérifiez que `npm run build` a été exécuté
2. Vérifiez que `public/build/` existe et contient les fichiers
3. Vérifiez les permissions : `chmod -R 755 public/build`

---

## 📝 Checklist Finale

Avant de considérer le déploiement comme terminé :

- [ ] `APP_ENV=production` dans `.env`
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `APP_URL` correct (sans slash final)
- [ ] Assets compilés (`npm run build`)
- [ ] `manifest.json` présent dans `public/build/`
- [ ] Fichiers sensibles supprimés
- [ ] Caches Laravel créés
- [ ] Permissions des dossiers correctes
- [ ] Base de données migrée
- [ ] Tests fonctionnels effectués
- [ ] Aucune erreur dans les logs

---

## 🔄 Mises à Jour Futures

Pour mettre à jour le projet :

1. **Localement :**
   ```bash
   git pull  # ou télécharger les nouveaux fichiers
   composer install
   npm install
   npm run build
   composer dump-autoload
   ```

2. **Sur le serveur :**
   - Uploader les nouveaux fichiers
   - Exécuter les migrations si nécessaire : `php artisan migrate`
   - Vider et recréer les caches
   - Tester

---

## 📞 Support

En cas de problème :
1. Consultez les logs : `storage/logs/laravel.log`
2. Vérifiez la documentation Laravel
3. Consultez `ANALYSE_PROJET.md` pour les problèmes connus

---

**Bon déploiement ! 🚀**

