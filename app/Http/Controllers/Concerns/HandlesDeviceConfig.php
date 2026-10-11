<?php

namespace App\Http\Controllers\Concerns;

use App\Helper\CommonHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Shared device-category / firmware / eSIM config validation + snapshot
 * logic used by both PurchaseOrderController (raise PO against a SKU) and
 * SkuController (define a SKU). Extracted so both stay in sync.
 */
trait HandlesDeviceConfig
{
    /**
     * Guard: category + firmware must belong to the target account's assignments.
     * Returns an error message string when invalid, or null when OK.
     */
    private function assertAssigned(int $accountId, int $categoryId, $firmwareId): ?string
    {
        $a = CommonHelper::assignmentsForUser($accountId);
        if (!$a['categories']->pluck('id')->map(fn($v) => (int) $v)->contains($categoryId)) {
            return 'The selected device category is not assigned to this account.';
        }
        if (!empty($firmwareId)) {
            if (!$a['firmware']->pluck('id')->map(fn($v) => (int) $v)->contains((int) $firmwareId)) {
                return 'The selected firmware is not assigned to this account.';
            }
        }
        return null;
    }

    /**
     * Enforce the device-category validation rules on submitted config values.
     * Returns an error message when invalid, or null when OK.
     */
    private function validateConfigValues(int $categoryId, array $config): ?string
    {
        foreach (CommonHelper::deviceCategoryFieldRules($categoryId) as $r) {
            $key = $r['key'];
            $val = array_key_exists($key, $config) ? trim((string) $config[$key]) : '';

            if ($r['required'] && $val === '') {
                return $r['label'] . ' is required.';
            }
            if ($val === '') {
                continue;
            }
            if (!empty($r['maxLength']) && mb_strlen($val) > $r['maxLength']) {
                return $r['label'] . " must be at most {$r['maxLength']} characters.";
            }
            if (($r['type'] ?? '') === 'number') {
                if (!is_numeric($val)) {
                    return $r['label'] . ' must be a number.';
                }
                if ($r['min'] !== null && (float) $val < (float) $r['min']) {
                    return $r['label'] . " must be greater than or equal to {$r['min']}.";
                }
                if ($r['max'] !== null && (float) $val > (float) $r['max']) {
                    return $r['label'] . " must be less than or equal to {$r['max']}.";
                }
            }
            if (($r['type'] ?? '') === 'IP/URL' && !preg_match('#^[A-Za-z0-9._:/\-]+$#', $val)) {
                return $r['label'] . ' is not a valid IP / URL.';
            }
            if (($r['type'] ?? '') === 'select' && !empty($r['options'])) {
                $allowed = array_map(fn($o) => (string) $o['value'], $r['options']);
                if (!in_array($val, $allowed, true)) {
                    return $r['label'] . ' has an invalid selection.';
                }
            }
        }
        return null;
    }

    /**
     * Build a frozen snapshot of the device-category configuration the ordered
     * devices will be provisioned with: category input defaults, overlaid with
     * firmware values (firmware wins), mirroring device provisioning.
     */
    private function buildConfigSnapshot(int $categoryId, ?int $firmwareId, array $overrides = []): array
    {
        $values = [];
        $schema = [];

        $cat = DB::table('device_categories')->where('id', $categoryId)->first();
        if ($cat) {
            foreach ([$cat->inputs ?? null, $cat->parameters ?? null] as $blob) {
                $fields = json_decode((string) $blob, true);
                if (!is_array($fields)) {
                    continue;
                }
                foreach ($fields as $f) {
                    if (empty($f['key'])) {
                        continue;
                    }
                    $key = strtolower(str_replace(' ', '_', $f['key']));
                    $values[$key] = [
                        'id' => $f['id'] ?? null,
                        'value' => $f['default'] ?? '',
                    ];
                    $schema[] = $f;
                }
            }
        }

        foreach ($overrides as $k => $v) {
            $key = strtolower(str_replace(' ', '_', (string) $k));
            if ($key === '') {
                continue;
            }
            if (isset($values[$key])) {
                $values[$key]['value'] = $v;
            } else {
                $values[$key] = ['id' => null, 'value' => $v];
            }
        }

        if ($firmwareId) {
            $fw = DB::table('firmware')->where('id', $firmwareId)->first();
            if ($fw) {
                $fwArr = json_decode((string) $fw->configurations, true) ?: [];
                $values['firmware_id']      = ['id' => 84, 'value' => (string) $firmwareId];
                $values['firmware_file']    = ['id' => 85, 'value' => $fwArr['filename'] ?? ''];
                $values['firmware_version'] = ['id' => 86, 'value' => $fwArr['version'] ?? ''];
                $values['firmwareFileSize'] = ['id' => 83, 'value' => $fwArr['fileSize'] ?? ''];
            }
        }

        return [
            'categoryId' => $categoryId,
            'firmwareId' => $firmwareId,
            'snapshotAt' => Carbon::now('UTC')->toIso8601String(),
            'hash' => 'sha1:' . sha1(json_encode($values)),
            'values' => $values,
            'schema' => $schema,
        ];
    }
}
