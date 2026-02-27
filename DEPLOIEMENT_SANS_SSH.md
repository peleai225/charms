# Déploiement Chamse — Sans terminal ni SSH

Guide pour héberger le site sur un serveur **sans accès terminal/SSH** (hébergement mutualisé type OVH, o2switch, etc.).

---

## Étape 1 : Préparer le projet en local

### 1.1 Build des assets

Sur votre ordinateur, dans le dossier du projet :

```bash
npm run build
```

Cela crée le dossier `public/build/` avec les fichiers CSS et JS compilés.

### 1.2 Modifier le `.env` pour la production

Créez un fichier `.env` avec les valeurs de production :

```
APP_NAME=Chamse
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:NPfr/d7nKT3OQdSPP8c4BUNvJSmV1GWu66sAIPe7uk4=
APP_URL=https://votre-domaine.com

# Base de données (fournie par l'hébergeur)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=votre_base
DB_USERNAME=votre_user
DB_PASSWORD=votre_mot_de_passe

# Token pour /setup (générez une chaîne aléatoire, ex: Chamse2026Secret!)
DEPLOY_TOKEN=VotreCleSecrete123

# CinetPay (production)
CINETPAY_SANDBOX=false
# ... autres clés CinetPay
```

**Important :** Remplacez `VotreCleSecrete123` par une chaîne difficile à deviner. Vous en aurez besoin à l’étape 4.

---

## Étape 2 : Upload des fichiers

Uploadez tout le projet sur le serveur via **FTP** ou le **gestionnaire de fichiers** de l’hébergeur.

**À ne pas envoyer :**
- `node_modules/`
- `.git/`
- `.env.example` (optionnel)
- Fichiers de tests

**À envoyer :**
- Tous les dossiers : `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`
- Fichiers à la racine : `.env`, `artisan`, `composer.json`, etc.
- Le dossier `public/build/` (résultat de `npm run build`)

---

## Étape 3 : Configuration du serveur

### 3.1 Racine du site (document root)

La racine du site doit pointer vers le dossier **`public`** du projet.

Exemples :
- Chemin réel : `/home/user/chamse/public`
- Ou : `/www/chamse/public`

Si l’hébergeur ne permet pas de changer la racine, renommez `public` en `www` ou `wwwroot` et déplacez son contenu selon la doc de l’hébergeur.

### 3.2 Permissions

Donnez les droits d’écriture aux dossiers :

- `storage/` (et tout son contenu)
- `bootstrap/cache/`

En général : **755** pour les dossiers, **644** pour les fichiers. Ou **775** si le serveur le demande.

### 3.3 Fichier `.env`

Sur le serveur, créez ou modifiez le fichier `.env` à la racine du projet avec les bonnes valeurs (base de données, `APP_URL`, etc.).

---

## Étape 4 : Lancer le setup

Ouvrez le navigateur et allez sur :

```
https://votre-domaine.com/setup?token=VotreCleSecrete123
```

Remplacez `VotreCleSecrete123` par la valeur de `DEPLOY_TOKEN` dans votre `.env`.

Cette page va :
1. Créer le lien symbolique `storage` (si possible sur l’hébergement)
2. Exécuter les migrations de la base de données

Si tout est correct, vous verrez un message du type :
```
✓ Lien storage créé
✓ Migrations exécutées
```

**Sécurité :** Après le déploiement, vous pouvez supprimer `DEPLOY_TOKEN` du `.env` ou le modifier. La route `/setup` ne fera plus rien sans token valide.

---

## Étape 5 : Vérifications

1. **Page d’accueil** : `https://votre-domaine.com`
2. **Admin** : `https://votre-domaine.com/admin/login`
3. **Images** : Si les images ne s’affichent pas, le lien symbolique peut être bloqué. Le projet prévoit une route de secours pour servir les images depuis `storage/`.

---

## En cas de problème

### Erreur 500

- Vérifiez les permissions sur `storage/` et `bootstrap/cache/`
- Vérifiez que le fichier `.env` existe et que `APP_KEY` est défini
- Consultez les logs dans `storage/logs/laravel.log`

### Images non affichées

Le projet inclut une route de secours : les images sont servies même sans lien symbolique. Si ce n’est pas le cas, contactez l’hébergeur pour activer les liens symboliques.

### Base de données

- Vérifiez les identifiants dans `.env`
- Vérifiez que l’hébergeur autorise les connexions MySQL depuis PHP

---

## Résumé

| Action | Où |
|--------|-----|
| `npm run build` | En local, avant l’upload |
| Upload des fichiers | FTP / gestionnaire de fichiers |
| Créer `.env` | Sur le serveur |
| Visiter `/setup?token=XXX` | Dans le navigateur |
| Configurer paiements, email | Admin > Paramètres |
