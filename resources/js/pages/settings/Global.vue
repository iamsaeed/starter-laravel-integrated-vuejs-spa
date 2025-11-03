<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Global Settings</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage application-wide settings and defaults
          </p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
          <Icon name="shield" :size="14" class="mr-1" />
          Admin Only
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="settingsStore.isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- General Settings -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
            <Icon name="globe" :size="20" class="mr-2" />
            General Settings
          </h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Site Name -->
          <div>
            <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Site Name
            </label>
            <input
              type="text"
              id="site_name"
              v-model="form.site_name"
              class="form-input"
              placeholder="My Application"
            />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              The name of your application
            </p>
          </div>

          <!-- Site Description -->
          <div>
            <label for="site_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Site Description
            </label>
            <textarea
              id="site_description"
              v-model="form.site_description"
              rows="3"
              class="form-input"
              placeholder="Enter a brief description..."
            ></textarea>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              A brief description of your application
            </p>
          </div>

          <!-- Default Theme -->
          <div>
            <label for="default_theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Default Theme
            </label>
            <select
              id="default_theme"
              v-model="form.default_theme"
              class="form-select"
            >
              <option
                v-for="theme in settingsStore.themes"
                :key="theme.id"
                :value="theme.value"
              >
                {{ theme.label }}
              </option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Default theme for new users
            </p>
          </div>
        </div>
      </div>

      <!-- System Configuration -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
            <Icon name="server" :size="20" class="mr-2" />
            System Configuration
          </h3>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Maintenance Mode -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="maintenance_mode" class="text-sm font-medium text-gray-900 dark:text-white">
                Maintenance Mode
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Put the site in maintenance mode
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="form.maintenance_mode = !form.maintenance_mode"
                :class="form.maintenance_mode
                  ? 'bg-red-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.maintenance_mode ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>

          <!-- User Registration -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="allow_registration" class="text-sm font-medium text-gray-900 dark:text-white">
                Allow User Registration
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Enable public user registration
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="form.allow_registration = !form.allow_registration"
                :class="form.allow_registration
                  ? 'bg-primary-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.allow_registration ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>
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
      Global settings saved successfully
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'

const settingsStore = useSettingsStore()
const showSuccess = ref(false)
const initialForm = ref({})

const form = ref({
  site_name: '',
  site_description: '',
  default_theme: 'ocean',
  maintenance_mode: false,
  allow_registration: true
})

// Check if there are unsaved changes
const hasChanges = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(initialForm.value)
})

// Load settings on mount
onMounted(async () => {
  await Promise.all([
    settingsStore.loadGlobalSettings('general'),
    settingsStore.loadThemes()
  ])

  // Populate form with current settings
  form.value = {
    site_name: settingsStore.globalSettings.site_name || '',
    site_description: settingsStore.globalSettings.site_description || '',
    default_theme: settingsStore.globalSettings.default_theme || 'ocean',
    maintenance_mode: settingsStore.globalSettings.maintenance_mode || false,
    allow_registration: settingsStore.globalSettings.allow_registration ?? true
  }

  initialForm.value = { ...form.value }
})

// Handle save
const handleSave = async () => {
  try {
    // Update each setting
    for (const [key, value] of Object.entries(form.value)) {
      await settingsStore.updateGlobalSetting(key, value)
    }

    initialForm.value = { ...form.value }
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
