/**
 * Debounced Validation System
 * Provides configurable debouncing for form validation to improve performance
 */

import { ref, watch, onUnmounted } from 'vue'

/**
 * Creates a debounced function that delays execution
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @param {Object} options - Debounce options
 * @returns {Function} - Debounced function
 */
export function debounce(func, delay, options = {}) {
  const { leading = false, trailing = true, maxWait = null } = options
  
  let timeoutId = null
  let maxTimeoutId = null
  let lastCallTime = 0
  let lastInvokeTime = 0
  let lastArgs = null
  let lastThis = null
  let result = null

  function invokeFunc(time) {
    const args = lastArgs
    const thisArg = lastThis

    lastArgs = null
    lastThis = null
    lastInvokeTime = time
    result = func.apply(thisArg, args)
    return result
  }

  function leadingEdge(time) {
    lastInvokeTime = time
    timeoutId = setTimeout(timerExpired, delay)
    return leading ? invokeFunc(time) : result
  }

  function remainingWait(time) {
    const timeSinceLastCall = time - lastCallTime
    const timeSinceLastInvoke = time - lastInvokeTime
    const timeWaiting = delay - timeSinceLastCall

    return maxWait !== null
      ? Math.min(timeWaiting, maxWait - timeSinceLastInvoke)
      : timeWaiting
  }

  function shouldInvoke(time) {
    const timeSinceLastCall = time - lastCallTime
    const timeSinceLastInvoke = time - lastInvokeTime

    return (
      lastCallTime === 0 ||
      timeSinceLastCall >= delay ||
      timeSinceLastCall < 0 ||
      (maxWait !== null && timeSinceLastInvoke >= maxWait)
    )
  }

  function timerExpired() {
    const time = Date.now()
    if (shouldInvoke(time)) {
      return trailingEdge(time)
    }
    timeoutId = setTimeout(timerExpired, remainingWait(time))
  }

  function trailingEdge(time) {
    timeoutId = null

    if (trailing && lastArgs) {
      return invokeFunc(time)
    }
    lastArgs = null
    lastThis = null
    return result
  }

  function cancel() {
    if (timeoutId !== null) {
      clearTimeout(timeoutId)
    }
    if (maxTimeoutId !== null) {
      clearTimeout(maxTimeoutId)
    }
    lastInvokeTime = 0
    lastArgs = null
    lastCallTime = 0
    lastThis = null
    timeoutId = null
    maxTimeoutId = null
  }

  function flush() {
    return timeoutId === null ? result : trailingEdge(Date.now())
  }

  function pending() {
    return timeoutId !== null
  }

  function debounced(...args) {
    const time = Date.now()
    const isInvoking = shouldInvoke(time)

    lastArgs = args
    lastThis = this
    lastCallTime = time

    if (isInvoking) {
      if (timeoutId === null) {
        return leadingEdge(lastCallTime)
      }
      if (maxWait !== null) {
        timeoutId = setTimeout(timerExpired, delay)
        return invokeFunc(lastCallTime)
      }
    }
    if (timeoutId === null) {
      timeoutId = setTimeout(timerExpired, delay)
    }
    return result
  }

  debounced.cancel = cancel
  debounced.flush = flush
  debounced.pending = pending

  return debounced
}

/**
 * Throttle function that limits execution frequency
 * @param {Function} func - Function to throttle  
 * @param {number} delay - Minimum delay between executions
 * @returns {Function} - Throttled function
 */
export function throttle(func, delay) {
  return debounce(func, delay, { leading: true, trailing: false, maxWait: delay })
}

/**
 * Composable for debounced validation
 * @param {Object} options - Configuration options
 * @returns {Object} - Debounced validation utilities
 */
export function useDebouncedValidation(options = {}) {
  const {
    inputDelay = 300,
    blurDelay = 100,
    submitDelay = 0,
    asyncDelay = 500
  } = options

  const validationTimers = new Map()
  const validationResults = ref(new Map())
  const isValidating = ref(new Set())

  // Create debounced validation function
  const createDebouncedValidator = (fieldName, validator, delay = inputDelay) => {
    const debouncedValidator = debounce(async (value) => {
      isValidating.value.add(fieldName)
      
      try {
        const result = await validator(value)
        validationResults.value.set(fieldName, { isValid: result, error: null })
        return result
      } catch (error) {
        validationResults.value.set(fieldName, { isValid: false, error: error.message })
        return false
      } finally {
        isValidating.value.delete(fieldName)
      }
    }, delay, { trailing: true })

    validationTimers.set(fieldName, debouncedValidator)
    return debouncedValidator
  }

  // Validate field with debouncing
  const validateField = (fieldName, value, validator, delay = inputDelay) => {
    let debouncedValidator = validationTimers.get(fieldName)
    
    if (!debouncedValidator) {
      debouncedValidator = createDebouncedValidator(fieldName, validator, delay)
    }
    
    return debouncedValidator(value)
  }

  // Validate immediately (bypass debounce)
  const validateFieldImmediate = async (fieldName, value, validator) => {
    // Cancel any pending debounced validation
    const debouncedValidator = validationTimers.get(fieldName)
    if (debouncedValidator) {
      debouncedValidator.cancel()
    }

    isValidating.value.add(fieldName)
    
    try {
      const result = await validator(value)
      validationResults.value.set(fieldName, { isValid: result, error: null })
      return result
    } catch (error) {
      validationResults.value.set(fieldName, { isValid: false, error: error.message })
      return false
    } finally {
      isValidating.value.delete(fieldName)
    }
  }

  // Clear validation for field
  const clearFieldValidation = (fieldName) => {
    const debouncedValidator = validationTimers.get(fieldName)
    if (debouncedValidator) {
      debouncedValidator.cancel()
    }
    validationResults.value.delete(fieldName)
    isValidating.value.delete(fieldName)
  }

  // Clear all validations
  const clearAllValidations = () => {
    validationTimers.forEach(validator => validator.cancel())
    validationTimers.clear()
    validationResults.value.clear()
    isValidating.value.clear()
  }

  // Get validation result for field
  const getValidationResult = (fieldName) => {
    return validationResults.value.get(fieldName) || { isValid: null, error: null }
  }

  // Check if field is currently validating
  const isFieldValidating = (fieldName) => {
    return isValidating.value.has(fieldName)
  }

  // Cleanup on unmount
  onUnmounted(() => {
    clearAllValidations()
  })

  return {
    validateField,
    validateFieldImmediate,
    clearFieldValidation,
    clearAllValidations,
    getValidationResult,
    isFieldValidating,
    validationResults,
    isValidating
  }
}

/**
 * Debounced search hook for select components
 * @param {Function} searchFn - Search function
 * @param {Object} options - Search options
 * @returns {Object} - Search utilities
 */
export function useDebouncedSearch(searchFn, options = {}) {
  const { delay = 300, minLength = 1 } = options
  
  const searchQuery = ref('')
  const searchResults = ref([])
  const isSearching = ref(false)
  const searchError = ref(null)

  const debouncedSearch = debounce(async (query) => {
    if (query.length < minLength) {
      searchResults.value = []
      return
    }

    isSearching.value = true
    searchError.value = null

    try {
      const results = await searchFn(query)
      searchResults.value = results || []
    } catch (error) {
      searchError.value = error.message
      searchResults.value = []
    } finally {
      isSearching.value = false
    }
  }, delay)

  // Watch search query changes
  watch(searchQuery, (newQuery) => {
    if (newQuery.length === 0) {
      debouncedSearch.cancel()
      searchResults.value = []
      isSearching.value = false
      searchError.value = null
    } else {
      debouncedSearch(newQuery)
    }
  })

  const clearSearch = () => {
    searchQuery.value = ''
    debouncedSearch.cancel()
    searchResults.value = []
    isSearching.value = false
    searchError.value = null
  }

  onUnmounted(() => {
    debouncedSearch.cancel()
  })

  return {
    searchQuery,
    searchResults,
    isSearching,
    searchError,
    clearSearch
  }
}

/**
 * Performance monitoring utilities
 */
export function usePerformanceMonitor() {
  const performanceMetrics = ref({
    validationCount: 0,
    averageValidationTime: 0,
    slowValidations: []
  })

  const measureValidation = (fieldName, validationFn) => {
    return async (...args) => {
      const startTime = performance.now()
      
      try {
        const result = await validationFn(...args)
        const endTime = performance.now()
        const duration = endTime - startTime

        // Update metrics
        performanceMetrics.value.validationCount++
        const currentAvg = performanceMetrics.value.averageValidationTime
        const count = performanceMetrics.value.validationCount
        performanceMetrics.value.averageValidationTime = 
          (currentAvg * (count - 1) + duration) / count

        // Track slow validations (> 100ms)
        if (duration > 100) {
          performanceMetrics.value.slowValidations.push({
            fieldName,
            duration,
            timestamp: Date.now()
          })
          
          // Keep only last 10 slow validations
          if (performanceMetrics.value.slowValidations.length > 10) {
            performanceMetrics.value.slowValidations.shift()
          }
        }

        return result
      } catch (error) {
        const endTime = performance.now()
        const duration = endTime - startTime
        
        console.warn(`Validation failed for ${fieldName} after ${duration}ms:`, error)
        throw error
      }
    }
  }

  return {
    performanceMetrics,
    measureValidation
  }
}