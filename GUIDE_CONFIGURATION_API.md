# 📘 Guide : Configuration des Clés API

## 🤔 Question : Faut-il passer par le fichier `.env` ?

**Réponse courte : Non, ce n'est pas obligatoire !** 

Il existe **deux approches** et le projet utilise actuellement **les deux** selon le service.

---

## 📊 État Actuel du Projet

### ✅ Lygos Pay : Settings (Base de données) + Fallback .env
```php
// app/Services/LygosPayService.php
$this->apiKey = \App\Models\Setting::get('lygos_api_key') ?: config('lygos.api_key');
```

**Fonctionnement** :
1. Cherche d'abord dans la table `settings` (configurable depuis l'admin)
2. Si pas trouvé, utilise le `.env` comme fallback

**Avantages** :
- ✅ Modifiable depuis **Admin → Paramètres → Paiement**
- ✅ Pas besoin de redéployer pour changer la clé
- ✅ Sécurisé (pas dans le code versionné)
- ✅ Fallback sur `.env` si pas configuré dans l'admin

---

### ⚠️ CinetPay : Uniquement .env (via config)
```php
// app/Services/CinetPayService.php
$this->apiKey = config('cinetpay.api_key');
```

**Fonctionnement** :
- Lit uniquement depuis `config/cinetpay.php` qui vient du `.env`

**Inconvénients** :
- ❌ Nécessite de modifier le `.env` et redéployer
- ❌ Pas modifiable depuis l'admin (même si l'interface existe)

---

## 🎯 Recommandation : Approche Hybride (comme Lygos Pay)

### Pourquoi ?

1. **Flexibilité** : L'admin peut changer les clés sans redéploiement
2. **Sécurité** : Les clés ne sont pas dans le code versionné
3. **Fallback** : Le `.env` reste disponible pour la configuration initiale
4. **Production** : Plus pratique pour gérer les clés en production

### Comment ça marche ?

```php
// Dans le service
public function __construct()
{
    // 1. Cherche dans Settings (base de données) - PRIORITÉ
    // 2. Si pas trouvé, utilise .env (fallback)
    $this->apiKey = Setting::get('lygos_api_key') ?: config('lygos.api_key');
}
```

**Ordre de priorité** :
1. **Settings (BDD)** ← Modifiable depuis l'admin
2. **Config (.env)** ← Configuration initiale/fallback

---

## 🔧 Comment Configurer ?

### Option 1 : Via l'Admin (Recommandé) ✅

1. Allez dans **Admin → Paramètres → Paiement**
2. Entrez votre clé API
3. Cliquez sur "Enregistrer"
4. **C'est tout !** Pas besoin de redéployer

**Avantages** :
- ✅ Changement immédiat
- ✅ Pas de redéploiement nécessaire
- ✅ Interface graphique

---

### Option 2 : Via .env (Configuration initiale)

```env
# .env
LYGOS_API_KEY=votre_cle_api_lygos
CINETPAY_API_KEY=votre_cle_cinetpay
CINETPAY_SITE_ID=votre_site_id
CINETPAY_SECRET_KEY=votre_secret_key
```

**Quand l'utiliser** :
- Configuration initiale du projet
- Développement local
- Fallback si Settings non configuré

**Inconvénients** :
- ❌ Nécessite redéploiement pour changer
- ❌ Pas modifiable depuis l'admin

---

## 📝 Bonnes Pratiques

### ✅ À FAIRE

1. **Utiliser Settings pour la production**
   - Permet de changer les clés sans redéployer
   - Plus flexible

2. **Garder .env comme fallback**
   - Pour la configuration initiale
   - Pour le développement local

3. **Ne jamais versionner les clés**
   - `.env` est dans `.gitignore` ✅
   - Settings sont en base de données (non versionnés) ✅

4. **Utiliser des variables d'environnement différentes**
   ```env
   # Développement
   LYGOS_API_KEY=test_key_dev
   
   # Production (dans Settings)
   LYGOS_API_KEY=prod_key_real
   ```

---

### ❌ À ÉVITER

1. **Ne pas hardcoder les clés dans le code**
   ```php
   // ❌ MAUVAIS
   $apiKey = "sk_live_1234567890";
   ```

2. **Ne pas versionner les clés**
   ```php
   // ❌ MAUVAIS - Ne pas mettre dans config/lygos.php
   'api_key' => 'sk_live_1234567890',
   ```

3. **Ne pas exposer les clés dans les logs**
   ```php
   // ⚠️ Attention - Ne pas logger la clé complète
   Log::info('API Key: ' . $this->apiKey); // ❌
   Log::info('API Key length: ' . strlen($this->apiKey)); // ✅
   ```

---

## 🔄 Harmoniser CinetPay avec Lygos Pay ?

**Actuellement** :
- ✅ Lygos Pay : Settings + .env
- ⚠️ CinetPay : Uniquement .env

**Pour harmoniser**, on pourrait modifier `CinetPayService` :

```php
// app/Services/CinetPayService.php
public function __construct()
{
    // Settings (BDD) en priorité, puis .env
    $this->siteId = Setting::get('cinetpay_site_id') ?: config('cinetpay.site_id');
    $this->apiKey = Setting::get('cinetpay_api_key') ?: config('cinetpay.api_key');
    $this->secretKey = Setting::get('cinetpay_secret_key') ?: config('cinetpay.secret_key');
    // ...
}
```

**Avantages** :
- ✅ Cohérence avec Lygos Pay
- ✅ Modifiable depuis l'admin
- ✅ Même approche pour tous les services

---

## 📋 Résumé

| Approche | Modifiable depuis Admin | Redéploiement requis | Recommandé pour |
|----------|------------------------|---------------------|-----------------|
| **Settings (BDD)** | ✅ Oui | ❌ Non | Production |
| **.env** | ❌ Non | ✅ Oui | Développement / Fallback |
| **Hybride (Settings + .env)** | ✅ Oui | ❌ Non | **Recommandé** ✅ |

---

## 🎯 Conclusion

**Pour les API, vous avez le choix** :

1. **Settings (BDD)** ← **Recommandé pour la production**
   - Modifiable depuis l'admin
   - Pas de redéploiement
   - Plus flexible

2. **.env** ← **Pour le développement / Fallback**
   - Configuration initiale
   - Développement local
   - Fallback si Settings non configuré

3. **Hybride (Settings + .env)** ← **Meilleur des deux mondes** ✅
   - Settings en priorité
   - .env en fallback
   - C'est ce que fait Lygos Pay actuellement

**Le projet utilise déjà l'approche hybride pour Lygos Pay !** 🎉

