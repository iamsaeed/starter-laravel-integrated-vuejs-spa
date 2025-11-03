<template>
  <AuthPage
    title="Reset Password"
    description="Enter your email address and we'll send you a link to reset your password"
    :help-text="helpText"
  >
    <template #form>
      <form @submit.prevent="handleForgotPassword" class="space-y-4">
        <!-- Email Field -->
        <FormGroup>
          <FormLabel for-id="email" :required="true">Email Address</FormLabel>
          <FormInput
            id="email"
            name="email"
            type="email"
            placeholder="Enter your email address"
          />
        </FormGroup>

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
        @click="handleForgotPassword"
        :disabled="loading || !!successMessage"
        class="auth-button-primary"
      >
        <span v-if="loading" class="flex items-center justify-center">
          <Icon name="loading" :size="20" class="animate-spin mr-2" />
          Sending Reset Link...
        </span>
        <span v-else-if="successMessage">Reset Link Sent</span>
        <span v-else>Send Reset Link</span>
      </button>

      <div v-if="successMessage" class="auth-divider">
        <div class="auth-divider-line"></div>
        <div class="auth-divider-text">or</div>
        <div class="auth-divider-line"></div>
      </div>

      <button
        v-if="successMessage"
        @click="resendEmail"
        :disabled="resendLoading || resendCooldown > 0"
        class="auth-button-ghost"
      >
        <span v-if="resendLoading" class="flex items-center justify-center">
          <Icon name="loading" :size="20" class="animate-spin mr-2" />
          Resending...
        </span>
        <span v-else-if="resendCooldown > 0">
          Resend in {{ resendCooldown }}s
        </span>
        <span v-else>Resend Reset Link</span>
      </button>
    </template>

    <template #links>
      <div class="space-y-2">
        <router-link :to="{ name: 'auth.login' }" class="auth-link block">
          Back to Sign In
        </router-link>
      </div>
    </template>

    <template #footer>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Don't have an account?
        <router-link :to="{ name: 'auth.register' }" class="auth-link">
          Sign up here
        </router-link>
      </p>
    </template>
  </AuthPage>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import AuthPage from '@/components/common/AuthPage.vue'
import Icon from '@/components/common/Icon.vue'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import FormInput from '@/components/form/FormInput.vue'
import { useForgotPasswordForm } from '@/components/composables/useForgotPasswordForm'

const helpText = 'We\'ll send you a secure link to reset your password. Check your spam folder if you don\'t receive it within a few minutes.'

const {
  onSubmit,
  isSubmitting: loading,
  successMessage,
  errorMessage
} = useForgotPasswordForm()

const resendLoading = ref(false)
const resendCooldown = ref(0)
let resendTimer = null

const handleForgotPassword = () => {
  onSubmit()
}

const startResendCooldown = () => {
  resendCooldown.value = 30
  resendTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(resendTimer)
      resendTimer = null
    }
  }, 1000)
}

const resendEmail = async () => {
  resendLoading.value = true
  try {
    await onSubmit()
    startResendCooldown()
  } finally {
    resendLoading.value = false
  }
}

// Cleanup timer on unmount
onUnmounted(() => {
  if (resendTimer) {
    clearInterval(resendTimer)
  }
})
</script>