# 📦 Guide Complet : Comment Fonctionne le Dropshipping

## 🎯 Vue d'Ensemble

Le système de dropshipping permet de **vendre des produits sans stock**, en faisant expédier directement par vos fournisseurs à vos clients.

---

## 🔄 Processus Complet

### **Étape 1 : Configuration Initiale**

#### 1.1 Créer un Fournisseur
1. Aller dans **Admin → Fournisseurs → Nouveau**
2. Remplir les informations :
   - Nom du fournisseur
   - Email de contact (important pour les notifications)
   - Adresse complète
   - Conditions de paiement
   - Statut : **Actif**

#### 1.2 Marquer un Produit en Dropshipping
1. Aller dans **Admin → Produits → Éditer un produit**
2. Cocher la case **"Dropshipping"**
3. Le produit sera géré en dropshipping

#### 1.3 Associer le Fournisseur au Produit
1. Dans la page d'édition du produit
2. Section **"Fournisseurs"**
3. Ajouter le fournisseur avec :
   - **Prix d'achat** : Prix que vous payez au fournisseur
   - **SKU fournisseur** : Référence du produit chez le fournisseur
   - **Fournisseur principal** : Cocher si c'est le fournisseur principal
   - **Délai de livraison** : Nombre de jours

---

### **Étape 2 : Commande Client**

#### 2.1 Le Client Passe Commande
- Le client ajoute des produits au panier (dropshipping ou non)
- Il valide sa commande
- Le système crée la commande (`Order`)

#### 2.2 Détection Automatique
**Dès que la commande est créée**, le système :
1. ✅ Analyse tous les produits de la commande
2. ✅ Détecte ceux marqués `is_dropshipping = true`
3. ✅ Trouve le fournisseur associé (principal ou premier disponible)
4. ✅ Groupe les produits par fournisseur

#### 2.3 Création des Sous-Commandes
Pour chaque fournisseur, le système crée un **`OrderSupplier`** avec :
- ✅ Statut : `pending` (En attente)
- ✅ Liste des produits à expédier
- ✅ Quantités et prix d'achat
- ✅ Adresse de livraison du client
- ✅ Montant total (prix d'achat × quantité)

---

### **Étape 3 : Notification Automatique**

#### 3.1 Email au Fournisseur
**Automatiquement**, un email est envoyé au fournisseur contenant :
- 📧 Numéro de commande client
- 📦 Liste des produits à expédier
- 📍 Adresse de livraison complète
- 💰 Prix d'achat et quantités
- 📝 Notes du client (si présentes)
- 🔗 Instructions pour l'expédition

#### 3.2 Contenu de l'Email
```
Nouvelle commande Dropshipping #CMD-20260112-ABC123

Bonjour [Nom du fournisseur],

Une nouvelle commande nécessite votre expédition :

Produits :
- Produit A × 2 (Prix d'achat : 5 000 F)
- Produit B × 1 (Prix d'achat : 3 000 F)

Adresse de livraison :
[Adresse complète du client]

Total : 13 000 F CFA

Merci de confirmer la réception et l'expédition.
```

---

### **Étape 4 : Gestion dans l'Admin**

#### 4.1 Interface Dropshipping
**Admin → Dropshipping** (`/admin/dropshipping`)

Vous pouvez :
- ✅ Voir toutes les commandes fournisseurs
- ✅ Filtrer par statut (En attente, Expédiée, Livrée...)
- ✅ Filtrer par fournisseur
- ✅ Rechercher par numéro de commande

#### 4.2 Suivi d'une Commande
**Admin → Dropshipping → [Commande]**

Vous voyez :
- 📋 Détails de la commande client
- 📦 Produits à expédier
- 👤 Informations client
- 📍 Adresse de livraison
- 💰 Montants (prix d'achat, coût livraison, total)

#### 4.3 Mise à Jour du Statut
Vous pouvez mettre à jour :
- ✅ **Statut** : En attente → Confirmée → En traitement → Expédiée → Livrée
- ✅ **Numéro de suivi** : Numéro de colis
- ✅ **URL de suivi** : Lien de suivi
- ✅ **Transporteur** : Nom du transporteur
- ✅ **Notes** : Notes internes
- ✅ **Notes fournisseur** : Notes du fournisseur

---

### **Étape 5 : Expédition**

#### 5.1 Le Fournisseur Expédie
- Le fournisseur reçoit l'email
- Il prépare et expédie les produits
- Il vous communique le numéro de suivi (optionnel)

#### 5.2 Mise à Jour dans l'Admin
1. Aller dans **Admin → Dropshipping → [Commande]**
2. Mettre le statut à **"Expédiée"**
3. Ajouter le **numéro de suivi**
4. Ajouter le **transporteur**
5. Cliquer sur **"Mettre à jour"**

#### 5.3 Notification Client (Optionnel)
- Le client peut être notifié automatiquement
- Il reçoit le numéro de suivi
- Il peut suivre sa commande

---

### **Étape 6 : Livraison**

#### 6.1 Confirmation de Livraison
Quand le client reçoit sa commande :
1. Mettre le statut à **"Livrée"**
2. La date de livraison est enregistrée automatiquement
3. La commande est marquée comme terminée

---

## 📊 Structure des Données

### **Table `order_suppliers`**
Chaque commande fournisseur contient :
- `order_id` : Lien vers la commande client
- `supplier_id` : Le fournisseur concerné
- `status` : Statut de la commande
- `tracking_number` : Numéro de suivi
- `tracking_url` : URL de suivi
- `shipping_carrier` : Transporteur
- `subtotal` : Montant produits (prix d'achat)
- `shipping_cost` : Coût de livraison
- `total` : Total à payer au fournisseur
- `shipped_at` : Date d'expédition
- `delivered_at` : Date de livraison
- `notes` : Notes internes
- `supplier_notes` : Notes du fournisseur

---

## 🔄 Flux Automatique

```
1. Client passe commande
   ↓
2. OrderCreated event déclenché
   ↓
3. AssignOrderToSuppliers listener s'exécute
   ↓
4. Détection produits dropshipping
   ↓
5. Groupement par fournisseur
   ↓
6. Création OrderSupplier pour chaque fournisseur
   ↓
7. Email automatique au fournisseur
   ↓
8. Commande visible dans Admin → Dropshipping
```

---

## 💡 Cas d'Usage

### **Cas 1 : Commande Simple**
- Client commande 1 produit en dropshipping
- 1 fournisseur concerné
- 1 `OrderSupplier` créé
- 1 email envoyé

### **Cas 2 : Commande Multi-Fournisseurs**
- Client commande 3 produits :
  - Produit A (Fournisseur 1)
  - Produit B (Fournisseur 1)
  - Produit C (Fournisseur 2)
- 2 `OrderSupplier` créés :
  - OrderSupplier 1 : Produits A + B (Fournisseur 1)
  - OrderSupplier 2 : Produit C (Fournisseur 2)
- 2 emails envoyés (un par fournisseur)

### **Cas 3 : Commande Mixte**
- Client commande :
  - Produit A (Stock propre)
  - Produit B (Dropshipping)
- 1 `Order` créé
- 1 `OrderSupplier` créé (pour Produit B)
- Produit A : Géré en interne
- Produit B : Géré par le fournisseur

---

## ✅ Fonctionnalités Disponibles

### **Automatique**
- ✅ Détection automatique des produits dropshipping
- ✅ Attribution automatique aux fournisseurs
- ✅ Groupement intelligent par fournisseur
- ✅ Création automatique des sous-commandes
- ✅ Envoi automatique des emails

### **Gestion**
- ✅ Suivi des statuts par fournisseur
- ✅ Gestion des numéros de suivi
- ✅ Historique complet
- ✅ Filtres et recherche
- ✅ Notes et communications

### **Rapports**
- ✅ Commandes par fournisseur
- ✅ Statistiques de livraison
- ✅ Performance des fournisseurs

---

## 🎯 Avantages du Système

1. **Automatisation Complète**
   - Pas d'intervention manuelle nécessaire
   - Tout se fait automatiquement à la création de commande

2. **Multi-Fournisseurs**
   - Gère plusieurs fournisseurs par commande
   - Groupement intelligent des produits

3. **Suivi Détaillé**
   - Chaque commande fournisseur est tracée
   - Statuts et dates enregistrés

4. **Notifications**
   - Email automatique au fournisseur
   - Informations complètes dans l'email

5. **Flexibilité**
   - Produits en stock et dropshipping dans la même commande
   - Gestion mixte possible

---

## 📝 Exemple Concret

### **Scénario**
1. **Client** : Jean Dupont
2. **Commande** : 
   - T-shirt Rouge (Dropshipping, Fournisseur A)
   - Pantalon Bleu (Dropshipping, Fournisseur A)
   - Chaussures (Stock propre)

### **Ce qui se passe automatiquement**

1. **Commande créée** : `Order #CMD-123`
2. **Système détecte** :
   - T-shirt Rouge → Fournisseur A
   - Pantalon Bleu → Fournisseur A
   - Chaussures → Stock propre (ignoré)

3. **OrderSupplier créé** :
   ```
   OrderSupplier #1
   - Fournisseur : Fournisseur A
   - Produits : T-shirt Rouge × 1, Pantalon Bleu × 1
   - Total : 15 000 F (prix d'achat)
   - Statut : En attente
   ```

4. **Email envoyé** au Fournisseur A avec :
   - Détails de la commande
   - Adresse de livraison de Jean Dupont
   - Liste des produits à expédier

5. **Dans l'admin** :
   - Visible dans **Admin → Dropshipping**
   - Vous pouvez suivre le statut
   - Mettre à jour le numéro de suivi

---

## 🔧 Configuration Requise

### **Pour que ça fonctionne :**

1. ✅ **Migrations exécutées** :
   ```bash
   php artisan migrate
   ```

2. ✅ **Produit configuré** :
   - `is_dropshipping = true`
   - Fournisseur associé avec prix d'achat

3. ✅ **Email configuré** :
   - Paramètres email dans **Admin → Paramètres → Emails**
   - Email du fournisseur renseigné

4. ✅ **Event Listener actif** :
   - `AssignOrderToSuppliers` enregistré dans `EventServiceProvider`

---

## 📍 URLs Importantes

- **Liste des commandes** : `/admin/dropshipping`
- **Détails d'une commande** : `/admin/dropshipping/{id}`
- **Mise à jour statut** : `PATCH /admin/dropshipping/{id}/status`

---

## ✅ Résumé

**Le dropshipping fonctionne automatiquement :**

1. ✅ Vous marquez un produit comme "Dropshipping"
2. ✅ Vous associez un fournisseur avec prix d'achat
3. ✅ Quand un client commande, **tout est automatique** :
   - Détection
   - Attribution
   - Création sous-commande
   - Email fournisseur
4. ✅ Vous suivez dans l'admin
5. ✅ Vous mettez à jour le statut et le suivi

**C'est simple, automatique et efficace ! 🚀**

