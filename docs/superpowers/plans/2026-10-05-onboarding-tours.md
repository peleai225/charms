# Visites guidées du back-office — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter au back-office un système de visites guidées interactives (spotlight + bulles, façon onboarding grand public), démarrant au premier passage d'un gérant puis rejouable, avec quatre visites en v1.

**Architecture:** Un moteur unique basé sur **driver.js** (ciblage DOM par sélecteur, donc indépendant du framework), chargé une fois dans l'habillage admin commun à Blade / Livewire / Inertia. Chaque page porte un repère `data-tour-page` ; le moteur lit ce repère au chargement et à chaque navigation Inertia, démarre la visite si l'utilisateur ne l'a pas encore vue, et enregistre « vue » dans une colonne `tours_seen` du compte via un endpoint dédié.

**Tech Stack:** PHP 8.2, Laravel 12, PHPUnit 11, Inertia.js + Vue 3.5, Vite 7, driver.js 1.x, Node (pour un test de validation des configs).

**Spec:** `docs/superpowers/specs/2026-10-05-onboarding-tours-design.md`

## Global Constraints

- Conventions : **snake_case** PHP, **camelCase** JS, **kebab-case** fichiers Vue. Langue d'interface : **français**.
- `public/build/` est commité : toute tâche touchant du JS/CSS/Vue finit, au plus tard en dernière tâche, par `npm run build` **et** le commit des assets générés.
- Police **Inter**, couleur primaire `#2563EB`. Icônes Lucide. Aucune donnée fictive.
- Format PHP : `vendor/bin/pint` sur les fichiers PHP touchés avant chaque commit.
- Tests : `RefreshDatabase`, nommage `test_…`, base `chamse_testing` (`phpunit.xml`). Lancer via `composer test` ou `vendor/bin/phpunit <chemin>`. **Ne jamais** passer `--env=testing` (écraserait la base de dev).
- La suite comporte **11 échecs préexistants** (OrderCreationTest, ProductStockTest, ThermalPrinterTest) : juger une régression par rapport à ce socle, pas par rapport à zéro.
- Les visites couvertes en v1 (clés) : `menus`, `promotions.index`, `products.index`, `orders.index`. Toute clé envoyée à l'endpoint hors de cette liste est refusée.

## Review Focus

Classes d'entrée que la spec implique sans qu'une tâche ne les exerce spontanément.

1. **`tours_seen` nul en base** (comptes créés avant la migration) — l'endpoint et l'injection Blade doivent traiter `null` comme `[]`, jamais planter. *(test en Task 1)*
2. **Clé de visite inconnue** envoyée à l'endpoint — refus `422`, rien n'est écrit. *(test en Task 1)*
3. **Clé déjà présente** dans `tours_seen` — l'endpoint est idempotent, pas de doublon. *(test en Task 1)*
4. **Élément cible absent du DOM** (donnée vide, bouton masqué par un droit) — l'étape est retirée avant démarrage ; si plus aucune étape, aucune visite ne s'ouvre. *(garanti par `stepsFor()` en Task 3 ; vérifié en QA manuel Task 5)*
5. **Navigation Inertia pendant une visite** — la visite en cours se ferme avant de réévaluer la nouvelle page. *(garanti par `driver().destroy()` sur `inertia:before` en Task 3 ; vérifié en QA manuel Task 5)*

---

## Structure des fichiers

### Créés

| Fichier | Responsabilité |
|---|---|
| `database/migrations/2026_10_05_100001_add_tours_seen_to_users_table.php` | Colonne `tours_seen` (json, nullable) |
| `app/Http/Controllers/Admin/OnboardingController.php` | Endpoint `markSeen` + liste blanche des clés |
| `tests/Feature/OnboardingSeenTest.php` | Tests de l'endpoint |
| `resources/js/onboarding/tours.mjs` | Données des 4 visites (pur data, importable par Node et Vite) |
| `resources/js/onboarding/validate-tours.mjs` | Test de validation des configs (exécuté par Node) |
| `resources/js/onboarding/api.js` | `markSeen(tour)` — POST vers l'endpoint |
| `resources/js/onboarding/index.js` | Chef d'orchestre : détection page, démarrage, rejouer |
| `resources/js/onboarding/driver-theme.css` | Habillage driver.js aux couleurs Chamse |
| `resources/views/partials/onboarding.blade.php` | Injection du bootstrap + `@vite` de l'entrée |

### Modifiés

| Fichier | Nature |
|---|---|
| `app/Models/User.php` | Cast `tours_seen` → `array` |
| `routes/web.php` | Route `admin.onboarding.seen` dans le groupe admin |
| `vite.config.js` | Entrée `resources/js/onboarding/index.js` |
| `resources/views/layouts/admin.blade.php` | `@include('partials.onboarding')` + bouton « ? » Rejouer dans la barre du haut |
| `resources/views/layouts/admin-nav-item.blade.php` | Paramètre optionnel `tour` → attribut `data-tour` |
| `resources/js/Pages/Admin/Promotions/Index.vue` | `data-tour-page="promotions.index"` + repères `data-tour` |
| `resources/js/Pages/Admin/Products/Index.vue` | `data-tour-page="products.index"` + repères |
| `resources/js/Pages/Admin/Orders/Index.vue` | `data-tour-page="orders.index"` + repères |
| Vue du Dashboard admin | `data-tour-page="menus"` sur la racine |

---

## Task 1: Backend — colonne `tours_seen` et endpoint `markSeen`

**Files:**
- Create: `database/migrations/2026_10_05_100001_add_tours_seen_to_users_table.php`
- Create: `app/Http/Controllers/Admin/OnboardingController.php`
- Modify: `app/Models/User.php`
- Modify: `routes/web.php` (groupe admin, après les offres par lot ~ligne 395)
- Test: `tests/Feature/OnboardingSeenTest.php`

**Interfaces:**
- Produces :
  - `OnboardingController::TOURS` — `string[]`, liste blanche des clés
  - `POST admin.onboarding.seen` — corps `{ tour: string }`, réponse `204` ou `422`
  - `User->tours_seen` — `array` (défaut `[]`)

- [ ] **Step 1: Écrire la migration**

`database/migrations/2026_10_05_100001_add_tours_seen_to_users_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('tours_seen')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tours_seen');
        });
    }
};
```

- [ ] **Step 2: Caster l'attribut sur `User`**

Dans `app/Models/User.php`, méthode `casts()`, ajouter la ligne :

```php
            'tours_seen' => 'array',
```

(La colonne n'est **pas** ajoutée à `$fillable` : l'endpoint l'écrit par affectation directe, jamais par remplissage de masse.)

- [ ] **Step 3: Écrire le test en échec**

`tests/Feature/OnboardingSeenTest.php` :

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingSeenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_rejected(): void
    {
        $this->postJson(route('admin.onboarding.seen'), ['tour' => 'menus'])
            ->assertStatus(401);
    }

    public function test_marks_a_tour_as_seen(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'menus'])
            ->assertNoContent();

        $this->assertSame(['menus'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº1 — tours_seen nul au départ. */
    public function test_null_tours_seen_is_treated_as_empty(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertNull($user->tours_seen);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'products.index'])
            ->assertNoContent();

        $this->assertSame(['products.index'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº3 — idempotence. */
    public function test_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->tours_seen = ['menus'];
        $user->save();

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'menus'])
            ->assertNoContent();

        $this->assertSame(['menus'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº2 — clé inconnue. */
    public function test_rejects_unknown_tour(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'bogus'])
            ->assertStatus(422);

        $this->assertNull($user->fresh()->tours_seen);
    }
}
```

- [ ] **Step 4: Lancer le test pour vérifier qu'il échoue**

Run: `vendor/bin/phpunit tests/Feature/OnboardingSeenTest.php`
Expected: FAIL — route `admin.onboarding.seen` non définie.

- [ ] **Step 5: Écrire le contrôleur**

`app/Http/Controllers/Admin/OnboardingController.php` :

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /** Liste blanche des visites connues. */
    public const TOURS = [
        'menus',
        'promotions.index',
        'products.index',
        'orders.index',
    ];

    public function markSeen(Request $request): Response
    {
        $validated = $request->validate([
            'tour' => ['required', 'string', Rule::in(self::TOURS)],
        ]);

        $user = $request->user();
        $seen = $user->tours_seen ?? [];

        if (! in_array($validated['tour'], $seen, true)) {
            $seen[] = $validated['tour'];
            $user->tours_seen = $seen;
            $user->save();
        }

        return response()->noContent();
    }
}
```

- [ ] **Step 6: Déclarer la route**

Dans `routes/web.php`, à la suite des offres par lot (après la ligne `promotions.margin-preview`, dans le même groupe admin) :

```php
            // Visites guidées (onboarding)
            Route::post('/onboarding/seen', [\App\Http\Controllers\Admin\OnboardingController::class, 'markSeen'])->name('onboarding.seen');
```

- [ ] **Step 7: Lancer le test pour vérifier qu'il passe**

Run: `vendor/bin/phpunit tests/Feature/OnboardingSeenTest.php`
Expected: PASS — 5 tests.

> Si `test_guests_are_rejected` renvoie 302 au lieu de 401, c'est que le groupe admin redirige les invités : remplacer `assertStatus(401)` par `assertRedirect()`. Vérifier le middleware du groupe avant d'adapter.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Http/Controllers/Admin/OnboardingController.php app/Models/User.php
git add database/migrations/2026_10_05_100001_add_tours_seen_to_users_table.php app/Http/Controllers/Admin/OnboardingController.php app/Models/User.php routes/web.php tests/Feature/OnboardingSeenTest.php
git commit -m "feat(onboarding): colonne tours_seen et endpoint markSeen

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Task 2: Définitions des visites et validateur

**Files:**
- Create: `resources/js/onboarding/tours.mjs`
- Create: `resources/js/onboarding/validate-tours.mjs`

**Interfaces:**
- Produces :
  - `tours` — objet `{ [pageKey: string]: Step[] }`, `Step = { element: string, title: string, intro: string, side?: string }`
  - Les clés doivent correspondre exactement à `OnboardingController::TOURS` (Task 1).

- [ ] **Step 1: Écrire les définitions**

`resources/js/onboarding/tours.mjs` (pur data, aucun import — importable par Node et par Vite) :

```js
// Visites guidées du back-office. Les sélecteurs reposent sur des attributs
// data-tour posés dans les vues (Task 4). Chaque étape pointe un élément de
// « chrome » stable (bouton, filtre, item de menu), jamais une ligne de données
// qui pourrait manquer sur un compte vide.
export const tours = {
    menus: [
        { element: '[data-tour="nav-dashboard"]',  title: 'Votre tableau de bord', intro: "Le point de départ : un résumé de l'activité de la boutique.", side: 'right' },
        { element: '[data-tour="nav-orders"]',      title: 'Les commandes',        intro: 'Retrouvez ici toutes les commandes et leur statut.', side: 'right' },
        { element: '[data-tour="nav-products"]',    title: 'Vos produits',         intro: 'Ajoutez et modifiez vos articles, leurs prix et leur stock.', side: 'right' },
        { element: '[data-tour="nav-promotions"]',  title: 'Les offres par lot',   intro: 'Créez des offres « X articles pour Y F CFA ». On y revient en détail sur cet écran.', side: 'right' },
    ],
    'promotions.index': [
        { element: '[data-tour="promo-new"]',    title: 'Créer une offre',  intro: 'Cliquez ici pour créer une nouvelle offre par lot.', side: 'bottom' },
        { element: '[data-tour="promo-filters"]', title: 'Filtrer',         intro: "Recherchez une offre par nom ou filtrez par statut (active, programmée, expirée).", side: 'bottom' },
    ],
    'products.index': [
        { element: '[data-tour="product-new"]',     title: 'Ajouter un produit', intro: 'Créez une nouvelle fiche produit : nom, prix, stock, catégorie.', side: 'bottom' },
        { element: '[data-tour="product-filters"]', title: 'Rechercher',         intro: 'Retrouvez un produit par son nom ou filtrez la liste.', side: 'bottom' },
    ],
    'orders.index': [
        { element: '[data-tour="order-filters"]', title: 'Suivre les commandes', intro: 'Filtrez par statut pour voir ce qui est à traiter.', side: 'bottom' },
    ],
};
```

- [ ] **Step 2: Écrire le validateur (le test)**

`resources/js/onboarding/validate-tours.mjs` :

```js
import { tours } from './tours.mjs';

// Liste blanche attendue (doit refléter OnboardingController::TOURS).
const EXPECTED_KEYS = ['menus', 'promotions.index', 'products.index', 'orders.index'];

const errors = [];
const keys = Object.keys(tours);

for (const key of EXPECTED_KEYS) {
    if (!keys.includes(key)) errors.push(`clé manquante : ${key}`);
}
for (const key of keys) {
    if (!EXPECTED_KEYS.includes(key)) errors.push(`clé inattendue : ${key}`);

    const steps = tours[key];
    if (!Array.isArray(steps) || steps.length === 0) {
        errors.push(`${key} : aucune étape`);
        continue;
    }
    steps.forEach((s, i) => {
        if (!s.element || typeof s.element !== 'string') errors.push(`${key}[${i}] : "element" manquant`);
        if (!s.title || typeof s.title !== 'string') errors.push(`${key}[${i}] : "title" manquant`);
        if (!s.intro || typeof s.intro !== 'string') errors.push(`${key}[${i}] : "intro" manquant`);
    });
}

if (errors.length) {
    console.error('tours.mjs invalide :\n' + errors.map((e) => '  - ' + e).join('\n'));
    process.exit(1);
}
console.log(`OK — ${keys.length} visites valides`);
```

- [ ] **Step 3: Lancer le validateur**

Run: `node resources/js/onboarding/validate-tours.mjs`
Expected: `OK — 4 visites valides`

- [ ] **Step 4: Commit**

```bash
git add resources/js/onboarding/tours.mjs resources/js/onboarding/validate-tours.mjs
git commit -m "feat(onboarding): definitions des 4 visites et validateur

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Task 3: Moteur d'onboarding et câblage

**Files:**
- Modify: `package.json` (dépendance `driver.js`)
- Create: `resources/js/onboarding/api.js`
- Create: `resources/js/onboarding/index.js`
- Create: `resources/js/onboarding/driver-theme.css`
- Modify: `vite.config.js` (ajouter l'entrée)
- Create: `resources/views/partials/onboarding.blade.php`
- Modify: `resources/views/layouts/admin.blade.php` (include + bouton Rejouer)

**Interfaces:**
- Consumes : `tours` (Task 2), `window.__chamseOnboarding = { seen: string[], endpoint: string, csrf: string }`, route `admin.onboarding.seen` (Task 1).
- Produces : au chargement d'une page admin, la visite non vue de la page démarre ; un bouton `[data-tour-replay]` relance la visite courante.

- [ ] **Step 1: Installer driver.js**

Run: `npm install driver.js@^1.3.1`
Expected: `package.json` et `package-lock.json` mis à jour.

- [ ] **Step 2: Écrire le client API**

`resources/js/onboarding/api.js` :

```js
// Marque une visite comme vue. Échec avalé : au pire la visite réapparaîtra.
export function markSeen(tour) {
    const cfg = window.__chamseOnboarding;
    if (!cfg || !cfg.endpoint) return;

    fetch(cfg.endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': cfg.csrf || '',
        },
        body: JSON.stringify({ tour }),
    }).catch(() => {});
}
```

- [ ] **Step 3: Écrire le chef d'orchestre**

`resources/js/onboarding/index.js` :

```js
import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';
import './driver-theme.css';
import { tours } from './tours.mjs';
import { markSeen } from './api.js';

const cfg = window.__chamseOnboarding || { seen: [] };
const seen = Array.isArray(cfg.seen) ? [...cfg.seen] : [];
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let active = null; // instance driver.js en cours

function currentKey() {
    const el = document.querySelector('[data-tour-page]');
    return el ? el.getAttribute('data-tour-page') : null;
}

// Review Focus nº4 — ne garder que les étapes dont la cible existe.
function stepsFor(key) {
    const steps = tours[key] || [];
    return steps.filter((s) => document.querySelector(s.element));
}

function run(key) {
    const steps = stepsFor(key);
    if (steps.length === 0) return;

    active = driver({
        animate: !reduce,
        showProgress: true,
        nextBtnText: 'Suivant',
        prevBtnText: 'Précédent',
        doneBtnText: 'Terminer',
        progressText: '{{current}} / {{total}}',
        popoverClass: 'chamse-tour',
        steps: steps.map((s) => ({
            element: s.element,
            popover: { title: s.title, description: s.intro, side: s.side || 'bottom' },
        })),
        onDestroyed: () => {
            active = null;
            if (!seen.includes(key)) {
                seen.push(key);
                markSeen(key);
            }
        },
    });
    active.drive();
}

function evaluate() {
    const key = currentKey();
    const btn = document.querySelector('[data-tour-replay]');
    if (btn) btn.hidden = !key || !tours[key];

    if (!key || !tours[key]) return;
    if (!seen.includes(key)) run(key);
}

// Bouton « ? » : relance la visite de la page courante.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-tour-replay]');
    if (!btn) return;
    const key = currentKey();
    if (key && tours[key]) run(key);
});

// Review Focus nº5 — fermer une visite en cours avant une navigation Inertia.
document.addEventListener('inertia:before', () => {
    if (active) active.destroy();
});

// Démarrage : chargement complet (Blade/Livewire) et navigation Inertia (SPA).
document.addEventListener('inertia:finish', () => requestAnimationFrame(evaluate));
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', evaluate);
} else {
    evaluate();
}
```

- [ ] **Step 4: Écrire l'habillage**

`resources/js/onboarding/driver-theme.css` :

```css
.driver-popover.chamse-tour {
    font-family: "Inter", system-ui, sans-serif;
    border-radius: 12px;
    padding: 16px;
    max-width: 320px;
}
.driver-popover.chamse-tour .driver-popover-title {
    font-size: 15px;
    font-weight: 700;
}
.driver-popover.chamse-tour .driver-popover-description {
    font-size: 13.5px;
    line-height: 1.5;
}
.driver-popover.chamse-tour .driver-popover-progress-text {
    font-size: 12px;
    color: #6b7280;
}
.driver-popover.chamse-tour button {
    font-family: "Inter", system-ui, sans-serif;
    font-size: 13px;
    border-radius: 8px;
}
.driver-popover.chamse-tour .driver-popover-next-btn {
    background-color: #2563eb;
    color: #fff;
    text-shadow: none;
    border: none;
}
.driver-popover.chamse-tour .driver-popover-next-btn:hover {
    background-color: #1d4ed8;
}
```

- [ ] **Step 5: Ajouter l'entrée Vite**

Dans `vite.config.js`, ajouter `'resources/js/onboarding/index.js'` au tableau `input` du plugin `laravel` :

```js
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-alpine.js', 'resources/js/admin-notifications.js', 'resources/js/admin-charts.js', 'resources/js/onboarding/index.js'],
```

- [ ] **Step 6: Écrire le partial d'injection**

`resources/views/partials/onboarding.blade.php` :

```blade
@auth
    <script>
        window.__chamseOnboarding = {
            seen: @json(auth()->user()->tours_seen ?? []),
            endpoint: "{{ route('admin.onboarding.seen') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>
    @vite(['resources/js/onboarding/index.js'])
@endauth
```

- [ ] **Step 7: Inclure le partial et le bouton Rejouer dans l'habillage**

Dans `resources/views/layouts/admin.blade.php` :

1. Juste avant `</body>` (ou à la suite des autres `@vite` admin), ajouter :

```blade
    @include('partials.onboarding')
```

2. Dans la barre du haut, à côté de la cloche de notifications, ajouter le bouton (masqué par défaut, révélé par le moteur quand la page a une visite) :

```blade
                        <button type="button" data-tour-replay hidden
                                class="h-11 w-11 sm:h-9 sm:w-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition"
                                title="Revoir le tutoriel de cette page">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
```

- [ ] **Step 8: Vérifier que le build passe**

Run: `npm run build`
Expected: build réussi ; l'entrée `onboarding` apparaît dans le manifeste.

> Pas de test automatisé du rendu visuel (pas d'infra JS DOM dans le dépôt). Le comportement se valide en QA manuel à la Task 5.

- [ ] **Step 9: Commit (sans les assets générés — build final en Task 5)**

```bash
git add package.json package-lock.json vite.config.js resources/js/onboarding/api.js resources/js/onboarding/index.js resources/js/onboarding/driver-theme.css resources/views/partials/onboarding.blade.php resources/views/layouts/admin.blade.php
git commit -m "feat(onboarding): moteur driver.js, bouton rejouer et injection

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Task 4: Poser les repères `data-tour` sur les quatre surfaces

**Files:**
- Modify: `resources/views/layouts/admin-nav-item.blade.php`
- Modify: `resources/views/layouts/admin.blade.php` (passer `tour` aux items de menu visés)
- Modify: `resources/js/Pages/Admin/Promotions/Index.vue`
- Modify: `resources/js/Pages/Admin/Products/Index.vue`
- Modify: `resources/js/Pages/Admin/Orders/Index.vue`
- Modify: la vue du Dashboard admin (racine)

**Interfaces:**
- Consumes : les sélecteurs attendus par `tours.mjs` (Task 2).
- Produces : les attributs `data-tour-page` et `data-tour` dans le DOM rendu.

- [ ] **Step 1: Repère optionnel sur les items de menu**

Dans `resources/views/layouts/admin-nav-item.blade.php`, sur la balise `<a>` de l'item, ajouter un attribut conditionnel :

```blade
@isset($tour) data-tour="{{ $tour }}" @endisset
```

- [ ] **Step 2: Nommer les items visés par la visite `menus`**

Dans `resources/views/layouts/admin.blade.php`, aux quatre `@include('layouts.admin-nav-item', [...])` concernés, ajouter la clé `'tour' => '…'` :
- Dashboard → `'tour' => 'nav-dashboard'`
- Commandes → `'tour' => 'nav-orders'`
- Produits → `'tour' => 'nav-products'`
- Offres par lot → `'tour' => 'nav-promotions'`

- [ ] **Step 3: Marquer le Dashboard**

Sur l'élément racine de la vue du Dashboard admin (celle rendue à `admin.dashboard`), ajouter `data-tour-page="menus"`. Repérer d'abord le fichier (Blade, Livewire ou Inertia) :

Run: `php artisan route:list --name=admin.dashboard`
Puis ajouter l'attribut sur le conteneur racine de la vue correspondante.

- [ ] **Step 4: Marquer la page Offres par lot**

Dans `resources/js/Pages/Admin/Promotions/Index.vue` :
- sur le `<div>` racine du template : `data-tour-page="promotions.index"`
- sur le lien « Nouvelle offre » (`route('admin.promotions.create')`) : `data-tour="promo-new"`
- sur le conteneur des champs recherche/filtres : `data-tour="promo-filters"`

- [ ] **Step 5: Marquer la page Produits**

Dans `resources/js/Pages/Admin/Products/Index.vue` :
- racine : `data-tour-page="products.index"`
- bouton d'ajout de produit : `data-tour="product-new"`
- zone recherche/filtres : `data-tour="product-filters"`

> Vérifier les libellés réels des boutons/zones avant de poser l'attribut (lire le fichier).

- [ ] **Step 6: Marquer la page Commandes**

Dans `resources/js/Pages/Admin/Orders/Index.vue` :
- racine : `data-tour-page="orders.index"`
- zone des filtres de statut : `data-tour="order-filters"`

- [ ] **Step 7: Vérifier que le build passe**

Run: `npm run build`
Expected: build réussi.

- [ ] **Step 8: Commit (sources, assets en Task 5)**

```bash
git add resources/views/layouts/admin-nav-item.blade.php resources/views/layouts/admin.blade.php resources/js/Pages/Admin/Promotions/Index.vue resources/js/Pages/Admin/Products/Index.vue resources/js/Pages/Admin/Orders/Index.vue
# + la vue Dashboard marquée au Step 3
git commit -m "feat(onboarding): reperes data-tour sur menus, offres, produits, commandes

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Task 5: Build des assets, QA manuel et commit final

**Files:**
- Modify: `public/build/**` (régénéré)

- [ ] **Step 1: Construire les assets**

Run: `npm run build`

- [ ] **Step 2: Lancer toute la suite PHP**

Run: `composer test`
Expected: les mêmes **11 échecs préexistants** et rien de plus ; `OnboardingSeenTest` vert.

- [ ] **Step 3: QA manuel** (avec un compte admin de test, `tours_seen` vide)

- [ ] Premier passage sur le Dashboard → la visite **menus** démarre, pointe les 4 menus, se termine → ne redémarre plus au rechargement.
- [ ] Premier passage sur Offres par lot / Produits / Commandes → la visite de la page démarre une fois.
- [ ] Bouton **« ? »** présent sur ces pages, absent sur une page sans visite ; au clic, la visite rejoue.
- [ ] *(Review Focus nº4)* Sur une page sans donnée (liste vide), aucune étape ne pointe dans le vide et aucune visite vide ne s'ouvre.
- [ ] *(Review Focus nº5)* Lancer une visite puis cliquer un menu (navigation Inertia) → la visite se ferme proprement, celle de la nouvelle page s'évalue.
- [ ] Mode sombre / `prefers-reduced-motion` : bulles lisibles, animation coupée si réglée.

- [ ] **Step 4: Commit final**

```bash
git add public/build
git commit -m "build(onboarding): assets de production

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Vérification finale

- [ ] `vendor/bin/phpunit tests/Feature/OnboardingSeenTest.php` — vert
- [ ] `node resources/js/onboarding/validate-tours.mjs` — `OK — 4 visites valides`
- [ ] `composer test` — 11 échecs préexistants, aucun de plus
- [ ] `npm run build` — assets générés et commités
- [ ] QA manuel (Task 5) — les 4 visites démarrent au 1ᵉʳ passage, le bouton « ? » rejoue, la mémoire tient d'un rechargement à l'autre
