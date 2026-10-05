# Visites guidées du back-office — Design

> Statut : en attente de validation. À relire avant la rédaction du plan d'implémentation.

## Objectif

Offrir aux gérants des boutiques Chamse une **visite guidée interactive** du
back-office, façon onboarding d'application grand public (type CapCut au
premier lancement) : une bulle pointe le vrai bouton à l'écran, le reste est
assombri (effet « spotlight »), avec boutons Suivant / Précédent / Passer. La
visite démarre **automatiquement au premier passage** sur une page, puis plus
jamais, et reste rejouable à la demande.

**Public :** responsables de boutique peu à l'aise avec l'interface, sur les
deux instances de production (legrandbazar.ci, charms-ci.com). Langue : français.

**Succès :** un gérant qui ouvre le back-office pour la première fois est guidé
sans aide extérieure pour (a) situer les grands menus, (b) créer une offre par
lot, (c) créer un produit, (d) traiter une commande.

## Contexte technique (constaté dans le dépôt)

- Le back-office a **trois formats de page** cohabitant pendant la migration :
  Blade (`layouts.admin`), Livewire (`layouts.admin-livewire`) et Inertia/Vue
  (`layouts.admin-inertia`).
- **`admin-inertia.blade.php` et `admin-livewire.blade.php` étendent
  `layouts.admin`** : l'habillage (sidebar, barre du haut) est **commun** aux
  trois. Un script injecté dans `layouts.admin` est donc présent partout.
- Les écrans Produits (`Admin/Products/Index`), Commandes (`Admin/Orders/Index`)
  et Offres par lot (`Admin/Promotions/Index`) sont des pages **Inertia/Vue**.
- Aucune librairie de visite guidée n'est installée.
- La table `users` existe (colonnes `role` ajoutées par migrations). La sidebar
  « Catalogue » n'est rendue que pour les rôles `admin` et `manager`
  (`layouts/admin.blade.php`).

## Décision d'architecture

**Approche retenue : librairie DOM-agnostique (driver.js) + un chef d'orchestre
partagé.** Le facteur décisif est la coexistence des trois formats de page :
seule une solution qui cible le DOM par sélecteur CSS les couvre toutes avec un
seul moteur. driver.js pèse ~6 ko, est maintenu, accessible (clavier, focus),
et son habillage se surcharge facilement aux couleurs Chamse.

Approches écartées :
- **Composant Vue maison** : ne couvrirait que les pages Inertia, et
  réimplémenterait (mal) le calcul de spotlight, le positionnement et le scroll.
- **Shepherd.js** : équivalent fonctionnel mais plus lourd et plus de CSS à
  surcharger. Overkill ici.

## Périmètre v1

Le moteur **plus** quatre visites :

1. **`menus`** — tour des grands menus de la sidebar, déclenché sur le Dashboard.
2. **`promotions.index`** — créer une offre par lot.
3. **`products.index`** — créer / gérer un produit.
4. **`orders.index`** — traiter une commande.

**Hors périmètre v1** (volontairement, YAGNI) : réinitialiser la visite d'un
autre utilisateur depuis l'admin, statistiques de complétion, édition des
étapes depuis l'interface, versionnage des visites, traduction multilingue.

## Composants

### 1. Base de données

Migration ajoutant à `users` une colonne **`tours_seen`** (`json`, nullable),
castée en `array` sur le modèle `User` (défaut `[]`). Elle liste les clés de
visites déjà vues par l'utilisateur, ex. `["menus", "promotions.index"]`.

### 2. Backend

- **Injection du bootstrap** dans l'habillage commun. Un partial
  `resources/views/partials/onboarding.blade.php`, inclus une fois dans
  `layouts.admin`, émet :
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
- **Route** `POST /admin/onboarding/seen` →
  `Admin\OnboardingController@markSeen`, protégée par le même middleware admin
  que le reste du back-office. Elle valide `tour` contre la **liste blanche** des
  clés connues, ajoute la clé à `tours_seen` de l'utilisateur (idempotent), et
  répond `204`.
- Pas d'endpoint de réinitialisation en v1 : « Rejouer » est purement
  front-end (relance la visite sans toucher `tours_seen`), et un nouvel
  utilisateur a `tours_seen = []` donc revoit tout naturellement.

### 3. Frontend — `resources/js/onboarding/`

- **`tours.js`** — définitions des 4 visites, objet indexé par clé de page.
  Chaque visite est une liste d'étapes `{ element, title, intro, side }`, où
  `element` est un sélecteur reposant sur un attribut `data-tour`.
- **`driver.js` (habillage)** — instance driver.js configurée en français
  (Suivant / Précédent / Terminer), thème Chamse (Inter, `--primary #2563EB`),
  via une petite feuille de style importée.
- **`api.js`** — `markSeen(tourKey)` : `POST` vers `endpoint` avec l'en-tête
  CSRF ; échec avalé silencieusement.
- **`index.js`** — le chef d'orchestre :
  1. lit `window.__chamseOnboarding` ;
  2. détermine la page courante via l'attribut `data-tour-page` présent dans le
     contenu ;
  3. filtre les étapes dont l'élément est absent du DOM ; si aucune ne reste,
     n'ouvre rien ;
  4. si la clé n'est pas dans `seen`, démarre la visite automatiquement ;
  5. à la fin ou au « Passer », appelle `markSeen(clé)` et met à jour la copie
     locale de `seen` ;
  6. câble le bouton « ? » de la barre du haut pour rejouer la visite courante ;
  7. se ré-exécute au chargement initial (`DOMContentLoaded`) **et** à chaque
     navigation Inertia (écoute de l'évènement `inertia:finish` sur `document`,
     sans dépendre du bundle Vue).

### 4. Repères dans les pages (`data-tour`)

- **`data-tour-page="<clé>"`** sur l'élément racine de chaque page concernée
  (Dashboard → `menus` ; les 3 pages Inertia → leur clé).
- **`data-tour="<id>"`** sur les éléments ciblés :
  - sidebar : ajout d'un paramètre optionnel `tour` au partial
    `layouts.admin-nav-item` pour poser le repère sur les items visés par la
    visite `menus` ;
  - pages Inertia : repères sur les boutons/zones clés (ex. « Nouvelle offre »,
    filtres, première ligne de tableau).

### 5. Bouton « Rejouer »

Un bouton « ? » discret dans la barre du haut de `layouts.admin`. Au clic, il
relance la visite de la page courante (si elle existe). Masqué si la page n'a
pas de visite.

## Flux

1. Le gérant se connecte → arrive sur le Dashboard. `menus` non vue → la visite
   des menus démarre.
2. Il va sur Offres par lot pour la première fois → `promotions.index` démarre.
   Idem Produits et Commandes à leur premier affichage.
3. À chaque fin/`Passer` → la visite est marquée vue, ne redémarre plus seule.
4. Il peut la relancer via « ? » à tout moment.

## Gestion des erreurs et cas limites

- **Élément ciblé absent** (droit masquant un bouton, variante de page) :
  l'étape est retirée avant démarrage ; plus aucune étape ⇒ pas de démarrage.
- **Échec réseau du `markSeen`** : silencieux ; au pire la visite réapparaît au
  prochain passage. Jamais bloquant pour l'UI.
- **Navigation Inertia pendant une visite** : la visite en cours est fermée
  proprement avant de réévaluer la nouvelle page.
- **Accessibilité** : navigation clavier (flèches, Échap) fournie par driver.js ;
  respect de `prefers-reduced-motion` (désactive l'animation du spotlight).
- **Rôles** : les visites ne ciblent que des éléments réellement affichés ; un
  rôle sans accès à la section ne déclenche rien pour cette section.

## Tests

- **Feature (PHPUnit)** `OnboardingSeenTest` : l'endpoint exige l'authentification ;
  il ajoute la clé à `tours_seen` ; il est idempotent (pas de doublon) ; il
  refuse une clé inconnue (hors liste blanche) avec `422`.
- **Unit** : cast `tours_seen` en tableau sur `User` (défaut `[]`).
- **Front** : test léger validant `tours.js` — clés de page uniques, chaque
  étape a un `element` et un `intro` non vides. (Pas d'infra de test DOM dans le
  dépôt ; le rendu visuel se valide en QA manuel.)
- **Build** : `public/build/` est commité → `npm run build` + commit des assets
  à la fin.

## Fichiers touchés (indicatif, détaillé dans le plan)

**Créés :**
- `database/migrations/XXXX_add_tours_seen_to_users_table.php`
- `app/Http/Controllers/Admin/OnboardingController.php`
- `resources/views/partials/onboarding.blade.php`
- `resources/js/onboarding/index.js`, `tours.js`, `api.js`, `driver-theme.css`
- `tests/Feature/OnboardingSeenTest.php`

**Modifiés :**
- `resources/views/layouts/admin.blade.php` (include du partial + bouton « ? »)
- `resources/views/layouts/admin-nav-item.blade.php` (param `tour` optionnel)
- `app/Models/User.php` (cast `tours_seen`)
- `routes/web.php` (route `admin.onboarding.seen`)
- `resources/js/Pages/Admin/{Promotions,Products,Orders}/Index.vue` et la vue
  Dashboard (repères `data-tour-page` / `data-tour`)
- `package.json` (dépendance `driver.js`)

## Monnaie / conventions

Sans objet côté calcul (fonctionnalité d'interface). Conventions du dépôt
respectées : snake_case PHP, camelCase JS, kebab-case fichiers Vue, Inter, icônes
Lucide, pas de données fictives.
