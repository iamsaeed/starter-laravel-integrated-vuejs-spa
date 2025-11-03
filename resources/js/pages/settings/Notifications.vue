<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Notifications</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Manage how you receive notifications and updates
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="settingsStore.isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Settings Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-6">
      <!-- Notification Preferences -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
            <Icon name="bell" :size="20" class="mr-2" />
            Notification Preferences
          </h3>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Enable Notifications -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="notifications_enabled" class="text-sm font-medium text-gray-900 dark:text-white">
                Enable Notifications
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Receive notifications about important updates and activities
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="toggleSwitch('notifications_enabled')"
                :class="form.notifications_enabled
                  ? 'bg-primary-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.notifications_enabled ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>

          <!-- Email Notifications -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="email_notifications" class="text-sm font-medium text-gray-900 dark:text-white">
                Email Notifications
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Receive notifications via email
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="toggleSwitch('email_notifications')"
                :class="form.email_notifications
                  ? 'bg-primary-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.email_notifications ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>

          <!-- Push Notifications -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="push_notifications" class="text-sm font-medium text-gray-900 dark:text-white">
                Push Notifications
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Receive push notifications in your browser
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="toggleSwitch('push_notifications')"
                :class="form.push_notifications
                  ? 'bg-primary-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.push_notifications ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>

          <!-- Marketing Emails -->
          <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex-1">
              <label for="marketing_emails" class="text-sm font-medium text-gray-900 dark:text-white">
                Marketing Emails
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Receive emails about new features and updates
              </p>
            </div>
            <div class="ml-4">
              <button
                type="button"
                @click="toggleSwitch('marketing_emails')"
                :class="form.marketing_emails
                  ? 'bg-primary-600'
                  : 'bg-gray-200 dark:bg-gray-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <span
                  :class="form.marketing_emails ? 'translate-x-5' : 'translate-x-0'"
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
      Notification settings saved successfully
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
  notifications_enabled: true,
  email_notifications: true,
  push_notifications: false,
  marketing_emails: false
})

// Check if there are unsaved changes
const hasChanges = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(initialForm.value)
})

// Toggle switch
const toggleSwitch = (key) => {
  form.value[key] = !form.value[key]
}

// Load settings on mount
onMounted(async () => {
  await settingsStore.loadUserSettings('notifications')

  // Populate form with current settings
  form.value = {
    notifications_enabled: settingsStore.userSettings.notifications_enabled ?? true,
    email_notifications: settingsStore.userSettings.email_notifications ?? true,
    push_notifications: settingsStore.userSettings.push_notifications ?? false,
    marketing_emails: settingsStore.userSettings.marketing_emails ?? false
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
