# Module Migration System

This document explains how tenant database migrations work for workspaces and modules.

## Migration Structure

```
database/migrations/tenant/
├── 2025_01_01_000001_create_workspace_users_cache_table.php    # Core tenant migrations
├── 2025_01_01_000002_create_workspace_roles_table.php          # Run when workspace created
├── 2025_01_01_000003_create_workspace_permissions_table.php
├── 2025_01_01_000004_create_workspace_role_permissions_table.php
├── 2025_01_01_000005_create_workspace_user_roles_table.php
├── 2025_01_01_000006_create_media_table.php
├── 2025_10_05_000007_add_performance_indexes_to_tenant_tables.php
├── expenses/                                                   # Module-specific migrations
│   ├── 2025_10_05_030434_create_expense_categories_table.php  # Run when module installed
│   └── 2025_10_05_030440_create_expenses_table.php
└── tasks/                                                      # Module-specific migrations
    └── [task module migrations]                                # Run when module installed
```

## How It Works

### 1. Workspace Creation

When a workspace is created via `WorkspaceService::createWorkspace()`:

```php
$workspaceService->createWorkspace($user, [
    'name' => 'My Workspace',
    'description' => 'Test workspace',
]);
```

**What happens:**
- Creates tenant database (e.g., `tenant_abc123...`)
- Runs all migrations **directly in** `database/migrations/tenant/`
- Creates core tenant tables: `workspace_roles`, `workspace_permissions`, `media`, etc.
- **Does NOT run** module migrations in subdirectories

**Result:** Workspace has core infrastructure ready, but no module-specific tables.

### 2. Module Installation

When a module is installed via `ModuleService::installModule()`:

```php
$moduleService->installModule($workspace, $expensesModule, $user);
```

**What happens:**
- Switches to the workspace's tenant database
- Runs migrations from `database/migrations/tenant/expenses/` (configured in `config/modules.php`)
- Creates module tables: `expense_categories`, `expenses`
- Seeds module permissions into `workspace_permissions` table
- Creates module roles (e.g., `expenses_manager`)

**Result:** Module tables are created in the workspace's tenant database.

### 3. Module Uninstallation

When a module is uninstalled:

```php
$moduleService->uninstallModule($workspace, $expensesModule);
```

**What happens:**
- Rolls back migrations from `database/migrations/tenant/expenses/`
- Drops module tables: `expense_categories`, `expenses`
- Removes module permissions from `workspace_permissions`
- Detaches module from workspace

## Adding a New Module

### 1. Create Module Directory

```bash
mkdir -p database/migrations/tenant/my_module
```

### 2. Create Module Migrations

```bash
php artisan make:migration create_my_module_items_table --path=database/migrations/tenant/my_module --no-interaction
```

### 3. Register in `config/modules.php`

```php
'my_module' => [
    'class' => \App\Modules\MyModule\MyModuleModule::class,
    'migrations_path' => 'database/migrations/tenant/my_module',
    'enabled' => true,
],
```

### 4. Create Module Class

```php
<?php

namespace App\Modules\MyModule;

use App\Modules\BaseModule;

class MyModuleModule extends BaseModule
{
    public function key(): string
    {
        return 'my_module';
    }

    public function name(): string
    {
        return 'My Module';
    }

    public function description(): string
    {
        return 'Description of my module';
    }

    public function features(): array
    {
        return [
            'Feature 1',
            'Feature 2',
        ];
    }

    public function permissions(): array
    {
        return [
            'my_module.view',
            'my_module.create',
            'my_module.edit',
            'my_module.delete',
        ];
    }
}
```

## Important Notes

### Core Migrations
- Place migrations for workspace infrastructure (roles, permissions, core features) **directly** in `database/migrations/tenant/`
- These run automatically when a workspace is created
- Examples: ACL tables, media tables, shared utilities

### Module Migrations
- Place module-specific migrations in **subdirectories** like `database/migrations/tenant/expenses/`
- These run only when the module is installed for a workspace
- Examples: module tables, module-specific data

### Database Isolation
- Each workspace has its own tenant database
- Module tables are isolated per workspace
- Workspace A's expenses are completely separate from Workspace B's expenses

### Testing
The system includes comprehensive tests:
- ✅ Module migrations run in tenant database on installation
- ✅ Module migrations rollback in tenant database on uninstallation
- ✅ Module permissions are seeded in tenant database

## Migration Paths

| Location | Runs When | Example Tables |
|----------|-----------|----------------|
| `database/migrations/tenant/*.php` | Workspace created | `workspace_roles`, `workspace_permissions`, `media` |
| `database/migrations/tenant/expenses/*.php` | Expenses module installed | `expense_categories`, `expenses` |
| `database/migrations/tenant/tasks/*.php` | Tasks module installed | `tasks`, `task_categories` |

## Configuration

Module migration paths are configured in `config/modules.php`:

```php
return [
    'expenses' => [
        'class' => \App\Modules\Expenses\ExpensesModule::class,
        'migrations_path' => 'database/migrations/tenant/expenses',
        'enabled' => true,
    ],
];
```

The `migrations_path` is relative to the project root and should point to the subdirectory containing module-specific migrations.
