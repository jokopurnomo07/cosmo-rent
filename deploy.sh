#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x build-app.sh`

# Exit the script if any command fails
set -e
chmod -R 755 public
chmod -R 755 storage
chmod -R 755 bootstrap/cache
# Build assets using NPM
npm run build

# Clear cache
php artisan optimize:clear

# Cache the various components of the Laravel application
php artisan config:clear
php artisan cache:clear
php artisan event:clear
php artisan route:clear
php artisan view:clear
