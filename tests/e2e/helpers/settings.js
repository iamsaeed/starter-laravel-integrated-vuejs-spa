/**
 * E2E Settings Page Helpers
 */

/**
 * Navigate to user settings page
 * @param {import('@playwright/test').Page} page
 */
export async function goToUserSettings(page) {
  await page.goto('/settings')
  await page.waitForSelector('h1:has-text("Settings")', { timeout: 5000 })
}

/**
 * Navigate to global (admin) settings page
 * @param {import('@playwright/test').Page} page
 */
export async function goToGlobalSettings(page) {
  await page.goto('/admin/settings')
  await page.waitForSelector('h1:has-text("Global Settings")', { timeout: 5000 })
}

/**
 * Fill a form field
 * @param {import('@playwright/test').Page} page
 * @param {string} fieldName - Name attribute of the field
 * @param {string} value
 */
export async function fillField(page, fieldName, value) {
  const field = page.locator(`[name="${fieldName}"]`)
  await field.clear()
  await field.fill(value)
}

/**
 * Select an option from a dropdown
 * @param {import('@playwright/test').Page} page
 * @param {string} fieldName
 * @param {string} optionText
 */
export async function selectOption(page, fieldName, optionText) {
  await page.selectOption(`[name="${fieldName}"]`, { label: optionText })
}

/**
 * Toggle a checkbox
 * @param {import('@playwright/test').Page} page
 * @param {string} fieldName
 * @param {boolean} checked
 */
export async function toggleCheckbox(page, fieldName, checked) {
  const checkbox = page.locator(`[name="${fieldName}"]`)
  const isChecked = await checkbox.isChecked()

  if (isChecked !== checked) {
    await checkbox.click()
  }
}

/**
 * Submit the settings form
 * @param {import('@playwright/test').Page} page
 */
export async function submitSettings(page) {
  await page.click('button[type="submit"]:has-text("Save")')
}

/**
 * Wait for success message
 * @param {import('@playwright/test').Page} page
 * @param {string} message
 */
export async function waitForSuccessMessage(page, message = 'Settings saved successfully') {
  await page.waitForSelector(`.bg-green-50:has-text("${message}")`, {
    timeout: 5000
  })
}

/**
 * Wait for error message
 * @param {import('@playwright/test').Page} page
 */
export async function waitForErrorMessage(page) {
  await page.waitForSelector('.bg-red-50', { timeout: 5000 })
}

/**
 * Check if save button is enabled
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
export async function isSaveButtonEnabled(page) {
  const button = page.locator('button[type="submit"]:has-text("Save")')
  return !(await button.isDisabled())
}

/**
 * Get current theme from DOM
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<string|null>}
 */
export async function getCurrentTheme(page) {
  const html = page.locator('html')
  const classNames = await html.getAttribute('class') || ''

  const themeMatch = classNames.match(/theme-(\w+)/)
  return themeMatch ? themeMatch[1] : null
}

/**
 * Check if unsaved changes warning is visible
 * @param {import('@playwright/test').Page} page
 * @returns {Promise<boolean>}
 */
export async function hasUnsavedWarning(page) {
  try {
    await page.waitForSelector('.bg-yellow-50:has-text("unsaved changes")', {
      timeout: 1000
    })
    return true
  } catch {
    return false
  }
}
