<?php

namespace App\Services;

use App\Device;
use App\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DeviceCategoryAccessService
{
    public function restrictsByCategory(?Writer $user): bool
    {
        if (!$user || $user->user_type === 'Admin') {
            return false;
        }

        return in_array($user->user_type, ['User', 'Reseller', 'Support'], true);
    }

    public function parseEnabledCategoryIds(?Writer $user): array
    {
        if (!$user || !$this->restrictsByCategory($user)) {
            return [];
        }

        if ($user->device_category_id === null || trim($user->device_category_id) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $user->device_category_id)), function ($id) {
            return $id !== '';
        }));
    }

    public function userHasCategory(?Writer $user, $categoryId): bool
    {
        if (!$this->restrictsByCategory($user)) {
            return true;
        }

        $enabled = $this->parseEnabledCategoryIds($user);
        if (empty($enabled)) {
            return false;
        }

        return in_array((string) $categoryId, array_map('strval', $enabled), true);
    }

    public function userCanAccessDevice(?Writer $user, ?Device $device): bool
    {
        if (!$device || (int) $device->is_deleted === 1) {
            return false;
        }

        if (!$this->restrictsByCategory($user)) {
            return true;
        }

        return $this->userHasCategory($user, $device->device_category_id);
    }

    public function applyCategoryScopeToQuery($query, ?Writer $user, string $column = 'devices.device_category_id'): void
    {
        if (!$this->restrictsByCategory($user)) {
            return;
        }

        $enabled = $this->parseEnabledCategoryIds($user);
        if (empty($enabled)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($column, $enabled);
    }

    public function filterDeviceCollection(iterable $devices, ?Writer $user): array
    {
        if (!$this->restrictsByCategory($user)) {
            return is_array($devices) ? $devices : iterator_to_array($devices);
        }

        $enabled = array_map('strval', $this->parseEnabledCategoryIds($user));
        if (empty($enabled)) {
            return [];
        }

        $filtered = [];
        foreach ($devices as $device) {
            if (in_array((string) $device->device_category_id, $enabled, true)) {
                $filtered[] = $device;
            }
        }

        return $filtered;
    }

    public function denyDeviceCategoryAccessResponse(): Response|JsonResponse
    {
        $message = 'You do not have access to this device category. Please contact your administrator.';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'message' => $message,
                'error' => 'device_category_disabled',
            ], 403);
        }

        return response()->view('unauthorized_access', [
            'error' => 403,
            'error_msg' => $message,
        ], 403);
    }

    public function authorizeDeviceAccess($device): Response|JsonResponse|null
    {
        $deviceModel = $device instanceof Device ? $device : Device::find($device);

        if (!$deviceModel || (int) $deviceModel->is_deleted === 1) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['message' => 'Device not found.'], 404);
            }

            return response()->view('unauthorized_access', [
                'error' => 404,
                'error_msg' => 'Device not found.',
            ], 404);
        }

        if (!$this->userCanAccessDevice(Auth::user(), $deviceModel)) {
            return $this->denyDeviceCategoryAccessResponse();
        }

        return null;
    }
}
