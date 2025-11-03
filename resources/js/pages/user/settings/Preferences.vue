<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Preferences</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Configure your personal preferences
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Localization -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          <Icon name="globe" :size="20" class="inline mr-2" />
          Localization
        </h3>

        <!-- Country -->
        <div>
          <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Country
          </label>
          <select
            id="country"
            v-model="formData.country"
            class="form-select w-full"
          >
            <option value="">Select a country</option>
            <option v-for="country in countries" :key="country.id" :value="country.code">
              {{ country.flag_emoji }} {{ country.name }}
            </option>
          </select>
        </div>

        <!-- Timezone -->
        <div>
          <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Timezone
          </label>
          <select
            id="timezone"
            v-model="formData.timezone"
            class="form-select w-full"
          >
            <option value="">Select a timezone</option>
            <option v-for="timezone in timezones" :key="timezone.id" :value="timezone.name">
              {{ timezone.display_name }} ({{ timezone.offset_formatted }})
            </option>
          </select>
        </div>

        <!-- Language -->
        <div>
          <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Language
          </label>
          <select
            id="language"
            v-model="formData.language"
            class="form-select w-full"
          >
            <option value="en">English</option>
            <option value="es">Español</option>
            <option value="fr">Français</option>
            <option value="de">Deutsch</option>
            <option value="it">Italiano</option>
            <option value="pt">Português</option>
            <option value="ja">日本語</option>
            <option value="zh">中文</option>
          </select>
        </div>
      </div>

      <!-- Date & Time Format -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          <Icon name="calendar" :size="20" class="inline mr-2" />
          Date & Time Format
        </h3>

        <!-- Date Format -->
        <div>
          <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Date Format
          </label>
          <select
            id="date_format"
            v-model="formData.date_format"
            class="form-select w-full"
          >
            <option value="Y-m-d">YYYY-MM-DD ({{ formatDateExample('Y-m-d') }})</option>
            <option value="m/d/Y">MM/DD/YYYY ({{ formatDateExample('m/d/Y') }})</option>
            <option value="d/m/Y">DD/MM/YYYY ({{ formatDateExample('d/m/Y') }})</option>
            <option value="d.m.Y">DD.MM.YYYY ({{ formatDateExample('d.m.Y') }})</option>
            <option value="M d, Y">Mon DD, YYYY ({{ formatDateExample('M d, Y') }})</option>
            <option value="F d, Y">Month DD, YYYY ({{ formatDateExample('F d, Y') }})</option>
          </select>
        </div>

        <!-- Time Format -->
        <div>
          <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Time Format
          </label>
          <select
            id="time_format"
            v-model="formData.time_format"
            class="form-select w-full"
          >
            <option value="H:i">24-hour ({{ formatTimeExample('H:i') }})</option>
            <option value="h:i A">12-hour ({{ formatTimeExample('h:i A') }})</option>
            <option value="h:i a">12-hour lowercase ({{ formatTimeExample('h:i a') }})</option>
          </select>
        </div>

        <!-- Week Start -->
        <div>
          <label for="week_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Week Starts On
          </label>
          <select
            id="week_start"
            v-model.number="formData.week_start"
            class="form-select w-full"
          >
            <option :value="0">Sunday</option>
            <option :value="1">Monday</option>
            <option :value="6">Saturday</option>
          </select>
        </div>
      </div>

      <!-- Interface Preferences -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          <Icon name="sliders" :size="20" class="inline mr-2" />
          Interface Preferences
        </h3>

        <!-- Compact Mode -->
        <div class="flex items-center justify-between">
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Compact Mode</label>
            <p class="text-sm text-gray-600 dark:text-gray-400">Use a more compact interface with less spacing</p>
          </div>
          <ToggleSwitch v-model="formData.compact_mode" />
        </div>

        <!-- Show Avatars -->
        <div class="flex items-center justify-between">
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Avatars</label>
            <p class="text-sm text-gray-600 dark:text-gray-400">Display user avatars throughout the interface</p>
          </div>
          <ToggleSwitch v-model="formData.show_avatars" />
        </div>

        <!-- Animations -->
        <div class="flex items-center justify-between">
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Animations</label>
            <p class="text-sm text-gray-600 dark:text-gray-400">Use animations and transitions in the interface</p>
          </div>
          <ToggleSwitch v-model="formData.enable_animations" />
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          type="submit"
          :disabled="isSaving || !hasChanges"
          class="btn-primary"
          :class="{ 'opacity-50 cursor-not-allowed': isSaving || !hasChanges }"
        >
          <Icon v-if="isSaving" name="spinner" :size="20" class="inline mr-2 animate-spin" />
          <Icon v-else name="save" :size="20" class="inline mr-2" />
          {{ isSaving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>

    <!-- Success Message -->
    <div
      v-if="showSuccess"
      class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
    >
      <div class="flex items-center">
        <Icon name="check-circle" :size="20" class="text-green-600 dark:text-green-400 mr-2" />
        <p class="text-sm font-medium text-green-600 dark:text-green-400">Preferences saved successfully!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'
import { useToast } from '@/composables/useToast'

const settingsStore = useSettingsStore()
const { showToast } = useToast()

const isLoading = ref(true)
const isSaving = ref(false)
const showSuccess = ref(false)
const countries = ref([])
const timezones = ref([])

const formData = ref({
  country: 'US',
  timezone: 'America/New_York',
  language: 'en',
  date_format: 'Y-m-d',
  time_format: 'H:i',
  week_start: 0,
  compact_mode: false,
  show_avatars: true,
  enable_animations: true
})

const originalData = ref({})

const hasChanges = computed(() => {
  return JSON.stringify(formData.value) !== JSON.stringify(originalData.value)
})

onMounted(async () => {
  try {
    isLoading.value = true
    
    // Load countries, timezones, and user preferences
    await Promise.all([
      settingsStore.loadCountries(),
      settingsStore.loadTimezones(),
      settingsStore.loadUserSettings('preferences')
    ])
    
    countries.value = settingsStore.countries
    timezones.value = settingsStore.timezones
    
    // Set form data from store with defaults
    formData.value = {
      country: settingsStore.userSettings.country || 'US',
      timezone: settingsStore.userSettings.timezone || 'America/New_York',
      language: settingsStore.userSettings.language || 'en',
      date_format: settingsStore.userSettings.date_format || 'Y-m-d',
      time_format: settingsStore.userSettings.time_format || 'H:i',
      week_start: settingsStore.userSettings.week_start ?? 0,
      compact_mode: settingsStore.userSettings.compact_mode ?? false,
      show_avatars: settingsStore.userSettings.show_avatars ?? true,
      enable_animations: settingsStore.userSettings.enable_animations ?? true
    }
    
    originalData.value = { ...formData.value }
  } catch (error) {
    console.error('Failed to load preferences:', error)
    showToast({ message: 'Failed to load preferences', type: 'error' })
  } finally {
    isLoading.value = false
  }
})

async function handleSave() {
  try {
    isSaving.value = true
    showSuccess.value = false
    
    await settingsStore.updateUserSettings(formData.value)
    
    originalData.value = { ...formData.value }
    showSuccess.value = true
    showToast({ message: 'Preferences saved successfully!', type: 'success' })
    
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to save preferences:', error)
    showToast({ message: 'Failed to save preferences', type: 'error' })
  } finally {
    isSaving.value = false
  }
}

// Helper functions for date/time format examples
function formatDateExample(format) {
  const now = new Date()
  const examples = {
    'Y-m-d': '2025-10-18',
    'm/d/Y': '10/18/2025',
    'd/m/Y': '18/10/2025',
    'd.m.Y': '18.10.2025',
    'M d, Y': 'Oct 18, 2025',
    'F d, Y': 'October 18, 2025'
  }
  return examples[format] || format
}

function formatTimeExample(format) {
  const examples = {
    'H:i': '14:30',
    'h:i A': '02:30 PM',
    'h:i a': '02:30 pm'
  }
  return examples[format] || format
}
</script>
