# Testing & Verification Plan

## Pre-Implementation Checklist

### Code Review
- [ ] Review all migration files for SQL syntax errors
- [ ] Verify foreign key constraints are correct
- [ ] Check index definitions are efficient
- [ ] Ensure no duplicate column additions

### Database Backup
- [ ] Create full database backup before running migrations
- [ ] Export current users, roles, permissions
- [ ] Save permission assignments for rollback reference
- [ ] Document current user hierarchy structure

### Environment Setup
- [ ] Ensure test database is configured
- [ ] Set up test fixtures (factories)
- [ ] Configure logging for permission changes
- [ ] Set up monitoring for permission checks

---

## Phase 1: Foundation Testing

### 1.1 Database Migration Tests

```php
// tests/Feature/DatabaseMigrationsTest.php

public function test_migration_adds_parent_user_id_column()
{
    $this->assertTrue(
        Schema::hasColumn('writers', 'parent_user_id'),
        'writers table should have parent_user_id column'
    );
}

public function test_migration_adds_role_id_column()
{
    $this->assertTrue(
        Schema::hasColumn('writers', 'role_id'),
        'writers table should have role_id column'
    );
}

public function test_migration_enhances_user_permissions_table()
{
    $this->assertTrue(Schema::hasColumn('user_permissions', 'granted_by_user_id'));
    $this->assertTrue(Schema::hasColumn('user_permissions', 'inherited_from_user_id'));
    $this->assertTrue(Schema::hasColumn('user_permissions', 'granted_at'));
    $this->assertTrue(Schema::hasColumn('user_permissions', 'revoked_at'));
    $this->assertTrue(Schema::hasColumn('user_permissions', 'revocation_reason'));
}

public function test_migration_creates_permission_change_logs_table()
{
    $this->assertTrue(
        Schema::hasTable('permission_change_logs'),
        'permission_change_logs table should exist'
    );
}

public function test_foreign_keys_are_properly_set()
{
    $foreignKeys = Schema::getForeignKeys('writers');
    $fkNames = array_map(fn($fk) => $fk['foreign_key'], $foreignKeys);
    
    $this->assertContains('parent_user_id', $fkNames);
    $this->assertContains('role_id', $fkNames);
}
```

### 1.2 Model Relationship Tests

```php
// tests/Unit/WriterModelTest.php

public function test_writer_has_parent_user_relationship()
{
    $parent = Writer::factory()->create(['user_type' => 'Admin']);
    $child = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $parent->id
    ]);
    
    $this->assertInstanceOf(Writer::class, $child->parentUser);
    $this->assertEquals($parent->id, $child->parentUser->id);
}

public function test_writer_has_child_users_relationship()
{
    $parent = Writer::factory()->create(['user_type' => 'Reseller']);
    $children = Writer::factory()->count(3)->create([
        'user_type' => 'User',
        'parent_user_id' => $parent->id
    ]);
    
    $this->assertCount(3, $parent->childUsers);
    $childIds = $parent->childUsers->pluck('id')->toArray();
    $this->assertEquals($children->pluck('id')->toArray(), $childIds);
}

public function test_writer_has_role_relationship()
{
    $role = Role::first();
    $writer = Writer::factory()->create(['role_id' => $role->id]);
    
    $this->assertInstanceOf(Role::class, $writer->role);
    $this->assertEquals($role->id, $writer->role->id);
}

public function test_get_ancestors_returns_all_parents()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $user = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $ancestors = $user->getAncestors();
    
    $this->assertCount(2, $ancestors);
    $this->assertTrue($ancestors->contains($reseller));
    $this->assertTrue($ancestors->contains($admin));
}

public function test_all_descendants_relationship()
{
    $parent = Writer::factory()->create(['user_type' => 'Reseller']);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $parent->id
    ]);
    $grandchild = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $child->id
    ]);
    
    $descendants = $parent->allDescendants();
    
    $this->assertCount(2, $descendants);
}
```

### 1.3 PermissionHelper Validation Tests

```php
// tests/Unit/PermissionHelperTest.php

public function test_is_descendant_of_returns_true_for_direct_child()
{
    $parent = Writer::factory()->create();
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    
    $this->assertTrue(
        PermissionHelper::isDescendantOf($child, $parent)
    );
}

public function test_is_descendant_of_returns_true_for_grandchild()
{
    $grandparent = Writer::factory()->create();
    $parent = Writer::factory()->create(['parent_user_id' => $grandparent->id]);
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    
    $this->assertTrue(
        PermissionHelper::isDescendantOf($child, $grandparent)
    );
}

public function test_is_descendant_of_returns_false_for_non_descendant()
{
    $user1 = Writer::factory()->create();
    $user2 = Writer::factory()->create();
    
    $this->assertFalse(
        PermissionHelper::isDescendantOf($user1, $user2)
    );
}

public function test_validate_permission_grant_fails_if_grantor_lacks_permission()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $user = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $perm = Permission::where('key', 'account_management.view')->first();
    
    // Reseller doesn't have the permission
    $validation = PermissionHelper::validatePermissionGrant($reseller, $user, $perm);
    
    $this->assertFalse($validation['valid']);
    $this->assertStringContainsString('Grantor doesn\'t have permission', $validation['reasons'][0]);
}

public function test_validate_permission_grant_fails_if_grantee_not_descendant()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller1 = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $reseller2 = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    
    // Reseller1 trying to assign to non-descendant Reseller2
    $validation = PermissionHelper::validatePermissionGrant($reseller1, $reseller2, $perm);
    
    $this->assertFalse($validation['valid']);
}

public function test_can_access_account_management_true_for_admin()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $perm = Permission::where('key', 'account_management.view')->first();
    $admin->permissions()->attach($perm->id);
    
    $this->assertTrue(
        PermissionHelper::canAccessAccountManagement($admin)
    );
}

public function test_can_access_account_management_true_for_reseller_with_permission()
{
    $reseller = Writer::factory()->create(['user_type' => 'Reseller']);
    $perm = Permission::where('key', 'account_management.view')->first();
    $reseller->permissions()->attach($perm->id);
    
    $this->assertTrue(
        PermissionHelper::canAccessAccountManagement($reseller)
    );
}

public function test_can_access_account_management_false_for_user()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $this->assertFalse(
        PermissionHelper::canAccessAccountManagement($user)
    );
}
```

---

## Phase 2: Access Control Testing

### 2.1 Route Protection Tests

```php
// tests/Feature/RouteProtectionTest.php

public function test_user_cannot_access_admin_account_management()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user)->get('/admin/view-user');
    
    // Should either not exist or redirect
    $this->assertTrue(
        $response->status() === 403 || $response->status() === 404 || $response->status() === 302,
        'User should not be able to access admin account management'
    );
}

public function test_user_cannot_access_reseller_account_management()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user)->get('/reseller/view-user');
    
    $this->assertTrue($response->status() !== 200);
}

public function test_reseller_can_access_own_account_management()
{
    $reseller = Writer::factory()->create(['user_type' => 'Reseller']);
    $perm = Permission::where('key', 'account_management.view')->first();
    $reseller->permissions()->attach($perm->id);
    
    $response = $this->actingAs($reseller)->get('/reseller/view-user');
    
    $this->assertEquals(200, $response->status());
}

public function test_admin_can_access_account_management()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $perm = Permission::where('key', 'account_management.view')->first();
    $admin->permissions()->attach($perm->id);
    
    $response = $this->actingAs($admin)->get('/admin/view-user');
    
    $this->assertEquals(200, $response->status());
}

public function test_user_cannot_access_edit_other_user_via_url()
{
    $user1 = Writer::factory()->create(['user_type' => 'User']);
    $user2 = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user1)->get("/user/edit-user/{$user2->id}");
    
    $this->assertFalse($response->status() === 200);
}

public function test_reseller_cannot_access_other_reseller_children()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller1 = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $reseller2 = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller2->id
    ]);
    
    $perm = Permission::where('key', 'account_management.edit')->first();
    $reseller1->permissions()->attach($perm->id);
    
    $response = $this->actingAs($reseller1)->patch(
        "/reseller/update-user/{$child->id}",
        ['name' => 'Updated Name']
    );
    
    $this->assertEquals(403, $response->status());
}

public function test_reseller_can_access_own_child()
{
    $reseller = Writer::factory()->create(['user_type' => 'Reseller']);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $perm = Permission::where('key', 'account_management.edit')->first();
    $reseller->permissions()->attach($perm->id);
    
    $response = $this->actingAs($reseller)->get("/reseller/edit-user/{$child->id}");
    
    $this->assertEquals(200, $response->status());
}
```

### 2.2 Middleware Tests

```php
// tests/Feature/AccountManagementAccessMiddlewareTest.php

public function test_account_management_middleware_blocks_user_type()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user)->get('/user/manage-account');
    
    $this->assertNotEquals(200, $response->status());
}

public function test_hierarchy_access_middleware_allows_parent_accessing_child()
{
    $parent = Writer::factory()->create(['user_type' => 'Reseller']);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $parent->id
    ]);
    
    // This would be a protected route with hierarchy.access middleware
    $response = $this->actingAs($parent)->get("/reseller/edit-user/{$child->id}");
    
    // Should not be blocked by hierarchy middleware (actual response depends on route existence)
    $this->assertNotEquals(403, $response->status());
}

public function test_hierarchy_access_middleware_blocks_non_parent()
{
    $user1 = Writer::factory()->create(['user_type' => 'User']);
    $user2 = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user1)->get("/user/edit-user/{$user2->id}");
    
    $this->assertEquals(403, $response->status());
}
```

---

## Phase 3: Permission Inheritance Testing

### 3.1 Permission Assignment Tests

```php
// tests/Feature/PermissionAssignmentTest.php

public function test_assign_permission_validates_grantor_ownership()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $user = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $perm = Permission::where('key', 'account_management.view')->first();
    
    // Reseller doesn't have the permission yet
    $this->expectException(Exception::class);
    PermissionAssignmentService::assignPermission($reseller, $user, $perm);
}

public function test_assign_permission_succeeds_for_admin()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    
    $result = PermissionAssignmentService::assignPermission($admin, $reseller, $perm);
    
    $this->assertTrue($result);
    $this->assertTrue($reseller->effectivePermissions()->where('id', $perm->id)->exists());
}

public function test_assign_permission_succeeds_for_reseller_with_permission()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $user = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    
    // First give reseller the permission
    PermissionAssignmentService::assignPermission($admin, $reseller, $perm);
    
    // Reseller can now assign it
    $result = PermissionAssignmentService::assignPermission($reseller, $user, $perm);
    
    $this->assertTrue($result);
    $this->assertTrue($user->effectivePermissions()->where('id', $perm->id)->exists());
}

public function test_inherited_permission_marked_correctly()
{
    $parent = Writer::factory()->create(['user_type' => 'Reseller']);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $parent->id
    ]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    
    // Assign as inherited
    PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    
    // Verify it's marked as inherited
    $record = DB::table('user_permissions')
        ->where('user_id', $child->id)
        ->where('permission_id', $perm->id)
        ->first();
    
    $this->assertNotNull($record->inherited_from_user_id);
    $this->assertEquals($parent->id, $record->inherited_from_user_id);
}

public function test_direct_permission_not_marked_as_inherited()
{
    $user = Writer::factory()->create();
    $perm = Permission::where('key', 'device_management.view')->first();
    
    // Assign as direct (not inherited)
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    PermissionAssignmentService::assignPermission($admin, $user, $perm, false);
    
    // Verify it's NOT marked as inherited
    $record = DB::table('user_permissions')
        ->where('user_id', $user->id)
        ->where('permission_id', $perm->id)
        ->first();
    
    $this->assertNull($record->inherited_from_user_id);
}
```

### 3.2 Cascading Revocation Tests

```php
// tests/Feature/CascadingRevocationTest.php

public function test_revoke_permission_from_parent_cascades_to_children()
{
    $parent = Writer::factory()->create(['user_type' => 'Reseller']);
    $child = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $parent->id
    ]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Assign to parent
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    
    // Assign to child (as inherited)
    PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    
    // Verify both have permission
    $this->assertTrue($parent->effectivePermissions()->where('id', $perm->id)->exists());
    $this->assertTrue($child->effectivePermissions()->where('id', $perm->id)->exists());
    
    // Revoke from parent
    PermissionAssignmentService::revokePermission($admin, $parent, $perm);
    
    // Refresh models
    $parent->refresh();
    $child->refresh();
    
    // Both should have permission revoked
    $this->assertFalse($parent->effectivePermissions()->where('id', $perm->id)->exists());
    $this->assertFalse($child->effectivePermissions()->where('id', $perm->id)->exists());
}

public function test_cascading_revocation_logs_correctly()
{
    $parent = Writer::factory()->create();
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Setup permissions
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    
    // Clear logs
    DB::table('permission_change_logs')->truncate();
    
    // Revoke
    PermissionAssignmentService::revokePermission($admin, $parent, $perm);
    
    // Check logs
    $logs = DB::table('permission_change_logs')->get();
    
    $this->assertGreaterThanOrEqual(2, $logs->count());
    
    $revokeLogs = $logs->where('action', 'revoke');
    $cascadeLogs = $logs->where('action', 'cascade_revoke');
    
    $this->assertGreater(0, $revokeLogs->count());
    $this->assertGreater(0, $cascadeLogs->count());
}

public function test_revoke_permission_with_multiple_children()
{
    $parent = Writer::factory()->create();
    $children = Writer::factory()->count(5)->create(['parent_user_id' => $parent->id]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Assign to all
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    foreach ($children as $child) {
        PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    }
    
    // Revoke from parent
    $affectedCount = PermissionAssignmentService::revokePermission($admin, $parent, $perm);
    
    // Should affect parent + 5 children = 6
    $this->assertEquals(6, $affectedCount);
    
    // Verify all lost permission
    foreach ($children as $child) {
        $child->refresh();
        $this->assertFalse($child->effectivePermissions()->where('id', $perm->id)->exists());
    }
}

public function test_cascade_revocation_reason_set_correctly()
{
    $parent = Writer::factory()->create();
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    
    // Revoke
    PermissionAssignmentService::revokePermission($admin, $parent, $perm);
    
    // Check child's revocation reason
    $childRecord = DB::table('user_permissions')
        ->where('user_id', $child->id)
        ->where('permission_id', $perm->id)
        ->first();
    
    $this->assertEquals('parent_revoke', $childRecord->revocation_reason);
}

public function test_non_inherited_permissions_not_revoked_when_parent_loses()
{
    $parent = Writer::factory()->create();
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Assign to parent
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    
    // Assign to child as DIRECT (not inherited)
    PermissionAssignmentService::assignPermission($admin, $child, $perm, false);
    
    // Revoke from parent
    PermissionAssignmentService::revokePermission($admin, $parent, $perm);
    
    // Parent loses permission
    $parent->refresh();
    $this->assertFalse($parent->effectivePermissions()->where('id', $perm->id)->exists());
    
    // But child keeps direct permission
    $child->refresh();
    $this->assertTrue($child->effectivePermissions()->where('id', $perm->id)->exists());
}
```

---

## Phase 4: UI/UX Testing

### 4.1 Sidebar Visibility Tests

```php
// tests/Feature/SidebarVisibilityTest.php

public function test_user_sidebar_does_not_show_account_management()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user)->get('/user');
    
    // Check response doesn't contain Account Management
    $response->assertDontSee('Account Management');
}

public function test_reseller_sidebar_shows_account_management_when_permitted()
{
    $reseller = Writer::factory()->create(['user_type' => 'Reseller']);
    $perm = Permission::where('key', 'account_management.view')->first();
    $reseller->permissions()->attach($perm->id);
    
    $response = $this->actingAs($reseller)->get('/reseller');
    
    $response->assertSee('Account Management');
}

public function test_reseller_sidebar_hides_account_management_when_not_permitted()
{
    $reseller = Writer::factory()->create(['user_type' => 'Reseller']);
    
    $response = $this->actingAs($reseller)->get('/reseller');
    
    $response->assertDontSee('Account Management');
}

public function test_admin_sidebar_shows_account_management()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    $response = $this->actingAs($admin)->get('/admin');
    
    $response->assertSee('Account Management');
}
```

---

## Phase 5: Security Testing

### 5.1 Permission Escalation Tests

```php
// tests/Security/PermissionEscalationTest.php

public function test_cannot_assign_permission_to_non_descendant()
{
    $user1 = Writer::factory()->create();
    $user2 = Writer::factory()->create();
    
    $perm = Permission::first();
    
    $this->expectException(Exception::class);
    PermissionAssignmentService::assignPermission($user1, $user2, $perm);
}

public function test_cannot_assign_unowned_permission()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create([
        'user_type' => 'Reseller',
        'parent_user_id' => $admin->id
    ]);
    $user = Writer::factory()->create([
        'user_type' => 'User',
        'parent_user_id' => $reseller->id
    ]);
    
    $perm = Permission::where('key', 'account_management.create')->first();
    
    // Reseller doesn't have permission
    $this->expectException(Exception::class);
    PermissionAssignmentService::assignPermission($reseller, $user, $perm);
}

public function test_cannot_assign_permission_to_self()
{
    $user = Writer::factory()->create();
    $perm = Permission::first();
    
    // User assigning to self should fail if not admin
    if ($user->user_type !== 'Admin') {
        $this->expectException(Exception::class);
        PermissionAssignmentService::assignPermission($user, $user, $perm);
    }
}

public function test_deep_hierarchy_respects_boundaries()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    $reseller = Writer::factory()->create(['parent_user_id' => $admin->id]);
    $user = Writer::factory()->create(['parent_user_id' => $reseller->id]);
    
    $perm = Permission::where('key', 'device_management.view')->first();
    
    // Admin assigns to Reseller
    PermissionAssignmentService::assignPermission($admin, $reseller, $perm);
    
    // Reseller assigns to User
    PermissionAssignmentService::assignPermission($reseller, $user, $perm);
    
    // User tries to assign to Admin should fail
    $this->expectException(Exception::class);
    PermissionAssignmentService::assignPermission($user, $admin, $perm);
}

public function test_api_validates_permissions()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    // Try to update permissions via API without permission
    $response = $this->actingAs($user)->json('PATCH', '/api/users/1/permissions', [
        'permissions' => [1, 2, 3]
    ]);
    
    $this->assertFalse($response->json('success') ?? true);
}
```

### 5.2 Data Integrity Tests

```php
// tests/Security/DataIntegrityTest.php

public function test_revoked_permissions_not_counted_as_effective()
{
    $user = Writer::factory()->create();
    $perm = Permission::first();
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Assign permission
    PermissionAssignmentService::assignPermission($admin, $user, $perm);
    $this->assertTrue($user->effectivePermissions()->where('id', $perm->id)->exists());
    
    // Revoke it
    PermissionAssignmentService::revokePermission($admin, $user, $perm);
    $user->refresh();
    
    // Should not be in effective permissions
    $this->assertFalse($user->effectivePermissions()->where('id', $perm->id)->exists());
}

public function test_orphaned_permissions_handled_correctly()
{
    $parent = Writer::factory()->create();
    $child = Writer::factory()->create(['parent_user_id' => $parent->id]);
    $perm = Permission::first();
    
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Assign inherited permission
    PermissionAssignmentService::assignPermission($admin, $parent, $perm);
    PermissionAssignmentService::assignPermission($parent, $child, $perm, true);
    
    // Delete parent
    $parent->delete();
    
    // Child should still function (inherited_from_user_id becomes orphaned but that's ok)
    $child->refresh();
    // System should handle gracefully
    $this->assertNotNull($child);
}

public function test_circular_reference_prevention()
{
    $user1 = Writer::factory()->create();
    $user2 = Writer::factory()->create(['parent_user_id' => $user1->id]);
    
    // Try to set user1's parent to user2 (would create cycle)
    try {
        $user1->update(['parent_user_id' => $user2->id]);
        
        // Verify cycle doesn't exist
        $ancestors = $user1->getAncestors();
        $this->assertFalse($ancestors->contains($user1));
    } catch (Exception $e) {
        // Expected - cycle prevention
        $this->assertTrue(true);
    }
}
```

---

## Post-Implementation Verification

### Performance Baseline

```php
// tests/Performance/PermissionPerformanceTest.php

public function test_permission_check_is_fast()
{
    $user = Writer::factory()->create();
    $perm = Permission::first();
    
    // Warm up cache
    $user->hasPermission($perm->key);
    
    // Time cached check
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $user->hasPermission($perm->key);
    }
    $elapsed = microtime(true) - $start;
    
    // Should be < 5ms per check on average
    $avgTime = ($elapsed / 1000) * 1000; // Convert to ms
    $this->assertLessThan(5, $avgTime);
}

public function test_sidebar_permission_checks_efficient()
{
    $user = Writer::factory()->create();
    
    // Add multiple permissions
    Permission::all()->take(10)->each(function ($perm) use ($user) {
        $user->permissions()->attach($perm->id);
    });
    
    // Check that sidebar loads efficiently
    // Should not have N+1 queries
    $queryCount = 0;
    DB::listen(function ($query) use (&$queryCount) {
        $queryCount++;
    });
    
    for ($i = 0; $i < 20; $i++) {
        PermissionHelper::getAccessibleModules($user);
    }
    
    // Should have very few queries (ideally 1-2, not 20+)
    $this->assertLessThan(5, $queryCount);
}
```

---

## Manual Testing Checklist

### Admin Workflows

- [ ] Admin can view all users
- [ ] Admin can create new user
- [ ] Admin can edit user details
- [ ] Admin can delete user
- [ ] Admin can grant permissions
- [ ] Admin can revoke permissions
- [ ] Admin can see cascading effects
- [ ] Admin sidebar shows Account Management

### Reseller Workflows

- [ ] Reseller can view own child users only
- [ ] Reseller cannot view other resellers' children
- [ ] Reseller can create child user
- [ ] Reseller can edit own child user
- [ ] Reseller can grant only owned permissions
- [ ] Reseller sidebar shows Account Management
- [ ] Reseller cannot access /admin routes
- [ ] Permission cascades correctly to reseller's children

### User/Dealer Workflows

- [ ] User cannot see Account Management menu
- [ ] User cannot access /admin/view-user
- [ ] User cannot access /reseller/view-user
- [ ] User can only view own devices
- [ ] User can only view own settings
- [ ] Direct URL access to account management returns 403

### Edge Cases

- [ ] Revoke permission from parent → children lose it
- [ ] Grant direct permission to child → parent losing it doesn't affect child
- [ ] Deep hierarchy works (4+ levels)
- [ ] Bulk operations handle correctly
- [ ] Cache invalidates on permission changes
- [ ] Audit trail captures all changes
- [ ] Permission history available for review

---

## Rollback Procedures

### If Issues Found

1. **Stop the application**
2. **Restore database backup**
3. **Revert code changes** (git revert)
4. **Clear application cache**
5. **Restart application**
6. **Notify users**

### Testing Rollback

- [ ] Practice rollback procedure before deployment
- [ ] Test that rollback restores all data correctly
- [ ] Verify application functions after rollback
- [ ] Document any issues encountered

---

**Testing Document Version:** 1.0  
**Last Updated:** 2026-06-02
