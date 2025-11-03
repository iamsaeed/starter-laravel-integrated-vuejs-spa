<template>
  <AuthPage
    title="Welcome Back"
    description="Sign in to your account to continue"
    :help-text="helpText"
  >
    <template #form>
      <form @submit.prevent="onSubmit" class="space-y-4">
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

        <!-- Password Field -->
        <FormGroup>
          <FormLabel for-id="password" :required="true">Password</FormLabel>
          <PasswordInput
            id="password"
            name="password"
            placeholder="Enter your password"
          />
        </FormGroup>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <CheckboxInput
            id="remember"
            name="remember"
            label="Remember me"
          />
        </div>

        <!-- Success Message -->
        <FormSuccess :message="successMessage" />

        <!-- General Error Message -->
        <div v-if="errorMessage" class="auth-error bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ errorMessage }}</p>
          </div>
        </div>
      </form>
    </template>

    <template #actions>
      <button
        @click="onSubmit"
        :disabled="isSubmitting"
        class="auth-button-primary"
      >
        <span v-if="isSubmitting" class="flex items-center justify-center">
          <Icon name="loading" :size="20" class="animate-spin mr-2" />
          Signing In...
        </span>
        <span v-else>Sign In</span>
      </button>
    </template>

    <template #links>
      <div class="space-y-2">
        <router-link :to="{ name: 'auth.forgot-password' }" class="auth-link block">
          Forgot your password?
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
import AuthPage from '@/components/common/AuthPage.vue'
import Icon from '@/components/common/Icon.vue'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import FormInput from '@/components/form/FormInput.vue'
import PasswordInput from '@/components/form/PasswordInput.vue'
import CheckboxInput from '@/components/form/CheckboxInput.vue'
import FormSuccess from '@/components/form/FormSuccess.vue'
import { useLoginForm } from '@/components/composables/useLoginForm'

const helpText = 'Having trouble signing in? Make sure you\'re using the correct email and password.'

const {
  onSubmit,
  isSubmitting,
  successMessage,
  errorMessage
} = useLoginForm()
</script>