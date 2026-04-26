#!/bin/bash
#
# Script de déploiement serveur — Chamse
#
# À lancer DIRECTEMENT SUR LE SERVEUR DE PRODUCTION après une mise à jour.
# Ce script :
#   1. Met le site en mode maintenance
#   2. Pull la dernière version depuis Git
#   3. Installe les dépendances Composer (--no-dev pour la prod)
#   4. Lance la commande Artisan deploy:after-pull qui s'occupe des caches,
#      migrations, OPcache et lien storage.
#   5. Sort du mode maintenance
#
# USAGE :
#   chmod +x deploy.sh
#   ./deploy.sh
#
# Pour sauter certaines étapes :
#   ./deploy.sh --no-pull      (utilise le code déjà présent)
#   ./deploy.sh --no-composer  (n'exécute pas composer install)
#   ./deploy.sh --no-maintenance (ne met pas en mode maintenance)
#

set -e

# === Couleurs pour la sortie ===
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# === Options ===
NO_PULL=0
NO_COMPOSER=0
NO_MAINTENANCE=0

for arg in "$@"; do
    case $arg in
        --no-pull) NO_PULL=1 ;;
        --no-composer) NO_COMPOSER=1 ;;
        --no-maintenance) NO_MAINTENANCE=1 ;;
        -h|--help)
            head -n 25 "$0" | tail -n 24
            exit 0
            ;;
    esac
done

cd "$(dirname "$0")"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Déploiement Chamse — $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# === 1. Mode maintenance ===
if [ $NO_MAINTENANCE -eq 0 ]; then
    echo -e "${YELLOW}[1/5] Activation du mode maintenance...${NC}"
    php artisan down --retry=15 --secret="deploy-$(date +%s)" || true
    echo ""
fi

# === 2. Git pull ===
if [ $NO_PULL -eq 0 ]; then
    echo -e "${YELLOW}[2/5] Mise à jour du code (git pull)...${NC}"
    git fetch --all
    BEFORE=$(git rev-parse HEAD)
    git pull --rebase
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
    echo -e "${YELLOW}[3/5] Installation des dépendances Composer (production)...${NC}"
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction
        echo -e "${GREEN}      OK : dépendances installées${NC}"
    else
        echo -e "${RED}      WARN : composer non trouvé dans le PATH, dépendances non mises à jour${NC}"
    fi
    echo ""
fi

# === 4. Tâches post-pull (Artisan) ===
echo -e "${YELLOW}[4/5] Tâches post-pull (caches, migrations, OPcache)...${NC}"
php artisan deploy:after-pull
echo ""

# === 5. Sortie du mode maintenance ===
if [ $NO_MAINTENANCE -eq 0 ]; then
    echo -e "${YELLOW}[5/5] Désactivation du mode maintenance...${NC}"
    php artisan up
    echo -e "${GREEN}      OK : site de nouveau en ligne${NC}"
    echo ""
fi

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Déploiement terminé avec succès${NC}"
echo -e "${GREEN}========================================${NC}"
