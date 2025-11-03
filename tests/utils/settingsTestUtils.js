/**
 * Settings Test Utilities
 * Mock data factories and helpers for settings testing
 */

import { vi } from 'vitest'

/**
 * Create a mock setting object
 * @param {Object} overrides - Properties to override
 * @returns {Object} Mock setting
 */
export function createMockSetting(overrides = {}) {
  return {
    id: 1,
    key: 'app_name',
    value: 'My Application',
    type: 'string',
    group: 'general',
    scope: 'global',
    is_public: true,
    description: 'Application name displayed throughout the site',
    validation_rules: 'required|string|max:255',
    reference_key: null,
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
    ...overrides,
  }
}

/**
 * Create a mock setting list object
 * @param {Object} overrides - Properties to override
 * @returns {Object} Mock setting list
 */
export function createMockSettingList(overrides = {}) {
  return {
    id: 1,
    key: 'themes',
    label: 'Available Themes',
    options: [
      { value: 'default', label: 'Default Theme' },
      { value: 'dark', label: 'Dark Theme' },
      { value: 'light', label: 'Light Theme' },
      { value: 'blue', label: 'Blue Theme' },
    ],
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
    ...overrides,
  }
}

/**
 * Create a mock country object
 * @param {Object} overrides - Properties to override
 * @returns {Object} Mock country
 */
export function createMockCountry(overrides = {}) {
  return {
    id: 1,
    name: 'United States',
    code: 'US',
    phone_code: '+1',
    emoji: '🇺🇸',
    region: 'Americas',
    subregion: 'Northern America',
    latitude: 37.0902,
    longitude: -95.7129,
    timezones: [],
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
    ...overrides,
  }
}

/**
 * Create a mock timezone object
 * @param {Object} overrides - Properties to override
 * @returns {Object} Mock timezone
 */
export function createMockTimezone(overrides = {}) {
  return {
    id: 1,
    country_id: 1,
    timezone: 'America/New_York',
    gmt_offset: -18000,
    dst_offset: -14400,
    raw_offset: -18000,
    is_primary: true,
    abbreviation: 'EST',
    display_name: 'Eastern Time (US & Canada)',
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
    ...overrides,
  }
}

/**
 * Create a settings API response
 * @param {string} group - Settings group
 * @param {string} scope - Settings scope
 * @returns {Object} Mock response
 */
export function createSettingsResponse(group = 'general', scope = 'global') {
  return {
    settings: {
      app_name: createMockSetting({
        key: 'app_name',
        value: 'My App',
        group,
        scope,
      }),
      site_description: createMockSetting({
        id: 2,
        key: 'site_description',
        value: 'Welcome to our application',
        group,
        scope,
        validation_rules: 'string|max:500',
      }),
    },
  }
}

/**
 * Create a mock themes list
 * @returns {Array} Array of theme options
 */
export function createThemesList() {
  return [
    { value: 'default', label: 'Default Theme', description: 'Classic look and feel' },
    { value: 'dark', label: 'Dark Theme', description: 'Dark mode for reduced eye strain' },
    { value: 'light', label: 'Light Theme', description: 'Bright and clean interface' },
    { value: 'blue', label: 'Blue Theme', description: 'Professional blue color scheme' },
    { value: 'green', label: 'Green Theme', description: 'Nature-inspired green palette' },
  ]
}

/**
 * Create a mock countries list
 * @returns {Array} Array of countries
 */
export function createCountriesList() {
  return [
    createMockCountry({
      id: 1,
      name: 'United States',
      code: 'US',
      emoji: '🇺🇸',
      region: 'Americas',
    }),
    createMockCountry({
      id: 2,
      name: 'Canada',
      code: 'CA',
      emoji: '🇨🇦',
      region: 'Americas',
      latitude: 56.1304,
      longitude: -106.3468,
    }),
    createMockCountry({
      id: 3,
      name: 'United Kingdom',
      code: 'GB',
      emoji: '🇬🇧',
      region: 'Europe',
      subregion: 'Northern Europe',
      latitude: 55.3781,
      longitude: -3.4360,
    }),
    createMockCountry({
      id: 4,
      name: 'Germany',
      code: 'DE',
      emoji: '🇩🇪',
      region: 'Europe',
      subregion: 'Western Europe',
      latitude: 51.1657,
      longitude: 10.4515,
    }),
    createMockCountry({
      id: 5,
      name: 'Australia',
      code: 'AU',
      emoji: '🇦🇺',
      region: 'Oceania',
      subregion: 'Australia and New Zealand',
      latitude: -25.2744,
      longitude: 133.7751,
    }),
  ]
}

/**
 * Create a mock timezones list
 * @returns {Array} Array of timezones
 */
export function createTimezonesList() {
  return [
    createMockTimezone({
      id: 1,
      country_id: 1,
      timezone: 'America/New_York',
      gmt_offset: -18000,
      dst_offset: -14400,
      is_primary: true,
      abbreviation: 'EST',
      display_name: 'Eastern Time (US & Canada)',
    }),
    createMockTimezone({
      id: 2,
      country_id: 1,
      timezone: 'America/Chicago',
      gmt_offset: -21600,
      dst_offset: -18000,
      is_primary: false,
      abbreviation: 'CST',
      display_name: 'Central Time (US & Canada)',
    }),
    createMockTimezone({
      id: 3,
      country_id: 1,
      timezone: 'America/Denver',
      gmt_offset: -25200,
      dst_offset: -21600,
      is_primary: false,
      abbreviation: 'MST',
      display_name: 'Mountain Time (US & Canada)',
    }),
    createMockTimezone({
      id: 4,
      country_id: 1,
      timezone: 'America/Los_Angeles',
      gmt_offset: -28800,
      dst_offset: -25200,
      is_primary: false,
      abbreviation: 'PST',
      display_name: 'Pacific Time (US & Canada)',
    }),
    createMockTimezone({
      id: 5,
      country_id: 3,
      timezone: 'Europe/London',
      gmt_offset: 0,
      dst_offset: 3600,
      is_primary: true,
      abbreviation: 'GMT',
      display_name: 'London',
    }),
  ]
}

/**
 * Create a mock user settings response
 * @returns {Object} Mock user settings
 */
export function createUserSettingsResponse() {
  return {
    settings: {
      user_theme: 'dark',
      notifications_enabled: true,
      email_notifications: true,
      push_notifications: false,
      marketing_emails: false,
      items_per_page: 25,
      date_format: 'MM/DD/YYYY',
      time_format: '12h',
      language: 'en',
      timezone: 'America/New_York',
    },
  }
}

/**
 * Create a mock global settings response
 * @returns {Object} Mock global settings
 */
export function createGlobalSettingsResponse() {
  return {
    settings: {
      site_name: 'My Application',
      site_description: 'Welcome to our application',
      default_theme: 'default',
      maintenance_mode: false,
      allow_registration: true,
      items_per_page: 25,
      max_upload_size: 10,
      session_timeout: 120,
    },
  }
}

/**
 * Create a validation error response
 * @param {Object} errors - Validation errors
 * @returns {Object} Mock error response
 */
export function createValidationErrorResponse(errors = {}) {
  return {
    response: {
      status: 422,
      data: {
        message: 'The given data was invalid.',
        errors: {
          value: ['The value field is required.'],
          ...errors,
        },
      },
    },
  }
}

/**
 * Create a 404 not found error response
 * @returns {Object} Mock error response
 */
export function createNotFoundErrorResponse() {
  return {
    response: {
      status: 404,
      data: {
        message: 'Setting not found.',
      },
    },
  }
}

/**
 * Create a server error response
 * @returns {Object} Mock error response
 */
export function createServerErrorResponse() {
  return {
    response: {
      status: 500,
      data: {
        message: 'Server Error',
      },
    },
  }
}

/**
 * Create a mock settings store
 * @param {Object} overrides - Properties to override
 * @returns {Object} Mock settings store
 */
export function createMockSettingsStore(overrides = {}) {
  return {
    userSettings: {},
    globalSettings: {},
    themes: createThemesList(),
    countries: createCountriesList(),
    timezones: createTimezonesList(),
    isLoading: false,
    isSaving: false,
    currentTheme: 'default',
    notificationsEnabled: true,
    itemsPerPage: 25,
    loadUserSettings: vi.fn(),
    loadGlobalSettings: vi.fn(),
    updateUserSetting: vi.fn(),
    updateUserSettings: vi.fn(),
    updateGlobalSetting: vi.fn(),
    updateGlobalSettings: vi.fn(),
    loadThemes: vi.fn(),
    loadCountries: vi.fn(),
    loadTimezones: vi.fn(),
    applyTheme: vi.fn(),
    ...overrides,
  }
}

/**
 * Create a successful settings update response
 * @param {string} key - Setting key
 * @param {any} value - Setting value
 * @returns {Object} Mock response
 */
export function createSuccessfulUpdateResponse(key, value) {
  return {
    message: 'Setting updated successfully',
    setting: createMockSetting({ key, value }),
  }
}

/**
 * Create a bulk update response
 * @param {Object} settings - Settings to update
 * @returns {Object} Mock response
 */
export function createBulkUpdateResponse(settings) {
  return {
    message: 'Settings updated successfully',
    updated: Object.keys(settings).length,
    settings,
  }
}
