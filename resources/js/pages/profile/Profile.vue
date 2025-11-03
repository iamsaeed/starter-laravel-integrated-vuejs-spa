<template>
  <div class="space-y-6">
    <!-- Profile Update Form -->
    <div>
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Personal Information</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Update your name and email address
        </p>
      </div>

      <form @submit.prevent="handleProfileUpdate" class="space-y-4">
        <!-- Name Field -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Full Name
          </label>
          <input
            id="name"
            :value="profileForm.name"
            @input="setProfileField('name', $event.target.value)"
            type="text"
            required
            class="auth-input"
            :class="{ error: profileErrors.name }"
            placeholder="Enter your full name"
            autocomplete="name"
          />
          <div v-if="profileErrors.name" class="auth-error">
            {{ profileErrors.name }}
          </div>
        </div>

        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Email Address
          </label>
          <input
            id="email"
            :value="profileForm.email"
            @input="setProfileField('email', $event.target.value)"
            type="email"
            required
            class="auth-input"
            :class="{ error: profileErrors.email }"
            placeholder="Enter your email address"
            autocomplete="email"
          />
          <div v-if="profileErrors.email" class="auth-error">
            {{ profileErrors.email }}
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="profileSuccessMessage" class="auth-success bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
          <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ profileSuccessMessage }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="profileErrorMessage" class="auth-error bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ profileErrorMessage }}</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="isProfileSubmitting"
            class="auth-button-primary"
          >
            <span v-if="isProfileSubmitting" class="flex items-center justify-center">
              <Icon name="loading" :size="20" class="animate-spin mr-2" />
              Updating...
            </span>
            <span v-else>Update Profile</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Session Management -->
    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Session Management</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage your active sessions across devices
        </p>
      </div>

      <div class="space-y-4">
        <!-- Logout Other Sessions -->
        <div class="flex items-start justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
          <div class="flex-1">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Logout Other Sessions</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Sign out from all other devices while keeping this session active
            </p>
          </div>
          <button
            @click="handleLogoutOtherSessions"
            :disabled="isLoggingOut"
            class="ml-4 px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors whitespace-nowrap"
          >
            <span v-if="isLoggingOut">Logging out...</span>
            <span v-else>Logout Others</span>
          </button>
        </div>

        <!-- Logout All Sessions -->
        <div class="flex items-start justify-between p-4 border border-red-200 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/10">
          <div class="flex-1">
            <h3 class="text-sm font-medium text-red-900 dark:text-red-400">Logout All Sessions</h3>
            <p class="mt-1 text-sm text-red-700 dark:text-red-500">
              Sign out from all devices including this one. You will be redirected to login.
            </p>
          </div>
          <button
            @click="handleLogoutAllSessions"
            :disabled="isLoggingOut"
            class="ml-4 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors whitespace-nowrap"
          >
            <span v-if="isLoggingOut">Logging out...</span>
            <span v-else>Logout All</span>
          </button>
        </div>

        <!-- Session Success Message -->
        <div v-if="sessionSuccessMessage" class="auth-success bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
          <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ sessionSuccessMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProfileForm } from '@/components/composables/useProfileForm'
import Icon from '@/components/common/Icon.vue'

const router = useRouter()
const authStore = useAuthStore()

// Profile update form
const {
  onSubmit: handleProfileUpdate,
  errors: profileErrors,
  values: profileForm,
  isSubmitting: isProfileSubmitting,
  successMessage: profileSuccessMessage,
  errorMessage: profileErrorMessage,
  setFieldValue: setProfileField
} = useProfileForm()

// Session management
const isLoggingOut = ref(false)
const sessionSuccessMessage = ref('')

const handleLogoutOtherSessions = async () => {
  if (!confirm('Are you sure you want to logout from all other sessions?')) {
    return
  }

  isLoggingOut.value = true
  sessionSuccessMessage.value = ''

  try {
    await authStore.logoutOtherSessions()
    sessionSuccessMessage.value = 'Successfully logged out from all other sessions.'

    setTimeout(() => {
      sessionSuccessMessage.value = ''
    }, 5000)
  } catch (error) {
    alert('Failed to logout other sessions. Please try again.')
  } finally {
    isLoggingOut.value = false
  }
}

const handleLogoutAllSessions = async () => {
  if (!confirm('Are you sure you want to logout from all sessions including this one?')) {
    return
  }

  isLoggingOut.value = true
  sessionSuccessMessage.value = ''

  try {
    await authStore.logoutAllSessions()
    router.push({ name: 'auth.login' })
  } catch (error) {
    alert('Failed to logout all sessions. Please try again.')
    isLoggingOut.value = false
  }
}
</script>
