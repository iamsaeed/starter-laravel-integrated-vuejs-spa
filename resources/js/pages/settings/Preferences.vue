<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Preferences</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Customize your application preferences and regional settings
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="settingsStore.isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Display Preferences -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
            <Icon name="settings" :size="20" class="mr-2" />
            Display Preferences
          </h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Items Per Page -->
          <div>
            <label for="items_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Items Per Page
            </label>
            <select
              id="items_per_page"
              v-model.number="form.items_per_page"
              class="form-select"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Number of items to display per page in lists and tables
            </p>
          </div>

          <!-- Date Format -->
          <div>
            <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Date Format
            </label>
            <select
              id="date_format"
              v-model="form.date_format"
              class="form-select"
            >
              <option value="MM/DD/YYYY">MM/DD/YYYY (12/31/2025)</option>
              <option value="DD/MM/YYYY">DD/MM/YYYY (31/12/2025)</option>
              <option value="YYYY-MM-DD">YYYY-MM-DD (2025-12-31)</option>
              <option value="DD MMM YYYY">DD MMM YYYY (31 Dec 2025)</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              How dates are displayed throughout the application
            </p>
          </div>

          <!-- Time Format -->
          <div>
            <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Time Format
            </label>
            <select
              id="time_format"
              v-model="form.time_format"
              class="form-select"
            >
              <option value="12h">12-hour (2:30 PM)</option>
              <option value="24h">24-hour (14:30)</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              How times are displayed throughout the application
            </p>
          </div>
        </div>
      </div>

      <!-- Regional Settings -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
            <Icon name="globe" :size="20" class="mr-2" />
            Regional Settings
          </h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Language -->
          <div>
            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Language
            </label>
            <select
              id="language"
              v-model="form.language"
              class="form-select"
            >
              <option value="en">English</option>
              <option value="es">Spanish</option>
              <option value="fr">French</option>
              <option value="de">German</option>
              <option value="ja">Japanese</option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Select your preferred language
            </p>
          </div>

          <!-- Timezone -->
          <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Timezone
            </label>
            <select
              id="timezone"
              v-model="form.timezone"
              class="form-select"
            >
              <option
                v-for="timezone in settingsStore.timezones"
                :key="timezone.id"
                :value="timezone.id"
              >
                {{ timezone.display_name }} ({{ timezone.offset_formatted }})
              </option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Your local timezone for displaying dates and times
            </p>
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
      Preferences saved successfully
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
  items_per_page: 25,
  date_format: 'MM/DD/YYYY',
  time_format: '12h',
  language: 'en',
  timezone: null
})

// Check if there are unsaved changes
const hasChanges = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(initialForm.value)
})

// Load settings on mount
onMounted(async () => {
  await Promise.all([
    settingsStore.loadUserSettings(),
    settingsStore.loadTimezones()
  ])

  // Populate form with current settings
  form.value = {
    items_per_page: settingsStore.userSettings.items_per_page || 25,
    date_format: settingsStore.userSettings.date_format || 'MM/DD/YYYY',
    time_format: settingsStore.userSettings.time_format || '12h',
    language: settingsStore.userSettings.language || 'en',
    timezone: settingsStore.userSettings.timezone || null
  }

  initialForm.value = { ...form.value }
})

// Handle save
const handleSave = async () => {
  try {
    await settingsStore.updateUserSettings(form.value)
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
