# Environment Configuration Guide

## Fixed .env Issue

### Problem
```
The environment file is invalid!
Failed to parse dotenv file. Encountered unexpected whitespace at [C:\Program Files\Tesseract-OCR\tesseract.exe].
```

### Root Cause
- Backslashes in Windows paths were not properly escaped in the .env file
- Spaces in paths require proper formatting in .env files

### Solution Applied
✅ **Fixed** - Commented out the problematic line and provided proper configuration instructions

---

## Current .env Configuration

Your `.env` file now has the correct format with two OCR options:

### Option 1: Google Cloud Vision API (Recommended)
```
# Uncomment if using Google Cloud Vision
GOOGLE_APPLICATION_CREDENTIALS=storage/google-vision-key.json
```

**Setup Steps:**
1. Create Google Cloud project at https://console.cloud.google.com
2. Download service account JSON key
3. Copy to `storage/google-vision-key.json`
4. Uncomment the line above in `.env`
5. Restart server

### Option 2: Tesseract-OCR (Alternative)
```
# Uncomment if you have Tesseract installed
TESSERACT_OCR_PATH=C:/Program Files/Tesseract-OCR/tesseract.exe
```

**Important Notes:**
- Use **forward slashes** (/) instead of backslashes (\)
- Only uncomment if Tesseract is actually installed
- Path must point to the executable file

### Option 3: No OCR Setup (Works Now)
```
# Leave both commented out to use manual entry only
# Users can still upload RC files and enter details manually
```

---

## .env File Format Rules

### ✅ Correct Format

**Path with spaces - use forward slashes:**
```
TESSERACT_OCR_PATH=C:/Program Files/Tesseract-OCR/tesseract.exe
```

**Path without spaces:**
```
TESSERACT_OCR_PATH=C:/Users/Admin/tesseract.exe
```

**String values with spaces - use quotes:**
```
APP_NAME="GPS Control Panel"
MAIL_FROM_NAME="GPS Cpanel"
```

**URLs:**
```
APP_URL=http://127.0.0.1:8000/
WEBSITE_URL=http://127.0.0.1:8000/
```

### ❌ Incorrect Format

**Don't use backslashes:**
```
TESSERACT_OCR_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe  ❌
```

**Don't mix quote styles:**
```
TESSERACT_OCR_PATH="C:\Program Files\Tesseract-OCR\tesseract.exe"  ❌
```

---

## Complete .env Configuration

### Current Application Settings
```
APP_NAME=gpscPortal
APP_ENV=local
APP_KEY=base64:MZ76IN/ndAXZHyI4m5vl99BUb3rQRqdFeefSMLn7A7c=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000/
WEBSITE_URL=http://127.0.0.1:8000/
```

### Database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gps_production
DB_USERNAME=root
DB_PASSWORD=""
```

### Mail Configuration
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=helpdesk@jsdelectronics.co.in
MAIL_PASSWORD="qvdl ynof zhqx gooh"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=helpdesk@jsdelectronics.co.in
MAIL_FROM_NAME="GPS Cpanel"
```

### Queue & Cache
```
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
QUEUE_DRIVER=sync
```

### Pusher (Real-time)
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2132446
PUSHER_APP_KEY=cfdb59af99db1d4b3365
PUSHER_APP_SECRET=30e3d9394e2418d060f0
PUSHER_APP_CLUSTER=ap2
```

### Security Keys
```
JWT_SECRET=W4JTlkF1YijEuday41LiLwDULZC4M3WyGTCyNzLM3eNgjZ3sn3VHHIGJnBIc7jma
DEVICE_WORKFLOW_API_TOKEN=dw_sk_7f3a9b2c1d4e5f6a8b9c0d1e2f3a4b5c6d7e8f9a
```

### OCR Configuration (Choose One)
```
# Option 1: Google Cloud Vision (Recommended)
# GOOGLE_APPLICATION_CREDENTIALS=storage/google-vision-key.json

# Option 2: Tesseract-OCR (Alternative)
# TESSERACT_OCR_PATH=C:/Program Files/Tesseract-OCR/tesseract.exe
```

---

## Server Status Check

### Verify Server is Running
```bash
# Check if server is listening on port 8000
curl http://127.0.0.1:8000/

# Should see HTML response (not error)
```

### Access the Application
```
Open browser: http://127.0.0.1:8000/
Login with your credentials
Navigate to: Device > Certificate
```

---

## Troubleshooting

### Issue: "Failed to parse dotenv file"

**Solution:**
1. Check for unescaped backslashes in paths
2. Use forward slashes: `C:/Program Files/...`
3. Wrap values with spaces in quotes
4. Comment out unused configuration lines

### Issue: "GOOGLE_APPLICATION_CREDENTIALS file not found"

**Solution:**
1. Ensure JSON key file is in `storage/google-vision-key.json`
2. Verify file exists with `ls -la storage/google-vision-key.json`
3. Check file permissions are readable
4. Ensure path is correct in `.env`

### Issue: "Tesseract not found"

**Solution:**
1. Verify Tesseract is installed
2. Check path with: `"C:/Program Files/Tesseract-OCR/tesseract.exe" --version`
3. Use correct path in `.env`
4. Ensure path uses forward slashes

### Issue: Server won't start

**Solution:**
1. Validate `.env` file syntax
2. Run: `php artisan config:cache` to clear cache
3. Run: `php artisan config:clear` to reset config
4. Check for syntax errors in `.env`
5. Ensure all required variables are set

---

## Production Deployment

### Before Deploying to Production

1. **Update `.env` for production:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Set up proper OCR:**
   ```
   GOOGLE_APPLICATION_CREDENTIALS=/path/to/production/key.json
   ```

3. **Configure proper database:**
   ```
   DB_HOST=production-db-server
   DB_DATABASE=production_db
   DB_USERNAME=prod_user
   DB_PASSWORD=secure_password
   ```

4. **Secure mail configuration:**
   ```
   MAIL_USERNAME=production@email.com
   MAIL_PASSWORD=secure_app_password
   ```

5. **Run production migrations:**
   ```bash
   php artisan migrate --force
   ```

6. **Cache configuration:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

---

## Security Best Practices

### ✅ Do's

- ✓ Keep `.env` file out of version control (add to `.gitignore`)
- ✓ Use strong, unique passwords
- ✓ Protect sensitive keys and tokens
- ✓ Use environment variables for secrets
- ✓ Regularly rotate API keys
- ✓ Use HTTPS in production

### ❌ Don'ts

- ✗ Never commit `.env` to git
- ✗ Never share API keys or credentials
- ✗ Don't use the same credentials across environments
- ✗ Don't hardcode secrets in code
- ✗ Don't expose `.env` file publicly
- ✗ Don't use debug mode in production

---

## Environment Variables Reference

| Variable | Purpose | Example |
|----------|---------|---------|
| APP_NAME | Application name | gpscPortal |
| APP_ENV | Environment type | local/production |
| APP_DEBUG | Enable debug mode | true/false |
| DB_HOST | Database server | 127.0.0.1 |
| GOOGLE_APPLICATION_CREDENTIALS | Google Vision API key | storage/key.json |
| TESSERACT_OCR_PATH | Tesseract executable | C:/Program Files/.../tesseract.exe |
| MAIL_USERNAME | Email account | user@gmail.com |
| JWT_SECRET | JWT signing key | random_secure_string |

---

## Quick Start Commands

### Start Development Server
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Validate .env
```bash
php artisan config:clear
php artisan config:cache
```

### Run Tests
```bash
php artisan test
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Support

For issues with environment configuration:
1. Check this guide for troubleshooting
2. Review the `.env` file format
3. Verify all paths use forward slashes
4. Ensure required variables are set
5. Clear Laravel cache and try again

See `GOOGLE_VISION_SETUP.md` for detailed OCR configuration.
