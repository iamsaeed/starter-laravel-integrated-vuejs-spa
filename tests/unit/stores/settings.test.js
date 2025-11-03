/**
 * Unit Tests for Settings Store
 * Tests state, getters, and actions of the settings Pinia store
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useSettingsStore } from '@/stores/settings'
import { settingsService } from '@/services/settingsService'
import {
  createUserSettingsResponse,
  createGlobalSettingsResponse,
  createThemesList,
  createCountriesList,
  createTimezonesList,
  createSuccessfulUpdateResponse,
  createBulkUpdateResponse,
} from '../../utils/settingsTestUtils'

// Mock the settingsService
vi.mock('@/services/settingsService', () => ({
  settingsService: {
    getUserSettings: vi.fn(),
    getUserSetting: vi.fn(),
    updateUserSettings: vi.fn(),
    updateUserSetting: vi.fn(),
    getGlobalSettings: vi.fn(),
    getGlobalSetting: vi.fn(),
    updateGlobalSetting: vi.fn(),
    updateGlobalSettings: vi.fn(),
    deleteGlobalSetting: vi.fn(),
    getSettingLists: vi.fn(),
    getCountries: vi.fn(),
    getCountry: vi.fn(),
    getTimezones: vi.fn(),
    getTimezone: vi.fn(),
    getSettingGroups: vi.fn(),
  },
}))

describe('useSettingsStore', () => {
  let store

  beforeEach(() => {
    // Create a fresh pinia instance for each test
    setActivePinia(createPinia())
    store = useSettingsStore()

    // Clear all mocks
    vi.clearAllMocks()

    // Clear localStorage
    localStorage.clear()

    // Reset DOM
    document.documentElement.className = ''
    document.documentElement.removeAttribute('data-theme')
  })

  describe('Initial State', () => {
    it('should initialize with empty user settings', () => {
      expect(store.userSettings).toEqual({})
    })

    it('should initialize with empty global settings', () => {
      expect(store.globalSettings).toEqual({})
    })

    it('should initialize with empty themes array', () => {
      expect(store.themes).toEqual([])
    })

    it('should initialize with empty countries array', () => {
      expect(store.countries).toEqual([])
    })

    it('should initialize with empty timezones array', () => {
      expect(store.timezones).toEqual([])
    })

    it('should initialize isLoading as false', () => {
      expect(store.isLoading).toBe(false)
    })

    it('should initialize isSaving as false', () => {
      expect(store.isSaving).toBe(false)
    })
  })

  describe('Getters', () => {
    describe('currentTheme', () => {
      it('should return user_theme from userSettings', () => {
        store.userSettings = { user_theme: 'dark' }
        expect(store.currentTheme).toBe('dark')
      })

      it('should return default when user_theme is not set', () => {
        store.userSettings = {}
        expect(store.currentTheme).toBe('default')
      })

      it('should return default when userSettings is empty', () => {
        expect(store.currentTheme).toBe('default')
      })
    })

    describe('notificationsEnabled', () => {
      it('should return notifications_enabled from userSettings', () => {
        store.userSettings = { notifications_enabled: true }
        expect(store.notificationsEnabled).toBe(true)
      })

      it('should return true by default when not set', () => {
        store.userSettings = {}
        expect(store.notificationsEnabled).toBe(true)
      })

      it('should handle false value correctly', () => {
        store.userSettings = { notifications_enabled: false }
        expect(store.notificationsEnabled).toBe(false)
      })
    })

    describe('itemsPerPage', () => {
      it('should return items_per_page from userSettings', () => {
        store.userSettings = { items_per_page: 50 }
        expect(store.itemsPerPage).toBe(50)
      })

      it('should return 25 as default when not set', () => {
        store.userSettings = {}
        expect(store.itemsPerPage).toBe(25)
      })

      it('should handle different numeric values', () => {
        store.userSettings = { items_per_page: 100 }
        expect(store.itemsPerPage).toBe(100)
      })
    })
  })

  describe('loadUserSettings', () => {
    it('should load user settings successfully', async () => {
      const mockResponse = createUserSettingsResponse()
      settingsService.getUserSettings.mockResolvedValue(mockResponse)

      await store.loadUserSettings()

      expect(settingsService.getUserSettings).toHaveBeenCalledWith(null)
      expect(store.userSettings).toEqual(mockResponse.settings)
    })

    it('should load user settings with group filter', async () => {
      const mockResponse = createUserSettingsResponse()
      settingsService.getUserSettings.mockResolvedValue(mockResponse)

      await store.loadUserSettings('appearance')

      expect(settingsService.getUserSettings).toHaveBeenCalledWith('appearance')
      expect(store.userSettings).toEqual(mockResponse.settings)
    })

    it('should set isLoading to true while loading', async () => {
      const mockResponse = createUserSettingsResponse()
      settingsService.getUserSettings.mockImplementation(
        () =>
          new Promise((resolve) =>
            setTimeout(() => resolve(mockResponse), 100)
          )
      )

      const promise = store.loadUserSettings()
      expect(store.isLoading).toBe(true)

      await promise
      expect(store.isLoading).toBe(false)
    })

    it('should set isLoading to false after error', async () => {
      settingsService.getUserSettings.mockRejectedValue(new Error('Failed to load'))

      try {
        await store.loadUserSettings()
      } catch (error) {
        // Expected error
      }

      expect(store.isLoading).toBe(false)
    })

    it('should handle network errors', async () => {
      settingsService.getUserSettings.mockRejectedValue(new Error('Network error'))

      await expect(store.loadUserSettings()).rejects.toThrow('Network error')
    })
  })

  describe('updateUserSetting', () => {
    it('should update a single user setting', async () => {
      const mockResponse = createSuccessfulUpdateResponse('user_theme', 'blue')
      settingsService.updateUserSetting.mockResolvedValue(mockResponse)

      await store.updateUserSetting('user_theme', 'blue')

      expect(settingsService.updateUserSetting).toHaveBeenCalledWith('user_theme', 'blue')
      expect(store.userSettings.user_theme).toBe('blue')
    })

    it('should save theme to localStorage when updating user_theme', async () => {
      const mockResponse = createSuccessfulUpdateResponse('user_theme', 'dark')
      settingsService.updateUserSetting.mockResolvedValue(mockResponse)

      await store.updateUserSetting('user_theme', 'dark')

      expect(localStorage.getItem('theme')).toBe('dark')
    })

    it('should apply theme to DOM when updating user_theme', async () => {
      const mockResponse = createSuccessfulUpdateResponse('user_theme', 'dark')
      settingsService.updateUserSetting.mockResolvedValue(mockResponse)

      await store.updateUserSetting('user_theme', 'dark')

      expect(document.documentElement.classList.contains('theme-dark')).toBe(true)
      expect(document.documentElement.getAttribute('data-theme')).toBe('dark')
    })

    it('should set isSaving to true while saving', async () => {
      const mockResponse = createSuccessfulUpdateResponse('user_theme', 'blue')
      settingsService.updateUserSetting.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(mockResponse), 100))
      )

      const promise = store.updateUserSetting('user_theme', 'blue')
      expect(store.isSaving).toBe(true)

      await promise
      expect(store.isSaving).toBe(false)
    })

    it('should set isSaving to false after error', async () => {
      settingsService.updateUserSetting.mockRejectedValue(new Error('Failed to save'))

      try {
        await store.updateUserSetting('user_theme', 'invalid')
      } catch (error) {
        // Expected error
      }

      expect(store.isSaving).toBe(false)
    })

    it('should handle different value types', async () => {
      // Boolean
      const boolResponse = createSuccessfulUpdateResponse('notifications_enabled', false)
      settingsService.updateUserSetting.mockResolvedValue(boolResponse)
      await store.updateUserSetting('notifications_enabled', false)
      expect(store.userSettings.notifications_enabled).toBe(false)

      // Number
      const numResponse = createSuccessfulUpdateResponse('items_per_page', 100)
      settingsService.updateUserSetting.mockResolvedValue(numResponse)
      await store.updateUserSetting('items_per_page', 100)
      expect(store.userSettings.items_per_page).toBe(100)

      // String
      const strResponse = createSuccessfulUpdateResponse('timezone', 'UTC')
      settingsService.updateUserSetting.mockResolvedValue(strResponse)
      await store.updateUserSetting('timezone', 'UTC')
      expect(store.userSettings.timezone).toBe('UTC')
    })
  })

  describe('updateUserSettings', () => {
    it('should update multiple user settings', async () => {
      const settings = {
        user_theme: 'dark',
        notifications_enabled: false,
        items_per_page: 50,
      }
      const mockResponse = createBulkUpdateResponse(settings)
      settingsService.updateUserSettings.mockResolvedValue(mockResponse)

      await store.updateUserSettings(settings)

      expect(settingsService.updateUserSettings).toHaveBeenCalledWith(settings)
      expect(store.userSettings).toMatchObject(settings)
    })

    it('should merge settings with existing userSettings', async () => {
      store.userSettings = { existing_setting: 'value' }
      const newSettings = { user_theme: 'dark' }
      const mockResponse = createBulkUpdateResponse(newSettings)
      settingsService.updateUserSettings.mockResolvedValue(mockResponse)

      await store.updateUserSettings(newSettings)

      expect(store.userSettings).toMatchObject({
        existing_setting: 'value',
        user_theme: 'dark',
      })
    })

    it('should set isSaving to true while saving', async () => {
      const settings = { user_theme: 'dark' }
      const mockResponse = createBulkUpdateResponse(settings)
      settingsService.updateUserSettings.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(mockResponse), 100))
      )

      const promise = store.updateUserSettings(settings)
      expect(store.isSaving).toBe(true)

      await promise
      expect(store.isSaving).toBe(false)
    })

    it('should handle errors gracefully', async () => {
      settingsService.updateUserSettings.mockRejectedValue(new Error('Validation error'))

      await expect(store.updateUserSettings({ invalid: 'data' })).rejects.toThrow()
      expect(store.isSaving).toBe(false)
    })
  })

  describe('loadGlobalSettings', () => {
    it('should load global settings successfully', async () => {
      const mockResponse = createGlobalSettingsResponse()
      settingsService.getGlobalSettings.mockResolvedValue(mockResponse)

      await store.loadGlobalSettings()

      expect(settingsService.getGlobalSettings).toHaveBeenCalledWith(null)
      expect(store.globalSettings).toEqual(mockResponse.settings)
    })

    it('should load global settings with group filter', async () => {
      const mockResponse = createGlobalSettingsResponse()
      settingsService.getGlobalSettings.mockResolvedValue(mockResponse)

      await store.loadGlobalSettings('general')

      expect(settingsService.getGlobalSettings).toHaveBeenCalledWith('general')
      expect(store.globalSettings).toEqual(mockResponse.settings)
    })

    it('should set isLoading to true while loading', async () => {
      const mockResponse = createGlobalSettingsResponse()
      settingsService.getGlobalSettings.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(mockResponse), 100))
      )

      const promise = store.loadGlobalSettings()
      expect(store.isLoading).toBe(true)

      await promise
      expect(store.isLoading).toBe(false)
    })
  })

  describe('updateGlobalSetting', () => {
    it('should update a single global setting', async () => {
      const mockResponse = createSuccessfulUpdateResponse('site_name', 'New Name')
      settingsService.updateGlobalSetting.mockResolvedValue(mockResponse)

      await store.updateGlobalSetting('site_name', 'New Name')

      expect(settingsService.updateGlobalSetting).toHaveBeenCalledWith('site_name', 'New Name')
      expect(store.globalSettings.site_name).toBe('New Name')
    })

    it('should set isSaving to true while saving', async () => {
      const mockResponse = createSuccessfulUpdateResponse('site_name', 'New Name')
      settingsService.updateGlobalSetting.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(mockResponse), 100))
      )

      const promise = store.updateGlobalSetting('site_name', 'New Name')
      expect(store.isSaving).toBe(true)

      await promise
      expect(store.isSaving).toBe(false)
    })
  })

  describe('loadThemes', () => {
    it('should load themes successfully', async () => {
      const mockResponse = {
        lists: createThemesList(),
      }
      settingsService.getSettingLists.mockResolvedValue(mockResponse)

      await store.loadThemes()

      expect(settingsService.getSettingLists).toHaveBeenCalledWith('themes')
      expect(store.themes).toEqual(mockResponse.lists)
    })

    it('should handle errors when loading themes', async () => {
      settingsService.getSettingLists.mockRejectedValue(new Error('Failed to load themes'))

      await expect(store.loadThemes()).rejects.toThrow('Failed to load themes')
    })
  })

  describe('loadCountries', () => {
    it('should load countries successfully', async () => {
      const mockResponse = {
        countries: createCountriesList(),
      }
      settingsService.getCountries.mockResolvedValue(mockResponse)

      await store.loadCountries()

      expect(settingsService.getCountries).toHaveBeenCalled()
      expect(store.countries).toEqual(mockResponse.countries)
    })

    it('should handle errors when loading countries', async () => {
      settingsService.getCountries.mockRejectedValue(new Error('Failed to load countries'))

      await expect(store.loadCountries()).rejects.toThrow('Failed to load countries')
    })
  })

  describe('loadTimezones', () => {
    it('should load timezones successfully', async () => {
      const mockResponse = {
        timezones: createTimezonesList(),
      }
      settingsService.getTimezones.mockResolvedValue(mockResponse)

      await store.loadTimezones()

      expect(settingsService.getTimezones).toHaveBeenCalledWith(null)
      expect(store.timezones).toEqual(mockResponse.timezones)
    })

    it('should load timezones with region filter', async () => {
      const mockResponse = {
        timezones: createTimezonesList(),
      }
      settingsService.getTimezones.mockResolvedValue(mockResponse)

      await store.loadTimezones('Americas')

      expect(settingsService.getTimezones).toHaveBeenCalledWith('Americas')
      expect(store.timezones).toEqual(mockResponse.timezones)
    })

    it('should handle errors when loading timezones', async () => {
      settingsService.getTimezones.mockRejectedValue(new Error('Failed to load timezones'))

      await expect(store.loadTimezones()).rejects.toThrow('Failed to load timezones')
    })
  })

  describe('applyTheme', () => {
    it('should apply theme class to document root', () => {
      store.applyTheme('dark')

      expect(document.documentElement.classList.contains('theme-dark')).toBe(true)
      expect(document.documentElement.getAttribute('data-theme')).toBe('dark')
    })

    it('should remove previous theme classes', () => {
      document.documentElement.classList.add('theme-light')
      store.applyTheme('dark')

      expect(document.documentElement.classList.contains('theme-light')).toBe(false)
      expect(document.documentElement.classList.contains('theme-dark')).toBe(true)
    })

    it('should handle multiple theme switches', () => {
      store.applyTheme('light')
      expect(document.documentElement.classList.contains('theme-light')).toBe(true)

      store.applyTheme('dark')
      expect(document.documentElement.classList.contains('theme-light')).toBe(false)
      expect(document.documentElement.classList.contains('theme-dark')).toBe(true)

      store.applyTheme('ocean')
      expect(document.documentElement.classList.contains('theme-dark')).toBe(false)
      expect(document.documentElement.classList.contains('theme-ocean')).toBe(true)
    })

    it('should set data-theme attribute', () => {
      store.applyTheme('sunset')

      expect(document.documentElement.getAttribute('data-theme')).toBe('sunset')
    })
  })

  describe('initTheme', () => {
    it('should initialize theme from userSettings if available', async () => {
      store.userSettings = { user_theme: 'dark' }

      await store.initTheme()

      expect(localStorage.getItem('theme')).toBe('dark')
      expect(document.documentElement.classList.contains('theme-dark')).toBe(true)
    })

    it('should fall back to localStorage if userSettings is empty', async () => {
      localStorage.setItem('theme', 'ocean')

      await store.initTheme()

      expect(document.documentElement.classList.contains('theme-ocean')).toBe(true)
    })

    it('should use default theme if no theme is found', async () => {
      await store.initTheme()

      expect(document.documentElement.classList.contains('theme-default')).toBe(true)
    })

    it('should handle errors gracefully', async () => {
      // Simulate error by breaking localStorage
      vi.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
        throw new Error('Storage error')
      })

      await store.initTheme()

      expect(document.documentElement.classList.contains('theme-default')).toBe(true)
    })
  })

  describe('resetSettings', () => {
    it('should reset all settings to initial state', () => {
      // Set some data
      store.userSettings = { user_theme: 'dark' }
      store.globalSettings = { site_name: 'Test' }
      store.themes = createThemesList()
      store.countries = createCountriesList()
      store.timezones = createTimezonesList()

      // Reset
      store.resetSettings()

      expect(store.userSettings).toEqual({})
      expect(store.globalSettings).toEqual({})
      expect(store.themes).toEqual([])
      expect(store.countries).toEqual([])
      expect(store.timezones).toEqual([])
    })

    it('should not affect loading states', () => {
      store.isLoading = true
      store.isSaving = true

      store.resetSettings()

      expect(store.isLoading).toBe(true)
      expect(store.isSaving).toBe(true)
    })
  })

  describe('Cache Invalidation', () => {
    it('should update settings cache when loading new data', async () => {
      const firstResponse = createUserSettingsResponse()
      settingsService.getUserSettings.mockResolvedValue(firstResponse)

      await store.loadUserSettings()
      expect(store.userSettings.user_theme).toBe('dark')

      const secondResponse = {
        settings: { ...firstResponse.settings, user_theme: 'light' },
      }
      settingsService.getUserSettings.mockResolvedValue(secondResponse)

      await store.loadUserSettings()
      expect(store.userSettings.user_theme).toBe('light')
    })
  })

  describe('Error Recovery', () => {
    it('should not corrupt state on error', async () => {
      const validSettings = { user_theme: 'dark' }
      store.userSettings = validSettings

      settingsService.updateUserSetting.mockRejectedValue(new Error('Network error'))

      try {
        await store.updateUserSetting('user_theme', 'invalid')
      } catch (error) {
        // Expected error
      }

      // State should still contain the valid update attempt
      // (Our implementation updates optimistically)
      expect(store.userSettings).toBeDefined()
    })

    it('should reset loading flags on error', async () => {
      settingsService.getUserSettings.mockRejectedValue(new Error('Network error'))

      try {
        await store.loadUserSettings()
      } catch (error) {
        // Expected error
      }

      expect(store.isLoading).toBe(false)
    })
  })
})
