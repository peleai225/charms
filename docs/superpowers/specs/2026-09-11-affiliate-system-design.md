# Système d'affiliation — Design Spec

**Date :** 2026-09-11
**Statut :** Approuvé, prêt pour implémentation

---

## Contexte et objectif

Ajouter un programme d'affiliation à la plateforme Chamse pour permettre aux clients de générer des commissions en partageant des liens vers la boutique. Le système doit être simple à administrer, résistant aux abus, et adapté au marché Côte d'Ivoire / Maroc (paiements Wave, Orange Money).

---

## Décisions clés

| Question | Décision | Raison |
|---|---|---|
| Qui peut être affilié ? | Sur demande + approbation admin | Évite les abus (auto-commissions) |
| Partage du lien | Lien URL `?ref=CODE` + saisie manuelle au checkout | Couverture maximale (WhatsApp, verbal) |
| Rémunération | Pourcentage configurable par affilié (défaut global) | Flexibilité sans complexité |
| Versement | Manuel (l'admin paie et marque comme versé) | Simple, adapté aux paiements locaux |

---

## Modèles de données

### Nouvelle table `affiliates`

```sql
id
user_id          FK users (unique)
code             VARCHAR unique (ex: AHMED25, généré automatiquement)
commission_rate  DECIMAL(5,2) nullable  -- si null → prend affiliate_commission_rate de settings
status           ENUM pending|active|suspended  DEFAULT pending
payment_method   VARCHAR  -- ex: "Wave 0707070707"
total_earned     DECIMAL(12,2) DEFAULT 0
total_paid       DECIMAL(12,2) DEFAULT 0
notes            TEXT nullable  -- notes admin internes
approved_at      TIMESTAMP nullable
approved_by      FK users nullable
timestamps
```

### Nouvelle table `affiliate_clicks`

```sql
id
affiliate_id  FK affiliates
ip_address    VARCHAR
user_agent    TEXT nullable
converted     BOOLEAN DEFAULT false  -- true si une commande a suivi dans les 30j
created_at
```

### Nouvelle table `affiliate_commissions`

```sql
id
affiliate_id      FK affiliates
order_id          FK orders
order_total       DECIMAL(12,2)
commission_rate   DECIMAL(5,2)  -- taux snapshot au moment de la commande
commission_amount DECIMAL(12,2)
status            ENUM pending|confirmed|paid  DEFAULT pending
confirmed_at      TIMESTAMP nullable
paid_at           TIMESTAMP nullable
withdrawal_id     FK affiliate_withdrawals nullable
timestamps
```

**Règle de confirmation :** la commission passe en `confirmed` lorsque la commande atteint le statut `delivered`. Pour les paiements en ligne (OrderPaid), elle reste `pending` jusqu'à livraison — évite de payer une commission sur une commande retournée ou non récupérée.

### Nouvelle table `affiliate_withdrawals`

```sql
id
affiliate_id    FK affiliates
amount          DECIMAL(12,2)
payment_method  VARCHAR  -- copie au moment de la demande
status          ENUM pending|paid|rejected  DEFAULT pending
admin_note      VARCHAR nullable
paid_at         TIMESTAMP nullable
timestamps
```

### Modification table `orders`

Ajout de la colonne :
```sql
affiliate_code  VARCHAR nullable  -- code copié depuis le cookie au checkout
```

### Modification table `settings`

Ajout de la clé `affiliate_commission_rate` (valeur par défaut : `5`, représente 5%).
Ajout de la clé `affiliate_min_withdrawal` (montant minimum de retrait, ex: `2000` FCFA).

---

## Backend

### Models

**`Affiliate`**
- `belongsTo(User)`
- `hasMany(AffiliateCommission)`
- `hasMany(AffiliateWithdrawal)`
- `hasMany(AffiliateClick)`
- Accessor `pending_balance` : `total_earned - total_paid`
- Accessor `effective_rate` : `commission_rate ?? Setting::get('affiliate_commission_rate', 5)`
- Scope `active()` : `where('status', 'active')`

**`AffiliateCommission`**
- `belongsTo(Affiliate)`
- `belongsTo(Order)`

**`AffiliateWithdrawal`**
- `belongsTo(Affiliate)`
- `hasMany(AffiliateCommission)` (via `withdrawal_id`)

**`AffiliateClick`**
- `belongsTo(Affiliate)`

**`Order`** — ajout de `affiliate_code` dans `$fillable`

### Event Listener

`CreateAffiliateCommission` — branché sur l'event `OrderPaid` existant :

```
handle(OrderPaid $event):
  1. Récupère order->affiliate_code
  2. Si vide → return (pas d'affiliation)
  3. Trouve Affiliate actif avec ce code
  4. Si non trouvé ou suspendu → return
  5. Calcule commission_amount = order->total × affiliate->effective_rate / 100
  6. Crée AffiliateCommission (status: pending)
  7. Met à jour affiliate->total_earned += commission_amount
```

Également, un Observer sur `Order` : quand `status` passe à `delivered`, les commissions `pending` liées à cette commande passent en `confirmed`.

### Middleware

`TrackAffiliateRef` — attaché aux routes front (groupe `web`) :

```
1. Si request()->has('ref') :
   a. Trouve Affiliate actif avec ce code
   b. Si trouvé : pose cookie 'affiliate_ref' (30 jours, httpOnly)
                  log AffiliateClick (ip, user_agent)
2. Return next(request)
```

### Controllers Admin

**`Admin\AffiliateController`**

| Méthode | Route | Description |
|---|---|---|
| `index()` | GET /admin/affiliates | Liste affiliés avec stats agrégées |
| `approve()` | POST /admin/affiliates/{id}/approve | Passe status → active |
| `suspend()` | POST /admin/affiliates/{id}/suspend | Passe status → suspended |
| `commissions()` | GET /admin/affiliates/{id}/commissions | Commissions d'un affilié |
| `withdrawals()` | GET /admin/affiliates/withdrawals | Toutes les demandes de retrait |
| `payWithdrawal()` | POST /admin/affiliates/withdrawals/{id}/pay | Marque retrait comme payé, lie les commissions |

### Controllers Front

**`Front\AffiliateController`**

| Méthode | Route | Middleware |
|---|---|---|
| `show()` | GET /mon-compte/affiliation | customer |
| `apply()` | POST /mon-compte/affiliation/apply | customer |
| `updatePayment()` | PUT /mon-compte/affiliation/payment | customer |
| `withdraw()` | POST /mon-compte/affiliation/withdraw | customer |

### Modification CheckoutController

Dans `store()` : avant de créer l'order, lire `Cookie::get('affiliate_ref')`. Si présent et valide → inclure dans `affiliate_code`. Après création → `Cookie::queue(Cookie::forget('affiliate_ref'))`.

---

## Frontend

### Admin — `resources/js/Pages/Admin/Affiliates/Index.vue`

Deux onglets :

**Onglet "Affiliés"**
- Tableau : nom/email, code, taux effectif, nb commandes, gains totaux, solde en attente, statut (badge coloré), actions
- Filtre par statut (pending / active / suspended)
- Boutons Approuver / Suspendre inline
- Clic sur une ligne → drawer latéral avec liste des commissions de cet affilié

**Onglet "Retraits"**
- Tableau : affilié, montant, moyen de paiement, date demande, statut
- Bouton "Marquer payé" sur les lignes pending → modal de confirmation avec champ note admin optionnel

### Front — `resources/js/Pages/Account/Affiliation.vue`

Ajouté dans le menu "Mon compte" (à côté de Fidélité).

**État 1 — Non affilié :**
- Bloc explicatif du programme (taux de commission, fonctionnement)
- Bouton "Devenir affilié" → formulaire : moyen de paiement (Wave / Orange Money / Autre) + numéro
- Soumission → message "Demande envoyée, l'équipe vous contacte sous 48h"

**État 2 — En attente (pending) :**
- Badge "Demande en cours de traitement"
- Formulaire de modification du moyen de paiement

**État 3 — Affilié actif :**
- Lien à copier avec bouton copier (URL complète avec `?ref=CODE`)
- 3 KPIs : Gains totaux / Solde disponible / Commandes générées
- Tableau des 20 dernières commissions (date, n° commande, montant, commission, statut badge)
- Bouton "Demander un retrait" (désactivé si solde < minimum configuré) → modal de confirmation

### Checkout — champ code parrainage

Ajout d'un champ "Code de parrainage" discret dans le formulaire checkout :
- Si cookie `affiliate_ref` présent → pré-rempli + lecture seule + indication "Parrainage appliqué ✓"
- Sinon → champ texte libre avec validation asynchrone (debounce 500ms, appel `GET /api/affiliate/validate?code=XXX`)
- Le champ ne bloque pas la commande si le code est invalide — il est simplement ignoré

---

## Sécurité et règles métier

- Un affilié ne peut pas générer une commission sur ses propres commandes (vérification `order->customer->user_id !== affiliate->user_id`)
- Commission uniquement sur commandes dont `payment_status = paid` (pas les COD non récupérés)
- Taux snapshot sauvegardé sur la commission — un changement de taux n'affecte pas les commissions passées
- Retrait possible uniquement si `pending_balance >= affiliate_min_withdrawal`
- Un retrait lie les commissions `confirmed` (pas les `pending`) jusqu'à concurrence du montant demandé

---

## Fichiers à créer / modifier

**Migrations (4 nouvelles + 1 modification)**
- `create_affiliates_table`
- `create_affiliate_clicks_table`
- `create_affiliate_commissions_table`
- `create_affiliate_withdrawals_table`
- `add_affiliate_code_to_orders_table`

**Models (4 nouveaux)**
- `app/Models/Affiliate.php`
- `app/Models/AffiliateCommission.php`
- `app/Models/AffiliateWithdrawal.php`
- `app/Models/AffiliateClick.php`

**Event / Listeners / Observer (2)**
- `app/Listeners/CreateAffiliateCommission.php`
- `app/Observers/OrderObserver.php` (ou ajout dans l'observer existant si présent)

**Middleware (1)**
- `app/Http/Middleware/TrackAffiliateRef.php`

**Controllers (2)**
- `app/Http/Controllers/Admin/AffiliateController.php`
- `app/Http/Controllers/Front/AffiliateController.php`

**Pages Vue (2)**
- `resources/js/Pages/Admin/Affiliates/Index.vue`
- `resources/js/Pages/Account/Affiliation.vue`

**Modifications existantes**
- `app/Models/Order.php` — `affiliate_code` dans `$fillable`
- `app/Http/Controllers/Front/CheckoutController.php` — lecture cookie + copie dans order
- `app/Providers/EventServiceProvider.php` — enregistrement listener
- `bootstrap/app.php` ou `Kernel.php` — enregistrement middleware
- Routes front + admin — nouvelles routes
- Menu "Mon compte" — lien Affiliation

---

## Hors périmètre (V1)

- Espace affilié séparé (tout passe par le compte client)
- Crédit boutique comme mode de versement
- Programme multi-niveaux (affiliation d'affiliés)
- Statistiques avancées (graphiques de clics/conversions)
- Paiement automatique via API Wave/OM
