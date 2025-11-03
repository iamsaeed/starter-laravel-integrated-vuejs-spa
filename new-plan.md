# Single Starter Template - Pull, Start, Update

## Goal

Create a **single starter template** that supports three simple workflows:

1. **PULL** - Clone the starter to begin a new project
2. **START** - Remove .git, push to new GitHub repo
3. **UPDATE** - Fetch and merge updates from starter anytime

No packages, no submodules, no complexity - just Git.

## How It Works

```
┌─────────────────────────────────────────────┐
│  Starter Template Repository                │
│  github.com/your-org/laravel-vue-starter    │
│                                             │
│  Contains:                                  │
│  - Resource System (base classes)           │
│  - ResourceManager (Vue components)         │
│  - Full Laravel + Vue app                   │
│  - Example resources (User, Role, etc.)     │
└──────────────┬──────────────────────────────┘
               │
               │ PULL (clone)
               ▼
┌──────────────────────────────────────────────┐
│  Step 1: Clone                               │
│  git clone starter.git my-project            │
└──────────────┬───────────────────────────────┘
               │
               │ START (setup new repo)
               ▼
┌──────────────────────────────────────────────┐
│  Step 2: Remove .git & Push to New Repo     │
│  rm -rf .git                                 │
│  git init                                    │
│  git remote add origin my-project.git        │
│  git remote add starter starter.git          │
└──────────────┬───────────────────────────────┘
               │
               │ Develop custom features
               ▼
┌──────────────────────────────────────────────┐
│  Your Project Repository                     │
│  github.com/your-org/my-project              │
│                                              │
│  - Started from template                     │
│  - Custom features added                     │
│  - Custom resources (Product, Order, etc.)   │
└──────────────┬───────────────────────────────┘
               │
               │ UPDATE (pull from starter)
               ▼
┌──────────────────────────────────────────────┐
│  Step 3: Fetch & Merge Updates               │
│  git fetch starter                           │
│  git merge starter/main                      │
└──────────────────────────────────────────────┘
```

## Implementation Plan

### Phase 1: Setup Helper Scripts and Configuration

**Goal:** Create automation scripts to make the workflow seamless.

#### Task 1: Create .gitattributes for Smart Merging
Protect project-specific files from being overwritten during updates.

```gitattributes
# .gitattributes

# Environment files - always keep yours
.env merge=ours
.env.example merge=ours

# Project-specific resources - keep yours
app/Resources/UserResource.php merge=ours
app/Resources/RoleResource.php merge=ours
app/Resources/CountryResource.php merge=ours
app/Resources/TimezoneResource.php merge=ours

# Routes - manual merge
routes/api.php merge=union
routes/web.php merge=union

# Migrations - always keep yours
database/migrations/* merge=ours

# Seeders - manual merge
database/seeders/* merge=union

# Config - manual merge
config/* merge=union

# Project-specific docs
README.md merge=ours
```

#### Task 2: Create .starter/setup.sh
Script to initialize a new project from the starter.

```bash
#!/bin/bash
# .starter/setup.sh

set -e

echo "🚀 Setting up new project from Laravel Vue Starter..."
echo ""

# Get project details
read -p "New project name: " PROJECT_NAME
read -p "GitHub repo URL (e.g., git@github.com:org/project.git): " REPO_URL
read -p "Starter repo URL [git@github.com:your-org/laravel-vue-starter.git]: " STARTER_URL
STARTER_URL=${STARTER_URL:-git@github.com:your-org/laravel-vue-starter.git}

echo ""
echo "📋 Summary:"
echo "  Project: $PROJECT_NAME"
echo "  Repo: $REPO_URL"
echo "  Starter: $STARTER_URL"
echo ""
read -p "Continue? (y/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Setup cancelled"
    exit 0
fi

# Remove old git history
echo "🗑️  Removing starter git history..."
rm -rf .git

# Initialize new repo
echo "📦 Initializing new repository..."
git init
git add .
git commit -m "Initial commit from Laravel Vue Starter"

# Add remotes
echo "🔗 Adding git remotes..."
git remote add origin "$REPO_URL"
git remote add starter "$STARTER_URL"

# Create main branch
git branch -M main

echo ""
echo "✅ Git setup complete!"
echo ""
echo "📝 Next steps:"
echo "  1. Push to GitHub:"
echo "     git push -u origin main"
echo ""
echo "  2. Install dependencies:"
echo "     composer install"
echo "     npm install"
echo ""
echo "  3. Setup environment:"
echo "     cp .env.example .env"
echo "     php artisan key:generate"
echo ""
echo "  4. Setup database:"
echo "     php artisan migrate --seed"
echo ""
echo "  5. Start developing:"
echo "     composer run dev"
echo ""
echo "💡 To get updates from starter later:"
echo "   ./.starter/update.sh"
```

#### Task 3: Create .starter/update.sh
Script to fetch and merge updates from starter template.

```bash
#!/bin/bash
# .starter/update.sh

set -e

STARTER_REMOTE="starter"
STARTER_BRANCH="main"

echo "🔄 Laravel Vue Starter - Update Tool"
echo ""

# Check if starter remote exists
if ! git remote get-url $STARTER_REMOTE > /dev/null 2>&1; then
    echo "❌ Starter remote not found!"
    echo ""
    read -p "Add starter remote? (Y/n) " -n 1 -r
    echo

    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        read -p "Starter repo URL: " STARTER_URL
        git remote add $STARTER_REMOTE "$STARTER_URL"
        echo "✅ Starter remote added"
    else
        exit 1
    fi
fi

# Fetch latest
echo "📥 Fetching updates from starter..."
git fetch $STARTER_REMOTE

# Check if there are updates
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse $STARTER_REMOTE/$STARTER_BRANCH)

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "✅ Already up to date!"
    exit 0
fi

# Show changes
echo ""
echo "📋 Updates available in starter:"
echo ""
git log --oneline --graph --decorate HEAD..$STARTER_REMOTE/$STARTER_BRANCH --max-count=20

echo ""
echo "📊 Files that will be affected:"
git diff --name-status HEAD..$STARTER_REMOTE/$STARTER_BRANCH | head -30

echo ""
read -p "Continue with merge? (y/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "⏭️  Update cancelled"
    exit 0
fi

# Create backup branch
BACKUP_BRANCH="backup-before-update-$(date +%Y%m%d-%H%M%S)"
git branch $BACKUP_BRANCH
echo "💾 Created backup branch: $BACKUP_BRANCH"

# Stash any uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "💼 Stashing uncommitted changes..."
    git stash push -m "Auto-stash before starter update"
    STASHED=true
else
    STASHED=false
fi

# Merge
echo ""
echo "🔀 Merging starter updates..."
if git merge $STARTER_REMOTE/$STARTER_BRANCH --no-edit; then
    echo ""
    echo "✅ Merge successful!"

    # Restore stashed changes
    if [ "$STASHED" = true ]; then
        echo "📦 Restoring stashed changes..."
        git stash pop
    fi

    echo ""
    echo "📝 Post-update checklist:"
    echo "  [ ] composer install"
    echo "  [ ] npm install"
    echo "  [ ] npm run build"
    echo "  [ ] php artisan migrate"
    echo "  [ ] php artisan optimize:clear"
    echo "  [ ] php artisan test"
    echo "  [ ] npm run test"
    echo ""
    echo "💡 Backup branch available: $BACKUP_BRANCH"
else
    echo ""
    echo "⚠️  Merge conflicts detected!"
    echo ""
    echo "📝 Resolve conflicts:"
    echo "  1. Fix conflicts in files marked with <<<<<<"
    echo "  2. Run: git add ."
    echo "  3. Run: git commit"
    echo ""
    echo "🔧 Conflict resolution strategies:"
    echo "  Keep yours:    git checkout --ours path/to/file"
    echo "  Keep starter:  git checkout --theirs path/to/file"
    echo ""
    echo "❌ To abort merge:"
    echo "   git merge --abort"
    echo "   git checkout $BACKUP_BRANCH"
fi
```

#### Task 4: Create .starter/USAGE.md
Complete documentation for using the starter template.

```markdown
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
```

### Phase 2: Documentation and Setup

#### Task 5: Update README.md
Add starter template instructions to the main README.

#### Task 6: Create CHANGELOG.md
Track all changes to the starter template.

```markdown
# Changelog

All notable changes to the Laravel Vue Starter template will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project uses **date-based versioning** (YYYY.MM).

## [Unreleased]

### Added
- Initial starter template release

## [v2025.01] - 2025-01-15

### Added
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
- Resource base class with Fields, Filters, Actions
- ResourceController for automatic CRUD API
- ResourceService for business logic
- ResourceManager, ResourceTable, ResourceForm (Vue)
- Field types: Text, Select, Number, Boolean, Date, Image, etc.
- Filter types: Select, Boolean, Date Range
- Actions: Bulk Delete, Bulk Update, Export

### Infrastructure
- Service-oriented architecture
- Spatie Media Library integration
- Laravel Boost for performance
- Comprehensive seeder system
- GitHub template repository support
```

#### Task 7: Configure Git Merge Strategies
Setup git config for merge strategies.

```bash
# Run this in the starter repository
git config merge.ours.driver true
git config merge.union.driver true
```

### Phase 3: Testing and Release

#### Task 8: Tag Initial Version

```bash
# In starter repository
git tag -a v2025.01 -m "Initial Laravel Vue Starter release"
git push origin v2025.01
git push origin main
```

#### Task 9: Test Complete Workflow

Create a test project and verify:

1. ✅ Clone starter works
2. ✅ Setup script works
3. ✅ Can push to new repo
4. ✅ Can add custom features
5. ✅ Update script works
6. ✅ Merge preserves custom code
7. ✅ Dependencies install correctly

### Phase 4: Enable GitHub Template

#### Task 10: Configure GitHub Template Repository

1. Go to repository Settings
2. Check "Template repository"
3. Users can now click "Use this template"

**Alternative workflow with GitHub template:**

```bash
# 1. Click "Use this template" on GitHub
# 2. Create new repository
# 3. Clone it
git clone git@github.com:your-org/my-project.git
cd my-project

# 4. Add starter for updates
git remote add starter git@github.com:your-org/laravel-vue-starter.git

# 5. Done! Start developing
composer install
npm install
composer run dev
```

## File Structure

```
laravel-vue-starter/
├── .starter/
│   ├── setup.sh              # New project setup script
│   ├── update.sh             # Update from starter script
│   └── USAGE.md              # Usage documentation
├── .gitattributes            # Merge strategies
├── CHANGELOG.md              # Starter template changes
├── README.md                 # Updated with starter instructions
├── app/
│   ├── Resources/
│   │   ├── Resource.php      # CORE - updatable
│   │   ├── Fields/           # CORE - updatable
│   │   ├── Filters/          # CORE - updatable
│   │   ├── Actions/          # CORE - updatable
│   │   ├── UserResource.php  # EXAMPLE - protected
│   │   ├── RoleResource.php  # EXAMPLE - protected
│   │   └── ...
│   ├── Http/Controllers/Api/
│   │   └── ResourceController.php  # CORE - updatable
│   └── Services/
│       └── ResourceService.php     # CORE - updatable
├── resources/js/
│   ├── components/resource/  # CORE - updatable
│   └── services/
│       └── resourceService.js      # CORE - updatable
└── project_development_guidelines/  # Documentation
```

## Complete Workflows

### Workflow 1: Start New Project (GitHub Template)

```bash
# On GitHub: Click "Use this template"
# Clone your new repo
git clone git@github.com:org/my-project.git
cd my-project

# Add starter for updates
git remote add starter git@github.com:org/laravel-vue-starter.git

# Setup and start
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

### Workflow 2: Start New Project (Manual)

```bash
# Clone starter
git clone git@github.com:org/laravel-vue-starter.git my-project
cd my-project

# Run setup script
./.starter/setup.sh

# Follow prompts, then:
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

### Workflow 3: Update Existing Project

```bash
# In your project
./.starter/update.sh

# Or manually
git fetch starter
git merge starter/main

# Then
composer install
npm install
npm run build
php artisan migrate
php artisan test
```

## Benefits

✅ **Single command setup** - `.starter/setup.sh`
✅ **Single command updates** - `.starter/update.sh`
✅ **Protected custom code** - Via `.gitattributes`
✅ **Safe updates** - Automatic backups before merge
✅ **Clear documentation** - `.starter/USAGE.md`
✅ **Version control** - Date-based versions
✅ **No dependencies** - Just Git
✅ **No complexity** - No packages, no submodules
✅ **GitHub template** - One-click project creation
✅ **Team friendly** - Easy for anyone to use

## Success Criteria

- [ ] Can clone and setup new project in < 5 minutes
- [ ] Can receive updates without breaking custom code
- [ ] Clear documentation for all scenarios
- [ ] Automated scripts work flawlessly
- [ ] Backup system prevents data loss
- [ ] Merge conflicts are minimal and resolvable
- [ ] Team members can use without training

## Next Steps

Would you like me to:

1. **Implement all scripts and configs** (.gitattributes, setup.sh, update.sh, USAGE.md)
2. **Update README and CHANGELOG**
3. **Tag the current version** as v2025.01
4. **Test the complete workflow** with a dummy project

Let me know and I'll start implementing!
