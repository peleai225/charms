#!/bin/bash
# Script de déploiement pour Linux/Mac
# Prépare le projet pour le déploiement en production

echo "========================================"
echo "🚀 Préparation du déploiement"
echo "========================================"
echo ""

# 1. Compiler les assets
echo "[1/5] Compilation des assets..."
npm run build
if [ $? -ne 0 ]; then
    echo "❌ ERREUR: npm run build a échoué"
    exit 1
fi
echo "✅ Assets compilés"
echo ""

# 2. Régénérer l'autoload
echo "[2/5] Régénération de l'autoload Composer..."
composer dump-autoload --optimize
if [ $? -ne 0 ]; then
    echo "❌ ERREUR: composer dump-autoload a échoué"
    exit 1
fi
echo "✅ Autoload régénéré"
echo ""

# 3. Vérifier les fichiers sensibles
echo "[3/5] Vérification des fichiers sensibles..."
if [ -f "install.php" ]; then
    echo "⚠️  ATTENTION: install.php existe encore - À supprimer avant déploiement"
fi
if [ -f "public/diagnostic.php" ]; then
    echo "⚠️  ATTENTION: public/diagnostic.php existe encore - À supprimer avant déploiement"
fi
echo "✅ Vérification terminée"
echo ""

# 4. Vérifier le manifest.json
echo "[4/5] Vérification du manifest.json..."
if [ -f "public/build/manifest.json" ]; then
    echo "✅ manifest.json trouvé"
else
    echo "❌ ERREUR: manifest.json non trouvé - Exécutez npm run build"
    exit 1
fi
echo ""

# 5. Vérifier ViteHelper
echo "[5/5] Vérification de ViteHelper..."
php -r "require 'vendor/autoload.php'; if (class_exists('\App\Helpers\ViteHelper')) { echo '✅ ViteHelper chargé\n'; } else { echo '❌ ERREUR: ViteHelper non trouvé\n'; exit(1); }"
if [ $? -ne 0 ]; then
    exit 1
fi
echo ""

echo "========================================"
echo "✅ Préparation terminée!"
echo "========================================"
echo ""
echo "📋 Checklist avant déploiement:"
echo "   [ ] Vérifier le fichier .env (APP_ENV=production, APP_DEBUG=false)"
echo "   [ ] Supprimer les fichiers sensibles (install.php, diagnostic.php)"
echo "   [ ] Tester l'application localement"
echo "   [ ] Uploader sur le serveur"
echo "   [ ] Exécuter cleanup-production.sh sur le serveur"
echo ""

