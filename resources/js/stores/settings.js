/**
 * Settings Store - Pinia Store for Settings Management
 * Manages user and global settings state
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { settingsService } from '@/services/settingsService'

export const useSettingsStore = defineStore('settings', () => {
  // State
  const userSettings = ref({})
  const globalSettings = ref({})
  const themes = ref([])
  const adminLayouts = ref([])
  const countries = ref([])
  const timezones = ref([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const layoutInitialized = ref(false)

  // Getters
  // Computed
  const currentTheme = computed(() => userSettings.value.user_theme || 'ocean')
  const currentAdminLayout = computed(() => userSettings.value.user_admin_layout || 'classic')
  const notificationsEnabled = computed(() => userSettings.value.notifications_enabled ?? true)
  const itemsPerPage = computed(() => userSettings.value.items_per_page || 25)

  // Actions

  /**
   * Load user settings
   * @param {string} group - Optional group filter
   */
  async function loadUserSettings(group = null) {
    isLoading.value = true
    try {
      const response = await settingsService.getUserSettings(group)
      // Merge settings instead of replacing to avoid overwriting recent changes
      userSettings.value = { ...userSettings.value, ...response.settings }
      return response
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update a single user setting
   * @param {string} key - Setting key
   * @param {*} value - Setting value
   */
  async function updateUserSetting(key, value) {
    isSaving.value = true
    try {
      const response = await settingsService.updateUserSetting(key, value)
      // Update local state
      userSettings.value[key] = value

      // Apply theme immediately if updated
      if (key === 'user_theme') {
        applyTheme(value)
        // Update cache (database is source of truth)
        localStorage.setItem('theme', value)
      }

      return response
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Update multiple user settings
   * @param {Object} settings - Key-value pairs
   */
  async function updateUserSettings(settings) {
    isSaving.value = true
    try {
      const response = await settingsService.updateUserSettings(settings)
      // Update local state
      Object.assign(userSettings.value, settings)

      // Apply theme if it was updated
      if (settings.user_theme) {
        localStorage.setItem('theme', settings.user_theme)
        applyTheme(settings.user_theme)
      }

      // Apply layout if it was updated
      if (settings.user_admin_layout) {
        localStorage.setItem('adminLayout', settings.user_admin_layout)
        applyLayout(settings.user_admin_layout)
      }

      return response
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Load global settings (admin only)
   * @param {string} group - Optional group filter
   */
  async function loadGlobalSettings(group = null) {
    isLoading.value = true
    try {
      const response = await settingsService.getGlobalSettings(group)
      globalSettings.value = response.settings
      return response
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update a global setting (admin only)
   * @param {string} key - Setting key
   * @param {*} value - Setting value
   */
  async function updateGlobalSetting(key, value) {
    isSaving.value = true
    try {
      const response = await settingsService.updateGlobalSetting(key, value)
      // Update local state
      globalSettings.value[key] = value
      return response
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Load available themes
   */
  async function loadThemes() {
    try {
      const response = await settingsService.getSettingLists('themes')
      // Parse metadata JSON strings to objects
      themes.value = response.lists.map(theme => ({
        ...theme,
        metadata: typeof theme.metadata === 'string' ? JSON.parse(theme.metadata) : theme.metadata
      }))
      return response
    } catch (error) {
      console.error('Failed to load themes:', error)
      throw error
    }
  }

  /**
   * Load available admin layouts
   */
  async function loadAdminLayouts() {
    try {
      const response = await settingsService.getSettingLists('admin_layouts')
      // Parse metadata JSON strings to objects
      adminLayouts.value = response.lists.map(layout => ({
        ...layout,
        metadata: typeof layout.metadata === 'string' ? JSON.parse(layout.metadata) : layout.metadata
      }))
      return response
    } catch (error) {
      console.error('Failed to load admin layouts:', error)
      throw error
    }
  }

  /**
   * Load countries
   */
  async function loadCountries() {
    try {
      const response = await settingsService.getCountries()
      countries.value = response.countries
      return response
    } catch (error) {
      console.error('Failed to load countries:', error)
      throw error
    }
  }

  /**
   * Load timezones
   * @param {string} region - Optional region filter
   */
  async function loadTimezones(region = null) {
    try {
      const response = await settingsService.getTimezones(region)
      timezones.value = response.timezones
      return response
    } catch (error) {
      console.error('Failed to load timezones:', error)
      throw error
    }
  }

  /**
   * Apply theme to DOM
   * @param {string} themeName - Theme name
   */
  function applyTheme(themeName) {
    // Remove all existing theme classes
    const themeNames = ['default', 'light', 'dark', 'ocean', 'sunset', 'forest', 'midnight', 'crimson', 'amber', 'slate', 'lavender', 'blue', 'green']
    themeNames.forEach(theme => {
      document.documentElement.classList.remove(`theme-${theme}`)
    })

    // Add new theme class
    document.documentElement.classList.add(`theme-${themeName}`)
    document.documentElement.setAttribute('data-theme', themeName)
  }

  /**
   * Initialize theme from database settings
   * LocalStorage is only used as a cache to prevent FOUC
   */
  async function initTheme() {
    try {
      // Database is the source of truth
      if (userSettings.value.user_theme) {
        const theme = userSettings.value.user_theme
        applyTheme(theme)
        // Update cache
        localStorage.setItem('theme', theme)
      } else {
        // If no database setting, use default and DON'T rely on localStorage
        const defaultTheme = 'ocean'
        applyTheme(defaultTheme)
        localStorage.setItem('theme', defaultTheme)
      }
    } catch (error) {
      console.error('Failed to initialize theme:', error)
      applyTheme('ocean')
    }
  }

  /**
   * Apply layout to DOM
   * @param {string} layoutName - Layout name
   */
  function applyLayout(layoutName) {
    // Remove all existing layout classes
    const layoutNames = ['classic', 'horizontal', 'compact', 'mini']
    layoutNames.forEach(layout => {
      document.documentElement.classList.remove(`layout-${layout}`)
    })

    // Add new layout class
    document.documentElement.classList.add(`layout-${layoutName}`)
    document.documentElement.setAttribute('data-layout', layoutName)
  }

  /**
   * Update user layout preference
   * @param {string} layoutName - Layout name
   */
  async function updateUserLayout(layoutName) {
    isSaving.value = true
    try {
      const response = await settingsService.updateUserSetting('user_admin_layout', layoutName)
      // Update local state
      userSettings.value.user_admin_layout = layoutName

      // Apply to DOM immediately
      applyLayout(layoutName)
      // Update cache (database is source of truth)
      localStorage.setItem('adminLayout', layoutName)

      return response
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Initialize layout from database settings
   * LocalStorage is only used as a cache to prevent FOUC
   */
  async function initLayout() {
    // Only initialize once to avoid overriding user's layout changes
    if (layoutInitialized.value) {
      return
    }

    try {
      // Database is the source of truth
      if (userSettings.value.user_admin_layout) {
        const dbLayout = userSettings.value.user_admin_layout
        applyLayout(dbLayout)
        // Update cache
        localStorage.setItem('adminLayout', dbLayout)
      } else {
        // If no database setting, use default and DON'T rely on localStorage
        const defaultLayout = 'classic'
        applyLayout(defaultLayout)
        localStorage.setItem('adminLayout', defaultLayout)
      }

      layoutInitialized.value = true
    } catch (error) {
      console.error('Failed to initialize layout:', error)
      applyLayout('classic')
      layoutInitialized.value = true
    }
  }

  /**
   * Reset settings store
   */
  function resetSettings() {
    userSettings.value = {}
    globalSettings.value = {}
    themes.value = []
    adminLayouts.value = []
    countries.value = []
    timezones.value = []
    layoutInitialized.value = false
  }

  return {
    // State
    userSettings,
    globalSettings,
    themes,
    adminLayouts,
    countries,
    timezones,
    isLoading,
    isSaving,

    // Getters
    currentTheme,
    currentAdminLayout,
    notificationsEnabled,
    itemsPerPage,

    // Actions
    loadUserSettings,
    updateUserSetting,
    updateUserSettings,
    loadGlobalSettings,
    updateGlobalSetting,
    loadThemes,
    loadAdminLayouts,
    loadCountries,
    loadTimezones,
    applyTheme,
    applyLayout,
    updateUserLayout,
    initTheme,
    initLayout,
    resetSettings
  }
})
