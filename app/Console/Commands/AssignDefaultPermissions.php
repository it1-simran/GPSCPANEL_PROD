<?php

namespace App\Console\Commands;

use App\Role;
use App\Writer;
use App\Services\PermissionAssignmentService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignDefaultPermissions extends Command
{
    protected $signature = 'permissions:assign-defaults
                            {--types=Reseller,User : Comma-separated user types (Reseller=Manufacturer, User=Dealer)}
                            {--dry-run : Preview changes without writing to the database}
                            {--replace : Replace all permissions with role defaults instead of merging}';

    protected $description = 'Assign default role-based permissions to existing Reseller and Dealer accounts (certificate view disabled by default)';

    private const USER_TYPE_ROLE_SLUG = [
        'Reseller' => 'reseller',
        'User' => 'user',
    ];

    public function handle(PermissionAssignmentService $service): int
    {
        $types = array_values(array_filter(array_map('trim', explode(',', $this->option('types')))));
        $dryRun = (bool) $this->option('dry-run');
        $replace = (bool) $this->option('replace');

        $invalidTypes = array_diff($types, array_keys(self::USER_TYPE_ROLE_SLUG));
        if ($invalidTypes) {
            $this->error('Invalid user types: ' . implode(', ', $invalidTypes));
            $this->line('Allowed values: Reseller (Manufacturer), User (Dealer)');

            return 1;
        }

        if (!$this->ensureRoleDefaultsSeeded($types)) {
            return 1;
        }

        $users = Writer::whereIn('user_type', $types)
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No matching accounts found.');

            return 0;
        }

        $this->info(sprintf(
            '%s %d account(s) (%s)...',
            $dryRun ? 'Previewing' : 'Processing',
            $users->count(),
            implode(', ', $types)
        ));

        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $defaultIds = $service->getDefaultPermissionIdsForExistingUser($user);

            if (empty($defaultIds)) {
                $this->warn("  [{$user->id}] {$user->email} — no defaults for user_type '{$user->user_type}', skipped");
                $skipped++;
                continue;
            }

            $existingIds = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->pluck('permission_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $mergedIds = $replace
                ? $defaultIds
                : array_values(array_unique(array_merge($defaultIds, $existingIds)));

            // Enforce policy: certificate view stays disabled unless explicitly re-enabled later via UI.
            $finalIds = $service->stripResellerDealerDefaultExclusions($mergedIds);

            $added = count(array_diff($finalIds, $existingIds));
            $removed = count(array_diff($existingIds, $finalIds));

            if ($added === 0 && $removed === 0) {
                $this->line("  [{$user->id}] {$user->email} ({$user->user_type}) — already up to date");
                $skipped++;
                continue;
            }

            $label = $user->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer';
            $action = $dryRun ? 'would update' : 'updated';
            $this->info("  [{$user->id}] {$user->email} ({$label}) — {$action}: +{$added}" . ($removed > 0 ? ", -{$removed}" : ''));

            if (!$dryRun) {
                $user->permissions()->sync($finalIds);

                $roleSlug = self::USER_TYPE_ROLE_SLUG[$user->user_type];
                $roleId = Role::where('slug', $roleSlug)->value('id');
                if ($roleId && !$user->role_id) {
                    $user->role_id = $roleId;
                    $user->save();
                }
            }

            $updated++;
        }

        $this->newLine();
        $this->info($dryRun
            ? "Dry run complete. {$updated} account(s) would be updated, {$skipped} unchanged."
            : "Done. {$updated} account(s) updated, {$skipped} unchanged.");

        return 0;
    }

    private function ensureRoleDefaultsSeeded(array $types): bool
    {
        $needsSeed = false;

        foreach ($types as $type) {
            $roleSlug = self::USER_TYPE_ROLE_SLUG[$type];
            $role = Role::where('slug', $roleSlug)->first();

            if (!$role || DB::table('role_permissions')->where('role_id', $role->id)->count() === 0) {
                $needsSeed = true;
                break;
            }
        }

        if (!$needsSeed) {
            return true;
        }

        $this->warn('Role defaults missing — running PermissionSeeder...');
        $this->callSilent('db:seed', ['--class' => PermissionSeeder::class]);

        foreach ($types as $type) {
            $roleSlug = self::USER_TYPE_ROLE_SLUG[$type];
            $role = Role::where('slug', $roleSlug)->first();

            if (!$role) {
                $this->error("Role '{$roleSlug}' still not found after seeding.");

                return false;
            }

            if (DB::table('role_permissions')->where('role_id', $role->id)->count() === 0) {
                $this->error("No permissions configured for role '{$roleSlug}' after seeding.");

                return false;
            }
        }

        $this->info('Role defaults seeded successfully.');

        return true;
    }
}
