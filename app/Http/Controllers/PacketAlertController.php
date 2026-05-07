<?php

namespace App\Http\Controllers;

use App\Models\PacketType;
use App\Models\PacketAlert;
use App\Models\PacketAlertCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PacketAlertController extends Controller
{
    public function index(PacketType $packetType)
    {
        $alerts = $packetType->alerts()->with('conditions.field')->get();
        $protocol = $packetType->protocol;
        
        if (!$protocol) {
            return redirect()->back()->with('error', 'Protocol not found for this packet type.');
        }

        return view('protocol.alerts.index', compact('packetType', 'alerts', 'protocol'));
    }

    public function create(PacketType $packetType)
    {
        $protocol = $packetType->protocol;
        
        if (!$protocol) {
            return redirect()->back()->with('error', 'Protocol not found for this packet type.');
        }

        $fields = $packetType->fields;
        return view('protocol.alerts.create', compact('packetType', 'protocol', 'fields'));
    }

    public function store(Request $request, PacketType $packetType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'conditions' => 'required|array|min:1',
            'conditions.*.field_id' => 'required|exists:packet_fields,id',
            'conditions.*.operator' => 'required|in:==,!=,<=,>=',
            'conditions.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $packetType) {
            $alert = $packetType->alerts()->create([
                'name' => $request->name,
                'is_active' => true,
            ]);

            foreach ($request->conditions as $cond) {
                $alert->conditions()->create([
                    'packet_field_id' => $cond['field_id'],
                    'operator' => $cond['operator'],
                    'value' => $cond['value'],
                ]);
            }
        });

        return response()->json([
            'success' => true, 
            'message' => 'Alert created successfully.',
            'redirect' => route($this->getRoutePrefix('packet-types.alerts'), $packetType->id)
        ]);
    }

    public function edit(PacketAlert $alert)
    {
        $packetType = $alert->packetType;
        $protocol = $packetType->protocol;
        
        if (!$protocol) {
            return redirect()->back()->with('error', 'Protocol not found for this alert.');
        }

        $fields = $packetType->fields;
        $alert->load('conditions');
        return view('protocol.alerts.create', compact('alert', 'packetType', 'protocol', 'fields'));
    }

    public function update(Request $request, PacketAlert $alert)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'conditions' => 'required|array|min:1',
            'conditions.*.field_id' => 'required|exists:packet_fields,id',
            'conditions.*.operator' => 'required|in:==,!=,<=,>=',
            'conditions.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $alert) {
            $alert->update(['name' => $request->name]);
            $alert->conditions()->delete();

            foreach ($request->conditions as $cond) {
                $alert->conditions()->create([
                    'packet_field_id' => $cond['field_id'],
                    'operator' => $cond['operator'],
                    'value' => $cond['value'],
                ]);
            }
        });

        return response()->json([
            'success' => true, 
            'message' => 'Alert updated successfully.',
            'redirect' => route($this->getRoutePrefix('packet-types.alerts'), $alert->packet_type_id)
        ]);
    }

    public function destroy(PacketAlert $alert)
    {
        $packetTypeId = $alert->packet_type_id;
        $alert->delete();
        return redirect()->route($this->getRoutePrefix('packet-types.alerts'), $packetTypeId)->with('success', 'Alert deleted successfully.');
    }

    private function getRoutePrefix($suffix)
    {
        $prefix = (auth()->user()->user_type == 'Support') ? 'support.protocols' : 'protocols';
        return $prefix . '.' . $suffix;
    }
}
