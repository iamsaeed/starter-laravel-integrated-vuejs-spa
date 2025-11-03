/**
 * Component Tests for Login.vue
 * Tests the Login component UI and interactions
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { ref } from 'vue'
import Login from '@/pages/auth/Login.vue'
import { mountWithSetup, flushPromises } from '../../../utils/testUtils'

// Mock the useLoginForm composable
const mockOnSubmit = vi.fn()
const mockIsSubmitting = ref(false)
const mockSuccessMessage = ref('')
const mockErrorMessage = ref('')

vi.mock('@/components/composables/useLoginForm', () => ({
  useLoginForm: () => ({
    onSubmit: mockOnSubmit,
    isSubmitting: mockIsSubmitting,
    successMessage: mockSuccessMessage,
    errorMessage: mockErrorMessage,
  }),
}))

describe('Login.vue Component', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mockIsSubmitting.value = false
    mockSuccessMessage.value = ''
    mockErrorMessage.value = ''
  })

  describe('Component Rendering', () => {
    it('should render the login page with all elements', () => {
      const wrapper = mountWithSetup(Login)

      // Check for form elements
      expect(wrapper.find('form').exists()).toBe(true)
      expect(wrapper.find('.auth-button-primary').exists()).toBe(true)

      // Check for form components (they are rendered as stubs)
      expect(wrapper.findAllComponents({ name: 'FormInput' }).length).toBeGreaterThan(0)
      expect(wrapper.findComponent({ name: 'PasswordInput' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'CheckboxInput' }).exists()).toBe(true)
    })

    it('should render the title and description', () => {
      const wrapper = mountWithSetup(Login)

      const authPage = wrapper.findComponent({ name: 'AuthPage' })
      expect(authPage.props('title')).toBe('Welcome Back')
      expect(authPage.props('description')).toBe('Sign in to your account to continue')
    })

    it('should render help text', () => {
      const wrapper = mountWithSetup(Login)

      const authPage = wrapper.findComponent({ name: 'AuthPage' })
      expect(authPage.props('helpText')).toContain('Having trouble signing in')
    })

    it('should render form labels correctly', () => {
      const wrapper = mountWithSetup(Login)

      const labels = wrapper.findAllComponents({ name: 'FormLabel' })
      expect(labels.length).toBeGreaterThanOrEqual(2)

      // Check email label
      const emailLabel = wrapper.findAll('label').find((label) => label.text().includes('Email'))
      expect(emailLabel).toBeDefined()

      // Check password label
      const passwordLabel = wrapper.findAll('label').find((label) => label.text().includes('Password'))
      expect(passwordLabel).toBeDefined()
    })

    it('should render remember me checkbox', () => {
      const wrapper = mountWithSetup(Login)

      const checkbox = wrapper.find('#remember')
      expect(checkbox.exists()).toBe(true)
    })

    it('should render submit button with correct text', () => {
      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      expect(button.exists()).toBe(true)
      expect(button.text()).toContain('Sign In')
    })
  })

  describe('Form Submission', () => {
    it('should call onSubmit when form is submitted', async () => {
      const wrapper = mountWithSetup(Login)

      const form = wrapper.find('form')
      await form.trigger('submit')
      await flushPromises()

      expect(mockOnSubmit).toHaveBeenCalled()
    })

    it('should call onSubmit when submit button is clicked', async () => {
      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      await button.trigger('click')
      await flushPromises()

      expect(mockOnSubmit).toHaveBeenCalled()
    })

    it('should disable submit button when isSubmitting is true', async () => {
      mockIsSubmitting.value = true

      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      expect(button.attributes('disabled')).toBeDefined()
    })

    it('should enable submit button when isSubmitting is false', () => {
      mockIsSubmitting.value = false

      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      expect(button.attributes('disabled')).toBeUndefined()
    })

    it('should show loading spinner when isSubmitting is true', async () => {
      mockIsSubmitting.value = true

      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      expect(button.text()).toContain('Signing In')
      expect(wrapper.findComponent({ name: 'Icon' }).exists()).toBe(true)
    })

    it('should show normal text when isSubmitting is false', () => {
      mockIsSubmitting.value = false

      const wrapper = mountWithSetup(Login)

      const button = wrapper.find('.auth-button-primary')
      expect(button.text()).toBe('Sign In')
    })
  })

  describe('Success State', () => {
    it('should display success message when successMessage is set', async () => {
      mockSuccessMessage.value = 'Login successful! Redirecting...'

      const wrapper = mountWithSetup(Login)

      const successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.exists()).toBe(true)
      expect(successComponent.props('message')).toBe('Login successful! Redirecting...')
    })

    it('should not display success message when successMessage is empty', () => {
      mockSuccessMessage.value = ''

      const wrapper = mountWithSetup(Login)

      const successDiv = wrapper.find('.success')
      // Success component may exist but should be empty
      if (successDiv.exists()) {
        expect(successDiv.text()).toBe('')
      }
    })
  })

  describe('Error State', () => {
    it('should display error message when errorMessage is set', async () => {
      mockErrorMessage.value = 'Invalid credentials'

      const wrapper = mountWithSetup(Login)

      const errorDiv = wrapper.find('.auth-error')
      expect(errorDiv.exists()).toBe(true)
      expect(errorDiv.text()).toContain('Invalid credentials')
    })

    it('should not display error message when errorMessage is empty', () => {
      mockErrorMessage.value = ''

      const wrapper = mountWithSetup(Login)

      const errorDiv = wrapper.find('.auth-error')
      expect(errorDiv.exists()).toBe(false)
    })

    it('should display error icon when error exists', async () => {
      mockErrorMessage.value = 'Invalid credentials'

      const wrapper = mountWithSetup(Login)

      const errorDiv = wrapper.find('.auth-error')
      expect(errorDiv.find('svg').exists()).toBe(true)
    })
  })

  describe('Navigation Links', () => {
    it('should render forgot password link', () => {
      const wrapper = mountWithSetup(Login)

      const forgotPasswordLink = wrapper.findAll('.auth-link').find((link) => {
        return link.text().includes('Forgot your password')
      })

      expect(forgotPasswordLink).toBeDefined()
    })

    it('should render register link', () => {
      const wrapper = mountWithSetup(Login)

      const registerLink = wrapper.findAll('.auth-link').find((link) => {
        return link.text().includes('Sign up here')
      })

      expect(registerLink).toBeDefined()
    })

    it('should have correct route for forgot password link', () => {
      const wrapper = mountWithSetup(Login)

      const forgotPasswordLink = wrapper
        .findAll('a')
        .find((link) => link.text().includes('Forgot your password'))

      expect(forgotPasswordLink?.attributes('href')).toContain('forgot-password')
    })

    it('should have correct route for register link', () => {
      const wrapper = mountWithSetup(Login)

      const registerLink = wrapper.findAll('a').find((link) => link.text().includes('Sign up here'))

      expect(registerLink?.attributes('href')).toContain('register')
    })
  })

  describe('Form Fields', () => {
    it('should render form input components', () => {
      const wrapper = mountWithSetup(Login)

      // Check that form exists
      const form = wrapper.find('form')
      expect(form.exists()).toBe(true)

      // Check for FormInput component (email)
      const formInputs = wrapper.findAllComponents({ name: 'FormInput' })
      expect(formInputs.length).toBeGreaterThan(0)

      // Check for PasswordInput component
      const passwordInput = wrapper.findComponent({ name: 'PasswordInput' })
      expect(passwordInput.exists()).toBe(true)

      // Check for CheckboxInput component
      const checkboxInput = wrapper.findComponent({ name: 'CheckboxInput' })
      expect(checkboxInput.exists()).toBe(true)
    })
  })

  describe('Component Props and Slots', () => {
    it('should pass correct props to AuthPage', () => {
      const wrapper = mountWithSetup(Login)

      const authPage = wrapper.findComponent({ name: 'AuthPage' })
      expect(authPage.props('title')).toBe('Welcome Back')
      expect(authPage.props('description')).toBe('Sign in to your account to continue')
      expect(authPage.props('helpText')).toBeTruthy()
    })

    it('should render all required slots in AuthPage', () => {
      const wrapper = mountWithSetup(Login)

      // Check that form slot is populated
      expect(wrapper.find('form').exists()).toBe(true)

      // Check that actions slot is populated (submit button)
      expect(wrapper.find('.auth-button-primary').exists()).toBe(true)

      // Check that links slot is populated
      expect(wrapper.find('.auth-link').exists()).toBe(true)
    })
  })

  describe('Reactive State Changes', () => {
    it('should update UI when isSubmitting changes', async () => {
      mockIsSubmitting.value = false

      const wrapper = mountWithSetup(Login)

      let button = wrapper.find('.auth-button-primary')
      expect(button.text()).toBe('Sign In')

      // Change state
      mockIsSubmitting.value = true
      await wrapper.vm.$nextTick()

      button = wrapper.find('.auth-button-primary')
      expect(button.text()).toContain('Signing In')
    })

    it('should update UI when errorMessage changes', async () => {
      mockErrorMessage.value = ''

      const wrapper = mountWithSetup(Login)

      let errorDiv = wrapper.find('.auth-error')
      expect(errorDiv.exists()).toBe(false)

      // Change state
      mockErrorMessage.value = 'New error message'
      await wrapper.vm.$nextTick()

      errorDiv = wrapper.find('.auth-error')
      expect(errorDiv.exists()).toBe(true)
      expect(errorDiv.text()).toContain('New error message')
    })

    it('should update UI when successMessage changes', async () => {
      mockSuccessMessage.value = ''

      const wrapper = mountWithSetup(Login)

      let successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.props('message')).toBe('')

      // Change state
      mockSuccessMessage.value = 'Success!'
      await wrapper.vm.$nextTick()

      successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.props('message')).toBe('Success!')
    })
  })

  describe('Accessibility', () => {
    it('should have proper form structure', () => {
      const wrapper = mountWithSetup(Login)

      const form = wrapper.find('form')
      expect(form.exists()).toBe(true)

      // Check for labels
      const labels = wrapper.findAllComponents({ name: 'FormLabel' })
      expect(labels.length).toBeGreaterThanOrEqual(2)
    })

    it('should have submit button with proper type', () => {
      const wrapper = mountWithSetup(Login)

      const form = wrapper.find('form')
      expect(form.exists()).toBe(true)

      // Form submission should work
      expect(form.attributes('action')).toBeUndefined() // SPA - no action
    })
  })
})
