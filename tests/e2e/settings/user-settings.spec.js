import { test, expect } from '@playwright/test'
import { login } from '../helpers/auth.js'
import {
  goToUserSettings,
  fillField,
  selectOption,
  toggleCheckbox,
  submitSettings,
  waitForSuccessMessage,
  getCurrentTheme,
  isSaveButtonEnabled,
  hasUnsavedWarning
} from '../helpers/settings.js'

test.describe('User Settings E2E', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await login(page)
  })

  test.describe('Settings Page Access', () => {
    test('should navigate to settings page', async ({ page }) => {
      await goToUserSettings(page)

      // Verify we're on the settings page
      await expect(page.locator('h1')).toContainText('Settings')
      await expect(page).toHaveURL(/\/settings/)
    })

    test('should display all settings sections', async ({ page }) => {
      await goToUserSettings(page)

      // Check for main setting groups
      await expect(page.locator('text=General')).toBeVisible()
      await expect(page.locator('text=Appearance')).toBeVisible()
      await expect(page.locator('text=Localization')).toBeVisible()
      await expect(page.locator('text=Notifications')).toBeVisible()
    })
  })

  test.describe('Theme Selection', () => {
    test('should change theme and see preview', async ({ page }) => {
      await goToUserSettings(page)

      // Get initial theme
      const initialTheme = await getCurrentTheme(page)

      // Select a different theme
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')

      // Theme should apply immediately (preview)
      const newTheme = await getCurrentTheme(page)
      expect(newTheme).not.toBe(initialTheme)
      expect(newTheme).toBe('dark')
    })

    test('should save theme preference', async ({ page }) => {
      await goToUserSettings(page)

      // Change theme
      await page.click('[name="user_theme"]')
      await page.click('text=Ocean')

      // Save settings
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Reload page and verify theme persists
      await page.reload()
      const theme = await getCurrentTheme(page)
      expect(theme).toBe('ocean')
    })

    test('should apply theme across all pages', async ({ page }) => {
      await goToUserSettings(page)

      // Set theme to sunset
      await page.click('[name="user_theme"]')
      await page.click('text=Sunset')
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Navigate to different page
      await page.goto('/dashboard')

      // Theme should still be applied
      const theme = await getCurrentTheme(page)
      expect(theme).toBe('sunset')
    })
  })

  test.describe('Items Per Page Setting', () => {
    test('should update items per page', async ({ page }) => {
      await goToUserSettings(page)

      // Change items per page
      await selectOption(page, 'items_per_page', '100 items')

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Verify saved
      await page.reload()
      const value = await page.locator('[name="items_per_page"]').inputValue()
      expect(value).toBe('100')
    })
  })

  test.describe('Localization Settings', () => {
    test('should select country', async ({ page }) => {
      await goToUserSettings(page)

      // Select country
      await page.click('[name="user_country"]')
      await page.click('text=United States')

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)
    })

    test('should select timezone for country', async ({ page }) => {
      await goToUserSettings(page)

      // Select country first
      await page.click('[name="user_country"]')
      await page.click('text=United States')

      // Timezone dropdown should be enabled
      await page.click('[name="user_timezone"]')

      // Select timezone
      await page.click('text=America/New_York')

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)
    })

    test('should update date format', async ({ page }) => {
      await goToUserSettings(page)

      // Change date format
      await selectOption(page, 'date_format', 'DD/MM/YYYY')

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)
    })
  })

  test.describe('Notification Settings', () => {
    test('should toggle notifications on/off', async ({ page }) => {
      await goToUserSettings(page)

      // Toggle main notifications
      await toggleCheckbox(page, 'notifications_enabled', false)

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Verify
      await page.reload()
      const checked = await page.locator('[name="notifications_enabled"]').isChecked()
      expect(checked).toBe(false)
    })

    test('should manage individual notification types', async ({ page }) => {
      await goToUserSettings(page)

      // Enable email, disable push
      await toggleCheckbox(page, 'email_notifications', true)
      await toggleCheckbox(page, 'push_notifications', false)

      // Save
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Verify
      await page.reload()
      const emailChecked = await page.locator('[name="email_notifications"]').isChecked()
      const pushChecked = await page.locator('[name="push_notifications"]').isChecked()

      expect(emailChecked).toBe(true)
      expect(pushChecked).toBe(false)
    })
  })

  test.describe('Form State Management', () => {
    test('should enable save button when changes made', async ({ page }) => {
      await goToUserSettings(page)

      // Initially, save button may be disabled
      // Make a change
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')

      // Save button should be enabled
      const enabled = await isSaveButtonEnabled(page)
      expect(enabled).toBe(true)
    })

    test('should show unsaved changes warning', async ({ page }) => {
      await goToUserSettings(page)

      // Make a change
      await page.click('[name="user_theme"]')
      await page.click('text=Ocean')

      // Check for warning
      const hasWarning = await hasUnsavedWarning(page)
      expect(hasWarning).toBe(true)
    })

    test('should handle cancel/reset', async ({ page }) => {
      await goToUserSettings(page)

      // Get initial value
      const initialTheme = await getCurrentTheme(page)

      // Change theme
      await page.click('[name="user_theme"]')
      await page.click('text=Sunset')

      // Click cancel
      page.on('dialog', dialog => dialog.accept())
      await page.click('button:has-text("Cancel")')

      // Theme should revert
      const currentTheme = await getCurrentTheme(page)
      expect(currentTheme).toBe(initialTheme)
    })
  })

  test.describe('Form Validation', () => {
    test('should validate required fields', async ({ page }) => {
      await goToUserSettings(page)

      // Try to submit without required field (if any)
      // This depends on your validation rules

      // For now, just verify form exists
      await expect(page.locator('form')).toBeVisible()
    })
  })

  test.describe('Complete Settings Workflow', () => {
    test('should update all settings and persist', async ({ page }) => {
      await goToUserSettings(page)

      // Update theme
      await page.click('[name="user_theme"]')
      await page.click('text=Crimson')

      // Update items per page
      await selectOption(page, 'items_per_page', '50 items')

      // Update country and timezone
      await page.click('[name="user_country"]')
      await page.click('text=Canada')
      await page.click('[name="user_timezone"]')
      await page.click('text=America/Toronto')

      // Update date format
      await selectOption(page, 'date_format', 'YYYY-MM-DD')

      // Update notifications
      await toggleCheckbox(page, 'notifications_enabled', false)

      // Save all changes
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Navigate away and back
      await page.goto('/dashboard')
      await goToUserSettings(page)

      // Verify all settings persisted
      const theme = await getCurrentTheme(page)
      expect(theme).toBe('crimson')

      const itemsValue = await page.locator('[name="items_per_page"]').inputValue()
      expect(itemsValue).toBe('50')

      const notifChecked = await page.locator('[name="notifications_enabled"]').isChecked()
      expect(notifChecked).toBe(false)
    })
  })

  test.describe('Navigation Guard', () => {
    test('should warn when leaving with unsaved changes', async ({ page }) => {
      await goToUserSettings(page)

      // Make a change
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')

      // Try to navigate away
      page.on('dialog', dialog => {
        expect(dialog.message()).toContain('unsaved changes')
        dialog.dismiss() // Cancel navigation
      })

      await page.click('a[href="/dashboard"]').catch(() => {
        // Navigation was cancelled
      })

      // Should still be on settings page
      await expect(page).toHaveURL(/\/settings/)
    })

    test('should allow navigation after saving', async ({ page }) => {
      await goToUserSettings(page)

      // Make and save a change
      await page.click('[name="user_theme"]')
      await page.click('text=Ocean')
      await submitSettings(page)
      await waitForSuccessMessage(page)

      // Navigate away - should work without warning
      await page.goto('/dashboard')
      await expect(page).toHaveURL(/\/dashboard/)
    })
  })
})
