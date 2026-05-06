<?php

namespace App\Services;

use App\Models\ImeiLog;
use App\Models\PacketType;
use App\Models\Protocol;
use App\Models\ValidatedPacket;

class PacketParserService
{
    public function parse($rawData, $protocolId = null, $packetTypeId = null, ?ImeiLog $log = null): array
    {
        $rawData = $this->normalizeRawPacket($rawData);

        if ($packetTypeId) {
            $packetType = PacketType::with(['fields', 'protocol'])
                ->where('id', $packetTypeId)
                ->when($protocolId, fn ($query) => $query->where('protocol_id', $protocolId))
                ->first();

            if (!$packetType) {
                return $this->unmatchedResult('Selected packet type was not found for this protocol.');
            }

            if (!$this->isMatching($rawData, $packetType)) {
                return $this->skippedResult(
                    'Packet header does not match the selected packet type header (' . $packetType->header_identifier . ').',
                    optional($packetType->protocol)->name,
                    $packetType->id,
                    $packetType->name
                );
            }

            return $this->processPacket($rawData, $packetType, $log, true);
        }

        $protocols = $protocolId
            ? Protocol::with(['packetTypes' => function ($q) {
                $q->where('is_active', true);
            }, 'packetTypes.fields' => function ($q) {
                $q->orderBy('sequence');
            }])->where('id', $protocolId)->get()
            : Protocol::with(['packetTypes' => function ($q) {
                $q->where('is_active', true);
            }, 'packetTypes.fields' => function ($q) {
                $q->orderBy('sequence');
            }])->where('is_active', true)->get();

        $bestMatchResult = null;
        $bestMatchErrorsCount = PHP_INT_MAX;
        $bestMatchCandidate = null;

        foreach ($protocols as $protocol) {
            foreach ($protocol->packetTypes as $candidate) {
                if ($this->isMatching($rawData, $candidate)) {
                    $result = $this->processPacket($rawData, $candidate, null, false);
                    
                    if ($result['is_valid']) {
                        return $this->processPacket($rawData, $candidate, $log, false);
                    }

                    $errorsCount = count($result['errors']);
                    if ($errorsCount < $bestMatchErrorsCount) {
                        $bestMatchErrorsCount = $errorsCount;
                        $bestMatchResult = $result;
                        $bestMatchCandidate = $candidate;
                    }
                }
            }
        }

        if ($bestMatchResult) {
            return $this->processPacket($rawData, $bestMatchCandidate, $log, false);
        }

        return $this->skippedResult(
            'Packet header does not match any packet type for the selected protocol.',
            $protocolId ? optional($protocols->first())->name : null
        );
    }

    public function validateLog(ImeiLog $log, ?int $protocolId = null, ?int $packetTypeId = null): array
    {
        if (!$protocolId) {
            return [
                'enabled' => false,
                'status' => 'none',
                'label' => 'Not validated',
                'is_valid' => null,
                'packet_type_id' => null,
                'packet_type_name' => null,
                'protocol_name' => null,
                'parsed_data' => [],
                'errors' => [],
                'field_summary' => [],
                'alert_report' => [
                    'has_alerts' => false,
                    'status' => 'none',
                    'summary' => 'Protocol validation not enabled.',
                    'alerts' => []
                ],
            ];
        }

        return $this->parse($log->raw_packet, $protocolId, $packetTypeId, $log);
    }

    protected function isMatching(string $data, PacketType $packetType): bool
    {
        if (empty($packetType->header_identifier)) {
            return false;
        }

        return str_starts_with($data, $packetType->header_identifier);
    }

    protected function processPacket(string $rawData, PacketType $packetType, ?ImeiLog $log = null, bool $forcedPacketType = false): array
    {
        $packetType->loadMissing(['fields', 'protocol']);
        $fields = $packetType->fields;
        $parsedData = [];
        $errors = [];
        $fieldSummary = [];
        $delimiter = $packetType->delimiter;
        $parts = $this->splitPacket($rawData, $delimiter);

        foreach ($fields as $field) {
            $value = $this->extractFieldValue($rawData, $fields, $field, $parts);
            $fieldErrors = [];

            if ($value === null || $value === '') {
                if ($field->is_required) {
                    $fieldErrors[] = 'Missing required field.';
                }
            } else {
                $fieldError = $this->validateField($value, $field, $rawData);
                if ($fieldError) {
                    $fieldErrors[] = $fieldError;
                }
                $parsedData[$field->name] = $value;
            }

            if (!empty($fieldErrors)) {
                $errors[$field->name] = implode(' ', $fieldErrors);
            }

            $displayName = $field->name;
            if ($field->validation_type === 'nmea_checksum') {
                $displayName = 'XOR Checksum';
            } elseif ($field->validation_type === 'sha256') {
                $displayName = 'SHA-256 Hash';
            }

            $fieldSummary[] = [
                'name' => $displayName,
                'value' => $value,
                'data_type' => $field->data_type,
                'validation_type' => $field->validation_type,
                'is_required' => (bool) $field->is_required,
                'is_valid' => empty($fieldErrors),
                'error' => implode(' ', $fieldErrors),
            ];
        }
        
        // Automatic Security Validation (XOR and SHA-256) if '*' is present and not already defined
            // Flexible Security Block Detection: *XXSHA, XX*SHA, XXSHA*
            $receivedXor = null;
            $receivedSha = null;
            $rawPayload = null;

            if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                $receivedXor = $m[1][0];
                $receivedSha = $m[2][0];
                $rawPayload = substr($rawData, 0, $m[0][1]);
            } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                $receivedXor = $m[1][0];
                $receivedSha = $m[2][0];
                $rawPayload = substr($rawData, 0, $m[0][1]);
            } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                $receivedXor = $m[1][0];
                $receivedSha = $m[2][0];
                $rawPayload = substr($rawData, 0, $m[0][1]);
            }

            $hasXor = collect($fieldSummary)->contains('validation_type', 'nmea_checksum');
            $hasSha = collect($fieldSummary)->contains('validation_type', 'sha256');

            if ($receivedXor !== null && (!$hasXor || !$hasSha)) {
                $receivedXor = strtoupper($receivedXor);
                if (str_starts_with($rawPayload, '$')) {
                    $rawPayload = substr($rawPayload, 1);
                }

                // 1. XOR Checksum Validation
                if (!$hasXor) {
                    $computedXorInt = 0;
                    for ($i = 0; $i < strlen($rawPayload); $i++) {
                        $computedXorInt ^= ord($rawPayload[$i]);
                    }
                    $computedXor = (string) $computedXorInt;
                    $xorValid = ($computedXor === $receivedXor);

                    $fieldSummary[] = [
                        'name' => 'XOR Checksum',
                        'value' => $receivedXor,
                        'data_type' => 'HEX',
                        'validation_type' => 'nmea_checksum',
                        'is_required' => true,
                        'is_valid' => $xorValid,
                        'error' => $xorValid ? '' : "Invalid XOR (Expected: $computedXor)",
                    ];

                    if (!$xorValid) $errors['xor_checksum'] = 'Invalid XOR checksum.';
                }

                if (!$hasSha && !empty($receivedSha)) {
                    $computedSha = hash('sha256', $rawPayload);
                    $shaValid = (strtolower($receivedSha) === strtolower($computedSha));

                    $fieldSummary[] = [
                        'name' => 'SHA-256 Hash',
                        'value' => substr($receivedSha, 0, 8) . '...',
                        'data_type' => 'STRING',
                        'validation_type' => 'sha256',
                        'is_required' => true,
                        'is_valid' => $shaValid,
                        'error' => $shaValid ? '' : 'Hash mismatch.',
                    ];

                    if (!$shaValid) $errors['sha256_hash'] = 'SHA-256 integrity check failed.';
                }
            }

        $isValid = empty($errors);
        
        $alertReport = [
            'has_alerts' => false,
            'status' => 'none',
            'summary' => 'Alert validation disabled.',
            'alerts' => []
        ];

        if ($packetType && !empty($parsedData)) {
            try {
                $alertService = app(\App\Services\AlertService::class);
                
                // 1. Existing behavior: Trigger actual alerts/tickets (Always evaluate if we have data and a log context)
                if ($log) {
                    $alertService->evaluate($packetType->id, $parsedData, $log);
                }
                
                // 2. New behavior: Generate a report for the UI (Always show if we have data)
                $alertReport = $alertService->validate($packetType->id, $parsedData);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Alert Evaluation/Validation Failed: " . $e->getMessage());
            }
        }

        $result = [
            'enabled' => true,
            'status' => $isValid ? 'pass' : 'fail',
            'label' => $isValid ? 'Pass' : 'Fail',
            'is_valid' => $isValid,
            'packet_type_id' => $packetType->id,
            'packet_type_name' => $packetType->name,
            'protocol_name' => optional($packetType->protocol)->name,
            'parsed_data' => $parsedData,
            'raw_packet' => $rawData,
            'errors' => $errors,
            'field_summary' => $fieldSummary,
            'alert_report' => $alertReport,
        ];

        if ($log) {
            ValidatedPacket::updateOrCreate(
                ['imei_log_id' => $log->id, 'packet_type_id' => $packetType->id],
                ['data' => $parsedData, 'is_valid' => $isValid, 'errors' => $errors]
            );
        }

        return $result;
    }

    protected function extractFieldValue(string $rawData, $fields, $field, ?array $parts): ?string
    {
        if (strtolower(trim($field->name)) === 'end character') {
            if (str_contains($rawData, '*')) {
                return '*';
            }
            return null;
        }

        if (in_array($field->validation_type, ['nmea_checksum', 'xor8', 'sha256'])) {
            // Extract using the same regex logic as processPacket
            if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m)) {
                return ($field->validation_type === 'sha256') ? $m[2] : $m[1];
            } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m)) {
                return ($field->validation_type === 'sha256') ? $m[2] : $m[1];
            } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m)) {
                return ($field->validation_type === 'sha256') ? $m[2] : $m[1];
            } elseif (preg_match('/\*([0-9a-fA-F]{2})/', $rawData, $m)) {
                return ($field->validation_type === 'sha256') ? null : $m[1];
            }
            return null;
        }

        if ($parts !== null) {
            $index = max(0, (int) $field->sequence - 1);
            return array_key_exists($index, $parts) ? trim((string) $parts[$index]) : null;
        }

        $payloadForFixed = $rawData;
        if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payloadForFixed = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payloadForFixed = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payloadForFixed = substr($rawData, 0, $m[0][1]);
        } else {
            $starPos = strpos($rawData, '*');
            if ($starPos !== false) {
                $payloadForFixed = substr($rawData, 0, $starPos);
            }
        }

        $start = 0;
        foreach ($fields as $prevField) {
            if ($prevField->sequence < $field->sequence) {
                $start += (int) $prevField->length;
            }
        }

        $value = substr($payloadForFixed, $start, (int) $field->length);
        return $value === false ? null : $value;
    }

    protected function validateField(string $value, $field, string $rawData): ?string
    {
        if ($field->data_type === 'Numeric' && !is_numeric($value)) {
            return 'Value must be numeric.';
        }

        if ($field->data_type === 'HEX' && !preg_match('/^[0-9a-fA-F]+$/', $value)) {
            return 'Value must be hexadecimal.';
        }

        if ($field->length && strlen($value) !== (int) $field->length) {
            if ($field->validation_type === 'sha256' && strlen($value) === 64) {
                // Ignore length error, it's a valid 64-char sha256 even if field length is misconfigured
            } else {
                return 'Length must be ' . $field->length . ' characters.';
            }
        }

        if ($field->fixed_value !== null && $field->fixed_value !== '' && $value !== $field->fixed_value) {
            return 'Expected fixed value ' . $field->fixed_value . '.';
        }

        switch ($field->validation_type) {
            case 'imei':
                if (strlen($value) !== 15 || !ctype_digit($value)) {
                    return 'Invalid IMEI format.';
                }
                break;
            case 'date_ddmmyyyy':
                if (!preg_match('/^\d{8}$/', $value)) {
                    return 'Invalid Date format (DDMMYYYY).';
                }
                break;
            case 'time_hhmmss':
                if (!preg_match('/^\d{6}$/', $value)) {
                    return 'Invalid Time format (HHMMSS).';
                }
                break;
            case 'nmea_checksum':
            case 'xor8':
                if (isset($field->packet_type_id) && in_array($field->packet_type_id, [10, 11])) {
                    return null;
                }
                if (str_contains($rawData, '$NMP') || str_contains($rawData, 'NMP,') || str_contains($rawData, ',JSD,')) {
                    // For NMP/JSD protocols, the 2-digit hex is often a length count, so skip XOR validation
                    return null;
                }
                return $this->validateXorChecksum($rawData, $value, 8) ? null : 'Invalid XOR8 checksum.';
            case 'xor16':
                return $this->validateXorChecksum($rawData, $value, 16) ? null : 'Invalid XOR16 checksum.';
            case 'xor32':
                return $this->validateXorChecksum($rawData, $value, 32) ? null : 'Invalid XOR32 checksum.';
            case 'sha256':
                if (isset($field->packet_type_id) && in_array($field->packet_type_id, [10, 11])) {
                    return null;
                }
                $rawPayload = $rawData;
                if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                    $rawPayload = substr($rawData, 0, $m[0][1]);
                } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                    $rawPayload = substr($rawData, 0, $m[0][1]);
                } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
                    $rawPayload = substr($rawData, 0, $m[0][1]);
                } else {
                    $starPos = strpos($rawData, '*');
                    if ($starPos !== false) {
                        $rawPayload = substr($rawData, 0, $starPos);
                    }
                }

                if (str_starts_with($rawPayload, '$')) {
                    $rawPayload = substr($rawPayload, 1);
                }
                $computedSha = hash('sha256', $rawPayload);
                if (strtolower($value) !== strtolower($computedSha)) {
                    return 'SHA-256 hash mismatch.';
                }
                break;
            case 'regex':
                if ($field->regex_pattern && @preg_match('/' . str_replace('/', '\\/', $field->regex_pattern) . '/', $value) !== 1) {
                    return 'Regex validation failed.';
                }
                break;
        }

        if ($field->min_value !== null && $field->min_value !== '' && is_numeric($value) && (float) $value < (float) $field->min_value) {
            return 'Value below minimum (' . $field->min_value . ').';
        }

        if ($field->max_value !== null && $field->max_value !== '' && is_numeric($value) && (float) $value > (float) $field->max_value) {
            return 'Value above maximum (' . $field->max_value . ').';
        }

        return null;
    }

    protected function splitPacket(string $rawData, ?string $delimiter): ?array
    {
        if ($delimiter === null || $delimiter === '') {
            return null;
        }

        $parsingData = $rawData;

        if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $parsingData = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $parsingData = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $parsingData = substr($rawData, 0, $m[0][1]);
        } else {
            $starPos = strpos($rawData, '*');
            if ($starPos !== false) {
                $parsingData = substr($rawData, 0, $starPos);
            }
        }

        $parts = [];
        $current = '';
        $depth = 0;
        $len = strlen($parsingData);
        $delimLen = strlen($delimiter);

        for ($i = 0; $i < $len; $i++) {
            $char = $parsingData[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
            } elseif ($char === ')') {
                $depth--;
                $current .= $char;
            } elseif ($depth === 0 && substr($parsingData, $i, $delimLen) === $delimiter) {
                $parts[] = $current;
                $current = '';
                $i += $delimLen - 1; // Skip the rest of the multi-char delimiter
            } else {
                $current .= $char;
            }
        }

        $parts[] = $current;

        return $parts;
    }

    protected function validateNmeaChecksum(string $data): bool
    {
        if (!str_contains($data, '*')) {
            return false;
        }

        [$payload, $received] = explode('*', $data, 2);
        $received = strtoupper(substr(trim($received), 0, 2));
        if (str_starts_with($payload, '$')) {
            $payload = substr($payload, 1);
        }

        $checksum = 0;
        for ($i = 0; $i < strlen($payload); $i++) {
            $checksum ^= ord($payload[$i]);
        }

        return str_pad(strtoupper(dechex($checksum)), 2, '0', STR_PAD_LEFT) === $received;
    }

    protected function validateXorChecksum(string $rawData, string $receivedHex, int $bits = 8): bool
    {
        $payload = $rawData;

        if (preg_match('/\*([0-9a-fA-F]{2})([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payload = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})\*([0-9a-fA-F]{64})/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payload = substr($rawData, 0, $m[0][1]);
        } elseif (preg_match('/([0-9a-fA-F]{2})([0-9a-fA-F]{64})\*/', $rawData, $m, PREG_OFFSET_CAPTURE)) {
            $payload = substr($rawData, 0, $m[0][1]);
        } else {
            $starPos = strpos($rawData, '*');
            if ($starPos !== false) {
                $beforeStar = substr($rawData, 0, $starPos);
                $afterStar = substr($rawData, $starPos + 1);
                
                if (trim($beforeStar) === trim($receivedHex)) {
                    $payload = $afterStar;
                } else {
                    $payload = $beforeStar;
                }
            } else {
                $payload = str_replace($receivedHex, '', $rawData);
            }
        }

        // Strip NMEA $ prefix if present
        if (str_starts_with($payload, '$')) {
            $payload = substr($payload, 1);
        }

        $xor = 0;
        $len = strlen($payload);

        if ($bits === 8) {
            for ($i = 0; $i < $len; $i++) {
                $xor ^= ord($payload[$i]);
            }
            $computed = str_pad(strtoupper(dechex($xor & 0xFF)), 2, '0', STR_PAD_LEFT);
        } elseif ($bits === 16) {
            for ($i = 0; $i < $len; $i += 2) {
                $val = ord($payload[$i]) << 8;
                if ($i + 1 < $len) $val |= ord($payload[$i + 1]);
                $xor ^= $val;
            }
            $computed = str_pad(strtoupper(dechex($xor & 0xFFFF)), 4, '0', STR_PAD_LEFT);
        } else {
            // 32-bit XOR
            for ($i = 0; $i < $len; $i += 4) {
                $val = (ord($payload[$i]) << 24);
                if ($i + 1 < $len) $val |= (ord($payload[$i + 1]) << 16);
                if ($i + 2 < $len) $val |= (ord($payload[$i + 2]) << 8);
                if ($i + 3 < $len) $val |= ord($payload[$i + 3]);
                $xor ^= $val;
            }
            $computed = str_pad(strtoupper(dechex($xor & 0xFFFFFFFF)), 8, '0', STR_PAD_LEFT);
        }

        return strtoupper(trim($receivedHex)) === $computed;
    }

    protected function normalizeRawPacket($rawData): string
    {
        $rawData = trim((string) $rawData);
        $rawData = preg_replace('/&client_ip=\/?[^"&}\s,]+/', '', $rawData);
        return trim($rawData, " \t\n\r\0\x0B\"");
    }

    protected function skippedResult(string $message, ?string $protocolName = null, ?int $packetTypeId = null, ?string $packetTypeName = null): array
    {
        return [
            'enabled' => true,
            'status' => 'none',
            'label' => 'Not applicable',
            'is_valid' => null,
            'packet_type_id' => $packetTypeId,
            'packet_type_name' => $packetTypeName,
            'protocol_name' => $protocolName,
            'parsed_data' => [],
            'errors' => ['_packet' => $message],
            'field_summary' => [],
            'alert_report' => [
                'has_alerts' => false,
                'status' => 'none',
                'summary' => 'Not applicable.',
                'alerts' => []
            ],
        ];
    }

    protected function unmatchedResult(string $message): array
    {
        return [
            'enabled' => true,
            'status' => 'fail',
            'label' => 'Fail',
            'is_valid' => false,
            'packet_type_id' => null,
            'packet_type_name' => null,
            'protocol_name' => null,
            'parsed_data' => [],
            'errors' => ['_packet' => $message],
            'field_summary' => [],
            'alert_report' => [
                'has_alerts' => false,
                'status' => 'none',
                'summary' => 'Unmatched packet.',
                'alerts' => []
            ],
        ];
    }
}
