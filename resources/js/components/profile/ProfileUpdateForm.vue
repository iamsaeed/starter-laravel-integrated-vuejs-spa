<template>
  <form @submit.prevent="$emit('submit')" class="space-y-4">
    <!-- Name Field -->
    <div>
      <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
        Full Name
      </label>
      <input
        id="name"
        :value="form.name"
        @input="$emit('update:field', 'name', $event.target.value)"
        type="text"
        required
        class="auth-input"
        :class="{ error: errors.name }"
        placeholder="Enter your full name"
        autocomplete="name"
      />
      <div v-if="errors.name" class="auth-error">
        {{ errors.name }}
      </div>
    </div>

    <!-- Email Field -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
        Email Address
      </label>
      <input
        id="email"
        :value="form.email"
        @input="$emit('update:field', 'email', $event.target.value)"
        type="email"
        required
        class="auth-input"
        :class="{ error: errors.email }"
        placeholder="Enter your email address"
        autocomplete="email"
      />
      <div v-if="errors.email" class="auth-error">
        {{ errors.email }}
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
        <span v-else>Update Profile</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import Icon from '@/components/common/Icon.vue'

defineProps({
  form: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  successMessage: {
    type: String,
    default: '',
  },
  errorMessage: {
    type: String,
    default: '',
  },
})

defineEmits(['submit', 'update:field'])
</script>
