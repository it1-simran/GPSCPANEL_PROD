<?php

namespace App\Services;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Exceptions\ImageQualityException;
use Exception;

class GoogleVisionRCService
{
    protected $imageManager;
    protected $credentialsPath;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->credentialsPath = self::resolveCredentialsPath();

        if (!$this->credentialsPath || !file_exists($this->credentialsPath)) {
            throw new Exception(
                'Google Cloud Vision credentials not configured. ' .
                'Please set GOOGLE_APPLICATION_CREDENTIALS in your .env file. ' .
                'Resolved path: ' . ($this->credentialsPath ?? 'not set')
            );
        }
    }

    /**
     * Resolve credentials path — supports both relative (to project root) and absolute paths
     */
    protected static function resolveCredentialsPath(): ?string
    {
        $path = env('GOOGLE_APPLICATION_CREDENTIALS');
        if (empty($path)) return null;

        // Already absolute path (Unix: starts with /, Windows: starts with drive letter like C:)
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            return $path;
        }

        // Relative path — resolve from project base directory
        return base_path($path);
    }

    protected function getClient(): ImageAnnotatorClient
    {
        // Set credentials path so Google SDK can find it
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->credentialsPath);

        return new ImageAnnotatorClient([
            'credentials' => $this->credentialsPath,
        ]);
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
        } catch (ImageQualityException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Error extracting RC data: ' . $e->getMessage());
        }
    }

    protected function extractFromImage($imagePath)
    {
        $client = null;
        try {
            $imageContent = file_get_contents($imagePath);
            if ($imageContent === false) {
                throw new Exception('Could not read image file.');
            }

            // Build the image object
            $image = new Image();
            $image->setContent($imageContent);

            // Build the feature request (TEXT_DETECTION)
            $feature = new Feature();
            $feature->setType(Type::DOCUMENT_TEXT_DETECTION); // Better for documents like RC

            // Build the per-image annotation request
            $annotateRequest = new AnnotateImageRequest();
            $annotateRequest->setImage($image);
            $annotateRequest->setFeatures([$feature]);

            // Build the batch request
            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$annotateRequest]);

            // Call the API
            $client = $this->getClient();
            $batchResponse = $client->batchAnnotateImages($batchRequest);

            // Get first response
            $responses = $batchResponse->getResponses();
            if (empty($responses)) {
                throw new Exception('No response from Google Vision API.');
            }

            $response = $responses[0];

            // Check for API-level errors
            $error = $response->getError();
            if ($error && $error->getCode() !== 0) {
                throw new Exception('Google Vision API error: ' . $error->getMessage());
            }

            // Extract full text from DOCUMENT_TEXT_DETECTION
            $fullTextAnnotation = $response->getFullTextAnnotation();
            if ($fullTextAnnotation) {
                $extractedText = $fullTextAnnotation->getText();
            } else {
                // Fallback to TEXT_DETECTION annotations
                $texts = $response->getTextAnnotations();
                if (empty($texts)) {
                    throw new Exception('No text could be extracted from the image. Please ensure the RC document is clear and readable.');
                }
                $extractedText = $texts[0]->getDescription();
            }

            // ── Image quality gate ───────────────────────────────────────
            // Reject blurry / cropped / tilted / unreadable images before any
            // field extraction is attempted.
            $confidence = $this->averageConfidence($fullTextAnnotation);
            if (!OcrQualityHelper::isReadable($extractedText, $confidence)) {
                throw new ImageQualityException(OcrQualityHelper::QUALITY_ERROR);
            }

            // Log extracted OCR text for debugging
            \Log::info('Google Vision OCR extracted text', [
                'text' => $extractedText,
                'length' => strlen($extractedText),
                'confidence' => $confidence,
            ]);

            return $this->parseRCData($extractedText);

        } catch (ImageQualityException $e) {
            // Bubble up unwrapped so the controller can show the exact message.
            throw $e;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // Parse common Google API errors into friendly messages
            if (str_contains($msg, 'BILLING_DISABLED') || str_contains($msg, 'billing')) {
                throw new Exception(
                    'Google Cloud Vision billing is not enabled. ' .
                    'Please enable billing on your Google Cloud project at: ' .
                    'https://console.cloud.google.com/billing — Google offers $300 free credits for new accounts.'
                );
            }
            if (str_contains($msg, 'PERMISSION_DENIED') || str_contains($msg, 'API_KEY_INVALID')) {
                throw new Exception('Google Vision API access denied. Please check your credentials JSON key and ensure the Vision API is enabled in your Google Cloud project.');
            }
            if (str_contains($msg, 'QUOTA_EXCEEDED')) {
                throw new Exception('Google Vision API quota exceeded. Please check your usage limits in Google Cloud Console.');
            }
            throw new Exception('Error processing image with Google Vision: ' . $msg);
        } finally {
            if ($client) {
                $client->close();
            }
        }
    }

    protected function extractFromPDF($pdfPath)
    {
        try {
            // Convert PDF first page to image using ImageMagick
            $tempImage = storage_path('app/temp_rc_' . time() . '.jpg');
            $command = "convert -density 300 \"{$pdfPath}[0]\" -quality 90 \"{$tempImage}\" 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempImage)) {
                $result = $this->extractFromImage($tempImage);
                @unlink($tempImage);
                return $result;
            }

            throw new Exception('Failed to convert PDF to image. Please ensure ImageMagick is installed, or upload a JPG/PNG instead.');
        } catch (ImageQualityException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Error processing PDF: ' . $e->getMessage());
        }
    }

    /**
     * Compute the average page-level confidence (0-1) from a Vision
     * full-text annotation. Returns null when the provider reports no
     * usable confidence values.
     */
    protected function averageConfidence($fullTextAnnotation): ?float
    {
        if (!$fullTextAnnotation) {
            return null;
        }

        try {
            $sum = 0.0;
            $count = 0;
            foreach ($fullTextAnnotation->getPages() as $page) {
                $c = (float) $page->getConfidence();
                if ($c > 0) {
                    $sum += $c;
                    $count++;
                }
            }
            return $count > 0 ? ($sum / $count) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseRCData($text)
    {
        $data = [];
        $upperText = strtoupper($text);

        // ══════════════════════════════════════════════════════════════════
        // Try smart line-based extraction first (handles tabular layout
        // where labels and values are on separate lines or columns)
        // ══════════════════════════════════════════════════════════════════
        $smartData = $this->smartLineExtract($upperText);
        if (!empty($smartData)) {
            $data = $smartData;
        }

        // ══════════════════════════════════════════════════════════════════
        // Indian RC Document Label Mapping (from real RC documents):
        //   REG NO    → Vehicle Registration Number
        //   CH NO     → Chassis Number
        //   E SNO     → Engine Serial Number
        //   REGD DT   → Registration Date
        //   REGD UPTO → Valid Upto
        //   NAME      → Owner Name
        //   MODEL     → Vehicle Model
        //   VHE CL    → Vehicle Class
        //   FUEL      → Fuel Type
        //   COLOR     → Color
        //   MFR       → Manufacturer
        // Fallback: regex-based same-line extraction
        // ══════════════════════════════════════════════════════════════════

        // ── Registration Number ──────────────────────────────────────────
        if (empty($data['vehicle_registration_no'])) {
            $data['vehicle_registration_no'] = $this->extractField($upperText, [
                'REG\s*NO\b',                              // "REG NO" (Indian RC abbrev)
                'REGN?\.?\s*NO\b',                         // "REGN. NO", "REG. NO"
                'REGISTRATION\s*N(?:O|UMBER)',             // "REGISTRATION NO/NUMBER"
                'VEHICLE\s+REG(?:ISTRATION)?\s*NO',
                'REG\s+MARK',
            ], 20);
        }

        // Fallback: detect Indian plate pattern directly (e.g. PB10EM1318)
        if (empty($data['vehicle_registration_no'])) {
            if (preg_match('~\b([A-Z]{2}\s*\d{1,2}\s*[A-Z]{1,3}\s*\d{1,4})\b~', $upperText, $m)) {
                $data['vehicle_registration_no'] = preg_replace('~\s+~', '', $m[1]);
            }
        }

        // ── Chassis Number (CH NO) ───────────────────────────────────────
        if (empty($data['chassis_no'])) {
            $data['chassis_no'] = $this->extractField($upperText, [
                'CH\s*NO\b', 'CHASS?IS\s*N(?:O|UMBER)', 'CHASS?IS\s*[:\-]', 'CHS\s*NO\b',
            ], 25);
        }

        // ── Engine Number (E SNO = Engine Serial Number) ─────────────────
        if (empty($data['engine_no'])) {
            $data['engine_no'] = $this->extractField($upperText, [
                'E\s*SNO\b', 'E\s*S\s*NO\b', 'E\.?\s*SNO\b',
                'ENGINE\s*N(?:O|UMBER)', 'ENGINE\s*[:\-]', 'ENG\s*NO\b',
            ], 25);
        }

        // ── Vehicle Model (MODEL) ────────────────────────────────────────
        if (empty($data['vehicle_model'])) {
            $data['vehicle_model'] = $this->extractField($upperText, [
                '\bMODEL\b(?!\s*(?:NAME|NO|NUMBER))',
                'VEHICLE\s+MODEL',
                'MAKE(?:R)?(?:\'S)?\s*MODEL',
                'MAKE\s*(?:AND|\&)\s*MODEL',
            ], 60);
        }

        // ── Vehicle Class (VHE CL) ───────────────────────────────────────
        if (empty($data['vehicle_class'])) {
            $data['vehicle_class'] = $this->extractField($upperText, [
                'VHE\s*CL\b', 'V\.?\s*H\.?\s*CL\b', 'VH\s*CL\b',
                'VEHICLE\s+CLASS', 'CLASS\s+OF\s+VEHICLE',
                'TYPE\s+OF\s+VEHICLE', 'CATEGORY\s+OF\s+VEH',
            ], 35);
        }

        // ── Fuel Type (FUEL) ─────────────────────────────────────────────
        if (empty($data['fuel_type'])) {
            $data['fuel_type'] = $this->extractField($upperText, [
                '\bFUEL\b(?!\s*(?:USED|TYPE))', 'FUEL\s+(?:USED|TYPE)', 'TYPE\s+OF\s+FUEL',
            ], 20);
        }

        // ── Color ────────────────────────────────────────────────────────
        if (empty($data['color'])) {
            $data['color'] = $this->extractField($upperText, ['COLO(?:U)?R\b'], 20);
        }

        // ── Owner / Holder Name (NAME) ───────────────────────────────────
        if (empty($data['holder_name'])) {
            $data['holder_name'] = $this->extractField($upperText, [
                'OWNER(?:\'S)?\s*NAME', 'REGISTERED\s+OWNER',
                'REGISTERED\s+IN\s+THE\s+NAME\s+OF', 'PRESENT\s+OWNER',
                '\bNAME\b(?!\s+OF)',
            ], 60);
        }

        // ── Registration Date (REGD DT) ──────────────────────────────────
        if (empty($data['registration_date'])) {
            $data['registration_date'] = $this->extractField($upperText, [
                'REGD\s*DT\b', 'REGD\.?\s*DATE', 'REG\s*DT\b',
                'DATE\s+OF\s+REG(?:ISTRATION)?', 'REG(?:ISTRATION|N)?\s+DATE',
            ], 15);
        }

        // ── Manufacturer / Make (MFR) ────────────────────────────────────
        if (empty($data['manufacturer'])) {
            $data['manufacturer'] = $this->extractField($upperText, [
                '\bMFR\b', 'MANUFACTURER', '\bMAKE\b(?!\s*(?:R|MODEL))',
            ], 50);
        }

        // ── Validity (REGD UPTO) ─────────────────────────────────────────
        if (empty($data['validity'])) {
            $data['validity'] = $this->extractField($upperText, [
                'REGD\s*UPTO\b', 'VALID\s+(?:UPTO|TILL|THROUGH|UP\s+TO)',
                'VALIDITY', 'VALID\s+DATE',
            ], 20);
        }

        // ── Owner Address ────────────────────────────────────────────────
        if (empty($data['owner_address'])) {
            $ownerAddress = null;

            // Try multiple patterns to find the address
            // Patterns handle both inline and multi-line address formats
            $patterns = [
                'ADDRESS\s+OF\s+OWNER[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
                'OWNER(?:\'S)?\s*ADDRESS[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
                'REGISTERED\s+ADDRESS[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
                'ADDRESS\s+OF\s+APPLICANT[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
                '\bADDRESS[\s:\-/\.]*([^\n]+(?:\n[^\n]{1,80})*)',
            ];

            foreach ($patterns as $pattern) {
                $regex = '~' . $pattern . '~i';
                if (preg_match($regex, $text, $matches)) {
                    $value = trim(preg_replace('~\s+~', ' ', $matches[1] ?? ''));
                    // Clean up the value - remove leading labels if any
                    $value = preg_replace('~^(?:ADDRESS\s+OF\s+OWNER|OWNER.*?ADDRESS|ADDRESS|ADDR)[\s:\-\.]*~i', '', $value);
                    $value = trim(preg_replace('~\s+~', ' ', $value));

                    if (!empty($value) && strlen($value) > 5) {
                        $ownerAddress = substr($value, 0, 300);
                        break;
                    }
                }
            }

            if ($ownerAddress) {
                $data['owner_address'] = $ownerAddress;
                \Log::info('RC Address Extraction (Google Vision): Found address: ' . $ownerAddress);
            } else {
                \Log::info('RC Address Extraction (Google Vision): No address found. Text preview: ' . substr($text, 0, 500));
            }
        }

        // Clean up empty / null values
        return array_filter(array_map('trim', $data));
    }

    protected function extractField($text, $patterns, $maxLength = 100)
    {
        foreach ($patterns as $pattern) {
            // Use ~ as delimiter — safe, never appears in RC document text
            // Match label followed by SAME-LINE separators (no newlines) then value
            // [ \t:\-/\.]* matches spaces, tabs, colons, dashes, dots, slashes — but NOT newlines
            $regex = '~' . $pattern . '[ \t:\-/\.]*([^\n\r]{1,' . $maxLength . '})~i';
            try {
                if (preg_match($regex, $text, $matches)) {
                    $value = trim(preg_replace('~\s+~', ' ', $matches[1]));
                    if (!empty($value) && strlen($value) > 1 && !$this->looksLikeLabel($value)) {
                        return substr($value, 0, $maxLength);
                    }
                }
            } catch (\Throwable $e) {
                \Log::debug('RC extractField bad pattern: ' . $pattern . ' — ' . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Detect if a captured "value" actually looks like another RC label
     * (e.g. "CH NO", "E SNO" — these are labels, not values)
     */
    protected function looksLikeLabel(string $value): bool
    {
        $value = trim($value);
        $labels = [
            'REG NO', 'CH NO', 'O SNO', 'E SNO', 'REGD UPTO', 'REGD DT', 'REGD',
            'MFR', 'VHE CL', 'NAME', 'S/W/D', 'ADDRESS', 'MODEL', 'CU CAP',
            'NO OF CYL', 'WHEEL BASE', 'FUEL', 'SEATING C', 'COLOR', 'COLOUR',
        ];
        foreach ($labels as $label) {
            if (strcasecmp($value, $label) === 0) return true;
        }
        return false;
    }

    /**
     * Smart line-based extraction for Indian RC documents.
     *
     * Google Vision OCR returns mixed layouts:
     *   1) LABEL \n : VALUE              (label, then colon-value on next line)
     *   2) LABEL \n LABEL \n VALUE \n VALUE  (stacked labels then stacked values)
     *   3) Sometimes value appears BEFORE label
     *
     * Strategy: Multi-pass with type validation
     */
    protected function smartLineExtract(string $upperText): array
    {
        // Split into trimmed non-empty lines, preserving order
        $allLines = array_map('trim', preg_split('~[\r\n]+~', $upperText));
        $allLines = array_values(array_filter($allLines, fn($l) => $l !== ''));

        $labelMap = $this->getIndianRCLabelMap();
        $result = [];
        $consumed = [];
        $labelsSeen = [];   // track which field labels actually appeared in the doc
        $count = count($allLines);

        // First sweep — record which labels exist in this document
        foreach ($allLines as $line) {
            $field = $this->matchLabel($line, $labelMap);
            if (!$field) {
                // Also check same-line "Label: Value" pattern
                if (preg_match('~^([A-Z][A-Z0-9\s\.\/\']{1,40}?)\s*[:]\s*(.+)$~i', $line, $m)) {
                    $field = $this->matchLabel(trim($m[1]), $labelMap);
                }
            }
            if ($field && !str_starts_with($field, '_skip_')) {
                $labelsSeen[$field] = true;
            }
        }

        // Helper: is line at index $idx a (non-consumed) label?
        $isLabel = function ($idx) use ($allLines, $labelMap, &$consumed, $count) {
            if ($idx < 0 || $idx >= $count) return false;
            if (isset($consumed[$idx])) return false;
            return (bool) $this->matchLabel($allLines[$idx], $labelMap);
        };

        // ─── PASS 0: Same-line "Label: Value" format ────────────────────
        // Example: "Vehicle Class: Motor Car"
        for ($i = 0; $i < $count; $i++) {
            if (isset($consumed[$i])) continue;
            $line = $allLines[$i];

            // Try to split into label and value at first colon
            if (preg_match('~^([A-Z][A-Z0-9\s\.\/\']{1,40}?)\s*[:]\s*(.+)$~i', $line, $m)) {
                $labelPart = trim($m[1]);
                $valuePart = trim($m[2]);
                $field = $this->matchLabel($labelPart, $labelMap);
                if (!$field) continue;

                $consumed[$i] = true;
                if (str_starts_with($field, '_skip_')) continue;

                if ($valuePart !== '' && empty($result[$field])
                    && $this->valueLooksValidForField($field, $valuePart)) {
                    $result[$field] = $valuePart;
                }
            }
        }

        // ─── PASS 1: ISOLATED label with adjacent value ─────────────────
        // Pattern: label is NOT next to other labels (singleton).
        // Next line is the value (with or without leading ":").
        for ($i = 0; $i < $count; $i++) {
            if (isset($consumed[$i])) continue;

            $line = $allLines[$i];
            $field = $this->matchLabel($line, $labelMap);
            if (!$field) continue;

            // If previous OR next line is also a label, this is a stacked group — skip
            if ($isLabel($i - 1) || $isLabel($i + 1)) continue;

            // Take the next non-empty non-label line as the value
            if (!isset($allLines[$i + 1])) continue;
            $valueLine = $allLines[$i + 1];
            if (isset($consumed[$i + 1])) continue;
            // If next line is also a label, skip (shouldn't happen due to above check)
            if ($this->matchLabel($valueLine, $labelMap)) continue;

            // Strip leading ":" if present
            $value = trim(preg_replace('~^\s*:\s*~', '', $valueLine));

            // Always consume the LABEL line
            $consumed[$i] = true;

            // For skip labels — consume both, don't save
            if (str_starts_with($field, '_skip_')) {
                $consumed[$i + 1] = true;
                continue;
            }

            // For real labels — only consume value line IF assignment succeeds
            // (otherwise leave value unconsumed for PASS 3 to potentially use)
            if ($value !== '' && empty($result[$field])
                && $this->valueLooksValidForField($field, $value)) {
                $result[$field] = $value;
                $consumed[$i + 1] = true;
            }
        }

        // ─── PASS 2: STACKED label groups → positional value matching ───
        for ($i = 0; $i < $count; $i++) {
            if (isset($consumed[$i])) continue;
            if (!$isLabel($i)) continue;

            // Collect consecutive unconsumed labels starting here
            $labelGroup = [];
            $j = $i;
            while ($j < $count && $isLabel($j)) {
                $labelGroup[] = ['idx' => $j, 'field' => $this->matchLabel($allLines[$j], $labelMap)];
                $j++;
            }
            // Mark all labels in group as consumed
            foreach ($labelGroup as $lg) $consumed[$lg['idx']] = true;

            // Collect value lines after the group until next label or end
            $valueGroup = [];
            while ($j < $count) {
                if (isset($consumed[$j])) { $j++; continue; }
                if ($this->matchLabel($allLines[$j], $labelMap)) break;
                $cleaned = trim(preg_replace('~^\s*:\s*~', '', $allLines[$j]));
                if ($cleaned !== '') {
                    $valueGroup[] = ['idx' => $j, 'value' => $cleaned];
                }
                $j++;
            }

            // Positional matching: label[0]↔value[0], label[1]↔value[1], etc.
            // Skip labels (Address, S/W/D, etc.) advance position but DON'T consume the
            // value — this lets PASS 3 pick up orphan model/class/color values that the
            // OCR happened to drop into a skip-label slot.
            $vIdx = 0;
            foreach ($labelGroup as $lg) {
                if ($vIdx >= count($valueGroup)) break;

                $val = $valueGroup[$vIdx]['value'];
                $valIdx = $valueGroup[$vIdx]['idx'];
                $field = $lg['field'];

                if (str_starts_with($field, '_skip_')) {
                    // Advance position; leave value unconsumed for PASS 3
                    $vIdx++;
                    continue;
                }

                if (empty($result[$field])
                    && $this->valueLooksValidForField($field, $val)) {
                    $result[$field] = $val;
                    $consumed[$valIdx] = true;
                }
                // If validation fails, don't consume — PASS 3 can try this value
                $vIdx++;
            }

            $i = $j - 1; // continue after the group
        }

        // ─── PASS 3: Global type-based hunt — ONLY if label was actually seen ──
        // The "label seen" check prevents PASS 3 from inventing junk values for
        // fields that don't exist in this RC variant.
        $allFields = [
            'vehicle_registration_no', 'chassis_no', 'engine_no',
            'registration_date', 'validity',
            'fuel_type', 'color',
            'vehicle_model', 'vehicle_class',
            'holder_name', 'manufacturer',
        ];
        foreach ($allFields as $field) {
            if (!empty($result[$field])) continue;
            if (!isset($labelsSeen[$field])) continue; // skip — label wasn't in doc

            foreach ($allLines as $idx => $line) {
                if (isset($consumed[$idx])) continue;
                $cleaned = trim(preg_replace('~^\s*:\s*~', '', $line));
                if ($cleaned === '') continue;
                if ($this->matchLabel($cleaned, $labelMap)) continue;
                if (!$this->valueLooksValidForField($field, $cleaned)) continue;
                if ($this->valueAlreadyAssigned($cleaned, $result)) continue;
                $result[$field] = $cleaned;
                $consumed[$idx] = true;
                break;
            }
        }

        return $this->filterTargetFields($result);
    }

    /**
     * Check if this value has already been assigned to another field
     * (prevents double-assignment in pass 3)
     */
    protected function valueAlreadyAssigned(string $value, array $result): bool
    {
        foreach ($result as $existing) {
            if (strcasecmp(trim($existing), trim($value)) === 0) return true;
        }
        return false;
    }

    /**
     * Indian RC label → field-key mapping (anchored to full-line regex)
     * Supports MULTIPLE RC formats:
     *   - Old Form 23A:   REG NO, CH NO, E SNO, VHE CL, MODEL, FUEL, COLOR, etc.
     *   - New Smartcard:  Regn. Number, Chassis Number, Engine / Motor Number,
     *                     Owner Name, Date of Regn., Regn. Validity, etc.
     */
    protected function getIndianRCLabelMap(): array
    {
        return [
            // ── REGISTRATION NUMBER ──────────────────────────────────────
            '~^REG\s*NO\.?$~i'                                          => 'vehicle_registration_no',
            '~^REGN\.?\s*NO\.?$~i'                                      => 'vehicle_registration_no',
            '~^REGN\.?\s*NUMBER$~i'                                     => 'vehicle_registration_no',
            '~^REGISTRATION\s*N(?:O|UMBER)\.?$~i'                       => 'vehicle_registration_no',
            '~^VEHICLE\s+REG(?:ISTRATION)?\s*N(?:O|UMBER)$~i'           => 'vehicle_registration_no',

            // ── CHASSIS NUMBER ───────────────────────────────────────────
            '~^CH\s*NO\.?$~i'                                           => 'chassis_no',
            '~^CHASS?IS\s*N(?:O|UMBER)\.?$~i'                           => 'chassis_no',
            '~^CHASS?IS$~i'                                             => 'chassis_no',

            // ── ENGINE NUMBER (new format includes "MOTOR") ──────────────
            '~^E\s*SNO\.?$~i'                                           => 'engine_no',
            '~^ENGINE\s*\/?\s*MOTOR\s*N(?:O|UMBER)$~i'                  => 'engine_no',
            '~^ENGINE\s*N(?:O|UMBER)\.?$~i'                             => 'engine_no',
            '~^ENG\s*NO\.?$~i'                                          => 'engine_no',
            '~^MOTOR\s*N(?:O|UMBER)$~i'                                 => 'engine_no',

            // ── VEHICLE MODEL ────────────────────────────────────────────
            '~^MODEL$~i'                                                => 'vehicle_model',
            '~^MODEL\s*NAME$~i'                                         => 'vehicle_model',
            '~^VEHICLE\s+MODEL$~i'                                      => 'vehicle_model',
            '~^MAKE(?:R)?(?:\'?S)?\s*MODEL$~i'                          => 'vehicle_model',

            // ── VEHICLE CLASS ────────────────────────────────────────────
            '~^VHE\s*CL\.?$~i'                                          => 'vehicle_class',
            '~^VEHICLE\s+CLASS$~i'                                      => 'vehicle_class',
            '~^CLASS\s+OF\s+VEHICLE$~i'                                 => 'vehicle_class',
            '~^TYPE\s+OF\s+VEHICLE$~i'                                  => 'vehicle_class',
            '~^BODY\s+TYPE$~i'                                          => '_skip_body_type',

            // ── FUEL TYPE ────────────────────────────────────────────────
            '~^FUEL$~i'                                                 => 'fuel_type',
            '~^FUEL\s+(?:USED|TYPE)$~i'                                 => 'fuel_type',
            '~^TYPE\s+OF\s+FUEL$~i'                                     => 'fuel_type',

            // ── COLOR ────────────────────────────────────────────────────
            '~^COLO(?:U)?R$~i'                                          => 'color',

            // ── MANUFACTURER ─────────────────────────────────────────────
            '~^MFR$~i'                                                  => 'manufacturer',
            '~^MANUFACTURER$~i'                                         => 'manufacturer',
            '~^MAKE$~i'                                                 => 'manufacturer',
            '~^MAKER(?:\'S)?\s*NAME$~i'                                 => 'manufacturer',

            // ── OWNER / HOLDER NAME ──────────────────────────────────────
            '~^NAME$~i'                                                 => 'holder_name',
            '~^OWNER\s*NAME$~i'                                         => 'holder_name',
            '~^OWNER(?:\'S)?\s+NAME$~i'                                 => 'holder_name',
            '~^REGISTERED\s+OWNER$~i'                                   => 'holder_name',

            // ── REGISTRATION DATE ────────────────────────────────────────
            '~^REGD\s*DT\.?$~i'                                         => 'registration_date',
            '~^REGD\.?\s*DATE$~i'                                       => 'registration_date',
            '~^DATE\s+OF\s+REGN\.?$~i'                                  => 'registration_date',
            '~^DATE\s+OF\s+REG(?:ISTRATION)?\.?$~i'                     => 'registration_date',
            '~^REG(?:ISTRATION|N)?\s+DATE$~i'                           => 'registration_date',

            // ── VALIDITY ─────────────────────────────────────────────────
            '~^REGD\s*UPTO$~i'                                          => 'validity',
            '~^REGN\.?\s*VALIDITY$~i'                                   => 'validity',
            '~^VALID\s+(?:UPTO|TILL|THROUGH)$~i'                        => 'validity',
            '~^VALIDITY$~i'                                             => 'validity',

            // ── SKIP-ONLY LABELS (recognize them so we don't mismatch) ───
            '~^O\s*SNO$~i'                                              => '_skip_old_serial',
            '~^OWNER\s*SERIAL$~i'                                       => '_skip_owner_serial',
            '~^REGD$~i'                                                 => '_skip_regd_authority',
            '~^ISSUED\s+BY$~i'                                          => '_skip_issued_by',
            '~^S\/W\/D$~i'                                              => '_skip_swd',
            '~^SON\s*\/?\s*WIFE\s*\/?\s*DAUGHTER\s*OF$~i'               => '_skip_swd',
            '~^ADDRESS$~i'                                              => '_skip_address',
            '~^CARD\s+ISSUE\s+DATE$~i'                                  => '_skip_card_issue_date',
            '~^EMISSION\s*NORMS?$~i'                                    => '_skip_emission_norms',
            '~^CU\s*CAP$~i'                                             => '_skip_cu_cap',
            '~^NO\s*OF\s*CYL$~i'                                        => '_skip_no_of_cyl',
            '~^WHEEL\s*BASE$~i'                                         => '_skip_wheel_base',
            '~^SEATING\s*C$~i'                                          => '_skip_seating_c',
        ];
    }

    /**
     * Lightweight type validation — reject values that obviously belong to a
     * different field (e.g. pure numbers as vehicle_model)
     */
    protected function valueLooksValidForField(string $field, string $value): bool
    {
        $value = trim($value);
        if ($value === '') return false;

        // Global blacklist — these are document headers/footers/state labels, never values
        $blacklist = [
            'VEHICLE REGISTRATION', 'CERTIFICATE', 'POWERED BY',
            'DIGILOCKER', 'GOVERNMENT OF INDIA', 'MINISTRY OF',
            'ISSUED BY', 'EMISSION NORMS', 'EMISSION NORM',
            'BHARAT STAGE', 'CARD ISSUE DATE', 'OWNER SERIAL',
            'DATE OF REGN', 'REGN VALIDITY', 'CHASSIS NUMBER',
            'ENGINE NUMBER', 'MOTOR NUMBER', 'OWNER NAME',
            'INDIAN UNION', 'VEHICLE REGISTRATION CERTIFICATE',
        ];
        foreach ($blacklist as $bad) {
            if (strcasecmp($value, $bad) === 0) return false; // exact match only
            // Also reject if value STARTS WITH a blacklist word followed by colon/end
            if (preg_match('~^' . preg_quote($bad, '~') . '\s*[:\-]?\s*$~i', $value)) return false;
        }

        switch ($field) {
            case 'vehicle_registration_no':
                // Indian plate format: 2 letters + 1-2 digits + 1-3 letters + 1-4 digits
                $compact = preg_replace('~[\s\-]+~', '', $value);
                return (bool) preg_match('~^[A-Z]{2}\d{1,2}[A-Z]{1,3}\d{1,4}$~', $compact);

            case 'chassis_no':
            case 'engine_no':
                // Must be 6-20 alphanumeric (after removing spaces/dashes)
                $compact = preg_replace('~[\s\-]+~', '', $value);
                if (!preg_match('~^[A-Z0-9]{6,20}$~', $compact)) return false;
                // CRITICAL: must contain BOTH letters AND digits
                // (rejects "ISSUEDBYPUNJAB" which is all letters)
                if (!preg_match('~[A-Z]~', $compact)) return false;
                if (!preg_match('~\d~', $compact)) return false;
                return true;

            case 'registration_date':
            case 'validity':
                // Date-like format: DD-MMM-YYYY, DD-MM-YYYY, DD/MM/YYYY, etc.
                return (bool) preg_match('~\d{1,2}[\-\/\.][A-Z0-9]{2,4}[\-\/\.]\d{2,4}~i', $value);

            case 'vehicle_model':
                if (strpos($value, ',') !== false) return false;
                // Reject if it looks like a chassis/engine no (10+ alphanumeric, no spaces)
                $compact = preg_replace('~[\s\-]+~', '', $value);
                if (preg_match('~^[A-Z0-9]{10,}$~', $compact) && !str_contains($value, ' ')) {
                    return false;
                }
                // Reject emission-norms-style values
                if (preg_match('~BHARAT\s+STAGE~i', $value)) return false;
                if (preg_match('~EURO\s*\d~i', $value)) return false;
                // Must have BOTH letters AND digits (most car models do)
                if (!preg_match('~\d~', $value)) return false;
                if (!preg_match('~[A-Z]~i', $value)) return false;
                return strlen($value) >= 4 && strlen($value) <= 60;

            case 'vehicle_class':
                if (strpos($value, ',') !== false) return false;
                if (preg_match('~^[\d\.\s]+$~', $value)) return false;
                // Reject emission norms
                if (preg_match('~BHARAT\s+STAGE~i', $value)) return false;
                if (preg_match('~EURO\s*\d~i', $value)) return false;
                if (preg_match('~EMISSION~i', $value)) return false;
                // Reject if it looks like a chassis no
                $compact = preg_replace('~[\s\-]+~', '', $value);
                if (preg_match('~^[A-Z0-9]{10,}$~', $compact) && !str_contains($value, ' ')) {
                    return false;
                }
                return (bool) preg_match('~[A-Z]{3,}~i', $value) && strlen($value) <= 40;

            case 'fuel_type':
                // Must be one of the known fuel types
                return (bool) preg_match('~^(DIESEL|PETROL|CNG|ELECTRIC|LPG|HYBRID|GASOLINE|GAS)\b~i', $value);

            case 'color':
                if (strpos($value, ',') !== false) return false;
                if (preg_match('~^[\d\.\s]+$~', $value)) return false;
                // Reject "DATE OF REGN" type matches
                if (preg_match('~DATE|REGN|NUMBER|EMISSION~i', $value)) return false;
                return (bool) preg_match('~[A-Z]{3,}~i', $value) && strlen($value) <= 30;

            case 'holder_name':
                if (strpos($value, ',') !== false) return false;
                if (preg_match('~^[\d\.\s]+$~', $value)) return false;
                // Reject any blacklist-like substring
                if (preg_match('~^(ISSUED|EMISSION|CHASSIS|ENGINE|MOTOR|CARD|REGN)~i', $value)) return false;
                return (bool) preg_match('~[A-Z]{3,}~i', $value) && strlen($value) >= 4;

            case 'manufacturer':
                if (strpos($value, ',') !== false) return false;
                if (preg_match('~^[\d\.\s]+$~', $value)) return false;
                if (preg_match('~BHARAT\s+STAGE|EMISSION|ISSUED~i', $value)) return false;
                return (bool) preg_match('~[A-Z]{3,}~i', $value);

            default:
                return true;
        }
    }

    /**
     * Match a raw label string against the label map (returns field key or null)
     * Strips trailing parenthetical content like "(In case of Individual Owner)"
     */
    protected function matchLabel(string $labelText, array $labelMap): ?string
    {
        $labelText = trim($labelText);
        if ($labelText === '') return null;

        // Strip trailing "(...)" parenthetical content
        $labelText = trim(preg_replace('~\s*\([^)]*\)\s*$~', '', $labelText));
        if ($labelText === '') return null;

        // Strip trailing punctuation
        $labelText = rtrim($labelText, ":.- \t");

        foreach ($labelMap as $regex => $fieldKey) {
            try {
                if (preg_match($regex, $labelText)) {
                    return $fieldKey;
                }
            } catch (\Throwable $e) {
                // ignore bad pattern
            }
        }
        return null;
    }

    /**
     * Keep only the fields we actually use in the certificate form.
     */
    protected function filterTargetFields(array $data): array
    {
        $allowed = [
            'vehicle_registration_no', 'chassis_no', 'engine_no',
            'vehicle_model', 'vehicle_class', 'fuel_type', 'color',
            'holder_name', 'registration_date', 'manufacturer', 'validity',
        ];
        $result = [];
        foreach ($allowed as $key) {
            if (!empty($data[$key])) {
                $result[$key] = trim($data[$key]);
            }
        }
        return $result;
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
            'holder_name'             => 'holder_name',
            'registration_date'       => 'fitment_date',
            'chassis_no'              => 'chassis_no',
            'engine_no'               => 'engine_no',
            'vehicle_model'           => 'vehicle_model',
            'color'                   => 'color',
            'vehicle_class'           => 'vehicle_class',
            'fuel_type'               => 'fuel_type',
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
        $credentialsPath = self::resolveCredentialsPath();
        return !empty($credentialsPath) && file_exists($credentialsPath);
    }

    /**
     * Extract a vehicle registration number from a number plate image.
     * Uses Google Vision TEXT_DETECTION and picks the best plate-like text.
     *
     * Returns the normalized plate number (e.g. "PB10EM1318") or null.
     */
    public function extractPlateNumber(string $imagePath): ?string
    {
        return $this->extractPlateData($imagePath)['plate'] ?? null;
    }

    /**
     * Analyze a number-plate image: returns the detected plate (if any) plus
     * the raw OCR text and average confidence so callers can distinguish
     * "poor image quality" from "readable image, no plate visible".
     *
     * @return array{plate: ?string, text: string, confidence: ?float}
     */
    public function extractPlateData(string $imagePath): array
    {
        $client = null;
        $result = ['plate' => null, 'text' => '', 'confidence' => null];
        try {
            if (!file_exists($imagePath)) {
                throw new Exception('Plate image not found: ' . $imagePath);
            }

            $imageContent = file_get_contents($imagePath);
            if ($imageContent === false) {
                throw new Exception('Could not read plate image.');
            }

            $image = new Image();
            $image->setContent($imageContent);

            $feature = new Feature();
            $feature->setType(Type::TEXT_DETECTION);

            $annotateRequest = new AnnotateImageRequest();
            $annotateRequest->setImage($image);
            $annotateRequest->setFeatures([$feature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$annotateRequest]);

            $client = $this->getClient();
            $batchResponse = $client->batchAnnotateImages($batchRequest);
            $responses = $batchResponse->getResponses();

            if (empty($responses)) return $result;
            $response = $responses[0];

            // Combine full text + individual annotations for max coverage
            $candidates = [];
            $fullAnnotation = $response->getFullTextAnnotation();
            if ($fullAnnotation) {
                $candidates[] = $fullAnnotation->getText();
            }
            foreach ($response->getTextAnnotations() as $annotation) {
                $candidates[] = $annotation->getDescription();
            }

            $combined = strtoupper(implode("\n", $candidates));
            $result['text'] = $combined;
            $result['confidence'] = $this->averageConfidence($fullAnnotation);

            // Remove any whitespace, dashes, dots between plate parts
            $compact = preg_replace('~[\s\-\.]+~', '', $combined);

            // Indian plate format: 2 letters + 1-2 digits + 1-3 letters + 1-4 digits
            if (preg_match('~([A-Z]{2}\d{1,2}[A-Z]{1,3}\d{1,4})~', $compact, $m)) {
                $result['plate'] = $m[1];
                return $result;
            }

            // Fallback: try with spaces allowed
            if (preg_match('~([A-Z]{2}\s*\d{1,2}\s*[A-Z]{1,3}\s*\d{1,4})~', $combined, $m)) {
                $result['plate'] = preg_replace('~\s+~', '', $m[1]);
                return $result;
            }

            return $result;
        } catch (Exception $e) {
            \Log::error('Plate extraction failed: ' . $e->getMessage());
            return $result;
        } finally {
            if ($client) {
                $client->close();
            }
        }
    }

    /**
     * Normalize a plate number for comparison (strip spaces, dashes, dots; uppercase)
     */
    public static function normalizePlateNumber(?string $plate): string
    {
        if (!$plate) return '';
        return strtoupper(preg_replace('~[\s\-\.]+~', '', $plate));
    }

    /**
     * Extract IMEI and ICCID from a GPS device image (label/sticker photo).
     *
     * - IMEI: exactly 15 digits (passes the Luhn check on a real device)
     * - ICCID: 19 or 20 digits starting with "89" (per ITU-T E.118 standard)
     *
     * Returns: [ 'imei' => '...', 'iccid' => '...', 'raw' => '<ocr text>' ]
     *  (any missing key will be null)
     */
    public function extractDeviceInfo(string $imagePath): array
    {
        $client = null;
        try {
            if (!file_exists($imagePath)) {
                throw new Exception('Device image not found: ' . $imagePath);
            }

            $imageContent = file_get_contents($imagePath);
            if ($imageContent === false) {
                throw new Exception('Could not read device image.');
            }

            $image = new Image();
            $image->setContent($imageContent);

            $feature = new Feature();
            $feature->setType(Type::DOCUMENT_TEXT_DETECTION);

            $annotateRequest = new AnnotateImageRequest();
            $annotateRequest->setImage($image);
            $annotateRequest->setFeatures([$feature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$annotateRequest]);

            $client = $this->getClient();
            $batchResponse = $client->batchAnnotateImages($batchRequest);
            $responses = $batchResponse->getResponses();

            if (empty($responses)) {
                return ['imei' => null, 'iccid' => null, 'raw' => ''];
            }
            $response = $responses[0];

            // Get the full text
            $text = '';
            $fullAnnotation = $response->getFullTextAnnotation();
            if ($fullAnnotation) {
                $text = $fullAnnotation->getText();
            } elseif ($response->getTextAnnotations()) {
                $text = $response->getTextAnnotations()[0]->getDescription();
            }

            return [
                'imei'       => $this->extractImei($text),
                'iccid'      => $this->extractIccid($text),
                'raw'        => $text,
                'confidence' => $this->averageConfidence($fullAnnotation),
            ];
        } catch (Exception $e) {
            \Log::error('Device info extraction failed: ' . $e->getMessage());
            return ['imei' => null, 'iccid' => null, 'raw' => '', 'confidence' => null, 'error' => $e->getMessage()];
        } finally {
            if ($client) {
                $client->close();
            }
        }
    }

    /**
     * Extract IMEI (15 digits) from OCR text. Looks for an explicit "IMEI" label
     * first; falls back to any 15-digit sequence.
     */
    protected function extractImei(string $text): ?string
    {
        $upper = strtoupper($text);

        // Pattern 1: "IMEI" label followed by 15 digits (possibly with separators)
        if (preg_match('~IMEI\s*(?:NO|NUMBER|#)?\s*[:\-]?\s*([\d\s\-]{15,25})~i', $upper, $m)) {
            $digits = preg_replace('~\D~', '', $m[1]);
            if (strlen($digits) >= 15) {
                return substr($digits, 0, 15);
            }
        }

        // Pattern 2: Any 15-digit number not preceded by "89" (which would be ICCID)
        if (preg_match_all('~(?<!\d)(\d{15})(?!\d)~', $upper, $matches)) {
            foreach ($matches[1] as $candidate) {
                // Reject ICCID-prefixed numbers
                if (!str_starts_with($candidate, '89')) {
                    return $candidate;
                }
            }
        }

        // Pattern 3: 15 digits broken by spaces/dashes (e.g. "12 345678 901234 5")
        if (preg_match_all('~(?<!\d)([\d][\d\s\-]{14,30}\d)(?!\d)~', $upper, $matches)) {
            foreach ($matches[1] as $candidate) {
                $digits = preg_replace('~\D~', '', $candidate);
                if (strlen($digits) === 15 && !str_starts_with($digits, '89')) {
                    return $digits;
                }
            }
        }

        return null;
    }

    /**
     * Extract ICCID (19-20 digits, typically starts with "89") from OCR text.
     */
    protected function extractIccid(string $text): ?string
    {
        $upper = strtoupper($text);

        // Pattern 1: "ICCID" label followed by digits
        if (preg_match('~ICCID\s*(?:NO|NUMBER|#)?\s*[:\-]?\s*([\d\s\-]{19,30})~i', $upper, $m)) {
            $digits = preg_replace('~\D~', '', $m[1]);
            if (strlen($digits) >= 19) {
                return substr($digits, 0, min(20, strlen($digits)));
            }
        }

        // Pattern 2: Any 19 or 20-digit number starting with "89"
        if (preg_match_all('~(?<!\d)(89\d{17,18})(?!\d)~', $upper, $matches)) {
            return $matches[1][0] ?? null;
        }

        // Pattern 3: 19-20 digits broken by spaces/dashes, starting with 89
        if (preg_match_all('~(?<!\d)(89[\d\s\-]{17,30}\d)(?!\d)~', $upper, $matches)) {
            foreach ($matches[1] as $candidate) {
                $digits = preg_replace('~\D~', '', $candidate);
                if (preg_match('~^89\d{17,18}$~', $digits)) {
                    return $digits;
                }
            }
        }

        return null;
    }
}
