# Plan: Website Module Implementation for Tenant Public-Facing Websites

## Overview
I'll create a "Website" module that tenants can activate to get a simple 4-6 page blog-based public website. When activated, the tenant can configure a custom domain and manage all website content from the existing user panel.

## Key Features
- **Module-based activation**: Works like existing modules (expenses, tasks, ai-assistant)
- **Custom domain support**: Using existing `domains` table and Stancl Tenancy's domain features
- **Simple blog/content management**: 4-6 pages with blog posts
- **Integrated admin panel**: Manage from existing workspace UI
- **SEO-friendly**: Clean URLs, meta tags, sitemap
- **Ultra-fast performance**: Blade templates with vanilla JS, minimal assets
- **Tailwind CSS**: Consistent styling with purged production builds
- **Custom HTML templates**: Support for tenant-specific designs

## Implementation Plan

## Phase 1: Backend Module Setup

### 1.1 Add Website Module Configuration
**File**: `config/modules.php`
- Add 'website' module configuration with features, permissions, icon, etc.
- Set price to 0.0 (free module initially)
- Define permissions: website.view, website.manage_pages, website.manage_posts, website.manage_settings, website.manage_domains

### 1.2 Create Module Migration
**File**: `database/migrations/[timestamp]_add_website_module.php`
- Run ModulesSeeder to add website module to modules table

### 1.3 Create Tenant Database Tables
**Files in**: `database/migrations/tenant/`
- `create_website_settings_table.php` - Site title, tagline, logo, theme settings, custom CSS/JS
- `create_website_pages_table.php` - Static pages with custom HTML templates
- `create_website_posts_table.php` - Blog posts with categories
- `create_website_categories_table.php` - Blog categories
- `create_website_menus_table.php` - Navigation menus
- `create_website_menu_items_table.php` - Menu links
- `create_website_contacts_table.php` - Contact form submissions
- `create_website_templates_table.php` - Custom HTML templates for pages/posts
- `create_website_blocks_table.php` - Reusable HTML content blocks

## Phase 2: Models & Backend Logic

### 2.1 Create Tenant Models
**Files in**: `app/Models/`
- `WebsiteSetting.php` - Site configuration
- `WebsitePage.php` - Static pages with template support
- `WebsitePost.php` - Blog posts
- `WebsiteCategory.php` - Blog categories
- `WebsiteMenu.php` - Navigation menus
- `WebsiteMenuItem.php` - Menu items
- `WebsiteContact.php` - Contact submissions
- `WebsiteTemplate.php` - Custom HTML templates
- `WebsiteBlock.php` - Reusable content blocks

### 2.2 Create Services
**Files in**: `app/Services/`
- `WebsiteService.php` - Core website logic
- `WebsitePageService.php` - Page management with template rendering
- `WebsitePostService.php` - Blog post management
- `WebsiteDomainService.php` - Domain configuration
- `WebsiteTemplateService.php` - Template compilation and caching
- `WebsiteAssetService.php` - CSS/JS optimization and minification

### 2.3 Create Controllers
**Files in**: `app/Http/Controllers/`

**API Controllers** (for admin panel):
- `Api/WebsiteSettingsController.php`
- `Api/WebsitePagesController.php`
- `Api/WebsitePostsController.php`
- `Api/WebsiteDomainsController.php`

**Public Controllers** (for public website):
- `Tenant/Public/WebsiteController.php` - Homepage
- `Tenant/Public/PageController.php` - Static pages
- `Tenant/Public/BlogController.php` - Blog listing & posts
- `Tenant/Public/ContactController.php` - Contact form

### 2.4 Create Resources
**Files in**: `app/Resources/`
- `WebsitePageResource.php` - CRUD for pages
- `WebsitePostResource.php` - CRUD for blog posts
- `WebsiteCategoryResource.php` - CRUD for categories

## Phase 3: Routing Configuration

### 3.1 Update Tenant Routes
**File**: `routes/tenant.php`
```php
// Public website routes (domain-based)
Route::middleware(['web', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
->group(function () {
    Route::get('/', [WebsiteController::class, 'home'])->name('website.home');
    Route::get('/blog', [BlogController::class, 'index'])->name('website.blog');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('website.blog.post');
    Route::get('/contact', [ContactController::class, 'show'])->name('website.contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('website.contact.submit');
    Route::get('/{slug}', [PageController::class, 'show'])->name('website.page');
});
```

### 3.2 Add API Routes
**File**: `routes/api.php`
```php
// Website module API routes (workspace-scoped)
Route::prefix('workspace/{workspace}/website')->middleware(['tenant', 'tenant.member'])->group(function () {
    Route::apiResource('pages', Api\WebsitePagesController::class);
    Route::apiResource('posts', Api\WebsitePostsController::class);
    Route::apiResource('categories', Api\WebsiteCategoriesController::class);
    Route::get('settings', [Api\WebsiteSettingsController::class, 'index']);
    Route::put('settings', [Api\WebsiteSettingsController::class, 'update']);
    Route::apiResource('domains', Api\WebsiteDomainsController::class);
});
```

## Phase 4: Frontend Admin Panel

### 4.1 Create Admin Components
**Files in**: `resources/js/pages/workspace/website/`
- `index.vue` - Main website dashboard
- `WebsiteSettings.vue` - Site settings (title, logo, theme)
- `PageManager.vue` - CRUD for pages
- `PostManager.vue` - CRUD for blog posts
- `CategoryManager.vue` - Manage categories
- `MenuBuilder.vue` - Drag-drop menu builder
- `DomainSettings.vue` - Configure custom domain
- `ContactSubmissions.vue` - View contact form entries

### 4.2 Add Router Routes
**File**: `resources/js/router/index.js`
```javascript
// Website Module Routes
{ path: 'website', name: 'workspace.website', component: () => import('@/pages/workspace/website/index.vue'), meta: { title: 'Website', auth: 'user' } },
{ path: 'website/settings', name: 'workspace.website.settings', component: () => import('@/pages/workspace/website/WebsiteSettings.vue'), meta: { title: 'Website Settings', auth: 'user' } },
{ path: 'website/pages', name: 'workspace.website.pages', component: () => import('@/pages/workspace/website/PageManager.vue'), meta: { title: 'Pages', auth: 'user' } },
{ path: 'website/blog', name: 'workspace.website.posts', component: () => import('@/pages/workspace/website/PostManager.vue'), meta: { title: 'Blog Posts', auth: 'user' } },
{ path: 'website/domains', name: 'workspace.website.domains', component: () => import('@/pages/workspace/website/DomainSettings.vue'), meta: { title: 'Domain Settings', auth: 'user' } },
```

### 4.3 Create Services
**Files in**: `resources/js/services/`
- `websiteService.js` - API calls for website management

## Phase 5: Public Website Frontend (Blade + Vanilla JS)

### 5.1 Create Blade Templates
**Files in**: `resources/views/tenant/website/`
```
website/
├── layouts/
│   ├── base.blade.php           # Master layout with Tailwind CSS
│   ├── header.blade.php         # Dynamic navigation header
│   └── footer.blade.php         # Site footer
├── pages/
│   ├── home.blade.php           # Homepage template
│   ├── page.blade.php           # Generic page template
│   ├── blog/
│   │   ├── index.blade.php     # Blog listing with pagination
│   │   └── show.blade.php      # Single blog post
│   └── contact.blade.php        # Contact form page
├── partials/
│   ├── blog-card.blade.php     # Blog post preview card
│   ├── contact-form.blade.php  # Contact form component
│   ├── pagination.blade.php    # Custom pagination
│   └── meta-tags.blade.php     # SEO meta tags
└── templates/                   # Custom tenant templates
    ├── default/                 # Default theme templates
    │   ├── header.blade.php
    │   └── footer.blade.php
    └── custom/                  # Tenant-specific overrides
```

### 5.2 Create Vanilla JS Assets
**Files in**: `resources/js/website/`
```javascript
// website.js - Minimal vanilla JS for interactivity
- Mobile menu toggle
- Contact form validation
- Lazy loading images
- Smooth scroll navigation
- Form submission handling (AJAX)
```

### 5.3 Tailwind CSS Configuration
**File**: `resources/css/website.css`
```css
@import "tailwindcss/base";
@import "tailwindcss/components";
@import "tailwindcss/utilities";

/* Custom utility classes for website */
.container-website { @apply max-w-6xl mx-auto px-4; }
.btn-primary { @apply bg-primary-600 text-white px-6 py-2 rounded hover:bg-primary-700; }
```

### 5.4 Asset Compilation
- Separate Vite entry point for public website
- Aggressive purging for Tailwind (only used classes)
- Minification and compression
- Critical CSS inlining for above-the-fold content

## Phase 6: Domain Configuration

### 6.1 Domain Management Features
- Add/verify custom domains through admin panel
- DNS verification (TXT record check)
- SSL certificate instructions
- Subdomain support (tenant.yourapp.com)
- Primary domain selection

### 6.2 Update Nginx/Apache Config
- Instructions for wildcard SSL certificates
- Server block configuration for tenant domains
- Proxy settings if needed

## Phase 7: Template Customization & Themes

### 7.1 Default Content Seeder
**File**: `database/seeders/tenant/DefaultWebsiteContentSeeder.php`
- Create default pages: Home, About, Services, Contact
- Sample blog posts
- Default menu structure
- Base templates for common layouts

### 7.2 Template Customization System
**Features:**
- **Template Editor in Admin Panel**
  - CodeMirror/Monaco editor for HTML/Blade editing
  - Live preview with iframe
  - Template variables documentation
  - Revert to default option

- **Template Variables**
  ```blade
  {{-- Available in all templates --}}
  {{ $site->title }}
  {{ $site->tagline }}
  {{ $site->logo_url }}
  {{ $menu->render() }}
  {{ $page->title }}
  {{ $page->content }}
  {{ $page->meta_description }}
  ```

- **Custom Blocks System**
  - Create reusable HTML blocks
  - Insert via shortcodes: `[block:header-cta]`
  - Drag-drop block builder in admin

### 7.3 Theme Configuration
**Customizable via Admin Panel:**
```php
// Stored in website_settings table
[
    'colors' => [
        'primary' => '#3B82F6',
        'secondary' => '#10B981',
        'accent' => '#F59E0B'
    ],
    'fonts' => [
        'heading' => 'Inter',
        'body' => 'system-ui'
    ],
    'layout' => [
        'container_width' => '1280px',
        'header_style' => 'sticky', // sticky, fixed, static
        'footer_columns' => 4
    ],
    'custom_css' => '/* Tenant custom CSS */',
    'custom_js' => '/* Tenant custom JS */',
    'custom_head' => '<!-- Analytics, fonts, etc -->',
]
```

### 7.4 Template Inheritance
```blade
{{-- Tenant can override any section --}}
@extends('tenant.website.layouts.base')

@section('header')
    {{-- Custom header HTML --}}
    @include('tenant.website.templates.custom.header')
@endsection

@section('content')
    {{-- Page content with custom wrapper --}}
    <div class="custom-content-wrapper">
        {!! $page->renderContent() !!}
    </div>
@endsection
```

## Phase 8: Performance Optimization

### 8.1 Caching Strategy
- **Full page caching**: Cache entire HTML responses
- **Fragment caching**: Cache expensive partials (menus, sidebars)
- **Database query caching**: Cache settings and menus
- **Template compilation**: Pre-compile Blade templates
- **Asset versioning**: Bust cache on updates

### 8.2 Asset Optimization
```php
// WebsiteAssetService.php
class WebsiteAssetService {
    public function optimizeCSS($css) {
        // Remove comments, whitespace
        // Inline critical CSS
        // Defer non-critical CSS
    }

    public function optimizeJS($js) {
        // Minify JavaScript
        // Remove console.logs in production
        // Defer non-critical scripts
    }
}
```

### 8.3 Performance Features
- **Lazy loading**: Images, iframes, videos
- **Image optimization**: WebP conversion, responsive images
- **CDN ready**: Asset URLs configurable for CDN
- **Gzip compression**: Compress HTML, CSS, JS
- **HTTP/2 Push**: Push critical assets

### 8.4 Monitoring
- **Page load metrics**: Track Core Web Vitals
- **Cache hit rates**: Monitor cache effectiveness
- **Database query analysis**: Identify slow queries

## Phase 9: Testing

### 9.1 Backend Tests
**Files in**: `tests/Feature/`
- `WebsiteModuleTest.php` - Module activation/deactivation
- `WebsitePagesTest.php` - Page CRUD operations
- `WebsitePostsTest.php` - Blog post management
- `WebsiteDomainTest.php` - Domain configuration
- `WebsitePublicTest.php` - Public website access
- `WebsiteTemplateTest.php` - Template rendering
- `WebsitePerformanceTest.php` - Performance benchmarks

### 9.2 Frontend Tests
- Performance tests with Lighthouse CI
- Accessibility tests (WCAG 2.1 AA)
- Cross-browser testing
- Mobile responsiveness tests

## File Structure Summary
```
Backend:
├── app/
│   ├── Models/
│   │   ├── WebsiteSetting.php
│   │   ├── WebsitePage.php
│   │   ├── WebsitePost.php
│   │   ├── WebsiteTemplate.php
│   │   └── WebsiteBlock.php
│   ├── Services/
│   │   ├── WebsiteService.php
│   │   ├── WebsiteTemplateService.php
│   │   └── WebsiteAssetService.php
│   ├── Http/Controllers/
│   │   ├── Api/Website*Controller.php
│   │   └── Tenant/Public/*Controller.php
│   └── Resources/
│       └── Website*Resource.php
├── database/migrations/tenant/
│   ├── create_website_*_table.php
│   └── ...
└── config/modules.php (updated)

Frontend (Admin Panel - Vue):
├── resources/js/
│   ├── pages/workspace/website/
│   │   ├── index.vue
│   │   ├── PageManager.vue
│   │   ├── PostManager.vue
│   │   ├── TemplateEditor.vue
│   │   └── ThemeCustomizer.vue
│   └── services/
│       └── websiteService.js

Frontend (Public Website - Blade):
├── resources/views/tenant/website/
│   ├── layouts/
│   │   ├── base.blade.php
│   │   ├── header.blade.php
│   │   └── footer.blade.php
│   ├── pages/
│   │   ├── home.blade.php
│   │   ├── page.blade.php
│   │   └── blog/
│   ├── partials/
│   │   └── ...
│   └── templates/
│       ├── default/
│       └── custom/
├── resources/js/website/
│   └── website.js (vanilla JS)
└── resources/css/
    └── website.css (Tailwind)
```

## Implementation Order
1. Backend module configuration and migrations
2. Models and services
3. API controllers and routes
4. Admin panel UI
5. Public website Blade templates
6. Template customization system
7. Domain configuration
8. Performance optimization
9. Testing

## How Tenants Customize HTML Templates

### 1. Through Admin Panel UI
Tenants can customize their website design through multiple methods:

#### Template Editor
- **Visual Editor**: Monaco/CodeMirror with syntax highlighting
- **Live Preview**: Side-by-side preview with auto-refresh
- **Template Library**: Pre-built templates to choose from
- **Version Control**: Save multiple versions, revert changes

#### Available Customization Options:
```blade
{{-- Example: Custom Page Template --}}
@extends('tenant.website.layouts.base')

@section('custom_styles')
<style>
    /* Tenant's custom CSS */
    .hero-section {
        background: {{ $settings->hero_gradient }};
        min-height: {{ $settings->hero_height }}px;
    }
</style>
@endsection

@section('content')
<div class="hero-section">
    <!-- Tenant's custom HTML -->
    <h1>{{ $page->title }}</h1>
    {!! $page->custom_html !!}
</div>

{{-- Include reusable blocks --}}
@include('tenant.website.blocks.' . $page->template_block)
@endsection
```

### 2. Template Variables Available
```php
// All templates have access to:
$site         // Site settings (title, logo, theme)
$menu         // Navigation menu builder
$page         // Current page data
$posts        // Blog posts (on blog pages)
$categories   // Blog categories
$blocks       // Reusable content blocks
$assets       // Asset URLs (images, files)
```

### 3. Custom HTML/CSS/JS Fields
Each page can have:
- **Custom HTML**: Raw HTML content
- **Custom CSS**: Page-specific styles
- **Custom JS**: Page-specific scripts (vanilla JS)
- **Meta tags**: SEO optimization

### 4. Theme Inheritance
```
Default Theme → Tenant Customizations → Page-Specific Overrides

Example flow:
1. Start with default Bootstrap/Tailwind theme
2. Apply tenant's global customizations (colors, fonts)
3. Apply page-specific template if selected
4. Inject custom HTML/CSS/JS
```

### 5. Safe Template Rendering
```php
// WebsiteTemplateService.php
public function renderTemplate($template, $data) {
    // Sanitize user HTML (configurable level)
    $html = $this->sanitizeHtml($template->content);

    // Compile Blade template with restricted directives
    $compiled = Blade::compileString($html);

    // Render with sandboxed data
    return view()->make($compiled, $data)->render();
}
```

This approach provides maximum flexibility while maintaining security and performance.