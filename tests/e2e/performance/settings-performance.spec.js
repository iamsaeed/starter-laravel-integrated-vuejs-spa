import { test, expect } from '@playwright/test'
import { login } from '../helpers/auth.js'
import { goToUserSettings, goToGlobalSettings } from '../helpers/settings.js'

test.describe('Performance Tests - Settings', () => {
  test.describe('Page Load Performance', () => {
    test('should load user settings page within acceptable time', async ({ page }) => {
      await login(page)

      const startTime = Date.now()
      await goToUserSettings(page)
      const endTime = Date.now()

      const loadTime = endTime - startTime

      // Should load within 2 seconds
      expect(loadTime).toBeLessThan(2000)
    })

    test('should load global settings page within acceptable time', async ({ page }) => {
      await login(page)

      const startTime = Date.now()
      await goToGlobalSettings(page)
      const endTime = Date.now()

      const loadTime = endTime - startTime

      // Should load within 2 seconds
      expect(loadTime).toBeLessThan(2000)
    })

    test('should have good Core Web Vitals', async ({ page }) => {
      await login(page)

      // Collect performance metrics
      await page.goto('/settings')

      const metrics = await page.evaluate(() => {
        const perfData = window.performance.getEntriesByType('navigation')[0]
        return {
          domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
          loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
          domInteractive: perfData.domInteractive - perfData.fetchStart
        }
      })

      // DOM Interactive should be under 3 seconds
      expect(metrics.domInteractive).toBeLessThan(3000)
    })
  })

  test.describe('Interaction Performance', () => {
    test('should handle theme changes quickly', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const startTime = Date.now()

      // Change theme
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')

      // Theme should apply immediately
      await page.waitForFunction(
        () => document.documentElement.className.includes('theme-dark'),
        { timeout: 500 }
      )

      const endTime = Date.now()
      const changeTime = endTime - startTime

      // Theme change should be instant (< 500ms)
      expect(changeTime).toBeLessThan(500)
    })

    test('should handle form submission quickly', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Make a small change
      await page.click('[name="user_theme"]')
      await page.click('text=Ocean')

      const startTime = Date.now()

      // Submit
      await page.click('button[type="submit"]')

      // Wait for success message
      await page.waitForSelector('.bg-green-50', { timeout: 2000 })

      const endTime = Date.now()
      const submitTime = endTime - startTime

      // Submission should complete within 2 seconds
      expect(submitTime).toBeLessThan(2000)
    })

    test('should handle multiple rapid changes', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const startTime = Date.now()

      // Make multiple rapid changes
      await page.click('[name="user_theme"]')
      await page.click('text=Dark')
      await page.waitForTimeout(100)

      await page.click('[name="user_theme"]')
      await page.click('text=Ocean')
      await page.waitForTimeout(100)

      await page.click('[name="user_theme"]')
      await page.click('text=Sunset')

      const endTime = Date.now()
      const totalTime = endTime - startTime

      // Multiple changes should still be fast
      expect(totalTime).toBeLessThan(1000)
    })
  })

  test.describe('Resource Loading', () => {
    test('should not load excessive JavaScript', async ({ page }) => {
      await login(page)

      // Navigate and collect resource metrics
      const resources = []
      page.on('response', response => {
        if (response.url().includes('.js')) {
          resources.push({
            url: response.url(),
            size: response.headers()['content-length']
          })
        }
      })

      await goToUserSettings(page)
      await page.waitForTimeout(1000)

      // Check total JS size
      const totalJsSize = resources.reduce((sum, r) => {
        return sum + (parseInt(r.size) || 0)
      }, 0)

      // Total JS should be reasonable (< 1MB)
      expect(totalJsSize).toBeLessThan(1024 * 1024)
    })

    test('should load CSS efficiently', async ({ page }) => {
      await login(page)

      const cssResources = []
      page.on('response', response => {
        if (response.url().includes('.css')) {
          cssResources.push(response)
        }
      })

      await goToUserSettings(page)

      // CSS files should load quickly
      for (const response of cssResources) {
        const timing = await response.timing()
        if (timing) {
          expect(timing.responseEnd).toBeLessThan(1000)
        }
      }
    })

    test('should not make excessive API calls', async ({ page }) => {
      await login(page)

      const apiCalls = []
      page.on('response', response => {
        if (response.url().includes('/api/')) {
          apiCalls.push(response.url())
        }
      })

      await goToUserSettings(page)
      await page.waitForTimeout(1000)

      // Should make a reasonable number of API calls
      expect(apiCalls.length).toBeLessThan(10)
    })
  })

  test.describe('Memory Usage', () => {
    test('should not leak memory on theme changes', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Get initial metrics
      const initialMetrics = await page.metrics()

      // Change theme multiple times
      for (let i = 0; i < 5; i++) {
        await page.click('[name="user_theme"]')
        await page.click('text=Dark')
        await page.waitForTimeout(100)

        await page.click('[name="user_theme"]')
        await page.click('text=Ocean')
        await page.waitForTimeout(100)
      }

      // Get final metrics
      const finalMetrics = await page.metrics()

      // Memory shouldn't grow excessively
      const memoryGrowth = finalMetrics.JSHeapUsedSize - initialMetrics.JSHeapUsedSize
      const growthMB = memoryGrowth / (1024 * 1024)

      // Memory growth should be reasonable (< 10MB)
      expect(growthMB).toBeLessThan(10)
    })

    test('should clean up on page navigation', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      const settingsMetrics = await page.metrics()

      // Navigate away
      await page.goto('/dashboard')

      const dashboardMetrics = await page.metrics()

      // Event listeners should be cleaned up
      // JSHeapUsedSize shouldn't grow significantly
      const diff = dashboardMetrics.JSHeapUsedSize - settingsMetrics.JSHeapUsedSize
      const diffMB = Math.abs(diff) / (1024 * 1024)

      expect(diffMB).toBeLessThan(5)
    })
  })

  test.describe('Rendering Performance', () => {
    test('should render form efficiently', async ({ page }) => {
      await login(page)

      await page.evaluate(() => performance.mark('settings-start'))

      await goToUserSettings(page)

      const paintTiming = await page.evaluate(() => {
        performance.mark('settings-end')
        performance.measure('settings-render', 'settings-start', 'settings-end')

        const measure = performance.getEntriesByName('settings-render')[0]
        return measure.duration
      })

      // Rendering should be fast
      expect(paintTiming).toBeLessThan(1000)
    })

    test('should handle scrolling smoothly', async ({ page }) => {
      await login(page)
      await goToUserSettings(page)

      // Measure scroll performance
      const startTime = Date.now()

      await page.evaluate(() => {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })
      })

      await page.waitForTimeout(500)

      const endTime = Date.now()
      const scrollTime = endTime - startTime

      // Scrolling should be smooth
      expect(scrollTime).toBeLessThan(1000)
    })
  })

  test.describe('Network Performance', () => {
    test('should cache static assets', async ({ page }) => {
      await login(page)

      // First load
      await goToUserSettings(page)

      // Navigate away
      await page.goto('/dashboard')

      // Return to settings
      const cachedResources = []
      page.on('response', response => {
        const cacheHeader = response.headers()['cache-control']
        if (cacheHeader) {
          cachedResources.push({
            url: response.url(),
            cached: cacheHeader.includes('max-age')
          })
        }
      })

      await goToUserSettings(page)

      // Some resources should be cached
      const cachedCount = cachedResources.filter(r => r.cached).length
      expect(cachedCount).toBeGreaterThan(0)
    })

    test('should compress responses', async ({ page }) => {
      await login(page)

      const compressedResponses = []
      page.on('response', response => {
        const encoding = response.headers()['content-encoding']
        if (encoding) {
          compressedResponses.push({
            url: response.url(),
            encoding: encoding
          })
        }
      })

      await goToUserSettings(page)

      // Some responses should be compressed
      expect(compressedResponses.length).toBeGreaterThan(0)
    })
  })
})
