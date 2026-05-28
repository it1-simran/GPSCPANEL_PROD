# RC (Registration Certificate) Extraction Setup Guide

## Overview
The RC Extraction feature allows users to upload their vehicle Registration Certificate (RC) documents and automatically extract vehicle details using OCR technology. The extracted data is then auto-populated in the certificate form.

## Requirements

### OCR Engine - Tesseract
This feature uses Tesseract-OCR for text extraction from images and PDFs.

### Installation Instructions

#### Windows
1. Download the Tesseract installer from:
   https://github.com/UB-Mannheim/tesseract/wiki
   
2. Run the installer and note the installation path (default: `C:\Program Files\Tesseract-OCR`)

3. Add the following to your `.env` file:
   ```
   TESSERACT_OCR_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe
   ```

4. Verify installation by running:
   ```
   "C:\Program Files\Tesseract-OCR\tesseract.exe" --version
   ```

#### Linux (Ubuntu/Debian)
```bash
sudo apt-get install tesseract-ocr
sudo apt-get install libtesseract-dev
```

Add to `.env`:
```
TESSERACT_OCR_PATH=/usr/bin/tesseract
```

#### macOS
```bash
brew install tesseract
```

Add to `.env`:
```
TESSERACT_OCR_PATH=/usr/local/bin/tesseract
```

#### For PDF Support
If you want to support PDF uploads, install one of these tools:

**Windows:**
- ImageMagick: https://imagemagick.org/script/download.php
- Or Ghostscript: https://www.ghostscript.com/download/gsdnld.html

**Linux:**
```bash
sudo apt-get install imagemagick ghostscript
```

**macOS:**
```bash
brew install imagemagick ghostscript
```

## Features

### 1. RC Upload
- Supported formats: PDF, JPG, JPEG, PNG, BMP, GIF
- Maximum file size: 5MB
- Located at the top of the Certificate form

### 2. Automatic Extraction
The system extracts the following information from RC documents:
- Vehicle Registration Number
- Owner/Certificate Holder Name
- Registration Date
- Chassis Number
- Engine Number
- Vehicle Model
- Vehicle Class/Type
- Fuel Type
- Color
- Permit Details
- Validity Information

### 3. Form Population
- Extracted data automatically populates the certificate form
- Users can review and edit extracted data before submission
- Changes are saved with the certificate

### 4. Certificate Integration
- RC details are stored in the device configuration
- Vehicle Class and Fuel Type appear in the generated certificate

## API Endpoints

### Upload RC Document
```
POST /user/device/{id}/certificate/upload-rc
```

**Parameters:**
- `rc_file` (required): File upload - PDF, JPG, PNG, etc.

**Response:**
```json
{
  "success": true,
  "message": "RC document processed successfully",
  "data": {
    "vehicle_registration_no": "RJ18GB8351",
    "holder_name": "John Doe",
    "chassis_no": "MAT479148G3D10399",
    "engine_no": "B592153061D63515457",
    "vehicle_model": "LPK 2523 BS III",
    "color": "White",
    ...
  },
  "raw_data": { ... }
}
```

**Error Response:**
```json
{
  "error": "Error message describing what went wrong"
}
```

### Get RC Data
```
GET /user/device/{id}/certificate/rc-data
```

**Response:**
```json
{
  "data": {
    "vehicle_registration_no": "RJ18GB8351",
    "holder_name": "John Doe",
    ...
  }
}
```

## Troubleshooting

### Tesseract Not Found
**Error:** "OCR feature is not available. Please install Tesseract-OCR..."

**Solution:**
1. Install Tesseract-OCR following the installation instructions above
2. Set the correct path in `.env` file
3. Restart the application

### Poor OCR Results
**Symptoms:** Text not extracted correctly, missing or incorrect data

**Solutions:**
1. Use clear, high-quality RC document images
2. Ensure the document is well-lit and not tilted
3. Avoid shadows and glare on the document
4. Use PDF format if available (more accurate than photos)

### PDF Upload Not Working
**Error:** "Failed to convert PDF to image"

**Solution:**
1. Install ImageMagick or Ghostscript
2. Verify installation and permissions
3. Ensure the PDF file is not corrupted

### Large File Uploads Failing
**Error:** "413 Payload Too Large"

**Solution:**
1. Increase `upload_max_filesize` in `php.ini`
2. Increase `post_max_size` in `php.ini`
3. Restart your web server

## Data Validation

The system validates extracted RC documents to ensure they contain essential information:
- Vehicle Registration Number (required)
- Chassis Number (required)
- Engine Number (required)

If these fields are missing, the upload will fail with an appropriate error message.

## Field Mapping

The extracted RC fields are mapped to form fields as follows:

| RC Field | Form Field |
|----------|-----------|
| vehicle_registration_no | vehicle_registration_no |
| holder_name | holder_name |
| registration_date | fitment_date |
| chassis_no | chassis_no |
| engine_no | engine_no |
| vehicle_model | vehicle_model |
| color | color |
| vehicle_class | vehicle_class |
| fuel_type | fuel_type |

## Storage

RC data is stored in the device configuration JSON with the following structure:

```json
{
  "rc_details": {
    "vehicle_registration_no": "RJ18GB8351",
    "holder_name": "John Doe",
    "chassis_no": "MAT479148G3D10399",
    "engine_no": "B592153061D63515457",
    "vehicle_model": "LPK 2523 BS III",
    "color": "White",
    "file_path": "rc_uploads/filename.pdf",
    "uploaded_at": "2026-05-28T10:30:00"
  },
  "certificate_details": { ... }
}
```

## Security Considerations

1. **File Upload Validation:**
   - Only allowed file formats are accepted
   - File size is limited to 5MB
   - Files are scanned before processing

2. **Data Privacy:**
   - RC files are stored securely
   - Access is restricted to device owner only
   - File paths are not exposed in API responses

3. **User Authorization:**
   - Only authenticated users can upload RC documents
   - Users can only access their own devices
   - Admin users can view all devices

## Future Enhancements

- [ ] Support for multiple RC languages
- [ ] Batch RC document processing
- [ ] RC document verification against government records
- [ ] Machine learning model for improved accuracy
- [ ] Support for other document types (permit, insurance, etc.)
- [ ] Integration with digital RC APIs (e-services)

## Support

For issues or questions about the RC Extraction feature:
1. Check the Troubleshooting section above
2. Review application logs for detailed error messages
3. Verify Tesseract-OCR installation and configuration
4. Contact system administrator for server-level issues
