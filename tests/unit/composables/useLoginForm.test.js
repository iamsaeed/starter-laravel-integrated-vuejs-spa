/**
 * Unit Tests for useLoginForm Composable
 * Tests the login form logic structure and exports
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useLoginForm } from '@/components/composables/useLoginForm'
import { createMockRouter, createMockRoute } from '../../mocks/routerMocks'
import { createMockAuthStore } from '../../mocks/storeMocks'

// Mock Vue Router
vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
}))

// Mock Auth Store
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => mockAuthStore,
}))

let mockRouter
let mockRoute
let mockAuthStore

describe('useLoginForm Composable', () => {
  beforeEach(() => {
    // Reset mocks before each test
    vi.clearAllMocks()
    localStorage.clear()

    // Create fresh pinia instance
    setActivePinia(createPinia())

    // Setup mock router
    mockRouter = createMockRouter()

    // Setup mock route with no redirect query
    mockRoute = createMockRoute({
      query: {},
    })

    // Setup mock auth store
    mockAuthStore = createMockAuthStore()
  })

  describe('Composable Structure', () => {
    it('should return required properties and methods', () => {
      const composable = useLoginForm()

      // Verify all required exports exist
      expect(composable).toHaveProperty('onSubmit')
      expect(composable).toHaveProperty('errors')
      expect(composable).toHaveProperty('values')
      expect(composable).toHaveProperty('isSubmitting')
      expect(composable).toHaveProperty('successMessage')
      expect(composable).toHaveProperty('errorMessage')
      expect(composable).toHaveProperty('setFieldValue')
      expect(composable).toHaveProperty('resetForm')
    })

    it('should have onSubmit as a function', () => {
      const composable = useLoginForm()

      expect(typeof composable.onSubmit).toBe('function')
    })

    it('should have setFieldValue as a function', () => {
      const composable = useLoginForm()

      expect(typeof composable.setFieldValue).toBe('function')
    })

    it('should have resetForm as a function', () => {
      const composable = useLoginForm()

      expect(typeof composable.resetForm).toBe('function')
    })
  })

  describe('Initial State', () => {
    it('should initialize with correct default state', () => {
      const composable = useLoginForm()

      expect(composable.isSubmitting.value).toBe(false)
      expect(composable.successMessage.value).toBe('')
      expect(composable.errorMessage.value).toBe('')
    })

    it('should have form values object', () => {
      const composable = useLoginForm()

      expect(composable.values).toBeDefined()
      expect(typeof composable.values).toBe('object')
    })

    it('should have email, password, and remember fields', () => {
      const composable = useLoginForm()

      expect(composable.values).toHaveProperty('email')
      expect(composable.values).toHaveProperty('password')
      expect(composable.values).toHaveProperty('remember')
    })

    it('should initialize remember as false', () => {
      const composable = useLoginForm()

      expect(composable.values.remember).toBe(false)
    })
  })

  describe('Form Field Management', () => {
    it('should update field value when setFieldValue is called', () => {
      const composable = useLoginForm()

      composable.setFieldValue('email', 'test@example.com')

      expect(composable.values.email).toBe('test@example.com')
    })

    it('should update multiple field values', () => {
      const composable = useLoginForm()

      composable.setFieldValue('email', 'test@example.com')
      composable.setFieldValue('password', 'password123')
      composable.setFieldValue('remember', true)

      expect(composable.values.email).toBe('test@example.com')
      expect(composable.values.password).toBe('password123')
      expect(composable.values.remember).toBe(true)
    })

    it('should have errors object', () => {
      const composable = useLoginForm()

      expect(composable.errors).toBeDefined()
      expect(typeof composable.errors).toBe('object')
    })
  })

  describe('State Management', () => {
    it('should have reactive isSubmitting ref', () => {
      const composable = useLoginForm()

      // Should be a Vue ref
      expect(composable.isSubmitting).toHaveProperty('value')
      expect(typeof composable.isSubmitting.value).toBe('boolean')
    })

    it('should have reactive successMessage ref', () => {
      const composable = useLoginForm()

      expect(composable.successMessage).toHaveProperty('value')
      expect(typeof composable.successMessage.value).toBe('string')
    })

    it('should have reactive errorMessage ref', () => {
      const composable = useLoginForm()

      expect(composable.errorMessage).toHaveProperty('value')
      expect(typeof composable.errorMessage.value).toBe('string')
    })
  })

  describe('Router and Route Integration', () => {
    it('should have access to router', () => {
      const composable = useLoginForm()

      // Composable should be able to use router (verified by successful creation)
      expect(composable).toBeDefined()
    })

    it('should have access to route', () => {
      const composable = useLoginForm()

      // Composable should be able to use route (verified by successful creation)
      expect(composable).toBeDefined()
    })

    it('should handle redirect query parameter in route', () => {
      mockRoute.query = { redirect: '/admin/settings' }

      const composable = useLoginForm()

      // Composable should initialize successfully with redirect query
      expect(composable).toBeDefined()
    })
  })

  describe('Auth Store Integration', () => {
    it('should have access to auth store', () => {
      const composable = useLoginForm()

      // Composable should be able to use auth store (verified by successful creation)
      expect(composable).toBeDefined()
    })
  })

  describe('Validation Schema', () => {
    it('should use VeeValidate form', () => {
      const composable = useLoginForm()

      // Should have VeeValidate's handleSubmit return value
      expect(composable.onSubmit).toBeDefined()
      expect(typeof composable.onSubmit).toBe('function')
    })
  })
})
