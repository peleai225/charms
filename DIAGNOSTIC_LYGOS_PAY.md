# 🔍 Diagnostic Lygos Pay

## Problème : "Erreur de connexion au service de paiement"

### ✅ Améliorations apportées

1. **Logging détaillé** : Tous les appels API sont maintenant loggés avec les détails complets
2. **Test de connexion** : Bouton "Tester la connexion API" dans les paramètres admin
3. **Messages d'erreur améliorés** : Messages plus clairs selon le type d'erreur
4. **Vérification de configuration** : Vérifie que la clé API est bien configurée avant d'appeler l'API

---

## 🔧 Étapes de diagnostic

### 1. Vérifier la configuration

**Dans l'admin** :
1. Allez dans **Admin → Paramètres → Paiement**
2. Vérifiez que "Lygos Pay" est activé
3. Vérifiez que votre clé API est bien entrée
4. Cliquez sur **"Tester la connexion API"**

**Résultats possibles** :
- ✅ **Succès** : L'API est accessible et la clé API est valide
- ❌ **Erreur 401** : Clé API invalide ou expirée
- ❌ **Erreur 404** : URL de l'API incorrecte
- ❌ **Erreur de connexion** : L'URL de l'API n'est pas accessible

### 2. Vérifier les logs

**Fichier** : `storage/logs/laravel.log`

**Recherchez** :
```
Lygos Pay: Initializing payment
Lygos Pay API Response
Lygos Pay: Connection exception
```

**Informations importantes dans les logs** :
- `api_url` : L'URL complète appelée
- `status_code` : Code HTTP de la réponse
- `response_body` : Réponse complète de l'API
- `api_key_length` : Longueur de la clé API (pour vérifier qu'elle est bien chargée)

### 3. Vérifier l'URL de l'API

**URL par défaut** : `https://api.lygosapp.com/v1`

**Si cette URL n'est pas accessible** :
1. Vérifiez la documentation officielle Lygos Pay
2. Contactez le support Lygos Pay pour confirmer l'URL de l'API
3. L'URL peut être différente selon votre région ou votre compte

**Pour changer l'URL** :
Modifiez dans `.env` :
```env
LYGOS_API_BASE_URL=https://votre-url-api.lygosapp.com/v1
```

### 4. Vérifier la clé API

**Où la trouver** :
- Dashboard Lygos Pay : https://dashboard.lygosapp.com
- Ou : https://pay.lygosapp.com/dashboard

**Format** :
- Généralement une chaîne de caractères longue
- Vérifiez qu'il n'y a pas d'espaces avant/après
- Vérifiez qu'elle est bien copiée en entier

**Dans les logs** :
- `api_key_length` doit être > 0
- Si `api_key_length` = 0, la clé API n'est pas chargée

---

## 🐛 Erreurs courantes et solutions

### Erreur : "Lygos Pay n'est pas configuré"

**Cause** : La clé API n'est pas définie

**Solution** :
1. Allez dans **Admin → Paramètres → Paiement**
2. Entrez votre clé API Lygos Pay
3. Cliquez sur "Enregistrer"
4. Réessayez

---

### Erreur : "Impossible de se connecter à l'API Lygos Pay"

**Causes possibles** :
1. L'URL de l'API est incorrecte
2. L'API Lygos Pay est en maintenance
3. Problème de connexion Internet
4. Firewall bloque l'accès

**Solutions** :
1. **Vérifier l'URL** :
   - Testez l'URL dans votre navigateur : `https://api.lygosapp.com/v1`
   - Si elle ne répond pas, contactez le support Lygos

2. **Vérifier la connexion** :
   ```bash
   curl -I https://api.lygosapp.com/v1
   ```

3. **Vérifier les logs** :
   - Regardez `storage/logs/laravel.log` pour l'erreur exacte

4. **Contacter le support Lygos** :
   - Email : support@lygosapp.com (à vérifier)
   - Dashboard : https://dashboard.lygosapp.com

---

### Erreur : "Clé API invalide" (401)

**Cause** : La clé API est incorrecte ou expirée

**Solution** :
1. Connectez-vous à votre dashboard Lygos Pay
2. Régénérez votre clé API
3. Copiez la nouvelle clé API
4. Mettez à jour dans **Admin → Paramètres → Paiement**
5. Testez à nouveau

---

### Erreur : "Endpoint introuvable" (404)

**Cause** : L'URL de l'endpoint est incorrecte

**Solution** :
1. Vérifiez la documentation Lygos Pay pour l'endpoint correct
2. L'endpoint peut être :
   - `/gateway` (créer un gateway)
   - `/payment` (créer un paiement)
   - `/collect` (collecte de paiement)
   - Autre selon la documentation

3. Si l'endpoint est différent, modifiez dans `app/Services/LygosPayService.php` :
   ```php
   $apiUrl = $this->baseUrl . '/votre-endpoint';
   ```

---

## 📋 Checklist de diagnostic

- [ ] Clé API configurée dans Admin → Paramètres → Paiement
- [ ] Lygos Pay activé dans les paramètres
- [ ] Test de connexion effectué (bouton "Tester la connexion API")
- [ ] Logs vérifiés (`storage/logs/laravel.log`)
- [ ] URL de l'API accessible (test dans navigateur)
- [ ] Clé API valide (vérifiée dans dashboard Lygos)
- [ ] Documentation Lygos Pay consultée pour l'endpoint correct

---

## 📞 Support

**Si le problème persiste** :

1. **Consultez les logs** :
   ```bash
   tail -f storage/logs/laravel.log | grep "Lygos Pay"
   ```

2. **Contactez le support Lygos Pay** :
   - Dashboard : https://dashboard.lygosapp.com
   - Documentation : https://docs.lygosapp.com

3. **Informations à fournir** :
   - Message d'erreur exact
   - Code HTTP (si disponible)
   - Extrait des logs (sans la clé API complète)
   - URL de l'API utilisée

---

## 🔄 Test manuel de l'API

**Avec cURL** :
```bash
curl -X POST https://api.lygosapp.com/v1/gateway \
  -H "api-key: VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1000,
    "currency": "XOF",
    "shop_name": "Test",
    "message": "Test payment",
    "order_id": "TEST-001",
    "success_url": "https://votre-site.com/success",
    "failure_url": "https://votre-site.com/failure"
  }'
```

**Résultat attendu** :
- Si succès : JSON avec `id` et `link`
- Si erreur : JSON avec `message` d'erreur

---

## ✅ Après correction

Une fois le problème résolu :
1. Testez un paiement complet (créer une commande test)
2. Vérifiez que la redirection vers Lygos Pay fonctionne
3. Vérifiez que le retour après paiement fonctionne
4. Vérifiez les logs pour confirmer que tout fonctionne

