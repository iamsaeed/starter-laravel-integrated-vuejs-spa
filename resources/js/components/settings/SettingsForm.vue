<template>
  <div class="settings-form-wrapper">
    <!-- Success Message -->
    <FormSuccess v-if="showSuccess" :message="successMessage" />

    <!-- Error Message -->
    <div v-if="errorMessage" class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ errorMessage }}</p>
      </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-8">
      <slot />

      <!-- Form Actions -->
      <FormActions :align="actionsAlign">
        <slot name="actions">
          <!-- Cancel/Reset Button -->
          <button
            v-if="showCancel"
            type="button"
            @click="handleCancel"
            :disabled="isSaving || (!isDirty && !allowResetClean)"
            class="btn-secondary"
          >
            {{ cancelLabel }}
          </button>

          <!-- Save Button -->
          <button
            type="submit"
            :disabled="isSaving || (!isDirty && !allowSaveClean) || (requireDirty && !isDirty)"
            :class="[
              'btn-primary',
              {
                'opacity-50 cursor-not-allowed': isSaving || (!isDirty && !allowSaveClean)
              }
            ]"
          >
            <span v-if="isSaving" class="flex items-center justify-center">
              <Icon name="loading" :size="18" class="animate-spin mr-2" />
              {{ savingLabel }}
            </span>
            <span v-else>{{ saveLabel }}</span>
          </button>
        </slot>
      </FormActions>
    </form>

    <!-- Unsaved Changes Warning -->
    <div v-if="isDirty && showUnsavedWarning" class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-sm text-yellow-700 dark:text-yellow-300">
          You have unsaved changes. Don't forget to save your settings.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted, getCurrentInstance } from 'vue'
import FormSuccess from '@/components/form/FormSuccess.vue'
import FormActions from '@/components/form/FormActions.vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  isSaving: {
    type: Boolean,
    default: false
  },
  isDirty: {
    type: Boolean,
    default: false
  },
  showSuccess: {
    type: Boolean,
    default: false
  },
  successMessage: {
    type: String,
    default: 'Settings saved successfully!'
  },
  errorMessage: {
    type: String,
    default: ''
  },
  saveLabel: {
    type: String,
    default: 'Save Settings'
  },
  savingLabel: {
    type: String,
    default: 'Saving...'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  },
  showCancel: {
    type: Boolean,
    default: true
  },
  actionsAlign: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'center', 'right', 'between'].includes(value)
  },
  allowSaveClean: {
    type: Boolean,
    default: false
  },
  allowResetClean: {
    type: Boolean,
    default: false
  },
  requireDirty: {
    type: Boolean,
    default: true
  },
  showUnsavedWarning: {
    type: Boolean,
    default: true
  },
  confirmNavigation: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'cancel', 'reset'])

// Get current instance to check if router is available
const instance = getCurrentInstance()

// Methods
const handleSubmit = () => {
  if (!props.isSaving && (props.isDirty || props.allowSaveClean)) {
    emit('submit')
  }
}

const handleCancel = () => {
  if (props.isDirty || props.allowResetClean) {
    if (confirm('Are you sure you want to discard your changes?')) {
      emit('cancel')
      emit('reset')
    }
  }
}

// Navigation guard for unsaved changes
// Only set up if router is available and confirmNavigation is true
if (props.confirmNavigation && instance?.appContext?.config?.globalProperties?.$router) {
  try {
    // Dynamically import and setup route guard only if router is available
    import('vue-router').then(({ onBeforeRouteLeave }) => {
      onBeforeRouteLeave((to, from, next) => {
        if (props.isDirty && !props.isSaving) {
          const answer = window.confirm(
            'You have unsaved changes. Do you really want to leave?'
          )
          if (answer) {
            next()
          } else {
            next(false)
          }
        } else {
          next()
        }
      })
    }).catch(() => {
      // Router not available, silently skip navigation guard
    })
  } catch (error) {
    // Silently fail if router is not available
  }
}

// Browser beforeunload event for unsaved changes
if (typeof window !== 'undefined' && props.confirmNavigation) {
  const handleBeforeUnload = (e) => {
    if (props.isDirty && !props.isSaving) {
      e.preventDefault()
      e.returnValue = ''
      return ''
    }
  }

  window.addEventListener('beforeunload', handleBeforeUnload)

  onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
  })
}
</script>
