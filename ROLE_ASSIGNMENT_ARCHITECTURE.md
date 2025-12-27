# Role Assignment Architecture for Admins

This document explains the architecture of role assignment to admins in the Kippis project.

## Overview

The project uses **Spatie Laravel Permission** package for role-based access control (RBAC). Admins can have multiple roles, and roles can have multiple permissions.

## Architecture Components

### 1. Core Model: Admin

**Location:** `app/Core/Models/Admin.php`

The Admin model uses the `HasRoles` trait from Spatie Permission:

```php
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    // ...
}
```

**Key Methods Available:**
- `assignRole($role)` - Assign one or more roles
- `syncRoles($roles)` - Sync roles (removes all existing, assigns new)
- `removeRole($role)` - Remove a role
- `hasRole($role)` - Check if admin has a role
- `hasAnyRole($roles)` - Check if admin has any of the roles
- `hasAllRoles($roles)` - Check if admin has all roles
- `getRoleNames()` - Get all role names
- `roles` - Relationship to roles

### 2. Database Structure

**Tables:**
- `admins` - Admin users table
- `roles` - Roles table (Spatie Permission)
- `permissions` - Permissions table (Spatie Permission)
- `model_has_roles` - Pivot table linking admins to roles
- `role_has_permissions` - Pivot table linking roles to permissions

**Guard:** `admin` (custom guard for admin authentication)

### 3. Role Assignment Methods

#### Method 1: Direct Assignment (Seeder/Code)

```php
// Single role
$admin->assignRole('admin');

// Multiple roles
$admin->assignRole('admin', 'support');

// Using role model
$admin->assignRole(Role::findByName('admin', 'admin'));
```

#### Method 2: Sync Roles (Replace All)

```php
// Remove all existing roles and assign new ones
$admin->syncRoles(['admin', 'support']);
```

#### Method 3: Remove Role

```php
$admin->removeRole('support');
```

### 4. Current Implementation

#### Seeder Implementation

**Location:** `database/seeders/AdminSeeder.php`

```php
// Super Admin with multiple roles
$superAdmin->assignRole('super_admin', 'admin');

// Regular admin
$admin1->assignRole('admin');

// Support user
$admin2->assignRole('support');
```

#### Role & Permission Seeder

**Location:** `database/seeders/RolePermissionSeeder.php`

Creates:
- **Roles:** `super_admin`, `admin`, `support`, `auditor`
- **Permissions:** Various manage_* and view_* permissions
- **Role-Permission Mapping:** Each role gets specific permissions

### 5. Available Roles

1. **super_admin**
   - All permissions
   - Full system access

2. **admin**
   - Most permissions except:
     - `manage_admins`
     - `manage_roles`
   - Can manage: pages, support, channels, payment methods, customers, stores

3. **support**
   - Limited permissions:
     - `manage_support`
     - `view_logs`

4. **auditor**
   - Read-only permissions:
     - `view_logs`
     - `view_activities`

### 6. Permission Checking

#### In Controllers

```php
// Check if admin has role
if (auth()->guard('admin')->user()->hasRole('admin')) {
    // Allow access
}

// Check if admin has permission
if (auth()->guard('admin')->user()->can('manage_stores')) {
    // Allow access
}
```

#### In Filament Resources

```php
// In Resource class
public static function canViewAny(): bool
{
    return Gate::forUser(auth()->guard('admin')->user())
        ->allows('manage_stores');
}
```

#### Using Gates

```php
Gate::forUser($admin)->allows('manage_stores');
Gate::forUser($admin)->denies('manage_admins');
```

### 7. Filament Integration

Currently, the `AdminResource` form does **NOT** include role assignment. To add it:

```php
// In AdminResource::form()
Forms\Components\CheckboxList::make('roles')
    ->label('Roles')
    ->relationship('roles', 'name')
    ->columns(2)
    ->required(),
```

Then handle in the page class:

```php
// In CreateAdmin or EditAdmin page
protected function mutateFormDataBeforeCreate(array $data): array
{
    $roles = $data['roles'] ?? [];
    unset($data['roles']);
    return $data;
}

protected function afterCreate(): void
{
    $roles = $this->form->getState()['roles'] ?? [];
    $this->record->syncRoles($roles);
}
```

### 8. Best Practices

#### ✅ DO:

1. **Use role names consistently:**
   ```php
   $admin->assignRole('admin'); // ✅ Good
   $admin->assignRole('Admin'); // ❌ Bad (case-sensitive)
   ```

2. **Check permissions, not roles (when possible):**
   ```php
   $admin->can('manage_stores'); // ✅ Better
   $admin->hasRole('admin'); // ⚠️ Less flexible
   ```

3. **Use guard name explicitly:**
   ```php
   Role::findByName('admin', 'admin'); // ✅ Explicit guard
   ```

4. **Clear cache after role changes:**
   ```php
   app()['cache']->forget('spatie.permission.cache');
   ```

#### ❌ DON'T:

1. **Don't hardcode role checks everywhere:**
   ```php
   // ❌ Bad
   if ($admin->hasRole('admin')) { ... }
   
   // ✅ Good
   if ($admin->can('manage_stores')) { ... }
   ```

2. **Don't assign roles without checking existence:**
   ```php
   // ❌ Bad
   $admin->assignRole('non_existent_role');
   
   // ✅ Good
   if (Role::where('name', 'admin')->exists()) {
       $admin->assignRole('admin');
   }
   ```

### 9. Migration Flow

1. **Create Roles & Permissions:**
   ```bash
   php artisan db:seed --class=RolePermissionSeeder
   ```

2. **Create Admins:**
   ```bash
   php artisan db:seed --class=AdminSeeder
   ```

3. **Clear Permission Cache:**
   ```php
   app()['cache']->forget('spatie.permission.cache');
   ```

### 10. Example: Complete Role Assignment Flow

```php
use App\Core\Models\Admin;
use Spatie\Permission\Models\Role;

// 1. Create or get admin
$admin = Admin::firstOrCreate(
    ['email' => 'user@example.com'],
    ['name' => 'User Name', 'password' => Hash::make('password')]
);

// 2. Get or create role
$role = Role::firstOrCreate(
    ['name' => 'admin', 'guard_name' => 'admin']
);

// 3. Assign role
$admin->assignRole($role);

// 4. Verify
if ($admin->hasRole('admin')) {
    echo "Role assigned successfully!";
}

// 5. Check permissions
if ($admin->can('manage_stores')) {
    echo "Admin can manage stores!";
}
```

### 11. Troubleshooting

#### Issue: Role not working after assignment

**Solution:**
```php
// Clear permission cache
app()['cache']->forget('spatie.permission.cache');

// Or in tinker
php artisan tinker
>>> app()['cache']->forget('spatie.permission.cache');
```

#### Issue: Permission check returns false

**Check:**
1. Role exists: `Role::where('name', 'admin')->exists()`
2. Permission exists: `Permission::where('name', 'manage_stores')->exists()`
3. Role has permission: `$role->hasPermissionTo('manage_stores')`
4. Admin has role: `$admin->hasRole('admin')`
5. Guard is correct: `'guard_name' => 'admin'`

### 12. Security Considerations

1. **Never assign `super_admin` role via user input** - Only via seeders or trusted code
2. **Validate role existence** before assignment
3. **Use permissions for fine-grained control** instead of role checks
4. **Audit role changes** - Log when roles are assigned/removed
5. **Protect role assignment endpoints** - Only super_admins should assign roles

## Summary

The role assignment architecture follows this flow:

```
Admin Model (HasRoles trait)
    ↓
assignRole() / syncRoles()
    ↓
model_has_roles table (pivot)
    ↓
Role Model
    ↓
role_has_permissions table (pivot)
    ↓
Permission Model
    ↓
Gate/Permission Checks
```

This architecture provides:
- ✅ Flexible role management
- ✅ Multiple roles per admin
- ✅ Permission-based access control
- ✅ Easy integration with Filament
- ✅ Cached for performance

