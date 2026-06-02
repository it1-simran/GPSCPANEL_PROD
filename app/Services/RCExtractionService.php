<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Exceptions\ImageQualityException;
use Illuminate\Support\Str;
use Exception;

class RCExtractionService
{
    protected $imageManager;
    protected $tesseractPath;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->tesseractPath = env('TESSERACT_OCR_PATH', 'tesseract');
        $this->checkTesseractAvailability();
    }

    protected function checkTesseractAvailability()
    {
        $output = [];
        $returnCode = 0;
        @exec("$this->tesseractPath --version 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            // Tesseract not available, but we'll handle this gracefully
            // by throwing error when OCR is attempted
        }
    }

    protected function isTesseractAvailable()
    {
        $output = [];
        $returnCode = 0;
        @exec("$this->tesseractPath --version 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }

    public function extractFromFile($filePath)
    {
        try {
            // Validate file exists
            if (!file_exists($filePath)) {
                throw new Exception('File not found: ' . $filePath);
            }

            // Try Google Vision API first if configured
            if (\App\Services\GoogleVisionRCService::isConfigured()) {
                try {
                    $googleService = new \App\Services\GoogleVisionRCService();
                    return $googleService->extractFromFile($filePath);
                } catch (ImageQualityException $e) {
                    // Poor image quality is a definitive result — do NOT fall back
                    // to Tesseract; surface the quality error to the user.
                    throw $e;
                } catch (Exception $e) {
                    // Fall back to Tesseract if Google Vision fails
                    if ($this->isTesseractAvailable()) {
                        // Continue with Tesseract
                    } else {
                        throw $e;
                    }
                }
            }

            // Fall back to Tesseract if Google Vision not configured
            if ($this->isTesseractAvailable()) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                if ($ext === 'pdf') {
                    return $this->extractFromPDF($filePath);
                }

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
                    return $this->extractFromImage($filePath);
                }
            }

            // No OCR available
            throw new Exception(
                'OCR feature is not available. Please configure Google Cloud Vision API or install Tesseract-OCR. ' .
                'See documentation for setup instructions.'
            );
        } catch (ImageQualityException $e) {
            // Bubble up unwrapped so the controller can show the exact message.
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Error extracting RC data: ' . $e->getMessage());
        }
    }

    protected function extractFromImage($imagePath)
    {
        try {
            if (!$this->isTesseractAvailable()) {
                throw new Exception(
                    'OCR feature is not available. Please install Tesseract-OCR on your server. ' .
                    'Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki, ' .
                    'set TESSERACT_OCR_PATH in .env. ' .
                    'For now, please enter RC details manually in the form.'
                );
            }

            // Pre-process image for better OCR accuracy
            $image = $this->imageManager->read($imagePath);

            // Convert to grayscale
            $image = $image->greyscale();

            // Increase contrast
            $image = $image->contrast(20);

            // Save processed image temporarily
            $tempPath = storage_path('app/temp_rc_' . time() . '.jpg');
            $image->tojpeg(quality: 90)->save($tempPath);

            // Extract text using Tesseract
            $ocr = new TesseractOCR($tempPath);
            $ocr->executable($this->tesseractPath);
            $extractedText = $ocr->run();

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (!OcrQualityHelper::isReadable($extractedText)) {
                throw new ImageQualityException(OcrQualityHelper::QUALITY_ERROR);
            }

            return $this->parseRCData($extractedText);
        } catch (ImageQualityException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Error processing image: ' . $e->getMessage());
        }
    }

    protected function extractFromPDF($pdfPath)
    {
        // For PDF handling, we'll need to convert PDF to image first
        // This requires additional tools like ImageMagick or Ghostscript
        // For now, we'll extract text from PDF directly if possible
        try {
            // Try using pdftotext if available
            $tempText = storage_path('app/temp_rc_' . time() . '.txt');
            $command = "pdftotext \"$pdfPath\" \"$tempText\" 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempText)) {
                $extractedText = file_get_contents($tempText);
                unlink($tempText);
                return $this->parseRCData($extractedText);
            }

            // Fallback: convert PDF to image and use OCR
            return $this->convertPDFToImageAndExtract($pdfPath);
        } catch (Exception $e) {
            throw new Exception('Error processing PDF: ' . $e->getMessage());
        }
    }

    protected function convertPDFToImageAndExtract($pdfPath)
    {
        try {
            // Use ImageMagick to convert PDF to image
            $tempImage = storage_path('app/temp_rc_' . time() . '.jpg');
            $command = "convert -density 300 \"$pdfPath[0]\" -quality 90 \"$tempImage\" 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempImage)) {
                $result = $this->extractFromImage($tempImage);
                unlink($tempImage);
                return $result;
            }

            throw new Exception('Failed to convert PDF to image');
        } catch (Exception $e) {
            throw new Exception('Error converting PDF: ' . $e->getMessage());
        }
    }

    protected function parseRCData($text)
    {
        $data = [];
        $text = strtoupper($text);

        // Indian RC document labels: REG NO, CH NO, E SNO, MODEL, VHE CL, FUEL, COLOR, NAME, REGD DT, MFR

        // Registration Number
        $data['vehicle_registration_no'] = $this->extractField($text, [
            'REG\s*NO\b',
            'REGN?\.?\s*NO\b',
            'REGISTRATION\s*N(?:O|UMBER)',
            'REG\s+MARK',
        ], 20);

        // Chassis Number (CH NO)
        $data['chassis_no'] = $this->extractField($text, [
            'CH\s*NO\b',
            'CHASS?IS\s*N(?:O|UMBER)',
            'CHS\s*NO\b',
        ], 25);

        // Engine Number (E SNO)
        $data['engine_no'] = $this->extractField($text, [
            'E\s*SNO\b',
            'E\s*S\s*NO\b',
            'ENGINE\s*N(?:O|UMBER)',
            'ENG\s*NO\b',
        ], 25);

        // Vehicle Model (MODEL)
        $data['vehicle_model'] = $this->extractField($text, [
            '\bMODEL\b(?!\s*(?:NAME|NO))',
            'VEHICLE\s+MODEL',
            'MAKE(?:R)?(?:\'S)?\s*MODEL',
        ], 60);

        // Vehicle Class (VHE CL)
        $data['vehicle_class'] = $this->extractField($text, [
            'VHE\s*CL\b',
            'V\.?\s*H\.?\s*CL\b',
            'VEHICLE\s+CLASS',
            'CLASS\s+OF\s+VEHICLE',
            'CATEGORY\s+OF\s+VEH',
        ], 35);

        // Fuel Type (FUEL)
        $data['fuel_type'] = $this->extractField($text, [
            '\bFUEL\b(?!\s*(?:USED|TYPE))',
            'FUEL\s+(?:USED|TYPE)',
            'TYPE\s+OF\s+FUEL',
        ], 20);

        // Color
        $data['color'] = $this->extractField($text, [
            'COLO(?:U)?R\b',
        ], 20);

        // Owner Name (NAME)
        $data['holder_name'] = $this->extractField($text, [
            'OWNER(?:\'S)?\s*NAME',
            'REGISTERED\s+OWNER',
            'NAME\s+OF\s+OWNER',
            '\bNAME\b(?!\s+OF)',
        ], 60);

        // Owner Address (ADDRESS) - Extract from RC document
        $ownerAddress = null;

        // Try multiple patterns to find the address
        $patterns = [
            'ADDRESS\s+OF\s+OWNER[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
            'OWNER(?:\'S)?\s*ADDRESS[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
            'REGISTERED\s+ADDRESS[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
            'ADDRESS\s+OF\s+APPLICANT[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
            'ADDR\.?[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
        ];

        foreach ($patterns as $pattern) {
            $regex = '~' . $pattern . '~i';
            if (preg_match($regex, $text, $matches)) {
                $value = trim(preg_replace('~\s+~', ' ', $matches[1] ?? $matches[0]));
                // Clean up the value - remove leading labels if any
                $value = preg_replace('~^(?:ADDRESS\s+OF\s+OWNER|OWNER.*?ADDRESS|ADDRESS)[:\s-\.]*~i', '', $value);
                $value = trim(preg_replace('~\s+~', ' ', $value));

                if (!empty($value) && strlen($value) > 5) {
                    $ownerAddress = substr($value, 0, 300);
                    break;
                }
            }
        }

        $data['owner_address'] = $ownerAddress;

        // Log address extraction for debugging
        if (!$ownerAddress) {
            \Log::info('RC Address Extraction: No address found. Text preview: ' . substr($text, 0, 500));
        } else {
            \Log::info('RC Address Extraction: Found address: ' . $ownerAddress);
        }

        // Registration Date (REGD DT)
        $data['registration_date'] = $this->extractField($text, [
            'REGD\s*DT\b',
            'REGD\.?\s*DATE',
            'REG\s*DT\b',
            'DATE\s+OF\s+REG',
            'REG(?:ISTRATION|N)?\s+DATE',
        ], 15);

        // Manufacturer (MFR)
        $data['manufacturer'] = $this->extractField($text, [
            '\bMFR\b',
            'MANUFACTURER',
        ], 50);

        // Validity (REGD UPTO)
        $data['validity'] = $this->extractField($text, [
            'REGD\s*UPTO\b',
            'VALID\s+(?:UPTO|TILL|THROUGH)',
            'VALIDITY',
        ], 20);

        // Clean up
        return array_filter(array_map('trim', $data));
    }

    protected function extractField($text, $patterns, $maxLength = 100)
    {
        foreach ($patterns as $pattern) {
            // Use ~ as delimiter — safe, never appears in RC document text
            $regex = '~' . $pattern . '[\s:\-/\.]*([^\n]{1,' . $maxLength . '})~i';
            try {
                if (preg_match($regex, $text, $matches)) {
                    $value = trim(preg_replace('~\s+~', ' ', $matches[1]));
                    if (!empty($value) && strlen($value) > 1) {
                        return substr($value, 0, $maxLength);
                    }
                }
            } catch (\Throwable $e) {
                \Log::debug('RC extractField bad pattern: ' . $pattern . ' — ' . $e->getMessage());
            }
        }
        return null;
    }

    public function validateRCDocument($data)
    {
        $required = ['vehicle_registration_no', 'chassis_no', 'engine_no'];
        $missing = [];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Invalid RC document. Missing: ' . implode(', ', $missing));
        }

        return true;
    }

    public function mapRCToFormFields($rcData)
    {
        $mapping = [
            'vehicle_registration_no' => 'vehicle_registration_no',
            'holder_name' => 'holder_name',
            'owner_address' => 'owner_address',
            'registration_date' => 'fitment_date',
            'chassis_no' => 'chassis_no',
            'engine_no' => 'engine_no',
            'vehicle_model' => 'vehicle_model',
            'color' => 'color',
            'vehicle_class' => 'vehicle_class',
            'fuel_type' => 'fuel_type',
        ];

        $formData = [];
        foreach ($mapping as $rcField => $formField) {
            if (isset($rcData[$rcField])) {
                $formData[$formField] = $rcData[$rcField];
            }
        }

        return $formData;
    }
}
