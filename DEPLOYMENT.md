# MediSync — Deployment Guide (Fly.io)

## What You Need to Create

### Free Accounts:

1. **GitHub** → https://github.com/signup
2. **Fly.io** → https://fly.io/sign-up
3. **PlanetScale** (MySQL) → https://planetscale.com
4. **OpenRouter** (AI) → https://openrouter.ai
5. **Groq** (AI) → https://console.groq.com
6. **DeepSeek** (AI) → https://platform.deepseek.com
7. **Stripe** (Payments) → https://dashboard.stripe.com
8. **Google Cloud Console** (OAuth) → https://console.cloud.google.com
9. **Firebase** (Push Notifications) → https://console.firebase.google.com

---

## Step 1: GitHub

1. Go to https://github.com/signup
2. Sign up with email or Google
3. Verify your email
4. Create a new repository named `clinic-system`
5. Note your username (e.g., `your-username`)

---

## Step 2: Fly.io

1. Go to https://fly.io/sign-up
2. Sign up with GitHub (easiest)
3. Verify your email
4. Install flyctl CLI on your computer:

```powershell
# Windows
powershell -Command "iwr https://fly.io/install.ps1 -useb | iex"

# Restart terminal after install
```

5. Open terminal and run:

```bash
flyctl auth login
```

6. A browser window opens → click "Authorize Fly.io"

---

## Step 3: PlanetScale (Free MySQL Database)

1. Go to https://planetscale.com
2. Sign up with GitHub
3. Click "Create database"
4. Name: `clinic-system`
5. Click "Connect" → choose "PHP"
6. Copy these values:
    - `DB_HOST` = Host URL
    - `DB_DATABASE` = Database name
    - `DB_USERNAME` = Username
    - `DB_PASSWORD` = Password (generate new)

---

## Step 4: OpenRouter (Free AI)

1. Go to https://openrouter.ai
2. Sign up with GitHub
3. Click "Keys" → "Create Key"
4. Copy the key: `sk-or-v1-xxxxxxxx`
5. This is your `OPENROUTER_API_KEY`

---

## Step 5: Groq (Free AI)

1. Go to https://console.groq.com
2. Sign up with GitHub
3. Click "API Keys" → "Create API Key"
4. Copy the key: `gsk_xxxxxxxx`
5. This is your `GROQ_API_KEY`

---

## Step 6: DeepSeek (Free AI)

1. Go to https://platform.deepseek.com
2. Sign up / Login
3. Click "API Keys" → "Create API Key"
4. Copy the key: `sk-xxxxxxxx`
5. This is your `DEEPSEEK_API_KEY`

---

## Step 7: Stripe (Payments)

1. Go to https://dashboard.stripe.com
2. Sign up
3. Go to "Developers" → "API Keys"
4. Copy:
    - `STRIPE_SECRET` = Secret key (starts with `sk_live_`)
    - `PUBLISHABLE_Key` = Publishable key (starts with `pk_live_`)
5. Go to "Webhooks" → "Add endpoint"
6. URL: `https://medisync.fly.dev/api/stripe/webhook`
7. Events: `checkout.session.completed`, `payment_intent.succeeded`, `charge.refunded`
8. Copy the Signing Secret → this is your `STRIPE_WEBHOOK_SECRET`
9. Go to "Settings" → "Account details" → copy Account ID → this is your `ACCOUNT_ID`

---

## Step 8: Google OAuth

1. Go to https://console.cloud.google.com
2. Create new project: "MediSync"
3. Go to "APIs & Services" → "Credentials"
4. Click "Create Credentials" → "OAuth 2.0 Client ID"
5. Application type: "Web application"
6. Name: "MediSync"
7. Authorized redirect URIs: add:
    - `https://medisync.fly.dev/api/auth/google/callback`
8. Copy:
    - `GOOGLE_CLIENT_ID` = Client ID
    - `GOOGLE_CLIENT_SECRET` = Client Secret

---

## Step 9: Firebase (Push Notifications)

1. Go to https://console.firebase.google.com
2. Click "Add project"
3. Name: "MediSync"
4. Go to "Project Settings" → "Service accounts"
5. Click "Generate new private key"
6. Save the JSON file as: `cmsp-1-firebase.json`
7. Place it in: `storage/app/firebase/cmsp-1-firebase.json`

---

## Step 10: Gmail App Password (for Email)

1. Go to https://myaccount.google.com
2. Security → 2-Step Verification → enable it
3. After enabling → "App passwords"
4. Generate new → copy the 16-character password
5. This is your `MAIL_PASSWORD`
6. `MAIL_USERNAME` = your Gmail address

---

## Step 11: Deploy to Fly.io

### From your computer (terminal):

```bash
# 1. Go to project folder
cd C:\Files\code\laravelEX\clinic-system

# 2. Push code to GitHub
git add .
git commit -m "Ready for deployment"
git push

# 3. Launch on Fly.io
flyctl launch --copy-config --name medisync --region ams

# 4. Set ALL secrets
flyctl secrets set APP_KEY=base64:YOUR_APP_KEY_HERE
flyctl secrets set DB_HOST=YOUR_PLANETSCALE_HOST
flyctl secrets set DB_DATABASE=clinic_system
flyctl secrets set DB_USERNAME=YOUR_PLANETSCALE_USERNAME
flyctl secrets set DB_PASSWORD=YOUR_PLANETSCALE_PASSWORD
flyctl secrets set MAIL_USERNAME=your@gmail.com
flyctl secrets set MAIL_PASSWORD=your_gmail_app_password
flyctl secrets set CIPHERSWEET_KEY=your_ciphersweet_key
flyctl secrets set GOOGLE_CLIENT_ID=your_google_client_id
flyctl secrets set GOOGLE_CLIENT_SECRET=your_google_client_secret
flyctl secrets set OPENROUTER_API_KEY=sk-or-v1-your_key
flyctl secrets set GROQ_API_KEY=gsk_your_key
flyctl secrets set DEEPSEEK_API_KEY=sk-your_key
flyctl secrets set STRIPE_SECRET=sk_live_your_key
flyctl secrets set PUBLISHABLE_Key=pk_live_your_key
flyctl secrets set STRIPE_WEBHOOK_SECRET=whsec_your_secret
flyctl secrets set ACCOUNT_ID=acct_your_id

# 5. Deploy
flyctl deploy
```

---

## Step 12: Update Google OAuth Redirect URI

1. Go to https://console.cloud.google.com
2. Credentials → your OAuth client
3. Add redirect URI: `https://medisync.fly.dev/api/auth/google/callback`
4. Save

---

## Step 13: Update Stripe Webhook URL

1. Go to https://dashboard.stripe.com
2. Webhooks → your webhook
3. Update URL to: `https://medisync.fly.dev/api/stripe/webhook`
4. Save

---

## Step 14: Test

```bash
# Test health endpoint
curl https://medisync.fly.dev/api/health

# Expected response:
# {"status":"ok","timestamp":"...","app":"MediSync","env":"production"}
```

---

## Checklist

- [ ] GitHub account created
- [ ] Fly.io account created
- [ ] flyctl installed and logged in
- [ ] PlanetScale database created
- [ ] OpenRouter API key obtained
- [ ] Groq API key obtained
- [ ] DeepSeek API key obtained
- [ ] Stripe live keys obtained
- [ ] Stripe webhook configured
- [ ] Google OAuth configured
- [ ] Firebase credentials placed
- [ ] Gmail App Password created
- [ ] All secrets set on Fly.io
- [ ] Code pushed to GitHub
- [ ] Deployed to Fly.io
- [ ] Health endpoint working

---

## Troubleshooting

### "Class not found" error

```bash
flyctl ssh console
composer dump-autoload
```

### Database connection error

```bash
flyctl secrets set DB_HOST=xxx DB_DATABASE=xxx DB_USERNAME=xxx DB_PASSWORD=xxx
flyctl deploy
```

### Firebase not working

```bash
flyctl ssh console
ls -la /var/www/html/storage/app/firebase/
```

### Stripe webhook not receiving

- Check webhook URL in Stripe Dashboard
- Check webhook signing secret matches `.env`

---

## Useful Commands

```bash
# View logs
flyctl logs

# SSH into server
flyctl ssh console

# Run artisan commands
flyctl ssh console -C "php artisan migrate --force"

# View secrets
flyctl secrets list

# Redeploy
flyctl deploy

# Scale to zero (save money)
flyctl scale count 0

# Scale back up
flyctl scale count 1
```
