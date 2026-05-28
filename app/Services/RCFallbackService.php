<?php

namespace App\Services;

class RCFallbackService
{
    /**
     * Provide a manual entry form for RC details when OCR is not available
     */
    public function getManualEntryTemplate()
    {
        return [
            'fields' => [
                'vehicle_registration_no' => [
                    'label' => 'Vehicle Registration Number',
                    'placeholder' => 'e.g., RJ18GB8351',
                    'required' => true,
                ],
                'holder_name' => [
                    'label' => 'Certificate Holder Name',
                    'placeholder' => 'Name of the registered owner',
                    'required' => true,
                ],
                'registration_date' => [
                    'label' => 'Registration Date',
                    'type' => 'date',
                    'required' => false,
                ],
                'chassis_no' => [
                    'label' => 'Chassis Number',
                    'placeholder' => 'From RC document',
                    'required' => true,
                ],
                'engine_no' => [
                    'label' => 'Engine Number',
                    'placeholder' => 'From RC document',
                    'required' => true,
                ],
                'vehicle_model' => [
                    'label' => 'Vehicle Model',
                    'placeholder' => 'e.g., LPK 2523 BS III',
                    'required' => false,
                ],
                'vehicle_class' => [
                    'label' => 'Vehicle Class',
                    'placeholder' => 'e.g., Truck, Bus, etc.',
                    'required' => false,
                ],
                'fuel_type' => [
                    'label' => 'Fuel Type',
                    'placeholder' => 'Petrol/Diesel/CNG/Electric',
                    'required' => false,
                ],
                'color' => [
                    'label' => 'Vehicle Color',
                    'placeholder' => 'e.g., White, Black, etc.',
                    'required' => false,
                ],
            ],
            'message' => 'OCR feature is not configured. Please enter the vehicle details manually from your RC document.',
        ];
    }

    /**
     * Get installation instructions for OCR solutions
     */
    public function getInstallationInstructions()
    {
        return [
            'options' => [
                'google_vision' => $this->getGoogleVisionInstructions(),
                'tesseract' => $this->getTesseractInstructions(),
            ],
        ];
    }

    protected function getGoogleVisionInstructions()
    {
        return [
            'name' => 'Google Cloud Vision API (Recommended)',
            'description' => 'Cloud-based OCR - more accurate, no local installation needed',
            'steps' => [
                '1. Create a Google Cloud Project at: https://console.cloud.google.com',
                '2. Enable Vision API: https://console.cloud.google.com/apis/library/vision.googleapis.com',
                '3. Create a Service Account at: https://console.cloud.google.com/iam-admin/serviceaccounts',
                '4. Download JSON key file from service account',
                '5. Copy the JSON file to your project (e.g., storage/google-vision-key.json)',
                '6. Add to .env file:',
                '   GOOGLE_APPLICATION_CREDENTIALS=storage/google-vision-key.json',
                '7. Restart your web server',
            ],
            'benefits' => [
                'More accurate OCR results',
                'No local installation required',
                'Works on any server',
                'Handles scanned and digital documents',
            ],
        ];
    }

    protected function getTesseractInstructions()
    {
        $osType = strtoupper(substr(PHP_OS, 0, 3));

        if ($osType === 'WIN') {
            return array_merge(['name' => 'Tesseract-OCR (Windows)'], $this->getWindowsInstructions());
        } elseif ($osType === 'LIN') {
            return array_merge(['name' => 'Tesseract-OCR (Linux)'], $this->getLinuxInstructions());
        } elseif ($osType === 'DAR') {
            return array_merge(['name' => 'Tesseract-OCR (macOS)'], $this->getMacInstructions());
        }

        return array_merge(['name' => 'Tesseract-OCR'], $this->getGenericInstructions());
    }

    protected function getWindowsInstructions()
    {
        return [
            'os' => 'Windows',
            'steps' => [
                '1. Download Tesseract installer from: https://github.com/UB-Mannheim/tesseract/wiki',
                '2. Run the installer and follow the setup wizard',
                '3. Note the installation path (usually C:\\Program Files\\Tesseract-OCR)',
                '4. Edit your .env file and add: TESSERACT_OCR_PATH=C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
                '5. Restart your web server',
            ],
        ];
    }

    protected function getLinuxInstructions()
    {
        return [
            'os' => 'Linux',
            'steps' => [
                'Run: sudo apt-get update',
                'Run: sudo apt-get install tesseract-ocr',
                'Run: sudo apt-get install libtesseract-dev',
                'Verify with: tesseract --version',
                'Edit .env and add: TESSERACT_OCR_PATH=/usr/bin/tesseract',
            ],
        ];
    }

    protected function getMacInstructions()
    {
        return [
            'os' => 'macOS',
            'steps' => [
                'Run: brew install tesseract',
                'Verify with: tesseract --version',
                'Edit .env and add: TESSERACT_OCR_PATH=/usr/local/bin/tesseract',
            ],
        ];
    }

    protected function getGenericInstructions()
    {
        return [
            'os' => 'Unknown',
            'steps' => [
                'Visit: https://github.com/UB-Mannheim/tesseract/wiki',
                'Download and install Tesseract-OCR for your operating system',
                'Note the installation path',
                'Add TESSERACT_OCR_PATH to your .env file',
                'Restart your web server',
            ],
        ];
    }

    /**
     * Check if Tesseract is available
     */
    public function isTesseractAvailable()
    {
        $tesseractPath = env('TESSERACT_OCR_PATH', 'tesseract');
        $output = [];
        $returnCode = 0;

        @exec("$tesseractPath --version 2>&1", $output, $returnCode);

        return $returnCode === 0;
    }
}
