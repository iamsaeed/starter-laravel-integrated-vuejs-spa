/**
 * E2E Authentication Helpers
 */

/**
 * Login with credentials
 * @param {import('@playwright/test').Page} page
 * @param {object} credentials
 */
export async function login(page, credentials = { email: 'admin@app.com', password: 'password' }) {
  await page.goto('/login')

  await page.fill('input[name="email"]', credentials.email)
  await page.fill('input[name="password"]', credentials.password)

  await page.click('button[type="submit"]')

  // Wait for navigation or redirect
  await page.waitForURL(/\/(dashboard|home)/, { timeout: 5000 })
}

/**
 * Logout
 * @param {import('@playwright/test').Page} page
 */
export async function logout(page) {
  // Click logout button (adjust selector based on your UI)
  await page.click('[data-test="logout"]', { timeout: 5000 }).catch(() => {
    // Try alternate selector
    return page.click('button:has-text("Logout")')
  })

  // Wait for redirect to login
  await page.waitForURL(/\/login/, { timeout: 5000 })
}

/**
 * Check if user is logged in
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
export async function isLoggedIn(page) {
  try {
    // Check for element that only appears when logged in
    await page.waitForSelector('[data-test="user-menu"]', { timeout: 1000 })
    return true
  } catch {
    return false
  }
}
