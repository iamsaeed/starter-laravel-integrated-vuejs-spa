/**
 * Integration Tests for Login Flow
 * Tests the complete login flow with real components and stores
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import Login from '@/pages/auth/Login.vue'
import { useAuthStore } from '@/stores/auth'
import { mountWithSetup, flushPromises, createTestRouter } from '../../utils/testUtils'
import {
  createSuccessfulLoginResponse,
  createAuthErrorResponse,
} from '../../mocks/storeMocks'

// Mock the auth service
vi.mock('@/services/authService', () => ({
  authService: {
    login: vi.fn(),
    register: vi.fn(),
    logout: vi.fn(),
    getUser: vi.fn(),
  },
}))

describe('Login Flow Integration', () => {
  let pinia
  let router
  let authStore
  let authService

  beforeEach(async () => {
    vi.clearAllMocks()
    vi.useFakeTimers()
    localStorage.clear()

    // Create fresh pinia and router
    pinia = createPinia()
    setActivePinia(pinia)
    router = createTestRouter()

    // Initialize router
    await router.push('/login')
    await router.isReady()

    // Get auth store and service
    authStore = useAuthStore()
    authService = (await import('@/services/authService')).authService
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  describe('Component Rendering', () => {
    it('should render login component with form', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      expect(wrapper.find('form').exists()).toBe(true)
      expect(wrapper.find('.auth-button-primary').exists()).toBe(true)
    })

    it('should render navigation links', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      const links = wrapper.findAll('.auth-link')
      expect(links.length).toBeGreaterThan(0)
    })
  })

  describe('Store Integration', () => {
    it('should update auth store on successful login', async () => {
      const successResponse = createSuccessfulLoginResponse()
      authService.login.mockResolvedValue(successResponse)

      // Verify initial state
      expect(authStore.isAuthenticated).toBe(false)
      expect(authStore.user).toBe(null)
      expect(authStore.token).toBe(null)

      // Perform login via store directly
      await authStore.login({
        email: 'test@example.com',
        password: 'password123',
        remember: false,
      })

      // Verify store state after login
      expect(authStore.isAuthenticated).toBe(true)
      expect(authStore.user).toEqual({
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      })
      expect(authStore.token).toBe('test-token-123')
      expect(authStore.userName).toBe('Test User')
      expect(authStore.userEmail).toBe('test@example.com')
    })

    it('should not update auth store on failed login', async () => {
      const authError = createAuthErrorResponse()
      authService.login.mockRejectedValue(authError)

      try {
        await authStore.login({
          email: 'test@example.com',
          password: 'wrongpassword',
          remember: false,
        })
      } catch (error) {
        // Expected to throw
      }

      // Verify store state remains unchanged
      expect(authStore.isAuthenticated).toBe(false)
      expect(authStore.user).toBe(null)
      expect(authStore.token).toBe(null)
    })

    it('should store token in localStorage on successful login', async () => {
      const successResponse = createSuccessfulLoginResponse()
      authService.login.mockResolvedValue(successResponse)

      await authStore.login({
        email: 'test@example.com',
        password: 'password123',
      })

      expect(localStorage.getItem('auth_token')).toBe('test-token-123')
    })

    it('should set axios authorization header on successful login', async () => {
      const successResponse = createSuccessfulLoginResponse()
      authService.login.mockResolvedValue(successResponse)

      await authStore.login({
        email: 'test@example.com',
        password: 'password123',
      })

      expect(window.axios.defaults.headers.common['Authorization']).toBe('Bearer test-token-123')
    })
  })

  describe('Error Handling', () => {
    it('should handle authentication error', async () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      const authError = createAuthErrorResponse()
      authService.login.mockRejectedValue(authError)

      try {
        await authStore.login({
          email: 'test@example.com',
          password: 'wrongpassword',
        })
      } catch (error) {
        // Expected error
      }

      // Verify no token is stored
      expect(localStorage.getItem('auth_token')).toBe(null)
      expect(authStore.isAuthenticated).toBe(false)
    })
  })

  describe('UI State During Actions', () => {
    it('should show submit button', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      const button = wrapper.find('.auth-button-primary')
      expect(button.exists()).toBe(true)
      expect(button.text()).toContain('Sign In')
    })

    it('should have form elements', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      expect(wrapper.find('form').exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'FormInput' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'PasswordInput' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'CheckboxInput' }).exists()).toBe(true)
    })
  })

  describe('Navigation', () => {
    it('should have link to forgot password', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      const forgotLink = wrapper
        .findAll('.auth-link')
        .find((link) => link.text().includes('Forgot your password'))

      expect(forgotLink).toBeDefined()
    })

    it('should have link to register', () => {
      const wrapper = mountWithSetup(Login, { router, pinia })

      const registerLink = wrapper.findAll('.auth-link').find((link) => link.text().includes('Sign up here'))

      expect(registerLink).toBeDefined()
    })
  })
})
