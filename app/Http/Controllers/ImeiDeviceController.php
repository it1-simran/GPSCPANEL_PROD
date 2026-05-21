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
        ImeiDevice::syncExpiredStatus();

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
        $device = ImeiDevice::create($validated);

        $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
        
        $message = 'Tracker added successfully.';
        if ($device->status === ImeiDevice::STATUS_ON) {
            if ($device->effective_start_at && $device->effective_start_at->isFuture()) {
                $message = 'Device future ke liye auto set hai. Recording will start at ' . \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_start_at, 'd-M-Y H:i:s') . '.';
            }
        }

        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', $message);
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
        
        $message = 'Tracker updated successfully.';
        if ($imei_device->status === ImeiDevice::STATUS_ON) {
            if ($imei_device->effective_start_at && $imei_device->effective_start_at->isFuture()) {
                $message = 'Device future ke liye auto set hai. Recording will start at ' . \App\Helper\CommonHelper::getDateAsTimeZone($imei_device->effective_start_at, 'd-M-Y H:i:s') . '.';
            }
        }

        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', $message);
    }

    public function destroy(ImeiDevice $imei_device)
    {
        $imei_device->delete();
        $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
        return redirect()->route($routePrefix . 'imei-devices.index')->with('success', 'Tracker deleted successfully.');
    }

    public function toggleStatus(ImeiDevice $imei_device)
    {
        ImeiDevice::syncExpiredStatus();
        $imei_device->refresh();

        if ($imei_device->status === ImeiDevice::STATUS_ON) {
            $nextStatus = ImeiDevice::STATUS_OFF;
        } else {
            if ($imei_device->recordingHasExpired()) {
                return back()->with('error', 'Cannot turn ON this tracker because its end date has expired.');
            }
            $nextStatus = ImeiDevice::STATUS_ON;
        }

        $imei_device->update(['status' => $nextStatus]);

        if ($nextStatus === ImeiDevice::STATUS_ON) {
            if ($imei_device->effective_start_at && $imei_device->effective_start_at->isFuture()) {
                return back()->with('success', 'Device future ke liye auto set hai. Recording will start at ' . \App\Helper\CommonHelper::getDateAsTimeZone($imei_device->effective_start_at, 'd-M-Y H:i:s') . '.');
            }
            return back()->with('success', 'Recording changed to ON.');
        }

        return back()->with('success', 'Recording changed to OFF.');
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

        if ($validated['status'] === ImeiDevice::STATUS_ON && $endAt->isPast()) {
            throw ValidationException::withMessages([
                'status' => 'Cannot set tracker status to ON because the selected End Date & Time is in the past.',
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
