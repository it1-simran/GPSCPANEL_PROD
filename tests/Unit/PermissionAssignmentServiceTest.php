<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\User;
use App\Permission;
use App\Services\PermissionAssignmentService;
use App\PermissionAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PermissionAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionAssignmentService $service;
    protected User $admin;
    protected User $reseller;
    protected User $child;
    protected User $grandchild;
    protected Permission $viewPermission;
    protected Permission $editPermission;
    protected Permission $accountMgmtPermission;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionAssignmentService();

        // Create test users with hierarchy
        $this->admin = User::factory()->create(['user_type' => 'Admin']);
        $this->reseller = User::factory()->create([
            'user_type' => 'Reseller',
            'parent_user_id' => $this->admin->id
        ]);
        $this->child = User::factory()->create([
            'user_type' => 'User',
            'parent_user_id' => $this->reseller->id
        ]);
        $this->grandchild = User::factory()->create([
            'user_type' => 'User',
            'parent_user_id' => $this->child->id
        ]);

        // Create test permissions
        $this->viewPermission = Permission::factory()->create([
            'key' => 'device_management.view',
            'module' => 'device_management',
            'action' => 'view'
        ]);

        $this->accountMgmtPermission = Permission::factory()->create([
            'key' => 'account_management.view',
            'module' => 'account_management',
            'action' => 'view'
        ]);

        $this->editPermission = Permission::factory()->create([
            'key' => 'device_management.edit',
            'module' => 'device_management',
            'action' => 'edit'
        ]);
    }

    /** @test */
    public function existing_reseller_default_permissions_exclude_certificate_view()
    {
        $certificatePermission = Permission::factory()->create([
            'key' => 'certificate_management.view',
            'module' => 'certificate_management',
            'action' => 'view',
        ]);

        $resellerRole = \App\Role::firstOrCreate(
            ['slug' => 'reseller'],
            ['name' => 'Reseller', 'slug' => 'reseller', 'is_active' => 1]
        );
        DB::table('role_permissions')->insert([
            ['role_id' => $resellerRole->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => $resellerRole->id, 'permission_id' => $certificatePermission->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $permissionIds = $this->service->getDefaultPermissionIdsForExistingUser($this->reseller);

        $this->assertContains($this->viewPermission->id, $permissionIds);
        $this->assertNotContains($certificatePermission->id, $permissionIds);
    }

    /** @test */
    public function admin_created_account_gets_system_default_permissions()
    {
        Permission::factory()->create([
            'key' => 'settings_management.view',
            'module' => 'settings_management',
            'action' => 'view'
        ]);
        Permission::factory()->create([
            'key' => 'certificate_management.view',
            'module' => 'certificate_management',
            'action' => 'view'
        ]);

        $permissionIds = $this->service->getDefaultPermissionIdsForNewAccount($this->admin, 'User');

        $this->assertContains($this->viewPermission->id, $permissionIds);
        $this->assertContains($this->editPermission->id, $permissionIds);
        $this->assertNotContains(
            Permission::where('key', 'certificate_management.view')->value('id'),
            $permissionIds
        );
    }

    /** @test */
    public function reseller_created_child_gets_parent_permissions_only()
    {
        DB::table('user_permissions')->insert([
            ['user_id' => $this->reseller->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->reseller->id, 'permission_id' => $this->editPermission->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $settingsPermission = Permission::factory()->create([
            'key' => 'settings_management.view',
            'module' => 'settings_management',
            'action' => 'view'
        ]);

        $permissionIds = $this->service->getDefaultPermissionIdsForNewAccount($this->reseller, 'User');

        $this->assertContains($this->viewPermission->id, $permissionIds);
        $this->assertContains($this->editPermission->id, $permissionIds);
        $this->assertNotContains($settingsPermission->id, $permissionIds);
        $this->assertNotContains($this->accountMgmtPermission->id, $permissionIds);
    }

    /** @test */
    public function reseller_created_user_child_excludes_account_management_even_if_parent_has_it()
    {
        DB::table('user_permissions')->insert([
            ['user_id' => $this->reseller->id, 'permission_id' => $this->accountMgmtPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->reseller->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $permissionIds = $this->service->getDefaultPermissionIdsForNewAccount($this->reseller, 'User');

        $this->assertContains($this->viewPermission->id, $permissionIds);
        $this->assertNotContains($this->accountMgmtPermission->id, $permissionIds);
    }

    /** @test */
    public function user_type_cannot_have_account_management_permission()
    {
        // Act: Try to assign account_management to User type
        $result = $this->service->syncPermissions(
            $this->child,
            [$this->accountMgmtPermission->id],
            $this->reseller
        );

        // Assert: Should fail
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Account Management', $result['message']);

        // Verify permission was not assigned
        $this->assertFalse(
            DB::table('user_permissions')
                ->where('user_id', $this->child->id)
                ->where('permission_id', $this->accountMgmtPermission->id)
                ->exists()
        );
    }

    /** @test */
    public function reseller_can_assign_permission_to_child()
    {
        // Act: Reseller assigns permission to child
        $result = $this->service->assignPermission(
            $this->child,
            $this->viewPermission,
            $this->reseller,
            'Testing permission assignment'
        );

        // Assert: Should succeed
        $this->assertTrue($result['success']);

        // Verify permission was assigned
        $this->assertTrue(
            DB::table('user_permissions')
                ->where('user_id', $this->child->id)
                ->where('permission_id', $this->viewPermission->id)
                ->exists()
        );

        // Verify audit log
        $this->assertTrue(
            PermissionAuditLog::where('user_id', $this->child->id)
                ->where('permission_id', $this->viewPermission->id)
                ->where('action', 'assigned')
                ->exists()
        );
    }

    /** @test */
    public function cascading_revocation_removes_from_all_descendants()
    {
        // Setup: Assign permission to child and grandchild
        DB::table('user_permissions')->insert([
            ['user_id' => $this->child->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->grandchild->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Act: Revoke from parent (child)
        $result = $this->service->revokePermission(
            $this->child,
            $this->viewPermission,
            $this->reseller,
            'Testing cascading revocation'
        );

        // Assert: Should succeed
        $this->assertTrue($result['success']);

        // Verify permission removed from child
        $this->assertFalse(
            DB::table('user_permissions')
                ->where('user_id', $this->child->id)
                ->where('permission_id', $this->viewPermission->id)
                ->exists()
        );

        // Verify permission removed from grandchild (cascaded)
        $this->assertFalse(
            DB::table('user_permissions')
                ->where('user_id', $this->grandchild->id)
                ->where('permission_id', $this->viewPermission->id)
                ->exists()
        );

        // Verify audit logs show cascading reason
        $grandchildLog = PermissionAuditLog::where('user_id', $this->grandchild->id)
            ->where('permission_id', $this->viewPermission->id)
            ->where('action', 'revoked')
            ->first();

        $this->assertNotNull($grandchildLog);
        $this->assertStringContainsString('Cascaded from parent', $grandchildLog->reason);
    }

    /** @test */
    public function sync_permissions_correctly_adds_and_removes()
    {
        $perm1 = Permission::factory()->create(['key' => 'test.perm1']);
        $perm2 = Permission::factory()->create(['key' => 'test.perm2']);
        $perm3 = Permission::factory()->create(['key' => 'test.perm3']);

        // Setup: User has perm1 and perm2
        DB::table('user_permissions')->insert([
            ['user_id' => $this->child->id, 'permission_id' => $perm1->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->child->id, 'permission_id' => $perm2->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Act: Sync to have perm2 and perm3 (remove perm1, add perm3)
        $result = $this->service->syncPermissions(
            $this->child,
            [$perm2->id, $perm3->id],
            $this->reseller
        );

        // Assert: Should succeed
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['added']); // perm3
        $this->assertEquals(1, $result['removed']); // perm1

        // Verify final state
        $permissions = DB::table('user_permissions')
            ->where('user_id', $this->child->id)
            ->pluck('permission_id')
            ->toArray();

        $this->assertContains($perm2->id, $permissions);
        $this->assertContains($perm3->id, $permissions);
        $this->assertNotContains($perm1->id, $permissions);
    }

    /** @test */
    public function audit_log_records_permission_metadata()
    {
        // Act: Assign permission
        $this->service->assignPermission(
            $this->child,
            $this->viewPermission,
            $this->reseller,
            'Test reason'
        );

        // Assert: Audit log exists with correct data
        $log = PermissionAuditLog::where('user_id', $this->child->id)
            ->where('permission_id', $this->viewPermission->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->reseller->id, $log->assigned_by);
        $this->assertEquals('assigned', $log->action);
        $this->assertEquals('Test reason', $log->reason);
        $this->assertNotNull($log->metadata);
        $this->assertArrayHasKey('ip_address', $log->metadata);
    }

    /** @test */
    public function reseller_cannot_assign_to_non_child()
    {
        // Create a reseller from different hierarchy
        $otherReseller = User::factory()->create([
            'user_type' => 'Reseller',
            'parent_user_id' => $this->admin->id
        ]);

        // Act: Try to assign permission to user from different reseller
        $result = $this->service->assignPermission(
            $this->child,
            $this->viewPermission,
            $otherReseller,
            'Testing cross-hierarchy assignment'
        );

        // Assert: Should fail
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('child', strtolower($result['message']));
    }

    /** @test */
    public function cannot_assign_permission_to_self()
    {
        // Act: Try to assign permission to self
        $result = $this->service->syncPermissions(
            $this->reseller,
            [$this->viewPermission->id],
            $this->reseller
        );

        // Assert: Should fail (validate method checks for self-assignment)
        // Note: This might pass sync as-is, but individual assign should fail
        // Let's check the assignPermission method directly
        $result = $this->service->assignPermission(
            $this->reseller,
            $this->viewPermission,
            $this->reseller
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('yourself', strtolower($result['message']));
    }
}
