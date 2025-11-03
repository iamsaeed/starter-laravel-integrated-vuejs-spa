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

      <ProfileUpdateForm
        :form="profileForm"
        :errors="profileErrors"
        :is-submitting="isProfileSubmitting"
        :success-message="profileSuccessMessage"
        :error-message="profileErrorMessage"
        @submit="handleProfileUpdate"
        @update:field="setProfileField"
      />
    </div>

    <!-- Session Management -->
    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Session Management</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage your active sessions across devices
        </p>
      </div>

      <SessionManagement
        :is-logging-out="isLoggingOut"
        :success-message="sessionSuccessMessage"
        @logout-others="handleLogoutOtherSessions"
        @logout-all="handleLogoutAllSessions"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProfileForm } from '@/components/composables/useProfileForm'
import ProfileUpdateForm from '@/components/profile/ProfileUpdateForm.vue'
import SessionManagement from '@/components/profile/SessionManagement.vue'

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
