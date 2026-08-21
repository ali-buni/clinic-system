#!/bin/sh
set -e

# Ensure .env exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure APP_KEY; a non-empty value injected via env is trusted
if [ -z "${APP_KEY:-}" ]; then
    unset APP_KEY
    grep -Eq '^APP_KEY=.+' .env || php artisan key:generate --force
fi

# Storage symlink (public/storage)
php artisan storage:link 2>/dev/null || true

# Production caches (route:cache skipped safely: closure routes exist)
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# OAuth2 keys (only if missing, so tokens are not invalidated each boot)
[ -f storage/oauth-private.key ] || php artisan passport:keys --force

# Ensure database is migrated (only if migrations exist)
if [ -d database/migrations]; then
    php artisan migrate --force 2>/dev/null || true
fi

# Seed database (only if seeders exist)
if [ -d database/seeders ]; then
    php artisan db:seed --force 2>/dev/null || true
fi

# Background queue worker
php artisan queue:work 2>/dev/null &

# Background scheduler
php artisan schedule:work 2>/dev/null &

# Foreground app server
exec "$@"
