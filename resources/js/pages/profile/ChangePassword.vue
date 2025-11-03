<template>
  <div class="space-y-6">
    <div>
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Password Security</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Choose a strong password with at least 8 characters
        </p>
      </div>

      <form @submit.prevent="handlePasswordChange" class="space-y-4">
        <!-- Current Password Field -->
        <div>
          <label for="current_password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Current Password
          </label>
          <div class="relative">
            <input
              id="current_password"
              :value="form.current_password"
              @input="setFieldValue('current_password', $event.target.value)"
              :type="showCurrentPassword ? 'text' : 'password'"
              required
              class="auth-input pr-12"
              :class="{ error: errors.current_password }"
              placeholder="Enter your current password"
              autocomplete="current-password"
            />
            <button
              type="button"
              @click="toggleCurrentPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <Icon :name="showCurrentPassword ? 'eye-off' : 'eye'" :size="20" />
            </button>
          </div>
          <div v-if="errors.current_password" class="auth-error">
            {{ errors.current_password }}
          </div>
        </div>

        <!-- New Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            New Password
          </label>
          <div class="relative">
            <input
              id="password"
              :value="form.password"
              @input="setFieldValue('password', $event.target.value)"
              :type="showPassword ? 'text' : 'password'"
              required
              class="auth-input pr-12"
              :class="{ error: errors.password }"
              placeholder="Create a new password"
              autocomplete="new-password"
            />
            <button
              type="button"
              @click="togglePassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <Icon :name="showPassword ? 'eye-off' : 'eye'" :size="20" />
            </button>
          </div>
          <div v-if="errors.password" class="auth-error">
            {{ errors.password }}
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Confirm New Password
          </label>
          <div class="relative">
            <input
              id="password_confirmation"
              :value="form.password_confirmation"
              @input="setFieldValue('password_confirmation', $event.target.value)"
              :type="showPasswordConfirm ? 'text' : 'password'"
              required
              class="auth-input pr-12"
              :class="{ error: errors.password_confirmation }"
              placeholder="Confirm your new password"
              autocomplete="new-password"
            />
            <button
              type="button"
              @click="togglePasswordConfirm"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <Icon :name="showPasswordConfirm ? 'eye-off' : 'eye'" :size="20" />
            </button>
          </div>
          <div v-if="errors.password_confirmation" class="auth-error">
            {{ errors.password_confirmation }}
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="auth-success bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
          <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ successMessage }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="auth-error bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ errorMessage }}</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="auth-button-primary"
          >
            <span v-if="isSubmitting" class="flex items-center justify-center">
              <Icon name="loading" :size="20" class="animate-spin mr-2" />
              Updating...
            </span>
            <span v-else>Update Password</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Security Tips -->
    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-400 mb-2">Password Security Tips</h3>
        <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-500">
          <li>• Use at least 8 characters with a mix of letters, numbers, and symbols</li>
          <li>• Avoid using personal information or common words</li>
          <li>• Don't reuse passwords from other accounts</li>
          <li>• Consider using a password manager</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useChangePasswordForm } from '@/components/composables/useChangePasswordForm'
import Icon from '@/components/common/Icon.vue'

const {
  onSubmit: handlePasswordChange,
  errors,
  values: form,
  isSubmitting,
  successMessage,
  errorMessage,
  setFieldValue
} = useChangePasswordForm()

const showCurrentPassword = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const toggleCurrentPassword = () => {
  showCurrentPassword.value = !showCurrentPassword.value
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const togglePasswordConfirm = () => {
  showPasswordConfirm.value = !showPasswordConfirm.value
}
</script>
