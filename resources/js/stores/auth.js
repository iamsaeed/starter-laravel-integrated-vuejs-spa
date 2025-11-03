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

  // State
  const user = ref(getUserFromStorage())
  const token = ref(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const isInitializing = ref(false) // Track initial app load user fetch

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
      localStorage.removeItem('auth_token')
      saveUserToStorage(null)
      delete window.axios.defaults.headers.common['Authorization']
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
      localStorage.removeItem('auth_token')
      saveUserToStorage(null)
      delete window.axios.defaults.headers.common['Authorization']
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