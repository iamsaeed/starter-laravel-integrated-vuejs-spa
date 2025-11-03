# Project Development Guidelines

This directory contains all reference documentation needed for development. All documentation here represents **completed, implemented features** that are part of the codebase.

## Structure

### Core Guidelines
- **[backend.md](backend.md)** - Backend development standards and architecture
- **[frontend.md](frontend.md)** - Frontend development standards and architecture
- **[laravel-boost.md](laravel-boost.md)** - Laravel Boost MCP tools and standards
- **[project-rules.md](project-rules.md)** - Project-specific rules and conventions

### Core Systems
- **[resource-crud-system.md](resource-crud-system.md)** - The Resource CRUD system (Laravel Nova-inspired)
- **[settings.md](settings.md)** - User and global settings system
- **[testing.md](testing.md)** - Frontend testing with Vitest and Playwright

### Components (`components/`)
Reusable UI components and their documentation:
- **[confirmation-dialog.md](components/confirmation-dialog.md)** - Confirmation dialog system
- **[toast.md](components/toast.md)** - Toast notification system
- **[tooltip.md](components/tooltip.md)** - Tooltip directive
- **[image-field.md](components/image-field.md)** - Image field for Resources
- **[media-field.md](components/media-field.md)** - Media upload field for Resources
- **[select-components.md](components/select-components.md)** - Select, VirtualSelect, ServerSelect, ResourceSelect components

### Features (`features/`)
System features and their implementation:
- **[email-templates.md](features/email-templates.md)** - Email template management system
- **[multi-tenancy.md](features/multi-tenancy.md)** - Multi-tenancy architecture and database context
- **[module-system.md](features/module-system.md)** - Module system architecture
- **[resource-enhancements.md](features/resource-enhancements.md)** - Resource system enhancements
- **[resource-conditional-fields.md](features/resource-conditional-fields.md)** - Conditional field visibility in resources

### Modules (`modules/`)
Completed module references:
- **[website-module.md](modules/website-module.md)** - Website/CMS module reference

## Documentation Usage

### For AI Assistants
All documentation in this directory should be used as reference when:
- Building new features following existing patterns
- Understanding system architecture
- Making changes to existing functionality
- Writing tests for features

### For Developers
This documentation provides:
- Architecture patterns to follow
- Code examples and conventions
- Testing strategies
- Integration points

## Archived Documentation

Historical implementation plans and phase documentation are in `/docs/archived/`. These are **not** reference materials but historical records of how features were built.

## Adding New Documentation

When adding new reference documentation:

1. **Components**: Add to `components/` if it's a reusable UI component
2. **Features**: Add to `features/` if it's a system-wide feature
3. **Modules**: Add to `modules/` if it's a complete module (after implementation is complete)
4. **Update References**: Update [CLAUDE.md](../CLAUDE.md), [backend.md](backend.md), or [frontend.md](frontend.md) as needed

## External Documentation

- **Laravel**: https://laravel.com/docs/12.x
- **Vue 3**: https://vuejs.org/guide/introduction.html
- **Tailwind CSS 4**: https://tailwindcss.com/docs
- **Multi-Tenancy (Stancl)**: https://tenancyforlaravel.com/docs
- **Neuron AI**: https://docs.neuron-ai.dev/
- **Production Deployment**: [../docs/PRODUCTION_DEPLOYMENT.md](../docs/PRODUCTION_DEPLOYMENT.md)
