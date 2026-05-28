<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use thiagoalessio\TesseractOCR\TesseractOCR;
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

            // Get file extension
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Handle PDF files
            if ($ext === 'pdf') {
                return $this->extractFromPDF($filePath);
            }

            // Handle image files
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
                return $this->extractFromImage($filePath);
            }

            throw new Exception('Unsupported file format. Please upload a PDF or image file.');
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

            if (empty($extractedText)) {
                throw new Exception('No text could be extracted from the image. Please ensure the RC document image is clear and readable.');
            }

            return $this->parseRCData($extractedText);
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

        // Clean and normalize text
        $text = strtoupper($text);
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        // Extract registration number
        $data['vehicle_registration_no'] = $this->extractField($text, ['REG.*?NO', 'REGISTRATION.*?NO', 'REG.*?MARK'], 10);

        // Extract owner/holder name
        $data['holder_name'] = $this->extractField($text, ['OWNER.*?NAME', 'REGISTERED.*?TO', 'NAME.*?OF.*?OWNER'], 50);

        // Extract registration date
        $data['registration_date'] = $this->extractField($text, ['REGISTRATION.*?DATE', 'DATE.*?OF.*?REGISTRATION'], 10);

        // Extract chassis number
        $data['chassis_no'] = $this->extractField($text, ['CHASSIS.*?NO', 'CHASSIS.*?NUMBER', 'CHASIS'], 20);

        // Extract engine number
        $data['engine_no'] = $this->extractField($text, ['ENGINE.*?NO', 'ENGINE.*?NUMBER'], 20);

        // Extract vehicle make/model
        $data['vehicle_model'] = $this->extractField($text, ['MAKE.*?MODEL', 'VEHICLE.*?MODEL', 'MODEL'], 50);

        // Extract vehicle category/class
        $data['vehicle_class'] = $this->extractField($text, ['CATEGORY.*?OF.*?VEHICLE', 'VEHICLE.*?CLASS', 'CLASS.*?TYPE'], 30);

        // Extract fuel type
        $data['fuel_type'] = $this->extractField($text, ['FUEL.*?TYPE', 'TYPE.*?OF.*?FUEL', 'FUEL'], 20);

        // Extract color
        $data['color'] = $this->extractField($text, ['COLOUR', 'COLOR'], 20);

        // Extract permit details if available
        $data['permit_details'] = $this->extractField($text, ['PERMIT', 'PERMIT.*?VALID'], 100);

        // Extract validity information
        $data['validity'] = $this->extractField($text, ['VALID.*?UPTO', 'VALIDITY', 'VALID.*?TILL'], 20);

        // Clean up extracted data
        $data = array_map(function ($value) {
            return !empty($value) ? trim($value) : null;
        }, $data);

        return array_filter($data, function ($value) {
            return !is_null($value);
        });
    }

    protected function extractField($text, $patterns, $maxLength = 100)
    {
        foreach ($patterns as $pattern) {
            // Try to find pattern and extract value
            if (preg_match("/$pattern\s*[:\-]?\s*([^\n]+)/i", $text, $matches)) {
                $value = trim($matches[1]);
                $value = preg_replace('/\s+/', ' ', $value);
                return substr($value, 0, $maxLength);
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
