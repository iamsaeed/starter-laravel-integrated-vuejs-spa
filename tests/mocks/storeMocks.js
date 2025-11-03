/**
 * Store Mocks
 * Mock implementations for Pinia stores
 */

import { vi } from 'vitest'
import { ref, computed } from 'vue'

/**
 * Create a mock auth store
 */
export function createMockAuthStore(overrides = {}) {
  const user = ref(overrides.user || null)
  const token = ref(overrides.token || null)
  const isLoading = ref(overrides.isLoading || false)

  return {
    user,
    token,
    isLoading,
    isAuthenticated: computed(() => !!token.value && !!user.value),
    userName: computed(() => user.value?.name || ''),
    userEmail: computed(() => user.value?.email || ''),
    login: vi.fn(),
    register: vi.fn(),
    logout: vi.fn(),
    fetchUser: vi.fn(),
    updateProfile: vi.fn(),
    changePassword: vi.fn(),
    logoutAllSessions: vi.fn(),
    logoutOtherSessions: vi.fn(),
    initAuth: vi.fn(),
    ...overrides,
  }
}

/**
 * Create a successful login response
 */
export function createSuccessfulLoginResponse() {
  return {
    token: 'test-token-123',
    user: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      created_at: '2024-01-01T00:00:00.000000Z',
      updated_at: '2024-01-01T00:00:00.000000Z',
    },
  }
}

/**
 * Create a validation error response
 */
export function createValidationErrorResponse() {
  return {
    response: {
      status: 422,
      data: {
        message: 'The given data was invalid.',
        errors: {
          email: ['The email field is required.'],
          password: ['The password field is required.'],
        },
      },
    },
  }
}

/**
 * Create an authentication error response
 */
export function createAuthErrorResponse() {
  return {
    response: {
      status: 401,
      data: {
        message: 'These credentials do not match our records.',
      },
    },
  }
}

/**
 * Create a server error response
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
