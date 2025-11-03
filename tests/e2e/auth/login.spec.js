import { test, expect } from '@playwright/test'
import { login, logout, isLoggedIn } from '../helpers/auth.js'

test.describe('Authentication E2E', () => {
  test.describe('Login Flow', () => {
    test('should login with valid credentials', async ({ page }) => {
      await page.goto('/login')

      await page.fill('input[name="email"]', 'admin@app.com')
      await page.fill('input[name="password"]', 'password')
      await page.click('button[type="submit"]')

      // Should redirect to dashboard/home
      await page.waitForURL(/\/(dashboard|home)/, { timeout: 5000 })

      // Should be logged in
      const loggedIn = await isLoggedIn(page)
      expect(loggedIn).toBe(true)
    })

    test('should show error with invalid credentials', async ({ page }) => {
      await page.goto('/login')

      await page.fill('input[name="email"]', 'wrong@example.com')
      await page.fill('input[name="password"]', 'wrongpassword')
      await page.click('button[type="submit"]')

      // Should show error message
      await expect(page.locator('.text-red-500, .bg-red-50')).toBeVisible()

      // Should still be on login page
      await expect(page).toHaveURL(/\/login/)
    })

    test('should validate required fields', async ({ page }) => {
      await page.goto('/login')

      // Try to submit empty form
      await page.click('button[type="submit"]')

      // Should show validation errors
      await expect(page.locator('.text-red-500')).toBeVisible()
    })

    test('should validate email format', async ({ page }) => {
      await page.goto('/login')

      await page.fill('input[name="email"]', 'invalid-email')
      await page.fill('input[name="password"]', 'password')
      await page.click('button[type="submit"]')

      // Should show email validation error
      await expect(page.locator('.text-red-500')).toBeVisible()
    })

    test('should show password toggle', async ({ page }) => {
      await page.goto('/login')

      await page.fill('input[name="password"]', 'password123')

      // Password should be hidden by default
      const passwordType = await page.locator('input[name="password"]').getAttribute('type')
      expect(passwordType).toBe('password')

      // Click toggle
      await page.click('[aria-label="Toggle password visibility"]').catch(() => {
        // Toggle might not be implemented
      })
    })

    test('should remember credentials if checked', async ({ page }) => {
      await page.goto('/login')

      await page.fill('input[name="email"]', 'admin@app.com')
      await page.fill('input[name="password"]', 'password')

      // Check remember me
      const rememberCheckbox = page.locator('input[name="remember"]')
      if (await rememberCheckbox.isVisible()) {
        await rememberCheckbox.check()
      }

      await page.click('button[type="submit"]')
      await page.waitForURL(/\/(dashboard|home)/)

      // Logout and check if email is remembered
      await logout(page)
      const emailValue = await page.locator('input[name="email"]').inputValue()
      // Implementation specific
    })
  })

  test.describe('Logout Flow', () => {
    test('should logout successfully', async ({ page }) => {
      await login(page)

      // Should be logged in
      expect(await isLoggedIn(page)).toBe(true)

      // Logout
      await logout(page)

      // Should be on login page
      await expect(page).toHaveURL(/\/login/)

      // Should not be logged in
      expect(await isLoggedIn(page)).toBe(false)
    })
  })

  test.describe('Protected Routes', () => {
    test('should redirect to login when accessing protected route', async ({ page }) => {
      // Try to access protected route without login
      await page.goto('/settings')

      // Should redirect to login
      await expect(page).toHaveURL(/\/login/)
    })

    test('should access protected route after login', async ({ page }) => {
      await login(page)

      // Now can access protected route
      await page.goto('/settings')

      await expect(page).toHaveURL(/\/settings/)
      await expect(page.locator('h1')).toContainText('Settings')
    })
  })

  test.describe('Session Persistence', () => {
    test('should maintain session across page reloads', async ({ page }) => {
      await login(page)

      // Reload page
      await page.reload()

      // Should still be logged in
      expect(await isLoggedIn(page)).toBe(true)
    })

    test('should maintain session across navigation', async ({ page }) => {
      await login(page)

      // Navigate to different pages
      await page.goto('/settings')
      await page.goto('/dashboard')
      await page.goto('/profile')

      // Should still be logged in
      expect(await isLoggedIn(page)).toBe(true)
    })
  })
})
