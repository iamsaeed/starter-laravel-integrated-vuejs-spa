<template>
  <AuthPage
    title="Create Account"
    description="Sign up to get started with your new account"
    :help-text="helpText"
  >
    <template #form>
      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name Field -->
        <FormGroup>
          <FormLabel for-id="name" :required="true">Full Name</FormLabel>
          <FormInput
            id="name"
            name="name"
            type="text"
            placeholder="Enter your full name"
          />
        </FormGroup>

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
            placeholder="Create a strong password"
          />
        </FormGroup>

        <!-- Confirm Password Field -->
        <FormGroup>
          <FormLabel for-id="password_confirmation" :required="true">Confirm Password</FormLabel>
          <PasswordInput
            id="password_confirmation"
            name="password_confirmation"
            placeholder="Confirm your password"
          />
        </FormGroup>

        <!-- Terms Agreement -->
        <CheckboxInput
          id="terms"
          name="terms"
        >
          <template #label>
            I agree to the
            <button type="button" class="auth-link" @click="showTerms">
              Terms of Service
            </button>
            and
            <button type="button" class="auth-link" @click="showPrivacy">
              Privacy Policy
            </button>
          </template>
        </CheckboxInput>

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
        @click="handleRegister"
        :disabled="loading"
        class="auth-button-primary"
      >
        <span v-if="loading" class="flex items-center justify-center">
          <Icon name="loading" :size="20" class="animate-spin mr-2" />
          Creating Account...
        </span>
        <span v-else>Create Account</span>
      </button>
    </template>

    <template #footer>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Already have an account?
        <router-link :to="{ name: 'auth.login' }" class="auth-link">
          Sign in here
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
import { useRegisterForm } from '@/components/composables/useRegisterForm'

const helpText = 'Your account will be created instantly and you\'ll be able to sign in right away.'

const {
  onSubmit,
  isSubmitting: loading,
  successMessage,
  errorMessage
} = useRegisterForm()

const handleRegister = () => {
  onSubmit()
}

const showTerms = () => {
  alert('Terms of Service would be displayed here')
}

const showPrivacy = () => {
  alert('Privacy Policy would be displayed here')
}
</script>