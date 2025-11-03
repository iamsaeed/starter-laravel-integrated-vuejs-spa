# E2E Tests with Playwright

This directory contains end-to-end tests for the application using Playwright.

## Setup

Tests are already configured. Playwright is installed as a dev dependency.

## Running Tests

```bash
# Run all E2E tests
npm run test:e2e

# Run with UI mode (interactive)
npm run test:e2e:ui

# Run in headed mode (see browser)
npm run test:e2e:headed

# Debug mode
npm run test:e2e:debug
```

## Test Structure

- `auth/` - Authentication flow tests
- `settings/` - Settings management tests (user and admin)
- `helpers/` - Reusable helper functions

## Prerequisites

Before running E2E tests:

1. Start the Laravel backend server
2. Ensure database is seeded with test data
3. Set `APP_URL` in `.env` or it will default to `http://127.0.0.1:8001`

## Test Coverage

**Auth Tests (11 tests)**
- Login with valid/invalid credentials
- Form validation
- Logout flow
- Protected routes
- Session persistence

**User Settings Tests (27 tests)**
- Page access and navigation
- Theme selection and preview
- Items per page configuration
- Localization settings (country, timezone, date format)
- Notification preferences
- Form state management (dirty state, unsaved warnings)
- Form validation
- Complete workflow
- Navigation guards

**Global Settings Tests (17 tests)**
- Admin access
- Application settings (name, URL, defaults)
- Security settings (email verification, 2FA, session lifetime)
- Email configuration
- Localization defaults
- Appearance defaults
- Complete configuration workflow
- Collapsible groups

**Total: 55 E2E Tests**

## Writing New Tests

Use the helpers in `helpers/` for common operations:

```javascript
import { login } from '../helpers/auth.js'
import { goToUserSettings, submitSettings } from '../helpers/settings.js'

test('my test', async ({ page }) => {
  await login(page)
  await goToUserSettings(page)
  // ... your test code
})
```

## CI/CD

E2E tests can be run in CI with:

```bash
npx playwright install --with-deps
npm run test:e2e
```

## Debugging

- Use `--debug` flag to step through tests
- Screenshots are captured on failure
- Videos are recorded on test failure
- Traces are captured for debugging
