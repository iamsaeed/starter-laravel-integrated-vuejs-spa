# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 + Vue 3 SPA application with custom Resource CRUD system, comprehensive authentication, and service-oriented architecture.

**Tech Stack:**
- Backend: Laravel 12, PHP 8.2, Laravel Sanctum, Spatie Media Library, Laravel Boost
- Frontend: Vue 3, Pinia, Vue Router, Vite, Tailwind CSS 4, VeeValidate, TipTap Editor
- Testing: PHPUnit (backend), Vitest (unit/integration), Playwright (E2E)

## Commands

### Development
```bash
composer run dev        # Start all services (server, queue, logs, vite) - RECOMMENDED
npm run dev             # Frontend dev server only (HMR)
npm run build           # Production build

# Individual services
php artisan serve       # Backend server
php artisan queue:listen --tries=1
php artisan pail        # Real-time logs
```

### Testing
```bash
# Backend (PHPUnit)
composer run test              # All backend tests with config clear
php artisan test               # All backend tests
php artisan test tests/Feature/ExampleTest.php  # Single file
php artisan test --filter=testMethodName        # Single test

# Frontend (Vitest)
npm run test            # Unit/integration tests
npm run test:ui         # Interactive UI
npm run test:coverage   # With coverage

# E2E (Playwright)
npm run test:e2e        # All E2E tests
npm run test:e2e:ui     # Interactive mode
npm run test:e2e:debug  # Debug mode
```

### Code Quality
```bash
vendor/bin/pint --dirty    # Format PHP (REQUIRED before commit)
```

### Database
```bash
php artisan app:reset      # Reset application (drop tables, migrate, seed)
php artisan migrate:fresh --seed  # Fresh migration with seeders
php artisan db:seed --class=ClassName  # Run specific seeder
```

## Architecture

### Service-Oriented Architecture (CRITICAL)

**All business logic MUST be in Service classes** - never in controllers or models.

```
Backend Flow:
Controller (HTTP) → Form Request (Validation) → Service (Business Logic) → Model (Data)

Frontend Flow:
Component → Service (API calls) → Store (State) → Component
```

**Key Locations:**
- Backend Services: `app/Services/` & `app/Core/Services/` (AuthService, EmailTemplateService, ResourceService, etc.)
- Frontend Services: `resources/js/services/` & `resources/js/core/services/` (authService.js, settingsService.js, resourceService.js)
- Controllers: `app/Http/Controllers/Api/` & `app/Core/Http/Controllers/` (HTTP only, no logic)
- Models: `app/Models/` (relationships and scopes only)

### Resource CRUD System (Laravel Nova-inspired)

Custom CRUD framework with automatic API endpoints, forms, and tables.

**Auto-generated endpoints for each Resource:**
- `GET /api/resources/{resource}/meta` - Field definitions
- `GET /api/resources/{resource}` - List (with search, filters, pagination)
- `POST /api/resources/{resource}` - Create
- `GET/PUT/PATCH/DELETE /api/resources/{resource}/{id}` - CRUD operations
- `POST /api/resources/{resource}/bulk/*` - Bulk operations

**Core System (UPDATABLE from starter):**
- Base Resource: `app/Core/Resources/Resource.php`
- Fields: `app/Core/Resources/Fields/*` (Text, Select, Number, Boolean, BelongsToMany, etc.)
- Filters: `app/Core/Resources/Filters/*` (SelectFilter, BooleanFilter, etc.)
- Actions: `app/Core/Resources/Actions/*` (BulkDeleteAction, BulkUpdateAction, etc.)
- Service: `app/Core/Services/ResourceService.php`
- Controller: `app/Core/Http/Controllers/ResourceController.php`
- Frontend: `resources/js/core/components/resource/*` (ResourceManager, ResourceTable, ResourceForm)
- API Service: `resources/js/core/services/resourceService.js`

**Project Resources (YOUR CODE - protected):**
- Resources: `app/Resources/` (UserResource.php, CountryResource.php, TimezoneResource.php, etc.)
- Register in: `config/resources.php`

### Frontend Architecture

Vue 3 SPA with strict service layer pattern.

**Key Patterns:**
- **Services** (`resources/js/services/` & `resources/js/core/services/`): ALL API calls here, never in components/stores
- **Stores** (`resources/js/stores/`): Pinia state management, calls services
- **Composables** (`resources/js/composables/` & `resources/js/core/composables/`): Reusable composition functions
- **Components** (`resources/js/components/` & `resources/js/core/components/`): Organized by type (common, form, resource, settings)
- **Routes**: Always use named routes, never paths

**Core vs Project Frontend:**
- **Core** (`resources/js/core/*`): Framework components, UPDATABLE from starter
- **Project** (`resources/js/components/*`, `resources/js/pages/*`, etc.): Your code, PROTECTED

**Always check for existing:**
- Select components: SelectInput, VirtualSelectInput, ServerSelectInput, ResourceSelectInput
- Form components: FormInput, CheckboxInput, SelectInput, MediaUpload, ColorPicker, IconPicker, PasswordInput
- UI components: Toast, ConfirmDialog, Tooltip (v-tooltip directive)
- Editors: TipTap WYSIWYG Editor, Monaco Code Editor

## Development Guidelines

**MUST READ - Comprehensive Guidelines:**
- Backend: `project_development_guidelines/backend.md`
- Frontend: `project_development_guidelines/frontend.md`
- Laravel Boost: `project_development_guidelines/laravel-boost.md`
- Project Rules: `project_development_guidelines/project-rules.md`

**Reference Documentation:**
- Resource CRUD: `project_development_guidelines/resource-crud-system.md`
- Settings System: `project_development_guidelines/settings.md`
- Email Templates: `project_development_guidelines/features/email-templates.md`
- Testing: `project_development_guidelines/testing.md`

**Components & Features:**
- Components: `project_development_guidelines/components/` (dialog, toast, tooltip, select, image, media)
- Features: `project_development_guidelines/features/` (conditional fields, resource enhancements, email templates)

## Core Folder Structure (CRITICAL)

This starter template uses a **Core folder structure** to separate updatable framework code from your project code.

### Backend Structure

```
app/
├── Core/                           # UPDATABLE from starter (merge=theirs)
│   ├── Resources/
│   │   ├── Resource.php            # Base Resource class
│   │   ├── Fields/*                # 18 field types
│   │   ├── Filters/*               # 5 filter types
│   │   └── Actions/*               # 4 action types
│   ├── Services/
│   │   └── ResourceService.php
│   └── Http/Controllers/
│       └── ResourceController.php
│
├── Resources/                      # YOUR CODE (merge=ours - protected)
│   ├── UserResource.php
│   ├── RoleResource.php
│   └── *Resource.php               # Your custom resources
├── Services/                       # YOUR CODE (protected)
├── Models/                         # YOUR CODE (protected)
└── Http/Controllers/               # YOUR CODE (protected)
```

### Frontend Structure

```
resources/js/
├── core/                           # UPDATABLE from starter (merge=theirs)
│   ├── components/resource/        # ResourceManager, ResourceTable, ResourceForm
│   ├── services/                   # resourceService.js
│   └── composables/                # Core composables
│
├── components/                     # YOUR CODE (merge=ours - protected)
├── pages/                          # YOUR CODE (protected)
├── services/                       # YOUR CODE (protected)
└── stores/                         # YOUR CODE (protected)
```

### Key Rules for Core Structure

1. **NEVER modify files in `app/Core/*` or `resources/js/core/*`** - these get overwritten during updates
2. **Extend Core classes in YOUR resources** - Use `extends Resource` from `App\Core\Resources\Resource`
3. **Import from Core** - `use App\Core\Resources\Fields\Text;` in your resources
4. **Use Core components** - `import ResourceManager from '@/core/components/resource/ResourceManager.vue'`
5. **Core updates automatically** - When you update from starter, Core files are replaced with `merge=theirs`
6. **Your code is protected** - Your resources, services, components are never overwritten with `merge=ours`

### Namespaces

- **Core System:** `App\Core\*` (framework code)
- **Your Project:** `App\*` (application code)

## Critical Project Rules

1. **Business Logic**: Services only - NEVER in controllers/models/components/stores
2. **Routes**: Always use named routes (`route()` function), never hardcoded paths
3. **API Calls**: Services only in frontend, never direct axios calls in components
4. **File Operations**: Always use Spatie Media Library, never custom file logic
5. **Environment**: Never use `env()` outside config files, always use `config()`
6. **Testing**: MySQL in tests (not SQLite), write tests for every change
7. **Code Format**: Run `vendor/bin/pint --dirty` before commits
8. **Migrations**: No changes to existing migrations, create new ones for production
9. **Seeders**: Always create seeders for settings, lists, and reference data required by the application
10. **Git Commits**: Never add "Generated with Claude Code" or co-author messages

## Laravel 12 Structure

- No `app/Console/Kernel.php` or `app/Http/Kernel.php`
- Middleware registered in `bootstrap/app.php`
- Providers in `bootstrap/providers.php`
- Commands auto-register from `app/Console/Commands/`

## Database & Seeders

**Seeder Best Practices:**
- Always use seeders for reference data (countries, timezones, settings lists, email templates)
- Use migrations to call seeders to ensure data reaches production
- Use `updateOrCreate()` or check for existence to prevent duplicates
- Register all seeders in `DatabaseSeeder.php` in the correct order

**Current Seeders:**
- CountriesSeeder - Country reference data
- TimezonesSeeder - Timezone reference data
- CountryTimezoneSeeder - Country-timezone relationships
- SettingListsSeeder - Settings lists/options
- EmailTemplatesSeeder - Email template definitions

## File Storage

- Production: S3/DigitalOcean Spaces
- Use Spatie Media Library for all file operations
- Media collections: `avatar`, `gallery`, `documents`, etc.

## External Documentation

- Laravel 12: https://laravel.com/docs/12.x
- Vue 3: https://vuejs.org/guide/introduction.html
- Tailwind CSS 4: https://tailwindcss.com/docs
- Spatie Media Library: https://spatie.be/docs/laravel-medialibrary

## Tailwind CSS 4 Notes

Use `@import "tailwindcss";` not `@tailwind` directives.

**Replaced utilities (opacity values still numeric):**
- `bg-opacity-*` → `bg-black/*`
- `text-opacity-*` → `text-black/*`
- `flex-shrink-*` → `shrink-*`
- `flex-grow-*` → `grow-*`
- `overflow-ellipsis` → `text-ellipsis`

## Available Resources

Current registered resources in the system:
- **users** - User management
- **roles** - Role management
- **countries** - Country reference data
- **timezones** - Timezone reference data

View all resources: Check `config/resources.php`
- do not git add or git commit or or push without explicit human confirmation
- do not run nom run build everytime you make a change in the frontend of this project