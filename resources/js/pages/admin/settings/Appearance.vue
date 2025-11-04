<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Appearance Settings</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Customize the look and feel of your admin panel
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Theme Selection -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
          <Icon name="palette" :size="20" class="inline mr-2" />
          Select Theme
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <div
            v-for="theme in themes"
            :key="theme.id"
            @click="formData.user_theme = theme.value"
            class="cursor-pointer group relative"
          >
            <div
              class="aspect-square rounded-lg border-2 transition-all"
              :class="formData.user_theme === theme.value
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
                v-if="formData.user_theme === theme.value"
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
          <Icon name="layout" :size="20" class="inline mr-2" />
          Admin Panel Layout
        </label>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Choose your preferred admin panel layout
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="layout in adminLayouts"
            :key="layout.id"
            @click="formData.user_admin_layout = layout.value"
            class="cursor-pointer group"
          >
            <div
              class="border-2 rounded-lg p-4 transition-all"
              :class="formData.user_admin_layout === layout.value
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700'"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">{{ layout.label }}</h4>
                <Icon
                  v-if="formData.user_admin_layout === layout.value"
                  name="check-circle"
                  :size="20"
                  class="text-primary-500"
                />
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ layout.metadata?.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Items Per Page -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <label for="items_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          <Icon name="list" :size="20" class="inline mr-2" />
          Items Per Page
        </label>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Number of items to display per page in tables
        </p>
        <select
          id="items_per_page"
          v-model.number="formData.items_per_page"
          class="form-select w-full md:w-64"
        >
          <option :value="10">10 items</option>
          <option :value="25">25 items</option>
          <option :value="50">50 items</option>
          <option :value="100">100 items</option>
        </select>
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
        <p class="text-sm font-medium text-green-600 dark:text-green-400">Settings saved successfully!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'
import { useToast } from '@/composables/useToast'

const settingsStore = useSettingsStore()
const { showToast } = useToast()

const isLoading = ref(true)
const isSaving = ref(false)
const showSuccess = ref(false)
const themes = ref([])
const adminLayouts = ref([])

const formData = ref({
  user_theme: 'ocean',
  user_admin_layout: 'classic',
  items_per_page: 25
})

const originalData = ref({})

const hasChanges = computed(() => {
  return JSON.stringify(formData.value) !== JSON.stringify(originalData.value)
})

onMounted(async () => {
  try {
    isLoading.value = true

    // Load themes and layouts
    await Promise.all([
      settingsStore.loadThemes(),
      settingsStore.loadAdminLayouts(),
      settingsStore.loadUserSettings('appearance')
    ])

    themes.value = settingsStore.themes
    adminLayouts.value = settingsStore.adminLayouts

    // Set form data from store
    formData.value = {
      user_theme: settingsStore.userSettings.user_theme || 'ocean',
      user_admin_layout: settingsStore.userSettings.user_admin_layout || 'classic',
      items_per_page: settingsStore.userSettings.items_per_page || 25
    }

    originalData.value = { ...formData.value }
  } catch (error) {
    console.error('Failed to load appearance settings:', error)
    showToast({ message: 'Failed to load settings', type: 'error' })
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
    showToast({ message: 'Appearance settings saved successfully!', type: 'success' })

    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to save settings:', error)
    showToast({ message: 'Failed to save settings', type: 'error' })
  } finally {
    isSaving.value = false
  }
}

function getThemePreviewClass(themeValue) {
  const themeColors = {
    default: 'text-violet-500',
    ocean: 'text-blue-500',
    sunset: 'text-orange-500',
    forest: 'text-green-600',
    midnight: 'text-indigo-600',
    crimson: 'text-red-500',
    amber: 'text-amber-500',
    slate: 'text-slate-500',
    lavender: 'text-purple-400'
  }
  return themeColors[themeValue] || themeColors.default
}
</script>
