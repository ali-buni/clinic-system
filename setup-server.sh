#!/bin/bash

# ============================================================
# Clinic System - Server Setup Script for Oracle Cloud Free Tier
# ============================================================
# Run this script ON A FRESH Ubuntu 22.04 ARM server
# Usage: sudo bash setup-server.sh
# ============================================================

set -e

echo "========================================="
echo "  Clinic System - Server Setup"
echo "  Ubuntu 22.04 (ARM) on Oracle Cloud"
echo "========================================="

# 1. System Update
echo "[1/8] Updating system packages..."
sudo apt update && sudo apt upgrade -y

# 2. Install Nginx
echo "[2/8] Installing Nginx..."
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx

# 3. Install PHP 8.2 + Extensions
echo "[3/8] Installing PHP 8.2 and extensions..."
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml \
  php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd \
  php8.2-bcmath php8.2-intl php8.2-redis php8.2-readline \
  php8.2-tokenizer php8.2-dom php8.2-sqlite3 -y

# 4. Install MySQL
echo "[4/8] Installing MySQL..."
sudo apt install mysql-server -y
sudo systemctl enable mysql
sudo systemctl start mysql

echo ""
echo ">>> MySQL Security Setup <<<"
echo "Please answer the prompts:"
sudo mysql_secure_installation

echo ""
echo ">>> Creating Database <<<"
echo "Run these MySQL commands manually:"
echo "  sudo mysql -u root -p"
echo ""
echo "  CREATE DATABASE clinic_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "  CREATE USER 'clinic_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD_HERE';"
echo "  GRANT ALL PRIVILEGES ON clinic_system.* TO 'clinic_user'@'localhost';"
echo "  FLUSH PRIVILEGES;"
echo "  EXIT;"
echo ""

# 5. Install Composer
echo "[5/8] Installing Composer..."
sudo apt install composer -y

# 6. Install Redis (optional, recommended)
echo "[6/8] Installing Redis..."
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server

# 7. Install Supervisor (for queue worker)
echo "[7/8] Installing Supervisor..."
sudo apt install supervisor -y
sudo systemctl enable supervisor
sudo systemctl start supervisor

# 8. Install Certbot (for SSL)
echo "[8/8] Installing Certbot for SSL..."
sudo apt install certbot python3-certbot-nginx -y

# Setup deployment directory
APP_DIR="/var/www/clinic-system"
echo ""
echo "Creating deployment directory..."
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR

# Copy config files
echo "Copying configuration files..."
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

if [ -f "$SCRIPT_DIR/nginx.conf" ]; then
    sudo cp "$SCRIPT_DIR/nginx.conf" /etc/nginx/sites-available/clinic-system
    sudo ln -sf /etc/nginx/sites-available/clinic-system /etc/nginx/sites-enabled/
    sudo rm -f /etc/nginx/sites-enabled/default
    sudo nginx -t
    sudo systemctl restart nginx
    echo "Nginx configured."
else
    echo "WARNING: nginx.conf not found. Please copy it manually."
fi

if [ -f "$SCRIPT_DIR/supervisor.conf" ]; then
    sudo cp "$SCRIPT_DIR/supervisor.conf" /etc/supervisor/conf.d/clinic-worker.conf
    sudo supervisorctl reread
    sudo supervisorctl update
    echo "Supervisor configured."
else
    echo "WARNING: supervisor.conf not found. Please copy it manually."
fi

# Setup firewall
echo "Configuring firewall..."
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw --force enable

# Setup cron job for Laravel scheduler
# echo "Setting up cron job for Laravel scheduler..."
# CRON_JOB="* * * * * cd /var/www/clinic-system && php artisan schedule:run >> /dev/null 2>&1"
# (crontab -l 2>/dev/null | grep -q "schedule:run") || (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
# echo "Cron job configured (runs every minute)."

echo ""
echo "========================================="
echo "  Server Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "  1. Edit /etc/nginx/sites-available/clinic-system"
echo "     - Replace 'your-domain.com' with your actual domain (e.g. medisync.com)"
echo ""
echo "  2. Create MySQL database and user (see commands above)"
echo ""
echo "  3. Deploy the application:"
echo "     bash deploy.sh"
echo ""
echo "  4. Setup SSL (if you have a domain):"
echo "     sudo certbot --nginx -d your-domain.com"
echo ""
echo "  5. Upload Firebase credentials:"
echo "     scp cmsp-1-firebase.json root@YOUR_SERVER_IP:/var/www/clinic-system/storage/app/firebase/"
echo ""
