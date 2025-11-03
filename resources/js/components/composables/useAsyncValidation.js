/**
 * Async Validation Composable
 * Provides debounced async validation for expensive operations like email uniqueness checks
 */

import { ref, computed, watch } from 'vue'

export function useAsyncValidation(validationFn, options = {}) {
  const {
    debounceDelay = 300,
    validateOnBlur = true,
    validateOnChange = true,
    validateOnMount = false,
    dependencies = [],
    cacheResults = true
  } = options

  // State
  const isValidating = ref(false)
  const validationResult = ref(null)
  const validationError = ref(null)
  const lastValidatedValue = ref(null)
  const debounceTimer = ref(null)
  const validationCache = ref(new Map())

  // Computed
  const isValid = computed(() => validationResult.value === true)
  const isInvalid = computed(() => validationResult.value === false)
  const hasError = computed(() => validationError.value !== null)

  // Methods
  const clearValidation = () => {
    isValidating.value = false
    validationResult.value = null
    validationError.value = null
    lastValidatedValue.value = null
    clearDebounce()
  }

  const clearDebounce = () => {
    if (debounceTimer.value) {
      clearTimeout(debounceTimer.value)
      debounceTimer.value = null
    }
  }

  const getCacheKey = (value, deps = []) => {
    return JSON.stringify({ value, deps })
  }

  const getFromCache = (value, deps = []) => {
    if (!cacheResults) return null
    const key = getCacheKey(value, deps)
    return validationCache.value.get(key) || null
  }

  const setCache = (value, deps = [], result, error = null) => {
    if (!cacheResults) return
    const key = getCacheKey(value, deps)
    validationCache.value.set(key, { result, error, timestamp: Date.now() })
  }

  const clearCache = () => {
    validationCache.value.clear()
  }

  const validateValue = async (value, immediate = false) => {
    // Skip validation for empty values unless explicitly required
    if (!value && value !== 0) {
      clearValidation()
      return null
    }

    // Skip if value hasn't changed
    if (value === lastValidatedValue.value && !immediate) {
      return validationResult.value
    }

    // Check cache first
    const currentDeps = dependencies.map(dep => typeof dep === 'function' ? dep() : dep)
    const cached = getFromCache(value, currentDeps)
    if (cached && !immediate) {
      validationResult.value = cached.result
      validationError.value = cached.error
      lastValidatedValue.value = value
      return cached.result
    }

    const performValidation = async () => {
      isValidating.value = true
      validationError.value = null

      try {
        const result = await validationFn(value, currentDeps)
        
        // Only update if this is still the current validation
        if (value === lastValidatedValue.value || immediate) {
          validationResult.value = result
          setCache(value, currentDeps, result)
        }
        
        return result
      } catch (error) {
        // Only update if this is still the current validation
        if (value === lastValidatedValue.value || immediate) {
          validationResult.value = false
          validationError.value = error.message || 'Validation failed'
          setCache(value, currentDeps, false, validationError.value)
        }
        
        return false
      } finally {
        isValidating.value = false
      }
    }

    lastValidatedValue.value = value

    if (immediate) {
      return await performValidation()
    } else {
      clearDebounce()
      debounceTimer.value = setTimeout(performValidation, debounceDelay)
      return null
    }
  }

  const validateImmediately = (value) => {
    return validateValue(value, true)
  }

  const validateOnFieldBlur = (value) => {
    if (validateOnBlur) {
      return validateImmediately(value)
    }
    return null
  }

  const validateOnFieldChange = (value) => {
    if (validateOnChange) {
      return validateValue(value, false)
    }
    return null
  }

  // Watch dependencies for changes
  if (dependencies.length > 0) {
    watch(
      () => dependencies.map(dep => typeof dep === 'function' ? dep() : dep),
      () => {
        if (lastValidatedValue.value !== null) {
          validateImmediately(lastValidatedValue.value)
        }
      },
      { deep: true }
    )
  }

  return {
    // State
    isValidating,
    isValid,
    isInvalid,
    hasError,
    validationResult,
    validationError,
    
    // Methods
    validateValue,
    validateImmediately,
    validateOnFieldBlur,
    validateOnFieldChange,
    clearValidation,
    clearCache
  }
}

// Helper for creating common async validators
export const createAsyncValidators = {
  // Email uniqueness validator
  emailUniqueness: (apiEndpoint) => {
    return async (email) => {
      if (!email) return true
      
      const response = await fetch(`${apiEndpoint}?email=${encodeURIComponent(email)}`)
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.message || 'Email validation failed')
      }
      
      return data.available // Assuming API returns { available: boolean }
    }
  },

  // Username availability validator
  usernameAvailability: (apiEndpoint) => {
    return async (username) => {
      if (!username) return true
      
      const response = await fetch(`${apiEndpoint}?username=${encodeURIComponent(username)}`)
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.message || 'Username validation failed')
      }
      
      return data.available
    }
  },

  // Custom API validator
  customApi: (apiEndpoint, fieldName = 'value') => {
    return async (value) => {
      if (!value) return true
      
      const response = await fetch(`${apiEndpoint}?${fieldName}=${encodeURIComponent(value)}`)
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.message || 'Validation failed')
      }
      
      return data.valid || data.available || true
    }
  }
}