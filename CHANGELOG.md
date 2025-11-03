# Changelog

All notable changes to the Laravel Vue Starter template will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project uses **date-based versioning** (YYYY.MM).

## [Unreleased]

### Added
- Starter template system with helper scripts
- Smart merging via .gitattributes
- Update and setup automation scripts

## [v2025.01] - 2025-01-15

### Added
- Initial starter template release
- Laravel 12 + Vue 3 SPA foundation
- Resource CRUD system with automatic API endpoints
- Resource Manager Vue components
- Authentication system with Laravel Sanctum
- Settings management system
- Email template management
- Example resources (User, Role, Country, Timezone)
- Comprehensive testing setup (PHPUnit, Vitest, Playwright)
- Development tooling (Pint, Vite, Tailwind CSS 4)
- Starter helper scripts (.starter/setup.sh, .starter/update.sh)
- Complete documentation in project_development_guidelines/

### Core Components

#### Resource System
- Resource base class with Fields, Filters, Actions
- ResourceController for automatic CRUD API
- ResourceService for business logic
- ResourceManager, ResourceTable, ResourceForm (Vue)

#### Field Types
- Text, Textarea, Email, Password
- Select, MultiSelect, BelongsToMany
- Number, Boolean, Date, DateTime
- Image, File, Media
- Color, Icon, Code

#### Filter Types
- SelectFilter
- BooleanFilter
- DateRangeFilter

#### Actions
- BulkDeleteAction
- BulkUpdateAction
- ExportAction

### Infrastructure
- Service-oriented architecture
- Spatie Media Library integration
- Laravel Boost for performance
- Comprehensive seeder system
- GitHub template repository support
- Smart git merge strategies

### Frontend Components
- ResourceManager - Main CRUD interface
- ResourceTable - Data table with search, filters, pagination
- ResourceForm - Auto-generated forms
- Select components (Select, VirtualSelect, ServerSelect, ResourceSelect)
- Form inputs (Text, Checkbox, Radio, File, Media, Color, Icon, Password)
- TipTap WYSIWYG Editor
- Monaco Code Editor
- Toast notifications
- Confirm dialogs
- Tooltip directive

### Backend Services
- AuthService - Authentication logic
- ResourceService - Resource CRUD operations
- EmailTemplateService - Email template management
- SettingsService - Settings management
- MediaService - File upload/management

### Testing
- PHPUnit backend tests with MySQL
- Vitest frontend unit/integration tests
- Playwright E2E tests
- Test utilities and helpers

### Development Tools
- Laravel Pint for code formatting
- Vite with HMR for fast development
- Comprehensive ESLint setup
- Database seeding system
- Development command scripts

### Documentation
- Complete resource CRUD system guide
- Backend development guidelines
- Frontend development guidelines
- Settings system documentation
- Email templates guide
- Testing guide
- Component documentation
- Feature guides

## Version History

### Versioning Strategy

This project uses date-based versioning:
- Format: `vYYYY.MM` (e.g., v2025.01, v2025.02)
- Major releases: Monthly or quarterly
- Patch releases: As needed for critical fixes

### Upgrade Notes

When upgrading from one version to another:

1. Review this CHANGELOG for breaking changes
2. Run `git fetch starter` to get latest updates
3. Review changes with `git diff HEAD..starter/main`
4. Merge updates with `./.starter/update.sh` or manually
5. Run `composer install` and `npm install`
6. Run `php artisan migrate`
7. Clear caches with `php artisan optimize:clear`
8. Test thoroughly

### Breaking Changes

None yet - this is the initial release.

### Deprecations

None yet.

## Support

- **Issues:** [GitHub Issues](https://github.com/your-org/laravel-vue-starter/issues)
- **Discussions:** [GitHub Discussions](https://github.com/your-org/laravel-vue-starter/discussions)
- **Documentation:** Check `project_development_guidelines/` folder
- **Usage Guide:** [.starter/USAGE.md](.starter/USAGE.md)
