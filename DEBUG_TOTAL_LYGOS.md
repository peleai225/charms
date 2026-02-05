# 🔍 Debug : Problème de Total Lygos Pay

## Problème Signalé

**Article** : Dolan Pollard
- Prix unitaire : 560 F CFA
- Quantité : 3
- **Sous-total attendu** : 1 680 F CFA

**Montant affiché par Lygos Pay** : 6 120 F CFA ❌

**Différence** : 6 120 - 1 680 = **4 440 F CFA**

---

## Analyse

### Calcul du Total dans CheckoutController

```php
$subtotal = $cart->subtotal;              // 1 680 F CFA
$discount = $cart->discount_amount;       // 0 F CFA (si pas de coupon)
$shippingCost = $this->calculateShipping($cart, $validated);  // ???
$taxAmount = $this->calculateTax($subtotal - $discount);     // 0 F CFA (actuellement)
$total = $subtotal - $discount + $shippingCost + $taxAmount;
```

### Frais de Livraison

Selon le pays sélectionné :
- **CI** (Côte d'Ivoire) : 2 000 F CFA
- **SN** (Sénégal) : 3 000 F CFA
- **ML** (Mali) : 3 500 F CFA
- **BF** (Burkina Faso) : 3 500 F CFA
- **TG** (Togo) : 3 000 F CFA
- **BJ** (Bénin) : 3 000 F CFA
- **FR** (France) : 15 000 F CFA
- **Autre** : 5 000 F CFA (par défaut)

### Calcul Probable

Si le total est **6 120 F CFA** :
- Sous-total : 1 680 F CFA
- Frais de livraison : 6 120 - 1 680 = **4 440 F CFA**

**4 440 F CFA ne correspond à aucun tarif de livraison défini !**

---

## Causes Possibles

### 1. ❌ Problème d'Affichage dans la Vue

Dans `resources/views/front/checkout/index.blade.php` ligne 356 :
```blade
<span>{{ number_format($cart->total, 0, ',', ' ') }} F CFA</span>
```

**Problème** : `$cart->total` ne comprend **PAS** les frais de livraison !

`$cart->total` = `subtotal - discount` seulement.

**Solution** : Afficher un total estimé qui inclut les frais de livraison.

---

### 2. ❌ Frais de Livraison Multipliés

Peut-être que les frais de livraison sont calculés plusieurs fois ou multipliés par erreur.

---

### 3. ❌ Plusieurs Articles dans le Panier

Peut-être qu'il y a d'autres articles dans le panier que l'utilisateur ne voit pas.

---

## Solutions

### ✅ Solution 1 : Afficher le Détail du Total

Modifier la vue checkout pour afficher :
- Sous-total
- Réduction (si coupon)
- Frais de livraison (estimés)
- **Total final**

### ✅ Solution 2 : Ajouter des Logs Détaillés

Les logs ont été ajoutés pour voir exactement ce qui est calculé :
- `Checkout: Calcul du total` - Montre le détail du calcul
- `Lygos Pay: Initializing payment` - Montre le montant envoyé à Lygos

### ✅ Solution 3 : Vérifier les Logs

Consultez `storage/logs/laravel.log` et cherchez :
```
Checkout: Calcul du total
Lygos Pay: Initializing payment
```

---

## Prochaines Étapes

1. **Vérifier les logs** pour voir le détail du calcul
2. **Vérifier le pays sélectionné** lors du checkout
3. **Vérifier s'il y a d'autres articles** dans le panier
4. **Corriger l'affichage** dans la vue checkout

---

## Logs à Vérifier

Après avoir fait un nouveau test, cherchez dans `storage/logs/laravel.log` :

```
[INFO] Checkout: Calcul du total {
    "subtotal": 1680,
    "discount": 0,
    "shipping": ???,
    "tax": 0,
    "total": 6120,
    "cart_items_count": ???
}

[INFO] Lygos Pay: Initializing payment {
    "subtotal": 1680,
    "discount": 0,
    "shipping": ???,
    "tax": 0,
    "total": 6120,
    "amount_sent_to_lygos": 6120
}
```

Ces logs vous diront exactement d'où vient le montant de 6 120 F CFA.

