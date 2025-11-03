/**
 * Global Test Setup
 * Runs before all tests
 */

import { vi, beforeAll, afterEach, afterAll } from 'vitest'
import { setupServer } from 'msw/node'
import { settingsHandlers } from './mocks/settingsHandlers'

// ============================================
// MSW Server Setup
// ============================================

/**
 * Setup MSW server for API mocking in integration tests
 * This allows us to mock HTTP requests without touching actual axios
 */
export const server = setupServer(...settingsHandlers)

// Start server before all tests
beforeAll(() => {
  server.listen({
    onUnhandledRequest: 'bypass', // Don't throw on unmocked requests
  })
})

// Reset handlers after each test
afterEach(() => {
  server.resetHandlers()
})

// Clean up after all tests
afterAll(() => {
  server.close()
})

// ============================================
// Mock window.axios for unit tests
// ============================================

global.window = global.window || {}
global.window.axios = {
  defaults: {
    headers: {
      common: {},
    },
  },
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
  delete: vi.fn(),
}

// ============================================
// Mock localStorage
// ============================================

const localStorageMock = (() => {
  let store = {}

  return {
    getItem: (key) => store[key] || null,
    setItem: (key, value) => {
      store[key] = value.toString()
    },
    removeItem: (key) => {
      delete store[key]
    },
    clear: () => {
      store = {}
    },
  }
})()

global.localStorage = localStorageMock

// ============================================
// Mock import.meta.env
// ============================================

global.import = global.import || {}
global.import.meta = {
  env: {
    VITE_APP_ENV: 'testing',
    VITE_API_BASE_URL: 'http://localhost:8001',
  },
}

// ============================================
// Mock console methods to reduce test noise
// ============================================

global.console = {
  ...console,
  error: vi.fn(),
  warn: vi.fn(),
}

// ============================================
// Additional DOM mocks
// ============================================

// Mock IntersectionObserver (for virtual scrolling components)
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  unobserve() {}
  takeRecords() {
    return []
  }
}

// Mock ResizeObserver (for responsive components)
global.ResizeObserver = class ResizeObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  unobserve() {}
}
