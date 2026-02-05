# 🗑️ Fichiers à Supprimer en Production

## ⚠️ FICHIERS SENSIBLES - À SUPPRIMER IMMÉDIATEMENT

Ces fichiers contiennent des informations sensibles ou des scripts de diagnostic qui ne doivent **JAMAIS** être accessibles en production.

### 1. `install.php` (Racine du projet)
- **Raison** : Script d'installation avec mot de passe
- **Risque** : Permet l'exécution de commandes Artisan via le navigateur
- **Action** : Supprimer après installation

### 2. `public/diagnostic.php`
- **Raison** : Script de diagnostic qui expose des informations système
- **Risque** : Révèle la structure du projet et les chemins
- **Action** : Supprimer après diagnostic

### 3. Tous les scripts `fix-*.php` dans `public/`
- **Raison** : Scripts de maintenance temporaires
- **Risque** : Peuvent être utilisés pour modifier la configuration
- **Action** : Supprimer après utilisation

---

## 📋 Checklist de Suppression

Avant de mettre en production, vérifiez que ces fichiers sont supprimés :

```bash
# Vérifier que ces fichiers n'existent plus
ls -la install.php
ls -la public/diagnostic.php
ls -la public/fix-*.php
```

---

## 🔒 Sécurité

**IMPORTANT** : Ces fichiers peuvent compromettre la sécurité de votre application s'ils restent accessibles en production.

### Comment supprimer :

**Via FTP/cPanel :**
1. Connectez-vous à votre serveur
2. Naviguez vers la racine du projet
3. Supprimez `install.php`
4. Naviguez vers `public/`
5. Supprimez `diagnostic.php` et tous les `fix-*.php`

**Via Terminal (si disponible) :**
```bash
rm install.php
rm public/diagnostic.php
rm public/fix-*.php
```

---

## ✅ Après Suppression

Vérifiez que votre application fonctionne toujours correctement :
- [ ] Le site charge normalement
- [ ] Les pages admin fonctionnent
- [ ] Les assets (CSS/JS) se chargent
- [ ] Aucune erreur 404 pour ces fichiers

