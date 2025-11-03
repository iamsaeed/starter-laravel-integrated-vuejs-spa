/**
 * Test Utilities
 * Helper functions for testing Vue components
 */

import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { vi } from 'vitest'

/**
 * Create a test router instance
 */
export function createTestRouter(routes = []) {
  const defaultRoutes = [
    { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
    { path: '/login', name: 'auth.login', component: { template: '<div>Login</div>' } },
    { path: '/register', name: 'auth.register', component: { template: '<div>Register</div>' } },
    { path: '/forgot-password', name: 'auth.forgot-password', component: { template: '<div>Forgot</div>' } },
    { path: '/admin/dashboard', name: 'admin.dashboard', component: { template: '<div>Dashboard</div>' } },
  ]

  return createRouter({
    history: createMemoryHistory(),
    routes: routes.length > 0 ? routes : defaultRoutes,
  })
}

/**
 * Create a test pinia instance
 */
export function createTestPinia() {
  return createPinia()
}

/**
 * Mount component with common test setup
 */
export function mountWithSetup(component, options = {}) {
  const router = options.router || createTestRouter()
  const pinia = options.pinia || createTestPinia()

  const defaultGlobalComponents = {
    FormGroup: { template: '<div><slot /></div>' },
    FormLabel: { template: '<label :for="forId"><slot /></label>', props: ['forId', 'required'] },
    FormInput: {
      template: '<input :id="id" :name="name" :type="type" :placeholder="placeholder" />',
      props: ['id', 'name', 'type', 'placeholder'],
    },
    FormError: { template: '<div class="error"><slot /></div>' },
    FormSuccess: { template: '<div class="success">{{ message }}</div>', props: ['message'] },
    PasswordInput: {
      template: '<input :id="id" :name="name" type="password" :placeholder="placeholder" />',
      props: ['id', 'name', 'placeholder'],
    },
    CheckboxInput: {
      template: '<input :id="id" :name="name" type="checkbox" />',
      props: ['id', 'name', 'label'],
    },
    Icon: { template: '<span></span>', props: ['name', 'size'] },
    AuthPage: {
      template: `
        <div class="auth-page">
          <slot name="form" />
          <slot name="actions" />
          <slot name="links" />
          <slot name="footer" />
        </div>
      `,
      props: ['title', 'description', 'helpText'],
    },
  }

  const mergedOptions = {
    global: {
      plugins: [router, pinia],
      components: {
        ...defaultGlobalComponents,
        ...(options.global?.components || {}),
      },
      stubs: {
        ...(options.global?.stubs || {}),
      },
      mocks: {
        ...(options.global?.mocks || {}),
      },
    },
    ...options,
  }

  // Remove duplicate global key
  delete mergedOptions.router
  delete mergedOptions.pinia

  return mount(component, mergedOptions)
}

/**
 * Wait for all promises to resolve
 */
export function flushPromises() {
  return new Promise((resolve) => setTimeout(resolve, 0))
}

/**
 * Create mock axios instance
 */
export function createMockAxios() {
  return {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    defaults: {
      headers: {
        common: {},
      },
    },
  }
}

/**
 * Create mock form submit event
 */
export function createSubmitEvent() {
  const event = new Event('submit')
  event.preventDefault = vi.fn()
  return event
}
