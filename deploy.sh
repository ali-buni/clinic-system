#!/bin/bash

# ============================================================
# MediSync - Deploy Script for InfinityFree (Free)
# ============================================================
# Usage: bash deploy.sh
# Run this script LOCALLY to prepare files for upload
# ============================================================

set -e

echo "========================================="
echo "  MediSync - InfinityFree Deployment"
echo "========================================="

# 1. Check if composer is installed
echo "[1/5] Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo "Composer not found. Please install it first."
    echo "Visit: https://getcomposer.org/download/"
    exit 1
fi
echo "Composer version: $(composer --version)"

# 2. Install dependencies
echo "[2/5] Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# 3. Setup .env
echo "[3/5] Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ""
    echo ">>> IMPORTANT: Edit .env with your production values!"
    echo ">>> Run: nano .env"
    echo ""
fi

# 4. Generate APP_KEY
echo "[4/5] Generating APP_KEY..."
php artisan key:generate --force

# 5. Create helper scripts
echo "[5/5] Creating helper scripts..."
mkdir -p scripts/byethost

cat > scripts/byethost/keygen.php << 'EOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('key:generate');
echo "APP_KEY generated. Check .env file.";
EOF

cat > scripts/byethost/migrate.php << 'EOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);
echo "Migration completed with status: $status";
EOF

cat > scripts/byethost/storagelink.php << 'EOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('storage:link');
echo "Storage link created.";
EOF

cat > scripts/byethost/cache.php << 'EOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');
echo "All caches built.";
EOF

echo "Helper scripts created in scripts/byethost/"

echo ""
echo "========================================="
echo "  Ready for Upload!"
echo "========================================="
echo ""
echo "Files to upload via FTP to htdocs/:"
echo "  - app/"
echo "  - bootstrap/"
echo "  - config/"
echo "  - database/"
echo "  - public/"
echo "  - resources/"
echo "  - routes/"
echo "  - storage/"
echo "  - vendor/"
echo "  - .env"
echo "  - artisan"
echo ""
echo "DO NOT upload:"
echo "  - .git/"
echo "  - node_modules/"
echo "  - tests/"
echo "  - docs/"
echo "  - scripts/"
echo ""
echo "Connect via FileZilla:"
echo "  Host: ftpupload.net"
echo "  Username: if0_xxxxxxxx"
echo "  Password: your_password"
echo "  Port: 21"
echo ""
echo "Then visit these URLs in order:"
echo "  1. http://clinic-system-cs.infinityfree.me/keygen.php"
echo "  2. http://clinic-system-cs.infinityfree.me/migrate.php"
echo "  3. http://clinic-system-cs.infinityfree.me/storagelink.php"
echo "  4. http://clinic-system-cs.infinityfree.me/cache.php"
echo ""
echo "DELETE all PHP scripts after running!"
echo ""
