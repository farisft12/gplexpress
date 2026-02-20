@echo off
REM Script untuk optimasi Laravel Production (Windows)
REM Usage: scripts\optimize-production.bat

echo 🚀 Starting Laravel Production Optimization...

REM Clear all caches first
echo 📦 Clearing all caches...
php artisan optimize:clear

REM Cache configuration
echo ⚙️  Caching configuration...
php artisan config:cache

REM Cache routes
echo 🛣️  Caching routes...
php artisan route:cache

REM Cache views
echo 👁️  Caching views...
php artisan view:cache

REM Cache events
echo 📅 Caching events...
php artisan event:cache

REM Optimize autoloader
echo 📚 Optimizing autoloader...
composer dump-autoload --optimize --classmap-authoritative

echo ✅ Optimization complete!
echo.
echo ⚠️  IMPORTANT: Make sure APP_DEBUG=false and APP_ENV=production in .env file!
pause
