#!/bin/bash

# ============================================================
# Clinic System - Deploy Script for Fly.io
# ============================================================
# Usage: bash deploy.sh
# Run this script LOCALLY after installing flyctl
# ============================================================

set -e

echo "========================================="
echo "  MediSync - Fly.io Deployment"
echo "========================================="

# 1. Check if flyctl is installed
echo "[1/6] Checking flyctl..."
if ! command -v flyctl &> /dev/null; then
    echo "flyctl not found. Installing..."
    curl -L https://fly.io/install.sh | sh
    export PATH="$HOME/.fly/bin:$PATH"
fi
echo "flyctl version: $(flyctl version)"

# 2. Login to Fly.io
echo "[2/6] Logging in to Fly.io..."
flyctl auth login

# 3. Launch app (first time only)
if [ ! -f fly.toml ]; then
    echo "[3/6] Launching app..."
    flyctl launch --copy-config --name medisync --region ams
else
    echo "[3/6] App already launched."
fi

# 4. Create MySQL database (first time only)
echo "[4/6] Setting up MySQL..."
echo ">>> IMPORTANT: Create a MySQL database addon from Fly.io Dashboard"
echo ">>> Or use PlanetScale/ClearDB/External MySQL"
echo ">>> Then set the DB_* env variables with: flyctl secrets set DB_HOST=xxx DB_DATABASE=xxx DB_USERNAME=xxx DB_PASSWORD=xxx"

# 5. Set secrets
echo "[5/6] Setting Fly.io secrets..."
echo ">>> Run these commands to set your secrets:"
echo ""
echo "flyctl secrets set APP_KEY=base64:YOUR_KEY_HERE"
echo "flyctl secrets set STRIPE_SECRET=sk_live_YOUR_KEY"
echo "flyctl secrets set PUBLISHABLE_Key=pk_live_YOUR_KEY"
echo "flyctl secrets set STRIPE_WEBHOOK_SECRET=whsec_YOUR_SECRET"
echo "flyctl secrets set ACCOUNT_ID=acct_YOUR_ID"
echo "flyctl secrets set OPENROUTER_API_KEY=sk-or-v1-YOUR_KEY"
echo "flyctl secrets set GROQ_API_KEY=gsk_YOUR_KEY"
echo "flyctl secrets set DEEPSEEK_API_KEY=YOUR_KEY"
echo "flyctl secrets set GOOGLE_CLIENT_ID=YOUR_ID"
echo "flyctl secrets set GOOGLE_CLIENT_SECRET=YOUR_SECRET"
echo "flyctl secrets set CIPHERSWEET_KEY=YOUR_64_CHAR_HEX_KEY"
echo "flyctl secrets set MAIL_USERNAME=your@gmail.com"
echo "flyctl secrets set MAIL_PASSWORD=your_app_password"
echo ""

# 6. Deploy
echo "[6/6] Deploying..."
flyctl deploy

echo ""
echo "========================================="
echo "  Deployment Complete!"
echo "  URL: https://medisync.fly.dev"
echo "========================================="
echo ""
echo "Don't forget to:"
echo "  1. Set all secrets: flyctl secrets set KEY=VALUE"
echo "  2. Create MySQL database addon"
echo "  3. Upload Firebase JSON: flyctl volumes create firebase_data"
echo "  4. Configure Stripe Webhook: https://dashboard.stripe.com/webhooks"
echo "     -> Add endpoint: https://medisync.fly.dev/api/stripe/webhook"
echo "     -> Events: checkout.session.completed, payment_intent.succeeded, charge.refunded"
echo "  5. Update Google OAuth redirect URI: https://medisync.fly.dev/api/auth/google/callback"
echo ""
