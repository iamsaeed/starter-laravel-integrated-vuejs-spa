<template>
  <div class="auth-container">
    <div class="auth-card">
      <!-- Logo Section -->
      <div class="auth-logo-section">
        <div class="auth-logo">
          <Icon name="shield" :size="32" class="auth-logo-icon" />
        </div>
        <div class="text-center">
          <h1 class="auth-logo-title">{{ logoTitle }}</h1>
          <p class="auth-logo-subtitle">{{ logoSubtitle }}</p>
        </div>
      </div>

      <!-- Auth Content -->
      <div class="auth-content">
        <!-- Title -->
        <div class="auth-header">
          <h2 class="auth-title">{{ title }}</h2>
          <p v-if="description" class="auth-description">{{ description }}</p>
        </div>

        <!-- Form Slot -->
        <div class="auth-form">
          <slot name="form" />
        </div>

        <!-- Action Buttons -->
        <div v-if="showActions" class="auth-actions">
          <slot name="actions" />
        </div>

        <!-- Additional Links -->
        <div v-if="showLinks" class="auth-links">
          <slot name="links" />
        </div>
      </div>

      <!-- Help Text -->
      <div v-if="helpText" class="auth-help">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ helpText }}</p>
      </div>

      <!-- Footer Links -->
      <div v-if="showFooter" class="auth-footer">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script>
import Icon from './Icon.vue'

export default {
  name: 'AuthPage',
  components: {
    Icon,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      default: null,
    },
    logoTitle: {
      type: String,
      default: 'AdminPanel',
    },
    logoSubtitle: {
      type: String,
      default: 'Secure Authentication',
    },
    helpText: {
      type: String,
      default: null,
    },
    showActions: {
      type: Boolean,
      default: true,
    },
    showLinks: {
      type: Boolean,
      default: true,
    },
    showFooter: {
      type: Boolean,
      default: true,
    },
  },
}
</script>