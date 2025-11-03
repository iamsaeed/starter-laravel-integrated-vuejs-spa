import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ProfileLayout from '@/layouts/ProfileLayout.vue'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import Dashboard from '@/pages/Dashboard.vue'

const routes = [
  { path: '/login', name: 'auth.login', component: () => import('@/pages/auth/Login.vue'), meta: { title: 'Sign In', guest: true } },

  // Admin Panel Routes
  {
    path: '/admin',
    component: AdminLayout,
    meta: { auth: 'admin' },
    children: [
      { path: '', name: 'admin.dashboard', component: Dashboard, meta: { title: 'Dashboard', auth: 'admin' } },
      { path: 'users', name: 'admin.users', component: () => import('@/pages/admin/UsersResource.vue'), meta: { title: 'Users', auth: 'admin' } },
      { path: 'roles', name: 'admin.roles', component: () => import('@/pages/admin/RolesResource.vue'), meta: { title: 'Roles', auth: 'admin' } },
      { path: 'countries', name: 'admin.countries', component: () => import('@/pages/admin/CountriesResource.vue'), meta: { title: 'Countries', auth: 'admin' } },
      { path: 'timezones', name: 'admin.timezones', component: () => import('@/pages/admin/TimezonesResource.vue'), meta: { title: 'Timezones', auth: 'admin' } },
      { path: 'email-templates', name: 'admin.email-templates.index', component: () => import('@/pages/admin/EmailTemplates.vue'), meta: { title: 'Email Templates', auth: 'admin' } },
      { path: 'email-templates/create', name: 'admin.email-templates.create', component: () => import('@/pages/admin/EmailTemplateForm.vue'), meta: { title: 'Create Email Template', auth: 'admin' } },
      { path: 'email-templates/:id/edit', name: 'admin.email-templates.edit', component: () => import('@/pages/admin/EmailTemplateForm.vue'), meta: { title: 'Edit Email Template', auth: 'admin' } },

      // Admin Profile Routes
      {
        path: 'profile',
        component: ProfileLayout,
        meta: { auth: 'admin' },
        redirect: { name: 'admin.profile.personal' },
        children: [
          { path: 'personal', name: 'admin.profile.personal', component: () => import('@/pages/admin/profile/Profile.vue'), meta: { title: 'Profile', auth: 'admin' } },
          { path: 'security', name: 'admin.profile.security', component: () => import('@/pages/admin/profile/ChangePassword.vue'), meta: { title: 'Security', auth: 'admin' } }
        ]
      },

      // Admin Settings Routes
      {
        path: 'settings',
        component: SettingsLayout,
        meta: { auth: 'admin' },
        redirect: { name: 'admin.settings.appearance' },
        children: [
          { path: 'appearance', name: 'admin.settings.appearance', component: () => import('@/pages/admin/settings/Appearance.vue'), meta: { title: 'Appearance', auth: 'admin' } },
          { path: 'notifications', name: 'admin.settings.notifications', component: () => import('@/pages/admin/settings/Notifications.vue'), meta: { title: 'Notifications', auth: 'admin' } },
          { path: 'preferences', name: 'admin.settings.preferences', component: () => import('@/pages/admin/settings/Preferences.vue'), meta: { title: 'Preferences', auth: 'admin' } },
          { path: 'global', name: 'admin.settings.global', component: () => import('@/pages/settings/Global.vue'), meta: { title: 'Global Settings', auth: 'admin' } },
          { path: 'system', name: 'admin.settings.system', component: () => import('@/pages/settings/System.vue'), meta: { title: 'System Settings', auth: 'admin' } }
        ]
      },

      // Admin Error Pages
      { path: 'error/404', name: 'admin.error.notFound', component: () => import('@/pages/admin/errors/NotFound.vue'), meta: { title: '404 - Page Not Found', auth: '' } },
      { path: 'error/403', name: 'admin.error.forbidden', component: () => import('@/pages/admin/errors/Forbidden.vue'), meta: { title: '403 - Access Forbidden', auth: '' } },
      { path: 'error/401', name: 'admin.error.unauthorized', component: () => import('@/pages/admin/errors/Unauthorized.vue'), meta: { title: '401 - Unauthorized', auth: '' } },
      { path: 'error/500', name: 'admin.error.serverError', component: () => import('@/pages/admin/errors/ServerError.vue'), meta: { title: '500 - Server Error', auth: '' } },
      { path: 'error/network', name: 'admin.error.networkError', component: () => import('@/pages/admin/errors/NetworkError.vue'), meta: { title: 'Network Error', auth: '' } },
      { path: 'error/maintenance', name: 'admin.error.maintenance', component: () => import('@/pages/admin/errors/MaintenanceMode.vue'), meta: { title: 'Under Maintenance', auth: '' } }
    ]
  },

  // User Panel Routes
  {
    path: '/user',
    component: AdminLayout,
    children: [
      { path: '', name: 'user.dashboard', component: () => import('@/pages/user/Dashboard.vue'), meta: { title: 'Dashboard', auth: 'user' } },

      // User Profile Routes
      {
        path: 'profile',
        component: ProfileLayout,
        meta: { auth: 'user' },
        redirect: { name: 'user.profile.personal' },
        children: [
          { path: 'personal', name: 'user.profile.personal', component: () => import('@/pages/user/profile/Profile.vue'), meta: { title: 'Profile', auth: 'user' } },
          { path: 'security', name: 'user.profile.security', component: () => import('@/pages/user/profile/ChangePassword.vue'), meta: { title: 'Security', auth: 'user' } }
        ]
      },

      // User Settings Routes
      {
        path: 'settings',
        component: SettingsLayout,
        meta: { auth: 'user' },
        redirect: { name: 'user.settings.appearance' },
        children: [
          { path: 'appearance', name: 'user.settings.appearance', component: () => import('@/pages/user/settings/Appearance.vue'), meta: { title: 'Appearance', auth: 'user' } },
          { path: 'notifications', name: 'user.settings.notifications', component: () => import('@/pages/user/settings/Notifications.vue'), meta: { title: 'Notifications', auth: 'user' } },
          { path: 'preferences', name: 'user.settings.preferences', component: () => import('@/pages/user/settings/Preferences.vue'), meta: { title: 'Preferences', auth: 'user' } }
        ]
      },

      // User Error Pages
      { path: 'error/404', name: 'user.error.notFound', component: () => import('@/pages/user/errors/NotFound.vue'), meta: { title: '404 - Page Not Found', auth: '' } },
      { path: 'error/403', name: 'user.error.forbidden', component: () => import('@/pages/user/errors/Forbidden.vue'), meta: { title: '403 - Access Forbidden', auth: '' } },
      { path: 'error/401', name: 'user.error.unauthorized', component: () => import('@/pages/user/errors/Unauthorized.vue'), meta: { title: '401 - Unauthorized', auth: '' } },
      { path: 'error/500', name: 'user.error.serverError', component: () => import('@/pages/user/errors/ServerError.vue'), meta: { title: '500 - Server Error', auth: '' } },
      { path: 'error/network', name: 'user.error.networkError', component: () => import('@/pages/user/errors/NetworkError.vue'), meta: { title: 'Network Error', auth: '' } },
      { path: 'error/maintenance', name: 'user.error.maintenance', component: () => import('@/pages/user/errors/MaintenanceMode.vue'), meta: { title: 'Under Maintenance', auth: '' } },
    ]
  },

  // Auth Routes
  { path: '/auth/register', name: 'auth.register', component: () => import('@/pages/auth/Register.vue'), meta: { title: 'Create Account', guest: true } },
  { path: '/auth/forgot-password', name: 'auth.forgot-password', component: () => import('@/pages/auth/ForgotPassword.vue'), meta: { title: 'Reset Password', guest: true } },
  { path: '/auth/reset-password', name: 'auth.reset-password', component: () => import('@/pages/auth/ResetPassword.vue'), meta: { title: 'Set New Password', guest: true } },
  { path: '/auth/verify-email', name: 'auth.verify-email', component: () => import('@/pages/auth/VerifyEmail.vue'), meta: { title: 'Verify Email', guest: true } },

  // 404 Catch-all for admin paths
  {
    path: '/admin/:pathMatch(.*)*',
    name: 'admin.notFound',
    component: () => import('@/pages/admin/errors/NotFound.vue'),
    meta: { title: '404 - Page Not Found', auth: 'admin' }
  },

  // 404 Catch-all for user paths
  {
    path: '/user/:pathMatch(.*)*',
    name: 'user.notFound',
    component: () => import('@/pages/user/errors/NotFound.vue'),
    meta: { title: '404 - Page Not Found', auth: 'user' }
  },

  // 404 Catch-all for all other paths - redirect to appropriate context
  {
    path: '/:pathMatch(.*)*',
    name: 'notFound',
    redirect: (to) => {
      // Check if user is authenticated and has admin access
      const authStore = useAuthStore()
      if (authStore.isAuthenticated) {
        if (authStore.user?.can_access_admin_panel) {
          return { name: 'admin.error.notFound' }
        }
        return { name: 'user.error.notFound' }
      }
      // For unauthenticated users, show user 404
      return { name: 'user.error.notFound' }
    }
  }
]

const router = createRouter({ history: createWebHistory(), routes })

// Navigation guards
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Update document title
  if (to.meta.title) {
    const suffix = to.path.startsWith('/admin') ? 'Account Panel' :
                   to.path.startsWith('/user') ? 'User Dashboard' :
                   'Laravel Starter'
    document.title = `${to.meta.title} - ${suffix}`
  }

  // Check if route requires authentication (auth meta is set)
  if (to.meta.auth !== undefined) {
    // Ensure user is authenticated for all protected routes
    if (!authStore.isAuthenticated) {
      // No token or user, redirect to login
      next({ name: 'auth.login', query: { redirect: to.fullPath } })
      return
    }

    // Check role-based access
    const requiredAuth = to.meta.auth

    if (requiredAuth === 'admin') {
      // Admin-only route - check whitelist
      if (!authStore.user?.can_access_admin_panel) {
        // Regular users trying to access admin routes -> redirect to user 404
        next({ name: 'user.error.notFound' })
        return
      }
    } else if (requiredAuth === 'user') {
      // User-only route - users with 'user' role only
      if (authStore.user?.can_access_admin_panel) {
        // Admins trying to access user-only routes -> redirect to admin 404
        next({ name: 'admin.error.notFound' })
        return
      }

      // Check if user has 'user' role
      if (!authStore.user?.is_user) {
        next({ name: 'user.error.notFound' })
        return
      }
    }
    // For requiredAuth === '' (common routes), user is authenticated and any role can access

    next()
  }
  // Check if route is guest only (login, register, etc.)
  else if (to.meta.guest) {
    if (authStore.isAuthenticated) {
      // Scenario 3: Logged-in user on dashboard clicks login link - cancel navigation
      // Scenario 4: Logged-in user directly visits /login in new tab - redirect to dashboard

      // Check if navigation was triggered by user action from an existing route
      if (_from.name && !_from.meta?.guest) {
        // Coming from an authenticated route (like dashboard), cancel navigation
        next(false)
      } else {
        // Direct navigation (new tab/refresh) or from another guest route, redirect to dashboard
        const redirectTo = to.query.redirect || { name: authStore.user?.can_access_admin_panel ? 'admin.dashboard' : 'user.dashboard' }
        next(redirectTo)
      }
    } else {
      next()
    }
  }
  // Public route (no auth meta)
  else {
    next()
  }
})

export default router
