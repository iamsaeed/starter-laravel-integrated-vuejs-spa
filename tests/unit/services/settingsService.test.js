/**
 * Unit Tests for settingsService
 * Tests all API calls related to settings
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { settingsService } from '@/services/settingsService'
import {
  createUserSettingsResponse,
  createGlobalSettingsResponse,
  createMockSetting,
  createCountriesList,
  createTimezonesList,
  createSuccessfulUpdateResponse,
  createBulkUpdateResponse,
  createMockSettingList,
} from '../../utils/settingsTestUtils'

// Mock window.axios
const mockAxios = {
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  delete: vi.fn(),
}

// Setup global window.axios
global.window = global.window || {}
global.window.axios = mockAxios

describe('settingsService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('getUserSettings', () => {
    it('should fetch all user settings successfully', async () => {
      const mockResponse = createUserSettingsResponse()
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getUserSettings()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/user/settings', { params: {} })
      expect(result).toBeDefined()
      expect(result.settings).toBeDefined()
      expect(result.settings.user_theme).toBe('dark')
      expect(result.settings.notifications_enabled).toBe(true)
    })

    it('should fetch user settings filtered by group', async () => {
      const mockResponse = createUserSettingsResponse()
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getUserSettings('appearance')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/user/settings', {
        params: { group: 'appearance' },
      })
      expect(result).toBeDefined()
      expect(result.settings).toBeDefined()
    })

    it('should handle null group parameter', async () => {
      const mockResponse = createUserSettingsResponse()
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getUserSettings(null)

      expect(mockAxios.get).toHaveBeenCalledWith('/api/user/settings', { params: {} })
      expect(result).toBeDefined()
      expect(result.settings).toBeDefined()
    })

    it('should handle network errors gracefully', async () => {
      mockAxios.get.mockRejectedValue(new Error('Network error'))

      await expect(settingsService.getUserSettings()).rejects.toThrow()
    })
  })

  describe('getUserSetting', () => {
    it('should fetch a specific user setting by key', async () => {
      const mockResponse = {
        setting: { key: 'user_theme', value: 'dark' },
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getUserSetting('user_theme')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/user/settings/user_theme')
      expect(result).toBeDefined()
      expect(result.setting).toBeDefined()
      expect(result.setting.key).toBe('user_theme')
    })

    it('should throw error for non-existent setting', async () => {
      mockAxios.get.mockRejectedValue({
        response: { status: 404, data: { message: 'Setting not found' } },
      })

      await expect(settingsService.getUserSetting('non_existent_setting')).rejects.toThrow()
    })
  })

  describe('updateUserSettings', () => {
    it('should update multiple user settings successfully', async () => {
      const settings = {
        user_theme: 'light',
        notifications_enabled: false,
        items_per_page: 50,
      }
      const mockResponse = createBulkUpdateResponse(settings)
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSettings(settings)

      expect(mockAxios.put).toHaveBeenCalledWith('/api/user/settings', { settings })
      expect(result).toBeDefined()
      expect(result.message).toBe('Settings updated successfully')
      expect(result.updated).toBe(3)
    })

    it('should handle empty settings object', async () => {
      const mockResponse = createBulkUpdateResponse({})
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSettings({})

      expect(result.updated).toBe(0)
    })

    it('should handle validation errors', async () => {
      const error = {
        response: {
          status: 422,
          data: {
            message: 'The given data was invalid.',
            errors: {
              settings: ['The settings field is required.'],
            },
          },
        },
      }
      mockAxios.put.mockRejectedValue(error)

      await expect(settingsService.updateUserSettings({ invalid: 'data' })).rejects.toThrow()
    })
  })

  describe('updateUserSetting', () => {
    it('should update a single user setting', async () => {
      const mockResponse = createSuccessfulUpdateResponse('user_theme', 'blue')
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSetting('user_theme', 'blue')

      expect(mockAxios.put).toHaveBeenCalledWith('/api/user/settings/user_theme', {
        value: 'blue',
      })
      expect(result).toBeDefined()
      expect(result.message).toBe('Setting updated successfully')
      expect(result.setting.key).toBe('user_theme')
      expect(result.setting.value).toBe('blue')
    })

    it('should handle different value types - boolean', async () => {
      const mockResponse = createSuccessfulUpdateResponse('notifications_enabled', false)
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSetting('notifications_enabled', false)

      expect(mockAxios.put).toHaveBeenCalledWith('/api/user/settings/notifications_enabled', {
        value: false,
      })
      expect(result.setting.value).toBe(false)
    })

    it('should handle different value types - number', async () => {
      const mockResponse = createSuccessfulUpdateResponse('items_per_page', 100)
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSetting('items_per_page', 100)

      expect(result.setting.value).toBe(100)
    })

    it('should handle different value types - string', async () => {
      const mockResponse = createSuccessfulUpdateResponse('timezone', 'UTC')
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSetting('timezone', 'UTC')

      expect(result.setting.value).toBe('UTC')
    })
  })

  describe('getGlobalSettings', () => {
    it('should fetch all global settings successfully', async () => {
      const mockResponse = createGlobalSettingsResponse()
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getGlobalSettings()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/settings', { params: { scope: 'global' } })
      expect(result).toBeDefined()
      expect(result.settings).toBeDefined()
      expect(result.settings.site_name).toBe('My Application')
      expect(result.settings.maintenance_mode).toBe(false)
    })

    it('should fetch global settings filtered by group', async () => {
      const mockResponse = createGlobalSettingsResponse()
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getGlobalSettings('general')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/settings', {
        params: { group: 'general', scope: 'global' },
      })
      expect(result).toBeDefined()
      expect(result.settings).toBeDefined()
    })

    it('should handle network errors', async () => {
      mockAxios.get.mockRejectedValue(new Error('Network error'))

      await expect(settingsService.getGlobalSettings()).rejects.toThrow()
    })
  })

  describe('getGlobalSetting', () => {
    it('should fetch a specific global setting', async () => {
      const mockResponse = {
        setting: createMockSetting({ key: 'site_name', value: 'My Application' }),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getGlobalSetting('site_name')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/settings/site_name')
      expect(result.setting.key).toBe('site_name')
    })
  })

  describe('createGlobalSetting', () => {
    it('should create a new global setting', async () => {
      const mockResponse = {
        message: 'Setting created successfully',
        setting: createMockSetting({ key: 'new_setting', value: 'new_value' }),
      }
      mockAxios.post.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.createGlobalSetting('new_setting', 'new_value')

      expect(mockAxios.post).toHaveBeenCalledWith('/api/settings', {
        key: 'new_setting',
        value: 'new_value',
      })
      expect(result.message).toBe('Setting created successfully')
    })
  })

  describe('updateGlobalSetting', () => {
    it('should update a single global setting', async () => {
      const mockResponse = createSuccessfulUpdateResponse('site_name', 'New Site Name')
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateGlobalSetting('site_name', 'New Site Name')

      expect(mockAxios.put).toHaveBeenCalledWith('/api/settings/site_name', {
        value: 'New Site Name',
      })
      expect(result.setting.key).toBe('site_name')
      expect(result.setting.value).toBe('New Site Name')
    })

    it('should handle non-existent settings', async () => {
      mockAxios.put.mockRejectedValue({
        response: { status: 404, data: { message: 'Setting not found' } },
      })

      await expect(
        settingsService.updateGlobalSetting('non_existent_setting', 'value')
      ).rejects.toThrow()
    })
  })

  describe('deleteGlobalSetting', () => {
    it('should delete a setting successfully', async () => {
      const mockResponse = {
        message: 'Setting deleted successfully',
      }
      mockAxios.delete.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.deleteGlobalSetting('old_setting')

      expect(mockAxios.delete).toHaveBeenCalledWith('/api/settings/old_setting')
      expect(result.message).toBe('Setting deleted successfully')
    })

    it('should handle non-existent settings', async () => {
      mockAxios.delete.mockRejectedValue({
        response: { status: 404, data: { message: 'Setting not found' } },
      })

      await expect(settingsService.deleteGlobalSetting('non_existent_setting')).rejects.toThrow()
    })
  })

  describe('getSettingGroups', () => {
    it('should fetch all setting groups with metadata', async () => {
      const mockResponse = {
        groups: [
          {
            name: 'general',
            label: 'General',
            description: 'General application settings',
            icon: 'settings',
            count: 5,
          },
          {
            name: 'appearance',
            label: 'Appearance',
            description: 'Theme and display settings',
            icon: 'palette',
            count: 3,
          },
        ],
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getSettingGroups()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/settings/groups')
      expect(result.groups).toBeDefined()
      expect(result.groups.length).toBe(2)
      expect(result.groups[0]).toHaveProperty('name')
      expect(result.groups[0]).toHaveProperty('label')
      expect(result.groups[0]).toHaveProperty('icon')
    })
  })

  describe('getSettingLists', () => {
    it('should fetch setting lists for themes', async () => {
      const mockResponse = {
        list: createMockSettingList({ key: 'themes' }),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getSettingLists('themes')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/settings/lists/themes')
      expect(result.list).toBeDefined()
      expect(result.list.key).toBe('themes')
      expect(result.list.options).toBeInstanceOf(Array)
    })

    it('should handle non-existent lists', async () => {
      mockAxios.get.mockRejectedValue({
        response: { status: 404, data: { message: 'List not found' } },
      })

      await expect(settingsService.getSettingLists('non_existent_list')).rejects.toThrow()
    })
  })

  describe('getCountries', () => {
    it('should fetch all countries', async () => {
      const mockResponse = {
        countries: createCountriesList(),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getCountries()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/countries', { params: {} })
      expect(result.countries).toBeInstanceOf(Array)
      expect(result.countries.length).toBeGreaterThan(0)
      expect(result.countries[0]).toHaveProperty('name')
      expect(result.countries[0]).toHaveProperty('code')
    })

    it('should filter countries by region', async () => {
      const mockResponse = {
        countries: createCountriesList().filter((c) => c.region === 'Americas'),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getCountries('Americas')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/countries', {
        params: { region: 'Americas' },
      })
      expect(result.countries).toBeInstanceOf(Array)
    })
  })

  describe('getCountry', () => {
    it('should fetch a country by code', async () => {
      const mockResponse = {
        country: createCountriesList()[0],
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getCountry('US')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/countries/US')
      expect(result.country).toBeDefined()
      expect(result.country.code).toBe('US')
    })

    it('should handle non-existent country codes', async () => {
      mockAxios.get.mockRejectedValue({
        response: { status: 404, data: { message: 'Country not found' } },
      })

      await expect(settingsService.getCountry('ZZ')).rejects.toThrow()
    })
  })

  describe('getTimezones', () => {
    it('should fetch all timezones', async () => {
      const mockResponse = {
        timezones: createTimezonesList(),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getTimezones()

      expect(mockAxios.get).toHaveBeenCalledWith('/api/timezones', { params: {} })
      expect(result.timezones).toBeInstanceOf(Array)
      expect(result.timezones.length).toBeGreaterThan(0)
      expect(result.timezones[0]).toHaveProperty('timezone')
      expect(result.timezones[0]).toHaveProperty('gmt_offset')
    })

    it('should filter timezones by country_id', async () => {
      const mockResponse = {
        timezones: createTimezonesList().filter((tz) => tz.country_id === 1),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getTimezones(null, 1)

      expect(mockAxios.get).toHaveBeenCalledWith('/api/timezones', {
        params: { country_id: 1 },
      })
      expect(result.timezones).toBeInstanceOf(Array)
    })

    it('should filter timezones by region', async () => {
      const mockResponse = {
        timezones: createTimezonesList(),
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getTimezones('Americas')

      expect(mockAxios.get).toHaveBeenCalledWith('/api/timezones', {
        params: { region: 'Americas' },
      })
      expect(result.timezones).toBeInstanceOf(Array)
    })
  })

  describe('getTimezone', () => {
    it('should fetch a specific timezone by ID', async () => {
      const mockResponse = {
        timezone: createTimezonesList()[0],
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getTimezone(1)

      expect(mockAxios.get).toHaveBeenCalledWith('/api/timezones/1')
      expect(result.timezone).toBeDefined()
      expect(result.timezone.id).toBe(1)
    })

    it('should handle non-existent timezone IDs', async () => {
      mockAxios.get.mockRejectedValue({
        response: { status: 404, data: { message: 'Timezone not found' } },
      })

      await expect(settingsService.getTimezone(9999)).rejects.toThrow()
    })
  })

  describe('Error Handling', () => {
    it('should handle 500 server errors', async () => {
      mockAxios.get.mockRejectedValue({
        response: {
          status: 500,
          data: { message: 'Server Error' },
        },
      })

      await expect(settingsService.getUserSettings()).rejects.toThrow()
    })

    it('should handle 422 validation errors correctly', async () => {
      const error = {
        response: {
          status: 422,
          data: {
            message: 'The given data was invalid.',
            errors: {
              user_theme: ['The user theme must be one of: default, dark, light.'],
            },
          },
        },
      }
      mockAxios.put.mockRejectedValue(error)

      try {
        await settingsService.updateUserSettings({ user_theme: 'invalid' })
      } catch (err) {
        expect(err.response.status).toBe(422)
        expect(err.response.data.errors).toBeDefined()
      }
    })

    it('should handle network timeouts', async () => {
      mockAxios.get.mockRejectedValue(new Error('Network timeout'))

      await expect(settingsService.getUserSettings()).rejects.toThrow('Network timeout')
    })
  })

  describe('Request/Response Transformation', () => {
    it('should properly transform request data', async () => {
      const settings = {
        user_theme: 'dark',
        notifications_enabled: true,
      }
      const mockResponse = createBulkUpdateResponse(settings)
      mockAxios.put.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.updateUserSettings(settings)

      expect(mockAxios.put).toHaveBeenCalledWith('/api/user/settings', { settings })
      expect(result.settings).toEqual(settings)
    })

    it('should handle null values in response', async () => {
      const mockResponse = {
        settings: {
          user_theme: null,
          timezone: null,
        },
      }
      mockAxios.get.mockResolvedValue({ data: mockResponse })

      const result = await settingsService.getUserSettings()

      expect(result.settings.user_theme).toBeNull()
      expect(result.settings.timezone).toBeNull()
    })
  })
})
