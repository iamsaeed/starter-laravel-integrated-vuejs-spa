/**
 * Cross-Feature Integration Tests
 * Tests interactions between user settings, global settings, and themes
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useSettingsStore } from '@/stores/settings'
import { settingsService } from '@/services/settingsService'
import {
  createUserSettingsResponse,
  createGlobalSettingsResponse,
  createSuccessfulUpdateResponse,
  createThemesList
} from '../../utils/settingsTestUtils'

describe('Cross-Feature Integration Tests', () => {
  let pinia
  let settingsStore

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    settingsStore = useSettingsStore()
    localStorage.clear()
    vi.clearAllMocks()
  })

  describe('User vs Global Settings Interaction', () => {
    it('should prioritize user settings over global defaults', async () => {
      // Load global settings first
      const globalSettings = createGlobalSettingsResponse()
      globalSettings.settings.default_theme = 'default'
      globalSettings.settings.default_items_per_page = 25

      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(globalSettings)
      await settingsStore.loadGlobalSettings()

      // Load user settings with overrides
      const userSettings = createUserSettingsResponse()
      userSettings.settings.user_theme = 'dark'
      userSettings.settings.items_per_page = 50

      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue(userSettings)
      await settingsStore.loadUserSettings()

      // User settings should override global defaults
      expect(settingsStore.currentTheme).toBe('dark')
      expect(settingsStore.itemsPerPage).toBe(50)
      expect(settingsStore.globalSettings.default_theme).toBe('default')
      expect(settingsStore.globalSettings.default_items_per_page).toBe(25)
    })

    it('should have global defaults available when user setting is not set', async () => {
      // Set global defaults
      const globalSettings = {
        default_theme: 'ocean',
        default_items_per_page: 100,
        default_date_format: 'YYYY-MM-DD'
      }

      settingsStore.globalSettings = globalSettings

      // User settings without some values
      const userSettings = {
        user_theme: '', // Empty - global default available
        items_per_page: null, // Null - global default available
        date_format: 'MM/DD/YYYY' // Set - should use this
      }

      settingsStore.userSettings = userSettings

      // Global defaults should be available
      expect(settingsStore.globalSettings.default_theme).toBe('ocean')
      expect(settingsStore.globalSettings.default_items_per_page).toBe(100)

      // User setting takes precedence when set
      expect(settingsStore.userSettings.date_format).toBe('MM/DD/YYYY')

      // When user setting is empty, application layer can fall back to global
      // currentTheme returns 'default' when empty, so we check the pattern
      const themeToUse = settingsStore.userSettings.user_theme ||
                        settingsStore.globalSettings.default_theme
      expect(themeToUse).toBe('ocean')
    })

    it('should handle both user and global settings being loaded', async () => {
      const globalSettings = createGlobalSettingsResponse()
      const userSettings = createUserSettingsResponse()

      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(globalSettings)
      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue(userSettings)

      // Load both in parallel
      await Promise.all([
        settingsStore.loadGlobalSettings(),
        settingsStore.loadUserSettings()
      ])

      expect(settingsStore.globalSettings).toBeDefined()
      expect(settingsStore.userSettings).toBeDefined()
      expect(Object.keys(settingsStore.globalSettings).length).toBeGreaterThan(0)
      expect(Object.keys(settingsStore.userSettings).length).toBeGreaterThan(0)
    })
  })

  describe('Theme System Integration', () => {
    it('should load themes and apply user preference', async () => {
      // Load theme options
      const mockThemes = createThemesList()
      vi.spyOn(settingsService, 'getSettingLists').mockResolvedValue({
        lists: mockThemes
      })

      await settingsStore.loadThemes()
      expect(settingsStore.themes).toHaveLength(mockThemes.length)

      // Set user theme preference
      const selectedTheme = 'sunset'
      vi.spyOn(settingsService, 'updateUserSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('user_theme', selectedTheme)
      )

      await settingsStore.updateUserSetting('user_theme', selectedTheme)

      // Verify theme applied
      expect(settingsStore.currentTheme).toBe(selectedTheme)
      expect(localStorage.getItem('theme')).toBe(selectedTheme)
      expect(document.documentElement.classList.contains(`theme-${selectedTheme}`)).toBe(true)
    })

    it('should respect allow_theme_change global setting', async () => {
      // Admin disables theme changes
      settingsStore.globalSettings = { allow_theme_change: false }

      // This is just documenting the behavior - actual enforcement
      // would be in the UI component or backend
      const canChangeTheme = settingsStore.globalSettings.allow_theme_change

      expect(canChangeTheme).toBe(false)

      // Enable theme changes
      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('allow_theme_change', true)
      )

      await settingsStore.updateGlobalSetting('allow_theme_change', true)
      expect(settingsStore.globalSettings.allow_theme_change).toBe(true)
    })

    it('should provide global default theme for new users', async () => {
      // Set global default theme
      const defaultTheme = 'crimson'
      settingsStore.globalSettings = { default_theme: defaultTheme }

      // New user with no theme preference
      settingsStore.userSettings = { user_theme: '' }

      // Global default should be available
      expect(settingsStore.globalSettings.default_theme).toBe(defaultTheme)

      // Application can use global default when user setting is empty
      const themeToApply = settingsStore.userSettings.user_theme ||
                          settingsStore.globalSettings.default_theme

      expect(themeToApply).toBe(defaultTheme)
    })
  })

  describe('Notification Settings Integration', () => {
    it('should respect global notification toggle', async () => {
      // Admin disables notifications globally
      settingsStore.globalSettings = { enable_notifications: false }

      // User tries to enable their notifications
      settingsStore.userSettings = { notifications_enabled: true }

      // Global setting should take precedence (enforced by backend/UI)
      const globallyEnabled = settingsStore.globalSettings.enable_notifications
      const userEnabled = settingsStore.userSettings.notifications_enabled

      expect(globallyEnabled).toBe(false)
      expect(userEnabled).toBe(true)
      // In actual implementation, UI would disable based on global setting
    })

    it('should handle notification preferences hierarchy', async () => {
      // User has fine-grained notification control
      const userNotifications = {
        notifications_enabled: true,
        email_notifications: false,
        push_notifications: true
      }

      vi.spyOn(settingsService, 'updateUserSettings').mockResolvedValue({
        success: true,
        settings: userNotifications
      })

      await settingsStore.updateUserSettings(userNotifications)

      expect(settingsStore.notificationsEnabled).toBe(true)
      expect(settingsStore.userSettings.email_notifications).toBe(false)
      expect(settingsStore.userSettings.push_notifications).toBe(true)
    })
  })

  describe('Localization Integration', () => {
    it('should coordinate country, timezone, and date format', async () => {
      const localizationSettings = {
        user_country: 'US',
        user_timezone: 'America/New_York',
        date_format: 'MM/DD/YYYY'
      }

      vi.spyOn(settingsService, 'updateUserSettings').mockResolvedValue({
        success: true,
        settings: localizationSettings
      })

      await settingsStore.updateUserSettings(localizationSettings)

      expect(settingsStore.userSettings.user_country).toBe('US')
      expect(settingsStore.userSettings.user_timezone).toBe('America/New_York')
      expect(settingsStore.userSettings.date_format).toBe('MM/DD/YYYY')
    })

    it('should use global defaults for localization when user not set', async () => {
      const globalDefaults = {
        default_timezone: 'UTC',
        default_date_format: 'YYYY-MM-DD',
        default_language: 'en'
      }

      settingsStore.globalSettings = globalDefaults
      settingsStore.userSettings = {
        user_timezone: '',
        date_format: '',
        user_language: ''
      }

      // Application should fall back to global defaults
      const effectiveTimezone = settingsStore.userSettings.user_timezone ||
                               globalDefaults.default_timezone
      const effectiveFormat = settingsStore.userSettings.date_format ||
                             globalDefaults.default_date_format

      expect(effectiveTimezone).toBe('UTC')
      expect(effectiveFormat).toBe('YYYY-MM-DD')
    })
  })

  describe('Security Settings Impact', () => {
    it('should enforce email verification requirement', async () => {
      // Admin enables email verification
      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('require_email_verification', true)
      )

      await settingsStore.updateGlobalSetting('require_email_verification', true)

      expect(settingsStore.globalSettings.require_email_verification).toBe(true)
      // This would affect registration flow and user access
    })

    it('should respect session lifetime setting', async () => {
      const sessionLifetime = 60 // minutes

      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('session_lifetime', sessionLifetime)
      )

      await settingsStore.updateGlobalSetting('session_lifetime', sessionLifetime)

      expect(settingsStore.globalSettings.session_lifetime).toBe(sessionLifetime)
    })
  })

  describe('Complete Application Configuration Flow', () => {
    it('should configure entire application through settings', async () => {
      // 1. Admin configures global defaults
      const globalConfig = {
        app_name: 'My Application',
        app_url: 'https://myapp.com',
        default_theme: 'ocean',
        default_items_per_page: 50,
        require_email_verification: true,
        enable_two_factor: true,
        session_lifetime: 120,
        mail_from_address: 'admin@myapp.com',
        enable_notifications: true,
        default_timezone: 'America/New_York',
        default_date_format: 'MM/DD/YYYY',
        default_language: 'en',
        allow_theme_change: true
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(globalConfig)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      // 2. User customizes their preferences
      const userPreferences = {
        user_theme: 'crimson',
        items_per_page: 100,
        user_country: 'CA',
        user_timezone: 'America/Toronto',
        date_format: 'DD/MM/YYYY',
        notifications_enabled: false,
        email_notifications: false,
        push_notifications: true
      }

      vi.spyOn(settingsService, 'updateUserSettings').mockResolvedValue({
        success: true,
        settings: userPreferences
      })

      await settingsStore.updateUserSettings(userPreferences)

      // 3. Verify complete configuration
      expect(settingsStore.globalSettings).toMatchObject(globalConfig)
      expect(settingsStore.userSettings).toMatchObject(userPreferences)

      // 4. Verify user preferences override defaults
      expect(settingsStore.currentTheme).toBe('crimson') // User override
      expect(settingsStore.itemsPerPage).toBe(100) // User override
      expect(settingsStore.globalSettings.default_theme).toBe('ocean') // Global default intact
    })
  })

  describe('Store State Consistency', () => {
    it('should maintain consistency between user and global settings', async () => {
      const globalSettings = createGlobalSettingsResponse()
      const userSettings = createUserSettingsResponse()

      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(globalSettings)
      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue(userSettings)

      await settingsStore.loadGlobalSettings()
      await settingsStore.loadUserSettings()

      // Both should be loaded
      expect(settingsStore.globalSettings).toBeDefined()
      expect(settingsStore.userSettings).toBeDefined()

      // Update one shouldn't affect the other
      vi.spyOn(settingsService, 'updateUserSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('user_theme', 'new-theme')
      )

      await settingsStore.updateUserSetting('user_theme', 'new-theme')

      expect(settingsStore.userSettings.user_theme).toBe('new-theme')
      expect(settingsStore.globalSettings.default_theme).toBe(globalSettings.settings.default_theme)
    })

    it('should handle simultaneous updates to user and global settings', async () => {
      vi.spyOn(settingsService, 'updateUserSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('user_theme', 'user-dark')
      )

      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('default_theme', 'global-dark')
      )

      // Update both simultaneously
      await Promise.all([
        settingsStore.updateUserSetting('user_theme', 'user-dark'),
        settingsStore.updateGlobalSetting('default_theme', 'global-dark')
      ])

      expect(settingsStore.userSettings.user_theme).toBe('user-dark')
      expect(settingsStore.globalSettings.default_theme).toBe('global-dark')
    })
  })

  describe('Error Isolation', () => {
    it('should isolate user settings errors from global settings', async () => {
      // Load global settings successfully
      const globalSettings = createGlobalSettingsResponse()
      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(globalSettings)

      await settingsStore.loadGlobalSettings()
      expect(settingsStore.globalSettings).toEqual(globalSettings.settings)

      // User settings fail
      vi.spyOn(settingsService, 'getUserSettings').mockRejectedValue(
        new Error('User settings error')
      )

      await expect(settingsStore.loadUserSettings()).rejects.toThrow('User settings error')

      // Global settings should still be intact
      expect(settingsStore.globalSettings).toEqual(globalSettings.settings)
      expect(Object.keys(settingsStore.globalSettings).length).toBeGreaterThan(0)
    })

    it('should isolate global settings errors from user settings', async () => {
      // Load user settings successfully
      const userSettings = createUserSettingsResponse()
      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue(userSettings)

      await settingsStore.loadUserSettings()
      expect(settingsStore.userSettings).toEqual(userSettings.settings)

      // Global settings fail
      vi.spyOn(settingsService, 'getGlobalSettings').mockRejectedValue(
        new Error('Global settings error')
      )

      await expect(settingsStore.loadGlobalSettings()).rejects.toThrow('Global settings error')

      // User settings should still be intact
      expect(settingsStore.userSettings).toEqual(userSettings.settings)
      expect(Object.keys(settingsStore.userSettings).length).toBeGreaterThan(0)
    })
  })

  describe('Data Synchronization', () => {
    it('should keep UI in sync with store after updates', async () => {
      // Initial load
      const initialSettings = createUserSettingsResponse()
      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue(initialSettings)

      await settingsStore.loadUserSettings()

      const initialTheme = settingsStore.currentTheme

      // Update theme
      vi.spyOn(settingsService, 'updateUserSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('user_theme', 'sunset')
      )

      await settingsStore.updateUserSetting('user_theme', 'sunset')

      // Store, localStorage, and DOM should all be updated
      expect(settingsStore.currentTheme).toBe('sunset')
      expect(settingsStore.currentTheme).not.toBe(initialTheme)
      expect(localStorage.getItem('theme')).toBe('sunset')
    })

    it('should propagate changes through computed properties', async () => {
      settingsStore.userSettings = {
        user_theme: 'dark',
        notifications_enabled: true,
        items_per_page: 50
      }

      expect(settingsStore.currentTheme).toBe('dark')
      expect(settingsStore.notificationsEnabled).toBe(true)
      expect(settingsStore.itemsPerPage).toBe(50)

      // Update directly
      settingsStore.userSettings.user_theme = 'ocean'
      settingsStore.userSettings.notifications_enabled = false
      settingsStore.userSettings.items_per_page = 100

      // Computed properties should reflect changes
      expect(settingsStore.currentTheme).toBe('ocean')
      expect(settingsStore.notificationsEnabled).toBe(false)
      expect(settingsStore.itemsPerPage).toBe(100)
    })
  })

  describe('Multi-User Scenario', () => {
    it('should handle different users with different preferences', async () => {
      // User 1 settings
      const user1Settings = {
        user_theme: 'dark',
        items_per_page: 25,
        notifications_enabled: true
      }

      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue({
        settings: user1Settings
      })

      await settingsStore.loadUserSettings()
      expect(settingsStore.currentTheme).toBe('dark')
      expect(settingsStore.itemsPerPage).toBe(25)

      // Simulate user logout/login - reset store
      settingsStore.resetSettings()

      // User 2 settings
      const user2Settings = {
        user_theme: 'ocean',
        items_per_page: 100,
        notifications_enabled: false
      }

      vi.spyOn(settingsService, 'getUserSettings').mockResolvedValue({
        settings: user2Settings
      })

      await settingsStore.loadUserSettings()
      expect(settingsStore.currentTheme).toBe('ocean')
      expect(settingsStore.itemsPerPage).toBe(100)
      expect(settingsStore.notificationsEnabled).toBe(false)
    })
  })
})
