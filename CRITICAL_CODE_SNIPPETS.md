# Critical Code Snippets - Implementation Reference

## 1. Database Migrations

### Migration 1: Add Hierarchy to Writers Table

**File:** `database/migrations/2026_06_02_add_hierarchy_to_writers_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('writers', function (Blueprint $table) {
            // Add parent user hierarchy
            if (!Schema::hasColumn('writers', 'parent_user_id')) {
                $table->unsignedBigInteger('parent_user_id')->nullable()->after('created_by');
                $table->foreign('parent_user_id')->references('id')->on('writers')->onDelete('cascade');
                $table->index('parent_user_id');
            }
            
            // Add role assignment
            if (!Schema::hasColumn('writers', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('parent_user_id');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
                $table->index('role_id');
            }
            
            // Add max hierarchy depth
            if (!Schema::hasColumn('writers', 'max_child_depth')) {
                $table->integer('max_child_depth')->default(0)->after('role_id');
            }
        });
    }

    public function down()
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->dropForeign(['parent_user_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['parent_user_id', 'role_id', 'max_child_depth']);
        });
    }
};
```

### Migration 2: Enhance User Permissions Table

**File:** `database/migrations/2026_06_02_enhance_user_permissions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_permissions', 'granted_by_user_id')) {
                $table->unsignedBigInteger('granted_by_user_id')->nullable()->after('permission_id');
                $table->foreign('granted_by_user_id')->references('id')->on('writers')->onDelete('set null');
                $table->index('granted_by_user_id');
            }
            
            if (!Schema::hasColumn('user_permissions', 'inherited_from_user_id')) {
                $table->unsignedBigInteger('inherited_from_user_id')->nullable()->after('granted_by_user_id');
                $table->foreign('inherited_from_user_id')->references('id')->on('writers')->onDelete('set null');
                $table->index('inherited_from_user_id');
            }
            
            if (!Schema::hasColumn('user_permissions', 'granted_at')) {
                $table->timestamp('granted_at')->nullable()->default(\DB::raw('CURRENT_TIMESTAMP'))->after('inherited_from_user_id');
            }
            
            if (!Schema::hasColumn('user_permissions', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('granted_at');
                $table->index('revoked_at');
            }
            
            if (!Schema::hasColumn('user_permissions', 'revocation_reason')) {
                $table->string('revocation_reason')->nullable()->after('revoked_at');
            }
        });
    }

    public function down()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign(['granted_by_user_id']);
            $table->dropForeign(['inherited_from_user_id']);
            $table->dropColumn([
                'granted_by_user_id', 
                'inherited_from_user_id', 
                'granted_at', 
                'revoked_at', 
                'revocation_reason'
            ]);
        });
    }
};
```

### Migration 3: Create Permission Change Logs

**File:** `database/migrations/2026_06_02_create_permission_change_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permission_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->enum('action', ['grant', 'revoke', 'inherit', 'cascade_revoke']);
            $table->unsignedBigInteger('triggered_by_user_id');
            $table->enum('trigger_type', ['direct', 'parent_revoke', 'hierarchy_change', 'manual_revoke'])->nullable();
            $table->integer('affected_children_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('writers')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('triggered_by_user_id')->references('id')->on('writers')->onDelete('cascade');
            
            $table->index(['user_id', 'permission_id']);
            $table->index('triggered_by_user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_change_logs');
    }
};
```

---

## 2. Model Enhancements

### Writer Model Relationships

**File:** `app/Writer.php` (Add these methods)

```php
/**
 * Get this user's parent user
 */
public function parentUser()
{
    return $this->belongsTo(Writer::class, 'parent_user_id');
}

/**
 * Get this user's direct child users
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
    return Writer::where('parent_user_id', $this->id)
        ->with('allDescendants')
        ->get();
}

/**
 * Get user's role
 */
public function role()
{
    return $this->belongsTo(Role::class, 'role_id');
}

/**
 * Get user's effective permissions (not revoked)
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
 * Check if user can assign a permission
 */
public function canAssignPermission(Permission $permission): bool
{
    if ($this->user_type === 'Admin') {
        return true;
    }
    
    return $this->effectivePermissions()
                ->where('permissions.id', $permission->id)
                ->exists();
}

/**
 * Get ancestors (parents up the hierarchy)
 */
public function getAncestors()
{
    $ancestors = collect();
    $current = $this->parentUser;
    
    while ($current) {
        $ancestors->push($current);
        $current = $current->parentUser;
    }
    
    return $ancestors;
}
```

---

## 3. PermissionHelper Key Methods

**File:** `app/Helpers/PermissionHelper.php` (Add these methods)

```php
/**
 * Check if one user is a descendant of another
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
 * Validate permission grant before assignment
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
    if ($grantor->user_type !== 'Admin' && !self::isDescendantOf($grantee, $grantor)) {
        $reasons[] = "Grantee is not a descendant of grantor";
    }
    
    return [
        'valid' => count($reasons) === 0,
        'reasons' => $reasons
    ];
}

/**
 * Check if user can access Account Management
 */
public static function canAccessAccountManagement($user = null): bool
{
    if (!$user) {
        $user = Auth::user();
    }
    
    if (!$user) {
        return false;
    }
    
    // Only Admin and Reseller
    if (!in_array($user->user_type, ['Admin', 'Reseller'])) {
        return false;
    }
    
    // Must have permission
    return self::hasPermission('account_management.view', $user);
}

/**
 * Get assignable permissions for a user
 */
public static function getAssignablePermissions(Writer $user)
{
    if ($user->user_type === 'Admin') {
        return Permission::where('is_active', 1)->get();
    }
    
    return $user->effectivePermissions()->get();
}
```

---

## 4. PermissionAssignmentService

**File:** `app/Services/PermissionAssignmentService.php` (Complete file)

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
     * Assign a permission with validation
     */
    public static function assignPermission(
        Writer $grantor, 
        Writer $grantee, 
        $permission, 
        bool $inherited = false
    ): bool {
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->firstOrFail();
        }

        // Validation
        $validation = PermissionHelper::validatePermissionGrant($grantor, $grantee, $permission);
        if (!$validation['valid']) {
            throw new Exception('Permission grant validation failed: ' . implode(', ', $validation['reasons']));
        }

        return DB::transaction(function () use ($grantor, $grantee, $permission, $inherited) {
            // Check if already exists
            $existing = DB::table('user_permissions')
                ->where('user_id', $grantee->id)
                ->where('permission_id', $permission->id)
                ->first();

            if ($existing) {
                if ($existing->revoked_at) {
                    // Reactivate revoked permission
                    DB::table('user_permissions')
                        ->where('id', $existing->id)
                        ->update([
                            'revoked_at' => null,
                            'revocation_reason' => null,
                            'granted_by_user_id' => $grantor->id,
                            'updated_at' => now()
                        ]);
                }
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

            // Log action
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
     * Revoke permission and cascade to children
     */
    public static function revokePermission(
        Writer $revoker, 
        Writer $revokeFrom, 
        $permission, 
        string $reason = 'manual_revoke'
    ): int {
        if (is_string($permission)) {
            $permission = Permission::where('key', $permission)->firstOrFail();
        }

        // Validation
        if (!PermissionHelper::canGrantPermission($revoker, $revokeFrom, $permission)) {
            throw new Exception('Cannot revoke this permission');
        }

        return DB::transaction(function () use ($revoker, $revokeFrom, $permission, $reason) {
            $affectedCount = 0;

            // Revoke from user
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

            // Log action
            self::logPermissionChange(
                $revokeFrom->id,
                $permission->id,
                'revoke',
                $revoker->id,
                'direct',
                $revokeFrom->childUsers->count()
            );

            // Cascade to children
            $children = $revokeFrom->allDescendants();
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

            // Invalidate cache
            PermissionHelper::flushCache();

            return $affectedCount;
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

---

## 5. Middleware

### AccountManagementAccess Middleware

**File:** `app/Http/Middleware/AccountManagementAccess.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;

class AccountManagementAccess
{
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

### HierarchyAccess Middleware

**File:** `app/Http/Middleware/HierarchyAccess.php`

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
    public function handle(Request $request, Closure $next, $userIdParameter = 'userId')
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response('Unauthorized', 401);
        }

        $targetUserId = $request->route($userIdParameter);
        if (!$targetUserId) {
            return response('User not found', 404);
        }

        $targetUser = Writer::find($targetUserId);
        if (!$targetUser) {
            return response('User not found', 404);
        }

        // Check if current user can access this target user
        if (!$this->canAccessUser($currentUser, $targetUser)) {
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'You cannot access this user'
            ]);
        }

        return $next($request);
    }

    private function canAccessUser(Writer $current, Writer $target): bool
    {
        // Admin can access all non-admin users
        if ($current->user_type === 'Admin') {
            return $target->user_type !== 'Admin' || $current->id === $target->id;
        }

        // Reseller can access only descendants
        if ($current->user_type === 'Reseller') {
            return PermissionHelper::isDescendantOf($target, $current);
        }

        // Regular users cannot access others
        return $current->id === $target->id;
    }
}
```

---

## 6. Route Protection Examples

**File:** `routes/web.php` (Key sections)

```php
// Admin Account Management - Protected routes
Route::middleware(['check.role:admin', 'account_management.access'])->prefix('admin')->group(function () {
    Route::get('/add-user', [RegisterController::class, 'showAddUser'])
        ->middleware('check.permission:account_management.create');
    
    Route::get('/view-user', [RegisterController::class, 'showViewUsers'])
        ->middleware('check.permission:account_management.view');
    
    Route::get('/edit-user/{userId}', [RegisterController::class, 'editUser'])
        ->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
});

// Reseller Account Management - Protected routes
Route::middleware(['check.role:reseller', 'account_management.access'])->prefix('reseller')->group(function () {
    Route::get('/add-user', [RegisterController::class, 'showAddUser'])
        ->middleware('check.permission:account_management.create');
    
    Route::get('/view-user', [RegisterController::class, 'showViewUsers'])
        ->middleware('check.permission:account_management.view');
    
    Route::get('/edit-user/{userId}', [RegisterController::class, 'editUser'])
        ->middleware(['check.permission:account_management.edit', 'hierarchy.access:userId']);
});

// User/Dealer routes - NO Account Management access at all
Route::middleware(['check.role:user'])->prefix('user')->group(function () {
    Route::get('/view-device', [DeviceController::class, 'showUserDevice'])
        ->middleware('check.permission:device_management.view');
    // ... other user routes (no account management)
});
```

---

## 7. Kernel Middleware Registration

**File:** `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // ... existing middleware
    'check.permission' => \App\Http\Middleware\CheckPermission::class,
    'check.role' => \App\Http\Middleware\CheckRole::class,
    'account_management.access' => \App\Http\Middleware\AccountManagementAccess::class,
    'hierarchy.access' => \App\Http\Middleware\HierarchyAccess::class,
];
```

---

## 8. Sidebar Permission Check Helper

**File:** `resources/views/partials/permission-helper.blade.php`

```blade
@php
use App\Helpers\PermissionHelper;

$user = Auth::user();
$canAccessAccountManagement = PermissionHelper::canAccessAccountManagement($user);
$hasAccountManagementView = $user->hasPermission('account_management.view');
$hasAccountManagementCreate = $user->hasPermission('account_management.create');
$userType = $user->user_type;
@endphp
```

---

## 9. Sidebar Menu Logic

**File:** `resources/views/layouts/sidebar.blade.php` (Key section)

```blade
@include('partials.permission-helper')

<!-- Admin & Reseller Account Management Section -->
@if(in_array($userType, ['Admin', 'Reseller']) && $canAccessAccountManagement)
<li class='sub-menu'>
    <a href="javascript:void(0)" class="hvr-bounce-to-right-sidebar-parent">
        <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
    </a>
    <ul class='sub'>
        @if($hasAccountManagementCreate)
        <li><a href="{{ url($userType === 'Admin' ? '/admin/add-user' : '/reseller/add-user') }}">
            Add Account
        </a></li>
        @endif
        
        @if($hasAccountManagementView)
        <li><a href="{{ url($userType === 'Admin' ? '/admin/view-user' : '/reseller/view-user') }}">
            View Account
        </a></li>
        @endif
    </ul>
</li>
@endif

<!-- User/Dealer - NO Account Management menu -->
@if($userType === 'User' || $userType === 'Dealer')
    {{-- Account Management menu completely hidden for regular users --}}
@endif
```

---

## 10. Testing Examples

### Unit Test: Permission Validation

```php
// tests/Unit/PermissionHierarchyTest.php
public function test_reseller_cannot_assign_permission_they_dont_have()
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

    $perm = Permission::create([
        'key' => 'account_management.create',
        'module' => 'account_management',
        'action' => 'create'
    ]);

    // Reseller doesn't have permission
    $this->expectException(Exception::class);
    PermissionAssignmentService::assignPermission($reseller, $user, $perm);
}
```

### Feature Test: Route Protection

```php
// tests/Feature/AccountManagementAccessTest.php
public function test_user_cannot_access_view_user_route()
{
    $user = Writer::factory()->create(['user_type' => 'User']);
    
    $response = $this->actingAs($user)
        ->get('/user/view-user');
    
    // Route should not exist or should redirect
    $this->assertFalse(
        Route::has('user.view-user'),
        'User route should not have view-user'
    );
}

public function test_admin_can_access_account_management()
{
    $admin = Writer::factory()->create(['user_type' => 'Admin']);
    
    // Give permission
    $perm = Permission::where('key', 'account_management.view')->first();
    $admin->permissions()->attach($perm->id);
    
    $response = $this->actingAs($admin)
        ->get('/admin/view-user');
    
    $this->assertEquals(200, $response->status());
}
```

---

## 11. Key Configuration Values

### Role Hierarchy
```php
// Role slug → Can manage role slugs
Admin    → [Reseller]
Reseller → [User, Dealer]
User     → []
Dealer   → []
```

### Permission Module Access
```php
account_management  → Admin, Reseller (only own children)
device_management   → Admin, Reseller, User, Dealer
settings_management → Admin, Reseller, User
```

---

**Quick Start Order:**

1. Create & run Migration 1 (writers table)
2. Create & run Migration 2 (user_permissions table)
3. Create & run Migration 3 (permission_change_logs table)
4. Update Writer model with relationships
5. Create PermissionAssignmentService
6. Update PermissionHelper with validation methods
7. Create middleware (AccountManagement, HierarchyAccess)
8. Register middleware in Kernel
9. Update routes in web.php
10. Update sidebar template
11. Test & verify

---

**Version:** 1.0  
**Last Updated:** 2026-06-02
