<template>
  <button
    @click="toggleDarkMode"
    v-tooltip="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    class="btn-ghost"
    aria-label="Toggle dark mode"
  >
    <Icon v-if="isDark" name="sun" :size="20" class="text-yellow-500" />
    <Icon v-else name="moon" :size="20" class="text-gray-600 dark:text-gray-400" />
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { settingsService } from '@/services/settingsService'
import Icon from '@/components/common/Icon.vue'

const authStore = useAuthStore()
const isDark = ref(false)

onMounted(() => {
  // Check if dark mode is enabled
  isDark.value = document.documentElement.classList.contains('dark')
})

const toggleDarkMode = async () => {
  isDark.value = !isDark.value

  console.log('Toggling dark mode to:', isDark.value)

  // Apply dark mode class to HTML element
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    console.log('Added dark class, current classes:', document.documentElement.className)
  } else {
    document.documentElement.classList.remove('dark')
    console.log('Removed dark class, current classes:', document.documentElement.className)
  }

  // Save to localStorage
  localStorage.setItem('darkMode', isDark.value ? 'dark' : 'light')

  // Save to database if user is authenticated
  if (authStore.isAuthenticated) {
    try {
      await settingsService.updateUserSetting('dark_mode', isDark.value)
      console.log('Saved dark mode preference to database')
    } catch (error) {
      console.error('Failed to save dark mode preference:', error)
    }
  }
}
</script>
