# Frontend Development Guidelines

## Architecture Overview

This application is a Vue 3 Single Page Application (SPA) with a service-oriented architecture where all API calls and business logic reside in dedicated service files.

### Directory Structure

**Entry Points**
- `resources/js/app.js` - Main entry for guest pages
- `resources/js/spa.js` - SPA entry for admin panel

**Core Directories**
- `resources/js/router/` - Vue Router with route guards
- `resources/js/stores/` - Pinia state management stores
- `resources/js/services/` - API service layer (ALL API calls here)
- `resources/js/components/` - Reusable Vue components
- `resources/js/layouts/` - Layout components
- `resources/js/pages/` - Page components
- `resources/js/composables/` - Vue composables
- `resources/js/directives/` - Custom Vue directives
- `resources/js/utils/` - Utility functions

## Core Architectural Rules

### Service Layer Pattern

**ALL API calls MUST be in service files.** Never make API calls directly in components or stores.

**Service Location:** `resources/js/services/`

**Pattern:**
- Services export objects with async methods
- Services use the centralized Axios instance from `utils/api.js`
- Components and stores call service methods

Reference: See `resources/js/services/authService.js`, `resources/js/services/settingsService.js`, and `resources/js/services/resourceService.js` for patterns.

### Pinia Store Pattern

**Stores coordinate between services and components.** They manage global state but delegate API calls to services.

**Store Location:** `resources/js/stores/`

**Pattern:**
- Stores import and call service methods
- Stores manage reactive state
- Use `storeToRefs()` for reactive destructuring in components
- Actions contain async logic that calls services

Reference: See `resources/js/stores/auth.js`, `resources/js/stores/settings.js`, and `resources/js/stores/toast.js` for patterns.

### Component Reusability

**Always check for existing components before creating new ones.**

**Component Categories:**
- **Form Components:** `components/form/` - FormInput, FormError, SelectInput, CheckboxInput
- **Common UI:** `components/common/` - Icon, DarkModeToggle, Toast, ConfirmDialog, ToggleSwitch
- **Resource CRUD:** `components/resource/` - ResourceManager, ResourceTable, ResourceForm, FilterBar, ActionButtons
- **Settings:** `components/settings/` - SettingsForm, SettingGroup, CountrySelect, TimezoneSelect
- **Layout:** `components/layout/` - Sidebar, Navbar, UserDropdown

Reference: Browse respective folders to see available components before creating new ones.

## Resource Manager System

The Resource Manager provides a generic CRUD interface for any backend Resource.

**Usage:** `<ResourceManager resource-key="users" />`

**Features:**
- Automatic table rendering with sorting and pagination
- Search functionality
- Filters integration
- Bulk actions support
- Create/Edit forms

Reference: See `components/resource/ResourceManager.vue` for implementation.

## Routing

### Route Configuration

**Location:** `resources/js/router/index.js`

Vue Router uses history mode (requires server configuration).

### Route Naming (CRITICAL)

**Always use route names, NEVER hardcoded paths.**

**In JavaScript:**
- Navigation: `router.push({ name: 'admin.users' })`
- Route checking: `route.name === 'admin.dashboard'`

**In Templates:**
- Links: `<RouterLink :to="{ name: 'admin.dashboard' }">Dashboard</RouterLink>`

### Route Guards

Routes can have meta fields for protection:
- `requiresAuth: true` - Requires authentication
- `requiresAdmin: true` - Requires admin role
- `requiresGuest: true` - Only for non-authenticated users

Reference: See `resources/js/router/index.js` for guard implementation.

## State Management

### Available Stores

- `useAuthStore()` - Authentication state and user data
- `useSettingsStore()` - User and global settings
- `useToastStore()` - Toast notifications
- `useDialogStore()` - Confirmation dialogs

### Accessing Stores

Use `storeToRefs()` for reactive destructuring of store state:

Reference: See existing components for usage patterns.

## Styling with Tailwind CSS

### Tailwind Version

This project uses **Tailwind CSS v4** with new import syntax.

**Import in CSS:**
Use `@import "tailwindcss";` not the old `@tailwind` directives.

Reference: See `resources/css/app.css` for import pattern.

### Utility Classes

Use Tailwind utility classes for all styling. Check existing project conventions before writing new patterns.

**Theme Colors (defined in `tailwind.config.js`):**
- `primary-*` - Blue shades
- `danger-*` - Red shades  
- `success-*` - Green shades

### Repeated Patterns

For repeated patterns, create CSS classes using Tailwind directives in `resources/css/app.css`.

Reference: See `resources/css/app.css` for existing custom classes.

### Spacing

When listing items, use gap utilities for spacing. Do not use margins.

### Dark Mode

All new pages and components must support dark mode using `dark:` classes if existing pages support it.

Reference: See existing components for dark mode patterns.

### Tailwind v4 Changes

**Deprecated utilities replaced:**
- Use `bg-black/*` instead of `bg-opacity-*`
- Use `text-black/*` instead of `text-opacity-*`
- Use `shrink-*` instead of `flex-shrink-*`
- Use `grow-*` instead of `flex-grow-*`
- Use `text-ellipsis` instead of `overflow-ellipsis`

Opacity values remain numeric.

## Key Frontend Features

### Dialog System

Confirmation dialogs with themes (danger, success, info).

**Composable:** `useDialog()` from `composables/useDialog.js`

**Usage:**
- `await confirmDanger('Delete this item?')`
- `await confirmSuccess('Proceed with action?')`
- `await confirmInfo('Are you sure?')`

**Features:**
- Promise-based API
- HTML content support
- Animations
- Loading states

Reference: See `project_development_guidelines/components/confirmation-dialog.md` and `composables/useDialog.js`.

### Toast Notifications

**Composable:** `useToast()` from `composables/useToast.js`

**Usage:**
- `showToast({ message: 'Success!', type: 'success' })`
- `showToast({ message: 'Error occurred', type: 'error', duration: 5000 })`

**Types:** success, error, info, warning

Reference: See `project_development_guidelines/components/toast.md` and `composables/useToast.js`.

### Tooltip Directive

**Directive:** `v-tooltip`

**Usage:** `<button v-tooltip="'Tooltip text'">Click</button>`

Reference: See `project_development_guidelines/components/tooltip.md` and `directives/tooltip.js`.

## Settings System

### User Settings
Per-user preferences (theme, timezone, notifications).

**Store:** `useSettingsStore()`
**Service:** `settingsService.js`
**Categories:** appearance, localization, notifications

### Global Settings
Application-wide configuration (admin only).

**Store:** `useSettingsStore()`
**Service:** `settingsService.js`
**Categories:** application, security, email, localization, appearance

### Settings Lists
Predefined options for select fields (countries, timezones, themes).

Reference: See `components/settings/` for components and `project_development_guidelines/settings.md` for documentation.

## Creating New Features

### Step-by-Step Process

1. **Create Service File**
   - Location: `resources/js/services/yourFeatureService.js`
   - Export object with async methods
   - Handle all API calls for this feature

2. **Create/Update Pinia Store (if needed)**
   - Location: `resources/js/stores/yourFeature.js`
   - Import and use service methods
   - Manage reactive state

3. **Create Page Component**
   - Location: `resources/js/pages/yourFeature/YourPage.vue`
   - Use existing layout components
   - Import and use services/stores

4. **Add Route**
   - File: `resources/js/router/index.js`
   - Use named routes
   - Add meta fields for guards if needed

5. **Write Vitest Tests**
   - Location: `resources/js/__tests__/`
   - Test component logic, stores, services
   - Test user interactions

6. **Add E2E Test (if critical)**
   - Location: `tests/e2e/`
   - Use Playwright for end-to-end testing
   - Test critical user journeys

Reference: See existing features in `resources/js/pages/` for patterns.

## Authentication & Authorization

### Authentication State

**Store:** `useAuthStore()`

**Key Properties:**
- `user` - Current user object
- `isAuthenticated` - Boolean authentication status
- `user.is_admin` - Check if user is admin

### Protected Routes

Routes with `requiresAuth: true` or `requiresAdmin: true` in meta are protected by router guards.

Reference: See `resources/js/router/index.js` for guard implementation.

## Form Handling

### VeeValidate Integration

This application uses VeeValidate for form validation.

Reference: See form components in `components/form/` for patterns.

### Form Components

Available form components:
- `FormInput.vue` - Text inputs with validation
- `FormError.vue` - Error message display
- `SelectInput.vue` - Select dropdowns
- `CheckboxInput.vue` - Checkboxes

Reference: See `components/form/` for all available form components.

## Image Handling

### Image Field Component

For displaying SVG, URL, and base64 images.

Reference: See `project_development_guidelines/components/image-field.md` for implementation details.

## Testing

### Testing Strategy

**Frontend Testing Tools:**
- **Vitest** - Unit and integration tests
- **Playwright** - E2E, accessibility, and performance tests

### Test Types

**Unit Tests (271):**
- Test component logic, stores, services, composables
- Location: `resources/js/__tests__/`

**Integration Tests (89):**
- Test feature workflows and store+service integration
- Location: `resources/js/__tests__/`

**E2E Tests (55):**
- Test critical user journeys with Playwright
- Location: `tests/e2e/`

**Accessibility Tests (35):**
- WCAG 2.1 AA compliance testing
- Location: `tests/e2e/`

**Performance Tests (10):**
- Core Web Vitals testing
- Location: `tests/e2e/`

### Running Tests

**Vitest (Unit/Integration):**
- All tests: `npm run test`
- Interactive UI: `npm run test:ui`
- With coverage: `npm run test:coverage`
- Pattern match: `npm run test -- settings`

**Playwright (E2E):**
- All E2E tests: `npm run test:e2e`
- Interactive mode: `npm run test:e2e:ui`
- Headed mode: `npm run test:e2e:headed`
- Debug mode: `npm run test:e2e:debug`

**Prerequisites:**
E2E tests require: `npx playwright install --with-deps`

Reference: See `TESTING.md` and `project_development_guidelines/testing.md` for comprehensive testing documentation.

## Development Workflow

### Running the Application

**Recommended:** `composer run dev`
This starts all services: backend server, queue worker, logs, and Vite dev server with HMR.

**Manual Alternative:**
- Backend: `php artisan serve`
- Queue: `php artisan queue:listen`
- Logs: `php artisan pail`
- Frontend: `npm run dev`

### Production Build

Command: `npm run build`

### Frontend Changes Not Appearing

If frontend changes don't appear in the browser:
1. Run `npm run build`
2. Restart `composer run dev` or `npm run dev`

## Vue 3 Best Practices

### Composition API

This project uses Vue 3 Composition API.

Reference: See existing components in `resources/js/components/` and `resources/js/pages/` for patterns.

### Composables

Reusable composition functions are in `resources/js/composables/`.

**Available Composables:**
- `useDialog()` - Confirmation dialogs
- `useToast()` - Toast notifications
- `useAuth()` - Authentication helpers

Reference: See `resources/js/composables/` for all available composables.

### Component Naming

Follow existing naming conventions in the project. Check sibling components for structure, approach, and naming patterns.

## API Integration

### Axios Instance

**Location:** `resources/js/utils/api.js`

This is a pre-configured Axios instance with interceptors for:
- Authentication token injection
- Error handling
- Response transformation

Always import this instance in services, never create new Axios instances.

Reference: See `resources/js/utils/api.js` for configuration.

## Layouts

### Available Layouts

- `AdminLayout.vue` - Admin panel layout with sidebar
- `ProfileLayout.vue` - Profile pages layout
- `SettingsLayout.vue` - Settings pages layout

**Location:** `resources/js/layouts/`

Reference: See existing layouts for structure and usage patterns.

## Important Documentation References

### Core Systems
- Resource CRUD System: `project_development_guidelines/resource-crud-system.md`
- Settings System: `project_development_guidelines/settings.md`
- Frontend Testing: `project_development_guidelines/testing.md`
- Main Testing: `TESTING.md`

### Components
- Image Field: `project_development_guidelines/components/image-field.md`
- Media Field: `project_development_guidelines/components/media-field.md`
- Dialog System: `project_development_guidelines/components/confirmation-dialog.md`
- Toast System: `project_development_guidelines/components/toast.md`
- Tooltips: `project_development_guidelines/components/tooltip.md`
- Select Components: `project_development_guidelines/components/select-components.md`

### Features
- Resource Enhancements: `project_development_guidelines/features/resource-enhancements.md`
- Conditional Fields: `project_development_guidelines/features/resource-conditional-fields.md`

## Notes

- Vue Router uses history mode requiring server configuration
- Dark mode is handled via Tailwind `dark:` classes stored in localStorage
- All Resource endpoints return consistent JSON structure with `data` wrapper
- Validation errors follow Laravel's standard format with 422 status
