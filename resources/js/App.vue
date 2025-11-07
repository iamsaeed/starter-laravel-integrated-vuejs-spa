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
import ToastContainer from '@/components/common/ToastContainer.vue'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()

// Initialize dark mode and theme on app load
onMounted(async () => {
  // Only apply cached settings if user is authenticated
  // The auth store will apply fresh settings after login/fetch
  if (authStore.isAuthenticated) {
    // Check localStorage first for immediate application (prevents FOUC)
    const savedMode = localStorage.getItem('setting_dark_mode')
    const savedTheme = localStorage.getItem('setting_user_theme')
    const savedLayout = localStorage.getItem('setting_user_admin_layout')

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

    // Apply layout
    if (savedLayout) {
      settingsStore.applyLayout(savedLayout)
    }
  }

  // Settings are already loaded from /api/me or /api/login and cached
  // No need to fetch them again on page refresh
})
</script>