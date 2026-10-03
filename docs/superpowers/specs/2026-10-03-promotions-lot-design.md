# Promotions par lot — Design

**Date** : 2026-10-03
**Statut** : en revue
**Portée** : moteur de promotions « N articles pour X F CFA » avec mix & match par palier de prix

---

## 1. Intention

Augmenter le panier moyen en vendant par lot : *« 3 t-shirts pour 10 000 F CFA »*.

Le client compose son lot librement — tailles et designs différents — à condition que les
articles appartiennent à la même catégorie et à la même fourchette de prix. Plusieurs
offres coexistent sur une même catégorie, une par palier de prix :

| Catégorie | Fourchette | Lot | Prix du lot | Remise |
|---|---|---|---|---|
| T-shirts | 4 000 – 4 499 | 3 | 10 000 | −2 000 (−16,7 %) |
| T-shirts | 5 000 – 5 499 | 3 | 12 000 | −3 000 (−20 %) |

**Succès** = l'exploitant crée une offre en moins d'une minute, voit sa marge avant de
publier, et le client comprend l'offre sans explication sur la fiche produit comme au panier.

### Décisions validées

| Sujet | Décision |
|---|---|
| Au-delà du lot | Lots répétés, reste au prix catalogue. 5 pièces → 1 lot + 2. 6 → 2 lots. |
| Éligibilité | Catégorie(s) **et** fourchette de prix. Un polo à 4 000 F n'entre pas dans l'offre t-shirts. |
| Cumul coupon | Les articles en offre sont **exclus** de la base du coupon. Drapeau `stackable_with_coupons` pour ouvrir au cas par cas. |
| Composition du lot | Les unités **les plus chères** éligibles entrent dans le lot (avantage client). |
| Répartition | Remise répartie au prorata sur les lignes, en **entiers F CFA**, somme exacte. |
| Prix de référence | Prix effectif : `variant.sale_price ?? product.sale_price`. |

### Hors périmètre

« 2 + 1 offert », lots inter-catégories, offres réservées à un segment client. Le modèle
retenu les accueille plus tard (`promotions.type`) sans redesign.

---

## 2. État des lieux

Un système de **prix dégressif par palier** existe depuis le 29/09/2026 et tourne en
production sur legrandbazar.ci et charms-ci.com.

| Couche | Emplacement |
|---|---|
| Colonnes JSON | `products.bulk_pricing_rules`, `categories.bulk_pricing_rules` — format `{min_qty, unit_price}` |
| Calcul produit | `Product::getBulkUnitPrice()` — `app/Models/Product.php:255` |
| Calcul catégorie | `Category::getBulkUnitPrice()` — `app/Models/Category.php:92` |
| Mutation panier | `Cart::recalcBulkPrices()` / `recalcCategoryBulkPrices()` — `app/Models/Cart.php:155-230` |
| Nudge panier | `CartController::computeBulkNudges()` — `app/Http/Controllers/Front/CartController.php:296` |
| Fiche produit | `resources/js/Pages/Shop/Product.vue:120-139` et `:309` |
| Vignette | `resources/js/Components/ProductCard.vue:140` |
| Saisie admin | `ProductController` / `CategoryController` — textarea JSON |

### Ce qui est réutilisable

`Cart.php:201` regroupe déjà par `category_id . '_' . round(sale_price)` : c'est exactement
le mix & match par palier de prix visé. Le nudge panier et le badge vignette se rebranchent
sans réécriture de leur présentation.

### Les cinq défauts à corriger

1. **Une seule règle par catégorie, insensible au palier.** `Category::getBulkUnitPrice($quantity, $basePrice)`
   reçoit `$basePrice` et l'ignore — il renvoie un `unit_price` fixe. Deux paliers de prix
   sur la même catégorie sont donc impossibles.
2. **Sémantique de palier au lieu de lot.** Dès 3 pièces, *toutes* les pièces passent au
   tarif remisé ; la remise croît sans plafond.
3. **Prix unitaire au lieu de prix de lot.** 10 000 ÷ 3 = 3 333,33, impossible en F CFA ;
   le total affiché devient 9 999,99.
4. **Les variantes annulent l'offre.** `Cart.php:203` lit `product->sale_price` et ignore
   `variant.sale_price` (`ProductVariant.php:82`) ; `Shop/Product.vue:126` et `:309`
   désactivent l'affichage dès qu'une taille est sélectionnée.
5. **Le prix catalogue est détruit.** `recalcBulkPrices` écrase `cart_items.unit_price`,
   donc « 10 000 F au lieu de 12 000 » est inaffichable au panier.

### Capacités déjà présentes, aujourd'hui inutilisées

- `order_items.discount_amount` — existe depuis la migration d'origine, toujours forcée à 0
  (`CheckoutController:298`).
- `order_items.cost_price`, `products.purchase_price` — permettent le contrôle de marge.
- `Order::recalculateTotals()` (`Order.php:344`) recalcule `subtotal` depuis `items.total`.

---

## 3. Modèle de données

### `promotions`

```php
Schema::create('promotions', function (Blueprint $table) {
    $table->id();
    $table->string('name');                                  // « 3 t-shirts pour 10 000 F »
    $table->text('description')->nullable();                 // affiché panier + fiche produit
    $table->string('type')->default('lot');                  // extensible
    $table->unsignedSmallInteger('lot_qty');                 // 3
    $table->decimal('lot_price', 10, 2);                     // 10000
    $table->decimal('price_min', 10, 2)->nullable();         // 4000 — null = sans borne
    $table->decimal('price_max', 10, 2)->nullable();         // 4499 — null = sans borne
    $table->unsignedSmallInteger('max_lots_per_order')->nullable(); // null = illimité
    $table->boolean('include_descendants')->default(true);   // sous-catégories incluses
    $table->boolean('stackable_with_coupons')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->integer('priority')->default(0);                 // ordre de résolution
    $table->timestamps();

    $table->index(['is_active', 'starts_at', 'expires_at']);
});
```

### Pivots d'éligibilité

```php
Schema::create('promotion_category', function (Blueprint $table) {
    $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->primary(['promotion_id', 'category_id']);
});

Schema::create('promotion_product', function (Blueprint $table) {
    $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->primary(['promotion_id', 'product_id']);
});
```

`promotion_product` sert les offres ciblant des produits nommés, et reçoit la conversion de
`products.bulk_pricing_rules` (§7). Éligibilité = `(catégories ∪ produits) ∩ fourchette de prix`.
Une promotion sans aucune catégorie ni produit n'est jamais éligible — elle est refusée à la
validation du formulaire.

### `order_items` — traçabilité

```php
Schema::table('order_items', function (Blueprint $table) {
    $table->foreignId('promotion_id')->nullable()->after('product_variant_id')->nullOnDelete();
    $table->string('promotion_name')->nullable()->after('variant_name'); // snapshot
});
```

Le snapshot du libellé suit la convention déjà en place sur cette table (`name`, `sku`,
`variant_name` sont figés à la commande) : la facture reste fidèle même si l'offre est
renommée ou supprimée.

### Aucune colonne ajoutée sur `orders`

La remise lot totale est dérivée (`withSum('items', 'discount_amount')`) plutôt que
dénormalisée. Dans un système monétaire, un total stocké est un total qui peut dériver de
ses lignes. `orders.discount_amount` conserve son sens actuel : **la remise coupon**.

---

## 4. Moteur de calcul

### Frontière

`App\Services\BundlePricingService` — un panier en entrée, un objet de prix en sortie.
Aucune dépendance à la session, au checkout ni aux requêtes HTTP.

```php
final class BundlePricingService
{
    public function price(Cart $cart): CartPricing;
    public function nudges(Cart $cart): array;            // offres à portée de main
    public function forProduct(Product $product): array;  // offres affichables sur la fiche
}
```

`App\Support\CartPricing` — objet valeur immuable :

| Propriété | Sens |
|---|---|
| `subtotal` | Somme catalogue : `Σ unit_price × quantity` |
| `bundleDiscount` | Somme des remises de lot |
| `couponEligibleBase` | Somme des lignes **sans** promotion (base du coupon) |
| `couponDiscount` | Remise coupon, calculée sur `couponEligibleBase` |
| `total` | `subtotal − bundleDiscount − couponDiscount` |
| `payableSubtotal` | `subtotal − bundleDiscount` |
| `lines` | Par `cart_item` : `unit_price`, `quantity`, `promotion`, `discount`, `lineTotal` |
| `lineFor(CartItem): PricedLine` | Accès à la ligne d'un article, utilisé au checkout |

### Algorithme

Promotions candidates : actives, `starts_at` passé ou nul, `expires_at` futur ou nul.
Triées par `priority` décroissante, puis `lot_price` croissante.

L'ordre doit être total et reproductible, sinon le prix affiché dépendrait de l'ordre de
lecture en base. `priority` donne la main à l'exploitant ; à priorité égale, le `lot_price`
le plus bas passe d'abord, ce qui attribue les unités à l'offre la plus avantageuse pour le
client.

Pour chaque promotion, dans cet ordre :

1. **Constituer le vivier d'unités.** Pour chaque `cart_item` dont le produit est éligible
   (produit listé, ou catégorie listée — descendants compris si `include_descendants`, via
   `Category::getAllChildrenIds()`) et dont le **prix effectif**
   `variant.sale_price ?? product.sale_price` tombe dans `[price_min, price_max]` :
   développer en `quantity` unités individuelles. Les unités déjà consommées par une
   promotion précédente sont écartées.
2. **Trier les unités par prix décroissant.**
3. **Compter les lots** : `floor(count / lot_qty)`, plafonné par `max_lots_per_order`.
4. **Retenir** les `lots × lot_qty` premières unités ; les autres restent disponibles.
5. **Par lot** de `lot_qty` unités consécutives : somme catalogue `C`, remise
   `D = max(0, C − lot_price)`. Le `max(0, …)` garantit qu'une offre mal saisie ne
   *renchérit* jamais le panier.
6. **Répartir `D`** sur les unités du lot au prorata de leur prix, en entiers F CFA, par la
   méthode du plus grand reste : la somme des remises réparties vaut `D` exactement.
7. **Agréger par `cart_item`** : une ligne de quantité 3 dont 2 unités sont dans un lot
   reçoit la somme des remises de ces 2 unités. La ligne porte `promotion_id` dès qu'au
   moins une de ses unités est remisée.
8. **Marquer les unités consommées** : aucune unité ne peut être remisée deux fois.

Une unité n'appartient qu'à une seule offre. C'est explicable au client et rend le calcul
indépendant de l'ordre d'ajout au panier.

### Exemples normatifs

Offre : catégorie T-shirts, 4 000–4 499, lot de 3 à 10 000.

| Panier | Vivier trié | Résultat | Total |
|---|---|---|---|
| 2 × 4 000 | 4 000, 4 000 | 0 lot | 8 000 |
| 3 × 4 000 | 4 000 ×3 | 1 lot, remise 2 000 | **10 000** |
| 5 × 4 000 | 4 000 ×5 | 1 lot + 2 au catalogue | **18 000** |
| 6 × 4 000 | 4 000 ×6 | 2 lots | **20 000** |
| 4 499 + 4 200 + 4 000 + 4 000 | 4 499, 4 200, 4 000, 4 000 | lot sur les 3 plus chères (C = 12 699, D = 2 699) | **14 000** |
| 3 × 4 000 + 1 × 5 000 | 4 000 ×3 (la 5 000 hors fourchette) | 1 lot + 1 au catalogue | **15 000** |

Répartition du cas 4 499 + 4 200 + 4 000 : `D = 2 699`, prorata `956 / 893 / 850`
(somme 2 699) → lignes à 3 543 / 3 307 / 3 150, total du lot **10 000**.

---

## 5. Intégration panier et commande

### Panier

`cart_items.unit_price` **cesse d'être muté** : il redevient le prix catalogue figé à
l'ajout. Les méthodes `Cart::recalcBulkPrices()` et `Cart::recalcCategoryBulkPrices()` sont
supprimées, ainsi que leurs appels dans `addItem`, `updateItemQuantity` et `removeItem`.

`Cart` expose un accès mémoïsé au calcul :

```php
public function pricing(): CartPricing   // mémoïsé par instance
```

Les accesseurs existants sont redirigés, et leur **sémantique change** — point de vigilance
central de cette refonte :

| Accesseur | Avant | Après |
|---|---|---|
| `subtotal` | total déjà remisé (prix mutés) | **total catalogue** |
| `discount_amount` | remise coupon sur `subtotal` | remise coupon sur `couponEligibleBase` |
| `total` | `subtotal − discount` | `subtotal − bundleDiscount − couponDiscount` |
| `bundle_discount` | — | nouveau |
| `payable_subtotal` | — | nouveau : `subtotal − bundleDiscount` |

Les trois consommateurs de `$cart->subtotal` sont repointés vers `payable_subtotal`, car ils
raisonnent sur ce que le client paie réellement :

- seuil de livraison gratuite — `CheckoutController:638`
- `Coupon::canBeUsedBy()` / `min_order_amount` — `Cart::applyCoupon()`
- assiette de TVA — `CheckoutController:227`

### Commande

`CheckoutController` lit `$cart->pricing()` une fois et écrit :

```php
$line = $pricing->lineFor($item);

OrderItem::create([
    // … champs existants inchangés
    'unit_price'      => $item->unit_price,       // prix catalogue
    'discount_amount' => $line->discount,         // remise de lot (était 0)
    'total'           => $line->lineTotal,        // unit_price × qty − discount
    'promotion_id'    => $line->promotion?->id,
    'promotion_name'  => $line->promotion?->name, // snapshot
]);
```

**Correspondance des totaux de commande.** `cart.subtotal` devenant le total catalogue,
`CheckoutController:268` ne peut plus l'y recopier tel quel. La règle est :

| Colonne `orders` | Source | Sens |
|---|---|---|
| `subtotal` | `pricing.payableSubtotal` | somme des `items.total`, donc **net de lot** |
| `discount_amount` | `pricing.couponDiscount` | remise coupon seule |
| `total` | `subtotal + shipping + tax − discount_amount` | inchangé |

Ce choix conserve l'invariant de `Order::recalculateTotals()` (`Order.php:348`), qui
recalcule `subtotal` depuis `Σ items.total` : un recalcul de commande redonne exactement le
même montant qu'au checkout. La remise de lot n'est donc pas stockée au niveau commande ;
elle se dérive de `Σ order_items.discount_amount` pour la facture et les rapports.

Aucune modification du module comptable n'est requise : l'écriture générée à l'encaissement
par `CreateAccountingEntryOnPayment` part de `orders.total`, qui reste juste.

### Cohérence entre affichage et encaissement

Le prix est recalculé à l'affichage du panier **et** au moment de la commande, par le même
service. Si une offre expire entre les deux, le total retenu est celui du calcul de
commande. Le checkout compare les deux totaux et, en cas d'écart, renvoie le client au
panier avec un message explicite plutôt que de débiter un montant qu'il n'a pas vu.

---

## 6. Interfaces

### Fiche produit — `Shop/Product.vue`

La réimplémentation JavaScript du calcul (`:120-139`) est supprimée : cinq implémentations
de la même règle (deux modèles PHP, deux méthodes `Cart`, une en JS) deviennent une seule,
côté serveur. `ShopController@show` passe les offres applicables via
`BundlePricingService::forProduct()` : nom, `lot_qty`, `lot_price`, et le prix unitaire
obtenu si les `lot_qty` unités du lot étaient toutes ce produit — la seule formulation
honnête sur une fiche produit, puisque le lot réel peut mélanger plusieurs références.

Les gardes `!selectedVariant` (`:126` et `:309`) **disparaissent** : la fourchette est
évaluée sur le prix effectif de la variante sélectionnée, ce qui corrige le défaut nº 4.
Une variante hors fourchette masque l'offre — avec la raison affichée, pas en silence.

### Panier — `Cart/Index.vue`

Trois ajouts :
- une ligne « Offre *nom* » avec son montant négatif dans le récapitulatif ;
- par ligne concernée, le prix catalogue barré et le prix remisé ;
- le nudge existant conservé, sa source passant du produit à l'offre (`promotion_name`
  remplace `product_name` ; `items_needed`, `total_saving`, la barre de progression et le
  lien catégorie sont inchangés).

Quand un coupon est saisi alors que des articles sont en offre, le panier indique sur quelle
base il s'applique — sans quoi l'exclusion passe pour un bug.

### Vignette — `ProductCard.vue`

`has_bulk_pricing` devient `promotion_label` (ex. « 3 pour 10 000 »), plus parlant que le
badge générique actuel. `ShopController:99` et `:192` fournissent la valeur.

### Back-office — `Admin/Promotions/{Index,Create,Edit}.vue`

`Route::resource('promotions')` dans le groupe `admin:admin,manager`, aux côtés de `coupons`.

**Formulaire** : nom, description, catégories (sélecteur multiple sur l'arbre existant),
produits nommés (optionnel), `price_min`/`price_max`, `lot_qty`, `lot_price`,
`max_lots_per_order`, `stackable_with_coupons`, dates, activation.

**Panneau de marge** — ce qui distingue un levier de revenu d'une fuite de marge. À chaque
changement de catégorie ou de fourchette, le serveur renvoie, depuis la base :

- le nombre de produits éligibles et leur liste ;
- la marge moyenne au prix catalogue, puis au prix du lot, calculée sur `purchase_price` ;
- une alerte `danger` (#DC2626) si le prix du lot passe sous le coût d'achat ;
- un `EmptyState` « Aucun produit ne correspond à cette fourchette » si le filtre ne ramène
  rien.

Uniquement des chiffres issus de la base — aucune donnée simulée. Les produits sans
`purchase_price` renseigné sont comptés à part, avec la mention explicite que la marge est
incalculable pour eux, plutôt qu'estimés.

**Liste** : badge de statut (*active* / *programmée* / *expirée* / *inactive*) sur le modèle
de `Coupon::getStatusAttribute()`, nombre de lots vendus et chiffre d'affaires associé
(dérivés de `order_items.promotion_id`), `ConfirmModal` avant suppression, `ActivityLog` sur
chaque écriture.

États UX requis : Loading, Skeleton, Empty avec CTA, Error, Validation, Confirmation, Toast.
Palette, typographie et icônes Lucide selon le design system. Aucun fichier Vue > 500 lignes :
le panneau de marge et le sélecteur de catégories sont des composants à part.

---

## 7. Reprise de l'existant

Deux instances tournent en production. La sémantique de lot diffère de celle de palier : une
conversion automatique **changerait les prix** pour toute quantité non multiple de `lot_qty`.
Une migration de données silencieuse serait donc un risque inacceptable.

La reprise passe par une commande explicite :

```bash
php artisan promotions:import-bulk-rules --dry-run   # rapport, aucune écriture
php artisan promotions:import-bulk-rules             # création effective
```

Le rapport liste, pour chaque règle existante, l'offre qui serait créée et un tableau
comparatif avant/après aux quantités 1 à 10. L'exploitant voit les écarts et décide.

Conversion appliquée :
- règle sur **produit** → promotion rattachée à ce produit via `promotion_product`,
  `lot_qty = min_qty`, `lot_price = min_qty × unit_price`, offre **inactive** par défaut ;
- règle sur **catégorie** → une promotion **par palier de prix distinct** présent parmi les
  produits de la catégorie (`round(sale_price)`), reproduisant le regroupement de
  `Cart.php:201` ; fourchette `[palier, palier + 499]`, offre **inactive** par défaut.

Créer les offres inactives est délibéré : rien ne change en production tant que l'exploitant
n'a pas relu chaque offre et son panneau de marge.

Les colonnes `bulk_pricing_rules` et le moteur de palier ne sont retirés que dans un second
temps, par une migration distincte, après confirmation sur les deux instances. Les deux
moteurs ne tournent **jamais** en parallèle : le code de palier est débranché du panier dès
la mise en service du nouveau service, les colonnes survivant uniquement comme source de la
commande d'import.

---

## 8. Erreurs et cas limites

| Situation | Comportement |
|---|---|
| `lot_price` ≥ somme catalogue du lot | Remise 0. Le panier n'augmente jamais. |
| Offre sans catégorie ni produit | Refusée à la validation. |
| `price_min` > `price_max` | Refusée à la validation. |
| `lot_qty` < 2 | Refusée à la validation. |
| `lot_price` sous le coût d'achat | Autorisé, avec alerte explicite. C'est une décision commerciale, pas une erreur. |
| Deux offres visant la même unité | La plus prioritaire consomme l'unité ; l'autre travaille sur le reste. |
| Offre expirée entre panier et commande | Recalcul à la commande ; écart → retour au panier avec message. |
| Produit supprimé, commande conservée | `promotion_id` passe à `null`, `promotion_name` conserve le libellé. |
| Variante hors fourchette | Offre masquée sur la fiche, avec la raison affichée. |
| Panier de 99 unités éligibles | `max_lots_per_order` plafonne ; sans plafond, lots répétés. |

---

## 9. Tests

Développement piloté par les tests : le moteur est écrit test par test avant toute interface.

### `tests/Unit/BundlePricingServiceTest.php`

Quantités : 2 (aucune remise), 3 (total exactement `lot_price`), 4 et 5 (lot + reste au
catalogue), 6 et 7 (deux lots), plafond `max_lots_per_order`.

Éligibilité : prix au-dessus de `price_max`, au-dessous de `price_min`, aux bornes exactes ;
catégorie non listée exclue ; descendant inclus puis exclu selon `include_descendants` ;
prix effectif de variante retenu plutôt que celui du produit.

Arithmétique : lot composé des unités les plus chères ; répartition au prorata dont la somme
est exactement `D` ; aucun centime (entiers F CFA) ; `lot_price` supérieur à la somme
catalogue donnant une remise nulle, jamais négative.

Cycle de vie : offre inactive, non commencée, expirée — toutes ignorées. Deux offres
concurrentes sans double consommation d'une unité.

Coupon : `couponEligibleBase` exclut les lignes en offre ; `stackable_with_coupons = true`
les réintègre.

### `tests/Feature/PromotionCheckoutTest.php`

Panier → commande : `order_items.discount_amount`, `promotion_id` et `promotion_name`
persistés ; `orders.total` exact au franc.

Coupon et offre simultanés : le coupon ne s'applique qu'à la base hors offre.

Offre désactivée entre l'affichage du panier et la validation : le client est renvoyé au
panier, aucune commande créée.

Conventions suivies : `RefreshDatabase`, nommage `test_…`, commentaires de section, sur le
modèle de `tests/Feature/CouponTest.php`. Base `chamse_testing` (`phpunit.xml`).

---

## 10. Découpage

| Phase | Contenu | Vérification |
|---|---|---|
| 1 | Migrations, modèle `Promotion`, `CartPricing`, `BundlePricingService` | `composer test` — tests unitaires verts |
| 2 | Intégration `Cart` + `CheckoutController`, retrait du moteur de palier | Tests de parcours verts |
| 3 | Fiche produit, panier, vignette | `npm run build`, parcours manuel |
| 4 | CRUD back-office + panneau de marge | Parcours manuel, états UX |
| 5 | Commande `promotions:import-bulk-rules`, retrait des colonnes | `--dry-run` sur une copie de production |

La phase 1 verrouille la logique monétaire par des tests avant qu'une seule interface
n'existe. Les phases 3 et 4 imposent `npm run build` et le commit de `public/build/`.

---

## 11. Suites possibles

`promotions.type` accueille « 2 + 1 offert » et les paliers dégressifs classiques sans
toucher au modèle. Les pistes identifiées mais écartées pour l'instant : lots
inter-catégories, offres réservées à un segment client, offres limitées en stock.
