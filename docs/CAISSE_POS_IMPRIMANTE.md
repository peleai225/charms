# 🖨️ Configuration de l'imprimante pour la caisse POS

Guide pour configurer l'impression des reçus sur la caisse (mode Scanner / POS).

---

## 📋 Prérequis

- **Imprimante thermique** compatible (58 mm ou 80 mm)
- **Pilote** de l'imprimante installé sur l'ordinateur de caisse
- **Navigateur** Chrome ou Edge (recommandé)

---

## 🔧 Étape 1 : Installer l'imprimante

1. Connectez l'imprimante thermique (USB ou réseau)
2. Installez le pilote fourni par le fabricant
3. Vérifiez que l'imprimante apparaît dans **Paramètres Windows → Périphériques → Imprimantes**

---

## 🔧 Étape 2 : Définir l'imprimante par défaut

1. Ouvrez **Paramètres Windows** (ou Panneau de configuration)
2. Allez dans **Périphériques → Imprimantes et scanners**
3. Cliquez sur votre **imprimante thermique**
4. Sélectionnez **Gérer** puis **Définir par défaut**

> **Important** : L'imprimante thermique doit être l'imprimante par défaut du système pour un flux rapide.

---

## 🔧 Étape 3 : Configurer dans l'admin

1. Connectez-vous à l'**administration**
2. Allez dans **Paramètres → Général**
3. Section **Caisse POS** : cochez **« Ouvrir le reçu et lancer l'impression après validation de vente »**
4. Cliquez sur **Enregistrer**

---

## ✅ Utilisation

### Lors d'une vente

1. Scannez ou ajoutez les produits au panier
2. Choisissez le mode de paiement (Espèces, Carte, Mobile Money)
3. Cliquez sur **Valider la vente**
4. Le reçu s'ouvre dans une nouvelle fenêtre
5. La boîte de dialogue d'impression apparaît
6. **Appuyez sur Entrée** (ou cliquez sur Imprimer) → le reçu s'imprime

### Astuce

Si l'imprimante thermique est définie par défaut, un simple **Entrée** suffit à chaque vente. Le flux devient : *Valider → Entrée → Reçu imprimé*.

---

## ❓ Dépannage

### Le reçu ne s'ouvre pas
- Vérifiez que les **fenêtres pop-up** ne sont pas bloquées pour le site
- Dans Chrome : icône cadenas → Paramètres du site → Fenêtres contextuelles → Autoriser

### Mauvaise imprimante sélectionnée
- Définissez l'imprimante thermique comme **imprimante par défaut** du système
- Ou choisissez-la manuellement dans la boîte de dialogue à chaque impression

### Le reçu est coupé ou mal formaté
- Utilisez une imprimante **80 mm** pour un meilleur rendu
- Le format du reçu est optimisé pour 80 mm

---

## 📦 Matériel recommandé

| Élément | Recommandation |
|---------|----------------|
| Imprimante | Thermique 80 mm (USB ou Ethernet) |
| Papier | Rouleau thermique 80 x 80 mm |
| PC caisse | Windows 10/11, 4 Go RAM minimum |
| Navigateur | Chrome ou Edge (dernière version) |

---

*Documentation Chamse - Caisse POS*
