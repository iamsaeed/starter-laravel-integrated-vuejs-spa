# Project-Specific Rules

These are critical project-specific rules that must be followed for this application.

## Email Templates

Always use the `EmailTemplate` model system for all emails.

**Location:** `app/Models/EmailTemplate.php`

**When creating new emails:**
1. Use the EmailTemplate model system
2. Create a migration to seed the `email_templates` table
3. Ensure templates are email-compatible and mobile-optimized
4. Migration ensures data reaches production

This ensures all email templates are versioned and deployed consistently.

## File & Media Operations

**Always use Spatie Media Library** for all file and image operations.

Never implement custom file upload or storage logic.

**Multi-Tenancy Context:**
Media uploads in tenant context must attach to tenant models (Task, Project, WorkspaceUserCache), NOT the User model.

Reference: Check existing models for `HasMedia` trait usage.

## Business Logic Location

**CRITICAL:** The business logic of the backend and frontend will always be stored in their respective services and nowhere else.

**Backend:** `app/Services/`
**Frontend:** `resources/js/services/`

This ensures we always have a single source of truth for business logic.

Never put business logic in controllers, components, or stores.

## Multi-Tenancy Database Rules

Always store tenant migrations in the `database/migrations/tenant/` folder.

**CRITICAL:** Make sure you are using the `workspace_users_cache` table for all tenant-related code in the backend, NOT the `users` table which is for the landlord tables.

**Database Context:**
- **Landlord:** `users`, `workspaces`, `workspace_user`
- **Tenant:** `workspace_users_cache`, `tasks`, `projects`

Always verify which database context you're in before choosing models.

Reference: https://tenancyforlaravel.com/docs for best practices.

## Testing Environment

Always use MySQL connection (as in `.env.testing`) for testing to replicate real production scenarios for everything.

Never use SQLite or other databases for testing if the production environment uses MySQL.

## Database Migrations

**No more changes to existing migrations.**

Any changes to the database or any seed data required for production must be added to new migrations.

**When modifying columns:**
Include ALL previous attributes or they'll be dropped and lost.

## AI Development

If you develop anything with the "Neuron AI" package (AI Agents, AI Workflows, AI Tools, AI Nodes) or tests related to this package, you should check the documentation at https://docs.neuron-ai.dev/

## Git Commits

**CRITICAL:** When creating a git commit, do not add "Generated with Claude Code" or "Co-Authored-By: Claude noreply@anthropic.com" type messages to the commit - never.

Keep commit messages clean and professional.

## Code Formatting

**MANDATORY:** Run `vendor/bin/pint --dirty` before finalizing any changes.

This ensures code matches the project's style conventions.
