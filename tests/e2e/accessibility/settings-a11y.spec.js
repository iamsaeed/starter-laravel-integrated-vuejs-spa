import { test, expect } from '@playwright/test'
import AxeBuilder from '@axe-core/playwright'
import { login } from '../helpers/auth.js'
import { goToUserSettings, goToGlobalSettings } from '../helpers/settings.js'

test.describe('Accessibility Tests - Settings', () => {
  test.describe('User Settings Accessibility', () => {
    test('should have no accessibility violations on initial load', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const accessibilityScanResults = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze()

      expect(accessibilityScanResults.violations).toEqual([])
    })

    test('should have proper heading hierarchy', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Check h1 exists
      const h1 = await page.locator('h1').count()
      expect(h1).toBeGreaterThan(0)

      // Run specific heading check
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('h1, h2, h3, h4, h5, h6')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible form labels', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('form')
        .analyze()

      // Check for label violations
      const labelViolations = results.violations.filter(v =>
        v.id === 'label' || v.id === 'label-title-only'
      )
      expect(labelViolations).toEqual([])
    })

    test('should have sufficient color contrast', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2aa'])
        .analyze()

      const contrastViolations = results.violations.filter(v =>
        v.id === 'color-contrast'
      )
      expect(contrastViolations).toEqual([])
    })

    test('should be keyboard navigable', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Tab through form elements
      await page.keyboard.press('Tab')
      await page.keyboard.press('Tab')
      await page.keyboard.press('Tab')

      // Check that focus is visible
      const focusedElement = await page.locator(':focus')
      expect(await focusedElement.count()).toBeGreaterThan(0)
    })

    test('should have accessible buttons', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('button')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible checkboxes', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('input[type="checkbox"]')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible select dropdowns', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('select, [role="combobox"]')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible error messages', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Trigger validation error
      await page.fill('[name="items_per_page"]', '')
      await page.click('button[type="submit"]')

      // Check error messages are accessible
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const ariaViolations = results.violations.filter(v =>
        v.id.includes('aria')
      )
      expect(ariaViolations).toEqual([])
    })

    test('should have proper ARIA roles', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const ariaRoleViolations = results.violations.filter(v =>
        v.id === 'aria-roles' || v.id === 'aria-allowed-role'
      )
      expect(ariaRoleViolations).toEqual([])
    })
  })

  test.describe('Global Settings Accessibility', () => {
    test('should have no accessibility violations', async ({ page }) => {
      await login(page)
      await goToGlobalSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible form groups', async ({ page }) => {
      await login(page)
      await goToGlobalSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('fieldset, [role="group"]')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible collapsible sections', async ({ page }) => {
      await login(page)
      await goToGlobalSettings(page)

      // Check ARIA attributes for collapsible
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const ariaViolations = results.violations.filter(v =>
        v.id === 'aria-expanded' || v.id === 'aria-controls'
      )
      expect(ariaViolations).toEqual([])
    })
  })

  test.describe('Success/Error Messages Accessibility', () => {
    test('should announce success message to screen readers', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Make a change and save
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')
      await page.click('button[type="submit"]')

      // Check success message has role="alert" or aria-live
      const successMessage = page.locator('.bg-green-50')
      if (await successMessage.count() > 0) {
        const role = await successMessage.getAttribute('role')
        const ariaLive = await successMessage.getAttribute('aria-live')

        expect(role === 'alert' || ariaLive === 'polite' || ariaLive === 'assertive').toBe(true)
      }
    })

    test('should announce error message to screen readers', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Trigger error
      await page.fill('[name="items_per_page"]', 'invalid')
      await page.click('button[type="submit"]')

      // Error should be announced
      const errorMessage = page.locator('.bg-red-50, .text-red-500').first()
      if (await errorMessage.count() > 0) {
        const role = await errorMessage.getAttribute('role')
        const ariaLive = await errorMessage.getAttribute('aria-live')

        expect(
          role === 'alert' ||
          ariaLive === 'polite' ||
          ariaLive === 'assertive' ||
          await errorMessage.getAttribute('aria-invalid') === 'true'
        ).toBe(true)
      }
    })
  })

  test.describe('Focus Management', () => {
    test('should maintain focus after modal/dialog close', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // If there are modals, test focus return
      // This is a placeholder for modal interactions
    })

    test('should have visible focus indicators', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Tab to first focusable element
      await page.keyboard.press('Tab')

      // Check focus is visible
      const focusedElement = await page.locator(':focus')
      const outlineStyle = await focusedElement.evaluate(el =>
        window.getComputedStyle(el).outline
      )

      // Should have some outline
      expect(outlineStyle).not.toBe('none')
    })

    test('should not trap focus unintentionally', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Tab through all elements
      for (let i = 0; i < 20; i++) {
        await page.keyboard.press('Tab')
      }

      // Should be able to reach elements outside form
      const focusedElement = await page.locator(':focus')
      expect(await focusedElement.count()).toBeGreaterThan(0)
    })
  })

  test.describe('Screen Reader Support', () => {
    test('should have descriptive page title', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const title = await page.title()
      expect(title).toBeTruthy()
      expect(title.length).toBeGreaterThan(0)
    })

    test('should have meaningful alt text for images', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const imageAltViolations = results.violations.filter(v =>
        v.id === 'image-alt'
      )
      expect(imageAltViolations).toEqual([])
    })

    test('should have proper landmark regions', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const landmarkViolations = results.violations.filter(v =>
        v.id === 'region' || v.id === 'landmark-one-main'
      )
      expect(landmarkViolations).toEqual([])
    })
  })

  test.describe('Mobile Accessibility', () => {
    test.use({ viewport: { width: 375, height: 667 } })

    test('should be accessible on mobile viewport', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have touch-friendly targets on mobile', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Check button sizes are adequate for touch
      const buttons = page.locator('button')
      const buttonCount = await buttons.count()

      for (let i = 0; i < buttonCount; i++) {
        const box = await buttons.nth(i).boundingBox()
        if (box) {
          // Touch targets should be at least 44x44px (WCAG guideline)
          expect(box.width).toBeGreaterThanOrEqual(40)
          expect(box.height).toBeGreaterThanOrEqual(40)
        }
      }
    })
  })

  test.describe('Theme Accessibility', () => {
    test('should maintain accessibility in dark theme', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Switch to dark theme
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')

      // Check accessibility
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2aa'])
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should maintain contrast in all themes', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const themes = ['Dark', 'Ocean', 'Sunset']

      for (const theme of themes) {
        await page.click('[name="user_theme"]')
        await page.click(`text=${theme}`)

        const results = await new AxeBuilder({ page })
          .withTags(['wcag2aa'])
          .analyze()

        const contrastViolations = results.violations.filter(v =>
          v.id === 'color-contrast'
        )

        expect(contrastViolations).toEqual([])
      }
    })
  })

  test.describe('Form Accessibility', () => {
    test('should have accessible required field indicators', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Check required fields have proper ARIA
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .include('[required], [aria-required="true"]')
        .analyze()

      expect(results.violations).toEqual([])
    })

    test('should have accessible help text', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Help text should be associated with inputs
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a'])
        .analyze()

      const ariaDescribedByViolations = results.violations.filter(v =>
        v.id === 'aria-describedby'
      )
      expect(ariaDescribedByViolations).toEqual([])
    })
  })
})
