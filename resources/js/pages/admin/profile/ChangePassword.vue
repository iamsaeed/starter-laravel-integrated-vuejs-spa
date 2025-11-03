<template>
  <div class="space-y-6">
    <!-- Password Change Form -->
    <div>
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Password Security (Admin)</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Choose a strong password with at least 8 characters
        </p>
      </div>

      <PasswordChangeForm
        :form="form"
        :errors="errors"
        :is-submitting="isSubmitting"
        :success-message="successMessage"
        :error-message="errorMessage"
        @submit="handlePasswordChange"
        @update:field="setFieldValue"
      />
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
import { useChangePasswordForm } from '@/components/composables/useChangePasswordForm'
import PasswordChangeForm from '@/components/profile/PasswordChangeForm.vue'

const {
  onSubmit: handlePasswordChange,
  errors,
  values: form,
  isSubmitting,
  successMessage,
  errorMessage,
  setFieldValue,
} = useChangePasswordForm()
</script>
