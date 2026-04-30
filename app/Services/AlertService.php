<?php

namespace App\Services;

use App\Models\PacketAlert;
use App\Models\ImeiLog;
use App\Models\TicketModel;
use Illuminate\Support\Facades\Log;

class AlertService
{
    public function evaluate($packetTypeId, array $parsedData, ?ImeiLog $log = null)
    {
        $alerts = PacketAlert::with('conditions.field')
            ->where('packet_type_id', $packetTypeId)
            ->where('is_active', true)
            ->get();

        foreach ($alerts as $alert) {
            $matched = true;
            $satisfiedConditions = [];

            foreach ($alert->conditions as $condition) {
                $fieldName = $condition->field->name;
                $value = $parsedData[$fieldName] ?? null;
                
                if ($value === null || !$this->checkCondition($value, $condition->operator, $condition->value)) {
                    $matched = false;
                    break;
                }
                $satisfiedConditions[] = "$fieldName ($value) {$condition->operator} {$condition->value}";
            }

            if ($matched && !empty($alert->conditions)) {
                $this->triggerAlert($alert, $parsedData, $log, $satisfiedConditions);
            }
        }
    }

    /**
     * Validate a packet against all active alerts and return a detailed report.
     */
    public function validate($packetTypeId, array $parsedData): array
    {
        $alerts = PacketAlert::with('conditions.field')
            ->where('packet_type_id', $packetTypeId)
            ->where('is_active', true)
            ->get();

        if ($alerts->isEmpty()) {
            return [
                'has_alerts' => false,
                'summary' => 'No active alerts configured for this packet type.',
                'alerts' => []
            ];
        }

        $report = [];
        $anyTriggered = false;

        foreach ($alerts as $alert) {
            $alertResult = [
                'name' => $alert->name,
                'status' => 'pass', // Default to pass (meaning NO alert triggered/failed condition)
                'triggered' => false,
                'conditions' => []
            ];

            $allConditionsMet = true;
            foreach ($alert->conditions as $condition) {
                $fieldName = $condition->field->name;
                $actualValue = $parsedData[$fieldName] ?? 'N/A';
                $isSatisfied = ($actualValue !== 'N/A') && $this->checkCondition($actualValue, $condition->operator, $condition->value);
                
                if (!$isSatisfied) {
                    $allConditionsMet = false;
                }

                $alertResult['conditions'][] = [
                    'field' => $fieldName,
                    'operator' => $condition->operator,
                    'expected' => $condition->value,
                    'actual' => $actualValue,
                    'is_satisfied' => $isSatisfied
                ];
            }

            if ($allConditionsMet && !empty($alert->conditions)) {
                $alertResult['triggered'] = true;
                $alertResult['status'] = 'fail'; // If all conditions met, the alert triggers (FAIL in validation terms)
                $anyTriggered = true;
            }

            $report[] = $alertResult;
        }

        return [
            'has_alerts' => true,
            'status' => $anyTriggered ? 'fail' : 'pass',
            'summary' => $anyTriggered ? 'One or more alerts triggered.' : 'All alert conditions cleared.',
            'alerts' => $report
        ];
    }

    protected function checkCondition($actual, $operator, $expected)
    {
        // Convert to numeric if possible for comparison
        $isNumeric = is_numeric($actual) && is_numeric($expected);
        $act = $isNumeric ? (float)$actual : $actual;
        $exp = $isNumeric ? (float)$expected : $expected;

        switch ($operator) {
            case '==': return $act == $exp;
            case '!=': return $act != $exp;
            case '<=': return $act <= $exp;
            case '>=': return $act >= $exp;
            default: return false;
        }
    }

    protected function triggerAlert($alert, $parsedData, ?ImeiLog $log, array $satisfiedConditions)
    {
        $packetName = $alert->packetType->name;
        $imei = $log ? $log->imei : 'Unknown Device';
        
        $subject = "Alert Triggered: {$alert->name}";
        $description = "Packet Alert '{$alert->name}' triggered for packet type '{$packetName}' on device {$imei}.\n\n";
        $description .= "Conditions Satisfied:\n" . implode("\n", $satisfiedConditions);
        $description .= "\n\nRaw Data Context: " . ($log ? $log->raw_packet : 'N/A');

        // Create a Ticket (Notification)
        TicketModel::create([
            'type' => 'alert',
            'subject' => $subject,
            'description' => $description,
            'is_read' => 0,
            'status' => 'open',
            'created_by' => 0 // System generated
        ]);

        Log::info("Packet Alert Triggered", [
            'alert_id' => $alert->id,
            'packet_type_id' => $alert->packet_type_id,
            'imei' => $imei,
            'conditions' => $satisfiedConditions
        ]);
    }
}
