#!/bin/bash
#
# Script de déploiement serveur — Chamse
#
# USAGE : bash deploy.sh
#
# Options :
#   --no-pull        ne pas faire git pull
#   --no-composer    ne pas faire composer install
#   --no-npm         ne pas faire npm run build
#   --no-maintenance ne pas activer le mode maintenance
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

NO_PULL=0
NO_COMPOSER=0
NO_NPM=0
NO_MAINTENANCE=0

for arg in "$@"; do
    case $arg in
        --no-pull)        NO_PULL=1 ;;
        --no-composer)    NO_COMPOSER=1 ;;
        --no-npm)         NO_NPM=1 ;;
        --no-maintenance) NO_MAINTENANCE=1 ;;
    esac
done

cd "$(dirname "$0")"

# Dossier public_html (fichiers statiques servis par Apache)
PUBLIC_HTML="$HOME/public_html"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Déploiement Chamse — $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# === 1. Mode maintenance ===
if [ $NO_MAINTENANCE -eq 0 ]; then
    echo -e "${YELLOW}[1/6] Activation du mode maintenance...${NC}"
    php artisan down --retry=15 --secret="deploy-$(date +%s)" || true
    echo ""
fi

# === 2. Git pull ===
if [ $NO_PULL -eq 0 ]; then
    echo -e "${YELLOW}[2/6] Mise à jour du code (git pull)...${NC}"
    # Annuler les changements locaux non commités pour éviter les conflits
    git checkout -- . 2>/dev/null || true
    git fetch --all
    BEFORE=$(git rev-parse HEAD)
    git pull origin main
    AFTER=$(git rev-parse HEAD)

    if [ "$BEFORE" = "$AFTER" ]; then
        echo -e "${GREEN}      OK : déjà à jour (${AFTER:0:7})${NC}"
    else
        echo -e "${GREEN}      OK : ${BEFORE:0:7} → ${AFTER:0:7}${NC}"
        echo "      Commits déployés :"
        git log --oneline "${BEFORE}..${AFTER}" | sed 's/^/        /'
    fi
    echo ""
fi

# === 3. Composer ===
if [ $NO_COMPOSER -eq 0 ]; then
    echo -e "${YELLOW}[3/6] Installation des dépendances Composer...${NC}"
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-intl
        echo -e "${GREEN}      OK : dépendances installées${NC}"
    else
        echo -e "${RED}      WARN : composer non trouvé${NC}"
    fi
    echo ""
fi

# === 4. Build assets (npm) ===
if [ $NO_NPM -eq 0 ]; then
    echo -e "${YELLOW}[4/6] Build des assets (npm run build)...${NC}"
    if command -v npm &> /dev/null; then
        source ~/.bashrc 2>/dev/null || true
        npm install --silent
        npm run build
        echo -e "${GREEN}      OK : assets compilés${NC}"
    else
        echo -e "${RED}      WARN : npm non trouvé, assets non recompilés${NC}"
    fi
    echo ""
fi

# === 5. Copie du build + sw.js vers public_html ===
echo -e "${YELLOW}[5/6] Synchronisation du build vers public_html...${NC}"
if [ -d "public/build" ]; then
    cp -r public/build/ "$PUBLIC_HTML/"
    echo -e "${GREEN}      OK : build copié vers $PUBLIC_HTML/build/${NC}"
else
    echo -e "${RED}      WARN : dossier public/build introuvable${NC}"
fi
# Stamp la version du SW avec le timestamp du déploiement pour invalider le cache
BUILD_TS=$(date +%Y%m%d%H%M%S)
sed "s/__BUILD__/${BUILD_TS}/" public/sw.js > "$PUBLIC_HTML/sw.js"
cp public/sw.js "$PUBLIC_HTML/sw.js.src" 2>/dev/null || true
echo -e "${GREEN}      OK : sw.js (v${BUILD_TS}) copié vers public_html${NC}"
echo ""

# === 6. Caches Laravel + migrations ===
echo -e "${YELLOW}[6/6] Caches, migrations, optimisation...${NC}"
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link 2>/dev/null || true
echo -e "${GREEN}      OK${NC}"
echo ""

# === Sortie du mode maintenance ===
if [ $NO_MAINTENANCE -eq 0 ]; then
    php artisan up
fi

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Déploiement terminé ✓${NC}"
echo -e "${GREEN}========================================${NC}"
