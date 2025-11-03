/**
 * Integration Tests for Global Settings Flow
 * Tests the complete global (admin) settings workflow with real API calls (mocked via MSW)
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useSettingsStore } from '@/stores/settings'
import { settingsService } from '@/services/settingsService'
import {
  createGlobalSettingsResponse,
  createSuccessfulUpdateResponse,
  createThemesList
} from '../../utils/settingsTestUtils'

describe('Global Settings Flow Integration', () => {
  let pinia
  let settingsStore

  beforeEach(() => {
    // Create fresh pinia instance
    pinia = createPinia()
    setActivePinia(pinia)
    settingsStore = useSettingsStore()

    // Clear localStorage
    localStorage.clear()

    // Clear mocks
    vi.clearAllMocks()
  })

  describe('Store + Service Integration', () => {
    it('should load global settings through store using service', async () => {
      const mockResponse = createGlobalSettingsResponse()

      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(mockResponse)

      await settingsStore.loadGlobalSettings()

      expect(settingsService.getGlobalSettings).toHaveBeenCalledWith(null)
      expect(settingsStore.globalSettings).toEqual(mockResponse.settings)
    })

    it('should update single global setting through store', async () => {
      const mockResponse = createSuccessfulUpdateResponse('app_name', 'My Application')
      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(mockResponse)

      await settingsStore.updateGlobalSetting('app_name', 'My Application')

      expect(settingsService.updateGlobalSetting).toHaveBeenCalledWith(
        'app_name',
        'My Application'
      )
      expect(settingsStore.globalSettings.app_name).toBe('My Application')
    })

    it('should handle bulk global settings update', async () => {
      const settings = {
        app_name: 'Test App',
        app_url: 'https://test.com',
        require_email_verification: true
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      // Update each setting
      for (const [key, value] of Object.entries(settings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      expect(settingsStore.globalSettings).toMatchObject(settings)
    })
  })

  describe('Application Settings Workflow', () => {
    it('should update application name and URL', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('app_name', 'New App'))
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('app_url', 'https://newapp.com'))

      await settingsStore.updateGlobalSetting('app_name', 'New App')
      await settingsStore.updateGlobalSetting('app_url', 'https://newapp.com')

      expect(settingsStore.globalSettings.app_name).toBe('New App')
      expect(settingsStore.globalSettings.app_url).toBe('https://newapp.com')
    })

    it('should update default items per page', async () => {
      const options = [10, 25, 50, 100]

      for (const items of options) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('default_items_per_page', items)
        )

        await settingsStore.updateGlobalSetting('default_items_per_page', items)
        expect(settingsStore.globalSettings.default_items_per_page).toBe(items)
      }
    })

    it('should validate app name minimum length', async () => {
      const shortName = 'AB'

      vi.spyOn(settingsService, 'updateGlobalSetting').mockRejectedValue(
        new Error('App name must be at least 3 characters')
      )

      await expect(
        settingsStore.updateGlobalSetting('app_name', shortName)
      ).rejects.toThrow()

      expect(settingsStore.isSaving).toBe(false)
    })
  })

  describe('Security Settings Workflow', () => {
    it('should toggle email verification requirement', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('require_email_verification', true))
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('require_email_verification', false))

      await settingsStore.updateGlobalSetting('require_email_verification', true)
      expect(settingsStore.globalSettings.require_email_verification).toBe(true)

      await settingsStore.updateGlobalSetting('require_email_verification', false)
      expect(settingsStore.globalSettings.require_email_verification).toBe(false)
    })

    it('should toggle two-factor authentication', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
        createSuccessfulUpdateResponse('enable_two_factor', true)
      )

      await settingsStore.updateGlobalSetting('enable_two_factor', true)
      expect(settingsStore.globalSettings.enable_two_factor).toBe(true)
    })

    it('should update session lifetime with validation', async () => {
      const lifetimes = [5, 30, 60, 120, 240, 480]

      for (const lifetime of lifetimes) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('session_lifetime', lifetime)
        )

        await settingsStore.updateGlobalSetting('session_lifetime', lifetime)
        expect(settingsStore.globalSettings.session_lifetime).toBe(lifetime)
        expect(settingsStore.globalSettings.session_lifetime).toBeGreaterThanOrEqual(5)
      }
    })

    it('should reject invalid session lifetime', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting').mockRejectedValue(
        new Error('Session lifetime must be at least 5 minutes')
      )

      await expect(
        settingsStore.updateGlobalSetting('session_lifetime', 2)
      ).rejects.toThrow()
    })

    it('should update multiple security settings together', async () => {
      const securitySettings = {
        require_email_verification: true,
        enable_two_factor: true,
        session_lifetime: 60
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(securitySettings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      expect(settingsStore.globalSettings).toMatchObject(securitySettings)
    })
  })

  describe('Email Settings Workflow', () => {
    it('should update email from address and name', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(
          createSuccessfulUpdateResponse('mail_from_address', 'noreply@test.com')
        )
        .mockResolvedValueOnce(
          createSuccessfulUpdateResponse('mail_from_name', 'Test Application')
        )

      await settingsStore.updateGlobalSetting('mail_from_address', 'noreply@test.com')
      await settingsStore.updateGlobalSetting('mail_from_name', 'Test Application')

      expect(settingsStore.globalSettings.mail_from_address).toBe('noreply@test.com')
      expect(settingsStore.globalSettings.mail_from_name).toBe('Test Application')
    })

    it('should validate email address format', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting').mockRejectedValue(
        new Error('Invalid email address')
      )

      await expect(
        settingsStore.updateGlobalSetting('mail_from_address', 'invalid-email')
      ).rejects.toThrow()
    })

    it('should toggle global notifications', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('enable_notifications', false))
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('enable_notifications', true))

      await settingsStore.updateGlobalSetting('enable_notifications', false)
      expect(settingsStore.globalSettings.enable_notifications).toBe(false)

      await settingsStore.updateGlobalSetting('enable_notifications', true)
      expect(settingsStore.globalSettings.enable_notifications).toBe(true)
    })
  })

  describe('Localization Settings Workflow', () => {
    it('should update default timezone', async () => {
      const timezones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo']

      for (const timezone of timezones) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('default_timezone', timezone)
        )

        await settingsStore.updateGlobalSetting('default_timezone', timezone)
        expect(settingsStore.globalSettings.default_timezone).toBe(timezone)
      }
    })

    it('should update default date format', async () => {
      const formats = ['MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD', 'MMM DD, YYYY']

      for (const format of formats) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('default_date_format', format)
        )

        await settingsStore.updateGlobalSetting('default_date_format', format)
        expect(settingsStore.globalSettings.default_date_format).toBe(format)
      }
    })

    it('should update default language', async () => {
      const languages = ['en', 'es', 'fr', 'de']

      for (const lang of languages) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('default_language', lang)
        )

        await settingsStore.updateGlobalSetting('default_language', lang)
        expect(settingsStore.globalSettings.default_language).toBe(lang)
      }
    })

    it('should update all localization settings together', async () => {
      const localizationSettings = {
        default_timezone: 'America/Los_Angeles',
        default_date_format: 'MM/DD/YYYY',
        default_language: 'en'
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(localizationSettings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      expect(settingsStore.globalSettings).toMatchObject(localizationSettings)
    })
  })

  describe('Appearance Settings Workflow', () => {
    it('should update default theme', async () => {
      const mockThemes = createThemesList()

      for (const theme of mockThemes.slice(0, 3)) {
        vi.spyOn(settingsService, 'updateGlobalSetting').mockResolvedValue(
          createSuccessfulUpdateResponse('default_theme', theme.value)
        )

        await settingsStore.updateGlobalSetting('default_theme', theme.value)
        expect(settingsStore.globalSettings.default_theme).toBe(theme.value)
      }
    })

    it('should toggle allow theme change permission', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('allow_theme_change', false))
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('allow_theme_change', true))

      await settingsStore.updateGlobalSetting('allow_theme_change', false)
      expect(settingsStore.globalSettings.allow_theme_change).toBe(false)

      await settingsStore.updateGlobalSetting('allow_theme_change', true)
      expect(settingsStore.globalSettings.allow_theme_change).toBe(true)
    })

    it('should update appearance settings together', async () => {
      const appearanceSettings = {
        default_theme: 'dark',
        allow_theme_change: true
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(appearanceSettings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      expect(settingsStore.globalSettings).toMatchObject(appearanceSettings)
    })
  })

  describe('Complete Global Settings Flow', () => {
    it('should handle complete settings configuration', async () => {
      // 1. Load initial settings
      const initialSettings = createGlobalSettingsResponse()
      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue(initialSettings)

      await settingsStore.loadGlobalSettings()
      expect(settingsStore.globalSettings).toEqual(initialSettings.settings)

      // 2. Update all setting groups
      const allSettings = {
        // Application
        app_name: 'Updated Application',
        app_url: 'https://updated.com',
        default_items_per_page: 50,

        // Security
        require_email_verification: true,
        enable_two_factor: true,
        session_lifetime: 240,

        // Email
        mail_from_address: 'admin@updated.com',
        mail_from_name: 'Updated App',
        enable_notifications: true,

        // Localization
        default_timezone: 'America/Chicago',
        default_date_format: 'DD/MM/YYYY',
        default_language: 'es',

        // Appearance
        default_theme: 'ocean',
        allow_theme_change: true
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(allSettings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      // 3. Verify all updates applied
      expect(settingsStore.globalSettings).toMatchObject(allSettings)

      // 4. Reload and verify persistence
      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue({
        settings: allSettings
      })

      await settingsStore.loadGlobalSettings()
      expect(settingsStore.globalSettings).toMatchObject(allSettings)
    })
  })

  describe('Error Handling', () => {
    it('should handle load errors gracefully', async () => {
      vi.spyOn(settingsService, 'getGlobalSettings').mockRejectedValue(
        new Error('Unauthorized')
      )

      await expect(settingsStore.loadGlobalSettings()).rejects.toThrow('Unauthorized')
      expect(settingsStore.isLoading).toBe(false)
      expect(settingsStore.globalSettings).toEqual({})
    })

    it('should handle individual setting update errors', async () => {
      const originalValue = 'Original App'
      settingsStore.globalSettings = { app_name: originalValue }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockRejectedValue(
        new Error('Validation failed')
      )

      await expect(
        settingsStore.updateGlobalSetting('app_name', '')
      ).rejects.toThrow('Validation failed')

      expect(settingsStore.globalSettings.app_name).toBe(originalValue)
      expect(settingsStore.isSaving).toBe(false)
    })

    it('should handle partial bulk update failures', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting')
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('app_name', 'New App'))
        .mockRejectedValueOnce(new Error('Invalid URL'))
        .mockResolvedValueOnce(createSuccessfulUpdateResponse('default_theme', 'dark'))

      const results = []

      // First setting succeeds
      try {
        await settingsStore.updateGlobalSetting('app_name', 'New App')
        results.push({ key: 'app_name', success: true })
      } catch (error) {
        results.push({ key: 'app_name', success: false })
      }

      // Second setting fails
      try {
        await settingsStore.updateGlobalSetting('app_url', 'invalid')
        results.push({ key: 'app_url', success: true })
      } catch (error) {
        results.push({ key: 'app_url', success: false })
      }

      // Third setting succeeds
      try {
        await settingsStore.updateGlobalSetting('default_theme', 'dark')
        results.push({ key: 'default_theme', success: true })
      } catch (error) {
        results.push({ key: 'default_theme', success: false })
      }

      expect(results[0].success).toBe(true)
      expect(results[1].success).toBe(false)
      expect(results[2].success).toBe(true)

      expect(settingsStore.globalSettings.app_name).toBe('New App')
      expect(settingsStore.globalSettings.default_theme).toBe('dark')
    })
  })

  describe('Loading States', () => {
    it('should properly manage loading state during fetch', async () => {
      let resolvePromise
      const promise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      vi.spyOn(settingsService, 'getGlobalSettings').mockReturnValue(promise)

      const loadPromise = settingsStore.loadGlobalSettings()

      expect(settingsStore.isLoading).toBe(true)

      resolvePromise(createGlobalSettingsResponse())
      await loadPromise

      expect(settingsStore.isLoading).toBe(false)
    })

    it('should properly manage saving state during update', async () => {
      let resolvePromise
      const promise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      vi.spyOn(settingsService, 'updateGlobalSetting').mockReturnValue(promise)

      const savePromise = settingsStore.updateGlobalSetting('app_name', 'Test')

      expect(settingsStore.isSaving).toBe(true)

      resolvePromise(createSuccessfulUpdateResponse('app_name', 'Test'))
      await savePromise

      expect(settingsStore.isSaving).toBe(false)
    })
  })

  describe('Concurrent Updates', () => {
    it('should handle concurrent setting updates', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      const updates = [
        settingsStore.updateGlobalSetting('app_name', 'App'),
        settingsStore.updateGlobalSetting('default_theme', 'dark'),
        settingsStore.updateGlobalSetting('session_lifetime', 120)
      ]

      await Promise.all(updates)

      expect(settingsStore.globalSettings.app_name).toBe('App')
      expect(settingsStore.globalSettings.default_theme).toBe('dark')
      expect(settingsStore.globalSettings.session_lifetime).toBe(120)
    })

    it('should handle rapid sequential updates to same setting', async () => {
      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      await settingsStore.updateGlobalSetting('app_name', 'Version 1')
      await settingsStore.updateGlobalSetting('app_name', 'Version 2')
      await settingsStore.updateGlobalSetting('app_name', 'Version 3')

      expect(settingsStore.globalSettings.app_name).toBe('Version 3')
    })
  })

  describe('Settings Persistence', () => {
    it('should persist settings across store resets', async () => {
      const settings = {
        app_name: 'Persistent App',
        default_theme: 'sunset',
        enable_notifications: true
      }

      vi.spyOn(settingsService, 'updateGlobalSetting').mockImplementation(
        (key, value) => Promise.resolve(createSuccessfulUpdateResponse(key, value))
      )

      for (const [key, value] of Object.entries(settings)) {
        await settingsStore.updateGlobalSetting(key, value)
      }

      // Simulate page reload
      const pinia2 = createPinia()
      setActivePinia(pinia2)
      const newStore = useSettingsStore()

      vi.spyOn(settingsService, 'getGlobalSettings').mockResolvedValue({
        settings: settings
      })

      await newStore.loadGlobalSettings()

      expect(newStore.globalSettings).toMatchObject(settings)
    })
  })
})
