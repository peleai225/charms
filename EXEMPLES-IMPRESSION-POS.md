# 📝 Exemples d'Impression POS

Exemples pratiques d'utilisation du système d'impression thermique.

---

## 🚀 Démarrage rapide

### **1. Test en mode texte (sans imprimante)**

```bash
# Créer une vente de test
php artisan tinker
>>> $order = App\Models\Order::factory()->create(['source' => 'pos']);
>>> exit

# Générer le reçu en texte
php artisan receipt:print 1 --text
```

**Résultat attendu** :
```
        NOM DE LA BOUTIQUE
         123 Rue Example
         Tel: +225 XX XX XX XX

------------------------------------------------
                RECU DE VENTE
             No: CMD-2026-001234
           21/07/2026 14:35
------------------------------------------------
2x   Produit Exemple                  10000 F CFA
     Variante: Taille M

------------------------------------------------
          TOTAL: 10000 F CFA
------------------------------------------------
Paiement: Especes
------------------------------------------------
      Merci pour votre achat !
        NOM DE LA BOUTIQUE
```

---

## 🖨️ Impression réelle

### **Étape 1 : Configuration**

Ajouter dans `.env` :

```env
POS_PRINTER_ENABLED=true
POS_PRINTER_TYPE=usb
POS_PRINTER_NAME=POS-80
POS_AUTO_CUT=true
POS_CASH_DRAWER=false
```

### **Étape 2 : Trouver le nom de l'imprimante**

**Windows** :
```bash
# Ouvrir Panneau de configuration → Périphériques et imprimantes
# Copier le nom exact (ex: "POS-80", "EPSON TM-T20")
```

**Linux** :
```bash
lpstat -p -d
# Exemple sortie:
# printer POS-80 is idle.  enabled since...
```

### **Étape 3 : Tester l'impression**

```bash
php artisan receipt:print 1 --printer="POS-80"
```

**Succès** :
```
📄 Génération du reçu pour commande CMD-2026-001234...
🖨️  Impression sur 'POS-80'...
✅ Reçu CMD-2026-001234 imprimé avec succès !
```

---

## 🌐 Impression depuis le navigateur

### **Méthode 1 : Bouton HTML**

Dans le Scanner/Caisse, après validation d'une vente :

```javascript
// Ouvrir la page de reçu
window.open(`/admin/scanner/receipt/${orderId}?auto_print=1`, '_blank');
```

Le reçu s'affiche et déclenche automatiquement `window.print()`.

### **Méthode 2 : API JSON (pour apps natives)**

```javascript
async function imprimerRecu(orderId) {
    // Récupérer les données thermiques
    const response = await fetch(`/admin/scanner/receipt/${orderId}/thermal`);
    const data = await response.json();

    // Envoyer à un service local (Electron, Tauri, app Android)
    await fetch('http://localhost:3000/print', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data.receipt)
    });
}
```

---

## 📡 Impression réseau (Network printer)

### **Configuration IP**

```env
POS_PRINTER_TYPE=network
POS_PRINTER_NAME=192.168.1.100
POS_PRINTER_PORT=9100
```

### **Test de connexion**

```bash
# Vérifier que l'imprimante répond
ping 192.168.1.100

# Vérifier le port 9100 ouvert
telnet 192.168.1.100 9100
# (Si connexion réussit, tapez du texte → devrait s'imprimer)
```

### **Impression**

```bash
php artisan receipt:print 1 --printer="192.168.1.100"
```

---

## 💡 Cas d'usage avancés

### **1. Impression avec monnaie rendue**

```bash
# Depuis le scanner/caisse
$receiptUrl = route('admin.scanner.receipt', $order) 
    . '?change=' . $change 
    . '&amount_received=' . $amountReceived;
```

**Affiche sur le reçu** :
```
Paiement: Especes
Recu: 20000 F CFA
Monnaie: 5000 F CFA
```

### **2. QR Code pour suivi client**

Le QR Code est automatiquement généré si :
- `POS_QRCODE=true` dans `.env`
- L'imprimante supporte ESC/POS QR (Epson TM-T88V+, Star TSP650II)

Le client scanne → accès direct à `/account/orders/{id}`.

### **3. Ouverture tiroir-caisse**

```env
POS_CASH_DRAWER=true
```

Après impression, l'imprimante envoie un signal électrique au tiroir (RJ11).

**Test manuel** :
```php
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

$connector = new WindowsPrintConnector("POS-80");
$printer = new Printer($connector);
$printer->pulse(); // Ouvre le tiroir
$printer->close();
```

### **4. Impression batch (plusieurs reçus)**

```bash
# Imprimer toutes les commandes POS du jour
php artisan tinker
>>> $orders = App\Models\Order::where('source', 'pos')
        ->whereDate('created_at', today())
        ->get();
>>> foreach ($orders as $order) {
        Artisan::call('receipt:print', ['order' => $order->id]);
    }
```

---

## 🐛 Dépannage erreurs fréquentes

### **❌ "Nom d'imprimante non configuré"**

```bash
# Vérifier .env
grep POS_PRINTER .env

# Ou utiliser --printer directement
php artisan receipt:print 1 --printer="NOM_EXACT"
```

### **❌ "Erreur impression: Failed to open stream"**

**Cause** : Nom d'imprimante incorrect ou imprimante éteinte.

**Solution** :
```bash
# Windows : Vérifier le nom dans Périphériques
# Linux : lpstat -p -d

# Tester avec impression système
echo "Test" | lpr -P POS-80  # Linux
notepad /pt "test.txt" "POS-80"  # Windows
```

### **❌ Caractères accentués mal affichés**

**Cause** : Encodage incompatible.

**Solution** : Changer dans `.env` :
```env
POS_ENCODING=CP850  # Pour français
# Ou CP437 si problème persiste
```

### **❌ Le papier ne se coupe pas**

**Cause** : Imprimante sans guillotine OU commande non supportée.

**Solution** :
```env
# Désactiver
POS_AUTO_CUT=false

# Ou vérifier manuel imprimante (certains modèles nécessitent config DIP switch)
```

---

## 🔌 Intégration avec apps tierces

### **Electron (app desktop)**

```javascript
// main.js
const { ipcMain } = require('electron');
const escpos = require('escpos');
const device = new escpos.USB();
const printer = new escpos.Printer(device);

ipcMain.handle('print-receipt', async (event, receiptData) => {
    device.open(() => {
        receiptData.forEach(instruction => {
            if (instruction.cmd === 'text') {
                printer.text(instruction.value).feed();
            } else if (instruction.cmd === 'cut') {
                printer.cut();
            }
        });
        printer.close();
    });
});
```

### **Sunmi Android (terminal de paiement)**

```java
// MainActivity.java
import com.sunmi.peripheral.printer.SunmiPrinterService;

public void printReceipt(String orderNumber) {
    try {
        sunmiPrinterService.setAlignment(1, null); // Center
        sunmiPrinterService.printTextWithFont("RECU DE VENTE\n", null, 24, null);
        sunmiPrinterService.printText("No: " + orderNumber + "\n", null);
        // ... autres commandes
        sunmiPrinterService.cutPaper(null);
    } catch (Exception e) {
        Log.e("Printer", e.getMessage());
    }
}
```

---

## 📊 Statistiques d'impression

Pour suivre le nombre d'impressions :

```php
// app/Models/Order.php
protected static function booted()
{
    static::updated(function ($order) {
        if ($order->wasChanged('print_count')) {
            event(new OrderPrinted($order));
        }
    });
}

// Incrémenter
$order->increment('print_count');
```

Puis dans le backoffice :
```php
// Nombre de réimpressions aujourd'hui
$reprints = Order::where('print_count', '>', 1)
    ->whereDate('updated_at', today())
    ->count();
```

---

## 🎯 Bonnes pratiques

1. **Toujours tester en mode texte** avant impression réelle
2. **Configurer marge à 0** dans les paramètres du navigateur
3. **Utiliser papier 80mm** (standard) sauf contrainte d'espace
4. **Activer auto-cut** uniquement si imprimante le supporte
5. **Backup des configs** : exporter `.env` avant changements
6. **Logs** : Activer `LOG_LEVEL=debug` pour diagnostiquer erreurs

---

## 📚 Ressources

- **Documentation ESC/POS** : https://reference.epson-biz.com/modules/ref_escpos/
- **Package PHP** : https://github.com/mike42/escpos-php
- **Imprimantes recommandées** : Epson TM-T20II, Xprinter XP-80C
- **Support Chamse** : [Discord/GitHub]

---

**Propulsé par peleAi** 🚀
