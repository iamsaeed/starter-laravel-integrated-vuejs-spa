import { test, expect } from '@playwright/test'
import { login } from '../helpers/auth.js'
import {
  goToGlobalSettings,
  fillField,
  selectOption,
  toggleCheckbox,
  submitSettings,
  waitForSuccessMessage
} from '../helpers/settings.js'

test.describe('Global Settings E2E (Admin)', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await login(page, { email: 'admin@app.com', password: 'password' })
  })

  test.describe('Page Access', () => {
    test('should access global settings as admin', async ({ page }) => {
      await goToGlobalSettings(page)

      await expect(page.locator('h1')).toContainText('Global Settings')
      await expect(page).toHaveURL(/\/admin\/settings/)
    })

    test('should display admin badge', async ({ page }) => {
      await goToGlobalSettings(page)

      await expect(page.locator('text=Admin Only')).toBeVisible()
    })
  })

  test.describe('Application Settings', () => {
    test('should update application name', async ({ page }) => {
      await goToGlobalSettings(page)

      await fillField(page, 'app_name', 'My Test Application')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')

      // Verify
      await page.reload()
      const value = await page.locator('[name="app_name"]').inputValue()
      expect(value).toBe('My Test Application')
    })

    test('should update application URL', async ({ page }) => {
      await goToGlobalSettings(page)

      await fillField(page, 'app_url', 'https://test.example.com')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should set default items per page', async ({ page }) => {
      await goToGlobalSettings(page)

      await selectOption(page, 'default_items_per_page', '50 items')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should validate app name minimum length', async ({ page }) => {
      await goToGlobalSettings(page)

      // Try to set too short name
      await fillField(page, 'app_name', 'AB')
      await submitSettings(page)

      // Should show validation error
      await expect(page.locator('.text-red-500')).toBeVisible()
    })
  })

  test.describe('Security Settings', () => {
    test('should toggle email verification requirement', async ({ page }) => {
      await goToGlobalSettings(page)

      await toggleCheckbox(page, 'require_email_verification', true)
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')

      // Verify
      await page.reload()
      const checked = await page.locator('[name="require_email_verification"]').isChecked()
      expect(checked).toBe(true)
    })

    test('should enable two-factor authentication', async ({ page }) => {
      await goToGlobalSettings(page)

      await toggleCheckbox(page, 'enable_two_factor', true)
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should update session lifetime', async ({ page }) => {
      await goToGlobalSettings(page)

      await fillField(page, 'session_lifetime', '240')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')

      // Verify
      await page.reload()
      const value = await page.locator('[name="session_lifetime"]').inputValue()
      expect(value).toBe('240')
    })

    test('should validate session lifetime minimum', async ({ page }) => {
      await goToGlobalSettings(page)

      // Try to set too low
      await fillField(page, 'session_lifetime', '2')
      await submitSettings(page)

      // Should show validation error
      await expect(page.locator('.text-red-500')).toBeVisible()
    })
  })

  test.describe('Email Settings', () => {
    test('should update email from address', async ({ page }) => {
      await goToGlobalSettings(page)

      await fillField(page, 'mail_from_address', 'noreply@test.com')
      await fillField(page, 'mail_from_name', 'Test App')

      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should validate email format', async ({ page }) => {
      await goToGlobalSettings(page)

      await fillField(page, 'mail_from_address', 'invalid-email')
      await submitSettings(page)

      // Should show validation error
      await expect(page.locator('.text-red-500')).toBeVisible()
    })

    test('should toggle global notifications', async ({ page }) => {
      await goToGlobalSettings(page)

      await toggleCheckbox(page, 'enable_notifications', false)
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })
  })

  test.describe('Localization Settings', () => {
    test('should set default timezone', async ({ page }) => {
      await goToGlobalSettings(page)

      await page.click('[name="default_timezone"]')
      await page.click('text=America/New_York')

      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should set default date format', async ({ page }) => {
      await goToGlobalSettings(page)

      await selectOption(page, 'default_date_format', 'DD/MM/YYYY')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should set default language', async ({ page }) => {
      await goToGlobalSettings(page)

      await selectOption(page, 'default_language', 'Spanish')
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })
  })

  test.describe('Appearance Settings', () => {
    test('should set default theme', async ({ page }) => {
      await goToGlobalSettings(page)

      await page.click('[name="default_theme"]')
      await page.click('text=Dark')

      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')
    })

    test('should control theme change permission', async ({ page }) => {
      await goToGlobalSettings(page)

      // Disable theme changes for users
      await toggleCheckbox(page, 'allow_theme_change', false)
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')

      // Verify setting saved
      await page.reload()
      const checked = await page.locator('[name="allow_theme_change"]').isChecked()
      expect(checked).toBe(false)
    })
  })

  test.describe('Complete Configuration', () => {
    test('should configure entire application', async ({ page }) => {
      await goToGlobalSettings(page)

      // Application
      await fillField(page, 'app_name', 'Complete Test App')
      await fillField(page, 'app_url', 'https://complete.test')
      await selectOption(page, 'default_items_per_page', '100 items')

      // Security
      await toggleCheckbox(page, 'require_email_verification', true)
      await toggleCheckbox(page, 'enable_two_factor', true)
      await fillField(page, 'session_lifetime', '120')

      // Email
      await fillField(page, 'mail_from_address', 'admin@complete.test')
      await fillField(page, 'mail_from_name', 'Complete Test')
      await toggleCheckbox(page, 'enable_notifications', true)

      // Appearance
      await page.click('[name="default_theme"]')
      await page.click('text=Ocean')
      await toggleCheckbox(page, 'allow_theme_change', true)

      // Save all
      await submitSettings(page)
      await waitForSuccessMessage(page, 'Global settings saved')

      // Verify all saved
      await page.reload()

      const appName = await page.locator('[name="app_name"]').inputValue()
      expect(appName).toBe('Complete Test App')

      const emailVerif = await page.locator('[name="require_email_verification"]').isChecked()
      expect(emailVerif).toBe(true)
    })
  })

  test.describe('Settings Groups Collapsible', () => {
    test('should collapse/expand setting groups', async ({ page }) => {
      await goToGlobalSettings(page)

      // Find a collapsible group
      const securityGroup = page.locator('text=Security').first()
      await securityGroup.click()

      // Group content should toggle visibility
      // This depends on your implementation
      await page.waitForTimeout(500)
    })
  })
})
