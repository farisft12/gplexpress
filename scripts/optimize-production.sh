#!/bin/bash

# Script untuk optimasi Laravel Production
# Usage: bash scripts/optimize-production.sh

echo "🚀 Starting Laravel Production Optimization..."

# Clear all caches first
echo "📦 Clearing all caches..."
php artisan optimize:clear

# Cache configuration
echo "⚙️  Caching configuration..."
php artisan config:cache

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache

# Cache views
echo "👁️  Caching views..."
php artisan view:cache

# Cache events
echo "📅 Caching events..."
php artisan event:cache

# Optimize autoloader
echo "📚 Optimizing autoloader..."
composer dump-autoload --optimize --classmap-authoritative

echo "✅ Optimization complete!"
echo ""
echo "⚠️  IMPORTANT: Make sure APP_DEBUG=false and APP_ENV=production in .env file!"
