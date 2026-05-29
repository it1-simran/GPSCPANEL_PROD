# Google Cloud Vision API Setup Guide

## Overview

The GPS Control Panel now supports Google Cloud Vision API for automatic RC (Registration Certificate) document extraction. This is a cloud-based OCR solution that provides better accuracy and requires no local installation.

## Why Google Cloud Vision API?

✅ **Advantages:**
- Better accuracy for complex documents
- No local software installation required
- Works on any server (cloud, VPS, shared hosting)
- Handles scanned PDFs and images
- Pay-per-use pricing (first 1000 requests/month free)
- Automatic fallback to Tesseract if configured
- Enterprise-grade reliability

## Prerequisites

- Google Cloud Account (free tier available)
- Access to Google Cloud Console
- Basic terminal/command-line knowledge

## Step-by-Step Setup

### 1. Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Click the project dropdown at the top
3. Click "NEW PROJECT"
4. Enter project name: "GPS Control Panel" (or similar)
5. Click "CREATE"
6. Wait for the project to be created (2-3 minutes)

### 2. Enable Vision API

1. In the Cloud Console, go to [Vision API page](https://console.cloud.google.com/apis/library/vision.googleapis.com)
2. Make sure your project is selected (top dropdown)
3. Click "ENABLE"
4. Wait for enabling to complete

### 3. Create Service Account

1. Go to [Service Accounts page](https://console.cloud.google.com/iam-admin/serviceaccounts)
2. Click "CREATE SERVICE ACCOUNT"
3. Enter service account details:
   - Service account name: "gps-control-panel"
   - Service account ID: auto-filled
   - Description: "For RC document OCR extraction"
4. Click "CREATE AND CONTINUE"
5. Grant roles:
   - Click "Select a role"
   - Search for "Vision"
   - Select "Cloud Vision API User"
   - Click "CONTINUE"
6. Click "DONE"

### 4. Create and Download JSON Key

1. Click on the service account you just created
2. Go to the "KEYS" tab
3. Click "ADD KEY" → "Create new key"
4. Choose "JSON"
5. Click "CREATE"
6. The JSON file will download automatically
7. **Keep this file secure** - it contains credentials

### 5. Configure Your Application

#### Option A: Using File Path (Recommended)

1. **Copy the JSON key file to your project:**
   ```
   Copy downloaded JSON file to: storage/google-vision-key.json
   ```

2. **Update `.env` file:**
   ```
   GOOGLE_APPLICATION_CREDENTIALS=storage/google-vision-key.json
   ```

3. **Set proper permissions (Linux/Mac):**
   ```bash
   chmod 600 storage/google-vision-key.json
   ```

#### Option B: Using Environment Variable (Alternative)

1. **Encode the JSON key:**
   ```bash
   base64 -i /path/to/key.json
   ```

2. **Add to `.env`:**
   ```
   GOOGLE_VISION_KEY_BASE64=<base64-encoded-key>
   ```

### 6. Verify Installation

1. **Run the test command:**
   ```bash
   php artisan tinker
   ```

2. **In Tinker, run:**
   ```php
   $service = new \App\Services\GoogleVisionRCService();
   echo $service->isConfigured() ? "Configured!" : "Not configured";
   ```

3. **Exit Tinker:**
   ```
   exit
   ```

### 7. Test the Feature

1. Navigate to your certificate page: `http://127.0.0.1:8000/user/device/599/certificate`
2. Click "Upload & Extract Details"
3. Select an RC document image
4. The system should extract and populate the form automatically

## Pricing & Costs

### Google Cloud Vision API Pricing

- **Free Tier:** 1,000 requests per month (unstructured documents)
- **Paid:** $1.50 per 1,000 requests (after free tier)

### Estimate for Your Use Case

- Small fleet: ~50 documents/month = **Free**
- Medium fleet: ~500 documents/month = **Free**
- Large fleet: ~2,000 documents/month = **$1.50**

## Fallback Behavior

The system intelligently falls back if Google Vision API is unavailable:

```
1. Try Google Cloud Vision API (if configured)
   ↓ (on success)
   Extract and populate form
   
   ↓ (on failure)
2. Try Tesseract-OCR (if installed)
   ↓ (on success)
   Extract and populate form
   
   ↓ (on failure)
3. Show error message and allow manual entry
```

## API Endpoints

### Upload RC Document
```
POST /user/device/{id}/certificate/upload-rc
```

**Response when Google Vision is active:**
```json
{
  "success": true,
  "message": "RC document processed successfully",
  "data": {
    "vehicle_registration_no": "RJ18GB8351",
    "holder_name": "John Doe",
    "chassis_no": "MAT479148G3D10399",
    ...
  }
}
```

### Check OCR Status
```
GET /user/device/{id}/certificate/rc-status
```

**Response:**
```json
{
  "google_vision_available": true,
  "tesseract_available": false,
  "ocr_available": true,
  "active_ocr": "Google Cloud Vision API",
  "message": "OCR feature is available and ready to use."
}
```

## Troubleshooting

### "Credentials not found" Error

**Problem:** `GOOGLE_APPLICATION_CREDENTIALS` path is incorrect

**Solution:**
1. Verify the JSON key file exists at `storage/google-vision-key.json`
2. Check `.env` file has correct path
3. Ensure path is relative to project root
4. Restart web server

### "Permission denied" Error

**Problem:** Service account doesn't have Vision API permissions

**Solution:**
1. Go to [IAM page](https://console.cloud.google.com/iam-admin/iam)
2. Click on service account row
3. Click "Edit principal"
4. Add "Cloud Vision API User" role
5. Click "Save"
6. Wait 5 minutes for changes to propagate

### "Vision API not enabled" Error

**Problem:** Vision API not enabled in the project

**Solution:**
1. Go to [Vision API page](https://console.cloud.google.com/apis/library/vision.googleapis.com)
2. Click "ENABLE"
3. Wait for it to complete
4. Restart web server

### Poor Extraction Results

**Problem:** Extracted text is incomplete or incorrect

**Solutions:**
1. **Improve image quality:**
   - Use well-lit, clear RC documents
   - Avoid tilted or curved documents
   - Use high-resolution images (300+ DPI)

2. **Try PDF format:**
   - Scanned PDFs often have better results
   - Ensure PDF is readable (not image-only)

3. **Manual entry fallback:**
   - User can always enter details manually
   - System shows helpful error messages

## Security Considerations

### Protect Your JSON Key

1. **Never commit to git:**
   ```bash
   # Add to .gitignore
   storage/google-vision-key.json
   ```

2. **Set proper file permissions:**
   ```bash
   chmod 600 storage/google-vision-key.json
   ```

3. **Use environment variables:**
   - Don't hardcode paths in code
   - Use `.env` file for sensitive data
   - Never share `.env` file

4. **Monitor API usage:**
   - Check Google Cloud Console regularly
   - Set up billing alerts
   - Review access logs

### Data Privacy

- Documents are processed by Google Cloud
- No documents are stored by Google
- RC data is stored securely in your database
- Only authenticated users can upload documents

## Integration with RC Extraction

The Google Vision API is automatically integrated into the RC extraction workflow:

```php
// Automatically tries Google Vision first
$service = new \App\Services\RCExtractionService();
$extractedData = $service->extractFromFile($filePath);
```

### Supported Document Types

✅ **Working Well:**
- Indian RC documents
- High-quality scans
- Color photographs
- Digital copies

⚠️ **May Need Improvement:**
- Very old/faded documents
- Low-resolution images
- Damaged documents
- Handwritten sections

## Advanced Configuration

### Use Both Google Vision and Tesseract

For maximum reliability, configure both:

1. Set up Google Vision API (primary)
2. Install Tesseract-OCR (fallback)
3. System automatically uses Google Vision first
4. Falls back to Tesseract if Google Vision fails

### Custom OCR Processing

To add custom processing logic:

```php
// In your custom service
$googleService = new \App\Services\GoogleVisionRCService();
$data = $googleService->extractFromFile($filePath);

// Post-process if needed
$data = $this->customProcessing($data);

// Map to form fields
$formData = $googleService->mapRCToFormFields($data);
```

## Monitoring & Logging

### View API Usage

1. Go to [Cloud Console](https://console.cloud.google.com)
2. Select your project
3. Go to "APIs & Services" → "Quotas"
4. Filter for "Vision API"
5. View usage statistics

### Enable Logging

Add to your error handler:

```php
\Log::info('RC extraction result', [
    'device_id' => $id,
    'method' => 'google_vision',
    'fields_extracted' => count($data),
    'timestamp' => now(),
]);
```

## Support & Further Help

### Official Documentation
- [Google Cloud Vision API Docs](https://cloud.google.com/vision/docs)
- [PHP Client Library](https://googleapis.dev/php/google-cloud-vision/latest/)

### Common Issues
- Billing not set up: [Google Cloud Billing](https://console.cloud.google.com/billing)
- Rate limits: Contact Google Cloud Support
- API quota exceeded: Upgrade to paid plan or wait for reset

## Migration from Tesseract to Google Vision

If you currently use Tesseract:

1. Set up Google Vision API (following steps above)
2. Keep Tesseract installed as fallback
3. System automatically uses Google Vision
4. No code changes needed

## Cost-Saving Tips

1. **Batch processing:** Process documents in batches during off-peak hours
2. **Image optimization:** Compress images to reduce processing time
3. **Free tier:** Use free tier for first 1,000 requests per month
4. **Cache results:** Store extracted data to avoid re-processing

## Uninstall/Disable

To switch back to Tesseract only:

1. Remove/comment out `GOOGLE_APPLICATION_CREDENTIALS` in `.env`
2. Delete JSON key file from storage
3. System will automatically use Tesseract

To disable OCR entirely:

1. Remove both Google Vision and Tesseract configuration
2. Users can enter RC details manually
3. System shows helpful manual entry form

---

**Setup Complete!** Your RC extraction feature is now ready with Google Cloud Vision API. For issues or questions, check the troubleshooting section or contact support.
