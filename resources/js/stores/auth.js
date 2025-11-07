/**
 * Auth Store - Pinia Store for Authentication State
 * Manages user state, tokens, and authentication status
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  // Helper function to get user from localStorage
  const getUserFromStorage = () => {
    const storedUser = localStorage.getItem('auth_user')
    return storedUser ? JSON.parse(storedUser) : null
  }

  // Helper function to save user to localStorage
  const saveUserToStorage = (userData) => {
    if (userData) {
      localStorage.setItem('auth_user', JSON.stringify(userData))
    } else {
      localStorage.removeItem('auth_user')
    }
  }

  // Helper function to get settings from localStorage
  const getSettingsFromStorage = () => {
    // Get all localStorage keys that start with 'setting_'
    const settings = {}

    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key && key.startsWith('setting_')) {
        const settingKey = key.replace('setting_', '')
        const value = localStorage.getItem(key)

        if (value !== null) {
          // Try to parse as JSON for booleans and numbers, fallback to string
          try {
            settings[settingKey] = JSON.parse(value)
          } catch {
            settings[settingKey] = value
          }
        }
      }
    }

    return Object.keys(settings).length > 0 ? settings : null
  }

  // Helper function to save settings to localStorage
  const saveSettingsToStorage = (settings) => {
    if (settings) {
      // Save each setting as individual key-value pair
      Object.keys(settings).forEach(key => {
        const value = settings[key]

        // Store value directly (strings as-is, booleans/numbers as JSON)
        if (typeof value === 'string') {
          localStorage.setItem(`setting_${key}`, value)
        } else {
          localStorage.setItem(`setting_${key}`, JSON.stringify(value))
        }
      })
    } else {
      // Remove all settings by iterating through localStorage
      const keysToRemove = []
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i)
        if (key && key.startsWith('setting_')) {
          keysToRemove.push(key)
        }
      }
      keysToRemove.forEach(key => localStorage.removeItem(key))
    }
  }

  // Helper function to get impersonation data from localStorage
  const getImpersonationFromStorage = () => {
    const storedImpersonation = localStorage.getItem('impersonation_status')
    return storedImpersonation ? JSON.parse(storedImpersonation) : null
  }

  // Helper function to save impersonation data to localStorage
  const saveImpersonationToStorage = (impersonation) => {
    if (impersonation) {
      localStorage.setItem('impersonation_status', JSON.stringify(impersonation))
    } else {
      localStorage.removeItem('impersonation_status')
    }
  }

  // State
  const user = ref(getUserFromStorage())
  const token = ref(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const isInitializing = ref(false) // Track initial app load user fetch
  const cachedSettings = ref(getSettingsFromStorage())
  const impersonationStatus = ref(getImpersonationFromStorage())

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')

  // Actions
  async function login(credentials) {
    isLoading.value = true
    try {
      const response = await authService.login(credentials)

      // Store token and user
      token.value = response.token
      user.value = response.user
      localStorage.setItem('auth_token', response.token)
      saveUserToStorage(response.user)

      // Store settings and impersonation status
      if (response.settings) {
        cachedSettings.value = response.settings
        saveSettingsToStorage(response.settings)

        // Initialize settings store with cached data
        const { useSettingsStore } = await import('./settings')
        const settingsStore = useSettingsStore()
        Object.assign(settingsStore.userSettings, response.settings)

        // Apply theme and layout immediately after login
        if (response.settings.user_theme) {
          settingsStore.applyTheme(response.settings.user_theme)
        }
        if (response.settings.user_admin_layout) {
          settingsStore.applyLayout(response.settings.user_admin_layout)
        }
      }

      if (response.impersonation) {
        impersonationStatus.value = response.impersonation
        saveImpersonationToStorage(response.impersonation)
      }

      // Set axios default header
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${response.token}`

      return response
    } finally {
      isLoading.value = false
    }
  }

  async function register(userData) {
    isLoading.value = true
    try {
      const response = await authService.register(userData)

      // Store token and user
      token.value = response.token
      user.value = response.user
      localStorage.setItem('auth_token', response.token)
      saveUserToStorage(response.user)

      // Set axios default header
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${response.token}`

      return response
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      if (token.value) {
        await authService.logout()
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // Clear state regardless of API call success
      token.value = null
      user.value = null
      cachedSettings.value = null
      impersonationStatus.value = null
      localStorage.removeItem('auth_token')
      saveUserToStorage(null)
      saveSettingsToStorage(null)
      saveImpersonationToStorage(null)
      delete window.axios.defaults.headers.common['Authorization']

      // Reset settings store
      const { useSettingsStore } = await import('./settings')
      const settingsStore = useSettingsStore()
      settingsStore.resetSettings()

      isLoading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) {
      return null
    }

    isLoading.value = true
    try {
      const response = await authService.getUser()
      user.value = response.user
      saveUserToStorage(response.user)

      // Store settings and impersonation status
      if (response.settings) {
        cachedSettings.value = response.settings
        saveSettingsToStorage(response.settings)

        // Initialize settings store with cached data
        const { useSettingsStore } = await import('./settings')
        const settingsStore = useSettingsStore()
        Object.assign(settingsStore.userSettings, response.settings)

        // Apply theme and layout immediately
        if (response.settings.user_theme) {
          settingsStore.applyTheme(response.settings.user_theme)
        }
        if (response.settings.user_admin_layout) {
          settingsStore.applyLayout(response.settings.user_admin_layout)
        }
      }

      if (response.impersonation) {
        impersonationStatus.value = response.impersonation
        saveImpersonationToStorage(response.impersonation)
      }

      return response.user
    } catch (error) {
      // If fetching user fails (401), clear auth state
      if (error.response?.status === 401) {
        await logout()
      }
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function updateProfile(profileData) {
    isLoading.value = true
    try {
      const response = await authService.updateProfile(profileData)
      user.value = response.user
      saveUserToStorage(response.user)
      return response
    } finally {
      isLoading.value = false
    }
  }

  async function changePassword(passwordData) {
    isLoading.value = true
    try {
      const response = await authService.changePassword(passwordData)
      return response
    } finally {
      isLoading.value = false
    }
  }

  async function logoutAllSessions() {
    isLoading.value = true
    try {
      await authService.logoutAllSessions()
    } catch (error) {
      console.error('Logout all sessions error:', error)
    } finally {
      // Clear state regardless of API call success
      token.value = null
      user.value = null
      cachedSettings.value = null
      impersonationStatus.value = null
      localStorage.removeItem('auth_token')
      saveUserToStorage(null)
      saveSettingsToStorage(null)
      saveImpersonationToStorage(null)
      delete window.axios.defaults.headers.common['Authorization']

      // Reset settings store
      const { useSettingsStore } = await import('./settings')
      const settingsStore = useSettingsStore()
      settingsStore.resetSettings()

      isLoading.value = false
    }
  }

  async function logoutOtherSessions() {
    isLoading.value = true
    try {
      await authService.logoutOtherSessions()
      return true
    } finally {
      isLoading.value = false
    }
  }

  function initAuth() {
    // Set axios header if token exists
    if (token.value && window.axios) {
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }
  }

  async function initializeUser() {
    if (!token.value) {
      return null
    }

    isInitializing.value = true
    try {
      const response = await authService.getUser()
      user.value = response.user
      saveUserToStorage(response.user)

      // Store settings and impersonation status
      if (response.settings) {
        cachedSettings.value = response.settings
        saveSettingsToStorage(response.settings)

        // Initialize settings store with cached data
        const { useSettingsStore } = await import('./settings')
        const settingsStore = useSettingsStore()
        Object.assign(settingsStore.userSettings, response.settings)

        // Apply theme and layout immediately
        if (response.settings.user_theme) {
          settingsStore.applyTheme(response.settings.user_theme)
        }
        if (response.settings.user_admin_layout) {
          settingsStore.applyLayout(response.settings.user_admin_layout)
        }
      }

      if (response.impersonation) {
        impersonationStatus.value = response.impersonation
        saveImpersonationToStorage(response.impersonation)
      }

      return response.user
    } catch (error) {
      // If fetching user fails (401), clear auth state
      if (error.response?.status === 401) {
        await logout()
      }
      throw error
    } finally {
      isInitializing.value = false
    }
  }

  return {
    // State
    user,
    token,
    isLoading,
    isInitializing,
    cachedSettings,
    impersonationStatus,
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    // Actions
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
    logoutAllSessions,
    logoutOtherSessions,
    initAuth,
    initializeUser
  }
})