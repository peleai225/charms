# 🖨️ Impression POS - Guide d'Intégration

Guide complet pour intégrer des imprimantes thermiques POS (80mm/58mm) avec Chamse.

---

## 📋 Table des matières

1. [Formats d'impression disponibles](#formats)
2. [Imprimantes compatibles](#imprimantes)
3. [Méthode 1 : Impression navigateur (simple)](#methode-1)
4. [Méthode 2 : ESC/POS via USB/Network (pro)](#methode-2)
5. [Configuration recommandée](#configuration)
6. [Dépannage](#depannage)

---

## <a name="formats"></a>📄 Formats d'impression disponibles

### 1. **HTML (navigateur)**
- **URL** : `/admin/scanner/receipt/{order_id}`
- **Usage** : Impression via Ctrl+P ou bouton "Imprimer"
- **Compatible** : Toutes imprimantes (système)
- **Avantages** : Simple, fonctionne partout
- **Limites** : Pas de contrôle avancé (coupe papier, tiroir-caisse)

### 2. **JSON Thermique (ESC/POS)**
- **URL** : `/admin/scanner/receipt/{order_id}/thermal`
- **Usage** : Driver ESC/POS pour envoyer commandes brutes
- **Compatible** : Imprimantes thermiques POS (USB/Network/Bluetooth)
- **Avantages** : Contrôle total (coupe, QR code, tiroir-caisse)
- **Format** :
```json
{
  "success": true,
  "order_number": "CMD-2026-001234",
  "receipt": [
    {"cmd": "align", "value": "center"},
    {"cmd": "text", "value": "NOM DE LA BOUTIQUE", "bold": true, "size": "large"},
    {"cmd": "feed", "lines": 1},
    ...
    {"cmd": "cut"}
  ]
}
```

### 3. **Texte brut**
- **URL** : `/admin/scanner/receipt/{order_id}/text`
- **Usage** : Copier-coller ou debug
- **Format** : Texte ASCII simple

---

## <a name="imprimantes"></a>🖨️ Imprimantes compatibles

### **Marques testées**
- ✅ **Epson** TM-T20, TM-T82, TM-T88
- ✅ **Star Micronics** TSP100, TSP650
- ✅ **Bixolon** SRP-350, SRP-380
- ✅ **Rongta** RP80, RP326
- ✅ **Xprinter** XP-80C, XP-Q200
- ✅ **Sunmi** V2 Pro (Android intégré)

### **Protocoles**
- **ESC/POS** : Standard universel (recommandé)
- **STAR Line Mode** : Star Micronics
- **ESC/POS + Commands** : Epson extended

---

## <a name="methode-1"></a>🌐 Méthode 1 : Impression navigateur (Simple)

### **Étapes**

1. **Ouvrir le reçu** :
   ```
   http://127.0.0.1:8000/admin/scanner/receipt/{order_id}?auto_print=1
   ```

2. **Configurer l'imprimante** :
   - Ctrl+P → Sélectionner imprimante thermique
   - Marges : `0mm` partout
   - Taille papier : `80mm` (ou `58mm`)
   - Échelle : `100%`

3. **Imprimer** : Entrée ou bouton "Imprimer"

### **Avantages**
- ✅ Zéro configuration technique
- ✅ Fonctionne sur Windows/Mac/Linux
- ✅ Compatible avec toutes imprimantes

### **Limites**
- ❌ Pas de coupe automatique du papier
- ❌ Pas d'ouverture tiroir-caisse
- ❌ Dépend du navigateur

---

## <a name="methode-2"></a>⚡ Méthode 2 : ESC/POS via USB/Network (Pro)

### **1. Installation PHP ESC/POS (backend)**

```bash
cd c:/laragon/www/chamse
composer require mike42/escpos-php
```

### **2. Connecter l'imprimante**

#### **USB (Windows/Linux)**
```php
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

$connector = new WindowsPrintConnector("POS-80"); // Nom imprimante dans Périphériques
$printer = new Printer($connector);
$printer->text("Bonjour Chamse!\n");
$printer->cut();
$printer->close();
```

#### **Network (IP)**
```php
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

$connector = new NetworkPrintConnector("192.168.1.100", 9100); // IP + port
$printer = new Printer($connector);
```

#### **Bluetooth (Android/Sunmi)**
Via app Android native avec SDK Sunmi ou driver générique.

### **3. Implémenter l'impression**

Créer une commande Artisan :

```php
// app/Console/Commands/PrintReceipt.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\ThermalPrinterService;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrintReceipt extends Command
{
    protected $signature = 'receipt:print {order_id}';
    protected $description = 'Imprimer un reçu sur imprimante thermique';

    public function handle(ThermalPrinterService $service)
    {
        $order = Order::findOrFail($this->argument('order_id'));

        try {
            $connector = new WindowsPrintConnector(config('pos.printer_name', 'POS-80'));
            $printer = new Printer($connector);

            // Générer le reçu
            $receiptData = $service->generateReceipt($order);

            foreach ($receiptData as $instruction) {
                match($instruction['cmd']) {
                    'text' => $printer->text($instruction['value'] . "\n"),
                    'feed' => $printer->feed($instruction['lines'] ?? 1),
                    'cut' => $printer->cut(),
                    'align' => $printer->setJustification(
                        match($instruction['value']) {
                            'center' => Printer::JUSTIFY_CENTER,
                            'right' => Printer::JUSTIFY_RIGHT,
                            default => Printer::JUSTIFY_LEFT
                        }
                    ),
                    default => null
                };
            }

            $printer->close();
            $this->info("✅ Reçu {$order->order_number} imprimé !");
        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
        }
    }
}
```

Utilisation :
```bash
php artisan receipt:print 1234
```

### **4. Intégration JavaScript (frontend)**

Pour déclencher l'impression depuis le navigateur :

```javascript
// Appel API depuis le Scanner POS
async function printThermalReceipt(orderId) {
    const response = await fetch(`/admin/scanner/receipt/${orderId}/thermal`);
    const data = await response.json();

    // Option A : Envoyer au backend via WebSocket/Polling
    await fetch('/admin/pos/print', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
    });

    // Option B : Utiliser un service local (Electron/Tauri app)
    // window.electronAPI.print(data.receipt);
}
```

---

## <a name="configuration"></a>⚙️ Configuration recommandée

### **Fichier `.env`**

```env
# Imprimante POS
POS_PRINTER_ENABLED=true
POS_PRINTER_TYPE=usb           # usb, network, bluetooth
POS_PRINTER_NAME="POS-80"      # Nom Windows/Linux
POS_PRINTER_IP=192.168.1.100   # Si network
POS_PRINTER_PORT=9100          # Port network (défaut 9100)
POS_PRINTER_WIDTH=48           # 48 chars (80mm), 32 chars (58mm)
POS_AUTO_PRINT=false           # Impression auto après vente
POS_AUTO_CUT=true              # Coupe papier automatique
POS_CASH_DRAWER=true           # Ouvrir tiroir-caisse
```

### **Paramètres Admin**

Aller dans **Paramètres → Caisse (POS)** :

- ✅ Activer l'impression auto après vente
- ✅ Format papier : 80mm (standard) ou 58mm (compact)
- ✅ Inclure logo boutique dans l'en-tête
- ✅ Afficher QR code pour suivi commande
- ✅ Ouvrir tiroir-caisse après impression

---

## <a name="depannage"></a>🔧 Dépannage

### **❌ L'imprimante n'est pas détectée**

1. Vérifier qu'elle est allumée et connectée (USB/réseau)
2. Windows : `Périphériques et imprimantes` → vérifier nom exact
3. Linux : `lpstat -p -d` pour lister imprimantes
4. Tester avec impression de test système

### **❌ Caractères bizarres imprimés**

- **Cause** : Encodage incorrect
- **Solution** : S'assurer que le texte est en UTF-8 et que l'imprimante supporte les accents français
- **Fix** : Dans `ThermalPrinterService`, ajouter :
```php
$text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
```

### **❌ Marges trop grandes (HTML)**

- Ctrl+P → Marges : `Aucune` ou `0mm`
- Désactiver en-têtes/pieds de page
- Échelle : `100%` (pas de réduction)

### **❌ Le papier ne se coupe pas**

- **USB** : Vérifier que `$printer->cut()` est appelé
- **HTML** : Impossible via navigateur (limitation)
- **Solution** : Utiliser ESC/POS natif

### **❌ QR Code ne s'affiche pas**

- Imprimante doit supporter commande ESC/POS QR
- Modèles compatibles : Epson TM-T88V+, Star TSP650II
- Alternative : Générer QR en image PNG et l'inclure

---

## 📦 Packages recommandés

### **Backend (PHP)**
- `mike42/escpos-php` : Driver ESC/POS complet
- `picqer/php-barcode-generator` : Codes-barres (déjà installé)

### **Frontend (JS)**
- `escpos-buffer` : Générer commandes ESC/POS en JS
- `qz-tray` : Bridge pour impression locale (alternative Electron)

### **Matériel**
- **Imprimante** : Epson TM-T20II (150 €) ou Xprinter XP-80C (60 €)
- **Tiroir-caisse** : RJ11 compatible imprimante
- **Lecteur code-barres** : Honeywell Voyager 1200g (USB)

---

## 🎯 Résumé rapide

| Besoin | Solution | Complexité |
|--------|----------|-----------|
| Impression simple | HTML + Ctrl+P | ⭐ Facile |
| Coupe papier auto | ESC/POS + USB | ⭐⭐ Moyen |
| Tiroir-caisse | ESC/POS + RJ11 | ⭐⭐ Moyen |
| Network printing | ESC/POS + IP | ⭐⭐⭐ Avancé |
| App mobile (Sunmi) | Android SDK | ⭐⭐⭐ Avancé |

---

## 📞 Support

**Problème non résolu ?**
- GitHub Issues : https://github.com/peleai/chamse/issues
- Documentation ESC/POS : https://github.com/mike42/escpos-php
- Discord communauté : [lien]

---

**Propulsé par peleAi** 🚀
