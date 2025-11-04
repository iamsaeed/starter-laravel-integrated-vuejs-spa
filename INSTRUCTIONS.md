# Getting Started with Laravel Vue Starter

Simple instructions for using this starter template for your project.

## 🚀 Starting Your Project (3 Steps)

### Option 1: Using GitHub Template (Easiest)

**Step 1:** Click "Use this template" button on GitHub
- Creates a new repository under your account
- No git history from the starter

**Step 2:** Clone your new repository
```bash
git clone git@github.com:YOUR-USERNAME/your-project-name.git
cd your-project-name
```

**Step 3:** Add the starter as a remote (for future updates)
```bash
git remote add starter git@github.com:STARTER-ORG/laravel-vue-starter.git
```

Done! Now install and run:
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

---

### Option 2: Clone and Setup Script

**Step 1:** Clone the starter
```bash
git clone git@github.com:STARTER-ORG/laravel-vue-starter.git my-project
cd my-project
```

**Step 2:** Run setup script
```bash
chmod +x .starter/setup.sh
./.starter/setup.sh
```

The script will ask you for:
- Your project name
- Your GitHub repository URL
- Then it sets everything up automatically

**Step 3:** Push to your repository
```bash
git push -u origin main
```

---

## 🔄 Keeping Your Project Updated (2 Steps)

The starter template gets improvements over time. Here's how to update your project:

### Step 1: Fetch updates from starter
```bash
git fetch starter
```

### Step 2: Run the update script
```bash
./.starter/update.sh
```

The script will:
- Show you what changed
- Create a backup branch automatically
- Merge updates safely
- Protect your custom code

**What gets updated:**
- ✅ Core system (`app/Core/*`, `resources/js/core/*`) - automatically updated
- ✅ Bug fixes and new features in the framework

**What stays yours:**
- ✅ Your resources (`app/Resources/*`)
- ✅ Your models, services, controllers
- ✅ Your Vue pages and components
- ✅ Your migrations and database

---

## 🐛 Submitting Bug Fixes or Improvements (5 Steps)

Found a bug in the Core system? Want to add a new feature? Here's how to contribute:

### Step 1: Fork the starter template on GitHub
- Go to the starter repository on GitHub
- Click "Fork" button
- Now you have your own copy of the starter

### Step 2: Add your fork as a remote
```bash
git remote add fork git@github.com:YOUR-USERNAME/laravel-vue-starter.git
```

You now have 3 remotes:
- `origin` - Your project repository
- `starter` - The original starter template
- `fork` - Your fork of the starter

### Step 3: Create a branch from starter/main
```bash
# Fetch latest from starter
git fetch starter

# Create a new branch from starter/main
git checkout -b fix-bug-in-text-field starter/main
```

### Step 4: Make your changes and push to your fork
```bash
# Fix the bug or add the feature
# Edit files in app/Core/* or resources/js/core/*
vim app/Core/Resources/Fields/Text.php

# Commit your changes
git add app/Core/Resources/Fields/Text.php
git commit -m "Fix validation bug in Text field"

# Push to YOUR fork
git push fork fix-bug-in-text-field
```

### Step 5: Create Pull Request on GitHub
- Go to the **original starter repository** on GitHub
- Click "Pull requests" → "New pull request"
- Click "compare across forks"
- Select your fork and your branch
- Write a clear description of your changes
- Submit the PR!

**What to include in your PR:**
- What the bug was or what feature you added
- Why it's needed
- How to test it
- Screenshots (if UI changes)

---

## 📂 What Can You Contribute?

### ✅ Good Contributions

**New Field Types:**
- `ColorPicker.php` - Color picker field
- `Slider.php` - Slider for numbers
- `Rating.php` - Star rating field
- `RichText.php` - Rich text editor field

**New Filter Types:**
- `NumberRangeFilter.php` - Filter by number range
- `MultiSelectFilter.php` - Multiple selection filter

**New Action Types:**
- `ExportPdfAction.php` - Export to PDF
- `ArchiveAction.php` - Archive records
- `DuplicateAction.php` - Duplicate records

**Bug Fixes:**
- Fix validation issues
- Fix performance problems
- Fix security issues

**Improvements:**
- Better error messages
- Improved UI/UX
- Performance optimizations
- Documentation fixes

### ❌ Don't Contribute These

These are project-specific and should stay in your project:
- Your custom resources (`app/Resources/UserResource.php`, etc.)
- Your services (`app/Services/AuthService.php`, etc.)
- Your models, migrations, seeders
- Your Vue pages and components
- Project-specific features

---

## 🗂️ Understanding the Structure

### Core System (UPDATABLE)
These folders get updated when you run `.starter/update.sh`:

```
app/Core/                      # Backend framework code
├── Resources/
│   ├── Resource.php          # Base class
│   ├── Fields/               # All field types
│   ├── Filters/              # All filter types
│   └── Actions/              # All action types
├── Services/
│   └── ResourceService.php
└── Http/Controllers/
    └── ResourceController.php

resources/js/core/             # Frontend framework code
├── components/resource/       # ResourceManager, ResourceTable, etc.
├── services/                  # resourceService.js
└── composables/               # Core composables
```

**Rule:** NEVER modify files in these folders - they get overwritten during updates.

### Your Project (PROTECTED)
These folders are YOURS and never get overwritten:

```
app/
├── Resources/                 # YOUR resources
├── Services/                  # YOUR services
├── Models/                    # YOUR models
└── Http/Controllers/          # YOUR controllers

resources/js/
├── components/                # YOUR components
├── pages/                     # YOUR pages
├── services/                  # YOUR services
└── stores/                    # YOUR stores
```

**Rule:** This is your code. Modify freely. Protected during updates.

---

## 🔍 Quick Reference

### Common Commands

**Start your project:**
```bash
composer run dev              # Start all services
```

**Update from starter:**
```bash
./.starter/update.sh          # Get latest updates
```

**Contribute to starter:**
```bash
git fetch starter
git checkout -b my-feature starter/main
# Make changes to app/Core/* or resources/js/core/*
git push fork my-feature
# Create PR on GitHub
```

**Check your remotes:**
```bash
git remote -v
```

You should see:
- `origin` - Your project
- `starter` - Original starter template
- `fork` - (Optional) Your fork of the starter

---

## 💡 Tips

1. **Update regularly** - Run `.starter/update.sh` monthly to get bug fixes and improvements

2. **Never modify Core** - Don't edit files in `app/Core/*` or `resources/js/core/*` - they'll be overwritten

3. **Extend, don't modify** - Create new resources in `app/Resources/`, not in `app/Core/`

4. **Test before contributing** - Make sure your PR includes tests and documentation

5. **Use the backup** - Update script creates a backup branch before merging. Use it if things go wrong:
   ```bash
   git checkout backup-before-update-YYYYMMDD-HHMMSS
   ```

---

## 🆘 Troubleshooting

**Problem:** "Remote 'starter' not found"
```bash
# Solution: Add the starter remote
git remote add starter git@github.com:STARTER-ORG/laravel-vue-starter.git
```

**Problem:** "Merge conflicts during update"
```bash
# Solution 1: Keep your version
git checkout --ours path/to/file
git add path/to/file

# Solution 2: Keep starter version
git checkout --theirs path/to/file
git add path/to/file

# Solution 3: Abort and try again
git merge --abort
```

**Problem:** "I modified Core files and now updates fail"
```bash
# Solution: Stash your changes, update, then reapply
git stash
./.starter/update.sh
# Then create your changes as new field types instead of modifying Core
```

---

## 📚 More Documentation

- **Detailed usage:** [.starter/USAGE.md](.starter/USAGE.md)
- **Contributing guidelines:** [CONTRIBUTING.md](CONTRIBUTING.md)
- **Backend development:** [project_development_guidelines/backend.md](project_development_guidelines/backend.md)
- **Frontend development:** [project_development_guidelines/frontend.md](project_development_guidelines/frontend.md)
- **Resource system:** [project_development_guidelines/resource-crud-system.md](project_development_guidelines/resource-crud-system.md)

---

## 🎉 Ready to Start!

You now know how to:
- ✅ Start your project from the template
- ✅ Keep it updated with the latest improvements
- ✅ Contribute bug fixes and features back

Happy coding! 🚀
