#!/bin/bash
# Script de nettoyage pour la production
# Supprime les fichiers sensibles et de diagnostic

echo "🧹 Nettoyage des fichiers sensibles pour la production..."

# Fichiers à supprimer
FILES_TO_REMOVE=(
    "install.php"
    "public/diagnostic.php"
    "public/fix-cache.php"
    "public/fix-vite.php"
    "public/fix-url.php"
    "public/diagnostic-500.php"
    "public/force-production.php"
)

# Compteur
REMOVED=0
NOT_FOUND=0

# Supprimer les fichiers
for file in "${FILES_TO_REMOVE[@]}"; do
    if [ -f "$file" ]; then
        rm "$file"
        echo "✅ Supprimé: $file"
        ((REMOVED++))
    else
        echo "ℹ️  Non trouvé: $file"
        ((NOT_FOUND++))
    fi
done

echo ""
echo "📊 Résumé:"
echo "   - Fichiers supprimés: $REMOVED"
echo "   - Fichiers non trouvés: $NOT_FOUND"
echo ""
echo "✅ Nettoyage terminé!"

