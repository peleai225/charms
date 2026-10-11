# Service SKU robuste — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remplacer la génération de SKU non fiable actuelle (fallback aléatoire non vérifié sur les produits, aucune génération sur les variantes) par un service centralisé qui garantit l'unicité sous concurrence, préserve les SKU existants, et permet toujours la saisie manuelle.

**Architecture:** Un service `SkuGeneratorService` réserve atomiquement un numéro de séquence (table dédiée `sku_counters`, verrouillée via `lockForUpdate`) scopé par préfixe (catégorie ou `DIV` si aucune catégorie) pour les produits, et par produit pour les variantes. Le service est appelé depuis les hooks `creating` des modèles `Product`/`ProductVariant`, ce qui le branche automatiquement sur tous les chemins de création existants (formulaire admin, import CSV, tinker) sans dupliquer la logique dans chaque contrôleur.

**Tech Stack:** PHP 8.3, Laravel 12.51, MySQL (`chamse_testing` pour les tests), Vue 3 / Inertia pour les ajustements frontend, PHPUnit (classes, pas Pest) avec `RefreshDatabase`.

**Spec:** `docs/superpowers/specs/2026-10-11-sku-service-design.md`

## Global Constraints

- Les migrations sont additives uniquement (nouvelle table, colonne nullable) — aucune perte de données possible.
- Ne jamais lancer de commande Artisan avec `--env=testing` (il n'existe pas de `.env.testing` — ça cibler la base de dev `chamse`, pas `chamse_testing`). Utiliser exclusivement `php artisan test` ou `vendor/bin/phpunit`, qui appliquent automatiquement les variables d'environnement de `phpunit.xml`.
- La suite de tests a **11 échecs préexistants** sans rapport avec ce travail (5 `OrderCreationTest`, 1 `ProductStockTest::test_add_to_cart_fails_when_product_is_inactive`, 5 `ThermalPrinterTest`). Juger une régression par rapport à cette liste, pas par rapport à zéro échec — n'importe quel nouvel échec en dehors de cette liste est imputable à ce plan.
- Convention PHP : snake_case colonnes, camelCase méthodes/variables JS. Pas de commentaire sauf si une contrainte non évidente le justifie.
- Après tout changement Vue (Task 7), exécuter `npm run build` et committer `public/build/` — ce dépôt committe les assets de production (CLAUDE.md).
- Le SKU n'est jamais régénéré sur un `update()` d'un produit déjà existant — uniquement généré à la création quand le champ est vide.

## Review Focus

- Un produit sans catégorie (`category_id` null, état métier valide existant) : la génération doit utiliser le préfixe `DIV` sans planter. Couvert dans Task 4.
- Une catégorie sans `code` et dont le `slug` ne contient aucune lettre (ex. slug purement numérique ou vide) : le préfixe doit retomber sur `GEN`, jamais sur une chaîne vide qui casserait le format. Couvert dans Task 4.
- Deux catégories avec le même `code` (le cas non bloquant explicitement permis par le design) : leurs produits doivent recevoir des SKU distincts, pas une collision silencieuse. Couvert dans Task 4.
- Une ligne d'import CSV ou de formulaire où `sku` est une chaîne de seuls espaces (`"   "`) doit être traitée comme vide (génération automatique), pas comme un SKU littéral avec espaces. Couvert dans Task 4 et Task 6.
- `bulkStoreVariants` avec un lot où **toutes** les lignes ont un SKU vide ne doit pas lever d'erreur de validation sur `rows.*.sku` (règle désormais `nullable`) et doit produire autant de variantes que de lignes valides, chacune avec un SKU distinct généré. Couvert dans Task 6.

---

## Task 1: Migrations — table `sku_counters` et colonne `categories.code`

**Files:**
- Create: `database/migrations/2026_10_11_100000_create_sku_counters_table.php`
- Create: `database/migrations/2026_10_11_100001_add_code_to_categories_table.php`

**Interfaces:**
- Consumes: rien.
- Produces: table `sku_counters` (`id`, `scope_key` unique string, `next_value` unsigned int défaut 1, timestamps) ; colonne `categories.code` (string(10) nullable).

- [ ] **Step 1: Écrire la migration `sku_counters`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sku_counters', function (Blueprint $table) {
            $table->id();
            $table->string('scope_key')->unique();
            $table->unsignedInteger('next_value')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_counters');
    }
};
```

- [ ] **Step 2: Écrire la migration `add_code_to_categories_table`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
```

- [ ] **Step 3: Appliquer les migrations sur la base de test et vérifier**

Run: `DB_DATABASE=chamse_testing php artisan migrate --force`
Expected: les deux nouvelles migrations s'exécutent sans erreur (`Migrating:` puis `Migrated:` pour chacune).

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_10_11_100000_create_sku_counters_table.php database/migrations/2026_10_11_100001_add_code_to_categories_table.php
git commit -m "feat(sku): migrations sku_counters et categories.code"
```

---

## Task 2: `SkuCounter`, `SkuGenerationException` et le cœur du service (réservation de séquence + résolution de préfixe)

**Files:**
- Create: `app/Models/SkuCounter.php`
- Create: `app/Services/Sku/Exceptions/SkuGenerationException.php`
- Create: `app/Services/Sku/SkuGeneratorService.php`
- Test: `tests/Unit/Services/Sku/SkuGeneratorServiceTest.php`

**Interfaces:**
- Consumes: table `sku_counters` et colonne `categories.code` (Task 1).
- Produces: `SkuGeneratorService::generateForProduct(?Category $category): string`, `SkuGeneratorService::generateForVariant(Product $product): string` — ces deux méthodes publiques seront consommées par les Tasks 4 et 6. `SkuGenerationException` (extends `\RuntimeException`) — sera catchée par les contrôleurs dans les Tasks 4 et 6.

- [ ] **Step 1: Écrire le test unitaire du compteur et de la résolution de préfixe (échec attendu)**

```php
<?php

namespace Tests\Unit\Services\Sku;

use App\Models\Category;
use App\Models\SkuCounter;
use App\Services\Sku\SkuGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SkuGeneratorService
    {
        return app(SkuGeneratorService::class);
    }

    public function test_generates_product_sku_using_category_code(): void
    {
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-ELEC-000001', $sku);
    }

    public function test_product_sku_sequence_increments_per_prefix(): void
    {
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $first = $this->service()->generateForProduct($category);
        $second = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-ELEC-000001', $first);
        $this->assertSame('LGB-ELEC-000002', $second);
    }

    public function test_derives_prefix_from_slug_when_category_code_is_empty(): void
    {
        $category = Category::create(['name' => 'Mode Homme', 'slug' => 'mode-homme', 'code' => null]);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-MODE-000001', $sku);
    }

    public function test_falls_back_to_gen_prefix_when_slug_has_no_letters(): void
    {
        $category = Category::create(['name' => '2026', 'slug' => '2026', 'code' => null]);

        $sku = $this->service()->generateForProduct($category);

        $this->assertSame('LGB-GEN-000001', $sku);
    }

    public function test_uses_div_prefix_when_no_category(): void
    {
        $sku = $this->service()->generateForProduct(null);

        $this->assertSame('LGB-DIV-000001', $sku);
    }

    public function test_two_categories_sharing_the_same_code_never_collide(): void
    {
        $catA = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);
        $catB = Category::create(['name' => 'Électroménager', 'slug' => 'electromenager', 'code' => 'ELEC']);

        $fromA = $this->service()->generateForProduct($catA);
        $fromB = $this->service()->generateForProduct($catB);

        $this->assertNotSame($fromA, $fromB);
        $this->assertSame('LGB-ELEC-000001', $fromA);
        $this->assertSame('LGB-ELEC-000002', $fromB);
    }

    public function test_counter_is_created_on_first_use(): void
    {
        $this->assertSame(0, SkuCounter::count());

        $category = Category::create(['name' => 'Mode', 'slug' => 'mode', 'code' => 'MOD']);
        $this->service()->generateForProduct($category);

        $this->assertSame(1, SkuCounter::count());
        $this->assertSame(2, SkuCounter::where('scope_key', 'prefix:MOD')->first()->next_value);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier l'échec**

Run: `php artisan test --filter=SkuGeneratorServiceTest`
Expected: FAIL — `Class "App\Services\Sku\SkuGeneratorService" not found` (ou équivalent, le modèle `SkuCounter` et la colonne `code` existent déjà via Task 1).

- [ ] **Step 3: Créer le modèle `SkuCounter`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkuCounter extends Model
{
    protected $fillable = [
        'scope_key',
        'next_value',
    ];

    protected $casts = [
        'next_value' => 'integer',
    ];
}
```

- [ ] **Step 4: Créer `SkuGenerationException`**

```php
<?php

namespace App\Services\Sku\Exceptions;

class SkuGenerationException extends \RuntimeException
{
}
```

- [ ] **Step 5: Créer `SkuGeneratorService` (sans `generateForVariant` pour l'instant)**

```php
<?php

namespace App\Services\Sku;

use App\Models\Category;
use App\Models\Product;
use App\Models\SkuCounter;
use App\Services\Sku\Exceptions\SkuGenerationException;
use Illuminate\Support\Facades\DB;

class SkuGeneratorService
{
    private const MAX_ATTEMPTS = 5;

    public function generateForProduct(?Category $category): string
    {
        $prefix = $category ? $this->resolveCategoryPrefix($category) : 'DIV';
        $scopeKey = "prefix:{$prefix}";

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $sequence = $this->reserveSequence($scopeKey);
            $candidate = sprintf('LGB-%s-%06d', $prefix, $sequence);

            if (! Product::where('sku', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new SkuGenerationException(
            'Impossible de générer un SKU unique après plusieurs tentatives. Veuillez réessayer ou saisir un SKU manuellement.'
        );
    }

    private function resolveCategoryPrefix(Category $category): string
    {
        if (! empty($category->code)) {
            return $category->code;
        }

        $letters = preg_replace('/[^A-Z]/', '', strtoupper($category->slug ?? ''));
        $prefix = substr($letters, 0, 4);

        return $prefix !== '' ? $prefix : 'GEN';
    }

    private function reserveSequence(string $scopeKey): int
    {
        return DB::transaction(function () use ($scopeKey) {
            $counter = SkuCounter::where('scope_key', $scopeKey)->lockForUpdate()->first();

            if (! $counter) {
                try {
                    SkuCounter::create(['scope_key' => $scopeKey, 'next_value' => 1]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // On ne récupère que le cas précis d'une ligne déjà créée par une requête concurrente
                    // (code erreur MySQL 1062, doublon sur l'index unique de scope_key) : toute autre
                    // erreur base de données (table manquante, connexion perdue, etc.) doit remonter telle quelle.
                    if (($e->errorInfo[1] ?? null) !== 1062) {
                        throw $e;
                    }
                }
                $counter = SkuCounter::where('scope_key', $scopeKey)->lockForUpdate()->first();
            }

            $value = $counter->next_value;
            $counter->increment('next_value');

            return $value;
        });
    }
}
```

- [ ] **Step 6: Ajouter `code` au `$fillable` de `Category`**

Modify `app/Models/Category.php:15-26` — ajouter `'code'` dans le tableau `$fillable` :

```php
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'image',
        'parent_id',
        'order',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];
```

- [ ] **Step 7: Lancer les tests pour vérifier qu'ils passent**

Run: `php artisan test --filter=SkuGeneratorServiceTest`
Expected: PASS — 7 tests, 0 échec.

- [ ] **Step 8: Commit**

```bash
git add app/Models/SkuCounter.php app/Models/Category.php app/Services/Sku/Exceptions/SkuGenerationException.php app/Services/Sku/SkuGeneratorService.php tests/Unit/Services/Sku/SkuGeneratorServiceTest.php
git commit -m "feat(sku): service de generation SKU produit (compteur par prefixe)"
```

---

## Task 3: `generateForVariant` et épuisement des tentatives

**Files:**
- Modify: `app/Services/Sku/SkuGeneratorService.php`
- Test: `tests/Unit/Services/Sku/SkuGeneratorServiceTest.php`

**Interfaces:**
- Consumes: `SkuCounter`, `Product` (pour lire `$product->sku` et `$product->id`), `App\Models\ProductVariant`.
- Produces: `SkuGeneratorService::generateForVariant(Product $product): string` — consommé par Task 6.

- [ ] **Step 1: Ajouter les tests (échec attendu)**

Ajouter à `tests/Unit/Services/Sku/SkuGeneratorServiceTest.php` :

```php
    public function test_generates_variant_sku_from_parent_sku(): void
    {
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);

        $sku = $this->service()->generateForVariant($product);

        $this->assertSame('LGB-MODE-000001-01', $sku);
    }

    public function test_variant_sku_sequence_increments_per_product(): void
    {
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);

        $first = $this->service()->generateForVariant($product);
        $second = $this->service()->generateForVariant($product);

        $this->assertSame('LGB-MODE-000001-01', $first);
        $this->assertSame('LGB-MODE-000001-02', $second);
    }

    public function test_variant_counters_are_independent_per_product(): void
    {
        $productA = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $productB = Product::create(['name' => 'Pantalon', 'sale_price' => 8000, 'sku' => 'LGB-MODE-000002']);

        $this->assertSame('LGB-MODE-000001-01', $this->service()->generateForVariant($productA));
        $this->assertSame('LGB-MODE-000002-01', $this->service()->generateForVariant($productB));
    }

    public function test_throws_after_exhausting_retries_on_persistent_collision(): void
    {
        $category = Category::create(['name' => 'Mode', 'slug' => 'mode', 'code' => 'MOD']);

        // Pré-remplir manuellement les 5 premiers SKU possibles pour forcer l'épuisement des tentatives.
        for ($i = 1; $i <= 5; $i++) {
            Product::create([
                'name' => "Produit {$i}",
                'sale_price' => 1000,
                'sku' => sprintf('LGB-MOD-%06d', $i),
            ]);
        }

        $this->expectException(\App\Services\Sku\Exceptions\SkuGenerationException::class);

        $this->service()->generateForProduct($category);
    }

    public function test_other_database_exceptions_are_not_swallowed(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'code' => 'TST']);

        // Casse délibérément la table sku_counters pour forcer une vraie QueryException
        // ("table doesn't exist") au lieu de simuler une collision de SKU.
        \Illuminate\Support\Facades\Schema::drop('sku_counters');

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->service()->generateForProduct($category);
    }
```

- [ ] **Step 2: Lancer les tests pour vérifier l'échec**

Run: `php artisan test --filter=SkuGeneratorServiceTest`
Expected: FAIL sur les 3 tests qui appellent `generateForVariant` (méthode encore inexistante). `test_throws_after_exhausting_retries_on_persistent_collision` et `test_other_database_exceptions_are_not_swallowed` ne dépendent que de `generateForProduct`, déjà en place depuis Task 2 — ils devraient déjà PASS à ce stade (le mécanisme de retry et le catch resserré sur l'erreur 1062 existent déjà). Si l'un des deux échoue, c'est un signal que Task 2 n'est pas complète, pas une attente normale de cette étape.

- [ ] **Step 3: Ajouter `generateForVariant` au service**

Modify `app/Services/Sku/SkuGeneratorService.php` — ajouter après `generateForProduct` :

```php
    public function generateForVariant(Product $product): string
    {
        $scopeKey = "product_variant:{$product->id}";

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $sequence = $this->reserveSequence($scopeKey);
            $candidate = sprintf('%s-%02d', $product->sku, $sequence);

            if (! ProductVariant::where('sku', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new SkuGenerationException(
            'Impossible de générer un SKU de variante unique après plusieurs tentatives. Veuillez réessayer ou saisir un SKU manuellement.'
        );
    }
```

Ajouter l'import `use App\Models\ProductVariant;` en haut du fichier.

- [ ] **Step 4: Lancer les tests et ajuster si nécessaire**

Run: `php artisan test --filter=SkuGeneratorServiceTest`
Expected: PASS pour tous les tests de variante. Pour `test_throws_after_exhausting_retries_on_persistent_collision`, vérifier le résultat réel : avec 5 SKU déjà pris (séquences 1 à 5) et `MAX_ATTEMPTS = 5`, le service tente les séquences 1 à 5 (toutes occupées) et épuise ses tentatives sans jamais atteindre la séquence 6 → l'exception est bien levée. Si le test échoue parce que le service atteint la séquence 6 (libre) avant d'épuiser ses tentatives, ajuster le nombre de SKU pré-remplis dans le test à `MAX_ATTEMPTS` exactement (5), pas plus — ne jamais changer `MAX_ATTEMPTS` pour faire passer le test.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Sku/SkuGeneratorService.php tests/Unit/Services/Sku/SkuGeneratorServiceTest.php
git commit -m "feat(sku): generation SKU variante et epuisement des tentatives"
```

---

## Task 4: Brancher la génération sur `Product::boot()` et `ProductController::store`

**Files:**
- Modify: `app/Models/Product.php:71-91`
- Modify: `app/Http/Controllers/Admin/ProductController.php:72-155`
- Test: `tests/Feature/Admin/ProductSkuGenerationTest.php`

**Interfaces:**
- Consumes: `SkuGeneratorService::generateForProduct(?Category $category): string` (Task 2).
- Produces: comportement observable — tout `Product::create()` sans `sku` (ou avec `sku` vide/espaces) reçoit un SKU auto-généré ; ce comportement sera consommé tel quel par l'import CSV (`ImportExportController::processProductRow`, aucune modification de ce fichier nécessaire — il passe déjà `'sku' => $sku ?: null`).

- [ ] **Step 1: Écrire le test feature (échec attendu)**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('secret123'),
        ]);
    }

    private function baseProductPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Produit Test',
            'purchase_price' => 1000,
            'sale_price' => 2000,
            'tax_rate' => 20,
            'stock_quantity' => 10,
            'stock_alert_threshold' => 5,
            'status' => 'active',
        ], $overrides);
    }

    public function test_store_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'category_id' => $category->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-ELEC-000001']);
    }

    public function test_store_with_whitespace_only_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'category_id' => $category->id,
            'sku' => '   ',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-ELEC-000001']);
    }

    public function test_store_without_category_uses_div_prefix(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'LGB-DIV-000001']);
    }

    public function test_store_with_manual_sku_is_preserved(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'sku' => 'FOURNISSEUR-XYZ-42',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Test', 'sku' => 'FOURNISSEUR-XYZ-42']);
    }

    public function test_store_with_duplicate_manual_sku_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        Product::create(['name' => 'Existant', 'sale_price' => 1000, 'sku' => 'LGB-DIV-000001']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->baseProductPayload([
            'sku' => 'LGB-DIV-000001',
        ]));

        $response->assertSessionHasErrors('sku');
        $this->assertSame(1, Product::where('sku', 'LGB-DIV-000001')->count());
    }

    public function test_update_does_not_change_sku_when_only_name_changes(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'Ancien Nom', 'sale_price' => 2000, 'sku' => 'LGB-DIV-000001']);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), $this->baseProductPayload([
            'name' => 'Nouveau Nom',
            'sku' => 'LGB-DIV-000001',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nouveau Nom', 'sku' => 'LGB-DIV-000001']);
    }

    public function test_csv_import_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();

        $csv = "sku;name;sale_price\n;Produit Import;1500\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.import-export.import-products'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Produit Import', 'sku' => 'LGB-DIV-000001']);
    }
}
```

Ce dernier test vérifie une préoccupation technique réelle : l'import CSV ouvre sa propre transaction autour de tout le fichier (`ImportExportController::importProducts`, `DB::beginTransaction()`), et le service de génération ouvre lui-même une transaction pour verrouiller le compteur. Sous MySQL, une transaction imbriquée devient une savepoint — ce test garantit que cette imbrication fonctionne réellement en conditions réelles, pas seulement en théorie.

- [ ] **Step 2: Lancer le test pour vérifier l'échec**

Run: `php artisan test --filter=ProductSkuGenerationTest`
Expected: FAIL — `test_store_without_sku_auto_generates_one` échoue (le SKU généré aujourd'hui est un `Str::random(10)`, pas `LGB-ELEC-000001`) ; `test_store_without_category_uses_div_prefix` échoue de même ; les autres peuvent déjà passer par coïncidence selon le code actuel — noter lesquels échouent réellement avant de continuer.

- [ ] **Step 3: Brancher le service dans `Product::boot()`**

Modify `app/Models/Product.php:71-91` :

```php
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $slug = $baseSlug;
                $counter = 1;
                // Générer un slug unique (l'index ne contraint que les produits actifs)
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $product->slug = $slug;
            }
            if (empty(trim((string) $product->sku))) {
                $product->sku = app(\App\Services\Sku\SkuGeneratorService::class)->generateForProduct($product->category);
            }
        });
    }
```

- [ ] **Step 4: Assouplir la validation `sku` dans `ProductController::store`**

Modify `app/Http/Controllers/Admin/ProductController.php:78` — remplacer :

```php
            'sku' => 'required|string|unique:products',
```

par :

```php
            'sku' => 'nullable|string|max:100|unique:products',
```

- [ ] **Step 5: Lancer les tests et vérifier qu'ils passent**

Run: `php artisan test --filter=ProductSkuGenerationTest`
Expected: PASS — 7 tests, 0 échec.

- [ ] **Step 6: Lancer la suite complète pour vérifier l'absence de régression**

Run: `php artisan test`
Expected: le nombre d'échecs ne dépasse pas les 11 préexistants listés dans Global Constraints. Si un échec supplémentaire apparaît, l'investiguer avant de continuer — ne pas l'ignorer en le mettant sur le compte de la baseline sans l'avoir vérifié.

- [ ] **Step 7: Commit**

```bash
git add app/Models/Product.php app/Http/Controllers/Admin/ProductController.php tests/Feature/Admin/ProductSkuGenerationTest.php
git commit -m "feat(sku): generation automatique du SKU produit a la creation"
```

---

## Task 5: Champ `categories.code` dans l'admin (validation, normalisation, avertissement de collision)

**Files:**
- Modify: `app/Http/Controllers/Admin/CategoryController.php:67-162`
- Test: `tests/Feature/Admin/CategoryCodeTest.php`

**Interfaces:**
- Consumes: colonne `categories.code` (Task 1), `Category::$fillable` incluant `code` (Task 2).
- Produces: `categories.code` normalisé en majuscules et validé par un contrôleur admin, consommé par `SkuGeneratorService::resolveCategoryPrefix` (Task 2, déjà en place) et par le frontend (Task 7).

- [ ] **Step 1: Écrire le test feature (échec attendu)**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryCodeTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('secret123'),
        ]);
    }

    public function test_store_normalizes_code_to_uppercase(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'code' => 'elec',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Électronique', 'code' => 'ELEC']);
    }

    public function test_store_rejects_code_with_invalid_characters(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'code' => 'EL C!',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_store_allows_empty_code(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Électronique', 'code' => null]);
    }

    public function test_store_does_not_block_duplicate_code(): void
    {
        $admin = $this->makeAdmin();
        Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électroménager',
            'code' => 'ELEC',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('code');
        $this->assertDatabaseHas('categories', ['name' => 'Électroménager', 'code' => 'ELEC']);
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier l'échec**

Run: `php artisan test --filter=CategoryCodeTest`
Expected: FAIL — le champ `code` n'est pas encore validé ni normalisé (il est ignoré silencieusement par `$validator->validated()` puisqu'absent des règles).

- [ ] **Step 3: Ajouter la validation et la normalisation dans `store()`**

Modify `app/Http/Controllers/Admin/CategoryController.php:67-98` :

```php
    public function store(Request $request)
    {
        $request->merge([
            'code' => $request->filled('code') ? strtoupper(trim($request->input('code'))) : null,
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|regex:/^[A-Z0-9]{2,10}$/',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categories.index', ['open_modal' => 'create'])
                ->withInput()
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);

        ActivityLog::logCreated($category, "Catégorie {$category->name} créée");

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }
```

- [ ] **Step 4: Appliquer le même changement à `update()`**

Modify `app/Http/Controllers/Admin/CategoryController.php:113-125` — ajouter le même `$request->merge(...)` juste avant la construction du `$validator`, et ajouter la même règle `'code' => 'nullable|string|regex:/^[A-Z0-9]{2,10}$/',` dans son tableau de règles.

- [ ] **Step 5: Lancer les tests et vérifier qu'ils passent**

Run: `php artisan test --filter=CategoryCodeTest`
Expected: PASS — 4 tests, 0 échec.

- [ ] **Step 6: Exposer `code` dans `CategoryController::index()`**

Modify `app/Http/Controllers/Admin/CategoryController.php:26-39` — ajouter `'code' => $c->code,` dans le tableau retourné par la closure `$mapCat` :

```php
        $mapCat = fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'code' => $c->code,
            'image' => $c->image,
            'is_active' => $c->is_active,
            'is_featured' => $c->is_featured,
            'parent_id' => $c->parent_id,
            'order' => $c->order,
            'products_count' => $c->products_count,
            'description' => $c->description,
            'meta_title' => $c->meta_title,
            'meta_description' => $c->meta_description,
        ];
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/CategoryController.php tests/Feature/Admin/CategoryCodeTest.php
git commit -m "feat(sku): champ code categorie (validation, normalisation majuscules)"
```

---

## Task 6: Brancher la génération sur les variantes (`ProductVariant::boot()`, `storeVariant`, `bulkStoreVariants`)

**Files:**
- Modify: `app/Models/ProductVariant.php`
- Modify: `app/Http/Controllers/Admin/ProductController.php:522-736`
- Test: `tests/Feature/Admin/ProductVariantSkuGenerationTest.php`

**Interfaces:**
- Consumes: `SkuGeneratorService::generateForVariant(Product $product): string` (Task 3).
- Produces: comportement observable — `storeVariant`/`bulkStoreVariants` acceptent un `sku` vide et génèrent automatiquement, sans régression sur le comportement existant de `bulkStoreVariants` pour les doublons explicites.

- [ ] **Step 1: Écrire le test feature (échec attendu)**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductVariantSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('secret123'),
        ]);
    }

    private function makeProductWithColorAttribute(): array
    {
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);

        return [$product, $attribute, $value];
    }

    public function test_store_variant_without_sku_auto_generates_one(): void
    {
        $admin = $this->makeAdmin();
        [$product, $attribute, $value] = $this->makeProductWithColorAttribute();

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.store', $product), [
            'attributes' => [$attribute->id => $value->id],
            'stock_quantity' => 5,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
    }

    public function test_store_variant_with_manual_sku_is_preserved(): void
    {
        $admin = $this->makeAdmin();
        [$product, $attribute, $value] = $this->makeProductWithColorAttribute();

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.store', $product), [
            'attributes' => [$attribute->id => $value->id],
            'stock_quantity' => 5,
            'sku' => 'FOURNISSEUR-ROUGE',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'FOURNISSEUR-ROUGE']);
    }

    public function test_bulk_store_variants_generates_sku_for_blank_rows_and_keeps_explicit_ones(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);
        $bleu = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Bleu']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => '', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
                ['sku' => 'FOURNISSEUR-BLEU', 'stock_quantity' => 3, 'attributes' => [$attribute->id => $bleu->id]],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(2, $product->variants()->count());
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'FOURNISSEUR-BLEU']);
    }

    public function test_bulk_store_variants_with_all_blank_skus_generates_distinct_ones(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);
        $bleu = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Bleu']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => '', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
                ['sku' => '', 'stock_quantity' => 3, 'attributes' => [$attribute->id => $bleu->id]],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(2, $response->json('created'));
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-01']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'LGB-MODE-000001-02']);
    }

    public function test_bulk_store_variants_still_skips_explicit_duplicate_sku(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::create(['name' => 'T-shirt', 'sale_price' => 5000, 'sku' => 'LGB-MODE-000001']);
        ProductVariant::create(['product_id' => $product->id, 'sku' => 'DEJA-PRIS']);
        $attribute = Attribute::create(['name' => 'Couleur', 'type' => 'color']);
        $rouge = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => 'Rouge']);

        $response = $this->actingAs($admin)->postJson(route('admin.products.variants.bulk', $product), [
            'rows' => [
                ['sku' => 'DEJA-PRIS', 'stock_quantity' => 5, 'attributes' => [$attribute->id => $rouge->id]],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, ProductVariant::where('sku', 'DEJA-PRIS')->count());
        $this->assertSame(0, $response->json('created'));
        $this->assertSame(1, $response->json('skipped'));
    }
}
```

- [ ] **Step 2: Lancer le test pour vérifier l'échec**

Run: `php artisan test --filter=ProductVariantSkuGenerationTest`
Expected: FAIL — `sku` est `required` dans les deux endpoints, donc les requêtes à SKU vide échouent la validation (422 avec erreur sur `sku`, pas le comportement attendu).

- [ ] **Step 3: Ajouter `boot()` à `ProductVariant`**

Modify `app/Models/ProductVariant.php` — ajouter après la déclaration des `$casts` (après la ligne `];` qui ferme `protected $casts`) :

```php
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variant) {
            if (empty(trim((string) $variant->sku)) && $variant->product_id) {
                $variant->sku = app(\App\Services\Sku\SkuGeneratorService::class)->generateForVariant($variant->product);
            }
        });
    }
```

- [ ] **Step 4: Assouplir la validation dans `storeVariant`**

Modify `app/Http/Controllers/Admin/ProductController.php:529` — remplacer :

```php
            'sku' => 'required|string|unique:product_variants',
```

par :

```php
            'sku' => 'nullable|string|max:100|unique:product_variants',
```

Et modifier la création de la variante ligne 547 pour passer `null` plutôt qu'une chaîne vide :

```php
                'sku' => $validated['sku'] ?? null,
```

- [ ] **Step 5: Ajuster `bulkStoreVariants`**

Modify `app/Http/Controllers/Admin/ProductController.php:622` — remplacer :

```php
            'rows.*.sku' => 'required|string|max:100',
```

par :

```php
            'rows.*.sku' => 'nullable|string|max:100',
```

Modify le corps de la boucle (lignes 644-654) — remplacer :

```php
                $sku = trim($row['sku'] ?? '');
                if ($sku === '') {
                    continue;
                }

                if (ProductVariant::where('sku', $sku)->exists()) {
                    $skipped++;

                    continue;
                }
```

par :

```php
                $sku = trim($row['sku'] ?? '');

                if ($sku !== '' && ProductVariant::where('sku', $sku)->exists()) {
                    $skipped++;

                    continue;
                }
```

Et la création de la variante (ligne 668) — remplacer :

```php
                    'sku' => $sku,
```

par :

```php
                    'sku' => $sku !== '' ? $sku : null,
```

- [ ] **Step 6: Lancer les tests et vérifier qu'ils passent**

Run: `php artisan test --filter=ProductVariantSkuGenerationTest`
Expected: PASS — 5 tests, 0 échec.

- [ ] **Step 7: Lancer la suite complète**

Run: `php artisan test`
Expected: pas d'échec supplémentaire au-delà des 11 préexistants.

- [ ] **Step 8: Commit**

```bash
git add app/Models/ProductVariant.php app/Http/Controllers/Admin/ProductController.php tests/Feature/Admin/ProductVariantSkuGenerationTest.php
git commit -m "feat(sku): generation automatique du SKU variante (store + bulk)"
```

---

## Task 7: Ajustements frontend (Vue) — champs SKU optionnels, champ code catégorie

**Files:**
- Modify: `resources/js/Pages/Admin/Products/Create.vue:141-151`
- Modify: `resources/js/Pages/Admin/Products/Edit.vue` (lignes ~45, ~106, ~220-250, ~844-849 — numéros approximatifs, à relocaliser par le contenu exact ci-dessous puisque le fichier peut avoir légèrement bougé depuis l'audit)
- Modify: `resources/js/Pages/Admin/Categories/Index.vue` (refs `createForm`/`editForm`, `openEdit()`, et les deux blocs marqués `<!-- Statuts -->`)

**Interfaces:**
- Consumes: le backend accepte déjà un `sku` vide pour `store`/`storeVariant`/`bulkStoreVariants` (Tasks 4 et 6) ; `categories.code` existe et est exposé par `CategoryController::index()` (Task 5).
- Produces: aucune (changements d'UI terminaux). Vérification manuelle uniquement — pas de test automatisé JS dans ce projet (aucune suite JS n'existe actuellement, confirmé par l'absence de config Vitest/Jest dans `package.json`).

- [ ] **Step 1: Rendre le champ SKU optionnel sur le formulaire de création produit**

Modify `resources/js/Pages/Admin/Products/Create.vue:146-150` — remplacer :

```html
                                <label class="block text-xs font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                                <input v-model="form.sku" type="text" required
                                    class="w-full h-9 px-3 text-[13px] font-mono border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.sku ? 'border-red-300' : 'border-gray-200'">
                                <p v-if="form.errors.sku" class="mt-1 text-[11px] text-red-500">{{ form.errors.sku }}</p>
```

par :

```html
                                <label class="block text-xs font-medium text-gray-700 mb-1">SKU</label>
                                <input v-model="form.sku" type="text" placeholder="Laisser vide pour génération automatique"
                                    class="w-full h-9 px-3 text-[13px] font-mono border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="form.errors.sku ? 'border-red-300' : 'border-gray-200'">
                                <p v-if="form.errors.sku" class="mt-1 text-[11px] text-red-500">{{ form.errors.sku }}</p>
```

- [ ] **Step 2: Retirer le caractère obligatoire du SKU de variante unique (Edit.vue)**

Modify `resources/js/Pages/Admin/Products/Edit.vue` — localiser le bloc contenant `<label class="block text-[11px] font-medium text-gray-600 mb-1">SKU <span class="text-red-500">*</span></label>` suivi de `<input v-model="variantForm.sku" type="text" required`, et remplacer par :

```html
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">SKU</label>
                            <input v-model="variantForm.sku" type="text" placeholder="Auto si vide"
                                class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
```

- [ ] **Step 3: Retirer le blocage JS côté client sur l'ajout d'une variante unique**

Modify `resources/js/Pages/Admin/Products/Edit.vue` — localiser et **supprimer entièrement** la ligne :

```js
    if (!variantForm.value.sku.trim()) { notify('Le SKU est obligatoire.', 'error'); return }
```

(dans la fonction qui soumet `variantForm` — vérifier qu'aucune autre validation de cette fonction n'en dépend avant de supprimer).

- [ ] **Step 4: Laisser passer les lignes à SKU vide dans la grille de variantes en masse**

Modify `resources/js/Pages/Admin/Products/Edit.vue` — dans `submitBulk()`, remplacer :

```js
    const rows = generatedRows.value.filter(r => !r.remove && r.sku.trim())
    if (!rows.length) return

    const skus = rows.map(r => r.sku.trim().toUpperCase())
    if (skus.length !== new Set(skus).size) {
        bulkError.value = 'Certaines lignes ont des SKU identiques.'
        setTimeout(() => { bulkError.value = null }, 6000)
        return
    }
```

par :

```js
    const rows = generatedRows.value.filter(r => !r.remove)
    if (!rows.length) return

    const nonEmptySkus = rows.map(r => r.sku.trim().toUpperCase()).filter(s => s !== '')
    if (nonEmptySkus.length !== new Set(nonEmptySkus).size) {
        bulkError.value = 'Certaines lignes ont des SKU identiques.'
        setTimeout(() => { bulkError.value = null }, 6000)
        return
    }
```

Et dans le corps envoyé au serveur (`body: JSON.stringify({ rows: rows.map(r => ({ sku: r.sku, ... }))`), remplacer `sku: r.sku,` par `sku: r.sku.trim(),`.

- [ ] **Step 5: Ajouter le champ `code` au formulaire de création de catégorie**

Modify `resources/js/Pages/Admin/Categories/Index.vue:20-30` — ajouter `code: '',` dans `createForm` :

```js
const createForm = useForm({
    name:                '',
    code:                '',
    description:         '',
    parent_id:           '',
    is_active:           true,
    is_featured:         false,
    order:               0,
    image:               null,
    meta_title:          '',
    meta_description:    '',
})
```

Puis juste avant le bloc `<!-- Statuts -->` du formulaire de création (le premier des deux, ligne ~373), ajouter :

```html
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Code (préfixe SKU)</label>
                        <input v-model="createForm.code" type="text" maxlength="10" placeholder="Ex. ELEC"
                            class="w-full h-9 px-3 text-[13px] font-mono uppercase border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="createForm.errors.code ? 'border-red-300' : 'border-gray-200'">
                        <p v-if="createForm.errors.code" class="mt-1 text-[11px] text-red-500">{{ createForm.errors.code }}</p>
                    </div>
```

- [ ] **Step 6: Même ajout pour le formulaire d'édition de catégorie**

Modify `resources/js/Pages/Admin/Categories/Index.vue:58-68` — ajouter `code: '',` dans `editForm` (même structure que `createForm`).

Modify `openEdit(cat)` (ligne ~70-84) — ajouter après `editForm.name = cat.name` :

```js
    editForm.code = cat.code ?? ''
```

Puis juste avant le second bloc `<!-- Statuts -->` (ligne ~507), ajouter le même champ que Step 5 mais avec `editForm` à la place de `createForm`.

- [ ] **Step 7: Avertissement non bloquant en cas de code dupliqué**

Le backend (Task 5) accepte déjà un `code` dupliqué sans erreur — il manque l'avertissement visuel côté client prévu par le design. Modify `resources/js/Pages/Admin/Categories/Index.vue` — ajouter une fonction utilitaire près des autres fonctions du script :

```js
function duplicateCodeWarning(code, excludeId = null) {
    const normalized = (code || '').trim().toUpperCase()
    if (!normalized) return false
    return (props.categories ?? []).some(c => c.id !== excludeId && (c.code || '').toUpperCase() === normalized)
}
```

Puis, juste après l'input du champ `code` ajouté en Step 5 (formulaire de création), ajouter :

```html
                        <p v-if="duplicateCodeWarning(createForm.code)" class="mt-1 text-[11px] text-amber-600">Ce code est déjà utilisé par une autre catégorie — les deux partageront la même numérotation de SKU.</p>
```

Et après l'input équivalent du formulaire d'édition (Step 6) :

```html
                        <p v-if="duplicateCodeWarning(editForm.code, editingId)" class="mt-1 text-[11px] text-amber-600">Ce code est déjà utilisé par une autre catégorie — les deux partageront la même numérotation de SKU.</p>
```

- [ ] **Step 8: Build et vérification manuelle**

Run: `npm run build`
Expected: build réussi sans erreur.

Vérification manuelle (pas de test automatisé JS disponible dans ce projet) :
1. Ouvrir `/admin/products/create`, laisser le champ SKU vide, enregistrer → le produit créé doit avoir un SKU du type `LGB-{PREFIXE}-000001`.
2. Ouvrir une fiche produit existante, section variantes, ouvrir "Ajouter une variante", laisser le SKU vide, enregistrer → la variante doit recevoir un SKU du type `{sku_parent}-01`.
3. Utiliser la grille de génération en masse, vider le champ SKU d'une des lignes générées, soumettre → cette ligne doit être créée avec un SKU auto-généré, les autres lignes gardent leur suggestion.
4. Ouvrir la création de catégorie, taper un code en minuscules avec un espace (ex. "el ec"), enregistrer → doit être rejeté avec un message d'erreur sur le champ code.
5. Créer une catégorie avec un code déjà utilisé par une autre → doit être accepté, avec le message d'avertissement ambre affiché sous le champ dès la saisie (avant même de soumettre).

- [ ] **Step 9: Commit**

```bash
npm run build
git add resources/js/Pages/Admin/Products/Create.vue resources/js/Pages/Admin/Products/Edit.vue resources/js/Pages/Admin/Categories/Index.vue public/build
git commit -m "feat(sku): sku optionnel sur les formulaires produit/variante, code categorie"
```

---

## Task 8: Vérification finale de la suite de tests et revue de branche

**Files:** aucun changement de fichier — tâche de vérification uniquement.

**Interfaces:** aucune nouvelle interface — vérifie l'intégration de toutes les tâches précédentes.

- [ ] **Step 1: Lancer la suite complète**

Run: `php artisan test`
Expected: tous les tests des Tasks 2 à 6 passent ; le total d'échecs ne dépasse pas les 11 préexistants (voir Global Constraints). Si un nouvel échec apparaît, l'investiguer et le corriger avant de continuer — ne pas clore la tâche avec un échec non expliqué.

- [ ] **Step 2: Vérifier qu'aucun fichier `public/build/` généré n'a été oublié**

Run: `git status`
Expected: arbre de travail propre après le commit de Task 7 — aucun fichier de build non committé.

- [ ] **Step 3: Relire le diff complet de la branche par rapport à `main`**

Run: `git diff main --stat`
Expected: la liste des fichiers modifiés correspond exactement à la liste de la section « Files » de chaque tâche (2 migrations, `Product.php`, `ProductVariant.php`, `Category.php`, `SkuCounter.php`, `SkuGeneratorService.php`, `SkuGenerationException.php`, `ProductController.php`, `CategoryController.php`, 3 fichiers Vue, les fichiers de test, `public/build/*`). Aucun fichier hors de ce périmètre ne doit apparaître.
