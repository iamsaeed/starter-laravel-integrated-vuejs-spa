<template>
  <AuthPage
    title="Set New Password"
    description="Enter your new password below to complete the reset process"
    :help-text="helpText"
  >
    <template #form>
      <form @submit.prevent="handleResetPassword" class="space-y-4">
        <!-- Token Display (for demo purposes) -->
        <div v-if="token" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
          <p class="text-sm text-green-700 dark:text-green-300">
            <Icon name="check" :size="16" class="inline mr-1" />
            Valid reset token received
          </p>
        </div>

        <!-- Email Display -->
        <div>
          <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Email Address
          </label>
          <input
            :value="email"
            type="email"
            disabled
            class="auth-input bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400"
          />
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
              placeholder="Enter your new password"
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

        <!-- Confirm New Password Field -->
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

        <!-- Password Requirements -->
        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
          <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Password Requirements:</p>
          <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
            <li class="flex items-center">
              <Icon
                :name="form.password.length >= 8 ? 'check' : 'close'"
                :size="16"
                :class="form.password.length >= 8 ? 'text-green-600' : 'text-red-500'"
                class="mr-2"
              />
              At least 8 characters
            </li>
            <li class="flex items-center">
              <Icon
                :name="/[A-Z]/.test(form.password) ? 'check' : 'close'"
                :size="16"
                :class="/[A-Z]/.test(form.password) ? 'text-green-600' : 'text-red-500'"
                class="mr-2"
              />
              One uppercase letter
            </li>
            <li class="flex items-center">
              <Icon
                :name="/[0-9]/.test(form.password) ? 'check' : 'close'"
                :size="16"
                :class="/[0-9]/.test(form.password) ? 'text-green-600' : 'text-red-500'"
                class="mr-2"
              />
              One number
            </li>
          </ul>
        </div>

        <!-- General Error Message -->
        <div v-if="errorMessage" class="auth-error bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-center">
          <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="auth-success bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-center">
          <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ successMessage }}</p>
        </div>
      </form>
    </template>

    <template #actions>
      <button
        @click="handleResetPassword"
        :disabled="loading || !!successMessage"
        class="auth-button-primary"
      >
        <span v-if="loading" class="flex items-center justify-center">
          <Icon name="loading" :size="20" class="animate-spin mr-2" />
          Updating Password...
        </span>
        <span v-else-if="successMessage">Password Updated</span>
        <span v-else>Update Password</span>
      </button>

      <div v-if="successMessage" class="mt-4">
        <button
          @click="redirectToLogin"
          class="auth-button-secondary"
        >
          Continue to Sign In
        </button>
      </div>
    </template>

    <template #links>
      <div v-if="!successMessage" class="space-y-2">
        <router-link :to="{ name: 'auth.login' }" class="auth-link block">
          Back to Sign In
        </router-link>
      </div>
    </template>

    <template #footer>
      <p v-if="!successMessage" class="text-sm text-gray-500 dark:text-gray-400">
        Remember your password?
        <router-link :to="{ name: 'auth.login' }" class="auth-link">
          Sign in here
        </router-link>
      </p>
    </template>
  </AuthPage>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AuthPage from '@/components/common/AuthPage.vue'
import Icon from '@/components/common/Icon.vue'
import { useResetPasswordForm } from '@/components/composables/useResetPasswordForm'

const router = useRouter()
const helpText = 'Choose a strong password that you haven\'t used before. You\'ll be able to sign in with this new password right away.'

const {
  onSubmit,
  errors,
  values: form,
  isSubmitting: loading,
  successMessage,
  errorMessage,
  token,
  email,
  setFieldValue
} = useResetPasswordForm()

const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const handleResetPassword = () => {
  onSubmit()
}

const redirectToLogin = () => {
  router.push({ name: 'auth.login' })
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const togglePasswordConfirm = () => {
  showPasswordConfirm.value = !showPasswordConfirm.value
}
</script>