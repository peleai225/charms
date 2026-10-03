# Promotions par lot — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remplacer le moteur de prix dégressif par palier par un moteur d'offres par lot (« 3 t-shirts pour 10 000 F CFA ») avec mix & match par catégorie et fourchette de prix.

**Architecture:** Une entité `Promotion` (table + 2 pivots d'éligibilité) et un service unique `BundlePricingService` qui prend un panier et renvoie un objet valeur `CartPricing` portant les remises ligne par ligne. Le service remplace cinq implémentations dispersées de la même règle (`Product::getBulkUnitPrice`, `Category::getBulkUnitPrice`, `Cart::recalcBulkPrices`, `Cart::recalcCategoryBulkPrices`, et un calcul JavaScript dans `Shop/Product.vue`). `cart_items.unit_price` cesse d'être muté et redevient le prix catalogue.

**Tech Stack:** PHP 8.2, Laravel 12, PHPUnit 11, Inertia.js + Vue 3.5, Tailwind CSS 4, Vite 7.

**Spec:** `docs/superpowers/specs/2026-10-03-promotions-lot-design.md`

## Global Constraints

- Monnaie **F CFA (XOF)**, sans sous-unité. Toute remise stockée ou affichée est un **entier**. Format d'affichage : `number_format($price, 0, ',', ' ') . ' F CFA'`.
- Conventions de nommage : **snake_case** en PHP, **camelCase** en JS/Vue, **kebab-case** pour les noms de fichiers Vue.
- Tout contrôleur `Admin\*` rendant une page Inertia appelle `Inertia::setRootView('layouts.admin-inertia')` avant `Inertia::render()`.
- Tests : `RefreshDatabase`, nommage `test_…`, base `chamse_testing` (`phpunit.xml`). Lancer via `composer test`.
- Design system : Primary `#2563EB`, Success `#16A34A`, Warning `#F59E0B`, Danger `#DC2626`. Police Inter. Icônes **Lucide uniquement** (`lucide-vue-next`), avec parcimonie.
- Interdits UI : dégradés flashy, glassmorphism, neumorphism, cercles décoratifs, animations décoratives, **données fictives**.
- États UX obligatoires sur chaque écran : Loading, Skeleton, Empty (avec CTA), Error, Validation, Confirmation avant suppression, Toast.
- Aucun fichier Vue > **500 lignes** — extraire en composants.
- `public/build/` est commité : toute tâche touchant du Vue ou du CSS finit par `npm run build` **et** le commit des assets générés.
- Les deux moteurs de prix ne tournent **jamais** en parallèle (cf. Task 6 et Task 13).
- Instances en production : legrandbazar.ci, charms-ci.com. Aucune migration ne modifie un prix sans action explicite de l'exploitant.
- Format PHP : `vendor/bin/pint` avant chaque commit touchant du PHP.

## Review Focus

Classes d'entrée que la spec implique sans qu'aucune tâche ne les exerce spontanément. Chacune reçoit son test dans la tâche qui possède le code concerné.

1. **Panier vide** — `price()` sur un panier sans ligne doit rendre un `CartPricing` à zéro, pas une erreur de tableau vide. *(test en Task 5)*
2. **Produit sans catégorie** — `products.category_id` est nullable ; une promotion par catégorie ne doit jamais capter un produit à `category_id = null` via un `in_array(null, …)` permissif. *(test en Task 3)*
3. **`lot_price` à 0** — une offre « 3 pour 0 » remise la totalité du lot ; les lignes tombent à 0 et le total du panier ne devient jamais négatif. *(test en Task 4)*
4. **Même produit en deux variantes** — la contrainte `cart_item_unique` autorise deux lignes pour un même `product_id` avec des `product_variant_id` distincts ; leurs unités doivent se cumuler dans un seul vivier. *(test en Task 4)*
5. **`lot_qty` supérieur au panier** — une offre « lot de 5 » sur un panier de 3 unités éligibles ne produit aucun lot et ne doit pas faire échouer `array_slice`. *(test en Task 4)*

---

## Structure des fichiers

### Créés

| Fichier | Responsabilité |
|---|---|
| `database/migrations/2026_10_03_100001_create_promotions_table.php` | Table `promotions` |
| `database/migrations/2026_10_03_100002_create_promotion_category_table.php` | Pivot catégories éligibles |
| `database/migrations/2026_10_03_100003_create_promotion_product_table.php` | Pivot produits éligibles |
| `database/migrations/2026_10_03_100004_add_promotion_to_order_items_table.php` | `promotion_id` + `promotion_name` |
| `app/Models/Promotion.php` | Entité, scopes, éligibilité catégorie/prix, statut |
| `app/Support/PricedLine.php` | Objet valeur : une ligne de panier tarifée |
| `app/Support/CartPricing.php` | Objet valeur : totaux d'un panier |
| `app/Services/BundlePricingService.php` | Moteur : vivier, lots, répartition, nudges, fiche produit |
| `app/Http/Controllers/Admin/PromotionController.php` | CRUD back-office + endpoint de marge |
| `app/Console/Commands/ImportBulkPricingRules.php` | Reprise des règles existantes |
| `resources/js/Pages/Admin/Promotions/Index.vue` | Liste des offres |
| `resources/js/Pages/Admin/Promotions/Create.vue` | Création |
| `resources/js/Pages/Admin/Promotions/Edit.vue` | Édition |
| `resources/js/Pages/Admin/Promotions/Partials/PromotionForm.vue` | Formulaire partagé Create/Edit |
| `resources/js/Pages/Admin/Promotions/Partials/MarginPanel.vue` | Panneau de marge |
| `resources/js/Pages/Admin/Promotions/Partials/CategoryPicker.vue` | Sélecteur d'arbre de catégories |
| `tests/Unit/PromotionModelTest.php` | Scopes, fourchette, descendants, statut |
| `tests/Unit/CartPricingTest.php` | Totaux de l'objet valeur |
| `tests/Unit/BundlePricingServiceTest.php` | Éligibilité, lots, répartition, coupon |
| `tests/Feature/PromotionCheckoutTest.php` | Persistance panier → commande |

### Modifiés

| Fichier | Nature |
|---|---|
| `app/Models/Cart.php:36-72` | Accesseurs redirigés vers `pricing()` |
| `app/Models/Cart.php:105-230` | Retrait de `recalcBulkPrices` / `recalcCategoryBulkPrices`, prix de variante dans `addItem` |
| `app/Models/CartItem.php:52-55` | `getTotalAttribute` devient le total catalogue |
| `app/Models/OrderItem.php` | `promotion_id`, `promotion_name` dans `$fillable` ; relation `promotion()` |
| `app/Http/Controllers/Front/CheckoutController.php:224-300` | Lecture unique de `pricing()`, persistance des remises |
| `app/Http/Controllers/Front/CheckoutController.php:638` | Seuil de livraison gratuite sur `payable_subtotal` |
| `app/Http/Controllers/Front/CartController.php:55-80` | Props de récapitulatif enrichies |
| `app/Http/Controllers/Front/CartController.php:293-360` | `computeBulkNudges` remplacé par le service |
| `app/Http/Controllers/Front/ShopController.php:99,192,405` | `promotion_label` et offres de la fiche |
| `resources/js/Pages/Cart/Index.vue` | Ligne « Offre », prix barré, nudge par offre |
| `resources/js/Pages/Shop/Product.vue:120-139,300-325` | Retrait du calcul JS, offres serveur, fin du blocage variante |
| `resources/js/Components/ProductCard.vue:140` | `promotion_label` |
| `routes/web.php:389` | `Route::resource('promotions')` |
| `app/Models/Product.php:251-287` | Retrait du moteur de palier (Task 13) |
| `app/Models/Category.php:86-105` | Retrait du moteur de palier (Task 13) |

---

## Task 1: Table et modèle `Promotion`

**Files:**
- Create: `database/migrations/2026_10_03_100001_create_promotions_table.php`
- Create: `database/migrations/2026_10_03_100002_create_promotion_category_table.php`
- Create: `database/migrations/2026_10_03_100003_create_promotion_product_table.php`
- Create: `database/migrations/2026_10_03_100004_add_promotion_to_order_items_table.php`
- Create: `app/Models/Promotion.php`
- Modify: `app/Models/OrderItem.php`
- Test: `tests/Unit/PromotionModelTest.php`

**Interfaces:**
- Consumes: `App\Models\Category::getAllChildrenIds(): array` (existant, `app/Models/Category.php`) — renvoie l'id de la catégorie **suivi** de ceux de ses descendants.
- Produces:
  - `Promotion::scopeValid($query)` — actives, dans la fenêtre de dates
  - `Promotion::scopeResolutionOrder($query)` — `priority` desc, `lot_price` asc, `id` asc
  - `Promotion::matchesPrice(float $price): bool`
  - `Promotion::eligibleCategoryIds(): array`
  - `Promotion::getStatusAttribute(): string` — `active|scheduled|expired|inactive`
  - `Promotion::categories(): BelongsToMany`, `Promotion::products(): BelongsToMany`
  - `OrderItem::promotion(): BelongsTo`

- [ ] **Step 1: Écrire les migrations**

`database/migrations/2026_10_03_100001_create_promotions_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('lot');
            $table->unsignedSmallInteger('lot_qty');
            $table->decimal('lot_price', 10, 2);
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->unsignedSmallInteger('max_lots_per_order')->nullable();
            $table->boolean('include_descendants')->default(true);
            $table->boolean('stackable_with_coupons')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
```

`database/migrations/2026_10_03_100002_create_promotion_category_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_category', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_category');
    }
};
```

`database/migrations/2026_10_03_100003_create_promotion_product_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_product');
    }
};
```

`database/migrations/2026_10_03_100004_add_promotion_to_order_items_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('product_variant_id')->nullOnDelete();
            $table->string('promotion_name')->nullable()->after('variant_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn('promotion_name');
        });
    }
};
```

- [ ] **Step 2: Écrire le test en échec**

`tests/Unit/PromotionModelTest.php` :

```php
<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    private function promotion(array $attributes = []): Promotion
    {
        return Promotion::create(array_merge([
            'name'      => '3 t-shirts pour 10 000 F',
            'lot_qty'   => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ], $attributes));
    }

    // ================================================================
    // matchesPrice()
    // ================================================================

    public function test_matches_price_inside_band(): void
    {
        $this->assertTrue($this->promotion()->matchesPrice(4200));
    }

    public function test_matches_price_on_exact_bounds(): void
    {
        $promotion = $this->promotion();

        $this->assertTrue($promotion->matchesPrice(4000));
        $this->assertTrue($promotion->matchesPrice(4499));
    }

    public function test_rejects_price_outside_band(): void
    {
        $promotion = $this->promotion();

        $this->assertFalse($promotion->matchesPrice(3999));
        $this->assertFalse($promotion->matchesPrice(4500));
    }

    public function test_null_bounds_accept_any_price(): void
    {
        $promotion = $this->promotion(['price_min' => null, 'price_max' => null]);

        $this->assertTrue($promotion->matchesPrice(1));
        $this->assertTrue($promotion->matchesPrice(999999));
    }

    // ================================================================
    // eligibleCategoryIds()
    // ================================================================

    public function test_eligible_category_ids_include_descendants_by_default(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $child  = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts', 'parent_id' => $parent->id]);

        $promotion = $this->promotion();
        $promotion->categories()->attach($parent->id);

        $ids = $promotion->fresh()->eligibleCategoryIds();

        $this->assertContains($parent->id, $ids);
        $this->assertContains($child->id, $ids);
    }

    public function test_eligible_category_ids_exclude_descendants_when_disabled(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $child  = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts', 'parent_id' => $parent->id]);

        $promotion = $this->promotion(['include_descendants' => false]);
        $promotion->categories()->attach($parent->id);

        $ids = $promotion->fresh()->eligibleCategoryIds();

        $this->assertContains($parent->id, $ids);
        $this->assertNotContains($child->id, $ids);
    }

    // ================================================================
    // scopeValid() et statut
    // ================================================================

    public function test_valid_scope_excludes_inactive(): void
    {
        $this->promotion(['is_active' => false]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_excludes_expired(): void
    {
        $this->promotion(['expires_at' => now()->subDay()]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_excludes_not_yet_started(): void
    {
        $this->promotion(['starts_at' => now()->addDay()]);

        $this->assertCount(0, Promotion::valid()->get());
    }

    public function test_valid_scope_includes_open_window(): void
    {
        $this->promotion(['starts_at' => now()->subDay(), 'expires_at' => now()->addDay()]);

        $this->assertCount(1, Promotion::valid()->get());
    }

    public function test_status_reflects_lifecycle(): void
    {
        $this->assertSame('active',    $this->promotion()->status);
        $this->assertSame('inactive',  $this->promotion(['is_active' => false])->status);
        $this->assertSame('expired',   $this->promotion(['expires_at' => now()->subDay()])->status);
        $this->assertSame('scheduled', $this->promotion(['starts_at' => now()->addDay()])->status);
    }

    // ================================================================
    // scopeResolutionOrder()
    // ================================================================

    public function test_resolution_order_sorts_by_priority_then_lot_price(): void
    {
        $low      = $this->promotion(['name' => 'basse',    'priority' => 0, 'lot_price' => 9000]);
        $high     = $this->promotion(['name' => 'haute',    'priority' => 5, 'lot_price' => 11000]);
        $cheapest = $this->promotion(['name' => 'pas cher', 'priority' => 0, 'lot_price' => 8000]);

        $names = Promotion::resolutionOrder()->pluck('name')->all();

        $this->assertSame(['haute', 'pas cher', 'basse'], $names);
    }
}
```

- [ ] **Step 3: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Unit/PromotionModelTest.php`
Expected: FAIL — `Class "App\Models\Promotion" not found`

- [ ] **Step 4: Écrire le modèle**

`app/Models/Promotion.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'lot_qty',
        'lot_price',
        'price_min',
        'price_max',
        'max_lots_per_order',
        'include_descendants',
        'stackable_with_coupons',
        'is_active',
        'starts_at',
        'expires_at',
        'priority',
    ];

    protected $casts = [
        'lot_qty'                => 'integer',
        'lot_price'              => 'decimal:2',
        'price_min'              => 'decimal:2',
        'price_max'              => 'decimal:2',
        'max_lots_per_order'     => 'integer',
        'include_descendants'    => 'boolean',
        'stackable_with_coupons' => 'boolean',
        'is_active'              => 'boolean',
        'starts_at'              => 'datetime',
        'expires_at'             => 'datetime',
        'priority'               => 'integer',
    ];

    // ========== RELATIONS ==========

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ========== SCOPES ==========

    /**
     * Offres actives et dans leur fenêtre de validité.
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Ordre de résolution du moteur. Doit être total et reproductible : sans quoi
     * le prix affiché dépendrait de l'ordre de lecture en base. À priorité égale,
     * le prix de lot le plus bas passe d'abord — l'offre la plus avantageuse pour
     * le client consomme les unités.
     */
    public function scopeResolutionOrder($query)
    {
        return $query->orderByDesc('priority')->orderBy('lot_price')->orderBy('id');
    }

    // ========== ÉLIGIBILITÉ ==========

    /**
     * Le prix tombe-t-il dans la fourchette ? Une borne nulle ne contraint pas.
     */
    public function matchesPrice(float $price): bool
    {
        if ($this->price_min !== null && $price < (float) $this->price_min) {
            return false;
        }

        if ($this->price_max !== null && $price > (float) $this->price_max) {
            return false;
        }

        return true;
    }

    /**
     * IDs des catégories couvertes, descendants compris si include_descendants.
     *
     * @return int[]
     */
    public function eligibleCategoryIds(): array
    {
        $ids = [];

        foreach ($this->categories as $category) {
            $ids = array_merge(
                $ids,
                $this->include_descendants ? $category->getAllChildrenIds() : [$category->id]
            );
        }

        return array_values(array_unique($ids));
    }

    // ========== ACCESSORS ==========

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        return 'active';
    }
}
```

- [ ] **Step 5: Déclarer la relation sur `OrderItem`**

Dans `app/Models/OrderItem.php`, ajouter `'promotion_id'` et `'promotion_name'` au tableau `$fillable`, puis ajouter la relation parmi les autres :

```php
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
```

- [ ] **Step 6: Lancer le test pour vérifier qu'il passe**

Run: `vendor/bin/phpunit tests/Unit/PromotionModelTest.php`
Expected: PASS — 12 tests

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Promotion.php app/Models/OrderItem.php
git add database/migrations/2026_10_03_1000*.php app/Models/Promotion.php app/Models/OrderItem.php tests/Unit/PromotionModelTest.php
git commit -m "feat(promotions): table, pivots et modele Promotion

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 2: Objets valeur `PricedLine` et `CartPricing`

**Files:**
- Create: `app/Support/PricedLine.php`
- Create: `app/Support/CartPricing.php`
- Test: `tests/Unit/CartPricingTest.php`

**Interfaces:**
- Consumes: `App\Models\Promotion` (Task 1), `App\Models\CartItem` (existant).
- Produces:
  - `PricedLine::__construct(int $cartItemId, int $quantity, float $unitPrice, float $discount, ?Promotion $promotion = null)` — propriétés publiques en lecture seule de même nom
  - `PricedLine::catalogTotal(): float` — `unitPrice × quantity`
  - `PricedLine::lineTotal(): float` — `catalogTotal() − discount`
  - `CartPricing::__construct(array $lines, float $couponDiscount = 0.0)` — `$lines` indexé par `cart_item_id`
  - `CartPricing::subtotal(): float`, `bundleDiscount(): float`, `payableSubtotal(): float`, `couponEligibleBase(): float`, `total(): float`
  - `CartPricing::lineFor(CartItem $item): PricedLine`
  - `CartPricing::withCouponDiscount(float $discount): self`
  - Propriété publique `CartPricing::$couponDiscount`

- [ ] **Step 1: Écrire le test en échec**

`tests/Unit/CartPricingTest.php` :

```php
<?php

namespace Tests\Unit;

use App\Models\Promotion;
use App\Support\CartPricing;
use App\Support\PricedLine;
use PHPUnit\Framework\TestCase;

class CartPricingTest extends TestCase
{
    private function promotion(bool $stackable = false): Promotion
    {
        $promotion = new Promotion([
            'name'                   => 'Offre test',
            'lot_qty'                => 3,
            'lot_price'              => 10000,
            'stackable_with_coupons' => $stackable,
        ]);

        return $promotion;
    }

    // ================================================================
    // PricedLine
    // ================================================================

    public function test_line_catalog_total_ignores_discount(): void
    {
        $line = new PricedLine(1, 3, 4000, 2000);

        $this->assertSame(12000.0, $line->catalogTotal());
    }

    public function test_line_total_subtracts_discount(): void
    {
        $line = new PricedLine(1, 3, 4000, 2000);

        $this->assertSame(10000.0, $line->lineTotal());
    }

    // ================================================================
    // CartPricing — totaux
    // ================================================================

    public function test_subtotal_is_catalog_sum(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(15000.0, $pricing->subtotal());
    }

    public function test_bundle_discount_sums_line_discounts(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(2000.0, $pricing->bundleDiscount());
    }

    public function test_payable_subtotal_is_net_of_bundle(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(13000.0, $pricing->payableSubtotal());
    }

    // ================================================================
    // CartPricing — base du coupon
    // ================================================================

    public function test_coupon_base_excludes_promoted_lines(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion()),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(3000.0, $pricing->couponEligibleBase());
    }

    public function test_coupon_base_includes_stackable_promoted_lines(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion(stackable: true)),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(13000.0, $pricing->couponEligibleBase());
    }

    // ================================================================
    // CartPricing — total
    // ================================================================

    public function test_total_subtracts_both_discounts(): void
    {
        $pricing = (new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion()),
            2 => new PricedLine(2, 1, 3000, 0),
        ]))->withCouponDiscount(600);

        $this->assertSame(12400.0, $pricing->total());
    }

    public function test_total_never_goes_negative(): void
    {
        $pricing = (new CartPricing([
            1 => new PricedLine(1, 1, 1000, 1000),
        ]))->withCouponDiscount(5000);

        $this->assertSame(0.0, $pricing->total());
    }

    public function test_with_coupon_discount_preserves_lines(): void
    {
        $original = new CartPricing([1 => new PricedLine(1, 2, 5000, 0)]);
        $updated  = $original->withCouponDiscount(1000);

        $this->assertSame(0.0, $original->couponDiscount);
        $this->assertSame(1000.0, $updated->couponDiscount);
        $this->assertSame(10000.0, $updated->subtotal());
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Unit/CartPricingTest.php`
Expected: FAIL — `Class "App\Support\PricedLine" not found`

- [ ] **Step 3: Écrire `PricedLine`**

`app/Support/PricedLine.php` :

```php
<?php

namespace App\Support;

use App\Models\Promotion;

/**
 * Une ligne de panier tarifée. Le prix unitaire reste le prix catalogue ;
 * la remise de lot est portée à part, ce qui permet d'afficher « 10 000 F
 * au lieu de 12 000 » sans avoir détruit le prix d'origine.
 */
final class PricedLine
{
    public function __construct(
        public readonly int $cartItemId,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $discount,
        public readonly ?Promotion $promotion = null,
    ) {
    }

    public function catalogTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function lineTotal(): float
    {
        return $this->catalogTotal() - $this->discount;
    }
}
```

- [ ] **Step 4: Écrire `CartPricing`**

`app/Support/CartPricing.php` :

```php
<?php

namespace App\Support;

use App\Models\CartItem;
use RuntimeException;

/**
 * Totaux d'un panier. Immuable : le moteur construit d'abord les lignes, puis
 * dérive la remise coupon de la base éligible via withCouponDiscount().
 */
final class CartPricing
{
    /**
     * @param array<int, PricedLine> $lines indexé par cart_item_id
     */
    public function __construct(
        public readonly array $lines,
        public readonly float $couponDiscount = 0.0,
    ) {
    }

    /** Somme catalogue, avant toute remise. */
    public function subtotal(): float
    {
        return array_sum(array_map(fn (PricedLine $line) => $line->catalogTotal(), $this->lines));
    }

    public function bundleDiscount(): float
    {
        return array_sum(array_map(fn (PricedLine $line) => $line->discount, $this->lines));
    }

    /** Ce que le client paie avant coupon, frais de port et taxes. */
    public function payableSubtotal(): float
    {
        return $this->subtotal() - $this->bundleDiscount();
    }

    /**
     * Assiette du coupon. Les lignes en offre en sont exclues, sauf si l'offre
     * est explicitement déclarée cumulable.
     */
    public function couponEligibleBase(): float
    {
        return array_sum(array_map(
            fn (PricedLine $line) => $line->promotion === null || $line->promotion->stackable_with_coupons
                ? $line->lineTotal()
                : 0.0,
            $this->lines
        ));
    }

    public function total(): float
    {
        return max(0, $this->payableSubtotal() - $this->couponDiscount);
    }

    public function lineFor(CartItem $item): PricedLine
    {
        if (!isset($this->lines[$item->id])) {
            throw new RuntimeException("Aucune ligne tarifée pour l'article de panier {$item->id}.");
        }

        return $this->lines[$item->id];
    }

    public function withCouponDiscount(float $discount): self
    {
        return new self($this->lines, $discount);
    }
}
```

- [ ] **Step 5: Lancer le test pour vérifier qu'il passe**

Run: `vendor/bin/phpunit tests/Unit/CartPricingTest.php`
Expected: PASS — 10 tests

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint app/Support
git add app/Support tests/Unit/CartPricingTest.php
git commit -m "feat(promotions): objets valeur PricedLine et CartPricing

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 3: Moteur — vivier d'unités éligibles

**Files:**
- Create: `app/Services/BundlePricingService.php`
- Test: `tests/Unit/BundlePricingServiceTest.php`

**Interfaces:**
- Consumes: `Promotion::valid()`, `Promotion::resolutionOrder()`, `Promotion::matchesPrice()`, `Promotion::eligibleCategoryIds()` (Task 1).
- Produces:
  - `BundlePricingService::pool(Promotion $promotion, iterable $items, array $consumed): array` — visibilité `protected`, exercée via une sous-classe de test. Renvoie une liste d'unités `['cart_item_id' => int, 'price' => float]` triée par prix **décroissant**.
  - `$consumed` est indexé `cart_item_id => nombre d'unités déjà consommées`.

**Note d'implémentation.** Le vivier lit `cart_items.unit_price`, et non le prix vivant du produit. Après la Task 6, `unit_price` est renseigné à l'ajout depuis `variant.sale_price ?? product.sale_price` : c'est donc bien le prix effectif exigé par la spec, et le moteur reste cohérent avec ce que le client voit même si l'exploitant change un tarif entre-temps.

- [ ] **Step 1: Écrire le test en échec**

`tests/Unit/BundlePricingServiceTest.php` — ce fichier est complété par les Tasks 4 et 5. Créer le socle et les tests d'éligibilité :

```php
<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Services\BundlePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Expose les méthodes protégées du moteur pour les tester isolément.
 */
class ProbedBundlePricingService extends BundlePricingService
{
    public function probePool(Promotion $promotion, iterable $items, array $consumed = []): array
    {
        return $this->pool($promotion, $items, $consumed);
    }

}

class BundlePricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);
    }

    // ================================================================
    // Fabriques locales — aucune factory n'existe pour ces modèles
    // ================================================================

    private function product(float $price, ?Category $category = null, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name'        => $name,
            'sku'         => 'SKU-' . fake()->unique()->numerify('######'),
            'sale_price'  => $price,
            'status'      => 'active',
            'category_id' => $category?->id ?? $this->tshirts->id,
        ]);
    }

    private function cart(): Cart
    {
        return Cart::create(['session_id' => 'test-session']);
    }

    private function addItem(Cart $cart, Product $product, int $quantity, ?float $unitPrice = null, ?ProductVariant $variant = null): CartItem
    {
        return CartItem::create([
            'cart_id'            => $cart->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity'           => $quantity,
            'unit_price'         => $unitPrice ?? $product->sale_price,
        ]);
    }

    private function promotion(array $attributes = [], ?Category $category = null): Promotion
    {
        $promotion = Promotion::create(array_merge([
            'name'      => '3 t-shirts pour 10 000 F',
            'lot_qty'   => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ], $attributes));

        $promotion->categories()->attach(($category ?? $this->tshirts)->id);

        return $promotion->fresh(['categories', 'products']);
    }

    private function service(): ProbedBundlePricingService
    {
        return new ProbedBundlePricingService();
    }

    private function items(Cart $cart)
    {
        return $cart->items()->with(['product.category', 'variant'])->get();
    }

    // ================================================================
    // pool() — éligibilité
    // ================================================================

    public function test_pool_expands_quantity_into_units(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(3, $units);
        $this->assertSame(4000.0, $units[0]['price']);
    }

    public function test_pool_sorts_units_by_price_descending(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 1);
        $this->addItem($cart, $this->product(4499), 1);
        $this->addItem($cart, $this->product(4200), 1);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertSame([4499.0, 4200.0, 4000.0], array_column($units, 'price'));
    }

    public function test_pool_excludes_price_outside_band(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 2);
        $this->addItem($cart, $this->product(5000), 2);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(2, $units);
        $this->assertSame([4000.0, 4000.0], array_column($units, 'price'));
    }

    public function test_pool_excludes_other_category(): void
    {
        $polos = Category::create(['name' => 'Polos', 'slug' => 'polos']);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000, $polos, 'Polo'), 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(0, $units);
    }

    public function test_pool_includes_descendant_category(): void
    {
        $parent = Category::create(['name' => 'Vêtements', 'slug' => 'vetements']);
        $this->tshirts->update(['parent_id' => $parent->id]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $promotion = $this->promotion([], $parent);
        $units     = $this->service()->probePool($promotion, $this->items($cart));

        $this->assertCount(3, $units);
    }

    /** Review Focus nº2 — products.category_id est nullable. */
    public function test_pool_excludes_product_without_category(): void
    {
        $cart    = $this->cart();
        $orphan  = Product::create([
            'name'        => 'Article sans catégorie',
            'sku'         => 'SKU-ORPHAN',
            'sale_price'  => 4000,
            'status'      => 'active',
            'category_id' => null,
        ]);
        $this->addItem($cart, $orphan, 3);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertCount(0, $units);
    }

    public function test_pool_includes_product_listed_explicitly(): void
    {
        $polos   = Category::create(['name' => 'Polos', 'slug' => 'polos']);
        $product = $this->product(4000, $polos, 'Polo listé');

        $cart = $this->cart();
        $this->addItem($cart, $product, 3);

        $promotion = $this->promotion();
        $promotion->products()->attach($product->id);

        $units = $this->service()->probePool($promotion->fresh(['categories', 'products']), $this->items($cart));

        $this->assertCount(3, $units);
    }

    public function test_pool_uses_variant_price_stored_on_the_item(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-VAR-L',
            'name'       => 'L',
            'sale_price' => 4200,
        ]);

        $cart = $this->cart();
        $this->addItem($cart, $product, 2, 4200, $variant);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart));

        $this->assertSame([4200.0, 4200.0], array_column($units, 'price'));
    }

    public function test_pool_skips_already_consumed_units(): void
    {
        $cart = $this->cart();
        $item = $this->addItem($cart, $this->product(4000), 5);

        $units = $this->service()->probePool($this->promotion(), $this->items($cart), [$item->id => 3]);

        $this->assertCount(2, $units);
    }

    public function test_pool_is_empty_for_empty_cart(): void
    {
        $units = $this->service()->probePool($this->promotion(), $this->items($this->cart()));

        $this->assertSame([], $units);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: FAIL — `Class "App\Services\BundlePricingService" not found`

- [ ] **Step 3: Écrire le squelette du service avec `pool()`**

`app/Services/BundlePricingService.php` :

```php
<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Promotion;

/**
 * Moteur unique des offres par lot. Prend un panier, rend des remises par ligne.
 *
 * Remplace les cinq implémentations dispersées de la règle de prix en gros :
 * Product::getBulkUnitPrice, Category::getBulkUnitPrice, Cart::recalcBulkPrices,
 * Cart::recalcCategoryBulkPrices et le calcul JavaScript de Shop/Product.vue.
 */
class BundlePricingService
{
    /**
     * Unités éligibles à une promotion, triées par prix décroissant.
     *
     * Le tri décroissant met les articles les plus chers dans le lot : la remise
     * est maximale pour le client, ce qui est l'usage du commerce.
     *
     * @param  iterable<CartItem>  $items
     * @param  array<int,int>      $consumed  cart_item_id => unités déjà prises
     * @return array<int, array{cart_item_id:int, price:float}>
     */
    protected function pool(Promotion $promotion, iterable $items, array $consumed): array
    {
        $categoryIds = $promotion->eligibleCategoryIds();
        $productIds  = $promotion->products->pluck('id')->all();

        $units = [];

        foreach ($items as $item) {
            if (!$this->isEligible($item, $categoryIds, $productIds)) {
                continue;
            }

            $price = (float) $item->unit_price;

            if (!$promotion->matchesPrice($price)) {
                continue;
            }

            $available = $item->quantity - ($consumed[$item->id] ?? 0);

            for ($i = 0; $i < $available; $i++) {
                $units[] = ['cart_item_id' => $item->id, 'price' => $price];
            }
        }

        usort($units, fn (array $a, array $b) => $b['price'] <=> $a['price']);

        return $units;
    }

    /**
     * Un produit nommé explicitement, ou rattaché à une catégorie couverte.
     * Un produit sans catégorie n'est jamais capté par une offre de catégorie.
     *
     * @param  int[]  $categoryIds
     * @param  int[]  $productIds
     */
    protected function isEligible(CartItem $item, array $categoryIds, array $productIds): bool
    {
        if (in_array($item->product_id, $productIds, true)) {
            return true;
        }

        return $item->product->category_id !== null
            && in_array($item->product->category_id, $categoryIds, true);
    }
}
```

- [ ] **Step 4: Lancer le test pour vérifier qu'il passe**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: PASS — 11 tests

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint app/Services/BundlePricingService.php
git add app/Services/BundlePricingService.php tests/Unit/BundlePricingServiceTest.php
git commit -m "feat(promotions): vivier d'unites eligibles du moteur de lots

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 4: Moteur — lots, remise et répartition entière

**Files:**
- Modify: `app/Services/BundlePricingService.php`
- Test: `tests/Unit/BundlePricingServiceTest.php`

**Interfaces:**
- Consumes: `pool()` (Task 3).
- Produces:
  - `BundlePricingService::distribute(float $total, array $weights): array` — `protected`. Répartit `$total` au prorata de `$weights`, en **entiers**, somme exactement égale à `(int) round($total)`. Clés préservées et réordonnées.
  - `BundlePricingService::applyPromotion(Promotion $promotion, array $units): array` — `protected`. Renvoie `['discounts' => array<int,float>, 'consumed' => array<int,int>]`, les deux indexés par `cart_item_id`.

- [ ] **Step 1: Exposer les deux méthodes dans la sous-classe de test**

Dans `tests/Unit/BundlePricingServiceTest.php`, compléter `ProbedBundlePricingService` :

```php
    public function probeDistribute(float $total, array $weights): array
    {
        return $this->distribute($total, $weights);
    }

    public function probeApplyPromotion(Promotion $promotion, array $units): array
    {
        return $this->applyPromotion($promotion, $units);
    }
```

- [ ] **Step 2: Écrire les tests de répartition en échec**

Ajouter à `BundlePricingServiceTest` :

```php
    // ================================================================
    // distribute() — méthode du plus grand reste
    // ================================================================

    public function test_distribute_sums_exactly_to_total(): void
    {
        $shares = $this->service()->probeDistribute(2699, [4499, 4200, 4000]);

        $this->assertSame(2699, array_sum($shares));
    }

    /** Le cas normatif de la spec §4. */
    public function test_distribute_matches_spec_example(): void
    {
        $shares = $this->service()->probeDistribute(2699, [4499, 4200, 4000]);

        $this->assertSame([956, 893, 850], array_values($shares));
    }

    public function test_distribute_returns_only_integers(): void
    {
        $shares = $this->service()->probeDistribute(2000, [4000, 4000, 4000]);

        foreach ($shares as $share) {
            $this->assertIsInt($share);
        }
    }

    public function test_distribute_handles_equal_weights(): void
    {
        $shares = $this->service()->probeDistribute(2000, [4000, 4000, 4000]);

        $this->assertSame(2000, array_sum($shares));
        $this->assertSame([667, 667, 666], array_values($shares));
    }

    public function test_distribute_returns_zeros_for_zero_total(): void
    {
        $shares = $this->service()->probeDistribute(0, [4000, 4000, 4000]);

        $this->assertSame([0, 0, 0], array_values($shares));
    }

    public function test_distribute_returns_zeros_for_zero_weights(): void
    {
        $shares = $this->service()->probeDistribute(500, [0, 0]);

        $this->assertSame([0, 0], array_values($shares));
    }

    // ================================================================
    // applyPromotion() — quantités
    // ================================================================

    private function unitsOf(float $price, int $count): array
    {
        return array_fill(0, $count, ['cart_item_id' => 1, 'price' => $price]);
    }

    public function test_two_units_get_no_discount(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 2));

        $this->assertSame([], $result['discounts']);
        $this->assertSame([], $result['consumed']);
    }

    public function test_three_units_discount_down_to_lot_price(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 3));

        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }

    public function test_five_units_discount_one_lot_only(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 5));

        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }

    public function test_six_units_discount_two_lots(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 6));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    public function test_seven_units_discount_two_lots(): void
    {
        $result = $this->service()->probeApplyPromotion($this->promotion(), $this->unitsOf(4000, 7));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    public function test_max_lots_per_order_caps_the_discount(): void
    {
        $promotion = $this->promotion(['max_lots_per_order' => 2]);
        $result    = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 9));

        $this->assertSame(4000.0, array_sum($result['discounts']));
        $this->assertSame(6, array_sum($result['consumed']));
    }

    /** Review Focus nº5 — lot_qty supérieur au panier. */
    public function test_lot_larger_than_cart_yields_nothing(): void
    {
        $promotion = $this->promotion(['lot_qty' => 5]);
        $result    = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 3));

        $this->assertSame([], $result['discounts']);
    }

    // ================================================================
    // applyPromotion() — arithmétique
    // ================================================================

    public function test_lot_price_above_catalog_sum_yields_no_discount(): void
    {
        $promotion = $this->promotion(['price_min' => null, 'price_max' => null, 'lot_price' => 10000]);
        $result    = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(3000, 3));

        $this->assertSame(0.0, array_sum($result['discounts']));
    }

    /** Review Focus nº3 — « 3 pour 0 ». */
    public function test_zero_lot_price_discounts_the_whole_lot(): void
    {
        $promotion = $this->promotion(['lot_price' => 0]);
        $result    = $this->service()->probeApplyPromotion($promotion, $this->unitsOf(4000, 3));

        $this->assertSame(12000.0, array_sum($result['discounts']));
    }

    public function test_discount_aggregates_per_cart_item(): void
    {
        $units = [
            ['cart_item_id' => 7, 'price' => 4000],
            ['cart_item_id' => 7, 'price' => 4000],
            ['cart_item_id' => 9, 'price' => 4000],
        ];

        $result = $this->service()->probeApplyPromotion($this->promotion(), $units);

        $this->assertArrayHasKey(7, $result['discounts']);
        $this->assertArrayHasKey(9, $result['discounts']);
        $this->assertSame(2, $result['consumed'][7]);
        $this->assertSame(1, $result['consumed'][9]);
        $this->assertSame(2000.0, array_sum($result['discounts']));
    }

    /** Review Focus nº4 — même produit, deux variantes, deux lignes de panier. */
    public function test_same_product_in_two_variants_shares_one_pool(): void
    {
        $product = $this->product(4000);

        $medium = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-VAR-M',
            'name'       => 'M',
            'sale_price' => 4000,
        ]);

        $large = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-VAR-L2',
            'name'       => 'L',
            'sale_price' => 4000,
        ]);

        $cart = $this->cart();
        $this->addItem($cart, $product, 1, 4000, $medium);
        $this->addItem($cart, $product, 2, 4000, $large);

        $units  = $this->service()->probePool($this->promotion(), $this->items($cart));
        $result = $this->service()->probeApplyPromotion($this->promotion(), $units);

        $this->assertCount(3, $units);
        $this->assertSame(2000.0, array_sum($result['discounts']));
        $this->assertSame(3, array_sum($result['consumed']));
    }
```

- [ ] **Step 3: Lancer les tests pour vérifier qu'ils échouent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: FAIL — `Call to undefined method …::distribute()`

- [ ] **Step 4: Implémenter `distribute()` et `applyPromotion()`**

Ajouter à `app/Services/BundlePricingService.php` :

```php
    /**
     * Applique une promotion à un vivier et rend les remises par ligne de panier.
     *
     * @param  array<int, array{cart_item_id:int, price:float}>  $units  trié par prix décroissant
     * @return array{discounts: array<int,float>, consumed: array<int,int>}
     */
    protected function applyPromotion(Promotion $promotion, array $units): array
    {
        $lotQty = (int) $promotion->lot_qty;

        if ($lotQty < 1) {
            return ['discounts' => [], 'consumed' => []];
        }

        $lots = intdiv(count($units), $lotQty);

        if ($promotion->max_lots_per_order !== null) {
            $lots = min($lots, (int) $promotion->max_lots_per_order);
        }

        $discounts = [];
        $consumed  = [];

        for ($lot = 0; $lot < $lots; $lot++) {
            $lotUnits = array_slice($units, $lot * $lotQty, $lotQty);
            $prices   = array_column($lotUnits, 'price');

            // max(0, …) : une offre mal saisie ne renchérit jamais le panier.
            $discount = max(0, array_sum($prices) - (float) $promotion->lot_price);

            foreach ($this->distribute($discount, $prices) as $index => $share) {
                $id = $lotUnits[$index]['cart_item_id'];

                $discounts[$id] = ($discounts[$id] ?? 0) + $share;
                $consumed[$id]  = ($consumed[$id] ?? 0) + 1;
            }
        }

        return ['discounts' => $discounts, 'consumed' => $consumed];
    }

    /**
     * Répartit un montant au prorata de poids, en entiers, par la méthode du plus
     * grand reste. La somme des parts vaut exactement le montant : en F CFA, qui
     * n'a pas de sous-unité, un arrondi ligne par ligne ferait dériver le total
     * du lot de son prix annoncé.
     *
     * @param  float[]  $weights
     * @return int[]    mêmes clés que $weights
     */
    protected function distribute(float $total, array $weights): array
    {
        $target = (int) round($total);
        $sum    = array_sum($weights);

        if ($target <= 0 || $sum <= 0) {
            return array_map(fn () => 0, $weights);
        }

        $shares     = [];
        $remainders = [];
        $assigned   = 0;

        foreach ($weights as $index => $weight) {
            $exact              = $target * $weight / $sum;
            $shares[$index]     = (int) floor($exact);
            $remainders[$index] = $exact - $shares[$index];
            $assigned          += $shares[$index];
        }

        // Les unités restantes vont aux plus grands restes.
        arsort($remainders);

        foreach (array_keys($remainders) as $index) {
            if ($assigned >= $target) {
                break;
            }

            $shares[$index]++;
            $assigned++;
        }

        ksort($shares);

        return $shares;
    }
```

- [ ] **Step 5: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: PASS — 29 tests

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint app/Services/BundlePricingService.php
git add app/Services/BundlePricingService.php tests/Unit/BundlePricingServiceTest.php
git commit -m "feat(promotions): calcul des lots et repartition entiere de la remise

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 5: Moteur — `price()`, coupon et totaux

**Files:**
- Modify: `app/Services/BundlePricingService.php`
- Test: `tests/Unit/BundlePricingServiceTest.php`

**Interfaces:**
- Consumes: `pool()`, `applyPromotion()` (Tasks 3-4) ; `CartPricing`, `PricedLine` (Task 2) ; `Coupon::calculateDiscount(float $amount): float` (existant, `app/Models/Coupon.php`).
- Produces:
  - `BundlePricingService::price(Cart $cart): CartPricing` — **public**.
  - Une ligne touchée par plusieurs offres porte l'id de la **première** dans l'ordre de résolution. Limite assumée : `order_items.promotion_id` est une colonne unique, et c'est l'offre la plus prioritaire qui est la plus représentative.

- [ ] **Step 1: Écrire les tests en échec**

Ajouter à `BundlePricingServiceTest` :

```php
    // ================================================================
    // price() — bout en bout
    // ================================================================

    /** Review Focus nº1 — panier vide. */
    public function test_price_of_empty_cart_is_all_zero(): void
    {
        $pricing = $this->service()->price($this->cart());

        $this->assertSame([], $pricing->lines);
        $this->assertSame(0.0, $pricing->subtotal());
        $this->assertSame(0.0, $pricing->bundleDiscount());
        $this->assertSame(0.0, $pricing->total());
    }

    public function test_price_without_any_promotion_leaves_catalog_total(): void
    {
        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $pricing = $this->service()->price($cart);

        $this->assertSame(12000.0, $pricing->subtotal());
        $this->assertSame(0.0, $pricing->bundleDiscount());
        $this->assertSame(12000.0, $pricing->total());
    }

    public function test_price_applies_lot_to_three_units(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $pricing = $this->service()->price($cart);

        $this->assertSame(12000.0, $pricing->subtotal());
        $this->assertSame(2000.0, $pricing->bundleDiscount());
        $this->assertSame(10000.0, $pricing->total());
    }

    public function test_price_leaves_remainder_at_catalog(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 5);

        $pricing = $this->service()->price($cart);

        $this->assertSame(18000.0, $pricing->total());
    }

    public function test_price_repeats_lots(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 6);

        $pricing = $this->service()->price($cart);

        $this->assertSame(20000.0, $pricing->total());
    }

    /** Cas normatif de la spec §4 : lot sur les trois unités les plus chères. */
    public function test_price_puts_most_expensive_units_in_the_lot(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4499, null, 'Graphic'), 1);
        $this->addItem($cart, $this->product(4200, null, 'Bleu'), 1);
        $this->addItem($cart, $this->product(4000, null, 'Rouge'), 2);

        $pricing = $this->service()->price($cart);

        $this->assertSame(16699.0, $pricing->subtotal());
        $this->assertSame(2699.0, $pricing->bundleDiscount());
        $this->assertSame(14000.0, $pricing->total());
    }

    public function test_price_ignores_items_outside_the_band(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);
        $this->addItem($cart, $this->product(5000, null, 'Premium'), 1);

        $pricing = $this->service()->price($cart);

        $this->assertSame(15000.0, $pricing->total());
    }

    public function test_price_handles_two_bands_independently(): void
    {
        $this->promotion(['name' => 'Palier 4000']);
        $this->promotion([
            'name'      => 'Palier 5000',
            'price_min' => 5000,
            'price_max' => 5499,
            'lot_price' => 12000,
        ]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);
        $this->addItem($cart, $this->product(5000, null, 'Premium'), 3);

        $pricing = $this->service()->price($cart);

        $this->assertSame(27000.0, $pricing->subtotal());
        $this->assertSame(5000.0, $pricing->bundleDiscount());
        $this->assertSame(22000.0, $pricing->total());
    }

    public function test_price_ignores_expired_promotion(): void
    {
        $this->promotion(['expires_at' => now()->subDay()]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $this->assertSame(12000.0, $this->service()->price($cart)->total());
    }

    public function test_price_ignores_inactive_promotion(): void
    {
        $this->promotion(['is_active' => false]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $this->assertSame(12000.0, $this->service()->price($cart)->total());
    }

    public function test_price_never_consumes_a_unit_twice(): void
    {
        $this->promotion(['name' => 'Offre A', 'priority' => 10]);
        $this->promotion(['name' => 'Offre B', 'priority' => 0]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $pricing = $this->service()->price($cart);

        // Une seule offre peut remiser ces trois unités.
        $this->assertSame(2000.0, $pricing->bundleDiscount());
    }

    public function test_price_tags_the_line_with_its_promotion(): void
    {
        $promotion = $this->promotion();

        $cart = $this->cart();
        $item = $this->addItem($cart, $this->product(4000), 3);

        $line = $this->service()->price($cart)->lineFor($item);

        $this->assertNotNull($line->promotion);
        $this->assertSame($promotion->id, $line->promotion->id);
    }

    // ================================================================
    // price() — interaction coupon
    // ================================================================

    private function coupon(array $attributes = []): \App\Models\Coupon
    {
        return \App\Models\Coupon::create(array_merge([
            'code'        => 'PROMO20',
            'name'        => 'Vingt pour cent',
            'type'        => 'percentage',
            'value'       => 20,
            'is_active'   => true,
            'usage_count' => 0,
        ], $attributes));
    }

    public function test_coupon_skips_promoted_lines(): void
    {
        $this->promotion();
        $this->coupon();

        $accessories = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);
        $this->addItem($cart, $this->product(3000, $accessories, 'Casquette'), 1);
        $cart->update(['coupon_code' => 'PROMO20']);

        $pricing = $this->service()->price($cart->fresh());

        $this->assertSame(2000.0, $pricing->bundleDiscount());
        $this->assertSame(600.0, $pricing->couponDiscount);
        $this->assertSame(12400.0, $pricing->total());
    }

    public function test_stackable_promotion_lets_the_coupon_through(): void
    {
        $this->promotion(['stackable_with_coupons' => true]);
        $this->coupon();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);
        $cart->update(['coupon_code' => 'PROMO20']);

        $pricing = $this->service()->price($cart->fresh());

        $this->assertSame(2000.0, $pricing->bundleDiscount());
        $this->assertSame(2000.0, $pricing->couponDiscount);
        $this->assertSame(8000.0, $pricing->total());
    }

    public function test_no_coupon_means_no_coupon_discount(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $this->assertSame(0.0, $this->service()->price($cart)->couponDiscount);
    }
}
```

> **Attention :** le `}` final ferme la classe. En ajoutant ces tests, remplacer l'accolade fermante existante plutôt que d'en ajouter une seconde.

- [ ] **Step 2: Lancer les tests pour vérifier qu'ils échouent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: FAIL — `Call to undefined method App\Services\BundlePricingService::price()`

- [ ] **Step 3: Implémenter `price()`**

Ajouter les imports en tête de `app/Services/BundlePricingService.php` :

```php
use App\Models\Cart;
use App\Support\CartPricing;
use App\Support\PricedLine;
```

Puis la méthode, en première position publique de la classe :

```php
    /**
     * Tarifie un panier : une passe par offre, dans l'ordre de résolution, chaque
     * unité ne pouvant être remisée qu'une fois.
     */
    public function price(Cart $cart): CartPricing
    {
        $items = $cart->items()->with(['product.category', 'variant'])->get();

        if ($items->isEmpty()) {
            return new CartPricing([]);
        }

        $discounts  = [];   // cart_item_id => remise cumulée
        $promotions = [];   // cart_item_id => première offre appliquée
        $consumed   = [];   // cart_item_id => unités déjà prises

        $candidates = Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->get();

        foreach ($candidates as $promotion) {
            $result = $this->applyPromotion($promotion, $this->pool($promotion, $items, $consumed));

            foreach ($result['discounts'] as $id => $share) {
                $discounts[$id]    = ($discounts[$id] ?? 0) + $share;
                $promotions[$id] ??= $promotion;
            }

            foreach ($result['consumed'] as $id => $count) {
                $consumed[$id] = ($consumed[$id] ?? 0) + $count;
            }
        }

        $lines = [];

        foreach ($items as $item) {
            $lines[$item->id] = new PricedLine(
                cartItemId: $item->id,
                quantity:   (int) $item->quantity,
                unitPrice:  (float) $item->unit_price,
                discount:   (float) ($discounts[$item->id] ?? 0),
                promotion:  $promotions[$item->id] ?? null,
            );
        }

        $pricing = new CartPricing($lines);

        return $pricing->withCouponDiscount($this->couponDiscount($cart, $pricing));
    }

    /**
     * Remise coupon, calculée sur la seule base éligible : les lignes en offre en
     * sont exclues sauf offre cumulable.
     */
    protected function couponDiscount(Cart $cart, CartPricing $pricing): float
    {
        if (!$cart->coupon_code || !$cart->coupon) {
            return 0.0;
        }

        return (float) $cart->coupon->calculateDiscount($pricing->couponEligibleBase());
    }
```

- [ ] **Step 4: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: PASS — 45 tests

- [ ] **Step 5: Lancer toute la suite pour vérifier qu'aucune régression n'apparaît**

Run: `composer test`
Expected: PASS. Le moteur n'est pas encore branché sur `Cart`, donc les tests existants (`CouponTest`, `OrderCreationTest`, `ProductStockTest`) ne doivent pas bouger.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint app/Services/BundlePricingService.php
git add app/Services/BundlePricingService.php tests/Unit/BundlePricingServiceTest.php
git commit -m "feat(promotions): price() complet avec exclusion des coupons

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 6: Brancher `Cart` et retirer le moteur de palier

**Files:**
- Modify: `app/Models/Cart.php`
- Modify: `app/Models/CartItem.php:52-55`
- Test: `tests/Unit/CartPricingIntegrationTest.php` (créé)

**Interfaces:**
- Consumes: `BundlePricingService::price()` (Task 5).
- Produces:
  - `Cart::pricing(): CartPricing` — mémoïsé par instance
  - `Cart::getSubtotalAttribute(): float` — **total catalogue** (sémantique changée)
  - `Cart::getBundleDiscountAttribute(): float` — nouveau
  - `Cart::getPayableSubtotalAttribute(): float` — nouveau
  - `Cart::getDiscountAmountAttribute(): float` — remise coupon sur la base éligible
  - `Cart::getTotalAttribute(): float`

**Les deux moteurs ne tournent jamais ensemble.** Cette tâche supprime `recalcBulkPrices()` et `recalcCategoryBulkPrices()` et tous leurs appels. Les colonnes `bulk_pricing_rules` subsistent jusqu'à la Task 13, mais plus rien ne les lit côté panier.

- [ ] **Step 1: Écrire le test en échec**

`tests/Unit/CartPricingIntegrationTest.php` :

```php
<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPricingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);

        Promotion::create([
            'name'      => '3 t-shirts pour 10 000 F',
            'lot_qty'   => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ])->categories()->attach($this->tshirts->id);
    }

    private function product(float $price = 4000, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name'        => $name,
            'sku'         => 'SKU-' . fake()->unique()->numerify('######'),
            'sale_price'  => $price,
            'status'      => 'active',
            'category_id' => $this->tshirts->id,
        ]);
    }

    // ================================================================
    // Le prix catalogue survit à l'application de l'offre
    // ================================================================

    public function test_unit_price_is_never_mutated(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame('4000.00', $cart->items()->first()->unit_price);
    }

    public function test_subtotal_is_the_catalog_total(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(12000.0, $cart->fresh()->subtotal);
    }

    public function test_bundle_discount_is_exposed(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(2000.0, $cart->fresh()->bundle_discount);
    }

    public function test_payable_subtotal_is_net_of_bundle(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->payable_subtotal);
    }

    public function test_total_matches_the_lot_price(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    // ================================================================
    // Le prix se recalcule quand le panier change
    // ================================================================

    public function test_pricing_refreshes_after_quantity_change(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($this->product(), 2);

        $this->assertSame(8000.0, $cart->fresh()->total);

        $cart->updateItemQuantity($item->id, 3);

        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    public function test_pricing_refreshes_after_removal(): void
    {
        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($this->product(), 3);

        $this->assertSame(10000.0, $cart->fresh()->total);

        $cart->updateItemQuantity($item->id, 2);

        $this->assertSame(8000.0, $cart->fresh()->total);
    }

    public function test_adding_the_same_product_twice_aggregates_quantity(): void
    {
        $product = $this->product();

        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($product, 1);
        $cart->addItem($product, 2);

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(10000.0, $cart->fresh()->total);
    }

    // ================================================================
    // addItem retient le prix de la variante
    // ================================================================

    public function test_add_item_stores_the_variant_price(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-VAR-L',
            'name'       => 'L',
            'sale_price' => 4200,
        ]);

        $cart = Cart::create(['session_id' => 'test']);
        $item = $cart->addItem($product, 1, $variant);

        $this->assertSame('4200.00', $item->unit_price);
    }

    public function test_variant_priced_items_enter_the_lot(): void
    {
        $product = $this->product(4000);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-VAR-M',
            'name'       => 'M',
            'sale_price' => 4200,
        ]);

        $cart = Cart::create(['session_id' => 'test']);
        $cart->addItem($product, 2, $variant);
        $cart->addItem($product, 1);

        // 4200 + 4200 + 4000 = 12 400 au catalogue, lot à 10 000
        $this->assertSame(12400.0, $cart->fresh()->subtotal);
        $this->assertSame(10000.0, $cart->fresh()->total);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Unit/CartPricingIntegrationTest.php`
Expected: FAIL — `test_unit_price_is_never_mutated` échoue car `recalcBulkPrices` écrase encore `unit_price` ; `bundle_discount` et `payable_subtotal` sont inconnus.

- [ ] **Step 3: Réécrire les accesseurs de `Cart`**

Dans `app/Models/Cart.php`, ajouter les imports :

```php
use App\Services\BundlePricingService;
use App\Support\CartPricing;
```

Ajouter la propriété de cache sous `$fillable` :

```php
    protected ?CartPricing $pricingCache = null;
```

Remplacer le bloc `// ========== ACCESSORS ==========` existant (lignes 36 à 72) par :

```php
    // ========== TARIFICATION ==========

    /**
     * Tarification du panier, mémoïsée par instance. Toute mutation du panier
     * appelle forgetPricing().
     */
    public function pricing(): CartPricing
    {
        return $this->pricingCache ??= app(BundlePricingService::class)->price($this);
    }

    protected function forgetPricing(): void
    {
        $this->pricingCache = null;
    }

    // ========== ACCESSORS ==========

    /**
     * Total catalogue, avant toute remise. Attention : avant l'introduction des
     * offres par lot, cet accesseur rendait un montant déjà remisé. Les
     * consommateurs qui raisonnent sur ce que le client paie doivent lire
     * payable_subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->pricing()->subtotal();
    }

    public function getBundleDiscountAttribute(): float
    {
        return $this->pricing()->bundleDiscount();
    }

    public function getPayableSubtotalAttribute(): float
    {
        return $this->pricing()->payableSubtotal();
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->pricing()->couponDiscount;
    }

    public function getTotalAttribute(): float
    {
        return $this->pricing()->total();
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }
```

- [ ] **Step 4: Supprimer le moteur de palier et corriger `addItem`**

Dans `app/Models/Cart.php` :

1. **Supprimer entièrement** les méthodes `getTotalProductQuantity()`, `recalcBulkPrices()` et `recalcCategoryBulkPrices()` (lignes 150 à 230 de la version actuelle).

2. Remplacer `addItem()` par :

```php
    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null): CartItem
    {
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $existingItem->update(['quantity' => $existingItem->quantity + $quantity]);
            $this->forgetPricing();

            return $existingItem->fresh();
        }

        // Prix catalogue figé à l'ajout. Le prix de la variante prime sur celui
        // du produit, sans quoi une taille au tarif différent sortirait des
        // fourchettes d'offres sans raison visible.
        $item = $this->items()->create([
            'product_id'         => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity'           => $quantity,
            'unit_price'         => (float) ($variant?->sale_price ?? $product->sale_price),
        ]);

        $this->forgetPricing();

        return $item->fresh();
    }
```

3. Remplacer `updateItemQuantity()` et `removeItem()` par :

```php
    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->items()->where('id', $itemId)->delete();
        } else {
            $this->items()->where('id', $itemId)->update(['quantity' => $quantity]);
        }

        $this->forgetPricing();
    }

    public function removeItem(int $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
        $this->forgetPricing();
    }
```

4. Ajouter `$this->forgetPricing();` en fin de `clear()`, `applyCoupon()` (avant le `return true`) et `removeCoupon()`.

5. Dans `applyCoupon()`, remplacer les deux usages de `$this->subtotal` par `$this->payable_subtotal` :

```php
        $validation = $coupon->canBeUsedBy($customer, $this->payable_subtotal);
```

- [ ] **Step 5: Aligner `CartItem::getTotalAttribute`**

Dans `app/Models/CartItem.php`, documenter la sémantique devenue catalogue :

```php
    /**
     * Total catalogue de la ligne. La remise de lot n'est pas portée ici : elle
     * vit dans CartPricing, qui a besoin du panier entier pour la calculer.
     */
    public function getTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
```

- [ ] **Step 6: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Unit/CartPricingIntegrationTest.php`
Expected: PASS — 10 tests

- [ ] **Step 7: Lancer toute la suite**

Run: `composer test`
Expected: PASS. Si `OrderCreationTest` échoue sur un total, c'est attendu : la Task 7 reprend le checkout. Noter l'échec et poursuivre — ne pas modifier le test.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Models/Cart.php app/Models/CartItem.php
git add app/Models/Cart.php app/Models/CartItem.php tests/Unit/CartPricingIntegrationTest.php
git commit -m "refactor(cart): brancher le moteur de lots, retirer le moteur de palier

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 7: Persistance en commande

**Files:**
- Modify: `app/Http/Controllers/Front/CheckoutController.php:224-300`
- Modify: `app/Http/Controllers/Front/CheckoutController.php:638`
- Test: `tests/Feature/PromotionCheckoutTest.php`

**Interfaces:**
- Consumes: `Cart::pricing()` (Task 6), `CartPricing::lineFor()` (Task 2).
- Produces: `order_items.discount_amount`, `promotion_id`, `promotion_name` renseignés ; `orders.subtotal` net de lot, `orders.discount_amount` = coupon seul.

- [ ] **Step 1: Écrire le test en échec**

`tests/Feature/PromotionCheckoutTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Category $tshirts;
    private Promotion $promotion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);

        $this->promotion = Promotion::create([
            'name'      => '3 t-shirts pour 10 000 F',
            'lot_qty'   => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ]);

        $this->promotion->categories()->attach($this->tshirts->id);
    }

    private function product(float $price = 4000, string $name = 'T-shirt'): Product
    {
        return Product::create([
            'name'           => $name,
            'sku'            => 'SKU-' . fake()->unique()->numerify('######'),
            'sale_price'     => $price,
            'status'         => 'active',
            'category_id'    => $this->tshirts->id,
            'stock_quantity' => 50,
        ]);
    }

    /**
     * Champs exigés par CheckoutController::store(). `cod` est la seule méthode
     * de paiement active par défaut (payment_cod_enabled vaut '1' sans réglage).
     */
    private function checkoutPayload(): array
    {
        return [
            'phone'               => '0700000000',
            'email'               => 'awa@example.test',
            'shipping_first_name' => 'Awa',
            'shipping_last_name'  => 'Koné',
            'shipping_address'    => 'Cocody, Abidjan',
            'shipping_city'       => 'Abidjan',
            'shipping_country'    => 'CI',
            'same_billing'        => true,
            'payment_method'      => 'cod',
        ];
    }

    // ================================================================
    // Persistance de la remise de lot
    // ================================================================

    public function test_order_item_carries_the_lot_discount(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();
        $item  = $order->items()->firstOrFail();

        $this->assertSame('4000.00', $item->unit_price);
        $this->assertSame('2000.00', $item->discount_amount);
        $this->assertSame('10000.00', $item->total);
    }

    public function test_order_item_records_the_promotion(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $item = Order::latest()->firstOrFail()->items()->firstOrFail();

        $this->assertSame($this->promotion->id, $item->promotion_id);
        $this->assertSame('3 t-shirts pour 10 000 F', $item->promotion_name);
    }

    public function test_order_subtotal_is_net_of_the_lot(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        $this->assertSame('10000.00', $order->subtotal);
        $this->assertSame('0.00', $order->discount_amount);
    }

    public function test_order_total_matches_the_lot_price(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        $this->assertSame(
            (float) $order->subtotal + (float) $order->shipping_amount + (float) $order->tax_amount,
            (float) $order->total
        );
    }

    public function test_remainder_stays_at_catalog_price(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 5]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->firstOrFail();

        // 1 lot (10 000) + 2 × 4 000 = 18 000
        $this->assertSame('18000.00', $order->subtotal);
    }

    public function test_bundle_discount_is_derivable_from_items(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest()->withSum('items', 'discount_amount')->firstOrFail();

        $this->assertSame(2000.0, (float) $order->items_sum_discount_amount);
    }

    // ================================================================
    // Sans offre, rien ne change
    // ================================================================

    public function test_items_without_promotion_record_no_discount(): void
    {
        $accessories = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);

        $product = Product::create([
            'name'           => 'Casquette',
            'sku'            => 'SKU-CAP',
            'sale_price'     => 3000,
            'status'         => 'active',
            'category_id'    => $accessories->id,
            'stock_quantity' => 10,
        ]);

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $item = Order::latest()->firstOrFail()->items()->firstOrFail();

        $this->assertSame('0.00', $item->discount_amount);
        $this->assertNull($item->promotion_id);
        $this->assertSame('6000.00', $item->total);
    }

    // ================================================================
    // Écart entre le total affiché et le total encaissé
    // ================================================================

    public function test_order_is_refused_when_the_total_changed_since_the_cart(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);

        // Le client a vu 10 000 F, puis l'offre est désactivée avant qu'il valide.
        $this->promotion->update(['is_active' => false]);

        $this->post(route('checkout.store'), array_merge($this->checkoutPayload(), [
            'expected_total' => 10000,
        ]))->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_proceeds_when_the_expected_total_still_holds(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);

        $cart  = \App\Models\Cart::firstOrFail();
        $total = $cart->total;

        $this->post(route('checkout.store'), array_merge($this->checkoutPayload(), [
            'expected_total' => $total,
        ]));

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_order_proceeds_when_no_expected_total_is_sent(): void
    {
        $product = $this->product();

        $this->post('/panier/ajouter', ['product_id' => $product->id, 'quantity' => 3]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertDatabaseCount('orders', 1);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Feature/PromotionCheckoutTest.php`
Expected: FAIL — `discount_amount` vaut `0.00` au lieu de `2000.00`

- [ ] **Step 3: Lire la tarification une seule fois au checkout**

Dans `app/Http/Controllers/Front/CheckoutController.php`, remplacer le bloc de calcul (autour de la ligne 224) :

```php
            $pricing  = $cart->pricing();
            $subtotal = $pricing->payableSubtotal();   // net de lot
            $discount = $pricing->couponDiscount;      // coupon seul

            $taxAmount = $this->calculateTax($subtotal - $discount);
            $total     = $subtotal - $discount + $shippingCost + $taxAmount;

            \Log::info('Checkout: Calcul du total', [
                'subtotal'        => $subtotal,
                'bundle_discount' => $pricing->bundleDiscount(),
                'discount'        => $discount,
                'shipping'        => $shippingCost,
                'tax'             => $taxAmount,
                'total'           => $total,
            ]);
```

- [ ] **Step 4: Persister les remises sur les lignes**

Remplacer la boucle `foreach ($cart->items as $item)` (autour de la ligne 285) :

```php
            foreach ($cart->items as $item) {
                $line = $pricing->lineFor($item);

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name'               => $item->product->name,
                    'variant_name'       => $item->variant?->name,
                    'sku'                => $item->variant?->sku ?? $item->product->sku,
                    'quantity'           => $item->quantity,
                    'unit_price'         => $item->unit_price,
                    'total'              => $line->lineTotal(),
                    'tax_rate'           => $item->product->tax_rate ?? 0,
                    'tax_amount'         => 0,
                    'discount_amount'    => $line->discount,
                    'promotion_id'       => $line->promotion?->id,
                    'promotion_name'     => $line->promotion?->name,
                ]);
            }
```

- [ ] **Step 5: Repointer le seuil de livraison gratuite**

Ligne 638 environ, remplacer :

```php
        if ($freeShippingThreshold > 0 && $cart->payable_subtotal >= $freeShippingThreshold) {
            return 0;
        }
```

- [ ] **Step 6: Refuser une commande dont le total a changé**

Spec §5 et §8 : si une offre expire entre l'affichage du panier et la validation, le client ne doit jamais être débité d'un montant qu'il n'a pas vu.

Dans la validation de `store()` de `CheckoutController`, ajouter la règle :

```php
            'expected_total' => 'nullable|numeric',
```

Puis, juste après le calcul de `$total` (Step 3) et **avant** la création de la commande :

```php
            // Le front renvoie le total qu'il a affiché. Un écart signifie qu'une
            // offre a changé entre-temps : on renvoie au panier plutôt que
            // d'encaisser un montant que le client n'a pas validé. Une valeur
            // falsifiée ne provoque qu'une redirection, jamais un débit.
            $expectedTotal = $request->input('expected_total');

            if ($expectedTotal !== null && abs((float) $expectedTotal - $total) >= 1) {
                return redirect()
                    ->route('cart.index')
                    ->with('warning', 'Le montant de votre panier a changé. Vérifiez votre commande avant de valider.');
            }
```

Dans `resources/js/Pages/Checkout/Index.vue`, ajouter le champ au `useForm` :

```js
const form = useForm({
    // … champs existants
    expected_total: props.cart.total,
});
```

- [ ] **Step 7: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Feature/PromotionCheckoutTest.php`
Expected: PASS — 10 tests

- [ ] **Step 8: Lancer toute la suite**

Run: `composer test`
Expected: PASS, y compris `OrderCreationTest` qui doit redevenir vert.

- [ ] **Step 9: Construire les assets**

Run: `npm run build`

- [ ] **Step 10: Commit**

```bash
vendor/bin/pint app/Http/Controllers/Front/CheckoutController.php
git add app/Http/Controllers/Front/CheckoutController.php resources/js/Pages/Checkout/Index.vue tests/Feature/PromotionCheckoutTest.php public/build
git commit -m "feat(checkout): persister les remises de lot sur les lignes de commande

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 8: Nudges et récapitulatif du panier

**Files:**
- Modify: `app/Services/BundlePricingService.php`
- Modify: `app/Http/Controllers/Front/CartController.php:55-80`
- Modify: `app/Http/Controllers/Front/CartController.php:293-360`
- Modify: `resources/js/Pages/Cart/Index.vue`
- Test: `tests/Unit/BundlePricingServiceTest.php`

**Interfaces:**
- Consumes: `pool()` (Task 3), `Cart::pricing()` (Task 6).
- Produces: `BundlePricingService::nudges(Cart $cart): array` — **public**. Chaque entrée : `promotion_id`, `promotion_name`, `category_name`, `items_needed`, `current_qty`, `next_tier_qty`, `total_saving`, `shop_url`.

Les clés `items_needed`, `current_qty`, `next_tier_qty`, `total_saving`, `shop_url` et `category_name` sont **identiques** à celles de `computeBulkNudges()` : le composant Vue existant continue de fonctionner. Seul `product_name` devient `promotion_name`.

- [ ] **Step 1: Écrire les tests en échec**

Ajouter à `BundlePricingServiceTest`, avant l'accolade fermante :

```php
    // ================================================================
    // nudges()
    // ================================================================

    public function test_no_nudge_when_the_lot_is_complete(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 3);

        $this->assertSame([], $this->service()->nudges($cart));
    }

    public function test_no_nudge_without_any_eligible_item(): void
    {
        $this->promotion();

        $this->assertSame([], $this->service()->nudges($this->cart()));
    }

    public function test_nudge_announces_the_missing_units(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 2);

        $nudges = $this->service()->nudges($cart);

        $this->assertCount(1, $nudges);
        $this->assertSame(1, $nudges[0]['items_needed']);
        $this->assertSame(2, $nudges[0]['current_qty']);
        $this->assertSame(3, $nudges[0]['next_tier_qty']);
        $this->assertSame('3 t-shirts pour 10 000 F', $nudges[0]['promotion_name']);
    }

    public function test_nudge_states_the_saving_of_the_next_lot(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 2);

        $nudges = $this->service()->nudges($cart);

        // 3 × 4 000 = 12 000 contre un lot à 10 000
        $this->assertSame(2000.0, $nudges[0]['total_saving']);
    }

    public function test_nudge_counts_only_the_incomplete_remainder(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 5);

        $nudges = $this->service()->nudges($cart);

        $this->assertSame(1, $nudges[0]['items_needed']);
        $this->assertSame(2, $nudges[0]['current_qty']);
    }

    public function test_no_nudge_once_the_lot_cap_is_reached(): void
    {
        $this->promotion(['max_lots_per_order' => 1]);

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 5);

        $this->assertSame([], $this->service()->nudges($cart));
    }

    public function test_nudge_links_to_the_promotion_category(): void
    {
        $this->promotion();

        $cart = $this->cart();
        $this->addItem($cart, $this->product(4000), 2);

        $nudges = $this->service()->nudges($cart);

        $this->assertSame('T-shirts', $nudges[0]['category_name']);
        $this->assertSame('/boutique?category=t-shirts', $nudges[0]['shop_url']);
    }
```

- [ ] **Step 2: Lancer les tests pour vérifier qu'ils échouent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php --filter nudge`
Expected: FAIL — `Call to undefined method …::nudges()`

- [ ] **Step 3: Implémenter `nudges()`**

Ajouter à `app/Services/BundlePricingService.php`, après `price()` :

```php
    /**
     * Offres à portée de main : pour chaque promotion dont le vivier n'est pas un
     * multiple de lot_qty, combien d'unités manquent et ce que le prochain lot
     * complet ferait gagner.
     *
     * @return array<int, array{promotion_id:int, promotion_name:string,
     *                          category_name:?string, items_needed:int,
     *                          current_qty:int, next_tier_qty:int,
     *                          total_saving:float, shop_url:string}>
     */
    public function nudges(Cart $cart): array
    {
        $items = $cart->items()->with(['product.category', 'variant'])->get();

        if ($items->isEmpty()) {
            return [];
        }

        $candidates = Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->get();

        $nudges = [];

        foreach ($candidates as $promotion) {
            $units = $this->pool($promotion, $items, []);
            $count = count($units);

            if ($count === 0) {
                continue;
            }

            $lotQty    = (int) $promotion->lot_qty;
            $lots      = intdiv($count, $lotQty);
            $remainder = $count % $lotQty;

            // Lot complet : rien à suggérer.
            if ($remainder === 0) {
                continue;
            }

            // Plafond atteint : un nudge serait mensonger.
            if ($promotion->max_lots_per_order !== null && $lots >= (int) $promotion->max_lots_per_order) {
                continue;
            }

            $needed = $lotQty - $remainder;

            // Les unités manquantes sont projetées au prix de la moins chère déjà
            // présente : hypothèse prudente, l'économie annoncée n'est jamais survendue.
            $cheapest  = min(array_column($units, 'price'));
            $tail      = array_slice($units, $lots * $lotQty);
            $projected = array_sum(array_column($tail, 'price')) + $needed * $cheapest;

            $category = $promotion->categories->first();

            $nudges[] = [
                'promotion_id'   => $promotion->id,
                'promotion_name' => $promotion->name,
                'category_name'  => $category?->name,
                'items_needed'   => $needed,
                'current_qty'    => $remainder,
                'next_tier_qty'  => $lotQty,
                'total_saving'   => max(0, $projected - (float) $promotion->lot_price),
                'shop_url'       => $category ? '/boutique?category=' . $category->slug : '/boutique',
            ];
        }

        return $nudges;
    }
```

- [ ] **Step 4: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: PASS — 52 tests

- [ ] **Step 5: Brancher le contrôleur du panier**

Dans `app/Http/Controllers/Front/CartController.php` :

1. Ajouter l'import `use App\Services\BundlePricingService;`
2. **Supprimer** entièrement la méthode `computeBulkNudges()` (lignes 293 à 360).
3. Dans `index()`, remplacer le bloc de totaux et l'appel au nudge :

```php
        $pricing = $cart->pricing();

        $cartData = [
            // … la transformation des items reste inchangée, avec en plus par ligne :
            //   'discount'   => $pricing->lineFor($item)->discount,
            //   'line_total' => $pricing->lineFor($item)->lineTotal(),
            //   'promotion'  => $pricing->lineFor($item)->promotion?->name,
            'subtotal'        => $pricing->subtotal(),
            'bundle_discount' => $pricing->bundleDiscount(),
            'discount'        => $pricing->couponDiscount,
            'coupon_base'     => $pricing->couponEligibleBase(),
            'shipping_cost'   => $cart->shipping_cost ?? 0,
            'total'           => $pricing->total(),
            'coupon_code'     => $cart->coupon_code,
        ];

        $nudges = app(BundlePricingService::class)->nudges($cart);

        return Inertia::render('Cart/Index', [
            'cart'   => $cartData,
            'nudges' => $nudges,
        ]);
```

Dans la transformation de chaque item (la closure `map` existante), ajouter les trois clés :

```php
                $line = $pricing->lineFor($item);

                return [
                    // … clés existantes inchangées
                    'discount'   => $line->discount,
                    'line_total' => $line->lineTotal(),
                    'promotion'  => $line->promotion?->name,
                ];
```

- [ ] **Step 6: Mettre à jour le panier côté Vue**

Dans `resources/js/Pages/Cart/Index.vue` :

1. Dans la boucle des lignes, afficher le prix barré quand la ligne est remisée :

```vue
<div class="text-right">
    <p v-if="item.discount > 0" class="text-sm text-slate-400 line-through">
        {{ formatPrice(item.unit_price * item.quantity) }}
    </p>
    <p class="font-semibold text-slate-900">{{ formatPrice(item.line_total) }}</p>
    <p v-if="item.promotion" class="text-xs font-medium text-green-600">{{ item.promotion }}</p>
</div>
```

2. Dans le récapitulatif, insérer la ligne d'offre entre le sous-total et le coupon :

```vue
<div v-if="cart.bundle_discount > 0" class="flex justify-between text-sm">
    <span class="text-slate-600">Remise offres</span>
    <span class="font-medium text-green-600">− {{ formatPrice(cart.bundle_discount) }}</span>
</div>
```

3. Sous la ligne de coupon, expliquer l'assiette quand elle diffère du payable :

```vue
<p
    v-if="cart.coupon_code && cart.bundle_discount > 0"
    class="text-xs text-slate-500"
>
    Code promo appliqué sur {{ formatPrice(cart.coupon_base) }} — les articles en offre
    en sont exclus.
</p>
```

4. Dans le bloc des nudges, remplacer `nudge.product_name` par `nudge.promotion_name` et `:key="nudge.product_id"` par `:key="nudge.promotion_id"`.

- [ ] **Step 7: Construire les assets et vérifier le parcours**

Run: `npm run build`
Expected: build sans erreur.

Vérifier à la main sur `/panier` : 3 articles éligibles affichent la ligne « Remise offres », le prix barré par ligne, et le total exact. Avec 2 articles, le nudge annonce « ajoutez 1 ».

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Services/BundlePricingService.php app/Http/Controllers/Front/CartController.php
git add app/Services app/Http/Controllers/Front/CartController.php resources/js/Pages/Cart/Index.vue tests/Unit/BundlePricingServiceTest.php public/build
git commit -m "feat(cart): recapitulatif des offres et nudges par promotion

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 9: Fiche produit et vignette

**Files:**
- Modify: `app/Services/BundlePricingService.php`
- Modify: `app/Http/Controllers/Front/ShopController.php:99,192,405`
- Modify: `resources/js/Pages/Shop/Product.vue:120-139,300-325`
- Modify: `resources/js/Components/ProductCard.vue:140`
- Test: `tests/Unit/BundlePricingServiceTest.php`

**Interfaces:**
- Consumes: `Promotion::valid()`, `matchesPrice()`, `eligibleCategoryIds()` (Task 1).
- Produces: `BundlePricingService::forProduct(Product $product, ?float $effectivePrice = null): array` — **public**. Chaque entrée : `name`, `description`, `lot_qty`, `lot_price`, `unit_price_in_lot`, `saving`.

`unit_price_in_lot` est le prix unitaire obtenu **si les `lot_qty` unités étaient toutes ce produit** — la seule formulation honnête sur une fiche, le lot réel pouvant mélanger plusieurs références.

- [ ] **Step 1: Écrire les tests en échec**

Ajouter à `BundlePricingServiceTest`, avant l'accolade fermante :

```php
    // ================================================================
    // forProduct()
    // ================================================================

    public function test_for_product_returns_the_matching_offer(): void
    {
        $this->promotion();
        $product = $this->product(4000);

        $offers = $this->service()->forProduct($product);

        $this->assertCount(1, $offers);
        $this->assertSame(3, $offers[0]['lot_qty']);
        $this->assertSame(10000.0, $offers[0]['lot_price']);
    }

    public function test_for_product_computes_the_unit_price_in_lot(): void
    {
        $this->promotion();

        $offers = $this->service()->forProduct($this->product(4000));

        $this->assertSame(3333.0, $offers[0]['unit_price_in_lot']);
    }

    public function test_for_product_computes_the_saving(): void
    {
        $this->promotion();

        $offers = $this->service()->forProduct($this->product(4000));

        $this->assertSame(2000.0, $offers[0]['saving']);
    }

    public function test_for_product_skips_offer_outside_the_band(): void
    {
        $this->promotion();

        $this->assertSame([], $this->service()->forProduct($this->product(5000, null, 'Premium')));
    }

    public function test_for_product_honours_an_explicit_variant_price(): void
    {
        $this->promotion();
        $product = $this->product(4000);

        // Une variante à 5 000 sort de la fourchette : plus d'offre affichable.
        $this->assertSame([], $this->service()->forProduct($product, 5000.0));

        // Une variante à 4 200 reste dedans.
        $this->assertCount(1, $this->service()->forProduct($product, 4200.0));
    }

    public function test_for_product_skips_inactive_offer(): void
    {
        $this->promotion(['is_active' => false]);

        $this->assertSame([], $this->service()->forProduct($this->product(4000)));
    }
```

- [ ] **Step 2: Lancer les tests pour vérifier qu'ils échouent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php --filter for_product`
Expected: FAIL — `Call to undefined method …::forProduct()`

- [ ] **Step 3: Implémenter `forProduct()`**

Ajouter l'import `use App\Models\Product;` puis, après `nudges()` :

```php
    /**
     * Offres affichables sur une fiche produit, pour un prix effectif donné.
     *
     * @return array<int, array{name:string, description:?string, lot_qty:int,
     *                          lot_price:float, unit_price_in_lot:float, saving:float}>
     */
    public function forProduct(Product $product, ?float $effectivePrice = null): array
    {
        $price = $effectivePrice ?? (float) $product->sale_price;

        return Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->get()
            ->filter(function (Promotion $promotion) use ($product, $price) {
                $eligible = $promotion->products->contains('id', $product->id)
                    || ($product->category_id !== null
                        && in_array($product->category_id, $promotion->eligibleCategoryIds(), true));

                return $eligible && $promotion->matchesPrice($price);
            })
            ->map(fn (Promotion $promotion) => [
                'name'              => $promotion->name,
                'description'       => $promotion->description,
                'lot_qty'           => (int) $promotion->lot_qty,
                'lot_price'         => (float) $promotion->lot_price,
                'unit_price_in_lot' => (float) round((float) $promotion->lot_price / (int) $promotion->lot_qty),
                'saving'            => max(0, $price * (int) $promotion->lot_qty - (float) $promotion->lot_price),
            ])
            ->values()
            ->all();
    }

    /**
     * Libellé court pour une vignette de catalogue, ou null s'il n'y a pas d'offre.
     */
    public function labelForProduct(Product $product): ?string
    {
        $offers = $this->forProduct($product);

        if ($offers === []) {
            return null;
        }

        return $offers[0]['lot_qty'] . ' pour ' . number_format($offers[0]['lot_price'], 0, ',', ' ');
    }
```

- [ ] **Step 4: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Unit/BundlePricingServiceTest.php`
Expected: PASS — 58 tests

- [ ] **Step 5: Alimenter le contrôleur boutique**

Dans `app/Http/Controllers/Front/ShopController.php`, ajouter l'import `use App\Services\BundlePricingService;`.

Aux lignes 99 et 192, remplacer `has_bulk_pricing` :

```php
                    'promotion_label' => app(BundlePricingService::class)->labelForProduct($product),
```

> Pour une liste paginée, instancier le service **une seule fois** avant la boucle et réutiliser l'instance, afin de ne pas relire les promotions à chaque produit :
> ```php
> $pricing = app(BundlePricingService::class);
> // puis dans la closure : 'promotion_label' => $pricing->labelForProduct($p),
> ```

Ligne 405 environ, dans `show()`, remplacer `'bulk_pricing_rules' => $product->bulk_pricing_rules,` par :

```php
            'promotions' => app(BundlePricingService::class)->forProduct($product),
```

- [ ] **Step 6: Réécrire l'affichage de la fiche produit**

Dans `resources/js/Pages/Shop/Product.vue` :

1. **Supprimer** les trois `computed` du calcul JavaScript (`bulkRules`, `bulkUnitPrice`, `bulkSaving`, lignes 120 à 139) et la prop `bulk_pricing_rules`.

2. Déclarer la nouvelle prop `promotions` (tableau, défaut `[]`) et un `computed` qui filtre selon la variante choisie :

```js
// Les offres viennent du serveur. Si une variante a son propre prix, elle peut
// sortir de la fourchette de l'offre : on le dit, au lieu de masquer en silence.
const activePromotions = computed(() => {
    const price = currentPrice.value;
    return props.promotions.filter(
        (offer) => offer.saving > 0 || price * offer.lot_qty > offer.lot_price,
    );
});

const variantLeavesOffer = computed(
    () => props.promotions.length > 0 && activePromotions.value.length === 0,
);
```

3. Remplacer le bloc de prix (lignes 300 à 325) par :

```vue
<div class="flex items-baseline gap-3 mb-2">
    <span class="text-3xl font-bold text-slate-900">{{ formatPrice(currentPrice) }}</span>
    <span v-if="product.compare_price" class="text-xl text-slate-400 line-through">
        {{ formatPrice(product.compare_price) }}
    </span>
</div>

<!-- Offres par lot -->
<div
    v-if="activePromotions.length"
    class="mb-4 border border-primary-600/20 rounded-lg overflow-hidden"
>
    <div class="bg-primary-600/5 px-3 py-2">
        <p class="text-xs font-semibold text-primary-600">Offres en cours</p>
    </div>
    <div class="divide-y divide-slate-100">
        <div
            v-for="offer in activePromotions"
            :key="offer.name"
            class="px-3 py-2 text-sm"
        >
            <div class="flex items-center justify-between">
                <span class="font-medium text-slate-900">{{ offer.name }}</span>
                <span class="font-semibold text-primary-600">
                    {{ formatPrice(offer.unit_price_in_lot) }} / unité
                </span>
            </div>
            <p v-if="offer.saving > 0" class="text-xs text-green-600 mt-0.5">
                Économisez {{ formatPrice(offer.saving) }} sur {{ offer.lot_qty }} articles
            </p>
            <p v-if="offer.description" class="text-xs text-slate-500 mt-0.5">
                {{ offer.description }}
            </p>
        </div>
    </div>
</div>

<p v-else-if="variantLeavesOffer" class="mb-4 text-sm text-slate-500">
    Cette taille n'entre pas dans les offres par lot en cours.
</p>
```

> Les gardes `!selectedVariant` disparaissent : c'est `currentPrice` — qui intègre le prix de la variante — qui détermine l'éligibilité.

- [ ] **Step 7: Mettre à jour la vignette**

Dans `resources/js/Components/ProductCard.vue`, ligne 140, remplacer le badge générique :

```vue
<div v-if="product.promotion_label" class="absolute bottom-2 left-2 z-10">
    <span class="px-2 py-1 rounded bg-primary-600 text-white text-xs font-semibold">
        {{ product.promotion_label }}
    </span>
</div>
```

- [ ] **Step 8: Construire et vérifier**

Run: `npm run build`
Expected: build sans erreur.

À la main : la fiche d'un t-shirt à 4 000 F affiche « 3 t-shirts pour 10 000 F — 3 333 F / unité ». Sélectionner une taille à 5 000 F fait apparaître le message d'inéligibilité au lieu de faire disparaître le bloc.

- [ ] **Step 9: Commit**

```bash
vendor/bin/pint app/Services/BundlePricingService.php app/Http/Controllers/Front/ShopController.php
git add app/Services app/Http/Controllers/Front/ShopController.php resources/js/Pages/Shop/Product.vue resources/js/Components/ProductCard.vue tests/Unit/BundlePricingServiceTest.php public/build
git commit -m "feat(shop): offres par lot sur la fiche produit et la vignette

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 10: CRUD back-office des offres

**Files:**
- Create: `app/Http/Controllers/Admin/PromotionController.php`
- Create: `resources/js/Pages/Admin/Promotions/Index.vue`
- Create: `resources/js/Pages/Admin/Promotions/Create.vue`
- Create: `resources/js/Pages/Admin/Promotions/Edit.vue`
- Create: `resources/js/Pages/Admin/Promotions/Partials/PromotionForm.vue`
- Create: `resources/js/Pages/Admin/Promotions/Partials/CategoryPicker.vue`
- Modify: `routes/web.php:389`
- Test: `tests/Feature/AdminPromotionTest.php`

**Interfaces:**
- Consumes: `Promotion` (Task 1), `ActivityLog::logCreated/logUpdated/logDeleted(Model $model, ?string $description)` (existant).
- Produces: routes nommées `admin.promotions.{index,create,store,edit,update,destroy}`.

Le contrôleur suit `Admin\CouponController` : filtres `search`/`status`, `Inertia::setRootView('layouts.admin-inertia')`, collection aplatie pour Inertia.

- [ ] **Step 1: Déclarer la route**

Dans `routes/web.php`, dans le groupe `admin:admin,manager`, juste après le bloc Coupons :

```php
            // Offres par lot
            Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class)->names('promotions');
            Route::post('/promotions-margin-preview', [\App\Http\Controllers\Admin\PromotionController::class, 'marginPreview'])->name('promotions.margin-preview');
```

- [ ] **Step 2: Écrire le test en échec**

`tests/Feature/AdminPromotionTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromotionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $tshirts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->create(['role' => 'admin']);
        $this->tshirts = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'          => '3 t-shirts pour 10 000 F',
            'lot_qty'       => 3,
            'lot_price'     => 10000,
            'price_min'     => 4000,
            'price_max'     => 4499,
            'category_ids'  => [$this->tshirts->id],
            'is_active'     => true,
        ], $overrides);
    }

    public function test_index_is_reachable(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.promotions.index'))
            ->assertOk();
    }

    public function test_store_creates_the_promotion_with_its_categories(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload())
            ->assertRedirect();

        $promotion = Promotion::firstOrFail();

        $this->assertSame('3 t-shirts pour 10 000 F', $promotion->name);
        $this->assertTrue($promotion->categories->contains('id', $this->tshirts->id));
    }

    public function test_store_rejects_lot_qty_below_two(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['lot_qty' => 1]))
            ->assertSessionHasErrors('lot_qty');
    }

    public function test_store_rejects_inverted_price_band(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['price_min' => 5000, 'price_max' => 4000]))
            ->assertSessionHasErrors('price_max');
    }

    public function test_store_rejects_promotion_without_any_target(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.store'), $this->payload(['category_ids' => [], 'product_ids' => []]))
            ->assertSessionHasErrors('category_ids');
    }

    public function test_update_replaces_the_categories(): void
    {
        $polos     = Category::create(['name' => 'Polos', 'slug' => 'polos']);
        $promotion = Promotion::create($this->payloadForModel());
        $promotion->categories()->attach($this->tshirts->id);

        $this->actingAs($this->admin)
            ->put(route('admin.promotions.update', $promotion), $this->payload(['category_ids' => [$polos->id]]))
            ->assertRedirect();

        $promotion->refresh()->load('categories');

        $this->assertTrue($promotion->categories->contains('id', $polos->id));
        $this->assertFalse($promotion->categories->contains('id', $this->tshirts->id));
    }

    public function test_destroy_removes_the_promotion(): void
    {
        $promotion = Promotion::create($this->payloadForModel());

        $this->actingAs($this->admin)
            ->delete(route('admin.promotions.destroy', $promotion))
            ->assertRedirect();

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_guest_is_rejected(): void
    {
        $this->get(route('admin.promotions.index'))->assertRedirect();
    }

    private function payloadForModel(): array
    {
        return [
            'name'      => '3 t-shirts pour 10 000 F',
            'lot_qty'   => 3,
            'lot_price' => 10000,
            'price_min' => 4000,
            'price_max' => 4499,
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 3: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Feature/AdminPromotionTest.php`
Expected: FAIL — `Class "App\Http\Controllers\Admin\PromotionController" not found`

- [ ] **Step 4: Écrire le contrôleur**

`app/Http/Controllers/Admin/PromotionController.php` :

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::query()->with('categories');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'    => $query->valid(),
                'expired'   => $query->where('expires_at', '<', now()),
                'scheduled' => $query->where('starts_at', '>', now()),
                'inactive'  => $query->where('is_active', false),
                default     => null,
            };
        }

        $promotions = $query
            ->withCount('orderItems')
            ->withSum('orderItems', 'discount_amount')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $promotions->getCollection()->transform(fn (Promotion $p) => [
            'id'                 => $p->id,
            'name'               => $p->name,
            'lot_qty'            => $p->lot_qty,
            'lot_price'          => (float) $p->lot_price,
            'lot_price_fmt'      => number_format((float) $p->lot_price, 0, ',', ' ') . ' F CFA',
            'band_fmt'           => $this->formatBand($p),
            'categories'         => $p->categories->pluck('name')->all(),
            'max_lots_per_order' => $p->max_lots_per_order,
            'starts_at_fmt'      => $p->starts_at?->format('d/m/Y'),
            'expires_at_fmt'     => $p->expires_at?->format('d/m/Y'),
            'is_active'          => $p->is_active,
            'status'             => $p->status,
            'lines_count'        => $p->order_items_count,
            'discount_total_fmt' => number_format((float) ($p->order_items_sum_discount_amount ?? 0), 0, ',', ' ') . ' F CFA',
        ]);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Index', [
            'promotions' => $promotions,
            'filters'    => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $promotion = Promotion::create($this->attributes($validated));
        $promotion->categories()->sync($validated['category_ids'] ?? []);
        $promotion->products()->sync($validated['product_ids'] ?? []);

        ActivityLog::logCreated($promotion, "Offre « {$promotion->name} » créée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre créée.');
    }

    public function edit(Promotion $promotion)
    {
        $promotion->load(['categories', 'products']);

        Inertia::setRootView('layouts.admin-inertia');

        return Inertia::render('Admin/Promotions/Edit', array_merge($this->formOptions(), [
            'promotion' => [
                'id'                     => $promotion->id,
                'name'                   => $promotion->name,
                'description'            => $promotion->description,
                'lot_qty'                => $promotion->lot_qty,
                'lot_price'              => (float) $promotion->lot_price,
                'price_min'              => $promotion->price_min !== null ? (float) $promotion->price_min : null,
                'price_max'              => $promotion->price_max !== null ? (float) $promotion->price_max : null,
                'max_lots_per_order'     => $promotion->max_lots_per_order,
                'include_descendants'    => $promotion->include_descendants,
                'stackable_with_coupons' => $promotion->stackable_with_coupons,
                'is_active'              => $promotion->is_active,
                'priority'               => $promotion->priority,
                'starts_at'              => $promotion->starts_at?->format('Y-m-d'),
                'expires_at'             => $promotion->expires_at?->format('Y-m-d'),
                'category_ids'           => $promotion->categories->pluck('id')->all(),
                'product_ids'            => $promotion->products->pluck('id')->all(),
            ],
        ]));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate($this->rules());
        $oldValues = $promotion->getOriginal();

        $promotion->update($this->attributes($validated));
        $promotion->categories()->sync($validated['category_ids'] ?? []);
        $promotion->products()->sync($validated['product_ids'] ?? []);

        ActivityLog::logUpdated($promotion, $oldValues, "Offre « {$promotion->name} » modifiée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        $name = $promotion->name;
        $promotion->delete();

        ActivityLog::logDeleted($promotion, "Offre « {$name} » supprimée");

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Offre supprimée.');
    }

    // ========== INTERNE ==========

    private function rules(): array
    {
        return [
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'lot_qty'                => 'required|integer|min:2|max:999',
            'lot_price'              => 'required|numeric|min:0',
            'price_min'              => 'nullable|numeric|min:0',
            'price_max'              => 'nullable|numeric|min:0|gte:price_min',
            'max_lots_per_order'     => 'nullable|integer|min:1',
            'include_descendants'    => 'boolean',
            'stackable_with_coupons' => 'boolean',
            'is_active'              => 'boolean',
            'priority'               => 'nullable|integer',
            'starts_at'              => 'nullable|date',
            'expires_at'             => 'nullable|date|after_or_equal:starts_at',
            // Une offre sans cible n'est jamais éligible : on la refuse ici
            // plutôt que de la laisser dormir sans effet en production.
            'category_ids'           => 'array|required_without:product_ids',
            'category_ids.*'         => ['integer', Rule::exists('categories', 'id')],
            'product_ids'            => 'array',
            'product_ids.*'          => ['integer', Rule::exists('products', 'id')],
        ];
    }

    private function attributes(array $validated): array
    {
        return [
            'name'                   => $validated['name'],
            'description'            => $validated['description'] ?? null,
            'type'                   => 'lot',
            'lot_qty'                => $validated['lot_qty'],
            'lot_price'              => $validated['lot_price'],
            'price_min'              => $validated['price_min'] ?? null,
            'price_max'              => $validated['price_max'] ?? null,
            'max_lots_per_order'     => $validated['max_lots_per_order'] ?? null,
            'include_descendants'    => $validated['include_descendants'] ?? true,
            'stackable_with_coupons' => $validated['stackable_with_coupons'] ?? false,
            'is_active'              => $validated['is_active'] ?? true,
            'priority'               => $validated['priority'] ?? 0,
            'starts_at'              => $validated['starts_at'] ?? null,
            'expires_at'             => $validated['expires_at'] ?? null,
        ];
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::active()
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id'])
                ->map(fn (Category $c) => [
                    'id'        => $c->id,
                    'name'      => $c->name,
                    'parent_id' => $c->parent_id,
                ]),
            'products' => Product::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'sale_price'])
                ->map(fn (Product $p) => [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'price' => (float) $p->sale_price,
                ]),
        ];
    }

    private function formatBand(Promotion $promotion): string
    {
        if ($promotion->price_min === null && $promotion->price_max === null) {
            return 'Tous les prix';
        }

        $min = $promotion->price_min !== null ? number_format((float) $promotion->price_min, 0, ',', ' ') : '0';
        $max = $promotion->price_max !== null ? number_format((float) $promotion->price_max, 0, ',', ' ') : '∞';

        return $min . ' – ' . $max . ' F';
    }
}
```

- [ ] **Step 5: Lancer le test pour vérifier qu'il passe**

Run: `vendor/bin/phpunit tests/Feature/AdminPromotionTest.php`
Expected: PASS — 8 tests. `marginPreview` n'est pas encore écrit : la route déclarée en Step 1 lèvera une erreur si elle est appelée, ce qu'aucun test ne fait avant la Task 11.

- [ ] **Step 6: Écrire les pages Inertia**

Suivre `resources/js/Pages/Admin/Coupons/{Index,Create,Edit}.vue` comme modèle de structure, de filtres et de pagination.

`Index.vue` affiche un `Table` avec : nom, lot (`3 pour 10 000 F CFA`), fourchette, catégories, statut en `Badge`, lignes vendues, remise cumulée, actions. Obligatoire :
- `Skeleton` pendant le chargement Inertia ;
- `EmptyState` avec CTA « Créer une offre » quand la liste est vide ;
- `ConfirmModal` avant suppression, texte « Supprimer l'offre *nom* ? Cette action est irréversible. » ;
- `Toast` sur succès via le flash partagé ;
- filtres `SearchInput` + `FilterSelect` (statuts : active, programmée, expirée, inactive).

Couleurs des statuts : `active` → Success `#16A34A`, `scheduled` → Primary `#2563EB`, `expired` → slate, `inactive` → slate.

`Create.vue` et `Edit.vue` se contentent d'un `AdminLayout` et du composant partagé :

```vue
<script setup>
import PromotionForm from './Partials/PromotionForm.vue';

const props = defineProps({
    categories: { type: Array, required: true },
    products:   { type: Array, required: true },
    promotion:  { type: Object, default: null },
});
</script>

<template>
    <PromotionForm
        :categories="categories"
        :products="products"
        :promotion="promotion"
    />
</template>
```

`Partials/PromotionForm.vue` porte le formulaire (`useForm` d'Inertia), la validation affichée champ par champ, et monte `CategoryPicker` et `MarginPanel` (Task 11). `Partials/CategoryPicker.vue` rend l'arbre `parent_id` en cases à cocher avec indentation, et une case « inclure les sous-catégories » liée à `include_descendants`.

Chaque fichier reste sous 500 lignes — d'où la séparation en partials.

- [ ] **Step 7: Construire et vérifier**

Run: `npm run build`

À la main sur `/admin/promotions` : liste vide → `EmptyState` avec CTA. Créer l'offre « 3 t-shirts pour 10 000 F » sur la catégorie T-shirts, fourchette 4 000–4 499. Vérifier qu'elle apparaît en statut *active*, puis qu'un panier de 3 t-shirts à 4 000 F totalise 10 000 F.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Http/Controllers/Admin/PromotionController.php
git add app/Http/Controllers/Admin/PromotionController.php resources/js/Pages/Admin/Promotions routes/web.php tests/Feature/AdminPromotionTest.php public/build
git commit -m "feat(admin): CRUD des offres par lot

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 11: Panneau de marge

**Files:**
- Modify: `app/Http/Controllers/Admin/PromotionController.php`
- Create: `resources/js/Pages/Admin/Promotions/Partials/MarginPanel.vue`
- Modify: `resources/js/Pages/Admin/Promotions/Partials/PromotionForm.vue`
- Test: `tests/Feature/AdminPromotionTest.php`

**Interfaces:**
- Produces: `PromotionController::marginPreview(Request $request)` → JSON `{ eligible_count, without_purchase_price, catalog_margin, lot_margin, below_cost, products[] }`.

C'est ce panneau qui distingue un levier de revenu d'une fuite de marge. **Aucune donnée simulée** : tout vient de `products.purchase_price`. Les produits sans prix d'achat renseigné sont comptés à part, jamais estimés.

- [ ] **Step 1: Écrire les tests en échec**

Ajouter à `AdminPromotionTest` :

```php
    // ================================================================
    // marginPreview()
    // ================================================================

    private function productAt(float $sale, ?float $purchase, ?Category $category = null): \App\Models\Product
    {
        return \App\Models\Product::create([
            'name'           => 'T-shirt ' . fake()->unique()->numerify('###'),
            'sku'            => 'SKU-' . fake()->unique()->numerify('######'),
            'sale_price'     => $sale,
            'purchase_price' => $purchase ?? 0,
            'status'         => 'active',
            'category_id'    => ($category ?? $this->tshirts)->id,
        ]);
    }

    public function test_margin_preview_counts_eligible_products(): void
    {
        $this->productAt(4000, 2500);
        $this->productAt(4200, 2600);
        $this->productAt(5000, 3000);   // hors fourchette

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min'    => 4000,
            'price_max'    => 4499,
            'lot_qty'      => 3,
            'lot_price'    => 10000,
        ]);

        $response->assertOk()->assertJsonPath('eligible_count', 2);
    }

    public function test_margin_preview_flags_a_lot_price_below_cost(): void
    {
        $this->productAt(4000, 3800);

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min'    => 4000,
            'price_max'    => 4499,
            'lot_qty'      => 3,
            'lot_price'    => 10000,   // 3 333 / unité contre 3 800 de coût
        ]);

        $response->assertOk()->assertJsonPath('below_cost', true);
    }

    public function test_margin_preview_separates_products_without_purchase_price(): void
    {
        $this->productAt(4000, 2500);
        $this->productAt(4100, null);

        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min'    => 4000,
            'price_max'    => 4499,
            'lot_qty'      => 3,
            'lot_price'    => 10000,
        ]);

        $response->assertOk()->assertJsonPath('without_purchase_price', 1);
    }

    public function test_margin_preview_returns_zero_when_nothing_matches(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.promotions.margin-preview'), [
            'category_ids' => [$this->tshirts->id],
            'price_min'    => 4000,
            'price_max'    => 4499,
            'lot_qty'      => 3,
            'lot_price'    => 10000,
        ]);

        $response->assertOk()->assertJsonPath('eligible_count', 0);
    }
```

- [ ] **Step 2: Lancer les tests pour vérifier qu'ils échouent**

Run: `vendor/bin/phpunit tests/Feature/AdminPromotionTest.php --filter margin`
Expected: FAIL — `Method …PromotionController::marginPreview does not exist`

- [ ] **Step 3: Implémenter `marginPreview()`**

Ajouter à `PromotionController` :

```php
    /**
     * Impact d'une offre en cours de saisie sur la marge, calculé depuis la base.
     * Les produits dont purchase_price n'est pas renseigné sont comptés à part :
     * leur marge est inconnue, pas estimée.
     */
    public function marginPreview(Request $request)
    {
        $validated = $request->validate([
            'category_ids'        => 'array',
            'category_ids.*'      => 'integer',
            'product_ids'         => 'array',
            'product_ids.*'       => 'integer',
            'include_descendants' => 'boolean',
            'price_min'           => 'nullable|numeric|min:0',
            'price_max'           => 'nullable|numeric|min:0',
            'lot_qty'             => 'required|integer|min:2',
            'lot_price'           => 'required|numeric|min:0',
        ]);

        $categoryIds = $this->expandCategories(
            $validated['category_ids'] ?? [],
            $validated['include_descendants'] ?? true
        );

        $products = Product::where('status', 'active')
            ->where(function ($query) use ($categoryIds, $validated) {
                $query->whereIn('category_id', $categoryIds ?: [0]);

                if (!empty($validated['product_ids'])) {
                    $query->orWhereIn('id', $validated['product_ids']);
                }
            })
            ->when($validated['price_min'] ?? null, fn ($q, $min) => $q->where('sale_price', '>=', $min))
            ->when($validated['price_max'] ?? null, fn ($q, $max) => $q->where('sale_price', '<=', $max))
            ->get(['id', 'name', 'sale_price', 'purchase_price']);

        $unitPriceInLot = (float) $validated['lot_price'] / (int) $validated['lot_qty'];

        $costed  = $products->filter(fn (Product $p) => (float) $p->purchase_price > 0);
        $unknown = $products->count() - $costed->count();

        $catalogMargin = $costed->isEmpty() ? null : round($costed->avg(
            fn (Product $p) => (float) $p->sale_price - (float) $p->purchase_price
        ));

        $lotMargin = $costed->isEmpty() ? null : round($costed->avg(
            fn (Product $p) => $unitPriceInLot - (float) $p->purchase_price
        ));

        return response()->json([
            'eligible_count'         => $products->count(),
            'without_purchase_price' => $unknown,
            'unit_price_in_lot'      => round($unitPriceInLot),
            'catalog_margin'         => $catalogMargin,
            'lot_margin'             => $lotMargin,
            'below_cost'             => $costed->contains(
                fn (Product $p) => $unitPriceInLot < (float) $p->purchase_price
            ),
            'products' => $products->map(fn (Product $p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'sale_price'     => (float) $p->sale_price,
                'purchase_price' => (float) $p->purchase_price > 0 ? (float) $p->purchase_price : null,
                'lot_margin'     => (float) $p->purchase_price > 0
                    ? round($unitPriceInLot - (float) $p->purchase_price)
                    : null,
            ])->values(),
        ]);
    }

    /**
     * @param  int[]  $ids
     * @return int[]
     */
    private function expandCategories(array $ids, bool $includeDescendants): array
    {
        if ($ids === []) {
            return [];
        }

        if (!$includeDescendants) {
            return $ids;
        }

        $expanded = [];

        foreach (Category::whereIn('id', $ids)->get() as $category) {
            $expanded = array_merge($expanded, $category->getAllChildrenIds());
        }

        return array_values(array_unique($expanded));
    }
```

- [ ] **Step 4: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Feature/AdminPromotionTest.php`
Expected: PASS — 12 tests

- [ ] **Step 5: Écrire `MarginPanel.vue`**

`resources/js/Pages/Admin/Promotions/Partials/MarginPanel.vue` : reçoit en props `categoryIds`, `productIds`, `includeDescendants`, `priceMin`, `priceMax`, `lotQty`, `lotPrice`. À chaque changement (debounce 400 ms via `useDebounceFn` de `@vueuse/core`), appelle `admin.promotions.margin-preview` en `axios.post` et rend :

- `LoadingSpinner` pendant l'appel ;
- `EmptyState` « Aucun produit ne correspond à cette fourchette » si `eligible_count === 0`, avec une note invitant à élargir la fourchette ;
- sinon : nombre de produits éligibles, `unit_price_in_lot`, `catalog_margin` et `lot_margin` côte à côte, et la liste repliable des produits avec leur marge dans le lot ;
- `Alert` de variante `danger` (`#DC2626`) si `below_cost` : « Le prix du lot passe sous le coût d'achat d'au moins un produit. » ;
- `Alert` de variante `warning` (`#F59E0B`) si `without_purchase_price > 0` : « N produits n'ont pas de prix d'achat renseigné — leur marge ne peut pas être calculée. » ;
- `Alert` d'erreur si l'appel échoue, avec un bouton « Réessayer ».

Ne jamais afficher de valeur de remplacement quand `catalog_margin` ou `lot_margin` vaut `null` : afficher « Non calculable ».

Monter le composant dans `PromotionForm.vue`, sous les champs de prix, lié aux champs du formulaire.

- [ ] **Step 6: Construire et vérifier**

Run: `npm run build`

À la main : saisir catégorie T-shirts et fourchette 4 000–4 499 sur un catalogue réel ; le panneau doit afficher le nombre exact de produits concernés. Mettre `lot_price` à 1 000 et vérifier l'alerte rouge.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Http/Controllers/Admin/PromotionController.php
git add app/Http/Controllers/Admin/PromotionController.php resources/js/Pages/Admin/Promotions tests/Feature/AdminPromotionTest.php public/build
git commit -m "feat(admin): panneau de marge a la saisie d'une offre

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 12: Commande de reprise des règles existantes

**Files:**
- Create: `app/Console/Commands/ImportBulkPricingRules.php`
- Test: `tests/Feature/ImportBulkPricingRulesTest.php`

**Interfaces:**
- Consumes: `products.bulk_pricing_rules`, `categories.bulk_pricing_rules` (colonnes existantes), `Promotion` (Task 1).
- Produces: commande `promotions:import-bulk-rules [--dry-run]`.

Deux instances tournent en production. La sémantique de lot diffère de celle de palier : les offres créées sont donc **inactives**, et `--dry-run` montre les écarts avant toute écriture.

- [ ] **Step 1: Écrire le test en échec**

`tests/Feature/ImportBulkPricingRulesTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportBulkPricingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_writes_nothing(): void
    {
        $product = Product::create([
            'name'               => 'T-shirt',
            'sku'                => 'SKU-TS1',
            'sale_price'         => 4000,
            'status'             => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules --dry-run')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_product_rule_becomes_an_inactive_promotion(): void
    {
        $product = Product::create([
            'name'               => 'T-shirt',
            'sku'                => 'SKU-TS2',
            'sale_price'         => 4000,
            'status'             => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $promotion = Promotion::firstOrFail();

        $this->assertSame(3, $promotion->lot_qty);
        $this->assertSame('9999.00', $promotion->lot_price);
        $this->assertFalse($promotion->is_active);
        $this->assertTrue($promotion->products->contains('id', $product->id));
    }

    public function test_category_rule_creates_one_promotion_per_price_band(): void
    {
        $category = Category::create([
            'name'               => 'T-shirts',
            'slug'               => 't-shirts',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        Product::create(['name' => 'A', 'sku' => 'SKU-A', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);
        Product::create(['name' => 'B', 'sku' => 'SKU-B', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);
        Product::create(['name' => 'C', 'sku' => 'SKU-C', 'sale_price' => 5000, 'status' => 'active', 'category_id' => $category->id]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        // Un palier 4 000 et un palier 5 000
        $this->assertDatabaseCount('promotions', 2);

        $bands = Promotion::orderBy('price_min')->pluck('price_min')->map(fn ($v) => (float) $v)->all();
        $this->assertSame([4000.0, 5000.0], $bands);
    }

    public function test_category_band_spans_five_hundred_francs(): void
    {
        $category = Category::create([
            'name'               => 'T-shirts',
            'slug'               => 't-shirts',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        Product::create(['name' => 'A', 'sku' => 'SKU-D', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $promotion = Promotion::firstOrFail();

        $this->assertSame('4000.00', $promotion->price_min);
        $this->assertSame('4499.00', $promotion->price_max);
    }

    public function test_running_twice_does_not_duplicate(): void
    {
        Product::create([
            'name'               => 'T-shirt',
            'sku'                => 'SKU-TS3',
            'sale_price'         => 4000,
            'status'             => 'active',
            'bulk_pricing_rules' => [['min_qty' => 3, 'unit_price' => 3333]],
        ]);

        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();
        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 1);
    }

    public function test_nothing_to_import_is_not_an_error(): void
    {
        $this->artisan('promotions:import-bulk-rules')->assertSuccessful();

        $this->assertDatabaseCount('promotions', 0);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Feature/ImportBulkPricingRulesTest.php`
Expected: FAIL — `The command "promotions:import-bulk-rules" does not exist`

- [ ] **Step 3: Écrire la commande**

`app/Console/Commands/ImportBulkPricingRules.php` :

```php
<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Console\Command;

class ImportBulkPricingRules extends Command
{
    protected $signature = 'promotions:import-bulk-rules {--dry-run : Afficher le rapport sans rien écrire}';

    protected $description = 'Convertit les anciennes règles de prix en gros en offres par lot';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation — aucune écriture en base.');
        }

        $created = 0;
        $created += $this->importProductRules($dryRun);
        $created += $this->importCategoryRules($dryRun);

        if ($created === 0) {
            $this->info('Aucune règle à reprendre.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info($dryRun
            ? "{$created} offre(s) seraient créées."
            : "{$created} offre(s) créées, toutes inactives.");

        if (!$dryRun) {
            $this->warn('Relisez chaque offre et son panneau de marge avant de l\'activer.');
        }

        return self::SUCCESS;
    }

    private function importProductRules(bool $dryRun): int
    {
        $products = Product::whereNotNull('bulk_pricing_rules')->get();
        $created  = 0;

        foreach ($products as $product) {
            foreach ($this->validRules($product->bulk_pricing_rules) as $rule) {
                $name = "{$rule['min_qty']} × {$product->name}";

                if (Promotion::where('name', $name)->exists()) {
                    continue;
                }

                $lotPrice = $rule['min_qty'] * $rule['unit_price'];

                $this->reportRule($name, (int) $rule['min_qty'], (float) $lotPrice, (float) $product->sale_price);

                if (!$dryRun) {
                    $promotion = Promotion::create([
                        'name'      => $name,
                        'lot_qty'   => $rule['min_qty'],
                        'lot_price' => $lotPrice,
                        'is_active' => false,
                    ]);

                    $promotion->products()->attach($product->id);
                }

                $created++;
            }
        }

        return $created;
    }

    private function importCategoryRules(bool $dryRun): int
    {
        $categories = Category::whereNotNull('bulk_pricing_rules')->get();
        $created    = 0;

        foreach ($categories as $category) {
            // Reproduit le regroupement de l'ancien moteur : catégorie + prix arrondi.
            $bands = Product::where('category_id', $category->id)
                ->where('status', 'active')
                ->get(['sale_price'])
                ->map(fn (Product $p) => (float) round((float) $p->sale_price))
                ->unique()
                ->sort()
                ->values();

            foreach ($this->validRules($category->bulk_pricing_rules) as $rule) {
                foreach ($bands as $band) {
                    $name = "{$rule['min_qty']} × {$category->name} à " . number_format($band, 0, ',', ' ') . ' F';

                    if (Promotion::where('name', $name)->exists()) {
                        continue;
                    }

                    $lotPrice = $rule['min_qty'] * $rule['unit_price'];

                    $this->reportRule($name, (int) $rule['min_qty'], (float) $lotPrice, $band);

                    if (!$dryRun) {
                        $promotion = Promotion::create([
                            'name'      => $name,
                            'lot_qty'   => $rule['min_qty'],
                            'lot_price' => $lotPrice,
                            'price_min' => $band,
                            'price_max' => $band + 499,
                            'is_active' => false,
                        ]);

                        $promotion->categories()->attach($category->id);
                    }

                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Comparatif ancien / nouveau aux quantités 1 à 10. C'est le cœur du rapport :
     * la sémantique de lot diffère de celle de palier, et l'exploitant doit voir
     * précisément où les prix changent avant d'activer quoi que ce soit.
     */
    private function reportRule(string $name, int $lotQty, float $lotPrice, float $basePrice): void
    {
        $unitPrice = $lotPrice / $lotQty;

        $this->newLine();
        $this->line("<options=bold>{$name}</>");
        $this->line('Prix de base : ' . number_format($basePrice, 0, ',', ' ') . ' F');

        $rows = [];

        for ($qty = 1; $qty <= 10; $qty++) {
            $before = $qty >= $lotQty ? $qty * $unitPrice : $qty * $basePrice;

            $lots  = intdiv($qty, $lotQty);
            $after = $lots * $lotPrice + ($qty - $lots * $lotQty) * $basePrice;

            $rows[] = [
                $qty,
                number_format($before, 0, ',', ' '),
                number_format($after, 0, ',', ' '),
                $before == $after ? '=' : sprintf('%+d', (int) round($after - $before)),
            ];
        }

        $this->table(['Qté', 'Avant (palier)', 'Après (lot)', 'Écart'], $rows);
    }

    /**
     * @return array<int, array{min_qty:int, unit_price:float}>
     */
    private function validRules(mixed $rules): array
    {
        if (!is_array($rules)) {
            return [];
        }

        return collect($rules)
            ->filter(fn ($rule) => isset($rule['min_qty'], $rule['unit_price']) && (int) $rule['min_qty'] >= 2)
            ->map(fn ($rule) => [
                'min_qty'    => (int) $rule['min_qty'],
                'unit_price' => (float) $rule['unit_price'],
            ])
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Lancer les tests pour vérifier qu'ils passent**

Run: `vendor/bin/phpunit tests/Feature/ImportBulkPricingRulesTest.php`
Expected: PASS — 6 tests

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint app/Console/Commands/ImportBulkPricingRules.php
git add app/Console/Commands/ImportBulkPricingRules.php tests/Feature/ImportBulkPricingRulesTest.php
git commit -m "feat(promotions): commande de reprise des anciennes regles de prix en gros

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 13: Retirer le moteur de palier

**Files:**
- Modify: `app/Models/Product.php:251-287`
- Modify: `app/Models/Category.php:86-105`
- Modify: `app/Http/Controllers/Admin/ProductController.php:96-102,276,320-326`
- Modify: `app/Http/Controllers/Admin/CategoryController.php:39,78,95,128,143`
- Create: `database/migrations/2026_10_03_200001_drop_bulk_pricing_rules_columns.php`

**Prérequis bloquant.** Cette tâche ne s'exécute qu'après confirmation explicite de l'exploitant que `promotions:import-bulk-rules` a été lancé et les offres relues sur **chacune** des deux instances (legrandbazar.ci, charms-ci.com). Sans cette confirmation, s'arrêter et le signaler : la migration détruit les règles d'origine.

- [ ] **Step 1: Vérifier qu'aucun code ne lit plus les colonnes**

Run: `grep -rn "bulk_pricing_rules\|getBulkUnitPrice\|hasOwnBulkPricingRules" app/ resources/ routes/ --include="*.php" --include="*.vue"`

Expected: uniquement les occurrences listées ci-dessus, plus `app/Console/Commands/ImportBulkPricingRules.php`. Toute autre occurrence doit être traitée avant de continuer.

- [ ] **Step 2: Retirer le moteur des modèles**

Dans `app/Models/Product.php` : supprimer `getBulkUnitPrice()` et `hasOwnBulkPricingRules()` (lignes 251 à 287), retirer `'bulk_pricing_rules'` de `$fillable` et de `$casts`.

Dans `app/Models/Category.php` : supprimer le bloc `// ========== BULK PRICING ==========` et `getBulkUnitPrice()` (lignes 86 à 105), retirer `'bulk_pricing_rules'` de `$fillable` et de `$casts`.

- [ ] **Step 3: Retirer la saisie des contrôleurs admin**

Dans `app/Http/Controllers/Admin/ProductController.php` : supprimer la règle de validation `'bulk_pricing_rules' => 'nullable|json'` et les blocs de décodage (lignes 96-102 et 320-326), ainsi que la clé `'bulk_pricing_rules'` passée à la vue ligne 276.

Dans `app/Http/Controllers/Admin/CategoryController.php` : supprimer la méthode privée `decodeBulkPricingRules()`, ses deux appels, les deux règles de validation, et la clé ligne 39.

Retirer également les champs correspondants des formulaires Blade `resources/views/admin/products/partials/_form-main.blade.php` et des vues catégories s'ils y figurent.

- [ ] **Step 4: Écrire la migration de suppression**

`database/migrations/2026_10_03_200001_drop_bulk_pricing_rules_columns.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('bulk_pricing_rules');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('bulk_pricing_rules');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('bulk_pricing_rules')->nullable()->after('compare_price');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->json('bulk_pricing_rules')->nullable()->after('is_featured');
        });
    }
};
```

> `down()` recrée les colonnes mais **pas leur contenu** : la restauration de structure n'est pas une restauration de données. C'est la raison du prérequis bloquant du Step 0.

- [ ] **Step 5: Retirer les tests de la commande d'import devenus impossibles**

La commande `promotions:import-bulk-rules` lit des colonnes qui n'existent plus. Supprimer `app/Console/Commands/ImportBulkPricingRules.php` et `tests/Feature/ImportBulkPricingRulesTest.php` dans le **même commit** que la migration, pour qu'aucune révision du dépôt ne contienne une commande cassée.

- [ ] **Step 6: Lancer toute la suite**

Run: `composer test`
Expected: PASS

- [ ] **Step 7: Construire les assets**

Run: `npm run build`

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Models/Product.php app/Models/Category.php app/Http/Controllers/Admin/ProductController.php app/Http/Controllers/Admin/CategoryController.php
git add -A
git commit -m "refactor(promotions): retirer le moteur de prix par palier

Les colonnes bulk_pricing_rules et leur moteur sont supprimés après
reprise des regles via promotions:import-bulk-rules sur les deux
instances de production.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vérification finale

- [ ] `composer test` — toute la suite verte
- [ ] `npm run build` — assets générés et commités
- [ ] `vendor/bin/pint --test` — formatage conforme
- [ ] `grep -rn "bulk_pricing" app/ resources/ routes/` — aucune occurrence
- [ ] Parcours manuel : 3 t-shirts à 4 000 F → panier à 10 000 F, commande à 10 000 F, facture cohérente
- [ ] Parcours manuel : 5 t-shirts → 18 000 F ; 6 → 20 000 F
- [ ] Parcours manuel : coupon −20 % sur un panier mixte → appliqué hors articles en offre
- [ ] Aucun fichier Vue > 500 lignes : `find resources/js -name "*.vue" -exec wc -l {} + | sort -rn | head -5`
