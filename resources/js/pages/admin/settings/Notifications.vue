<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Notification Settings</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Manage your notification preferences for the admin panel
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Email Notifications -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <Icon name="mail" :size="20" class="text-gray-600 dark:text-gray-400 mr-2" />
            <div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Email Notifications</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Receive notifications via email</p>
            </div>
          </div>
          <ToggleSwitch v-model="formData.notifications_email" />
        </div>

        <!-- Email Notification Types (shown when email is enabled) -->
        <div v-if="formData.notifications_email" class="mt-4 pl-8 space-y-3">
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">Task assignments</span>
            <ToggleSwitch v-model="formData.notifications_email_task_assignments" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">System updates</span>
            <ToggleSwitch v-model="formData.notifications_email_system_updates" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">Marketing emails</span>
            <ToggleSwitch v-model="formData.notifications_email_marketing" />
          </label>
        </div>
      </div>

      <!-- Push Notifications -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <Icon name="bell" :size="20" class="text-gray-600 dark:text-gray-400 mr-2" />
            <div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Push Notifications</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Receive browser push notifications</p>
            </div>
          </div>
          <ToggleSwitch v-model="formData.notifications_push" />
        </div>

        <!-- Push Notification Types (shown when push is enabled) -->
        <div v-if="formData.notifications_push" class="mt-4 pl-8 space-y-3">
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">New messages</span>
            <ToggleSwitch v-model="formData.notifications_push_messages" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">Task updates</span>
            <ToggleSwitch v-model="formData.notifications_push_task_updates" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">Mentions</span>
            <ToggleSwitch v-model="formData.notifications_push_mentions" />
          </label>
        </div>
      </div>

      <!-- Desktop Notifications -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <Icon name="monitor" :size="20" class="text-gray-600 dark:text-gray-400 mr-2" />
            <div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Desktop Notifications</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Show desktop notifications when app is open</p>
            </div>
          </div>
          <ToggleSwitch v-model="formData.notifications_desktop" />
        </div>
      </div>

      <!-- Notification Sound -->
      <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <Icon name="volume-2" :size="20" class="text-gray-600 dark:text-gray-400 mr-2" />
            <div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Notification Sounds</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Play sound when receiving notifications</p>
            </div>
          </div>
          <ToggleSwitch v-model="formData.notifications_sound" />
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
        <p class="text-sm font-medium text-green-600 dark:text-green-400">Notification settings saved successfully!</p>
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

const formData = ref({
  notifications_email: true,
  notifications_email_task_assignments: true,
  notifications_email_system_updates: true,
  notifications_email_marketing: false,
  notifications_push: true,
  notifications_push_messages: true,
  notifications_push_task_updates: true,
  notifications_push_mentions: true,
  notifications_desktop: true,
  notifications_sound: true
})

const originalData = ref({})

const hasChanges = computed(() => {
  return JSON.stringify(formData.value) !== JSON.stringify(originalData.value)
})

onMounted(async () => {
  try {
    isLoading.value = true

    // Load user notification settings
    await settingsStore.loadUserSettings('notifications')

    // Set form data from store with defaults
    formData.value = {
      notifications_email: settingsStore.userSettings.notifications_email ?? true,
      notifications_email_task_assignments: settingsStore.userSettings.notifications_email_task_assignments ?? true,
      notifications_email_system_updates: settingsStore.userSettings.notifications_email_system_updates ?? true,
      notifications_email_marketing: settingsStore.userSettings.notifications_email_marketing ?? false,
      notifications_push: settingsStore.userSettings.notifications_push ?? true,
      notifications_push_messages: settingsStore.userSettings.notifications_push_messages ?? true,
      notifications_push_task_updates: settingsStore.userSettings.notifications_push_task_updates ?? true,
      notifications_push_mentions: settingsStore.userSettings.notifications_push_mentions ?? true,
      notifications_desktop: settingsStore.userSettings.notifications_desktop ?? true,
      notifications_sound: settingsStore.userSettings.notifications_sound ?? true
    }

    originalData.value = { ...formData.value }
  } catch (error) {
    console.error('Failed to load notification settings:', error)
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
    showToast({ message: 'Notification settings saved successfully!', type: 'success' })

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
</script>
