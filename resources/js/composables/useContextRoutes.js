import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable to get context-specific routes based on current panel (admin/user)
 * This ensures profile and other common pages use the correct panel-specific routes
 */
export function useContextRoutes() {
  const route = useRoute()
  const authStore = useAuthStore()

  // Determine if we're in admin context
  const isAdminContext = computed(() => {
    return route?.path?.startsWith('/admin') || authStore.user?.can_access_admin_panel
  })

  // Get profile routes based on context
  const profileRoutes = computed(() => {
    if (isAdminContext.value) {
      return {
        personal: 'admin.profile.personal',
        security: 'admin.profile.security',
      }
    } else {
      return {
        personal: 'user.profile.personal',
        security: 'user.profile.security',
      }
    }
  })

  // Get dashboard route based on context
  const dashboardRoute = computed(() => {
    if (isAdminContext.value) {
      return { name: 'admin.dashboard' }
    } else {
      return { name: 'user.dashboard' }
    }
  })

  // Get settings routes based on context
  const settingsRoutes = computed(() => {
    if (isAdminContext.value) {
      return {
        appearance: 'admin.settings.appearance',
        notifications: 'admin.settings.notifications',
        preferences: 'admin.settings.preferences',
        global: 'admin.settings.global',
        system: 'admin.settings.system',
      }
    } else {
      return {
        appearance: 'user.settings.appearance',
        notifications: 'user.settings.notifications',
        preferences: 'user.settings.preferences',
      }
    }
  })

  // Get error routes based on context
  const errorRoutes = computed(() => {
    if (isAdminContext.value) {
      return {
        notFound: 'admin.error.notFound',
        forbidden: 'admin.error.forbidden',
        unauthorized: 'admin.error.unauthorized',
        serverError: 'admin.error.serverError',
        networkError: 'admin.error.networkError',
        maintenance: 'admin.error.maintenance',
      }
    } else {
      return {
        notFound: 'user.error.notFound',
        forbidden: 'user.error.forbidden',
        unauthorized: 'user.error.unauthorized',
        serverError: 'user.error.serverError',
        networkError: 'user.error.networkError',
        maintenance: 'user.error.maintenance',
      }
    }
  })

  return {
    isAdminContext,
    profileRoutes,
    settingsRoutes,
    dashboardRoute,
    errorRoutes,
  }
}
