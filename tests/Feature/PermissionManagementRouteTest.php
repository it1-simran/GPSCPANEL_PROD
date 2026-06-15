<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PermissionManagementRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $reseller;
    protected User $userType;
    protected Permission $viewPermission;

    public function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->admin = User::factory()->create(['user_type' => 'Admin']);
        $this->reseller = User::factory()->create([
            'user_type' => 'Reseller',
            'parent_user_id' => $this->admin->id
        ]);
        $this->userType = User::factory()->create([
            'user_type' => 'User',
            'parent_user_id' => $this->reseller->id
        ]);

        // Create permission
        $this->viewPermission = Permission::factory()->create([
            'key' => 'device_management.view',
            'module' => 'device_management'
        ]);

        // Grant permissions
        DB::table('user_permissions')->insert([
            ['user_id' => $this->admin->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->reseller->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->userType->id, 'permission_id' => $this->viewPermission->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @test */
    public function admin_can_access_permission_management()
    {
        // Act & Assert
        $response = $this->actingAs($this->admin)
            ->get('/admin/manage-permissions');

        $response->assertStatus(200);
    }

    /** @test */
    public function reseller_cannot_access_admin_permission_management()
    {
        // Act & Assert
        $response = $this->actingAs($this->reseller)
            ->get('/admin/manage-permissions');

        // Should be blocked by account.management middleware or auth/role check
        $response->assertStatus(403);
    }

    /** @test */
    public function user_type_cannot_access_permission_management()
    {
        // Act & Assert
        $response = $this->actingAs($this->userType)
            ->get('/admin/manage-permissions');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_type_cannot_update_permissions_via_api()
    {
        // Create target user to update
        $targetUser = User::factory()->create([
            'user_type' => 'User',
            'parent_user_id' => $this->reseller->id
        ]);

        // Act: User type tries to update child permissions
        $response = $this->actingAs($this->userType)
            ->postJson("/admin/permissions/{$targetUser->id}/update", [
                'permissions' => [$this->viewPermission->id]
            ]);

        // Assert: Should be blocked
        $response->assertStatus(403);
    }

    /** @test */
    public function reseller_can_access_child_permissions()
    {
        // Act & Assert
        $response = $this->actingAs($this->reseller)
            ->get('/reseller/manage-child-permissions');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_child_permissions()
    {
        // Act & Assert
        $response = $this->actingAs($this->userType)
            ->get('/reseller/manage-child-permissions');

        // Should be blocked by role check and account.management middleware
        $response->assertStatus(403);
    }

    /** @test */
    public function reseller_can_get_child_permissions()
    {
        // Act & Assert
        $response = $this->actingAs($this->reseller)
            ->getJson("/reseller/permissions/child/{$this->userType->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'permissions',
            'assignable_permissions',
            'permissions_by_module',
            'modules',
            'available_count',
            'user_type',
        ]);
    }

    /** @test */
    public function reseller_cannot_manage_non_child_user()
    {
        // Create another reseller's child
        $otherReseller = User::factory()->create([
            'user_type' => 'Reseller',
            'parent_user_id' => $this->admin->id
        ]);
        $otherChild = User::factory()->create([
            'user_type' => 'User',
            'parent_user_id' => $otherReseller->id
        ]);

        // Act: Try to manage non-child user
        $response = $this->actingAs($this->reseller)
            ->postJson("/reseller/permissions/child/{$otherChild->id}/update", [
                'permissions' => [$this->viewPermission->id]
            ]);

        // Assert: Should fail due to hierarchy check
        $response->assertStatus(403);
    }

    /** @test */
    public function api_returns_403_json_for_user_type_on_permission_routes()
    {
        // Act & Assert
        $response = $this->actingAs($this->userType)
            ->postJson("/admin/permissions/{$this->reseller->id}/update", [
                'permissions' => [$this->viewPermission->id]
            ]);

        // Should return JSON error
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => "You don't have permission to access Account Management!"
        ]);
    }

    /** @test */
    public function unauthenticated_user_redirects_to_login()
    {
        // Act & Assert
        $response = $this->get('/admin/manage-permissions');

        $response->assertRedirect('/login');
    }
}
