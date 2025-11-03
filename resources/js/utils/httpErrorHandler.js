/**
 * HTTP Error Handler Utility
 * Provides centralized error handling for HTTP requests
 */

export function handleHttpError(error) {
  if (error.response) {
    // Server responded with error status
    const status = error.response.status
    const data = error.response.data

    switch (status) {
      case 400:
        return {
          title: 'Bad Request',
          message: data.message || 'The request was invalid.',
          type: 'error'
        }
      case 401:
        return {
          title: 'Unauthorized',
          message: data.message || 'You are not authorized to access this resource.',
          type: 'error'
        }
      case 403:
        return {
          title: 'Forbidden',
          message: data.message || 'You do not have permission to access this resource.',
          type: 'error'
        }
      case 404:
        return {
          title: 'Not Found',
          message: data.message || 'The requested resource was not found.',
          type: 'error'
        }
      case 422:
        return {
          title: 'Validation Error',
          message: data.message || 'Please check your input and try again.',
          type: 'validation',
          errors: data.errors || {}
        }
      case 429:
        return {
          title: 'Too Many Requests',
          message: data.message || 'Too many requests. Please try again later.',
          type: 'error'
        }
      case 500:
        return {
          title: 'Server Error',
          message: data.message || 'An unexpected server error occurred.',
          type: 'error'
        }
      case 503:
        return {
          title: 'Service Unavailable',
          message: data.message || 'The service is temporarily unavailable.',
          type: 'error'
        }
      default:
        return {
          title: 'Error',
          message: data.message || 'An unexpected error occurred.',
          type: 'error'
        }
    }
  } else if (error.request) {
    // Request was made but no response received
    return {
      title: 'Network Error',
      message: 'Unable to connect to the server. Please check your internet connection.',
      type: 'error'
    }
  } else {
    // Something else happened
    return {
      title: 'Error',
      message: error.message || 'An unexpected error occurred.',
      type: 'error'
    }
  }
}

/**
 * Log error to console in development
 */
export function logError(error) {
  if (import.meta.env.DEV) {
    console.error('HTTP Error:', error)
  }
}