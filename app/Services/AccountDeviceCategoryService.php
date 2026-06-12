<?php

namespace App\Services;

use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\Writer;
use App\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountDeviceCategoryService
{
    public function getTemplatesForUserCategory(int $userId, int $categoryId)
    {
        return Template::where('id_user', $userId)
            ->where('device_category_id', $categoryId)
            ->where('is_deleted', '0')
            ->where('verify', 2)
            ->orderByDesc('default_template')
            ->orderBy('template_name')
            ->get();
    }

    public function getDefaultTemplateForCategory(int $userId, int $categoryId): ?Template
    {
        $templates = $this->getTemplatesForUserCategory($userId, $categoryId);
        if ($templates->isEmpty()) {
            return null;
        }

        return $templates->firstWhere('default_template', 1) ?? $templates->first();
    }

    public function getAdminDefaultTemplateForCategory(int $categoryId): ?Template
    {
        return Template::where('device_category_id', $categoryId)
            ->where('is_deleted', '0')
            ->where('verify', 1)
            ->orderByDesc('default_template')
            ->orderBy('template_name')
            ->first();
    }

    public function getPingIntervalFieldId(): int
    {
        $field = DB::table('data_fields')
            ->where('is_common', 1)
            ->where(function ($query) {
                $query->where('fieldName', 'ping_interval')
                    ->orWhere('fieldName', 'Ping Interval');
            })
            ->first();

        return $field ? (int) $field->id : 77;
    }

    public function getAdminPingIntervalForCategory(int $categoryId): int
    {
        $template = $this->getAdminDefaultTemplateForCategory($categoryId);
        if (!$template || empty($template->configurations)) {
            return 4;
        }

        $config = json_decode($template->configurations, true);
        if (!is_array($config)) {
            return 4;
        }

        $value = $config['ping_interval']['value'] ?? $config['ping_interval'] ?? null;

        return ($value !== null && $value !== '') ? (int) $value : 4;
    }

    public function buildAdminPingIntervalMap(?array $categoryIds = null): array
    {
        $map = [];
        $ids = $categoryIds ?? DeviceCategory::where('is_deleted', 0)->pluck('id')->all();

        foreach ($ids as $categoryId) {
            $map[(int) $categoryId] = $this->getAdminPingIntervalForCategory((int) $categoryId);
        }

        return $map;
    }

    public function applyAdminPingIntervalToConfiguration(int $categoryId, array $configuration): array
    {
        $configuration['ping_interval'] = [
            'id' => $this->getPingIntervalFieldId(),
            'value' => $this->getAdminPingIntervalForCategory($categoryId),
        ];

        return $configuration;
    }

    /**
     * View-mode data for edit-account page: each enabled category shows the
     * edited account's own default template (not the parent's configuration).
     */
    public function buildCategoryDefaultTemplateViewData(Writer $writer): array
    {
        $viewConfigMap = [];
        $defaultTemplateMap = [];
        $canViewConfigMap = [];

        foreach ($this->parseCategoryIds($writer->device_category_id) as $categoryId) {
            $categoryId = (int) $categoryId;
            $template = $this->getDefaultTemplateForCategory($writer->id, $categoryId);
            if (!$template) {
                continue;
            }

            $defaultTemplateMap[$categoryId] = $template;
            $config = json_decode($template->configurations, true);
            $viewConfigMap[$categoryId] = is_array($config) ? $config : [];

            $canConfig = json_decode($template->can_configurations ?? '', true);
            if (is_array($canConfig) && !empty($canConfig)) {
                $canViewConfigMap[$categoryId] = $canConfig;
            }
        }

        return [
            'viewConfigMap' => $viewConfigMap,
            'defaultTemplateMap' => $defaultTemplateMap,
            'canViewConfigMap' => $canViewConfigMap,
        ];
    }

    public function buildCategoryConfigMap(Writer $writer): array
    {
        $categoryIds = $this->parseCategoryIds($writer->device_category_id);
        $configurations = json_decode($writer->configurations, true) ?: [];
        $map = [];

        foreach ($categoryIds as $index => $categoryId) {
            if (isset($configurations[$index])) {
                $map[(int) $categoryId] = $configurations[$index];
            }
        }

        return $map;
    }

    public function enableCategoryForAccount(int $userId, int $categoryId): array
    {
        $writer = Writer::where('id', $userId)->where('is_deleted', 0)->firstOrFail();
        $category = DeviceCategory::where('id', $categoryId)->where('is_deleted', 0)->firstOrFail();

        $categoryIds = $this->parseCategoryIds($writer->device_category_id);
        $configurations = json_decode($writer->configurations, true) ?: [];
        $canConfigurations = json_decode($writer->can_configurations, true) ?: [];
        $isNewCategory = !in_array((string) $categoryId, array_map('strval', $categoryIds), true);

        if ($isNewCategory) {
            $categoryIds[] = $categoryId;
            $configurations[] = $this->resolveConfigurationForCategory($writer, $category);
            if (!isset($canConfigurations[$categoryId]) && !isset($canConfigurations[(string) $categoryId])) {
                $canConfigurations[$categoryId] = $this->resolveCanConfigurationForCategory($writer, $categoryId);
            }
        }

        $writer->device_category_id = implode(',', $categoryIds);
        $writer->configurations = json_encode(array_values($configurations));
        $writer->can_configurations = json_encode($canConfigurations);
        $writer->save();

        $categoryConfig = $this->getConfigurationForCategory($writer, $categoryId);
        $canConfig = $canConfigurations[$categoryId] ?? $canConfigurations[(string) $categoryId] ?? null;
        $template = $this->upsertDefaultTemplate($writer, $category, $categoryConfig, $canConfig);

        return [
            'template' => $template,
            'templates' => $this->getTemplatesForUserCategory($userId, $categoryId),
        ];
    }

    public function syncDefaultTemplatesFromAccount(Writer $writer): void
    {
        $this->syncTemplatesFromAccount($writer);
    }

    public function syncTemplatesFromAccount(Writer $writer, array $configurationInput = []): void
    {
        $categoryConfigMap = $this->buildCategoryConfigMap($writer);
        $canConfigurations = json_decode($writer->can_configurations, true) ?: [];

        foreach ($this->parseCategoryIds($writer->device_category_id) as $categoryId) {
            $category = DeviceCategory::where('id', (int) $categoryId)->where('is_deleted', 0)->first();
            if (!$category) {
                continue;
            }

            $configuration = $categoryConfigMap[(int) $categoryId] ?? [];
            if (empty($configuration)) {
                continue;
            }

            $canConfig = $canConfigurations[$categoryId] ?? $canConfigurations[(string) $categoryId] ?? null;
            $selectedTemplateId = $configurationInput[$categoryId]['template']
                ?? $configurationInput[(string) $categoryId]['template']
                ?? ($configuration['template']['value'] ?? null);

            if ($this->accountOwnsTemplate((int) $writer->id, (int) $categoryId, $selectedTemplateId)) {
                $this->applyConfigurationToSelectedTemplate(
                    (int) $writer->id,
                    (int) $categoryId,
                    (int) $selectedTemplateId,
                    $configuration,
                    $canConfig
                );
                continue;
            }

            $template = $this->upsertDefaultTemplate($writer, $category, $configuration, $canConfig);
            $this->setConfigurationTemplateReference($writer, (int) $categoryId, (int) $template->id);
        }

        $writer->save();
    }

    public function ensureMissingDefaultTemplates(Writer $writer): void
    {
        $categoryConfigMap = $this->buildCategoryConfigMap($writer);
        if (empty($categoryConfigMap)) {
            return;
        }

        $canConfigurations = json_decode($writer->can_configurations, true) ?: [];

        foreach ($categoryConfigMap as $categoryId => $configuration) {
            if ($this->hasAnyTemplateForCategory($writer->id, (int) $categoryId)) {
                continue;
            }

            $category = DeviceCategory::where('id', $categoryId)->where('is_deleted', 0)->first();
            if (!$category) {
                continue;
            }

            $canConfig = $canConfigurations[$categoryId] ?? $canConfigurations[(string) $categoryId] ?? null;
            $this->upsertDefaultTemplate($writer, $category, $configuration, $canConfig);
        }
    }

    public function disableCategoryForAccount(int $userId, int $categoryId): void
    {
        $writer = Writer::where('id', $userId)->where('is_deleted', 0)->firstOrFail();
        $accounts = $this->collectAccountAndDescendants($writer);

        DB::transaction(function () use ($accounts, $categoryId) {
            foreach ($accounts as $account) {
                $freshAccount = Writer::where('id', $account->id)->where('is_deleted', 0)->first();
                if (!$freshAccount) {
                    continue;
                }

                $this->removeCategoryFromAccount($freshAccount, $categoryId);
            }
        });
    }

    protected function resolveConfigurationForCategory(Writer $writer, DeviceCategory $category): array
    {
        $existingMap = $this->buildCategoryConfigMap($writer);
        if (!empty($existingMap[$category->id])) {
            return $this->applyAdminPingIntervalToConfiguration((int) $category->id, $existingMap[$category->id]);
        }

        if ($writer->created_by) {
            $parent = Writer::where('id', $writer->created_by)->where('is_deleted', 0)->first();
            if ($parent) {
                $parentMap = $this->buildCategoryConfigMap($parent);
                if (!empty($parentMap[$category->id])) {
                    return $this->applyAdminPingIntervalToConfiguration((int) $category->id, $parentMap[$category->id]);
                }
            }
        }

        return $this->buildDefaultConfiguration($category);
    }

    protected function resolveCanConfigurationForCategory(Writer $writer, int $categoryId): array
    {
        $canConfigurations = json_decode($writer->can_configurations, true) ?: [];
        $existing = $canConfigurations[$categoryId] ?? $canConfigurations[(string) $categoryId] ?? null;
        if (!empty($existing)) {
            return is_array($existing) ? $existing : (json_decode($existing, true) ?: []);
        }

        if ($writer->created_by) {
            $parent = Writer::where('id', $writer->created_by)->where('is_deleted', 0)->first();
            if ($parent) {
                $parentCan = json_decode($parent->can_configurations, true) ?: [];
                $parentValue = $parentCan[$categoryId] ?? $parentCan[(string) $categoryId] ?? null;
                if (!empty($parentValue)) {
                    return is_array($parentValue) ? $parentValue : (json_decode($parentValue, true) ?: []);
                }
            }
        }

        return [];
    }

    protected function getConfigurationForCategory(Writer $writer, int $categoryId): array
    {
        $map = $this->buildCategoryConfigMap($writer);

        return $map[$categoryId] ?? [];
    }

    protected function applyConfigurationToSelectedTemplate(
        int $userId,
        int $categoryId,
        int $templateId,
        array $configuration,
        $canConfiguration = null
    ): void {
        $template = $this->templateQueryForUserCategory($userId, $categoryId)
            ->where('id', $templateId)
            ->where('is_deleted', '0')
            ->first();

        if (!$template) {
            return;
        }

        $configForTemplate = $this->applyAdminPingIntervalToConfiguration($categoryId, $configuration);
        unset($configForTemplate['template']);

        $template->configurations = json_encode($configForTemplate);
        if ($canConfiguration !== null) {
            $template->can_configurations = is_array($canConfiguration)
                ? json_encode($canConfiguration)
                : $canConfiguration;
        }

        $this->templateQueryForUserCategory($userId, $categoryId)
            ->where('id', '!=', $templateId)
            ->update(['default_template' => 0]);

        $template->default_template = 1;
        $template->save();
    }

    protected function upsertDefaultTemplate(
        Writer $writer,
        DeviceCategory $category,
        array $configuration,
        $canConfiguration = null
    ): Template {
        if (empty($configuration)) {
            $configuration = $this->resolveConfigurationForCategory($writer, $category);
        }

        $configuration = $this->applyAdminPingIntervalToConfiguration((int) $category->id, $configuration);

        $encodedCan = null;
        if ($canConfiguration !== null) {
            $encodedCan = is_array($canConfiguration) ? json_encode($canConfiguration) : $canConfiguration;
        }

        $defaultTemplate = $this->templateQueryForUserCategory($writer->id, $category->id)
            ->where('is_deleted', '0')
            ->where('default_template', 1)
            ->first();

        if ($defaultTemplate) {
            $defaultTemplate->configurations = json_encode($configuration);
            if ($encodedCan !== null) {
                $defaultTemplate->can_configurations = $encodedCan;
            }
            $defaultTemplate->save();

            return $defaultTemplate;
        }

        $softDeletedTemplate = $this->templateQueryForUserCategory($writer->id, $category->id)
            ->where('is_deleted', '1')
            ->orderByDesc('default_template')
            ->orderByDesc('id')
            ->first();

        if ($softDeletedTemplate) {
            $restorePayload = [
                'configurations' => json_encode($configuration),
                'template_name' => $category->device_category_name . ' Default',
                'default_template' => 1,
                'verify' => 2,
                'is_deleted' => '0',
            ];
            if ($encodedCan !== null) {
                $restorePayload['can_configurations'] = $encodedCan;
            }
            if (Schema::hasColumn('templates', 'active_status')) {
                $restorePayload['active_status'] = 1;
            }

            Template::where('id', $softDeletedTemplate->id)->update($restorePayload);

            $this->templateQueryForUserCategory($writer->id, $category->id)
                ->where('id', '!=', $softDeletedTemplate->id)
                ->update(['default_template' => 0]);

            return $softDeletedTemplate->fresh();
        }

        $this->templateQueryForUserCategory($writer->id, $category->id)
            ->where('is_deleted', '0')
            ->update(['default_template' => 0]);

        return Template::create([
            'id_user' => $writer->id,
            'template_name' => $category->device_category_name . ' Default',
            'device_category_id' => $category->id,
            'configurations' => json_encode($configuration),
            'can_configurations' => $encodedCan,
            'default_template' => 1,
            'verify' => 2,
            'is_deleted' => 0,
        ]);
    }

    protected function buildDefaultConfiguration(DeviceCategory $category): array
    {
        $inputs = json_decode($category->inputs, true) ?: [];
        $configuration = [];

        foreach ($inputs as $input) {
            $key = strtolower(str_replace(' ', '_', $input['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            $configuration[$key] = [
                'id' => $input['id'] ?? null,
                'value' => $input['default'] ?? '',
            ];
        }

        $commonFields = DB::table('data_fields')->where('is_common', 1)->get();
        foreach ($commonFields as $field) {
            $key = strtolower(str_replace(' ', '_', $field->fieldName));
            if ($key === 'is_editable') {
                $configuration[$key] = [
                    'id' => $field->id,
                    'value' => 1,
                ];
            }
        }

        return $this->applyAdminPingIntervalToConfiguration((int) $category->id, $configuration);
    }

    protected function collectAccountAndDescendants(Writer $writer): array
    {
        $accounts = [];
        $visited = [];
        $queue = [$writer->id];

        while (!empty($queue)) {
            $parentId = array_shift($queue);
            if (isset($visited[$parentId])) {
                continue;
            }

            $visited[$parentId] = true;
            $account = Writer::where('id', $parentId)->where('is_deleted', 0)->first();
            if ($account) {
                $accounts[] = $account;
            }

            $children = Writer::where('is_deleted', 0)
                ->where(function ($query) use ($parentId) {
                    $query->where('created_by', $parentId)
                        ->orWhere('parent_user_id', $parentId);
                })
                ->get();

            foreach ($children as $child) {
                if (!isset($visited[$child->id])) {
                    $queue[] = $child->id;
                }
            }
        }

        return $accounts;
    }

    protected function removeCategoryFromAccount(Writer $account, int $categoryId): void
    {
        $categoryIds = $this->parseCategoryIds($account->device_category_id);
        $configurations = json_decode($account->configurations, true) ?: [];
        $canConfigurations = json_decode($account->can_configurations, true) ?: [];

        foreach ($categoryIds as $index => $id) {
            if ((int) $id === (int) $categoryId) {
                unset($categoryIds[$index]);
                unset($configurations[$index]);
            }
        }

        unset($canConfigurations[$categoryId]);
        unset($canConfigurations[(string) $categoryId]);

        $this->deleteTemplatesForCategory($account->id, $categoryId);

        $account->device_category_id = implode(',', array_values($categoryIds));
        $account->configurations = json_encode(array_values($configurations));
        $account->can_configurations = json_encode($canConfigurations);
        $account->save();
    }

    protected function templateQueryForUserCategory(int $userId, int $categoryId)
    {
        return Template::where('device_category_id', $categoryId)
            ->where(function ($query) use ($userId) {
                $query->where('id_user', $userId);
                if (Schema::hasColumn('templates', 'user_id')) {
                    $query->orWhere('user_id', $userId);
                }
            });
    }

    protected function hasAnyTemplateForCategory(int $userId, int $categoryId): bool
    {
        return $this->templateQueryForUserCategory($userId, $categoryId)->exists();
    }

    protected function deleteTemplatesForCategory(int $userId, int $categoryId): void
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

        $this->templateQueryForUserCategory($userId, $categoryId)->update($payload);
    }

    public function getRemovedCategoryIds(Writer $writer, array $newCategoryIds): array
    {
        $oldCategoryIds = array_map('strval', $this->parseCategoryIds($writer->device_category_id));
        $normalizedNew = array_map('strval', array_values(array_filter($newCategoryIds, function ($id) {
            return $id !== null && $id !== '';
        })));

        return array_values(array_diff($oldCategoryIds, $normalizedNew));
    }

    public function getAddedCategoryIds(Writer $writer, array $newCategoryIds): array
    {
        $oldCategoryIds = array_map('strval', $this->parseCategoryIds($writer->device_category_id));
        $normalizedNew = array_map('strval', array_values(array_filter($newCategoryIds, function ($id) {
            return $id !== null && $id !== '';
        })));

        return array_values(array_diff($normalizedNew, $oldCategoryIds));
    }

    public function applyAdminOnlyConfigurationFields(Writer $writer, array $formattedRows, array $deviceCategoryIds): void
    {
        $existingMap = $this->buildCategoryConfigMap($writer);

        foreach ($deviceCategoryIds as $index => $categoryId) {
            $categoryId = (int) $categoryId;
            $row = $formattedRows[$index] ?? null;
            if (!$row) {
                continue;
            }

            $rowArray = is_object($row) ? (array) $row : $row;
            if (!isset($existingMap[$categoryId])) {
                continue;
            }

            if (isset($rowArray['ping_interval'])) {
                $existingMap[$categoryId]['ping_interval'] = $rowArray['ping_interval'];
            }
            if (isset($rowArray['is_editable'])) {
                $existingMap[$categoryId]['is_editable'] = $rowArray['is_editable'];
            }
        }

        $orderedConfigs = [];
        foreach ($deviceCategoryIds as $categoryId) {
            $categoryId = (int) $categoryId;
            if (isset($existingMap[$categoryId])) {
                $orderedConfigs[] = $existingMap[$categoryId];
            }
        }

        $writer->configurations = json_encode($orderedConfigs);
    }

    public function getTemplatesForParentCategory(Writer $account, int $categoryId)
    {
        if (!$account->created_by) {
            return collect();
        }

        $parent = Writer::where('id', $account->created_by)->where('is_deleted', 0)->first();
        if (!$parent) {
            return collect();
        }

        return $this->getTemplatesForUserCategory((int) $parent->id, $categoryId);
    }

    public function getAdminTemplatesForCategory(int $categoryId)
    {
        return Template::where('device_category_id', $categoryId)
            ->where('is_deleted', '0')
            ->where('verify', 1)
            ->whereNull('id_user')
            ->orderByDesc('default_template')
            ->orderBy('template_name')
            ->get();
    }

    public function resolveParentDefaultTemplate(Writer $account, int $categoryId): ?Template
    {
        $parentTemplates = $this->getTemplatesForParentCategory($account, $categoryId);
        if ($parentTemplates->isNotEmpty()) {
            return $parentTemplates->firstWhere('default_template', 1) ?? $parentTemplates->first();
        }

        $adminTemplates = $this->getAdminTemplatesForCategory($categoryId);

        return $adminTemplates->firstWhere('default_template', 1) ?? $adminTemplates->first();
    }

    public function accountOwnsTemplate(int $userId, int $categoryId, $templateId): bool
    {
        if ($templateId === null || $templateId === '') {
            return false;
        }

        return $this->templateQueryForUserCategory($userId, $categoryId)
            ->where('id', (int) $templateId)
            ->where('is_deleted', '0')
            ->exists();
    }

    public function setConfigurationTemplateReference(Writer $writer, int $categoryId, int $templateId): void
    {
        $categoryIds = $this->parseCategoryIds($writer->device_category_id);
        $configurations = json_decode($writer->configurations, true) ?: [];

        foreach ($categoryIds as $index => $id) {
            if ((int) $id !== (int) $categoryId || !isset($configurations[$index])) {
                continue;
            }

            $configurations[$index]['template'] = [
                'id' => null,
                'value' => (string) $templateId,
            ];
            break;
        }

        $writer->configurations = json_encode(array_values($configurations));
    }

    public function stripForeignTemplateReferences(Writer $writer, array $configuration): array
    {
        foreach ($configuration as $categoryId => $config) {
            if (!is_array($config)) {
                continue;
            }

            $templateId = $config['template'] ?? null;
            if (!$this->accountOwnsTemplate((int) $writer->id, (int) $categoryId, $templateId)) {
                unset($configuration[$categoryId]['template']);
            }
        }

        return $configuration;
    }

    public function buildMultipleCategoryTemplatePayload(Writer $account, array $categoryIds): array
    {
        $templates = [];
        $parentTemplates = [];
        $parentCanConfigs = [];
        $templatesAreParentSourced = [];

        foreach ($categoryIds as $categoryId) {
            $categoryId = (int) $categoryId;
            $userTemplates = $this->getTemplatesForUserCategory((int) $account->id, $categoryId);
            $selectableTemplates = $userTemplates;
            $isParentSourced = false;

            if ($userTemplates->isEmpty()) {
                $parentAccountTemplates = $this->getTemplatesForParentCategory($account, $categoryId);
                if ($parentAccountTemplates->isNotEmpty()) {
                    $selectableTemplates = $parentAccountTemplates;
                    $isParentSourced = true;
                } else {
                    $selectableTemplates = $this->getAdminTemplatesForCategory($categoryId);
                    $isParentSourced = $selectableTemplates->isNotEmpty();
                }
            }

            $templates[] = $selectableTemplates;
            $templatesAreParentSourced[] = $isParentSourced;
            $parentTemplates[] = $this->resolveParentDefaultTemplate($account, $categoryId);

            $defaultTemplate = $isParentSourced
                ? $this->resolveParentDefaultTemplate($account, $categoryId)
                : ($userTemplates->firstWhere('default_template', 1) ?? $userTemplates->first());

            if ($defaultTemplate && !empty($defaultTemplate->can_configurations)) {
                $canConfig = json_decode($defaultTemplate->can_configurations, true);
                $parentCanConfigs[$categoryId] = is_array($canConfig) ? $canConfig : [];
            } else {
                $parentCanConfigs[$categoryId] = $this->resolveCanConfigurationForCategory($account, $categoryId);
            }
        }

        return [
            'templates' => $templates,
            'parentTemplates' => $parentTemplates,
            'parentCanConfigs' => $parentCanConfigs,
            'templatesAreParentSourced' => $templatesAreParentSourced,
        ];
    }

    public function mergeNewCategoryConfigurations(
        Writer $writer,
        array $formattedRows,
        array $deviceCategoryIds,
        array $addedCategoryIds,
        array $canConfiguration = []
    ): void {
        if (empty($addedCategoryIds)) {
            return;
        }

        $existingMap = $this->buildCategoryConfigMap($writer);
        $addedSet = array_map('strval', $addedCategoryIds);

        foreach ($deviceCategoryIds as $index => $categoryId) {
            $categoryId = (int) $categoryId;
            if (!in_array((string) $categoryId, $addedSet, true)) {
                continue;
            }

            $row = $formattedRows[$index] ?? null;
            if (!$row) {
                continue;
            }

            $rowArray = is_object($row) ? (array) $row : $row;
            $templateValue = $rowArray['template']['value'] ?? $rowArray['template'] ?? null;
            if (!$this->accountOwnsTemplate((int) $writer->id, $categoryId, $templateValue)) {
                unset($rowArray['template']);
            }

            $existingMap[$categoryId] = $rowArray;
        }

        $orderedConfigs = [];
        foreach ($deviceCategoryIds as $categoryId) {
            $categoryId = (int) $categoryId;
            if (isset($existingMap[$categoryId])) {
                $orderedConfigs[] = $existingMap[$categoryId];
            }
        }

        $writer->configurations = json_encode($orderedConfigs);

        if (empty($canConfiguration)) {
            return;
        }

        $existingCan = json_decode($writer->can_configurations, true) ?: [];
        foreach ($addedCategoryIds as $addedId) {
            $addedId = (int) $addedId;
            if (isset($canConfiguration[$addedId])) {
                $existingCan[$addedId] = $canConfiguration[$addedId];
            } elseif (isset($canConfiguration[(string) $addedId])) {
                $existingCan[$addedId] = $canConfiguration[(string) $addedId];
            }
        }

        $writer->can_configurations = json_encode($existingCan);
    }

    protected function parseCategoryIds(?string $deviceCategoryIds): array
    {
        if ($deviceCategoryIds === null || trim($deviceCategoryIds) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $deviceCategoryIds)), function ($id) {
            return $id !== '';
        }));
    }
}
