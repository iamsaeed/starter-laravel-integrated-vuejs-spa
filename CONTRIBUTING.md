# Contributing to Laravel Vue Starter

Thank you for considering contributing to the Laravel Vue Starter template! We welcome contributions from the community.

## How to Contribute

### If You're Using This Template for Your Project

Even if you're building your own project with this template, you can still contribute improvements back to the template!

#### Setup Your Remotes

When you start a project from this template, you'll have:

```bash
git remote -v
origin    git@github.com:your-org/your-project.git  # Your project
starter   git@github.com:our-org/laravel-vue-starter.git  # The template
```

#### Contributing Workflow

1. **Fork the Template Repository**
   - Go to the starter template repository on GitHub
   - Click "Fork" to create your own copy

2. **Add Your Fork as a Remote**
   ```bash
   git remote add fork git@github.com:your-username/laravel-vue-starter.git
   ```

3. **Create a Feature Branch from starter/main**
   ```bash
   git fetch starter
   git checkout -b feature/new-field-type starter/main
   ```

4. **Make Your Changes**
   - Edit files in `app/Core/*` or `resources/js/core/*`
   - Write tests
   - Update documentation

5. **Commit and Push to Your Fork**
   ```bash
   git add .
   git commit -m "Add ColorPicker field type"
   git push fork feature/new-field-type
   ```

6. **Create a Pull Request**
   - Go to the original template repository on GitHub
   - Create a Pull Request from your fork's branch
   - Describe your changes clearly

## What Can You Contribute?

### ✅ Core System Improvements

**Backend Core (`app/Core/`):**
- New Field types (e.g., `ColorPicker.php`, `Slider.php`, `Rating.php`)
- New Filter types (e.g., `NumberRangeFilter.php`, `MultiSelectFilter.php`)
- New Action types (e.g., `ExportPdfAction.php`, `ArchiveAction.php`)
- Bug fixes in existing Fields, Filters, Actions
- Performance improvements in `ResourceService.php`
- Security improvements in `ResourceController.php`

**Frontend Core (`resources/js/core/`):**
- Improvements to `ResourceManager.vue`, `ResourceTable.vue`, `ResourceForm.vue`
- New field renderers in `FieldRenderer.vue`
- UI/UX enhancements
- Accessibility improvements
- Performance optimizations
- Bug fixes

**Other:**
- Documentation improvements
- Test coverage improvements
- CI/CD improvements
- Development tooling
- Starter scripts (`.starter/`)

### ❌ Not Suitable for PRs

These are project-specific and should not be contributed back:

- Changes to `app/Resources/UserResource.php`, `RoleResource.php`, etc. (example resources)
- Changes to `app/Services/AuthService.php` or other project services
- Changes to `app/Models/*` (project models)
- Changes to `resources/js/pages/*` (project pages)
- Changes to `resources/js/components/` (project-specific components)
- Project-specific features

## Development Guidelines

### Code Quality

- **PHP:** Run `vendor/bin/pint --dirty` before committing
- **JavaScript:** Follow existing code style
- **Tests:** Add tests for new features
- **Documentation:** Update docs for any changes

### Commit Messages

Use clear, descriptive commit messages:

```
Good:
- "Add ColorPicker field type with validation"
- "Fix BelongsToMany filter query bug"
- "Improve ResourceTable performance for large datasets"

Bad:
- "fix bug"
- "update code"
- "changes"
```

### Testing

Before submitting:

```bash
# Run backend tests
composer run test

# Run frontend tests
npm run test

# Build frontend
npm run build
```

All tests should pass.

## Pull Request Guidelines

### Before Submitting

- [ ] Code follows project style guidelines
- [ ] All tests pass
- [ ] Added tests for new features
- [ ] Updated documentation
- [ ] Ran code formatters (Pint for PHP)
- [ ] No breaking changes to existing APIs (unless discussed)

### PR Description

Include:

1. **What:** Brief description of changes
2. **Why:** Reason for the changes
3. **How:** Approach taken
4. **Testing:** How to test the changes
5. **Screenshots:** If UI changes

### Example PR Description

```markdown
## Add ColorPicker Field Type

### What
Adds a new `ColorPicker` field type for the Resource system.

### Why
Users frequently need to select colors for branding, themes, etc.
Currently, they have to use a Text field and validate manually.

### How
- Created `app/Core/Resources/Fields/ColorPicker.php`
- Added color validation
- Added frontend color picker component in FieldRenderer
- Uses HTML5 color input with fallback

### Testing
1. Add ColorPicker field to a resource
2. Verify color picker appears in forms
3. Test validation with invalid color codes
4. Test on different browsers

### Screenshots
[Attach screenshots]
```

## Types of Contributions

### Bug Fixes

Found a bug in the Core system? Please:

1. Check if it's already reported in Issues
2. Create an issue describing the bug
3. Submit a PR with the fix
4. Reference the issue in your PR

### New Features

Want to add a new feature to Core?

1. Open an issue to discuss the feature first
2. Get feedback from maintainers
3. Implement the feature
4. Submit PR with tests and docs

### Documentation

Documentation improvements are always welcome:

- Fix typos
- Clarify confusing sections
- Add examples
- Improve code comments

## Code of Conduct

- Be respectful and constructive
- Help others learn
- Focus on what is best for the community
- Show empathy towards other community members

## Questions?

- **Issues:** For bugs and feature requests
- **Discussions:** For questions and ideas
- **Email:** For security vulnerabilities

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Laravel Vue Starter! 🚀
