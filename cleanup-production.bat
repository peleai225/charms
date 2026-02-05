@echo off
REM Script de nettoyage pour la production (Windows)
REM Supprime les fichiers sensibles et de diagnostic

echo 🧹 Nettoyage des fichiers sensibles pour la production...
echo.

set REMOVED=0
set NOT_FOUND=0

REM Supprimer install.php
if exist "install.php" (
    del /F /Q "install.php"
    echo ✅ Supprimé: install.php
    set /a REMOVED+=1
) else (
    echo ℹ️  Non trouvé: install.php
    set /a NOT_FOUND+=1
)

REM Supprimer les fichiers dans public/
if exist "public\diagnostic.php" (
    del /F /Q "public\diagnostic.php"
    echo ✅ Supprimé: public\diagnostic.php
    set /a REMOVED+=1
) else (
    echo ℹ️  Non trouvé: public\diagnostic.php
    set /a NOT_FOUND+=1
)

if exist "public\fix-cache.php" (
    del /F /Q "public\fix-cache.php"
    echo ✅ Supprimé: public\fix-cache.php
    set /a REMOVED+=1
)

if exist "public\fix-vite.php" (
    del /F /Q "public\fix-vite.php"
    echo ✅ Supprimé: public\fix-vite.php
    set /a REMOVED+=1
)

if exist "public\fix-url.php" (
    del /F /Q "public\fix-url.php"
    echo ✅ Supprimé: public\fix-url.php
    set /a REMOVED+=1
)

if exist "public\diagnostic-500.php" (
    del /F /Q "public\diagnostic-500.php"
    echo ✅ Supprimé: public\diagnostic-500.php
    set /a REMOVED+=1
)

if exist "public\force-production.php" (
    del /F /Q "public\force-production.php"
    echo ✅ Supprimé: public\force-production.php
    set /a REMOVED+=1
)

echo.
echo 📊 Résumé:
echo    - Fichiers supprimés: %REMOVED%
echo    - Fichiers non trouvés: %NOT_FOUND%
echo.
echo ✅ Nettoyage terminé!
pause

