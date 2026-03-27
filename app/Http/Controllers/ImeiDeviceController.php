<?php

namespace App\Http\Controllers;

use App\Models\ImeiDevice;
use Illuminate\Http\Request;

class ImeiDeviceController extends Controller
{
    public function index()
    {
        $devices = ImeiDevice::latest()->paginate(15);
        return view('imei.index', compact('devices'));
    }

    public function create()
    {
        return view('imei.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'imei' => 'required|string|unique:imei_devices,imei',
            'status' => 'required|in:active,inactive,close',
            'schedule_start' => 'nullable|date',
            'schedule_end' => 'nullable|date|after_or_equal:schedule_start',
        ]);

        ImeiDevice::create($validated);
        return redirect()->route('imei-devices.index')->with('success', 'Tracker added successfully.');
    }

    public function edit(ImeiDevice $imei_device)
    {
        return view('imei.edit', compact('imei_device'));
    }

    public function update(Request $request, ImeiDevice $imei_device)
    {
        $validated = $request->validate([
            'imei' => 'required|string|unique:imei_devices,imei,' . $imei_device->id,
            'status' => 'required|in:active,inactive,close',
            'schedule_start' => 'nullable|date',
            'schedule_end' => 'nullable|date|after_or_equal:schedule_start',
        ]);

        $imei_device->update($validated);
        return redirect()->route('imei-devices.index')->with('success', 'Tracker updated successfully.');
    }

    public function destroy(ImeiDevice $imei_device)
    {
        $imei_device->delete();
        return redirect()->route('imei-devices.index')->with('success', 'Tracker deleted successfully.');
    }

    public function toggleStatus(ImeiDevice $imei_device)
    {
        $nextStatus = 'active';
        if ($imei_device->status === 'active') {
            $nextStatus = 'inactive';
        } elseif ($imei_device->status === 'inactive') {
            $nextStatus = 'close';
        }

        $imei_device->update(['status' => $nextStatus]);
        return back()->with('success', 'Status changed to ' . ucfirst($nextStatus));
    }
}
