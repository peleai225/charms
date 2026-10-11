# Service SKU robuste — Design

**Date** : 2026-10-11
**Statut** : En attente de revue
**Sous-projet** : A sur 6 (voir « Contexte et découpage » ci-dessous)

## Contexte et découpage

La demande initiale (gestion intelligente du catalogue : SKU, détection de doublons, suggestions à la création, audit qualité, import, correction historique) couvre en réalité 6 sous-systèmes indépendants :

| # | Sous-projet | Dépend de |
|---|---|---|
| **A** | **Service SKU** (ce document) — génération/validation robuste pour produits et variantes | — |
| B | Détection de produits similaires | A (décisions de modèle de données) |
| C | UX création/édition — panneau de suggestions | B |
| D | Page « Qualité du catalogue » | A + B |
| E | Intégration import CSV (prévisualisation + contrôles) | A + B |
| F | Correction des références historiques | A |

Chaque sous-projet aura son propre cycle design → spec → plan → implémentation. Ce document ne couvre que le sous-projet A.

## Diagnostic (audit du code existant)

- Stack : PHP 8.3, Laravel 12.51, Livewire 4.3 (CLAUDE.md mentionne « Livewire 3 », info obsolète), MySQL. Import CSV fait main (`fgetcsv`), pas de package Maatwebsite/Excel.
- Le SKU existe à la fois sur `products.sku` et `product_variants.sku` — deux espaces d'unicité indépendants, chacun `VARCHAR`, `UNIQUE`, `NOT NULL` depuis la migration d'origine (jamais modifiée depuis).
- `Product::boot()` génère un SKU de repli (`strtoupper(Str::random(10))`) uniquement si le champ est vide à la création — mais ce chemin n'est quasiment jamais utilisé par le formulaire admin (`ProductController::store` exige toujours un SKU explicite). Il ne s'active que via l'import CSV quand la colonne SKU est vide, et **le SKU généré n'est pas vérifié contre l'existant avant assignation** (contrairement au slug, juste au-dessus dans le même hook) — risque de collision réel, actuellement masqué par un catalogue de dev à 7 lignes.
- Les variantes n'ont **aucune génération automatique** : `storeVariant` et `bulkStoreVariants` exigent un SKU explicite. `bulkStoreVariants` a un contrôle de doublon ad hoc (`where('sku', ...)->exists()` manuel, ligne ignorée en cas de collision) — pas de garantie en cas de concurrence.
- Les contraintes `UNIQUE` sur `sku` existent déjà en base sur les deux tables. L'exigence « ajouter une contrainte UNIQUE après résolution des conflits » (Phase 2 de la demande initiale) est donc déjà satisfaite — aucune migration n'est nécessaire sur ce point.
- Aucune relation en base n'utilise `sku` comme clé étrangère. `order_items.sku` est un instantané figé au moment de la commande (pas de FK, jamais resynchronisé) ; `stock_movements` est indexé uniquement par `product_id`/`product_variant_id`. Changer un SKU existant n'a donc aucun impact sur l'intégrité référentielle — seulement un impact opérationnel (étiquettes imprimées, scanners, bons de commande fournisseur).
- `Category` n'a aucun champ court type « code » aujourd'hui (seulement `name`/`slug`).
- Les valeurs de SKU actuelles en base sont des données de seed/faker ("Glow", "SP", "Suscipit quia harum") — aucune convention réelle à préserver.

## Décisions validées avec l'utilisateur

- **Convention produit** : `LGB-{PREFIXE_CATEGORIE}-{000000}` (ex. `LGB-ELEC-000123`).
- **Préfixe catégorie** : nouveau champ court `categories.code`, renseigné par l'admin ; à défaut, dérivé automatiquement du slug.
- **Portée de la séquence** : par catégorie (chaque catégorie a son propre compteur qui démarre à 1), pas une séquence globale.
- **Convention variante** : SKU du produit parent + suffixe séquentiel à 2 chiffres (`LGB-ELEC-000123-01`, `-02`, ...), pas de code d'attribut dans le SKU.
- **Saisie manuelle** : libre — unique et non vide suffisent, pas d'obligation de respecter le format `LGB-XXXX-000000` (utile pour les références héritées ou imposées par un fournisseur).
- **Champ `brand`** : son ajout est validé en principe, mais reporté au sous-projet B (détection de similarité), qui est le seul à le consommer. Non traité ici.

## Architecture

### Changements de schéma

- `categories` : ajout de
  - `code` (`string`, nullable, longueur courte ex. 10) — préfixe choisi par l'admin. Validation stricte à la saisie : `^[A-Z0-9]{2,10}$` (normalisé en majuscules avant enregistrement, pas d'espace/accent/caractère spécial). Pas de contrainte `UNIQUE` en base — une collision de `code` entre deux catégories reste possible (cf. limite documentée plus bas) mais le formulaire admin affiche un avertissement non bloquant si un autre `code` identique existe déjà.
  - `sku_sequence_next` (entier non signé, défaut 1) — compteur du prochain numéro de séquence pour cette catégorie.
- `products` : ajout de
  - `variant_sku_sequence_next` (entier non signé, défaut 1) — compteur du prochain suffixe de variante pour ce produit.
- Aucune modification des contraintes `UNIQUE` existantes sur `products.sku` / `product_variants.sku` (déjà en place, déjà valides avec les données actuelles).
- Migrations additives uniquement (colonnes nullable ou avec valeur par défaut) — pas de risque de perte de données, pas de verrouillage de table nécessaire sur MySQL pour ce type d'ajout.

### Service : `App\Services\Sku\SkuGeneratorService`

Responsabilités :

- `generateForProduct(Category $category): string`
  Verrouille la ligne de la catégorie (`lockForUpdate`) dans une transaction courte et dédiée, lit ou dérive le préfixe, incrémente `sku_sequence_next`, formate `LGB-{PREFIXE}-{sequence sur 6 chiffres}`. Cette transaction se limite à la réservation du numéro — elle ne couvre pas l'insertion du produit, qui reste gérée par l'appelant (ex. `ProductController::store`, déjà dans sa propre transaction).

- `generateForVariant(Product $product): string`
  Même mécanique sur la ligne du produit parent (`variant_sku_sequence_next`), formate `{sku_parent}-{suffixe sur 2 chiffres}`.

- `resolveCategoryPrefix(Category $category): string`
  Retourne `code` si renseigné ; sinon dérive un préfixe depuis le `slug` (ex. 4 premières lettres significatives, majuscules, repli sur `GEN` si le slug est trop court/vide).
  **Limite connue, documentée et acceptée** : deux catégories sans `code` explicite peuvent dériver vers le même préfixe apparent (ex. « Électronique » et « Électroménager » → `ELEC`). Les SKU resteraient uniques (la séquence est par catégorie, pas par préfixe), mais visuellement les deux familles partageraient le même préfixe. On ne bloque pas la création pour ça — on recommande simplement aux admins de renseigner un `code` propre par catégorie.

- `validateManualSku(string $sku, Model $owner): void`
  Trim, longueur raisonnable, non vide, unicité sur la bonne table (`products` ou `product_variants`) en excluant l'enregistrement courant en cas de modification. Lève `SkuValidationException` avec message explicite si invalide.

- Boucle de nouvelles tentatives, max 5, en cas de violation de contrainte unique détectée spécifiquement sur la colonne `sku` (vérification du nom de contrainte / code d'erreur MySQL 1062, pas juste « une `QueryException` a été levée »). Toute autre exception base de données remonte sans interception. Après épuisement des tentatives : `SkuGenerationException` avec message explicite en français (« Impossible de générer un SKU unique après plusieurs tentatives. Veuillez réessayer ou saisir un SKU manuellement. »).

**Compromis accepté** : comme la réservation du numéro de séquence se fait dans une transaction séparée de l'insertion finale, un échec de création après réservation (ex. validation métier qui échoue plus loin) laisse un « trou » dans la séquence (le numéro réservé n'est jamais réutilisé). C'est un compromis standard (similaire à la numérotation de factures) — pas un bug, juste un choix explicite à documenter.

### Points d'intégration

- **`Product::boot()`** : le hook `creating` actuel (génération aléatoire non vérifiée) est remplacé par un appel à `SkuGeneratorService::generateForProduct($product->category)` quand `sku` est vide. Garde ce filet de sécurité actif sur tous les chemins de création (formulaire admin, import, tinker, future API), pas seulement un contrôleur.
- **`ProductController::store` / `update`** : règle de validation `sku` passe de `required|unique:products` à `nullable|string`, avec appel à `validateManualSku()` quand une valeur est fournie.
- **`ProductController::storeVariant` / `bulkStoreVariants`** : le SKU devient optionnel (génération automatique si vide), avec validation manuelle sinon. Petit ajustement front-end nécessaire : les formulaires Vue de création de variante ne doivent plus marquer le champ SKU comme obligatoire, et afficher un placeholder du type « Laisser vide pour génération automatique ».
  Précision sur `bulkStoreVariants` : seul le cas « SKU vide » change de comportement (auto-génération au lieu d'échouer). Le cas « SKU explicite qui entre en conflit avec un SKU existant » garde exactement le comportement actuel — la ligne est ignorée et comptée dans `$skipped`, sans faire échouer le reste du lot. On ne transforme pas ce contrôle ad hoc en erreur bloquante dans ce sous-projet, pour ne pas changer l'UX de l'outil de grille au-delà de ce qui est demandé ici.
- **`ImportExportController::processProductRow`** : les lignes CSV sans SKU appellent le service au lieu du fallback actuel du modèle (qui sera supprimé/remplacé).
- **`Admin\CategoryController`** (création/édition de catégorie) : ajout du champ `code` optionnel dans la validation et le formulaire, pour permettre à l'admin de le renseigner.

### Gestion des erreurs

- `SkuGenerationException` : échec de génération automatique après épuisement des tentatives. Remonté comme une erreur 422/message explicite côté contrôleur, jamais une 500 silencieuse.
- `SkuValidationException` : échec de validation d'un SKU saisi manuellement (doublon, vide, trop court). Converti en erreur de validation Laravel standard (422) par les contrôleurs.
- Toute autre exception base de données (contrainte étrangère, colonne manquante, etc.) n'est jamais interprétée comme un conflit de SKU — elle remonte telle quelle.

## Tests prévus (couvre les points 1 à 5 de la demande initiale)

- **Unitaires** (`SkuGeneratorService`) :
  - Format correct généré pour un produit et pour une variante.
  - Compteurs indépendants par catégorie et par produit (deux catégories n'interfèrent pas, deux produits n'interfèrent pas).
  - Dérivation du préfixe de repli quand `categories.code` est vide.
  - Nouvelle tentative puis exception explicite après 5 collisions simulées.
  - Toute exception non liée à une collision SKU n'est pas interceptée par la boucle de retry.

- **Fonctionnels** :
  - Création d'un produit sans SKU → SKU auto-généré au bon format, incrémente le compteur de la catégorie.
  - Création avec SKU manuel valide → conservé tel quel.
  - SKU manuel dupliqué → rejeté en 422, pas en 500.
  - Modification du nom/prix/description d'un produit existant → le SKU reste inchangé.
  - `bulkStoreVariants` avec un mélange de lignes SKU vide / SKU explicite → les vides reçoivent un suffixe généré, les explicites sont conservés et validés individuellement.

- **Limite assumée** : la concurrence réelle multi-process (deux requêtes HTTP simultanées) n'est pas testable directement dans PHPUnit (process unique). On teste la logique de retry-sur-collision comme indicateur de robustesse, et on documente que c'est le `lockForUpdate()` sur la ligne catégorie/produit qui garantit réellement la sécurité en concurrence — pas une assertion de test automatisée.

## Notes d'implémentation complémentaires

- **Factories/seeders** : `ProductFactory` et `ProductVariantFactory` assignent aujourd'hui un SKU Faker explicite (d'où les valeurs "Glow", "SP", etc. observées dans l'audit). Elles seront revues pour laisser `sku` vide par défaut, afin que les tests exercent réellement le service de génération plutôt que de le contourner systématiquement. Un état explicite (`->withSku('XYZ')`) restera possible pour les tests qui ont besoin d'une valeur fixe.
- **Autorisation** : aucune règle d'autorisation nouvelle n'est nécessaire — la génération de SKU est interne aux contrôleurs admin existants (`ProductController`, `ImportExportController`, `CategoryController`), qui sont déjà protégés par les middlewares/policies admin en place. Le test du point 14 (« autorisations administratives ») vérifie donc que ce comportement existant n'est pas régressé par les changements de validation, pas qu'une nouvelle règle est ajoutée.

## Hors périmètre (sous-projets suivants)

- Champ `brand`, détection de similarité, scoring (sous-projet B).
- Panneau de suggestions à la création/édition (sous-projet C).
- Page « Qualité du catalogue » (sous-projet D).
- Prévisualisation et contrôles de doublons dans l'import CSV (sous-projet E).
- Correction des SKU historiques existants (sous-projet F) — non nécessaire immédiatement puisque les données actuelles sont des données de seed sans valeur commerciale réelle.
