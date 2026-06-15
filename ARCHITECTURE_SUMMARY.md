# Hierarchical Permission System - Architecture Summary

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      USER HIERARCHY                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│                          ADMIN                                   │
│                     (Full System Access)                         │
│                      user_type = 'Admin'                         │
│                                                                   │
│                 ┌──────────────────────────┐                     │
│                 │    Can assign to all     │                     │
│                 │   resellers & users      │                     │
│                 └──────────────────────────┘                     │
│                           │                                       │
│    ┌──────────────────────┴──────────────────────┐               │
│    │                                              │               │
│   RESELLER A                                   RESELLER B        │
│ (Limited Access)                            (Limited Access)     │
│ user_type = 'Reseller'                     user_type = 'Reseller'│
│                                                                   │
│ parent_user_id = Admin.id                                        │
│ Permissions ⊆ Admin's permissions                                │
│                                                                   │
│  ┌─────────────────────┐                                         │
│  │ Can assign to own    │                                        │
│  │ children only        │                                        │
│  └─────────────────────┘                                         │
│        │                                                          │
│   ┌────┴─────┐                                                   │
│   │           │                                                  │
│  USER-A1    USER-A2                                              │
│(No Account   (No Account                                         │
│Management)   Management)                                         │
│ user_type =  user_type =                                         │
│  'User'       'User'                                             │
│                                                                   │
│ parent_user_id = Reseller A.id                                   │
│ Permissions ⊆ Reseller A's permissions                           │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Key Characteristics

### User Types & Their Capabilities

| Feature | Admin | Reseller | User/Dealer |
|---------|-------|----------|-------------|
| **View Account Mgmt** | ✅ | ✅ (own children) | ❌ |
| **Create Account** | ✅ | ✅ (for children) | ❌ |
| **Edit Account** | ✅ | ✅ (own children) | ❌ |
| **Delete Account** | ✅ | ✅ (own children) | ❌ |
| **View Devices** | ✅ | ✅ (own) | ✅ (own) |
| **Manage Settings** | ✅ | ✅ (own) | ✅ (own) |
| **Assign Permissions** | ✅ (to all) | ✅ (to children) | ❌ |
| **Max Permission Level** | All | ≤ Admin | ≤ Parent |

## Permission Inheritance Rules

### Rule 1: Parent Permission Constraint
```
Child's Permissions ⊆ Parent's Permissions

If Parent has [account_management.view, device_management.view, device_management.edit]
Child can have at most [account_management.view, device_management.view, device_management.edit]
Child CANNOT have [settings_management.create] (parent doesn't have it)
```

### Rule 2: Assignment Validation
```
Grantor can assign Permission P to Grantee IF:
1. Grantor has Permission P (directly or inherited)
2. Grantee is a descendant of Grantor in hierarchy
3. Permission P is valid for Grantee's role
4. Grantor is Admin OR Grantor is Reseller managing children
```

### Rule 3: Cascading Revocation
```
When Parent loses Permission P:
├─ Parent's Permission P revoked
├─ All Children's inherited Permission P revoked
│  └─ With reason "parent_revoke"
├─ All Grandchildren's inherited Permission P revoked
│  └─ With reason "parent_revoke"
└─ Audit log tracks all revocations
```

### Rule 4: Account Management Access
```
User can access Account Management IF:
1. user_type IN ['Admin', 'Reseller'] 
2. has permission 'account_management.view'
3. accessing own hierarchy level only
   - Admin: all non-admin users
   - Reseller: only direct children
   - User/Dealer: BLOCKED at all levels
```

## Database Schema Changes

### New/Enhanced Tables

#### 1. writers (existing, enhanced)
```sql
Existing columns:
- id, name, email, password, user_type, created_by, ...

New columns:
+ parent_user_id       -- Links to parent user (null for Admin)
+ role_id              -- User's role assignment
+ max_child_depth      -- Max hierarchy depth allowed

Indexes:
+ idx_parent_user_id
+ idx_role_id
```

#### 2. user_permissions (existing, enhanced)
```sql
Existing columns:
- id, user_id, permission_id

New columns:
+ granted_by_user_id         -- Who granted this permission
+ inherited_from_user_id     -- If not null, inherited from this user
+ granted_at                 -- When permission granted (timestamp)
+ revoked_at                 -- When revoked (null = active)
+ revocation_reason          -- Why revoked (parent_revoke, manual_revoke, etc)

Soft delete via revoked_at:
- Permission is "active" when revoked_at IS NULL
- Permission is "revoked" when revoked_at IS NOT NULL
```

#### 3. permission_change_logs (new)
```sql
id, user_id, permission_id, action, triggered_by_user_id, 
trigger_type, affected_children_count, notes, created_at

Actions: grant, revoke, inherit, cascade_revoke
Indexes on: user_id+permission_id, triggered_by_user_id, action, created_at
```

#### 4. role_hierarchy (new)
```sql
id, parent_role_id, child_role_id, allowed_depth

Example:
- admin → reseller (depth 2)
- reseller → user (depth 1)
- reseller → dealer (depth 1)
```

## Permission Structure

### Naming Convention
```
{module}.{action}

Examples:
- account_management.view
- account_management.create
- account_management.edit
- account_management.delete
- device_management.view
- device_management.create
- settings_management.view
- settings_management.assign_bulk
```

### Modules & Actions
```
account_management:     view, create, edit, delete
device_management:      view, create, edit, delete
settings_management:    view, create, edit, delete, assign_bulk
certificate_management: view, create, edit, delete (if added)
```

## Multi-Layer Security Architecture

### Layer 1: Sidebar Menu (Client-side Prevention)
```
├─ Admin view
│  └─ Account Management → Permission helper checks
├─ Reseller view
│  └─ Account Management → Permission helper checks
└─ User/Dealer view
   └─ NO Account Management menu (completely hidden)
```

### Layer 2: Route Protection (Server-side Enforcement)
```
Routes → Middleware Stack:
├─ check.role:admin         (only Admin can access)
├─ account_management.access (Admin/Reseller only)
├─ check.permission:...     (specific permission required)
└─ hierarchy.access:userId  (can only manage descendants)
```

### Layer 3: Controller Validation
```
Controller receives request
├─ Verify user authentication
├─ Verify hierarchy relationship
├─ Verify permission ownership
└─ Execute action or reject
```

### Layer 4: Service Layer Validation
```
PermissionAssignmentService::assignPermission()
├─ Validate grantor has permission
├─ Validate grantee is descendant
├─ Validate permission for role
├─ Apply to database
└─ Log action & invalidate cache
```

### Layer 5: Database Constraints
```
Foreign keys:
├─ parent_user_id → writers(id)
├─ role_id → roles(id)
├─ granted_by_user_id → writers(id)
└─ inherited_from_user_id → writers(id)

Unique constraints:
└─ user_permissions(user_id, permission_id)
```

## Permission Flow Diagram

### Granting Permission

```
Admin/Reseller attempts to grant permission
│
├─ PermissionHelper::validatePermissionGrant()
│  ├─ Check: Grantor has permission?
│  ├─ Check: Grantee is descendant?
│  └─ Check: Valid for grantee's role?
│
├─ If valid → PermissionAssignmentService::assignPermission()
│  ├─ Check if permission already exists
│  ├─ Insert/Update in user_permissions
│  ├─ Log to permission_change_logs
│  └─ Flush permission cache
│
└─ If invalid → Reject with reasons
```

### Revoking Permission

```
Admin/Reseller attempts to revoke permission
│
├─ PermissionHelper::validatePermissionGrant()
│  └─ (same checks as above)
│
├─ If valid → PermissionAssignmentService::revokePermission()
│  ├─ Revoke from target user
│  ├─ Log revocation
│  ├─ Get all descendants
│  ├─ For each descendant with inherited permission:
│  │  ├─ Check if inherited from revoked user
│  │  ├─ Revoke with reason "parent_revoke"
│  │  └─ Log cascade_revoke
│  └─ Flush permission cache
│
└─ If invalid → Reject with reasons
```

### Checking Permission

```
Route middleware checks: user->hasPermission('account_management.view')
│
├─ PermissionHelper::hasPermission()
│  ├─ Check cache (per-request)
│  ├─ If Admin → return true
│  ├─ Otherwise:
│  │  ├─ Get role permissions (non-revoked)
│  │  ├─ Get direct permissions (non-revoked)
│  │  ├─ Get inherited permissions (non-revoked)
│  │  └─ Merge & cache
│  └─ Check if key in list
│
├─ Special check for account_management:
│  ├─ PermissionHelper::canAccessAccountManagement()
│  ├─ Verify user_type in ['Admin', 'Reseller']
│  └─ Verify hierarchy (managing own descendants)
│
└─ Return true/false → Allow/Deny access
```

## Implementation Priorities

### Phase 1: Foundation (Weeks 1-2) ⭐ CRITICAL
- Database schema migrations
- Model enhancements (Writer, Permission, Role)
- PermissionAssignmentService
- PermissionHelper validation methods
- Core middleware updates

**Risk Level:** High - touches core data model

### Phase 2: Access Control (Week 3) ⭐ CRITICAL
- HierarchyAccess middleware
- Update Account Management routes
- Route protection in web.php
- API route protection
- Direct URL access validation

**Risk Level:** High - security critical

### Phase 3: UI/UX Updates (Week 4)
- Sidebar menu visibility
- Permission assignment UI
- Reseller permission management views

**Risk Level:** Medium - visual/UX layer

### Phase 4: Cascading & Audit (Week 5)
- Cascading revocation implementation
- Audit trail viewer
- Permission history reports

**Risk Level:** Medium - batch operations

### Phase 5: Testing & Hardening (Weeks 6-7)
- Comprehensive test suite
- Security audit
- Performance testing
- Documentation

**Risk Level:** Low - validation phase

## File Structure Reference

### Create Files
```
database/migrations/
├─ 2026_06_02_add_hierarchy_to_writers_table.php
├─ 2026_06_02_enhance_user_permissions_table.php
├─ 2026_06_02_create_permission_change_logs_table.php
└─ 2026_06_02_create_role_hierarchy_table.php

app/Http/Middleware/
├─ AccountManagementAccess.php (NEW)
└─ HierarchyAccess.php (NEW)

app/Services/
└─ PermissionAssignmentService.php (NEW)

resources/views/
└─ partials/permission-helper.blade.php (NEW/Enhanced)
```

### Modify Files
```
app/
├─ Writer.php (+ relationships & methods)
├─ Permission.php (+ utility methods)
├─ Role.php (+ hierarchy methods)
├─ Helpers/PermissionHelper.php (+ validation methods)
└─ Http/
   ├─ Middleware/CheckPermission.php (enhance)
   ├─ Controllers/PermissionManagementController.php (update)
   └─ Kernel.php (register middleware)

routes/
├─ web.php (route protection)
└─ api.php (API protection)

resources/views/
├─ layouts/sidebar.blade.php (menu visibility)
├─ admin/manage_permissions.blade.php (UI)
└─ reseller/manage_child_permissions.blade.php (UI)
```

## Quick Reference: Permission Check Examples

### Check if user can perform action
```php
$user->hasPermission('account_management.view')
```

### Check if user can see Account Management
```php
PermissionHelper::canAccessAccountManagement($user)
```

### Check if user is descendant
```php
PermissionHelper::isDescendantOf($user, $parent)
```

### Validate before granting permission
```php
$validation = PermissionHelper::validatePermissionGrant($grantor, $grantee, $permission);
if ($validation['valid']) {
    PermissionAssignmentService::assignPermission($grantor, $grantee, $permission);
}
```

### Revoke permission (cascades to children)
```php
PermissionAssignmentService::revokePermission($revoker, $revokeFrom, $permission, 'manual_revoke');
```

## Testing Checklist

- [ ] Admin can grant permissions to Resellers
- [ ] Reseller can grant permissions only to children
- [ ] Reseller cannot grant permissions they don't have
- [ ] User cannot see Account Management menu
- [ ] User cannot access Account Management routes
- [ ] Direct URL access to /admin/view-user blocked for non-Admin
- [ ] Cascading revocation works (parent loses permission → children lose it)
- [ ] Audit trail captures all changes
- [ ] Permission cache invalidates on changes
- [ ] No N+1 queries in sidebar
- [ ] Deep hierarchies work correctly (Admin → Reseller → User → User)
- [ ] Permission inheritance on user creation
- [ ] API validates permissions
- [ ] User cannot self-elevate permissions

## Deployment Checklist

- [ ] Backup database
- [ ] Run all migrations
- [ ] Backfill parent_user_id and role_id
- [ ] Test permission checks work
- [ ] Deploy code changes
- [ ] Clear application cache
- [ ] Update documentation
- [ ] Train admins on new permission system
- [ ] Monitor for permission-related errors
- [ ] Verify sidebar displays correctly for all user types

---

**Document Version:** 1.0  
**Last Updated:** 2026-06-02  
**Status:** Ready for Implementation
