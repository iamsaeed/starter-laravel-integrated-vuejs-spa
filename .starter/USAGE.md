# Laravel Vue Starter - Usage Guide

## Starting a New Project

### Option 1: Automated Setup (Recommended)

```bash
# 1. Clone the starter
git clone git@github.com:your-org/laravel-vue-starter.git my-project
cd my-project

# 2. Run setup script
chmod +x .starter/setup.sh
./.starter/setup.sh

# 3. Follow the prompts
```

### Option 2: Manual Setup

```bash
# 1. Clone the starter
git clone git@github.com:your-org/laravel-vue-starter.git my-project
cd my-project

# 2. Remove git history
rm -rf .git

# 3. Initialize new repo
git init
git add .
git commit -m "Initial commit from Laravel Vue Starter"

# 4. Add remotes
git remote add origin git@github.com:your-org/my-project.git
git remote add starter git@github.com:your-org/laravel-vue-starter.git

# 5. Push to GitHub
git branch -M main
git push -u origin main

# 6. Install dependencies
composer install
npm install

# 7. Setup environment
cp .env.example .env
php artisan key:generate

# 8. Setup database
php artisan migrate --seed

# 9. Start developing
composer run dev
```

## Receiving Updates

### Option 1: Automated Update (Recommended)

```bash
# Run update script
./.starter/update.sh
```

### Option 2: Manual Update

```bash
# 1. Fetch updates from starter
git fetch starter

# 2. Review changes
git log HEAD..starter/main
git diff HEAD..starter/main

# 3. Merge updates
git merge starter/main

# 4. Resolve conflicts if any
# Edit files, then:
git add .
git commit -m "Merged updates from starter"

# 5. Install updated dependencies
composer install
npm install
npm run build

# 6. Run migrations if needed
php artisan migrate

# 7. Test
php artisan test
npm run test
```

## What Gets Updated vs What Stays

### ✅ Safe to Update (Core System)

These files are managed by the starter and safe to update:

- `app/Resources/Resource.php` - Base Resource class
- `app/Resources/Fields/*` - All field types
- `app/Resources/Filters/*` - All filter types
- `app/Resources/Actions/*` - All action types
- `app/Http/Controllers/Api/ResourceController.php` - Resource API controller
- `app/Services/ResourceService.php` - Core resource service
- `resources/js/components/resource/*` - All Vue resource components
- `resources/js/services/resourceService.js` - Resource API service
- `resources/js/composables/useResource.js` - Resource composables
- `.starter/*` - Helper scripts and docs

### ⚠️ Manual Review Needed

These files may need manual conflict resolution:

- `routes/api.php` - API routes (you may have custom routes)
- `routes/web.php` - Web routes (you may have custom routes)
- `config/*` - Configuration files (review changes)
- `database/seeders/*` - Seeders (you may have custom seeders)
- `tests/*` - Tests (review updates)

### ❌ Never Update (Project-Specific)

These files are project-specific and protected:

- `app/Resources/UserResource.php` - Your custom User resource
- `app/Resources/RoleResource.php` - Your custom Role resource
- `app/Resources/CountryResource.php` - Your custom Country resource
- `app/Resources/TimezoneResource.php` - Your custom Timezone resource
- `app/Resources/*Resource.php` - Any other resources you create
- `database/migrations/*` - Your migrations
- `.env` - Your environment config
- `.env.example` - Your environment template
- `README.md` - Your project README

**Note:** The `.gitattributes` file automatically protects these files during merge.

## Handling Merge Conflicts

### Keep Your Version

```bash
# For project-specific files
git checkout --ours path/to/file
git add path/to/file
```

### Keep Starter Version

```bash
# For core system files
git checkout --theirs path/to/file
git add path/to/file
```

### Manual Merge

```bash
# Edit the file manually to combine changes
vim path/to/file
git add path/to/file
```

### Abort Merge

```bash
# If things go wrong
git merge --abort

# Restore from backup branch created by update.sh
git checkout backup-before-update-YYYYMMDD-HHMMSS
```

## Post-Update Checklist

After merging updates from starter:

- [ ] `composer install` - Update PHP dependencies
- [ ] `npm install` - Update JS dependencies
- [ ] `npm run build` - Build frontend assets
- [ ] `php artisan migrate` - Run new migrations (if any)
- [ ] `php artisan optimize:clear` - Clear all caches
- [ ] `php artisan test` - Run backend tests
- [ ] `npm run test` - Run frontend tests
- [ ] Test manually in browser
- [ ] Review `CHANGELOG.md` in starter repo
- [ ] Update your project's CHANGELOG

## Version Strategy

### Starter Template Versions

The starter uses **date-based versioning**:

- `v2025.01` - January 2025 release
- `v2025.02` - February 2025 release
- `v2025.03` - March 2025 release

### Merging Specific Versions

```bash
# Fetch all tags
git fetch starter --tags

# List available versions
git tag -l

# Merge specific version
git merge v2025.01

# Or merge latest
git merge starter/main
```

## Tips & Best Practices

### 1. Update Regularly

```bash
# Weekly or monthly
./.starter/update.sh
```

### 2. Test Updates in Branch First

```bash
# Create update branch
git checkout -b update-from-starter

# Merge updates
git fetch starter
git merge starter/main

# Test thoroughly
composer install
npm install
npm run build
php artisan test

# If all good, merge to main
git checkout main
git merge update-from-starter
```

### 3. Keep Your Custom Code Separate

Create new resources instead of modifying example ones:

```bash
# Good
app/Resources/ProductResource.php

# Avoid modifying these
app/Resources/UserResource.php (example from starter)
```

### 4. Subscribe to Starter Updates

Watch the starter repository on GitHub to get notified of updates.

### 5. Review CHANGELOG

Always check `CHANGELOG.md` in starter repo before updating:

```bash
# View changelog
git fetch starter
git show starter/main:CHANGELOG.md
```

## Troubleshooting

### "Starter remote not found"

```bash
# Add starter remote
git remote add starter git@github.com:your-org/laravel-vue-starter.git
```

### "Merge conflicts everywhere"

```bash
# Abort and try selective merge
git merge --abort

# Merge only specific files
git checkout starter/main -- app/Resources/Fields/
git commit -m "Updated field types from starter"
```

### "Update broke my custom features"

```bash
# Restore from backup
git reset --hard backup-before-update-YYYYMMDD-HHMMSS

# Or cherry-pick specific commits
git cherry-pick abc123f
```

### "Dependencies conflict"

```bash
# Clear everything and reinstall
rm -rf vendor node_modules
composer install
npm install
```

## Getting Help

- **Starter Issues:** [GitHub Issues](https://github.com/your-org/laravel-vue-starter/issues)
- **Discussions:** [GitHub Discussions](https://github.com/your-org/laravel-vue-starter/discussions)
- **Documentation:** Check `project_development_guidelines/` folder
