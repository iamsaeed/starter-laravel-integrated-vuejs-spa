<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Appearance</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Customize the look and feel of your application
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="settingsStore.isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Theme Selection -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
          <Icon name="palette" :size="20" class="mr-2" />
          Select Theme
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <div
            v-for="theme in settingsStore.themes"
            :key="theme.id"
            @click="selectedTheme = theme.value"
            class="cursor-pointer group relative"
          >
            <div
              class="aspect-square rounded-lg border-2 transition-all"
              :class="selectedTheme === theme.value
                ? 'border-primary-500 ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-800'
                : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700'"
            >
              <!-- Theme Preview -->
              <div class="h-full w-full rounded-md overflow-hidden p-2">
                <div class="h-full w-full rounded flex flex-col gap-1" :class="getThemePreviewClass(theme.value)">
                  <div class="h-2 bg-current opacity-90 rounded"></div>
                  <div class="h-2 w-3/4 bg-current opacity-70 rounded"></div>
                  <div class="h-2 w-1/2 bg-current opacity-50 rounded"></div>
                </div>
              </div>

              <!-- Selected Indicator -->
              <div
                v-if="selectedTheme === theme.value"
                class="absolute -top-2 -right-2 bg-primary-500 text-white rounded-full p-1"
              >
                <Icon name="check" :size="16" />
              </div>
            </div>

            <!-- Theme Name -->
            <p class="mt-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
              {{ theme.label }}
            </p>
          </div>
        </div>
      </div>

      <!-- Admin Layout Selection -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          <Icon name="layout" :size="20" class="mr-2" />
          Admin Panel Layout
        </label>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Choose your preferred admin panel layout
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <LayoutOption
            v-for="layout in settingsStore.adminLayouts"
            :key="layout.id"
            :layout="layout"
            :is-active="selectedLayout === layout.value"
            @select="selectedLayout = layout.value"
          />
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          type="submit"
          :disabled="settingsStore.isSaving || !hasChanges"
          class="btn-primary"
          :class="{ 'opacity-50 cursor-not-allowed': settingsStore.isSaving || !hasChanges }"
        >
          <Icon v-if="settingsStore.isSaving" name="spinner" :size="20" class="mr-2 animate-spin" />
          <Icon v-else name="save" :size="20" class="mr-2" />
          {{ settingsStore.isSaving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>

    <!-- Success Message -->
    <div
      v-if="showSuccess"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center animate-fade-in"
    >
      <Icon name="check-circle" :size="20" class="mr-2" />
      Settings saved successfully
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'
import LayoutOption from '@/components/settings/LayoutOption.vue'

const settingsStore = useSettingsStore()
// Themes with their color schemes for preview
const selectedTheme = ref('ocean')
const selectedLayout = ref('classic')
const showSuccess = ref(false)

// Check if there are unsaved changes
const hasChanges = computed(() => {
  return selectedTheme.value !== settingsStore.currentTheme ||
         selectedLayout.value !== settingsStore.currentAdminLayout
})

// Get theme preview colors
const getThemePreviewClass = (theme) => {
  const colors = {
    default: 'text-gray-800',
    ocean: 'text-blue-500',
    sunset: 'text-orange-500',
    forest: 'text-green-600',
    midnight: 'text-indigo-900',
    crimson: 'text-red-600',
    amber: 'text-yellow-600',
    slate: 'text-slate-600',
    lavender: 'text-purple-500'
  }
  return colors[theme] || colors.default
}

// Load settings on mount
onMounted(async () => {
  await settingsStore.loadUserSettings('appearance')
  await settingsStore.loadThemes()
  await settingsStore.loadAdminLayouts()
  selectedTheme.value = settingsStore.currentTheme
  selectedLayout.value = settingsStore.currentAdminLayout
})

// Handle save
const handleSave = async () => {
  try {
    // Save both theme and layout if changed
    if (selectedTheme.value !== settingsStore.currentTheme) {
      await settingsStore.updateUserSetting('user_theme', selectedTheme.value)
    }
    if (selectedLayout.value !== settingsStore.currentAdminLayout) {
      await settingsStore.updateUserLayout(selectedLayout.value)
    }

    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to save settings:', error)
  }
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
