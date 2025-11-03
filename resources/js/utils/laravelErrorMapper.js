/**
 * Maps Laravel validation errors to VeeValidate format
 * Laravel returns errors as { field: ['error1', 'error2'] }
 * VeeValidate expects { field: 'error1' }
 */

export function mapLaravelErrorsToVeeValidate(laravelErrors) {
  const veeValidateErrors = {}

  Object.keys(laravelErrors).forEach((field) => {
    // Take the first error message for each field
    veeValidateErrors[field] = Array.isArray(laravelErrors[field])
      ? laravelErrors[field][0]
      : laravelErrors[field]
  })

  return veeValidateErrors
}

/**
 * Handles Laravel validation errors from axios responses
 * @param {Object} error - Axios error object
 * @param {Function} setErrors - VeeValidate setErrors function
 * @returns {boolean} - Returns true if validation errors were handled
 */
export function handleLaravelValidationErrors(error, setErrors) {
  if (error.response && error.response.status === 422) {
    const laravelErrors = error.response.data.errors || {}
    const mappedErrors = mapLaravelErrorsToVeeValidate(laravelErrors)
    setErrors(mappedErrors)
    return true
  }
  return false
}

/**
 * Extracts general error message from Laravel response
 * @param {Object} error - Axios error object
 * @returns {string} - Error message
 */
export function getLaravelErrorMessage(error) {
  if (error.response) {
    // Handle validation errors (422)
    if (error.response.status === 422) {
      return error.response.data.message || 'Validation error occurred.'
    }

    // Handle other error responses
    return error.response.data.message || 'An error occurred.'
  }

  // Handle network errors
  if (error.request) {
    return 'Network error. Please check your connection.'
  }

  // Handle other errors
  return error.message || 'An unexpected error occurred.'
}