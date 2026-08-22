#!/bin/bash
#
# Script de déploiement — legrandbazar.ci (cPanel / LiteSpeed)
#
# USAGE depuis ~/charms/ sur le serveur :
#   bash deploy-legrandbazar.sh
#
# Options :
#   --no-pull        ne pas faire git pull
#   --no-composer    ne pas faire composer install
#   --no-maintenance ne pas activer le mode maintenance
#
# NOTE : npm build est fait en LOCAL et commité dans git.
#        Ce script ne lance PAS npm sur le serveur.
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

NO_PULL=0
NO_COMPOSER=0
NO_MAINTENANCE=0

for arg in "$@"; do
    case $arg in
        --no-pull)        NO_PULL=1 ;;
        --no-composer)    NO_COMPOSER=1 ;;
        --no-maintenance) NO_MAINTENANCE=1 ;;
    esac
done

# Dossiers
APP_DIR="$HOME/charms"
PUBLIC_HTML="$HOME/public_html"

cd "$APP_DIR"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Déploiement legrandbazar.ci — $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# === 1. Mode maintenance ===
if [ $NO_MAINTENANCE -eq 0 ]; then
    echo -e "${YELLOW}[1/5] Activation du mode maintenance...${NC}"
    php artisan down --retry=15 || true
    echo ""
fi

# === 2. Git pull ===
if [ $NO_PULL -eq 0 ]; then
    echo -e "${YELLOW}[2/5] Mise à jour du code (git pull)...${NC}"
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

# === 3. Composer (cherche le binaire dans plusieurs emplacements) ===
if [ $NO_COMPOSER -eq 0 ]; then
    echo -e "${YELLOW}[3/5] Installation des dépendances Composer...${NC}"
    COMPOSER_BIN=""
    for candidate in \
        "/usr/local/cpanel/3rdparty/bin/composer" \
        "$APP_DIR/composer.phar" \
        "$HOME/composer.phar" \
        "$(which composer 2>/dev/null)"; do
        if [ -n "$candidate" ] && [ -f "$candidate" ]; then
            COMPOSER_BIN="$candidate"
            break
        fi
    done

    if [ -n "$COMPOSER_BIN" ]; then
        php "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-intl
        echo -e "${GREEN}      OK : dépendances installées (via $COMPOSER_BIN)${NC}"
    else
        echo -e "${RED}      WARN : composer introuvable — dépendances non mises à jour${NC}"
    fi
    echo ""
fi

# === 4. Sync build vers public_html ===
# LiteSpeed ne suit pas les symlinks hors document root, on copie directement.
echo -e "${YELLOW}[4/5] Synchronisation build → public_html...${NC}"
if [ -d "$APP_DIR/public/build" ]; then
    rsync -a --delete "$APP_DIR/public/build/" "$PUBLIC_HTML/build/"
    echo -e "${GREEN}      OK : build synchronisé vers $PUBLIC_HTML/build/${NC}"
else
    echo -e "${RED}      WARN : $APP_DIR/public/build introuvable${NC}"
fi

# .htaccess
cp "$APP_DIR/public/.htaccess" "$PUBLIC_HTML/.htaccess"
echo -e "${GREEN}      OK : .htaccess copié${NC}"

# sw.js
BUILD_TS=$(date +%Y%m%d%H%M%S)
if [ -f "$APP_DIR/public/sw.js" ]; then
    sed "s/__BUILD__/${BUILD_TS}/" "$APP_DIR/public/sw.js" > "$PUBLIC_HTML/sw.js"
    echo -e "${GREEN}      OK : sw.js (v${BUILD_TS}) copié${NC}"
fi
echo ""

# === 5. Caches Laravel + migrations ===
echo -e "${YELLOW}[5/5] Migrations, caches, optimisation...${NC}"
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
echo -e "${GREEN}  Déploiement legrandbazar.ci terminé ✓${NC}"
echo -e "${GREEN}========================================${NC}"
