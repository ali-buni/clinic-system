#!/bin/bash

# ============================================================
# Clinic System - Deploy Script for Oracle Cloud Free Tier
# ============================================================
# Usage: bash deploy.sh
# Run this script ON THE SERVER after initial setup (setup-server.sh)
# ============================================================

set -e

APP_DIR="/var/www/clinic-system"
REPO_URL="https://github.com/your-username/clinic-system.git"
BRANCH="main"

echo "========================================="
echo "  Clinic System - Deployment"
echo "========================================="

# 1. Clone or pull code
if [ ! -d "$APP_DIR/.git" ]; then
    echo "[1/7] Cloning repository..."
    sudo mkdir -p $APP_DIR
    sudo chown $USER:$USER $APP_DIR
    git clone -b $BRANCH $REPO_URL $APP_DIR
    cd $APP_DIR
else
    echo "[1/7] Pulling latest changes..."
    cd $APP_DIR
    git pull origin $BRANCH
fi

# 2. Install dependencies
echo "[2/7] Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# 3. Setup .env
echo "[3/7] Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
    echo ""
    echo ">>> IMPORTANT: Edit .env with your production values!"
    echo ">>> Run: nano $APP_DIR/.env"
    echo ""
fi

# 4. Run migrations
echo "[4/7] Running database migrations..."
php artisan migrate --force

# 5. Cache configurations
echo "[5/7] Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Setup storage link
echo "[6/7] Creating storage link..."
php artisan storage:link --force

# 7. Set permissions
echo "[7/7] Setting permissions..."
sudo chown -R www-data:www-data $APP_DIR/storage
sudo chown -R www-data:www-data $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# 8. Setup cron job for scheduler
# echo "[+] Setting up cron job for scheduler..."
# CRON_JOB="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
# (crontab -l 2>/dev/null | grep -q "schedule:run") || (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
# echo "Cron job configured (runs every minute)."

# 9. Clear old caches before restarting
# echo "[+] Clearing old caches..."
# php artisan config:clear 2>/dev/null || true
# php artisan route:clear 2>/dev/null || true
# php artisan view:clear 2>/dev/null || true

# Re-cache for production
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl reread 2>/dev/null || true
sudo supervisorctl update 2>/dev/null || true
sudo supervisorctl restart "clinic-worker:*" 2>/dev/null || true

echo ""
echo "========================================="
echo "  Deployment Complete!"
echo "  URL: https://medisync.com"
echo "========================================="
echo ""
echo "Don't forget to:"
echo "  1. Edit .env: nano $APP_DIR/.env"
echo "  2. Upload Firebase JSON: scp cmsp-1-firebase.json root@server:$APP_DIR/storage/app/firebase/"
echo "  3. Setup SSL: sudo certbot --nginx -d medisync.com"
echo "  4. Configure Stripe Webhook: https://dashboard.stripe.com/webhooks"
echo "     -> Add endpoint: https://medisync.com/api/stripe/webhook"
echo "     -> Events: checkout.session.completed, payment_intent.succeeded, charge.refunded"
echo ""
