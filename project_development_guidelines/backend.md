# Backend Development Guidelines

## Architecture Overview

This application follows a strict service-oriented architecture where all business logic resides in Service classes, not in controllers or models.

### Directory Structure

**IMPORTANT:** This project uses a **Core folder structure** to separate updatable framework code from your project code.

**Core System (UPDATABLE from starter)**
- Location: `app/Core/`
- Purpose: Framework code that gets updated from starter template
- Contents:
  - `app/Core/Resources/` - Base Resource class, Fields, Filters, Actions
  - `app/Core/Services/ResourceService.php` - Core CRUD service
  - `app/Core/Http/Controllers/ResourceController.php` - Core controller
- **NEVER modify files in app/Core/** - they get overwritten during updates

**Your Services Layer**
- Location: `app/Services/`
- Purpose: Contains YOUR business logic
- Examples: `AuthService.php`, `EmailTemplateService.php`, `SettingsService.php`
- **Protected** - Never overwritten during updates

**Your Controllers Layer**
- Location: `app/Http/Controllers/Api/`
- Purpose: Handle HTTP concerns only (validation, responses)
- Examples: `AuthController.php`, `SettingsController.php`
- **Protected** - Never overwritten during updates

**Your Resources**
- Location: `app/Resources/`
- Purpose: Define YOUR CRUD interfaces for models
- Examples: `UserResource.php`, `RoleResource.php`, `CountryResource.php`
- Must extend: `App\Core\Resources\Resource`
- Must import from: `App\Core\Resources\Fields\*`, `App\Core\Resources\Filters\*`, etc.
- **Protected** - Never overwritten during updates

**Models Layer**
- Location: `app/Models/`
- Purpose: Define database relationships and scopes only
- Examples: `User.php`, `Setting.php`, `Role.php`
- **Protected** - Never overwritten during updates

## Core Architectural Rules

### Service Layer Pattern

**All business logic MUST be in Service classes.** Controllers should only:
- Accept validated input from Form Requests
- Call appropriate Service methods
- Return HTTP responses

Never put business logic in controllers or models.

Reference: See `app/Services/AuthService.php` for the pattern.

### Resource CRUD System

This is the CORE architecture pattern inspired by Laravel Nova. When building admin interfaces or CRUD features, always use the Resource system.

**How It Works:**
1. Create a Resource class in `app/Resources/` (extends `App\Core\Resources\Resource`)
2. Import fields, filters, actions from `App\Core\Resources\*`
3. Define fields, filters, and actions
4. Register in `config/resources.php`
5. Automatic API endpoints are generated

**Example:**
```php
namespace App\Resources;

use App\Core\Resources\Resource;
use App\Core\Resources\Fields\ID;
use App\Core\Resources\Fields\Text;
use App\Core\Resources\Fields\Email;
use App\Models\User;

class UserResource extends Resource
{
    public static string $model = User::class;
    // ...
}
```

**Automatic Endpoints:**
- GET `/api/resources/{resource}/meta` - Field definitions
- GET `/api/resources/{resource}` - List with search, filters, pagination
- POST `/api/resources/{resource}` - Create
- GET `/api/resources/{resource}/{id}` - Show
- PUT `/api/resources/{resource}/{id}` - Full update
- PATCH `/api/resources/{resource}/{id}` - Partial update
- DELETE `/api/resources/{resource}/{id}` - Delete
- POST `/api/resources/{resource}/bulk/delete` - Bulk delete
- POST `/api/resources/{resource}/bulk/update` - Bulk update

Reference: See `project_development_guidelines/resource-crud-system.md` for complete guide.

## Multi-Tenancy Architecture

This application uses multi-tenancy with separate databases for landlord and tenant contexts.

### Database Context Rules

**Landlord Database:**
- Contains: `users`, `workspaces`, `workspace_user` tables
- Use models: `User`, `Workspace`
- Purpose: Application-wide data

**Tenant Database:**
- Contains: `workspace_users_cache`, `tasks`, `projects` tables
- Use models: `WorkspaceUserCache`, `Task`, `Project`
- Purpose: Workspace-scoped data

**CRITICAL RULES:**
- NEVER use `User` model for tenant-scoped features
- Always use `WorkspaceUserCache` for tenant features
- Media uploads in tenant context must attach to tenant models, NOT User model
- Always verify which database context you're in before choosing models
- Store tenant migrations in `database/migrations/tenant/` folder

Reference: https://tenancyforlaravel.com/docs

## Database & Eloquent

### Model Relationships

Always use proper Eloquent relationship methods with explicit return type hints.

Prefer relationship methods over raw queries or manual joins.

Reference: See existing models in `app/Models/` for patterns.

### Query Best Practices

- Always use `Model::query()` instead of `DB::`
- Use eager loading to prevent N+1 query problems
- Use Laravel's query builder for complex operations
- Laravel 11+ allows limiting eagerly loaded records: `$query->latest()->limit(10)`

### Migrations

**Column Modifications:**
When modifying a column, include ALL previous attributes or they will be dropped and lost.

**Seed Data:**
For data required by the application, create a seeder and call it from the migration.

Reference: See migrations in `database/migrations/` for patterns.

**Multi-Tenancy:**
Always store tenant migrations in `database/migrations/tenant/` folder.

**Production Changes:**
No changes to existing migrations. Any database changes or seed data required for production must be added to new migrations.

## Creating New Features

### Step-by-Step Process

1. **Create Service Class**
   - Command: `php artisan make:class Services/YourService --no-interaction`
   - Location: `app/Services/`
   - Contains: All business logic

2. **Create Controller**
   - Command: `php artisan make:controller Api/YourController --api --no-interaction`
   - Location: `app/Http/Controllers/Api/`
   - Contains: HTTP handling only

3. **Create Form Request**
   - Command: `php artisan make:request YourRequest --no-interaction`
   - Location: `app/Http/Requests/`
   - Contains: Validation rules and error messages
   - Check sibling Form Requests to see if the application uses array or string based validation rules

4. **Create Resource (if CRUD-based)**
   - Command: `php artisan make:class Resources/YourResource --no-interaction`
   - Location: `app/Resources/`
   - Register in: `config/resources.php`
   - Highly recommended for admin interfaces

5. **Write PHPUnit Tests**
   - Command: `php artisan make:test --phpunit YourTest --no-interaction`
   - Location: `tests/Feature/`
   - Test all happy paths, failure paths, and edge cases

6. **Format Code**
   - Command: `vendor/bin/pint --dirty`
   - Must run before committing

### Adding New Models

Always create models with factories and seeders:

Command: `php artisan make:model YourModel -mfs --no-interaction`
- `-m` creates migration
- `-f` creates factory
- `-s` creates seeder

Then create a Resource class if CRUD functionality is needed.

Reference: See `database/factories/` and `database/seeders/` for patterns.

## API Development

### API Resources

Use Eloquent API Resources for all API responses unless existing routes follow a different convention.

Reference: Check existing controllers in `app/Http/Controllers/Api/` for patterns.

### Validation

Always create Form Request classes for validation. Never use inline validation in controllers.

Include both validation rules and custom error messages in Form Requests.

Reference: See `app/Http/Requests/` for patterns.

## Authentication & Authorization

### Authentication
- Uses Laravel Sanctum (token-based)
- Middleware: `auth:sanctum`

### Roles & Permissions
- Users have many-to-many relationship with roles via `role_user` pivot table
- Check admin: `$user->isAdmin()` method
- Use Laravel's built-in gates and policies

Reference: See `app/Models/User.php` and `app/Policies/` for patterns.

## Email Templates

### Email System Rules

Always use the `EmailTemplate` model system for all emails.

**When creating new emails:**
1. Use `app/Models/EmailTemplate.php` system
2. Create migration to seed `email_templates` table
3. Ensure templates are email-compatible and mobile-optimized
4. Migration ensures data reaches production

Reference: See `app/Models/EmailTemplate.php` for implementation.

## File & Media Operations

**Always use Spatie Media Library** for all file and image operations.

Never implement custom file upload/storage logic.

**Multi-Tenancy Context:**
Media uploads in tenant context must attach to tenant models (Task, Project, WorkspaceUserCache), NOT the User model.

Reference: Check existing models for `HasMedia` trait usage.

## Configuration

### Environment Variables

**CRITICAL RULE:** Never use `env()` directly in code.

Always use `config()` and define values in config files first.

The `env()` function only works in config files located in `config/` directory.

Reference: See files in `config/` directory for patterns.

## Queues & Background Jobs

Use queued jobs with the `ShouldQueue` interface for time-consuming operations.

Reference: See `app/Jobs/` for patterns.

## Testing

### Testing Strategy

Use PHPUnit (not Pest) for all backend tests.

**Test Types:**
- Feature tests (default): `php artisan make:test --phpunit YourTest --no-interaction`
- Unit tests: `php artisan make:test --phpunit --unit YourTest --no-interaction`

Most tests should be feature tests.

### Test Data

- Use model factories for test data
- Check if factories have custom states before manually setting up models
- Follow existing conventions for Faker usage (`$this->faker` vs `fake()`)

Reference: See `tests/Feature/` and `tests/Unit/` for patterns.

### Running Tests

- All tests: `php artisan test`
- Single file: `php artisan test tests/Feature/YourTest.php`
- Single test: `php artisan test --filter=testMethodName`
- Run tests after every change to verify functionality

### Testing Environment

Always use MySQL connection (as in `.env.testing`) for testing to replicate real production scenarios.

Reference: See `.env.testing` for configuration.

### Test Coverage Requirements

Every change must be programmatically tested. Write new tests or update existing tests, then run affected tests to ensure they pass.

Never remove tests without approval. Tests are core to the application, not temporary files.

## PHP Coding Standards

### Type Safety

- Always use explicit return type declarations for methods and functions
- Use appropriate PHP type hints for method parameters
- Use PHPDoc blocks with array shape type definitions when appropriate

### Constructors

- Use PHP 8 constructor property promotion
- Do not allow empty constructors with zero parameters

### Control Structures

Always use curly braces for control structures, even for single-line statements.

### Enums

Enum keys should be TitleCase format.

Reference: See existing enums in `app/Enums/` for patterns.

## Laravel Specific Standards

### Commands

- Always pass `--no-interaction` to Artisan commands
- Use `php artisan make:` commands for creating files
- Use `php artisan make:class` for generic PHP classes

### URL Generation

Always use named routes with the `route()` function. Never hardcode URLs.

Reference: See `routes/api.php` for route names.

### Laravel 12 Structure

This application uses Laravel 12's streamlined structure:
- No `app/Console/Kernel.php` or `app/Http/Kernel.php`
- Middleware registered in `bootstrap/app.php`
- Service providers in `bootstrap/providers.php`
- Commands auto-register from `app/Console/Commands/`

## Code Formatting

**MANDATORY:** Run `vendor/bin/pint --dirty` before finalizing any changes.

This ensures code matches the project's style conventions.

Never run `vendor/bin/pint --test`. Always run formatting to fix issues, not just check them.

## Settings System

### User Settings
- Per-user preferences stored in `settings` table with `user_id`
- Categories: appearance, localization, notifications
- Service: `SettingsService.php`

### Global Settings
- Application-wide config (admin only)
- Stored without `user_id`
- Categories: application, security, email, localization, appearance
- Service: `SettingsService.php`

### Settings Lists
- Predefined options (countries, timezones, themes)
- Stored in `setting_lists` table

Reference: See `project_development_guidelines/settings.md` and `app/Services/SettingsService.php` for implementation details.

## Important Documentation References

- Resource CRUD System: `project_development_guidelines/resource-crud-system.md`
- Settings System: `project_development_guidelines/settings.md`
- Email Templates: `project_development_guidelines/features/email-templates.md`
- Multi-Tenancy: `project_development_guidelines/features/multi-tenancy.md`
- Module System: `project_development_guidelines/features/module-system.md`
- Multi-Tenancy (Stancl): https://tenancyforlaravel.com/docs
- Testing: `TESTING.md`
