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
            ? Protocol::with(['packetTypes.fields'])->where('id', $protocolId)->get()
            : Protocol::with(['packetTypes.fields'])->where('is_active', true)->get();

        foreach ($protocols as $protocol) {
            foreach ($protocol->packetTypes as $candidate) {
                if ($this->isMatching($rawData, $candidate)) {
                    return $this->processPacket($rawData, $candidate, $log, false);
                }
            }
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
        $parts = $delimiter !== null && $delimiter !== '' ? explode($delimiter, $rawData) : null;

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

            $fieldSummary[] = [
                'name' => $field->name,
                'value' => $value,
                'data_type' => $field->data_type,
                'validation_type' => $field->validation_type,
                'is_required' => (bool) $field->is_required,
                'is_valid' => empty($fieldErrors),
                'error' => implode(' ', $fieldErrors),
            ];
        }

        $isValid = empty($errors);
        $result = [
            'enabled' => true,
            'status' => $isValid ? 'pass' : 'fail',
            'label' => $isValid ? 'Pass' : 'Fail',
            'is_valid' => $isValid,
            'packet_type_id' => $packetType->id,
            'packet_type_name' => $packetType->name,
            'protocol_name' => optional($packetType->protocol)->name,
            'parsed_data' => $parsedData,
            'errors' => $errors,
            'field_summary' => $fieldSummary,
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
        if ($parts !== null) {
            $index = max(0, (int) $field->sequence - 1);
            return array_key_exists($index, $parts) ? trim((string) $parts[$index]) : null;
        }

        if (!$field->length) {
            return null;
        }

        $start = 0;
        foreach ($fields as $prevField) {
            if ($prevField->sequence < $field->sequence) {
                $start += (int) $prevField->length;
            }
        }

        $value = substr($rawData, $start, (int) $field->length);
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
            return 'Length must be ' . $field->length . ' characters.';
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
                if (!$this->validateNmeaChecksum($rawData)) {
                    return 'Invalid NMEA checksum.';
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
        ];
    }
}
