/**
 * Router Mocks
 * Mock implementations for Vue Router
 */

import { vi } from 'vitest'

/**
 * Create a mock router instance
 */
export function createMockRouter(overrides = {}) {
  return {
    push: vi.fn(),
    replace: vi.fn(),
    go: vi.fn(),
    back: vi.fn(),
    forward: vi.fn(),
    currentRoute: {
      value: {
        path: '/',
        name: 'home',
        params: {},
        query: {},
        hash: '',
        fullPath: '/',
        matched: [],
        meta: {},
        redirectedFrom: undefined,
      },
    },
    ...overrides,
  }
}

/**
 * Create a mock route instance
 */
export function createMockRoute(overrides = {}) {
  return {
    path: '/',
    name: 'home',
    params: {},
    query: {},
    hash: '',
    fullPath: '/',
    matched: [],
    meta: {},
    redirectedFrom: undefined,
    ...overrides,
  }
}
