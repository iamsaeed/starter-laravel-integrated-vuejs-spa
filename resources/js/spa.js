import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router/index.js'
import App from './App.vue'
import { useAuthStore } from './stores/auth'
import tooltip from './directives/tooltip'

// Import Tailwind CSS
import '../css/app.css'

// Apply cached layout/theme immediately to prevent FOUC (Flash of Unstyled Content)
// NOTE: This is only a temporary visual fix - database is the source of truth
const applyCachedSettings = () => {
  // Apply cached layout
  const cachedLayout = localStorage.getItem('adminLayout') || 'classic'
  const layoutNames = ['classic', 'horizontal', 'compact', 'mini']
  layoutNames.forEach(layout => {
    document.documentElement.classList.remove(`layout-${layout}`)
  })
  document.documentElement.classList.add(`layout-${cachedLayout}`)
  document.documentElement.setAttribute('data-layout', cachedLayout)

  // Apply cached theme
  const cachedTheme = localStorage.getItem('theme') || 'ocean'
  const themeNames = ['default', 'ocean', 'sunset', 'forest', 'midnight', 'crimson', 'amber', 'slate', 'lavender']
  themeNames.forEach(theme => {
    document.documentElement.classList.remove(`theme-${theme}`)
  })
  document.documentElement.classList.add(`theme-${cachedTheme}`)
  document.documentElement.setAttribute('data-theme', cachedTheme)
}

// Execute immediately before Vue app initialization
applyCachedSettings()

// Create Pinia instance
const pinia = createPinia()

// Create Vue app
const app = createApp(App)

// Register global directives
app.directive('tooltip', tooltip)

// Use Pinia and router
app.use(pinia)
app.use(router)

// Initialize stores
import { useSettingsStore } from './stores/settings'
const settingsStore = useSettingsStore()
const authStore = useAuthStore()
authStore.initAuth()

// Fetch user data and settings if token exists (before mounting the app)
// IMPORTANT: Wait for user fetch to complete before mounting to prevent redirect on refresh
const initializeApp = async () => {
  if (authStore.token) {
    try {
      // Load user data (this now includes settings and impersonation status)
      await authStore.initializeUser()

      // Apply theme and layout from cached settings (already loaded by initializeUser)
      await settingsStore.initTheme()
      await settingsStore.initLayout()
    } catch (error) {
      // If user fetch fails, the auth store will handle logout
      // Router will redirect to login when navigation occurs
      console.error('Failed to initialize app:', error)
    }
  }

  // Mount the app after user is fetched (or if no token exists)
  app.mount('#app')
}

initializeApp()