<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use App\Models\PacketType;
use App\Models\PacketField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProtocolController extends Controller
{
    public function index()
    {
        $protocols = Protocol::withCount('packetTypes')->get();
        return view('protocol.index', compact('protocols'));
    }

    public function create()
    {
        return view('protocol.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:protocols,name']);
        Protocol::create($request->all());
        return redirect()->route('protocols.index')->with('success', 'Protocol created successfully.');
    }

    public function edit(Protocol $protocol)
    {
        return view('protocol.edit', compact('protocol'));
    }

    public function update(Request $request, Protocol $protocol)
    {
        $request->validate(['name' => 'required|unique:protocols,name,' . $protocol->id]);
        $protocol->update($request->all());
        return redirect()->route('protocols.index')->with('success', 'Protocol updated successfully.');
    }

    public function destroy(Protocol $protocol)
    {
        $protocol->delete();
        return redirect()->route('protocols.index')->with('success', 'Protocol deleted successfully.');
    }

    public function viewPacketTypes(Protocol $protocol)
    {
        $packetTypes = $protocol->packetTypes()->withCount('fields')->get();
        return view('protocol.packet_types', compact('protocol', 'packetTypes'));
    }

    public function createPacketType(Protocol $protocol)
    {
        // Redirecting to the unified builder
        return view('protocol.packet_type_builder', compact('protocol'));
    }

    public function storePacketType(Request $request, Protocol $protocol)
    {
        // This is handled by storeFullConfiguration now, but keeping for compatibility
        return $this->storeFullConfiguration($request, $protocol);
    }

    public function viewFields(PacketType $packetType)
    {
        $protocol = $packetType->protocol;
        return view('protocol.packet_type_builder', compact('protocol', 'packetType'));
    }

    public function storeFullConfiguration(Request $request, Protocol $protocol)
    {
        $normalizedFields = collect($request->input('fields', []))
            ->map(function ($field) {
                if (!is_array($field)) {
                    return $field;
                }

                $field['is_required'] = filter_var($field['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

                return $field;
            })
            ->values()
            ->all();

        $request->merge(['fields' => $normalizedFields]);

        $validator = Validator::make($request->all(), [
            'packet' => ['required', 'array'],
            'packet.id' => ['nullable', 'integer', 'exists:packet_types,id'],
            'packet.name' => ['required', 'string', 'max:100'],
            'packet.header_identifier' => ['nullable', 'string', 'max:100'],
            'packet.delimiter' => ['nullable', 'string', 'max:10'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.name' => ['required', 'string', 'max:100'],
            'fields.*.data_type' => ['required', 'in:String,Numeric,ASCII,HEX'],
            'fields.*.length' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'fields.*.validation_type' => ['required', 'in:none,imei,date_ddmmyyyy,time_hhmmss,nmea_checksum,regex'],
            'fields.*.regex_pattern' => ['nullable', 'string', 'max:255'],
            'fields.*.min_value' => ['nullable', 'numeric'],
            'fields.*.max_value' => ['nullable', 'numeric'],
            'fields.*.is_required' => ['nullable', 'boolean'],
        ], [
            'packet.name.required' => 'Packet name is required.',
            'fields.required' => 'Please add at least one parameter.',
            'fields.min' => 'Please add at least one parameter.',
            'fields.*.name.required' => 'Every parameter must have a field name.',
            'fields.*.data_type.in' => 'Selected field type is invalid.',
            'fields.*.length.integer' => 'Length must be a whole number.',
            'fields.*.length.min' => 'Length must be greater than 0.',
            'fields.*.validation_type.in' => 'Selected validation type is invalid.',
            'fields.*.regex_pattern.max' => 'Regex pattern is too long.',
            'fields.*.min_value.numeric' => 'Minimum range must be numeric.',
            'fields.*.max_value.numeric' => 'Maximum range must be numeric.',
        ]);

        $validator->after(function ($validator) use ($request, $protocol) {
            $packetData = $request->input('packet', []);
            $fields = $request->input('fields', []);

            $duplicatePacket = $protocol->packetTypes()
                ->where('name', $packetData['name'] ?? '')
                ->when(!empty($packetData['id']), function ($query) use ($packetData) {
                    $query->where('id', '!=', $packetData['id']);
                })
                ->exists();

            if ($duplicatePacket) {
                $validator->errors()->add('packet.name', 'This packet name already exists for the selected protocol.');
            }

            $fieldNames = [];
            foreach ($fields as $index => $field) {
                $rowNumber = $index + 1;
                $nameKey = strtolower(trim($field['name'] ?? ''));

                if ($nameKey !== '') {
                    if (isset($fieldNames[$nameKey])) {
                        $validator->errors()->add("fields.$index.name", "Duplicate parameter name found on row {$rowNumber}.");
                    }
                    $fieldNames[$nameKey] = true;
                }

                if (($field['validation_type'] ?? 'none') === 'regex') {
                    $pattern = trim($field['regex_pattern'] ?? '');
                    if ($pattern === '') {
                        $validator->errors()->add("fields.$index.regex_pattern", "Regex pattern is required on row {$rowNumber}.");
                    } elseif (@preg_match('/' . str_replace('/', '\/', $pattern) . '/', '') === false) {
                        $validator->errors()->add("fields.$index.regex_pattern", "Regex pattern is invalid on row {$rowNumber}.");
                    }
                }

                $min = $field['min_value'] ?? null;
                $max = $field['max_value'] ?? null;
                if ($min !== null && $min !== '' && $max !== null && $max !== '' && (float) $min > (float) $max) {
                    $validator->errors()->add("fields.$index.min_value", "Minimum range cannot be greater than maximum range on row {$rowNumber}.");
                }

                if (($field['validation_type'] ?? 'none') === 'imei' && !empty($field['length']) && (int) $field['length'] !== 15) {
                    $validator->errors()->add("fields.$index.length", "IMEI validation requires length 15 on row {$rowNumber}.");
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the highlighted validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $packetData = $validator->validated()['packet'];
        $fields = $validator->validated()['fields'];

        return DB::transaction(function () use ($protocol, $packetData, $fields) {
            $packetType = $protocol->packetTypes()->updateOrCreate(
                ['id' => $packetData['id'] ?? null],
                [
                    'name' => trim($packetData['name']),
                    'header_identifier' => $this->nullableTrim($packetData['header_identifier'] ?? null),
                    'delimiter' => $this->nullableTrim($packetData['delimiter'] ?? null),
                    'is_active' => true,
                ]
            );

            $packetType->fields()->delete();
            foreach ($fields as $index => $fieldData) {
                $packetType->fields()->create([
                    'name' => trim($fieldData['name']),
                    'sequence' => $index + 1,
                    'data_type' => $fieldData['data_type'] ?? 'ASCII',
                    'length_type' => $fieldData['length_type'] ?? 'Fixed',
                    'length' => $this->nullableTrim($fieldData['length'] ?? null),
                    'fixed_value' => $this->nullableTrim($fieldData['fixed_value'] ?? null),
                    'min_value' => $this->nullableTrim($fieldData['min_value'] ?? null),
                    'max_value' => $this->nullableTrim($fieldData['max_value'] ?? null),
                    'regex_pattern' => $this->nullableTrim($fieldData['regex_pattern'] ?? null),
                    'validation_type' => $fieldData['validation_type'] ?? 'none',
                    'is_required' => filter_var($fieldData['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => true,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Configuration saved.', 'redirect' => route('protocols.packet-types', $protocol->id)]);
        });
    }

    protected function nullableTrim($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}



