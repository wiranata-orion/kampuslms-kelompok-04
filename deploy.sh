#!/usr/bin/env bash
set -e

php artisan down

git pull origin production
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart

php artisan up