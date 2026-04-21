<?php

namespace App\Http\Controllers;

use App\Models\ImeiDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ImeiDeviceController extends Controller
{
    public function index()
    {
        $devices = ImeiDevice::withCount([
            'commands as pending_commands_count' => function ($query) {
                $query->where('status', 0);
            }
        ])->latest()->get();

        return view('imei.index', compact('devices'));
    }

    public function create()
    {
        return view('imei.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTracker($request);
        ImeiDevice::create($validated);

        $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', 'Tracker added successfully.');
    }

    public function edit(ImeiDevice $imei_device)
    {
        return view('imei.edit', compact('imei_device'));
    }

    public function update(Request $request, ImeiDevice $imei_device)
    {
        $validated = $this->validateTracker($request, $imei_device->id);
        $imei_device->update($validated);

        $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', 'Tracker updated successfully.');
    }

    public function destroy(ImeiDevice $imei_device)
    {
        $imei_device->delete();
        $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', 'Tracker deleted successfully.');
    }

    public function toggleStatus(ImeiDevice $imei_device)
    {
        $nextStatus = $imei_device->status === ImeiDevice::STATUS_ON
            ? ImeiDevice::STATUS_OFF
            : ImeiDevice::STATUS_ON;

        $imei_device->update(['status' => $nextStatus]);
        return back()->with('success', 'Recording changed to ' . ($nextStatus === ImeiDevice::STATUS_ON ? 'ON' : 'OFF'));
    }

    protected function validateTracker(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'imei' => [
                'required',
                'digits:15',
                Rule::unique('imei_devices', 'imei')->ignore($ignoreId),
            ],
            'status' => ['required', Rule::in([ImeiDevice::STATUS_ON, ImeiDevice::STATUS_OFF, ImeiDevice::STATUS_CLOSE])],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ]);

        $startAt = \App\Helper\CommonHelper::convertLocalToUTC($validated['start_at']);
        $endAt = \App\Helper\CommonHelper::convertLocalToUTC($validated['end_at']);

        if ($endAt->gt($startAt->copy()->addDays(7))) {
            throw ValidationException::withMessages([
                'end_at' => 'End Date & Time must be within 7 days from Start Date & Time.',
            ]);
        }

        $validated['schedule_start'] = $startAt;
        $validated['schedule_end'] = $endAt;

        if (Schema::hasColumn('imei_devices', 'start_at')) {
            $validated['start_at'] = $startAt;
        }

        if (Schema::hasColumn('imei_devices', 'end_at')) {
            $validated['end_at'] = $endAt;
        }

        return $validated;
    }
}
