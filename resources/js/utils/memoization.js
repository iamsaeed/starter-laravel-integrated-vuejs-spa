/**
 * Memoization Utilities for Form Validation and Performance
 * Caches expensive computations to improve performance
 */

import { ref, computed, watch, shallowRef } from 'vue'

/**
 * Generic memoization function
 * @param {Function} fn - Function to memoize
 * @param {Object} options - Memoization options
 * @returns {Function} - Memoized function
 */
export function memoize(fn, options = {}) {
  const {
    maxCacheSize = 100,
    ttl = null, // Time to live in milliseconds
    keyFn = (...args) => JSON.stringify(args),
    weakCache = false
  } = options

  const cache = weakCache ? new WeakMap() : new Map()
  const timestamps = weakCache ? new WeakMap() : new Map()

  const memoized = function (...args) {
    const key = keyFn(...args)
    const now = Date.now()

    // Check if cached result exists and is still valid
    if (cache.has(key)) {
      if (ttl === null || (now - timestamps.get(key)) < ttl) {
        return cache.get(key)
      } else {
        // Expired, remove from cache
        cache.delete(key)
        timestamps.delete(key)
      }
    }

    // Compute result
    const result = fn.apply(this, args)

    // Store in cache
    if (!weakCache && cache.size >= maxCacheSize) {
      // Remove oldest entry if cache is full
      const firstKey = cache.keys().next().value
      cache.delete(firstKey)
      timestamps.delete(firstKey)
    }

    cache.set(key, result)
    timestamps.set(key, now)

    return result
  }

  // Add cache management methods
  memoized.cache = cache
  memoized.clear = () => {
    cache.clear()
    if (!weakCache) timestamps.clear()
  }
  memoized.has = (key) => cache.has(key)
  memoized.delete = (key) => {
    cache.delete(key)
    if (!weakCache) timestamps.delete(key)
  }
  memoized.size = () => cache.size

  return memoized
}

/**
 * Memoized validation schema creation
 */
export const memoizedSchemaCreation = memoize(
  (schemaDefinition) => {
    // This would contain the expensive schema creation logic
    console.log('Creating validation schema...', schemaDefinition)
    return schemaDefinition // Simplified for demo
  },
  {
    maxCacheSize: 50,
    ttl: 5 * 60 * 1000, // 5 minutes
    keyFn: (def) => JSON.stringify(def)
  }
)

/**
 * Memoized validation rule compilation
 */
export const memoizedRuleCompilation = memoize(
  (rules) => {
    // Expensive rule compilation logic would go here
    console.log('Compiling validation rules...', rules)
    return rules // Simplified for demo
  },
  {
    maxCacheSize: 200,
    ttl: 10 * 60 * 1000 // 10 minutes
  }
)

/**
 * Vue composable for memoized computations
 * @param {Function} computeFn - Function to compute value
 * @param {Array} dependencies - Reactive dependencies
 * @param {Object} options - Memoization options
 * @returns {Object} - Computed value and utilities
 */
export function useMemoizedComputed(computeFn, dependencies = [], options = {}) {
  const { ttl = null, keyFn = (deps) => JSON.stringify(deps) } = options
  
  const cache = ref(new Map())
  const timestamps = ref(new Map())
  
  const memoizedValue = computed(() => {
    const deps = dependencies.map(dep => dep.value)
    const key = keyFn(deps)
    const now = Date.now()
    
    // Check cache
    if (cache.value.has(key)) {
      const timestamp = timestamps.value.get(key)
      if (ttl === null || (now - timestamp) < ttl) {
        return cache.value.get(key)
      } else {
        // Expired
        cache.value.delete(key)
        timestamps.value.delete(key)
      }
    }
    
    // Compute new value
    const result = computeFn(deps)
    
    // Cache result
    cache.value.set(key, result)
    timestamps.value.set(key, now)
    
    return result
  })
  
  const clearCache = () => {
    cache.value.clear()
    timestamps.value.clear()
  }
  
  return {
    value: memoizedValue,
    clearCache,
    cacheSize: computed(() => cache.value.size)
  }
}

/**
 * Memoized validation result cache
 */
export function useValidationCache() {
  const validationCache = ref(new Map())
  const maxCacheSize = 500
  const ttl = 2 * 60 * 1000 // 2 minutes
  
  const getCachedValidation = (fieldName, value, rules) => {
    const key = `${fieldName}:${JSON.stringify(value)}:${JSON.stringify(rules)}`
    const cached = validationCache.value.get(key)
    
    if (cached && (Date.now() - cached.timestamp) < ttl) {
      return cached.result
    }
    
    return null
  }
  
  const setCachedValidation = (fieldName, value, rules, result) => {
    const key = `${fieldName}:${JSON.stringify(value)}:${JSON.stringify(rules)}`
    
    // Implement LRU cache behavior
    if (validationCache.value.size >= maxCacheSize) {
      const firstKey = validationCache.value.keys().next().value
      validationCache.value.delete(firstKey)
    }
    
    validationCache.value.set(key, {
      result,
      timestamp: Date.now()
    })
  }
  
  const clearValidationCache = () => {
    validationCache.value.clear()
  }
  
  return {
    getCachedValidation,
    setCachedValidation,
    clearValidationCache,
    cacheSize: computed(() => validationCache.value.size)
  }
}

/**
 * Memoized option filtering for select components
 */
export function useMemoizedOptionFilter() {
  const filterOptions = memoize(
    (options, searchQuery, searchFields = ['label']) => {
      if (!searchQuery) return options
      
      const query = searchQuery.toLowerCase()
      return options.filter(option => 
        searchFields.some(field => 
          option[field] && option[field].toLowerCase().includes(query)
        )
      )
    },
    {
      maxCacheSize: 100,
      ttl: 30 * 1000, // 30 seconds
      keyFn: (options, query, fields) => 
        `${options.length}:${query}:${fields.join(',')}`
    }
  )
  
  return { filterOptions }
}

/**
 * Memoized form value comparison
 */
export function useMemoizedFormComparison() {
  const compareFormValues = memoize(
    (currentValues, initialValues, excludeFields = []) => {
      const changes = {}
      let hasChanges = false
      
      for (const [key, value] of Object.entries(currentValues)) {
        if (excludeFields.includes(key)) continue
        
        const initialValue = initialValues[key]
        const currentSerialized = JSON.stringify(value)
        const initialSerialized = JSON.stringify(initialValue)
        
        if (currentSerialized !== initialSerialized) {
          changes[key] = { from: initialValue, to: value }
          hasChanges = true
        }
      }
      
      return { changes, hasChanges }
    },
    {
      maxCacheSize: 50,
      keyFn: (current, initial, exclude) => 
        `${JSON.stringify(current)}:${JSON.stringify(initial)}:${exclude.join(',')}`
    }
  )
  
  return { compareFormValues }
}

/**
 * Memoized async operation cache
 */
export function useAsyncCache() {
  const cache = shallowRef(new Map())
  const pendingRequests = new Map()
  
  const getCachedAsync = async (key, asyncFn, options = {}) => {
    const { ttl = 5 * 60 * 1000, forceRefresh = false } = options
    
    // Check if already pending
    if (pendingRequests.has(key)) {
      return pendingRequests.get(key)
    }
    
    // Check cache
    if (!forceRefresh && cache.value.has(key)) {
      const cached = cache.value.get(key)
      if (Date.now() - cached.timestamp < ttl) {
        return cached.data
      }
    }
    
    // Create and cache the promise
    const promise = asyncFn().then(data => {
      // Cache the result
      cache.value.set(key, {
        data,
        timestamp: Date.now()
      })
      
      // Remove from pending
      pendingRequests.delete(key)
      
      return data
    }).catch(error => {
      // Remove from pending on error
      pendingRequests.delete(key)
      throw error
    })
    
    // Store pending request
    pendingRequests.set(key, promise)
    
    return promise
  }
  
  const clearCache = (key = null) => {
    if (key) {
      cache.value.delete(key)
      pendingRequests.delete(key)
    } else {
      cache.value.clear()
      pendingRequests.clear()
    }
  }
  
  const invalidateCache = (keyPattern) => {
    if (typeof keyPattern === 'string') {
      // Simple string match
      for (const key of cache.value.keys()) {
        if (key.includes(keyPattern)) {
          cache.value.delete(key)
          pendingRequests.delete(key)
        }
      }
    } else if (keyPattern instanceof RegExp) {
      // Regex match
      for (const key of cache.value.keys()) {
        if (keyPattern.test(key)) {
          cache.value.delete(key)
          pendingRequests.delete(key)
        }
      }
    }
  }
  
  return {
    getCachedAsync,
    clearCache,
    invalidateCache,
    cacheSize: computed(() => cache.value.size),
    pendingCount: computed(() => pendingRequests.size)
  }
}

/**
 * Performance-optimized computed with smart caching
 */
export function useSmartComputed(computeFn, dependencies, options = {}) {
  const {
    deep = false,
    debounce = 0,
    throttle = 0,
    cache = true,
    maxCacheSize = 10
  } = options
  
  const cacheMap = cache ? ref(new Map()) : null
  let computeTimer = null
  let lastComputeTime = 0
  
  const debouncedCompute = (deps) => {
    return new Promise(resolve => {
      if (computeTimer) clearTimeout(computeTimer)
      
      computeTimer = setTimeout(() => {
        const now = Date.now()
        
        // Throttle check
        if (throttle > 0 && (now - lastComputeTime) < throttle) {
          return
        }
        
        lastComputeTime = now
        const result = computeFn(deps)
        
        // Cache management
        if (cache && cacheMap.value) {
          const key = JSON.stringify(deps)
          
          if (cacheMap.value.size >= maxCacheSize) {
            const firstKey = cacheMap.value.keys().next().value
            cacheMap.value.delete(firstKey)
          }
          
          cacheMap.value.set(key, result)
        }
        
        resolve(result)
      }, debounce)
    })
  }
  
  const smartComputed = computed(() => {
    const deps = dependencies.map(dep => dep.value)
    
    // Check cache first
    if (cache && cacheMap.value) {
      const key = JSON.stringify(deps)
      if (cacheMap.value.has(key)) {
        return cacheMap.value.get(key)
      }
    }
    
    // Synchronous computation for immediate needs
    if (debounce === 0 && throttle === 0) {
      return computeFn(deps)
    }
    
    // Asynchronous computation with debounce/throttle
    debouncedCompute(deps)
    return null // Return null while computing
  })
  
  return {
    value: smartComputed,
    clearCache: () => cacheMap.value?.clear(),
    cacheSize: computed(() => cacheMap.value?.size || 0)
  }
}