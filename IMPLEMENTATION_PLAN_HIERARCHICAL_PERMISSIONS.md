# Hierarchical Permission Inheritance System - Implementation Plan

**Project:** GPS C Panel Permission Hierarchy Enhancement  
**Date:** 2026-06-02  
**Status:** Design Phase  
**Priority:** Critical

---

## Executive Summary

This document outlines a comprehensive implementation plan for designing and building a hierarchical permission inheritance system that enforces strict access control across user types (Admin → Reseller → User/Dealer). The system will prevent unauthorized access to sensitive features like Account Management and ensure that child users can only have a subset of parent permissions.

---

## 1. Current System Analysis

### 1.1 Existing Architecture

**User Hierarchy:**
- `Admin` (user_type = 'Admin') - Full system access
- `Reseller` (user_type = 'Reseller') - Can manage child users
- `User/Dealer` (user_type = 'User') - Limited access to own data

**Database Tables:**
- `writers` - Main user table (acts as User model)
- `permissions` - Individual permission definitions
- `roles` - Role groupings
- `role_permissions` - Role-to-permission mapping
- `user_permissions` - Direct user-to-permission mapping
- Relationships: `role_id`, `parent_user_id`, `created_by`

**Permission Structure:**
- Organized by module (e.g., `account_management`, `device_management`, `settings_management`)
- Actions: `view`, `create`, `edit`, `delete`, `download`, `print`, `assign_bulk`
- Format: `{module}.{action}` (e.g., `account_management.view`)

**Current Limitations:**
1. Permission inheritance not enforced - parent can assign permissions they don't have
2. No automatic cascading revocation when parent loses permission
3. Account Management visibility relies on user_type checks in Blade templates
4. Missing middleware validation for child user access visibility
5. Sidebar shows Account Management to all Resellers/Users regardless of permission hierarchy

### 1.2 Current Permission Helper Implementation

**File:** `app/Helpers/PermissionHelper.php`

Strengths:
- Per-request caching to avoid N+1 queries
- Wildcard support for admin users (`*` = all permissions)
- Module-level permission checks
- Role + direct user permission merging

Weaknesses:
- No validation of permission inheritance rules
- Admin check bypasses hierarchy validation
- No enforcement of parent-child permission constraints
- Missing audit trail for permission changes

---

## 2. Requirements & Design Goals

### 2.1 Functional Requirements

#### F1: Permission Inheritance Enforcement
- **Resellers** can only assign permissions they themselves possess
- **Child Users** (Users/Dealers) inherit only a subset of parent permissions
- No permission elevation below current user level

#### F2: Automatic Cascading Revocation
- When parent loses a permission, all child users automatically lose it
- Maintain referential integrity in `user_permissions` table
- Log all revocation events for audit purposes

#### F3: Account Management Access Control
- **Admin:** Full access to Account Management module
- **Reseller:** Access only to Account Management for child accounts
- **User/Dealer:** NO access to Account Management menu or routes
  - Sidebar menu hidden
  - Direct URL access blocked (403 Forbidden)
  - API/AJAX requests rejected

#### F4: Permission Visibility in UI
- Resellers can only see and assign permissions appropriate to their role
- No exposed UI for permission editing beyond designated interfaces
- Settings validated server-side, not client-side

#### F5: Audit & Monitoring
- Track who granted/revoked permissions and when
- Log permission violations and unauthorized access attempts
- Support permission history for compliance

### 2.2 Non-Functional Requirements

#### NF1: Security
- All permission checks done server-side
- Defense in depth: middleware + controller validation + API validation
- No client-side permission visibility exposure

#### NF2: Scalability
- Support deep hierarchies (Admin → Reseller → User → User)
- Efficient queries with proper indexing
- Permission cache invalidation on changes

#### NF3: Maintainability
- Clear separation of concerns
- Well-documented permission inheritance logic
- Reusable validation methods

#### NF4: Performance
- Minimal database queries (cached where possible)
- Indexed permission lookups
- Batch operations for cascading revocations

---

## 3. Database Schema Changes

### 3.1 Writers Table Enhancements

**New Columns to Add:**

```sql
ALTER TABLE writers ADD COLUMN parent_user_id BIGINT UNSIGNED NULLABLE AFTER created_by;
ALTER TABLE writers ADD COLUMN role_id BIGINT UNSIGNED NULLABLE AFTER parent_user_id;
ALTER TABLE writers ADD COLUMN max_child_depth INT DEFAULT 0 AFTER role_id;
ALTER TABLE writers ADD CONSTRAINT fk_writers_parent_user FOREIGN KEY (parent_user_id) 
    REFERENCES writers(id) ON DELETE CASCADE;
ALTER TABLE writers ADD CONSTRAINT fk_writers_role FOREIGN KEY (role_id) 
    REFERENCES roles(id) ON DELETE SET NULL;
ALTER TABLE writers ADD INDEX idx_parent_user_id (parent_user_id);
ALTER TABLE writers ADD INDEX idx_role_id (role_id);
```

**Column Descriptions:**
- `parent_user_id` - References parent user for hierarchy tracking
- `role_id` - User's assigned role (Admin, Reseller, User, Dealer)
- `max_child_depth` - Maximum hierarchy depth allowed below this user

### 3.2 User Permissions Table Enhancement

**New Columns to Add:**

```sql
ALTER TABLE user_permissions ADD COLUMN granted_by_user_id BIGINT UNSIGNED NULLABLE 
    AFTER permission_id;
ALTER TABLE user_permissions ADD COLUMN inherited_from_user_id BIGINT UNSIGNED NULLABLE 
    AFTER granted_by_user_id;
ALTER TABLE user_permissions ADD COLUMN granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
    AFTER inherited_from_user_id;
ALTER TABLE user_permissions ADD COLUMN revoked_at TIMESTAMP NULL AFTER granted_at;
ALTER TABLE user_permissions ADD COLUMN revocation_reason VARCHAR(255) NULLABLE 
    AFTER revoked_at;

ALTER TABLE user_permissions ADD CONSTRAINT fk_granted_by_user 
    FOREIGN KEY (granted_by_user_id) REFERENCES writers(id) ON DELETE SET NULL;
ALTER TABLE user_permissions ADD CONSTRAINT fk_inherited_from_user 
    FOREIGN KEY (inherited_from_user_id) REFERENCES writers(id) ON DELETE SET NULL;
ALTER TABLE user_permissions ADD INDEX idx_granted_by (granted_by_user_id);
ALTER TABLE user_permissions ADD INDEX idx_inherited_from (inherited_from_user_id);
ALTER TABLE user_permissions ADD INDEX idx_revoked_at (revoked_at);
```

**Column Descriptions:**
- `granted_by_user_id` - Who explicitly granted this permission
- `inherited_from_user_id` - If null, permission is direct; otherwise shows inheritance source
- `granted_at` - Timestamp of grant
- `revoked_at` - Timestamp of revocation (null = active)
- `revocation_reason` - Why permission was revoked (parent revoked, hierarchy change, etc.)

### 3.3 Permission Inheritance Log Table (NEW)

```sql
CREATE TABLE permission_change_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    action ENUM('grant', 'revoke', 'inherit', 'cascade_revoke') NOT NULL,
    triggered_by_user_id BIGINT UNSIGNED NOT NULL,
    trigger_type ENUM('direct', 'parent_revoke', 'hierarchy_change', 'manual_revoke'),
    affected_children_count INT DEFAULT 0,
    notes TEXT NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES writers(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by_user_id) REFERENCES writers(id) ON DELETE CASCADE,
    
    INDEX idx_user_permission (user_id, permission_id),
    INDEX idx_triggered_by (triggered_by_user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```

### 3.4 Role Hierarchy Table (NEW)

```sql
CREATE TABLE role_hierarchy (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    parent_role_id BIGINT UNSIGNED NOT NULL,
    child_role_id BIGINT UNSIGNED NOT NULL,
    allowed_depth INT DEFAULT 1,
    
    UNIQUE KEY unique_role_pair (parent_role_id, child_role_id),
    FOREIGN KEY (parent_role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (child_role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_parent_role (parent_role_id)
);
```

**Pre-populated Data:**
```sql
INSERT INTO role_hierarchy (parent_role_id, child_role_id, allowed_depth) VALUES
    ((SELECT id FROM roles WHERE slug = 'admin'), (SELECT id FROM roles WHERE slug = 'reseller'), 2),
    ((SELECT id FROM roles WHERE slug = 'reseller'), (SELECT id FROM roles WHERE slug = 'user'), 1),
    ((SELECT id FROM roles WHERE slug = 'reseller'), (SELECT id FROM roles WHERE slug = 'dealer'), 1);
```

### 3.5 Migration Files to Create

**Files:**
1. `2026_06_02_add_hierarchy_to_writers_table.php` - Add parent_user_id, role_id, max_child_depth
2. `2026_06_02_enhance_user_permissions_table.php` - Add audit columns
3. `2026_06_02_create_permission_change_logs_table.php` - Audit trail
4. `2026_06_02_create_role_hierarchy_table.php` - Role hierarchy rules

---

## 4. Model Enhancements

### 4.1 Writer Model Enhancements

**File:** `app/Writer.php`

```php
<?php

class Writer extends Authenticatable {
    
    /**
     * Get this user's parent user
     */
    public function parentUser()
    {
        return $this->belongsTo(Writer::class, 'parent_user_id');
    }
    
    /**
     * Get this user's child users (direct descendants)
     */
    public function childUsers()
    {
        return $this->hasMany(Writer::class, 'parent_user_id');
    }
    
    /**
     * Get ALL descendant users recursively
     */
    public function allDescendants()
    {
        return $this->childUsers()->with('allDescendants');
    }
    
    /**
     * Get user's role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    
    /**
     * Get user's direct permissions (not inherited)
     */
    public function directPermissions()
    {
        return $this->belongsToMany(
            Permission::class, 
            'user_permissions', 
            'user_id', 
            'permission_id'
        )->wherePivot('revoked_at', null);
    }
    
    /**
     * Get user's inherited permissions from parent
     */
    public function inheritedPermissions()
    {
        return $this->belongsToMany(
            Permission::class, 
            'user_permissions', 
            'user_id', 
            'permission_id'
        )->wherePivot('inherited_from_user_id', '!=', null)
         ->wherePivot('revoked_at', null);
    }
    
    /**
     * Get all effective permissions (direct + inherited, excluding revoked)
     */
    public function effectivePermissions()
    {
        return $this->belongsToMany(
            Permission::class, 
            'user_permissions', 
            'user_id', 
            'permission_id'
        )->wherePivot('revoked_at', null);
    }
    
    /**
     * Check if user can assign a permission (must own it)
     */
    public function canAssignPermission(Permission $permission): bool
    {
        if ($this->user_type === 'Admin') {
            return true;
        }
        
        // User can only assign permissions they have
        return $this->effectivePermissions()
                    ->where('permissions.id', $permission->id)
                    ->exists();
    }
    
    /**
     * Check if user can assign permission to a child user
     */
    public function canAssignToChild(Writer $child, Permission $permission): bool
    {
        // Child must be a descendant
        if (!$this->hasDescendant($child)) {
            return false;
        }
        
        // Can only assign permissions user has
        if (!$this->canAssignPermission($permission)) {
            return false;
        }
        
        // Cannot exceed child's role max permissions
        return $this->isValidPermissionForRole($permission, $child->role);
    }
    
    /**
     * Verify if a user is a descendant of this user
     */
    public function hasDescendant(Writer $user): bool
    {
        return $user->parentUser()->exists() && 
               $user->parentUser()->first()->id === $this->id;
    }
    
    /**
     * Get all ancestors (parents up the hierarchy)
     */
    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parentUser;
        
        while ($current) {
            $ancestors->push($current);
            $current = $current->parentUser;
        }
        
        return $ancestors;
    }
    
    /**
     * Check if permission is valid for a role
     */
    private function isValidPermissionForRole(Permission $permission, ?Role $role): bool
    {
        if (!$role) {
            return true;
        }
        
        // Check role_permissions table for role-level restrictions
        return DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->exists();
    }
}
```

### 4.2 Permission Model Enhancements

**File:** `app/Permission.php`

```php
<?php

class Permission extends Model {
    
    /**
     * Get module name from permission key
     */
    public function getModule(): string
    {
        return explode('.', $this->key)[0];
    }
    
    /**
     * Get action name from permission key
     */
    public function getAction(): string
    {
        $parts = explode('.', $this->key);
        return $parts[1] ?? '';
    }
    
    /**
     * Check if this permission is a view permission
     */
    public function isViewPermission(): bool
    {
        return $this->getAction() === 'view';
    }
    
    /**
     * Get all users with this permission
     */
    public function usersWithPermission()
    {
        return $this->belongsToMany(
            Writer::class,
            'user_permissions',
            'permission_id',
            'user_id'
        )->wherePivot('revoked_at', null);
    }
}
```

### 4.3 Role Model Enhancements

**File:** `app/Role.php`

```php
<?php

class Role extends Model {
    
    /**
     * Get parent role in hierarchy
     */
    public function parentRole()
    {
        return $this->belongsTo(
            Role::class, 
            'role_hierarchy', 
            'child_role_id', 
            'parent_role_id'
        );
    }
    
    /**
     * Get child roles
     */
    public function childRoles()
    {
        return $this->hasMany(
            Role::class,
            'role_hierarchy',
            'parent_role_id',
            'child_role_id'
        );
    }
    
    /**
     * Get all permissions for this role
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions'
        );
    }
    
    /**
     * Check if this role can contain another role
     */
    public function canContainRole(Role $childRole): bool
    {
        return DB::table('role_hierarchy')
            ->where('parent_role_id', $this->id)
            ->where('child_role_id', $childRole->id)
            ->exists();
    }
}
```

---

## 5. PermissionHelper Enhancements

### 5.1 New Methods for Inheritance Validation

**File:** `app/Helpers/PermissionHelper.php`

```php
<?php

class PermissionHelper {
    
    /**
     * Check if a user can assign a specific permission to another user
     * 
     * @param Writer $grantor - User attempting to grant permission
     * @param Writer $grantee - User receiving permission
     * @param Permission|string $permission - Permission to grant
     * @return bool
     */
    public static function canGrantPermission(Writer $grantor, Writer $grantee, $permission): bool
    {
        // Admin can grant any permission
        if ($grantor->user_type === 'Admin') {
            return true;
        }
        
        // Get the actual permission object
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->first();
            if (!$permission) {
                return false;
            }
        }
        
        // User must be able to assign permission (must have it)
        if (!$grantor->canAssignPermission($permission)) {
            return false;
        }
        
        // Grantee must be a direct or indirect descendant
        if (!self::isDescendantOf($grantee, $grantor)) {
            return false;
        }
        
        // Permission must be valid for grantee's role
        return $grantor->isValidPermissionForRole($permission, $grantee->role);
    }
    
    /**
     * Check if one user is a descendant of another
     * 
     * @param Writer $user - Potential descendant
     * @param Writer $potentialAncestor - Potential ancestor
     * @return bool
     */
    public static function isDescendantOf(Writer $user, Writer $potentialAncestor): bool
    {
        $current = $user;
        
        while ($current->parentUser) {
            if ($current->parent_user_id === $potentialAncestor->id) {
                return true;
            }
            $current = $current->parentUser;
        }
        
        return false;
    }
    
    /**
     * Get all permissions that a user can assign to children
     * 
     * @param Writer $user
     * @return Collection
     */
    public static function getAssignablePermissions(Writer $user): Collection
    {
        if ($user->user_type === 'Admin') {
            return Permission::where('is_active', 1)->get();
        }
        
        // Get user's effective permissions
        return $user->effectivePermissions()->get();
    }
    
    /**
     * Validate permission hierarchy before assignment
     * 
     * @param Writer $grantor
     * @param Writer $grantee
     * @param Permission|string $permission
     * @return array - ['valid' => bool, 'reasons' => string[]]
     */
    public static function validatePermissionGrant(Writer $grantor, Writer $grantee, $permission): array
    {
        $reasons = [];
        
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->first();
            if (!$permission) {
                return ['valid' => false, 'reasons' => ['Permission not found']];
            }
        }
        
        // Check 1: Grantor must have the permission
        if (!$grantor->canAssignPermission($permission)) {
            $reasons[] = "Grantor doesn't have permission: {$permission->key}";
        }
        
        // Check 2: Grantee must be descendant
        if (!self::isDescendantOf($grantee, $grantor)) {
            $reasons[] = "Grantee is not a descendant of grantor";
        }
        
        // Check 3: Hierarchy role validation
        if ($grantee->role && !$grantor->isValidPermissionForRole($permission, $grantee->role)) {
            $reasons[] = "Permission not allowed for grantee's role";
        }
        
        return [
            'valid' => count($reasons) === 0,
            'reasons' => $reasons
        ];
    }
    
    /**
     * Check if user should see Account Management menu
     * 
     * @param Writer|null $user
     * @return bool
     */
    public static function canAccessAccountManagement($user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        // Only Admin and Reseller can access Account Management
        if (!in_array($user->user_type, ['Admin', 'Reseller'])) {
            return false;
        }
        
        // Must have account_management.view permission
        return self::hasPermission('account_management.view', $user);
    }
    
    /**
     * Get descendant users that current user can manage
     * 
     * @param Writer|null $user
     * @return Collection
     */
    public static function getManagedUsers($user = null): Collection
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user || $user->user_type === 'User') {
            return collect();
        }
        
        if ($user->user_type === 'Admin') {
            return Writer::where('user_type', '!=', 'Admin')->get();
        }
        
        // Reseller can manage only their direct children
        return $user->childUsers()->get();
    }
}
```

### 5.2 Existing Methods Enhancement

Enhance the existing `getGrantedKeys()` method to respect hierarchy:

```php
private static function getGrantedKeys($user = null): array
{
    if (!$user) {
        $user = Auth::user();
    }
    if (!$user) {
        return [];
    }

    // Admin always has everything
    if ($user->user_type === 'Admin') {
        return ['*'];
    }

    $cacheKey = $user->id;
    if (!isset(self::$cache[$cacheKey])) {
        $keys = [];

        // Get role permissions
        if ($user->role) {
            foreach ($user->role->permissions as $p) {
                // Only include if not revoked
                if (self::isPermissionActive($user, $p)) {
                    $keys[] = $p->key;
                }
            }
        }

        // Get direct user permissions (not revoked)
        foreach ($user->effectivePermissions as $p) {
            // Only include if not revoked and parent still has it
            if (self::isPermissionActive($user, $p)) {
                $keys[] = $p->key;
            }
        }

        self::$cache[$cacheKey] = array_unique($keys);
    }

    return self::$cache[$cacheKey];
}

/**
 * Check if a permission is active for a user
 */
private static function isPermissionActive(Writer $user, Permission $permission): bool
{
    // Check if user_permissions record is revoked
    $record = DB::table('user_permissions')
        ->where('user_id', $user->id)
        ->where('permission_id', $permission->id)
        ->first();
    
    if ($record && $record->revoked_at) {
        return false;
    }
    
    // If permission is inherited, check if parent still has it
    if ($record && $record->inherited_from_user_id) {
        $parent = Writer::find($record->inherited_from_user_id);
        if (!$parent) {
            return false;
        }
        
        // Recursively check parent's permissions
        return $parent->effectivePermissions()
                      ->where('permissions.id', $permission->id)
                      ->exists();
    }
    
    return true;
}
```

---

## 6. Middleware Updates

### 6.1 CheckPermission Middleware Enhancement

**File:** `app/Http/Middleware/CheckPermission.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permissionKey
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissionKey)
    {
        if (!Auth::check()) {
            return $this->unauthorized($request, 'User not authenticated');
        }

        $user = Auth::user();

        // Check if permission is active and not revoked
        if (!$user->hasPermission($permissionKey)) {
            return $this->unauthorized($request, 'Permission denied');
        }

        // Special check for account_management
        if (strpos($permissionKey, 'account_management') === 0) {
            if (!PermissionHelper::canAccessAccountManagement($user)) {
                return $this->unauthorized($request, 'Account Management access denied');
            }
        }

        return $next($request);
    }

    private function unauthorized(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        return response()->view('unauthorized_access', [
            'error' => 403,
            'error_msg' => $message
        ]);
    }
}
```

### 6.2 New Middleware: AccountManagementAccess

**File:** `app/Http/Middleware/AccountManagementAccess.php` (NEW)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;

class AccountManagementAccess
{
    /**
     * Only Admin and Reseller can access Account Management
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->view('unauthorized_access', [
                'error' => 401,
                'error_msg' => 'Authentication required'
            ]);
        }

        if (!PermissionHelper::canAccessAccountManagement()) {
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'You do not have access to Account Management'
            ]);
        }

        return $next($request);
    }
}
```

### 6.3 New Middleware: HierarchyAccess

**File:** `app/Http/Middleware/HierarchyAccess.php` (NEW)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Writer;
use App\Helpers\PermissionHelper;

class HierarchyAccess
{
    /**
     * Middleware to check if current user can access/manage another user
     * Used for routes like /reseller/view-user/{id}, /admin/edit-user/{id}
     */
    public function handle(Request $request, Closure $next, $userIdParameter = 'userId')
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response('Unauthorized', 401);
        }

        // Get the target user ID from route parameter
        $targetUserId = $request->route($userIdParameter);
        if (!$targetUserId) {
            return response('User not found', 404);
        }

        $targetUser = Writer::find($targetUserId);
        if (!$targetUser) {
            return response('User not found', 404);
        }

        // Check if current user can access this target user
        if (!self::canAccessUser($currentUser, $targetUser)) {
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'You cannot access this user'
            ]);
        }

        return $next($request);
    }

    /**
     * Determine if current user can access target user
     */
    private static function canAccessUser(Writer $current, Writer $target): bool
    {
        // Admin can access all non-admin users
        if ($current->user_type === 'Admin') {
            return $target->user_type !== 'Admin' || $current->id === $target->id;
        }

        // Reseller can access only their descendants
        if ($current->user_type === 'Reseller') {
            return PermissionHelper::isDescendantOf($target, $current);
        }

        // Regular users cannot access other users
        return $current->id === $target->id;
    }
}
```

### 6.4 Register Middleware in Kernel

**File:** `app/Http/Kernel.php`

Add to `$routeMiddleware`:
```php
'account_management.access' => \App\Http\Middleware\AccountManagementAccess::class,
'hierarchy.access' => \App\Http\Middleware\HierarchyAccess::class,
```

---

## 7. Sidebar Menu Visibility Logic

### 7.1 Blade Helper Component

**File:** `resources/views/partials/permission-helper.blade.php` (Enhanced)

```blade
@php
use App\Helpers\PermissionHelper;

// Check if user can access Account Management
$canAccessAccountManagement = PermissionHelper::canAccessAccountManagement(Auth::user());
$accessibleModules = PermissionHelper::getAccessibleModules(Auth::user());
$userType = Auth::user()->user_type;
@endphp
```

### 7.2 Sidebar Template Updates

**File:** `resources/views/layouts/sidebar.blade.php` (Key Changes)

Replace the hardcoded user_type checks with permission helpers:

```blade
@php
use App\Helpers\PermissionHelper;
@endphp

<!-- Admin Section -->
@if($userType === 'Admin')
    <li class=""><a href="{{ url('/admin') }}" class="...">
        <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
    </a></li>
    
    @if(PermissionHelper::canAccessAccountManagement())
    <li class='sub-menu'>
        <a href="1" class="...">
            <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
        </a>
        <ul class='sub'>
            @if(Auth::user()->hasPermission('account_management.create'))
            <li><a href="{{ url('/admin/add-user') }}">Add Account</a></li>
            @endif
            @if(Auth::user()->hasPermission('account_management.view'))
            <li><a href="{{ url('admin/view-user') }}">View Account</a></li>
            @endif
        </ul>
    </li>
    @endif

<!-- Reseller Section -->
@elseif($userType === 'Reseller')
    <li class=""><a href="{{ url('/reseller') }}" class="...">
        <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
    </a></li>
    
    {{-- Only show Account Management if Reseller has permission --}}
    @if(PermissionHelper::canAccessAccountManagement() && Auth::user()->hasPermission('account_management.view'))
    <li class='sub-menu'>
        <a href="1" class="...">
            <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
        </a>
        <ul class='sub'>
            @if(Auth::user()->hasPermission('account_management.create'))
            <li><a href="{{ url('/reseller/add-user') }}">Add Account</a></li>
            @endif
            @if(Auth::user()->hasPermission('account_management.view'))
            <li><a href="{{ url('reseller/view-user') }}">View Account</a></li>
            @endif
        </ul>
    </li>
    @endif

<!-- User/Dealer Section -->
@else {{-- User or Dealer --}}
    <li class=""><a href="{{ url('/user') }}" class="...">
        <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
    </a></li>
    
    {{-- NO Account Management for regular users --}}
    
    @if(PermissionHelper::hasAnyModulePermission('device_management'))
    <li class='sub-menu'>
        <a href="1" class="...">
            <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
        </a>
        <ul class='sub'>
            @if(Auth::user()->hasPermission('device_management.view'))
            <li><a href="{{route('device.view')}}">View Device</a></li>
            @endif
        </ul>
    </li>
    @endif
@endif
```

---

## 8. Route Protection Strategy

### 8.1 Account Management Routes

**File:** `routes/web.php` (Changes)

```php
// Admin Account Management Routes
Route::middleware(['check.role:admin', 'account_management.access'])->prefix('admin')->group(function () {
    Route::get('/add-user', [RegisterController::class, 'showAddUser'])->middleware('check.permission:account_management.create');
    Route::post('/store-user', [RegisterController::class, 'storeUser'])->middleware('check.permission:account_management.create');
    Route::get('/view-user', [RegisterController::class, 'showViewUsers'])->middleware('check.permission:account_management.view');
    Route::get('/edit-user/{userId}', [RegisterController::class, 'editUser'])->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
    Route::patch('/update-user/{userId}', [RegisterController::class, 'updateUser'])->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
    Route::delete('/delete-user/{userId}', [RegisterController::class, 'deleteUser'])->middleware(['check.permission:account_management.delete', 'hierarchy.access:userId']);
});

// Reseller Account Management Routes
Route::middleware(['check.role:reseller', 'account_management.access'])->prefix('reseller')->group(function () {
    Route::get('/add-user', [RegisterController::class, 'showAddUser'])->middleware('check.permission:account_management.create');
    Route::post('/store-user', [RegisterController::class, 'storeUser'])->middleware('check.permission:account_management.create');
    Route::get('/view-user', [RegisterController::class, 'showViewUsers'])->middleware('check.permission:account_management.view');
    Route::get('/edit-user/{userId}', [RegisterController::class, 'editUser'])->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
    Route::patch('/update-user/{userId}', [RegisterController::class, 'updateUser'])->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
});

// User/Dealer routes - NO Account Management access
Route::middleware(['check.role:user'])->prefix('user')->group(function () {
    Route::get('/view-device', [DeviceController::class, 'showUserDevice']);
    // ... other user routes
});
```

### 8.2 API Routes Protection

**File:** `routes/api.php` (Changes)

```php
// Permission Management API
Route::middleware('auth:api')->group(function () {
    
    // Get permissions for a user (only accessible by admin or parent)
    Route::get('/users/{userId}/permissions', [PermissionManagementController::class, 'getUserPermissions'])
        ->middleware('hierarchy.access:userId');
    
    // Update permissions (only accessible by admin or parent)
    Route::patch('/users/{userId}/permissions', [PermissionManagementController::class, 'updateUserPermissions'])
        ->middleware(['hierarchy.access:userId', 'check.permission:permission_management.assign']);
});
```

---

## 9. Permission Assignment Validation Logic

### 9.1 Permission Assignment Service

**File:** `app/Services/PermissionAssignmentService.php` (NEW)

```php
<?php

namespace App\Services;

use App\Writer;
use App\Permission;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Exception;

class PermissionAssignmentService
{
    /**
     * Assign a permission to a user with full validation
     * 
     * @param Writer $grantor - User assigning permission
     * @param Writer $grantee - User receiving permission
     * @param Permission|string $permission - Permission to assign
     * @param bool $inherited - Is this inherited from parent?
     * @return bool
     * @throws Exception
     */
    public static function assignPermission(Writer $grantor, Writer $grantee, $permission, bool $inherited = false): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->firstOrFail();
        }

        // Validation
        $validation = PermissionHelper::validatePermissionGrant($grantor, $grantee, $permission);
        if (!$validation['valid']) {
            throw new Exception('Permission grant validation failed: ' . implode(', ', $validation['reasons']));
        }

        return DB::transaction(function () use ($grantor, $grantee, $permission, $inherited) {
            // Check if permission already exists
            $existing = DB::table('user_permissions')
                ->where('user_id', $grantee->id)
                ->where('permission_id', $permission->id)
                ->first();

            if ($existing) {
                // If revoked, reactivate it
                if ($existing->revoked_at) {
                    DB::table('user_permissions')
                        ->where('id', $existing->id)
                        ->update([
                            'revoked_at' => null,
                            'revocation_reason' => null,
                            'granted_by_user_id' => $grantor->id,
                            'updated_at' => now()
                        ]);
                }
                // Otherwise, already exists
                return true;
            }

            // Insert new permission
            DB::table('user_permissions')->insert([
                'user_id' => $grantee->id,
                'permission_id' => $permission->id,
                'granted_by_user_id' => $grantor->id,
                'inherited_from_user_id' => $inherited ? $grantor->id : null,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log the action
            self::logPermissionChange(
                $grantee->id,
                $permission->id,
                'grant',
                $grantor->id,
                $inherited ? 'inherit' : 'direct'
            );

            // Invalidate cache
            PermissionHelper::flushCache();

            return true;
        });
    }

    /**
     * Revoke a permission from a user and cascade to children
     * 
     * @param Writer $revoker - User revoking permission
     * @param Writer $revokeFrom - User losing permission
     * @param Permission|string $permission
     * @param string $reason - Reason for revocation
     * @return int - Number of users affected
     */
    public static function revokePermission(Writer $revoker, Writer $revokeFrom, $permission, string $reason = 'manual_revoke'): int
    {
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->firstOrFail();
        }

        // Validation - revoker must have permission and target must be descendant
        if (!PermissionHelper::canGrantPermission($revoker, $revokeFrom, $permission)) {
            throw new Exception('Cannot revoke this permission');
        }

        return DB::transaction(function () use ($revoker, $revokeFrom, $permission, $reason) {
            $affectedCount = 0;

            // Revoke from the user
            DB::table('user_permissions')
                ->where('user_id', $revokeFrom->id)
                ->where('permission_id', $permission->id)
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                    'revocation_reason' => $reason,
                    'updated_at' => now()
                ]);

            $affectedCount++;

            // Log the action
            self::logPermissionChange(
                $revokeFrom->id,
                $permission->id,
                'revoke',
                $revoker->id,
                'direct',
                count($revokeFrom->childUsers)
            );

            // Cascade revoke to all children
            $children = $revokeFrom->allDescendants()->get();
            foreach ($children as $child) {
                DB::table('user_permissions')
                    ->where('user_id', $child->id)
                    ->where('permission_id', $permission->id)
                    ->whereNull('revoked_at')
                    ->where('inherited_from_user_id', $revokeFrom->id)
                    ->update([
                        'revoked_at' => now(),
                        'revocation_reason' => 'parent_revoke',
                        'updated_at' => now()
                    ]);

                $affectedCount++;

                // Log cascade
                self::logPermissionChange(
                    $child->id,
                    $permission->id,
                    'cascade_revoke',
                    $revoker->id,
                    'parent_revoke'
                );
            }

            // Invalidate cache for all affected users
            PermissionHelper::flushCache();

            return $affectedCount;
        });
    }

    /**
     * Inherit parent's permissions when user is created/updated
     * 
     * @param Writer $parent
     * @param Writer $child
     * @return int - Number of permissions inherited
     */
    public static function inheritParentPermissions(Writer $parent, Writer $child): int
    {
        if (!PermissionHelper::isDescendantOf($child, $parent)) {
            throw new Exception('Child is not a descendant of parent');
        }

        return DB::transaction(function () use ($parent, $child) {
            $count = 0;

            // Get parent's effective permissions
            $parentPermissions = $parent->effectivePermissions()
                ->where('revoked_at', null)
                ->get();

            foreach ($parentPermissions as $permission) {
                // Check if child already has this permission
                $existing = DB::table('user_permissions')
                    ->where('user_id', $child->id)
                    ->where('permission_id', $permission->id)
                    ->first();

                if (!$existing) {
                    // Assign inherited permission
                    self::assignPermission($parent, $child, $permission, true);
                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * Log permission change for audit trail
     */
    private static function logPermissionChange(
        int $userId,
        int $permissionId,
        string $action,
        int $triggeredByUserId,
        string $triggerType,
        int $affectedChildrenCount = 0
    ): void {
        DB::table('permission_change_logs')->insert([
            'user_id' => $userId,
            'permission_id' => $permissionId,
            'action' => $action,
            'triggered_by_user_id' => $triggeredByUserId,
            'trigger_type' => $triggerType,
            'affected_children_count' => $affectedChildrenCount,
            'created_at' => now()
        ]);
    }
}
```

### 9.2 Controller Method for Permission Assignment

**File:** `app/Http/Controllers/PermissionManagementController.php` (New/Updated Methods)

```php
<?php

namespace App\Http\Controllers;

use App\Writer;
use App\Permission;
use App\Services\PermissionAssignmentService;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionManagementController extends Controller
{
    /**
     * Update child user permissions
     */
    public function updateChildUserPermissions(Request $request, $userId)
    {
        $currentUser = Auth::user();
        $targetUser = Writer::findOrFail($userId);

        // Validate hierarchy access
        if (!PermissionHelper::isDescendantOf($targetUser, $currentUser)) {
            return response()->json(['error' => 'Cannot manage this user'], 403);
        }

        // Get permission IDs from request
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        try {
            $addedCount = 0;
            $revokedCount = 0;

            // Get current permissions
            $currentPermissions = $targetUser->effectivePermissions()
                ->whereNull('user_permissions.revoked_at')
                ->pluck('permissions.id')
                ->toArray();

            // Add new permissions
            foreach ($permissions as $permission) {
                if (!in_array($permission->id, $currentPermissions)) {
                    PermissionAssignmentService::assignPermission(
                        $currentUser,
                        $targetUser,
                        $permission
                    );
                    $addedCount++;
                }
            }

            // Revoke removed permissions
            foreach ($currentPermissions as $permId) {
                if (!in_array($permId, $permissionIds)) {
                    $permission = Permission::find($permId);
                    PermissionAssignmentService::revokePermission(
                        $currentUser,
                        $targetUser,
                        $permission,
                        'manual_revoke'
                    );
                    $revokedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Permissions updated: {$addedCount} added, {$revokedCount} revoked",
                'added' => $addedCount,
                'revoked' => $revokedCount
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update permissions: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get assignable permissions for current user
     */
    public function getAssignablePermissions(Request $request)
    {
        $currentUser = Auth::user();
        $permissions = PermissionHelper::getAssignablePermissions($currentUser);

        return response()->json([
            'permissions' => $permissions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'key' => $p->key,
                    'label' => $p->label,
                    'module' => $p->module,
                    'action' => $p->action
                ];
            })
        ]);
    }
}
```

---

## 10. Implementation Phases

### Phase 1: Foundation (Priority: CRITICAL)
**Duration:** 1-2 weeks

1. Create all database migrations (schema changes)
2. Enhance Writer, Permission, Role models
3. Create PermissionAssignmentService
4. Update PermissionHelper with validation methods
5. Create CheckPermission middleware enhancements
6. Create AccountManagementAccess middleware

**Deliverables:**
- Database schema ready
- Core permission validation logic working
- Middleware protecting routes

**Testing:**
- Unit tests for permission validation
- Database integrity tests
- Middleware behavior tests

### Phase 2: Access Control (Priority: CRITICAL)
**Duration:** 1 week

1. Create HierarchyAccess middleware
2. Update all Account Management routes
3. Implement route protection in web.php
4. Update API routes in api.php
5. Test direct URL access validation

**Deliverables:**
- Account Management routes protected
- Hierarchy access enforced
- User cannot bypass permissions via direct URL

**Testing:**
- Integration tests for route protection
- Try accessing routes without permission
- Verify hierarchy is enforced

### Phase 3: UI/UX Updates (Priority: HIGH)
**Duration:** 1 week

1. Update sidebar template with permission helpers
2. Hide Account Management from Users/Dealers
3. Update form visibility for permission assignment
4. Create permission management views for Resellers
5. Add UI for cascading revocation warnings

**Deliverables:**
- Sidebar shows correct menu items
- No Account Management menu for Users
- Permission assignment UI works

**Testing:**
- Sidebar tests for different user types
- Form visibility tests
- Permission assignment workflow tests

### Phase 4: Cascading & Audit (Priority: HIGH)
**Duration:** 1 week

1. Implement cascading revocation logic
2. Create permission change log tracking
3. Build audit trail viewer
4. Add permission history reports
5. Implement cache invalidation strategy

**Deliverables:**
- Cascading revocations work
- Full audit trail available
- Cache properly invalidated

**Testing:**
- Cascade tests (revoke from parent, verify children affected)
- Audit trail tests
- Cache invalidation tests

### Phase 5: Testing & Hardening (Priority: HIGH)
**Duration:** 1-2 weeks

1. Comprehensive security testing
2. Load testing for permission checks
3. Edge case handling (circular references, deep hierarchies)
4. Documentation & code comments
5. Migration rollback procedures

**Deliverables:**
- Full test suite
- Security audit passed
- Documentation complete

---

## 11. Security Considerations

### 11.1 Prevention of Permission Escalation

1. **Server-side validation only** - Never trust client-side permission checks
2. **Hierarchy enforcement** - User can only assign permissions they have AND target is descendant
3. **Revocation cascade** - Parent losing permission cascades to children
4. **Audit trail** - All permission changes logged and traceable

### 11.2 Defense in Depth

1. **Multiple validation layers:**
   - Middleware (route-level)
   - Controller validation
   - Model-level constraints
   - Service layer validation

2. **Account Management specific:**
   - User type check (Admin/Reseller only)
   - Permission check (account_management.view)
   - Hierarchy check (managing own descendants only)
   - Sidebar menu hidden for Users

### 11.3 Audit & Compliance

1. Track who granted/revoked permissions and when
2. Log failed access attempts
3. Maintain audit trail for at least 90 days
4. Generate permission audit reports

### 11.4 Edge Cases

1. **Circular references:** Prevent parent-child cycles in hierarchy
2. **Deep hierarchies:** Limit max depth to prevent infinite loops
3. **Bulk revocations:** Handle large-scale permission revocations efficiently
4. **Race conditions:** Use database transactions for all permission changes

---

## 12. Testing Strategy

### 12.1 Unit Tests

```php
// Tests/Feature/PermissionHierarchyTest.php
class PermissionHierarchyTest extends TestCase {
    
    public function test_reseller_cannot_assign_permission_they_dont_have() { }
    public function test_user_cannot_see_account_management_menu() { }
    public function test_cascading_revocation_works() { }
    public function test_child_cannot_have_more_permissions_than_parent() { }
    public function test_permission_revocation_invalidates_cache() { }
}
```

### 12.2 Feature/Integration Tests

```php
// Tests/Feature/AccountManagementAccessTest.php
class AccountManagementAccessTest extends TestCase {
    
    public function test_admin_can_access_account_management() { }
    public function test_reseller_can_access_own_child_accounts() { }
    public function test_reseller_cannot_access_other_resellers_children() { }
    public function test_user_cannot_access_account_management_routes() { }
    public function test_direct_url_access_blocked_for_unauthorized_users() { }
}
```

### 12.3 Security Tests

```php
// Tests/Security/PermissionEscalationTest.php
class PermissionEscalationTest extends TestCase {
    
    public function test_cannot_elevate_own_permissions() { }
    public function test_cannot_assign_unowned_permissions() { }
    public function test_cannot_access_outside_hierarchy() { }
    public function test_api_validates_permissions() { }
}
```

---

## 13. Key Files to Create/Modify

### Create:
1. `database/migrations/2026_06_02_add_hierarchy_to_writers_table.php`
2. `database/migrations/2026_06_02_enhance_user_permissions_table.php`
3. `database/migrations/2026_06_02_create_permission_change_logs_table.php`
4. `database/migrations/2026_06_02_create_role_hierarchy_table.php`
5. `app/Http/Middleware/AccountManagementAccess.php`
6. `app/Http/Middleware/HierarchyAccess.php`
7. `app/Services/PermissionAssignmentService.php`
8. `resources/views/partials/permission-helper.blade.php` (Enhanced)

### Modify:
1. `app/Writer.php` - Add relationship methods
2. `app/Permission.php` - Add utility methods
3. `app/Role.php` - Add hierarchy methods
4. `app/Helpers/PermissionHelper.php` - Add new validation methods
5. `app/Http/Middleware/CheckPermission.php` - Enhance with hierarchy checks
6. `app/Http/Controllers/PermissionManagementController.php` - Update permission assignment
7. `app/Http/Kernel.php` - Register new middleware
8. `routes/web.php` - Update route protection
9. `routes/api.php` - Update API route protection
10. `resources/views/layouts/sidebar.blade.php` - Update menu visibility
11. `resources/views/admin/manage_permissions.blade.php` - Update UI for permission assignment
12. `resources/views/reseller/manage_child_permissions.blade.php` - Create Reseller permission UI

---

## 14. Configuration & Constants

### 14.1 Permission Modules

```php
// config/permissions.php (NEW)
return [
    'modules' => [
        'account_management' => [
            'label' => 'Account Management',
            'accessible_to' => ['Admin', 'Reseller'],
            'actions' => ['view', 'create', 'edit', 'delete']
        ],
        'device_management' => [
            'label' => 'Device Management',
            'accessible_to' => ['Admin', 'Reseller', 'User', 'Dealer'],
            'actions' => ['view', 'create', 'edit', 'delete']
        ],
        'settings_management' => [
            'label' => 'Settings Management',
            'accessible_to' => ['Admin', 'Reseller', 'User'],
            'actions' => ['view', 'create', 'edit', 'delete', 'assign_bulk']
        ],
    ],
    
    'max_hierarchy_depth' => 5,
    'audit_retention_days' => 90,
];
```

### 14.2 Role Constants

```php
// In Role.php
const ADMIN = 'Admin';
const RESELLER = 'Reseller';
const USER = 'User';
const DEALER = 'Dealer';

const HIERARCHY_RULES = [
    self::ADMIN => [self::RESELLER],
    self::RESELLER => [self::USER, self::DEALER],
    self::USER => [],
    self::DEALER => []
];
```

---

## 15. Migration Path & Backward Compatibility

### 15.1 Data Migration Strategy

1. Backfill `role_id` from `user_type`
2. Backfill `parent_user_id` from `created_by`
3. Migrate existing role permissions to user_permissions
4. Initialize audit log for existing permissions

### 15.2 Rollback Plan

1. All migrations are reversible
2. Keep old permission checks in place temporarily
3. Feature flags to toggle new vs old behavior
4. Parallel testing before full deployment

---

## 16. Success Metrics

### 16.1 Functional Metrics
- All Account Management routes protected: 100%
- Permission hierarchy enforced: 100%
- Cascading revocations working: 100%
- Audit trail complete: 100%

### 16.2 Performance Metrics
- Permission check < 5ms (cached)
- Permission assignment < 200ms
- Cascading revocation < 1s (per 100 children)
- No N+1 queries in sidebar

### 16.3 Security Metrics
- Zero unauthorized access attempts succeed
- All permission violations logged
- Audit trail 100% complete
- No permission escalation possible

---

## 17. Documentation & Training

### 17.1 Developer Documentation
- How permission inheritance works
- How to add new permissions
- How to protect new routes
- Permission testing guidelines

### 17.2 Admin Documentation
- How to assign permissions
- How cascading works
- Audit trail review
- Troubleshooting guide

### 17.3 User Documentation
- What permissions do
- Permission hierarchy visualization
- FAQ

---

## Appendix A: Complete Permission List

```sql
-- Core permissions
INSERT INTO permissions (key, module, action, label, description, order, is_active) VALUES
-- Account Management
('account_management.view', 'account_management', 'view', 'View Accounts', 'View user/account list', 1, 1),
('account_management.create', 'account_management', 'create', 'Create Account', 'Create new user/account', 2, 1),
('account_management.edit', 'account_management', 'edit', 'Edit Account', 'Edit user/account details', 3, 1),
('account_management.delete', 'account_management', 'delete', 'Delete Account', 'Delete user/account', 4, 1),

-- Device Management
('device_management.view', 'device_management', 'view', 'View Devices', 'View device list', 1, 1),
('device_management.create', 'device_management', 'create', 'Add Device', 'Create new device', 2, 1),
('device_management.edit', 'device_management', 'edit', 'Edit Device', 'Edit device details', 3, 1),
('device_management.delete', 'device_management', 'delete', 'Delete Device', 'Delete device', 4, 1),

-- Settings Management
('settings_management.view', 'settings_management', 'view', 'View Settings', 'View settings/templates', 1, 1),
('settings_management.create', 'settings_management', 'create', 'Create Settings', 'Create new settings', 2, 1),
('settings_management.edit', 'settings_management', 'edit', 'Edit Settings', 'Edit settings', 3, 1),
('settings_management.delete', 'settings_management', 'delete', 'Delete Settings', 'Delete settings', 4, 1),
('settings_management.assign_bulk', 'settings_management', 'assign_bulk', 'Bulk Assign', 'Bulk assign settings to devices', 5, 1);
```

---

**End of Implementation Plan**

This comprehensive plan provides everything needed to implement a secure, scalable hierarchical permission inheritance system. Prioritize Phase 1 and Phase 2 for the critical security aspects, then proceed with UI/UX and auditing phases.
