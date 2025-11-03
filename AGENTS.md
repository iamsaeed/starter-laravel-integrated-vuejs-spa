# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Laravel 12 + Vue 3 SPA starter application** with a custom Resource CRUD system, comprehensive authentication, user/global settings management, and a component-driven frontend architecture.

**Key Technologies:**
- Backend: Laravel 12, PHP 8.2, Laravel Sanctum, PHPUnit
- Frontend: Vue 3, Vue Router, Pinia, Vite, Tailwind CSS 4, VeeValidate, Vitest
- Testing: PHPUnit (backend), Vitest (frontend unit/integration), Playwright (E2E, accessibility, performance)

## Development Commands

### Running the Application

```bash
# Start all services (server, queue, logs, vite) - RECOMMENDED
composer run dev

# Or manually:
php artisan serve              # Backend server
php artisan queue:listen       # Queue worker
php artisan pail              # Real-time logs
npm run dev                   # Frontend dev server with HMR
npm run build                 # Production build
```

**Note:** If frontend changes don't appear in the browser, run `npm run build` or restart `composer run dev`/`npm run dev`.

### Testing

```bash
# Backend tests
php artisan test                          # All PHPUnit tests
php artisan test --filter=testName        # Single test
php artisan test tests/Feature/AuthTest.php  # Single file

# Frontend unit/integration tests
npm run test                   # Run all Vitest tests
npm run test:ui               # Interactive UI mode
npm run test:coverage         # With coverage report
npm run test -- settings      # Run tests matching pattern

# E2E tests (requires: npx playwright install --with-deps)
npm run test:e2e              # All E2E tests
npm run test:e2e:ui           # Interactive mode
npm run test:e2e:headed       # See browser
npm run test:e2e:debug        # Debug mode
```

### Code Quality

```bash
vendor/bin/pint --dirty       # Format PHP code (run before committing)
vendor/bin/pint              # Format all PHP code (not just dirty files)
```

**IMPORTANT:** Always run `vendor/bin/pint --dirty` before finalizing changes to ensure code matches project style.

## Application Architecture

### Backend Architecture

#### Resource CRUD System

The application uses a **custom Resource system** (inspired by Laravel Nova) for building admin interfaces. This is the CORE architecture pattern.

**Directory Structure:**
```
app/
├── Resources/
│   ├── Resource.php              # Base Resource class
│   ├── UserResource.php          # Example Resource
│   ├── Fields/                   # Field definitions
│   │   ├── Field.php             # Base Field
│   │   ├── Text.php, Email.php, Select.php, etc.
│   │   ├── BelongsTo.php         # Relationship fields
│   │   └── BelongsToMany.php
│   ├── Filters/                  # Filter definitions
│   │   ├── Filter.php            # Base Filter
│   │   ├── SelectFilter.php
│   │   └── BooleanFilter.php
│   └── Actions/                  # Bulk actions
│       └── Action.php
├── Services/
│   ├── ResourceService.php       # Handles Resource CRUD logic
│   ├── AuthService.php           # Authentication logic
│   └── SettingsService.php       # Settings management
├── Http/
│   ├── Controllers/Api/
│   │   ├── ResourceController.php    # Generic Resource controller
│   │   ├── AuthController.php
│   │   └── SettingsController.php
│   └── Requests/                 # Form Request validation
└── Models/
    ├── User.php
    ├── Setting.php
    └── SettingList.php
```

**How to Create a New Resource:**

1. Create the Resource class in `app/Resources/`:
```bash
php artisan make:class Resources/ProductResource --no-interaction
```

**Note:** Always pass `--no-interaction` to Artisan commands to ensure they work without user input.

2. Define the Resource (see `docs/resource-crud-system.md` for full guide):
```php
<?php
namespace App\Resources;

use App\Models\Product;
use App\Resources\Fields\{ID, Text, Number, Select};

class ProductResource extends Resource
{
    public static string $model = Product::class;
    public static string $label = 'Products';
    public static string $singularLabel = 'Product';
    public static string $title = 'name';
    public static array $search = ['name', 'sku'];

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required|max:255')->sortable(),
            Number::make('Price')->rules('required|numeric|min:0'),
            Select::make('Status')->options(['active', 'inactive']),
        ];
    }

    public function filters(): array { return []; }
    public function actions(): array { return []; }
    public function with(): array { return []; }
}
```

3. Register in `config/resources.php`:
```php
'products' => \App\Resources\ProductResource::class,
```

**Automatic Endpoints Generated:**
- `GET /api/resources/products/meta` - Field/filter definitions
- `GET /api/resources/products` - List with search, filters, pagination
- `POST /api/resources/products` - Create
- `GET /api/resources/products/{id}` - Show
- `PUT /api/resources/products/{id}` - Full update
- `PATCH /api/resources/products/{id}` - Partial update (for toggles)
- `DELETE /api/resources/products/{id}` - Delete
- `POST /api/resources/products/bulk/delete` - Bulk delete
- `POST /api/resources/products/bulk/update` - Bulk update

#### Service Layer Pattern

**All business logic MUST be in Service classes**, not controllers.

- Controllers handle HTTP concerns (validation, responses)
- Services contain business logic and data manipulation
- Models define relationships and scopes

**Example:**
```php
// ✅ Good: Logic in Service
class AuthService {
    public function login(array $credentials): array {
        // Business logic here
    }
}

class AuthController {
    public function login(LoginRequest $request, AuthService $authService) {
        return response()->json($authService->login($request->validated()));
    }
}

// ❌ Bad: Logic in Controller
class AuthController {
    public function login(LoginRequest $request) {
        // Don't put business logic here!
    }
}
```

### Frontend Architecture

#### Directory Structure

```
resources/js/
├── app.js                    # Main entry (for guest pages)
├── spa.js                    # SPA entry (for admin panel)
├── router/
│   └── index.js              # Vue Router with route guards
├── stores/                   # Pinia stores
│   ├── auth.js
│   ├── settings.js
│   ├── toast.js
│   └── dialog.js
├── services/                 # API services (ALL API calls here)
│   ├── authService.js
│   ├── settingsService.js
│   └── resourceService.js
├── components/
│   ├── common/               # Reusable components
│   │   ├── Icon.vue
│   │   ├── DarkModeToggle.vue
│   │   ├── Toast.vue
│   │   ├── ConfirmDialog.vue
│   │   └── ToggleSwitch.vue
│   ├── form/                 # Form components
│   │   ├── FormInput.vue
│   │   ├── FormError.vue
│   │   ├── SelectInput.vue
│   │   └── CheckboxInput.vue
│   ├── resource/             # Resource CRUD components
│   │   ├── ResourceManager.vue    # Main container
│   │   ├── ResourceTable.vue      # Data table
│   │   ├── ResourceForm.vue       # Create/edit form
│   │   ├── FilterBar.vue          # Filters
│   │   └── ActionButtons.vue
│   ├── settings/             # Settings components
│   │   ├── SettingsForm.vue
│   │   ├── SettingGroup.vue
│   │   ├── CountrySelect.vue
│   │   └── TimezoneSelect.vue
│   └── layout/               # Layout components
│       ├── Sidebar.vue
│       ├── Navbar.vue
│       └── UserDropdown.vue
├── layouts/
│   ├── AdminLayout.vue       # Admin panel layout
│   ├── ProfileLayout.vue     # Profile pages layout
│   └── SettingsLayout.vue    # Settings pages layout
├── pages/
│   ├── auth/                 # Authentication pages
│   ├── admin/                # Admin pages
│   ├── profile/              # Profile pages
│   ├── settings/             # Settings pages
│   └── errors/               # Error pages
├── composables/              # Vue composables
│   ├── useDialog.js
│   ├── useToast.js
│   └── useAuth.js
├── directives/               # Vue directives
│   └── tooltip.js
└── utils/
    └── api.js                # Axios instance with interceptors
```

#### Frontend Patterns

**1. Service Layer for API Calls**

All API calls MUST be in service files (`resources/js/services/`), never in components or stores.

```javascript
// ✅ Good: API logic in service
// resources/js/services/authService.js
export const authService = {
  async login(credentials) {
    const response = await api.post('/api/login', credentials)
    return response.data
  }
}

// Component uses service
import { authService } from '@/services/authService'
const data = await authService.login(credentials)

// ❌ Bad: API call in component
const response = await api.post('/api/login', credentials) // Don't do this!
```

**2. Pinia Stores for State Management**

- Stores manage global state and coordinate between services and components
- Use `storeToRefs()` for reactive destructuring
- Actions call service methods

**3. Reusable Components**

Before creating a new component, check if one exists:
- Form inputs: `components/form/`
- Common UI: `components/common/`
- Resource CRUD: `components/resource/`

**4. Route Names (CRITICAL)**

Always use route names, NEVER hardcoded paths:

```javascript
// ✅ Good
router.push({ name: 'admin.users' })
<RouterLink :to="{ name: 'admin.dashboard' }">Dashboard</RouterLink>

// ❌ Bad
router.push('/admin/users')
<RouterLink to="/admin/dashboard">Dashboard</RouterLink>
```

**5. Tailwind CSS Classes**

Use Tailwind utility classes. For repeated patterns, create CSS classes using Tailwind directives:

```css
/* resources/css/app.css */
.btn-primary {
  @apply px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700;
}
```

Use theme colors (defined in `tailwind.config.js`):
- `primary-*` (blue shades)
- `danger-*` (red shades)
- `success-*` (green shades)

#### Key Frontend Features

**Resource Manager (`components/resource/ResourceManager.vue`):**
- Generic CRUD interface for any Resource
- Handles table, filters, search, pagination, bulk actions
- Usage: `<ResourceManager resource-key="users" />`

**Settings System:**
- User settings: Per-user preferences (theme, timezone, notifications)
- Global settings: Application-wide config (admin only)
- Stores: `useSettingsStore()`, services: `settingsService.js`

**Dialog System (`composables/useDialog.js`):**
- Confirmation dialogs with themes (danger, success, info)
- Promise-based API: `await confirmDanger('Delete this?')`
- Supports HTML content, animations, loading states

**Toast Notifications (`composables/useToast.js`):**
- `showToast({ message, type, duration })`
- Types: success, error, info, warning

**Tooltip Directive:**
- Usage: `<button v-tooltip="'Tooltip text'">Click</button>`

## Settings System

### User Settings
User-specific preferences stored per-user in `settings` table with `user_id`.

**Categories:**
- `appearance`: theme, items_per_page
- `localization`: country, timezone, date_format, time_format
- `notifications`: email, push, desktop toggles

**API Endpoints:**
- `GET /api/user/settings` - Get all user settings
- `PUT /api/user/settings` - Bulk update settings
- `PUT /api/user/settings/{key}` - Update single setting

### Global Settings
Application-wide configuration (admin only) stored without `user_id`.

**Categories:**
- `application`: app_name, app_url, default_theme, default_timezone
- `security`: email_verification, two_factor, session_lifetime
- `email`: mail_driver, mail_host, mail_port, mail_from
- `localization`: default_country, default_language
- `appearance`: available_themes, default_items_per_page

**API Endpoints:**
- `GET /api/settings` - Get all global settings
- `POST /api/settings` - Create setting
- `PUT /api/settings/{key}` - Update setting
- `DELETE /api/settings/{key}` - Delete setting

### Settings Lists
Predefined options for select fields (countries, timezones, themes) stored in `setting_lists` table.

**API Endpoints:**
- `GET /api/settings/lists/{key}` - Get list items (e.g., countries, timezones)

## Testing Strategy

**Test Coverage: 464+ tests**

- **Unit Tests (271):** Component logic, stores, services, composables
- **Integration Tests (89):** Feature workflows, store+service integration
- **E2E Tests (55):** Critical user journeys (Playwright)
- **Accessibility Tests (35):** WCAG 2.1 AA compliance (Playwright + Axe)
- **Performance Tests (10):** Core Web Vitals (Playwright)

**Testing Guidelines:**

1. **Backend:** Write comprehensive PHPUnit tests for all API endpoints
2. **Frontend:** Write Vitest tests for business logic, Playwright for E2E
3. **Run tests after changes:** `php artisan test --filter=RelatedTest`
4. **Use factories/seeders:** For test data generation
5. **Check existing tests:** Before writing new ones

See `TESTING.md` for detailed testing documentation.

## Authentication & Authorization

**Authentication:** Laravel Sanctum (token-based)

**User Roles:**
- Users have many-to-many relationship with roles via `role_user` pivot
- Check admin: `$user->isAdmin()` (backend) or `authStore.user.is_admin` (frontend)
- Route guards: `requiresAdmin: true` in route meta

**Protected Routes:**
- Backend: `auth:sanctum` middleware
- Frontend: `requiresAuth: true` in route meta, checked by router guards

## Configuration & Environment

**Never use `env()` directly in code.** Always use `config()` and define values in config files.

```php
// ✅ Good
config('app.name')

// ❌ Bad
env('APP_NAME')
```

## Database Migrations

**When modifying columns:** Include ALL previous attributes, or they'll be dropped.

**For seed data required by the application:** Create a seeder and call it from the migration:

```php
public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        // ...
    });

    // Seed required data
    $this->call(DefaultSettingsSeeder::class);
}
```

## Important Documentation

- **Resource CRUD System:** `docs/resource-crud-system.md` - Complete guide to the Resource system
- **Image Field:** `docs_dev/image-field.md` - Image field for displaying SVG, URL, and base64 images
- **Testing:** `TESTING.md` - Comprehensive testing documentation
- **Settings:** `docs_dev/settings.md` - Settings system documentation
- **Dialog System:** `docs_dev/confirmation-dialogue.md`
- **Toast System:** `docs_dev/toast.md`
- **Tooltips:** `docs_dev/tooltip.md`
- **Frontend Testing:** `docs_dev/frontend-testing.md`

## Common Patterns

### Creating a New Feature

1. **Backend:**
   - Create Service class for business logic
   - Create Controller for HTTP handling
   - Create Form Request for validation
   - Create Resource if CRUD-based (highly recommended)
   - Write PHPUnit tests
   - Run `vendor/bin/pint --dirty`

2. **Frontend:**
   - Create service file for API calls
   - Create/update Pinia store if needed
   - Create page component
   - Add route to `resources/js/router/index.js`
   - Write Vitest tests
   - Add E2E test if critical user journey

### Adding a New Model

```bash
php artisan make:model Product -mfs --no-interaction
# -m: migration
# -f: factory
# -s: seeder
```

Then create Resource class and register in `config/resources.php`.

**Best Practice:** Always create factories and seeders with models for testing purposes.

### Creating New Settings

1. Add to appropriate seeder (`database/seeders/`)
2. Call seeder from migration
3. Update frontend setting categories if needed
4. Update `SettingsService.php` if custom logic required

## PHP & Laravel Coding Standards

### PHP Standards
- Always use curly braces for control structures, even one-liners
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) {}`
- Always use explicit return type declarations for methods and functions
- Use appropriate PHP type hints for method parameters
- Add PHPDoc blocks with array shape type definitions when appropriate
- Enum keys should be TitleCase (e.g., `FavoritePerson`, `Monthly`)

### Laravel Standards
- Use `php artisan make:` commands for creating new files
- Use `php artisan make:class` for generic PHP classes
- Always pass `--no-interaction` to Artisan commands
- Prefer Eloquent relationships over raw queries or manual joins
- Always use `Model::query()` instead of `DB::`
- Use eager loading to prevent N+1 query problems
- Use Form Request classes for validation (never inline validation)
- Use queued jobs for time-consuming operations with `ShouldQueue`
- Use Laravel's built-in authentication/authorization (gates, policies, Sanctum)
- Use named routes with `route()` function for URL generation
- Use `config()` not `env()` outside of config files

### Model & Database
- Use `casts()` method on models rather than `$casts` property
- Always type-hint Eloquent relationship methods
- When modifying columns in migrations, include ALL previous attributes or they'll be dropped
- Create useful factories and seeders when creating models

### Testing
- Use PHPUnit (not Pest) - run `php artisan make:test --phpunit <name>`
- Create feature tests by default, use `--unit` only for unit tests
- Use model factories for test data
- Test all happy paths, failure paths, and edge cases
- Run specific tests after changes: `php artisan test --filter=testName`

## Notes

- This application uses **Laravel 12's streamlined structure**:
  - No `app/Console/Kernel.php` or `app/Http/Kernel.php`
  - Middleware registered in `bootstrap/app.php`
  - Service providers in `bootstrap/providers.php`
  - Commands auto-register from `app/Console/Commands/`
- Vue Router uses **history mode** (requires server configuration)
- Dark mode is handled via Tailwind's `dark:` classes and stored in `localStorage`
- All Resource endpoints return consistent JSON structure with `data` wrapper
- Validation errors follow Laravel's standard format with `422` status

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.28
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- vue (VUE) - v3
- tailwindcss (TAILWINDCSS) - v4


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
- for new email temapltes make sure to use the @app/Models/EmailTemplate.php system, and when a new email is required to be created then create it and add a migration to seed the email_temapltes table so that may be send to production as well
- for all files and images operation always use the spatie media library
- whatever email templates you create they must be email compatable and mobile optimized
- the business logic of the backend and frontend will be always stored in their respective services and no where else so that we always have a single source of truth for business logic
- alaways store tenants migration in the @database/migrations/tenant/ folder
- make sure you are using 'workspace_users_cache' table for all @database/migrations/tenant/ realted code in the backend and not the 'users' table which is for the landlord tables
- awlays use mysql connection as in the @.env.testing for testing to replicate the real scnearios for everyhting
- always refere to the multi tenancy documentation for best practices https://tenancyforlaravel.com/docs
- if you develop anything with the "Neuron AI" package like Ai-Agents, Ai-Workflows, Ai-Tools, Ai-Nodes or test realted to this package you should check the documentation on https://docs.neuron-ai.dev/