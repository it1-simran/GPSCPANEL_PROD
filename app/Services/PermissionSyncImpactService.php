<?php

namespace App\Services;

use App\DeviceCategory;
use App\Permission;
use App\Template;
use App\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSyncImpactService
{
    private PermissionAssignmentService $assignmentService;

    private AccountDeviceCategoryService $categoryService;

    public function __construct(
        PermissionAssignmentService $assignmentService = null,
        AccountDeviceCategoryService $categoryService = null
    ) {
        $this->assignmentService = $assignmentService ?? new PermissionAssignmentService();
        $this->categoryService = $categoryService ?? app(AccountDeviceCategoryService::class);
    }

    /**
     * Preview device categories and templates that will be removed from child users.
     */
    public function previewImpact($targetUser, array $requestedPermissionIds, $assigningUser = null): array
    {
        $plan = $this->assignmentService->prepareSyncPlan($targetUser, $requestedPermissionIds, $assigningUser);
        if (!$plan['success']) {
            return [
                'success' => false,
                'message' => $plan['message'],
                'hasImpact' => false,
                'childUsers' => [],
            ];
        }

        $toRemoveWithDependents = $plan['toRemoveWithDependents'] ?? [];
        if (empty($toRemoveWithDependents)) {
            return [
                'success' => true,
                'hasImpact' => false,
                'childUsers' => [],
            ];
        }

        $settingsViewId = $this->permissionId('settings_management.view');
        $deviceViewId = $this->permissionId('device_management.view');
        $removingSettingsView = $settingsViewId && in_array($settingsViewId, $toRemoveWithDependents, true);
        $removingDeviceView = $deviceViewId && in_array($deviceViewId, $toRemoveWithDependents, true);

        if (!$removingSettingsView && !$removingDeviceView) {
            return [
                'success' => true,
                'hasImpact' => false,
                'childUsers' => [],
            ];
        }

        $affectedUsers = $this->collectAffectedUsers($targetUser, $toRemoveWithDependents);
        if ($targetUser->user_type === 'Reseller') {
            $affectedUsers = array_values(array_filter(
                $affectedUsers,
                fn(Writer $user) => (int) $user->id !== (int) $targetUser->id
            ));
        }

        $childUsers = [];

        foreach ($affectedUsers as $user) {
            $impact = $this->buildUserImpact($user, $removingSettingsView, $removingDeviceView);
            if (empty($impact['deviceCategories']) && empty($impact['templates'])) {
                continue;
            }

            $childUsers[] = [
                'user_id' => (int) $user->id,
                'user_name' => $user->name,
                'deviceCategories' => $impact['deviceCategories'],
                'templates' => $impact['templates'],
            ];
        }

        return [
            'success' => true,
            'hasImpact' => !empty($childUsers),
            'childUsers' => $childUsers,
            'removingSettingsView' => $removingSettingsView,
            'removingDeviceView' => $removingDeviceView,
        ];
    }

    /**
     * Remove device categories / templates after a successful permission sync.
     */
    public function applyImpact($targetUser, array $requestedPermissionIds, $assigningUser = null): void
    {
        $preview = $this->previewImpact($targetUser, $requestedPermissionIds, $assigningUser);
        if (!$preview['success'] || empty($preview['hasImpact'])) {
            return;
        }

        $removingDeviceView = !empty($preview['removingDeviceView']);
        $removingSettingsView = !empty($preview['removingSettingsView']);

        foreach ($preview['childUsers'] as $childImpact) {
            $userId = (int) $childImpact['user_id'];

            if ($removingDeviceView) {
                foreach ($childImpact['deviceCategories'] as $category) {
                    $this->categoryService->disableCategoryForAccount($userId, (int) $category['id']);
                }
                continue;
            }

            if ($removingSettingsView) {
                foreach ($childImpact['templates'] as $template) {
                    $this->softDeleteTemplate((int) $template['id']);
                }
            }
        }
    }

    /**
     * @return Writer[]
     */
    private function collectAffectedUsers(Writer $targetUser, array $toRemoveWithDependents): array
    {
        $users = [];
        $candidateIds = array_merge([$targetUser->id], $this->collectDescendantIds($targetUser->id));

        foreach (array_unique($candidateIds) as $userId) {
            $hasRemovedPermission = DB::table('user_permissions')
                ->where('user_id', $userId)
                ->whereIn('permission_id', $toRemoveWithDependents)
                ->exists();

            if (!$hasRemovedPermission) {
                continue;
            }

            $user = Writer::where('id', $userId)->where('is_deleted', 0)->first();
            if ($user) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @return int[]
     */
    private function collectDescendantIds(int $parentUserId): array
    {
        $ids = [];
        $queue = [$parentUserId];
        $visited = [];

        while (!empty($queue)) {
            $parentId = array_shift($queue);
            if (isset($visited[$parentId])) {
                continue;
            }
            $visited[$parentId] = true;

            $childIds = DB::table('writers')
                ->where('is_deleted', 0)
                ->where(function ($query) use ($parentId) {
                    $query->where('parent_user_id', $parentId)
                        ->orWhere('created_by', $parentId);
                })
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            foreach ($childIds as $childId) {
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    private function buildUserImpact(Writer $user, bool $removingSettingsView, bool $removingDeviceView): array
    {
        $deviceCategories = [];
        $templates = [];
        $categoryIds = $this->parseCategoryIds($user->device_category_id);

        foreach ($categoryIds as $categoryId) {
            $category = DeviceCategory::where('id', (int) $categoryId)->where('is_deleted', 0)->first();
            if (!$category) {
                continue;
            }

            $userTemplates = $this->categoryService
                ->getTemplatesForUserCategory((int) $user->id, (int) $categoryId)
                ->map(function ($template) use ($category) {
                    return [
                        'id' => (int) $template->id,
                        'name' => $template->template_name,
                        'category_id' => (int) $category->id,
                        'category_name' => $category->device_category_name,
                    ];
                })
                ->values()
                ->all();

            if ($removingDeviceView) {
                $deviceCategories[] = [
                    'id' => (int) $category->id,
                    'name' => $category->device_category_name,
                ];
            }

            if ($removingSettingsView) {
                $templates = array_merge($templates, $userTemplates);
            } elseif ($removingDeviceView) {
                $templates = array_merge($templates, $userTemplates);
            }
        }

        return [
            'deviceCategories' => $deviceCategories,
            'templates' => $templates,
        ];
    }

    private function permissionId(string $key): ?int
    {
        $id = Permission::where('key', $key)->where('is_active', 1)->value('id');

        return $id ? (int) $id : null;
    }

    private function parseCategoryIds(?string $deviceCategoryIds): array
    {
        if ($deviceCategoryIds === null || trim($deviceCategoryIds) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $deviceCategoryIds)), function ($id) {
            return $id !== '';
        }));
    }

    private function softDeleteTemplate(int $templateId): void
    {
        $payload = ['is_deleted' => '1'];

        if (Schema::hasColumn('templates', 'deleted_at')) {
            $payload['deleted_at'] = now();
        }
        if (Schema::hasColumn('templates', 'active_status')) {
            $payload['active_status'] = 0;
        }
        if (Schema::hasColumn('templates', 'default_template')) {
            $payload['default_template'] = 0;
        }

        Template::where('id', $templateId)->update($payload);
    }
}
