# Laravel Vue Starter Template

A production-ready Laravel 12 + Vue 3 SPA starter template with a powerful Resource CRUD system, comprehensive authentication, and modern development tooling.

## 🚀 Quick Start

### Using GitHub Template (Recommended)

1. Click "Use this template" on GitHub
2. Clone your new repository
3. Run setup:

```bash
cd your-project
git remote add starter git@github.com:your-org/laravel-vue-starter.git
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

### Manual Setup

```bash
# Clone and setup
git clone git@github.com:your-org/laravel-vue-starter.git my-project
cd my-project
./.starter/setup.sh

# Install and run
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

📖 **Full documentation:** [.starter/USAGE.md](.starter/USAGE.md)

## ✨ Features

### Resource CRUD System
- **Laravel Nova-inspired** automatic CRUD system
- Define resources with Fields, Filters, and Actions
- Auto-generated API endpoints, forms, and tables
- Built-in field types: Text, Select, Number, Boolean, Date, Image, BelongsToMany, etc.
- Customizable filters and bulk actions

### Authentication & Authorization
- Laravel Sanctum SPA authentication
- Role-based permissions
- User management resource

### Frontend (Vue 3)
- Modern Vue 3 Composition API
- Pinia state management
- Vue Router with named routes
- Tailwind CSS 4
- Reusable form components
- VeeValidate form validation
- TipTap WYSIWYG editor
- Monaco code editor

### Backend (Laravel 12)
- Service-oriented architecture
- Spatie Media Library integration
- Laravel Boost for performance
- Comprehensive testing setup
- Scheduled tasks and queues

### Developer Experience
- Hot Module Replacement (HMR)
- Laravel Pint code formatting
- Comprehensive test suites (PHPUnit, Vitest, Playwright)
- Development helper scripts
- Detailed documentation

## 📦 Tech Stack

**Backend:**
- Laravel 12
- PHP 8.2
- MySQL
- Laravel Sanctum
- Spatie Media Library
- Laravel Boost

**Frontend:**
- Vue 3
- Pinia
- Vue Router
- Vite
- Tailwind CSS 4
- VeeValidate
- TipTap Editor

**Testing:**
- PHPUnit (Backend)
- Vitest (Frontend Unit/Integration)
- Playwright (E2E)

**Payments:**
- Stripe
- Razorpay
- Laravel Cashier

## 🔄 Receiving Updates

This starter template can receive updates even after you've started your project:

```bash
# Automated update
./.starter/update.sh

# Manual update
git fetch starter
git merge starter/main
```

The `.gitattributes` file protects your custom code during updates. See [.starter/USAGE.md](.starter/USAGE.md) for details.

## 📝 Documentation

- **Quick Start:** [.starter/USAGE.md](.starter/USAGE.md)
- **Resource CRUD System:** [project_development_guidelines/resource-crud-system.md](project_development_guidelines/resource-crud-system.md)
- **Backend Guidelines:** [project_development_guidelines/backend.md](project_development_guidelines/backend.md)
- **Frontend Guidelines:** [project_development_guidelines/frontend.md](project_development_guidelines/frontend.md)
- **Settings System:** [project_development_guidelines/settings.md](project_development_guidelines/settings.md)
- **Email Templates:** [project_development_guidelines/features/email-templates.md](project_development_guidelines/features/email-templates.md)
- **Testing Guide:** [project_development_guidelines/testing.md](project_development_guidelines/testing.md)

## 🛠️ Development Commands

```bash
# Start development server with all services
composer run dev

# Run tests
composer run test          # Backend tests
npm run test              # Frontend tests
npm run test:e2e          # E2E tests

# Code formatting
vendor/bin/pint --dirty   # Format PHP code

# Database
php artisan app:reset     # Reset database with fresh data
php artisan migrate:fresh --seed

# Build for production
npm run build
```

## 📁 Project Structure

```
├── app/
│   ├── Core/                      # UPDATABLE - Core system from starter
│   │   ├── Resources/
│   │   │   ├── Resource.php       # Base Resource class
│   │   │   ├── Fields/            # All field types
│   │   │   ├── Filters/           # All filter types
│   │   │   └── Actions/           # All action types
│   │   ├── Services/
│   │   │   └── ResourceService.php
│   │   └── Http/Controllers/
│   │       └── ResourceController.php
│   │
│   ├── Resources/                 # PROJECT-SPECIFIC - Your resources
│   │   ├── UserResource.php
│   │   ├── RoleResource.php
│   │   ├── CountryResource.php
│   │   └── TimezoneResource.php
│   ├── Services/                  # Your services
│   ├── Models/                    # Your models
│   └── Http/Controllers/          # Your controllers
│
├── resources/js/
│   ├── core/                      # UPDATABLE - Core UI system
│   │   ├── components/resource/   # ResourceManager, ResourceTable, etc.
│   │   ├── services/              # resourceService.js
│   │   └── composables/           # Core composables
│   │
│   ├── components/                # PROJECT-SPECIFIC - Your components
│   │   ├── common/
│   │   └── form/
│   ├── pages/                     # Your pages
│   ├── services/                  # Your services
│   └── stores/                    # Your stores
│
├── .starter/                      # Starter template utilities
│   ├── setup.sh                   # New project setup
│   ├── update.sh                  # Update from starter
│   └── USAGE.md                   # Usage guide
│
└── project_development_guidelines/  # Documentation
```

### Core vs Project Files

**Core Files (Updatable):**
- `app/Core/*` - Gets updated from starter
- `resources/js/core/*` - Gets updated from starter

**Project Files (Protected):**
- `app/Resources/*` - Your custom resources
- `app/Services/*`, `app/Models/*`, `app/Http/Controllers/*` - Your code
- `resources/js/components/*`, `resources/js/pages/*`, etc. - Your frontend

## 🎯 Available Resources

Current pre-built resources:
- **Users** - User management with roles
- **Roles** - Role management
- **Countries** - Country reference data
- **Timezones** - Timezone reference data

Creating new resources is simple - see [resource-crud-system.md](project_development_guidelines/resource-crud-system.md).

## 🔐 Environment Setup

```bash
cp .env.example .env
php artisan key:generate

# Configure your database
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate --seed
```

## 🧪 Testing

```bash
# Backend tests (PHPUnit)
php artisan test
php artisan test --filter=ResourceTest

# Frontend tests (Vitest)
npm run test
npm run test:ui

# E2E tests (Playwright)
npm run test:e2e
npm run test:e2e:ui
```

## 📄 License

This starter template is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

This is a starter template. Fork it and make it your own!

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
