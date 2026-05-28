<?php

namespace App\Services;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class GoogleVisionRCService
{
    protected $visionClient;
    protected $imageManager;
    protected $credentialsPath;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->credentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS');

        if (!$this->credentialsPath || !file_exists($this->credentialsPath)) {
            throw new Exception(
                'Google Cloud Vision credentials not configured. ' .
                'Please set GOOGLE_APPLICATION_CREDENTIALS in your .env file.'
            );
        }
    }

    protected function getClient()
    {
        if (!$this->visionClient) {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->credentialsPath);
            $this->visionClient = new ImageAnnotatorClient([
                'credentials' => $this->credentialsPath,
            ]);
        }
        return $this->visionClient;
    }

    public function extractFromFile($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception('File not found: ' . $filePath);
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                return $this->extractFromPDF($filePath);
            }

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
            $imageContent = file_get_contents($imagePath);

            $image = new Image();
            $image->setContent($imageContent);

            $client = $this->getClient();
            $response = $client->textDetection($image);
            $texts = $response->getTextAnnotations();

            if (empty($texts)) {
                throw new Exception('No text could be extracted from the image. Please ensure the RC document is clear and readable.');
            }

            $extractedText = $texts[0]->getDescription();

            return $this->parseRCData($extractedText);
        } catch (Exception $e) {
            throw new Exception('Error processing image with Google Vision: ' . $e->getMessage());
        }
    }

    protected function extractFromPDF($pdfPath)
    {
        try {
            // Convert PDF to image first
            $tempImage = storage_path('app/temp_rc_' . time() . '.jpg');

            // Try using ImageMagick to convert PDF to image
            $command = "convert -density 300 \"{$pdfPath}[0]\" -quality 90 \"{$tempImage}\" 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempImage)) {
                $result = $this->extractFromImage($tempImage);
                unlink($tempImage);
                return $result;
            }

            throw new Exception('Failed to convert PDF to image. Please ensure ImageMagick is installed.');
        } catch (Exception $e) {
            throw new Exception('Error processing PDF: ' . $e->getMessage());
        }
    }

    protected function parseRCData($text)
    {
        $data = [];

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

    public static function isConfigured()
    {
        $credentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        return !empty($credentialsPath) && file_exists($credentialsPath);
    }
}
