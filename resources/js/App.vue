<template>
  <div id="app">
    <!-- Only render router-view after auth initialization is complete -->
    <div v-if="authStore.isInitializing" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading...</p>
      </div>
    </div>
    <router-view v-else />
    <ToastContainer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import { settingsService } from '@/services/settingsService'
import ToastContainer from '@/components/common/ToastContainer.vue'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()

// Initialize dark mode and theme on app load
onMounted(async () => {
  // Check localStorage first for immediate application
  const savedMode = localStorage.getItem('darkMode')
  const savedTheme = localStorage.getItem('theme')

  // Apply dark mode
  if (savedMode === 'dark') {
    document.documentElement.classList.add('dark')
  } else if (savedMode === 'light') {
    document.documentElement.classList.remove('dark')
  }

  // Apply theme
  if (savedTheme) {
    settingsStore.applyTheme(savedTheme)
  }

  // If user is authenticated, fetch their preferences from database
  if (authStore.isAuthenticated) {
    try {
      const settings = await settingsService.getUserSettings('appearance')

      // Handle dark mode
      if (settings.data && settings.data.dark_mode !== undefined) {
        const isDark = settings.data.dark_mode

        if (isDark) {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }

        // Sync with localStorage
        localStorage.setItem('darkMode', isDark ? 'dark' : 'light')
      }

      // Handle theme
      if (settings.data && settings.data.user_theme) {
        const theme = settings.data.user_theme
        localStorage.setItem('theme', theme)
        settingsStore.applyTheme(theme)
      }
    } catch (error) {
      console.error('Failed to load user preferences:', error)
    }
  }
})
</script>