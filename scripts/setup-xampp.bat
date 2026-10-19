@echo off
setlocal
cd /d "%~dp0..\backend"
echo ========================================
echo FoodHub Laravel XAMPP Setup
echo ========================================
where php >nul 2>nul || (echo PHP not found. Add XAMPP PHP to PATH.&exit /b 1)
where composer >nul 2>nul || (echo Composer not found. Install Composer and retry.&exit /b 1)
if not exist .env copy .env.example .env
php -r "file_exists('storage/framework/sessions') || mkdir('storage/framework/sessions',0777,true);"
php artisan key:generate
if exist composer.lock (
  echo Installing locked Composer dependencies...
  composer install --prefer-dist --no-interaction
) else (
  echo No composer.lock found. Resolving dependencies and creating it now...
  composer update --no-install --prefer-dist --no-interaction
  if errorlevel 1 exit /b 1
  composer install --prefer-dist --no-interaction
  if errorlevel 1 exit /b 1
)
php artisan storage:link
php artisan migrate --force
if errorlevel 1 exit /b 1
php artisan optimize:clear
if errorlevel 1 exit /b 1
echo.
echo FoodHub backend is ready.
echo Run: php artisan serve --host=127.0.0.1 --port=8000
echo Website: http://127.0.0.1:8000
endlocal
