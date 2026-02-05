@echo off
REM Script de déploiement pour Windows
REM Prépare le projet pour le déploiement en production

echo ========================================
echo 🚀 Préparation du déploiement
echo ========================================
echo.

REM 1. Compiler les assets
echo [1/5] Compilation des assets...
call npm run build
if %errorlevel% neq 0 (
    echo ❌ ERREUR: npm run build a échoué
    pause
    exit /b 1
)
echo ✅ Assets compilés
echo.

REM 2. Régénérer l'autoload
echo [2/5] Régénération de l'autoload Composer...
call composer dump-autoload --optimize
if %errorlevel% neq 0 (
    echo ❌ ERREUR: composer dump-autoload a échoué
    pause
    exit /b 1
)
echo ✅ Autoload régénéré
echo.

REM 3. Vérifier les fichiers sensibles
echo [3/5] Vérification des fichiers sensibles...
if exist "install.php" (
    echo ⚠️  ATTENTION: install.php existe encore - À supprimer avant déploiement
)
if exist "public\diagnostic.php" (
    echo ⚠️  ATTENTION: public\diagnostic.php existe encore - À supprimer avant déploiement
)
echo ✅ Vérification terminée
echo.

REM 4. Vérifier le manifest.json
echo [4/5] Vérification du manifest.json...
if exist "public\build\manifest.json" (
    echo ✅ manifest.json trouvé
) else (
    echo ❌ ERREUR: manifest.json non trouvé - Exécutez npm run build
    pause
    exit /b 1
)
echo.

REM 5. Vérifier ViteHelper
echo [5/5] Vérification de ViteHelper...
php -r "require 'vendor/autoload.php'; if (class_exists('\App\Helpers\ViteHelper')) { echo '✅ ViteHelper chargé\n'; } else { echo '❌ ERREUR: ViteHelper non trouvé\n'; exit(1); }"
if %errorlevel% neq 0 (
    pause
    exit /b 1
)
echo.

echo ========================================
echo ✅ Préparation terminée!
echo ========================================
echo.
echo 📋 Checklist avant déploiement:
echo    [ ] Vérifier le fichier .env (APP_ENV=production, APP_DEBUG=false)
echo    [ ] Supprimer les fichiers sensibles (install.php, diagnostic.php)
echo    [ ] Tester l'application localement
echo    [ ] Uploader sur le serveur
echo    [ ] Exécuter cleanup-production.bat sur le serveur
echo.
pause

