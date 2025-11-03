import { useRouter } from 'vue-router'

export function useErrorHandler() {
  const router = useRouter()

  const handleError = (error, context = {}) => {
    console.error('Error occurred:', error, context)

    // Determine error type and navigate to appropriate page
    if (error.response) {
      const status = error.response.status

      switch (status) {
        case 401:
          router.push({ name: 'admin.error.unauthorized' })
          break
        case 403:
          router.push({ name: 'admin.error.forbidden' })
          break
        case 404:
          router.push({ name: 'admin.error.notFound' })
          break
        case 500:
        case 502:
        case 503:
        case 504:
          router.push({ name: 'admin.error.serverError' })
          break
        default:
          // For other HTTP errors, show server error page
          router.push({ name: 'admin.error.serverError' })
      }
    } else if (error.code === 'NETWORK_ERROR' || !error.response) {
      // Network errors (no response from server)
      router.push({ name: 'admin.error.networkError' })
    } else {
      // Generic error fallback
      router.push({ name: 'admin.error.serverError' })
    }
  }

  const navigateToError = (errorType) => {
    const errorRoutes = {
      '401': 'admin.error.unauthorized',
      '403': 'admin.error.forbidden',
      '404': 'admin.error.notFound',
      '500': 'admin.error.serverError',
      'network': 'admin.error.networkError',
      'maintenance': 'admin.error.maintenance',
    }

    const routeName = errorRoutes[errorType]
    if (routeName) {
      router.push({ name: routeName })
    } else {
      console.warn(`Unknown error type: ${errorType}`)
      router.push({ name: 'admin.error.notFound' })
    }
  }

  const isMaintenanceMode = () => {
    // You can implement logic here to check if the app is in maintenance mode
    // This could check a flag from your API, localStorage, or environment variable
    return false
  }

  const handleMaintenanceCheck = () => {
    if (isMaintenanceMode()) {
      router.push('/admin/error/maintenance')
      return true
    }
    return false
  }

  return {
    handleError,
    navigateToError,
    isMaintenanceMode,
    handleMaintenanceCheck,
  }
}