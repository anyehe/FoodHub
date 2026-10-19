@echo off
setlocal
cd /d "%~dp0..\backend"

echo ==============================================
echo FoodHub Phase 1 Runtime Check
 echo ==============================================
if not exist vendor\autoload.php (
  echo [STOP] Composer dependencies are not installed.
  echo Run: composer install
  exit /b 1
)

php artisan optimize:clear
if errorlevel 1 exit /b 1

php artisan migrate:status
if errorlevel 1 exit /b 1

echo.
echo --- Running Laravel test suite ---
php artisan test
if errorlevel 1 exit /b 1

echo.
echo --- Route registration ---
php artisan route:list
if errorlevel 1 exit /b 1

echo.
echo Phase 1 runtime checks completed successfully.
endlocal
