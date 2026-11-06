# Module Livreurs — suivi des livraisons et des points

## Pourquoi

Le Grand Bazar (legrandbazar.ci) vend presque tout en **paiement à la livraison**. Les colis partent avec des **livreurs extérieurs** — des indépendants, et aussi des sociétés de livraison (Yango…) — qui encaissent l'argent du client puis font un **« point »** avec la gérante : quelles commandes sont livrées, lesquelles ont raté, combien d'argent ils remettent.

Ce point se fait aujourd'hui hors du site. Constat dans son admin le 04/11/2026 :
- 67 commandes en 8 jours, mais **382 commandes bloquées** en « en attente / en cours », jamais passées en livrée ;
- les rapports affichaient « Aucune vente » (corrigé dans la PR #7 : une commande livrée compte comme vente) ;
- elle « ne voit pas ses commandes » : elle ne sait pas lesquelles sont chez quel livreur, ni qui lui doit combien.

Le site connaît les statuts (« Expédiée », « Livreur en route », « Livrée ») mais **pas les livreurs**.

**Réussi =** chaque soir, la gérante sait pour chaque livreur ce qu'il a emporté, ce qu'il a livré, ce qu'il lui doit (ou ce qu'elle lui doit) ; les commandes livrées passent en « Livrée / payée » au moment du point ; ses rapports montrent le vrai chiffre.

## Ce qu'elle doit pouvoir faire

1. **Gérer ses livreurs** : nom, téléphone, type (**indépendant** ou **société**), note libre, actif / inactif.
2. **Régler les frais livreur par quartier** : dans les zones de livraison existantes (Paramètres → Livraison : nom, villes/quartiers, prix client), un champ de plus, **« Frais livreur »**.
3. **Confier des commandes à un livreur** : depuis la liste des commandes, cocher les commandes du jour → « Confier à un livreur » → choisir le livreur. Les commandes passent en **« Livreur en route »**, avec le livreur, l'heure de départ et les **frais livreur** de leur zone.
4. **Envoyer la liste au livreur** : un récapitulatif à copier ou ouvrir dans WhatsApp (n° de commande, nom et téléphone du client, quartier/adresse, **montant à encaisser**).
5. **Faire le point** : un écran par livreur listant ses commandes « en route ». Pour chacune : **Livrée** ou **Pas livrée** (motif : client absent, injoignable, refus, reportée, autre). L'écran calcule :
   - **argent encaissé** = total des commandes livrées non encore payées ;
   - **frais du livreur** = frais de **chaque commande présentée**, livrée ou non (une livraison ratée est payée comme une livraison) ;
   - **à remettre** = encaissé − frais (négatif : c'est la boutique qui doit au livreur).
   Elle saisit le montant réellement reçu (0 si le livreur reverse plus tard), puis valide :
   - Livrées → statut « Livrée », paiement « payé », date de livraison.
   - Pas livrées → retour « à relivrer » (statut « En préparation ») avec le motif ; le compteur de tentatives augmente ; elles peuvent repartir avec n'importe quel livreur.
   - Le point est **enregistré** (historique consultable).
6. **Suivre le solde de chaque livreur** : chaque point ajoute « à remettre » au solde et retire le montant reçu. Un **versement** reçu plus tard (cas des sociétés : virement, fin de semaine) s'enregistre à part et diminue le solde. Solde positif = le livreur doit à la boutique ; négatif = la boutique lui doit.
7. **Voir l'argent chez les livreurs** : par livreur, colis en route, montant à encaisser en cours, solde.
8. **Tableau de bord « À traiter »** : commandes à confirmer, à confier à un livreur, en route (et montant attendu), à relivrer ; total des soldes livreurs.

## Hors périmètre (v1)

- Pas de compte ni d'application pour les livreurs : c'est la gérante qui saisit le point.
- Pas de suivi GPS, pas d'intégration (API) avec des sociétés de livraison.
- Pas de paie des livreurs au-delà des frais et du solde.
- Pas de message automatique aux clients lors de la remise au livreur ou du point.

## À ne surtout pas faire

- Marquer une commande « payée » sans que la gérante ait validé un point (ou la mise à jour manuelle existante).
- Laisser une commande confiée disparaître de la vue « en route » sans point (ou retrait explicite) : un colis chez un livreur doit toujours être visible.
- Prévenir les clients en masse.
- Modifier un point validé en silence : un point validé est figé ; une erreur se corrige par un nouveau mouvement (versement, ajustement) visible dans l'historique.

## Contraintes

- Laravel 12 + Inertia/Vue 3, design system de `CLAUDE.md` (cibles tactiles 44 px : elle fera probablement le point sur son téléphone), montants en F CFA.
- Une instance par client : tout passe par des migrations, lancées au déploiement.
- **Le statut `delivery_in_progress` (« Livreur en route ») n'existe pas dans la colonne `orders.status`** (enum créé avec 7 valeurs) alors que le code le propose déjà : la migration doit l'ajouter. Tant que ce n'est pas fait, choisir ce statut dans le panneau d'une commande provoque une erreur.
- Les zones de livraison sont stockées en JSON dans le réglage `shipping_zones` (`name`, `cities`, `price`) et associées à une commande par sa ville de livraison (même logique que `CheckoutController::calculateShipping`).
- Les mises à jour faites par un point suivent les règles des actions groupées de la PR #7 : pas d'e-mail client, stock non touché (une commande non livrée n'est pas annulée).
- Les rapports comptent déjà une commande livrée comme vente (PR #7).

## Ce qui est décidé

- Les frais livreur dépendent de la **zone** (quartier), pas du livreur. Ils sont copiés sur la commande au moment où elle est confiée, et **modifiables pendant le point** (exception, société qui facture autrement).
- Une livraison ratée coûte les mêmes frais qu'une livraison réussie.
- Indépendants et sociétés utilisent **le même point** ; la seule différence est le moment où l'argent est reçu, géré par le solde et les versements.
- Une commande n'est chez **qu'un seul** livreur à la fois ; on peut la confier depuis « Confirmée » ou « En préparation » uniquement.
- Une commande non cochée pendant le point reste en route (traitée au point suivant). Une commande **retirée** au livreur avant son départ revient « En préparation » sans frais ni tentative.
- Argent à encaisser d'une commande = son total si elle n'est pas encore payée, 0 si elle a été payée en ligne.

## Choix faits par Claude (à vérifier à la relecture)

- Si la ville de livraison ne correspond à aucune zone, les frais livreur sont à 0 et la commande est signalée « zone inconnue » pour être complétée au point.
- Livraison partielle ou prix négocié à la porte : pas géré commande par commande en v1. Tout écart d'argent se note au point (montant reçu + note).
- Échecs répétés : pas d'annulation automatique. Le nombre de tentatives est affiché ; la gérante annule elle-même (action groupée de la PR #7).

## Laissé au constructeur

- Le découpage exact des écrans et composants, tant que la liste des commandes reste lisible (le fichier dépasse déjà 500 lignes).
- La forme des données (tables des livreurs, des points, des versements ou d'un journal unique), tant que le solde de chaque livreur se recalcule à partir de mouvements datés et non modifiables.
- Le format exact du texte envoyé au livreur et la présentation de l'historique.
