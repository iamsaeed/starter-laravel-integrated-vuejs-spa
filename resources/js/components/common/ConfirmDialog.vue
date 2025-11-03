<template>
  <Transition
    :name="`dialog-${animation}`"
    :duration="animationDuration"
    appear
  >
    <div
      v-if="show"
      :class="['fixed inset-0 flex items-center justify-center p-4', zIndex ? '' : 'z-[70]']"
      :style="zIndex ? { zIndex } : {}"
      @click.self="handleBackdropClick"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click.self="handleBackdropClick"></div>

      <!-- Dialog -->
      <div
        :class="[dialogClasses, sizeClasses, customClass]"
        :style="customStyle"
        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl border-2 max-h-[90vh] overflow-y-auto"
        role="dialog"
        :aria-labelledby="`dialog-title-${id}`"
        :aria-describedby="`dialog-message-${id}`"
        aria-modal="true"
        @keydown.esc="handleEscapeKey"
      >
        <!-- Close Button -->
        <button
          v-if="closable && !persistent"
          @click="handleClose"
          class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          aria-label="Close dialog"
        >
          <Icon name="close" :size="20" />
        </button>

        <!-- Header -->
        <div class="px-6 pt-6 pb-4">
          <div class="flex items-start space-x-4">
            <!-- Icon -->
            <div :class="iconClasses" class="flex-shrink-0">
              <Icon :name="iconName" :size="24" />
            </div>

            <!-- Title & Message -->
            <div class="flex-1 min-w-0">
              <h3
                v-if="title"
                :id="`dialog-title-${id}`"
                :class="titleClasses"
                class="text-lg font-semibold leading-6"
              >
                {{ title }}
              </h3>
              <div
                v-if="html"
                :id="`dialog-message-${id}`"
                class="mt-2 text-sm text-gray-600 dark:text-gray-300"
                v-html="sanitizedHtml"
              />
              <p
                v-else-if="message"
                :id="`dialog-message-${id}`"
                class="mt-2 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line"
              >
                {{ message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Confirmation Input -->
        <div v-if="requireConfirmation" class="px-6 pb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ confirmationHint || `Type "${confirmationText}" to confirm` }}
          </label>
          <input
            v-model="confirmationInput"
            type="text"
            :placeholder="confirmationPlaceholder || confirmationText"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @keydown.enter="confirmationMatches && handleConfirm()"
          />
        </div>

        <!-- Input Fields (Prompt) -->
        <div v-if="inputs && inputs.length > 0" class="px-6 pb-4 space-y-4">
          <div v-for="input in inputs" :key="input.name">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ input.label }}
              <span v-if="input.required" class="text-red-500">*</span>
            </label>
            <textarea
              v-if="input.type === 'textarea'"
              v-model="inputValues[input.name]"
              :placeholder="input.placeholder"
              :required="input.required"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <input
              v-else
              v-model="inputValues[input.name]"
              :type="input.type"
              :placeholder="input.placeholder"
              :required="input.required"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <p v-if="inputErrors[input.name]" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ inputErrors[input.name] }}
            </p>
          </div>
        </div>

        <!-- Custom Component -->
        <div v-if="component" class="px-6 pb-4">
          <component :is="component" v-bind="componentProps" />
        </div>

        <!-- Timer Progress Bar -->
        <div v-if="timer && timerProgressBar" class="px-6">
          <div class="h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div
              class="h-full transition-all duration-100 ease-linear"
              :class="progressBarClasses"
              :style="{ width: `${timerProgress}%` }"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg">
          <!-- Custom Buttons -->
          <div v-if="buttons && buttons.length > 0" class="flex flex-wrap gap-3 justify-end">
            <button
              v-for="(button, index) in buttons"
              :key="index"
              @click="handleCustomButton(button)"
              :disabled="loading && disableOnLoading"
              :class="getButtonClasses(button.variant, button.class)"
              class="px-4 py-2 rounded-lg font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ button.label }}
            </button>
          </div>

          <!-- Default Confirm/Cancel -->
          <div v-else class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
            <button
              v-if="showCancel"
              @click="handleCancel"
              :disabled="loading && disableOnLoading"
              :class="cancelClass"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 rounded-lg font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ cancelLabel }}
            </button>
            <button
              @click="handleConfirm"
              :disabled="(requireConfirmation && !confirmationMatches) || (loading && disableOnLoading)"
              :class="[confirmButtonClasses, confirmClass]"
              class="px-4 py-2 rounded-lg font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <Icon v-if="loading" name="loading" :size="16" class="animate-spin" />
              <span>{{ loading ? loadingText : (timerOnButton && timerRemaining ? `${confirmLabel} (${timerRemaining}s)` : confirmLabel) }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useDialogStore } from '@/stores/dialog'
import Icon from './Icon.vue'
import DOMPurify from 'dompurify'

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'info',
    validator: (val) => ['success', 'danger', 'error', 'warning', 'info'].includes(val),
  },
  title: String,
  message: String,
  html: String,
  sanitize: {
    type: Boolean,
    default: true,
  },
  icon: String,
  confirmLabel: {
    type: String,
    default: 'Confirm',
  },
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },
  confirmClass: String,
  cancelClass: String,
  showCancel: {
    type: Boolean,
    default: true,
  },
  buttons: Array,
  closable: {
    type: Boolean,
    default: true,
  },
  closeOnBackdrop: {
    type: Boolean,
    default: false,
  },
  closeOnEscape: {
    type: Boolean,
    default: true,
  },
  persistent: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingText: {
    type: String,
    default: 'Processing...',
  },
  disableOnLoading: {
    type: Boolean,
    default: true,
  },
  autoFocusButton: {
    type: String,
    default: 'confirm',
    validator: (val) => ['confirm', 'cancel', null].includes(val),
  },
  size: {
    type: String,
    default: 'md',
    validator: (val) => ['sm', 'md', 'lg', 'xl'].includes(val),
  },
  animation: {
    type: String,
    default: 'scale',
    validator: (val) => ['fade', 'scale', 'slide-up', 'slide-down'].includes(val),
  },
  animationDuration: {
    type: Number,
    default: 200,
  },
  timer: Number,
  timerProgressBar: {
    type: Boolean,
    default: false,
  },
  timerOnButton: {
    type: Boolean,
    default: false,
  },
  requireConfirmation: {
    type: Boolean,
    default: false,
  },
  confirmationText: {
    type: String,
    default: 'CONFIRM',
  },
  confirmationPlaceholder: String,
  confirmationHint: String,
  inputs: Array,
  component: Object,
  componentProps: {
    type: Object,
    default: () => ({}),
  },
  customClass: String,
  customStyle: Object,
  zIndex: Number,
})

const store = useDialogStore()
const show = ref(false)
const confirmationInput = ref('')
const inputValues = ref({})
const inputErrors = ref({})
const timerProgress = ref(100)
const timerRemaining = ref(null)

let timerInterval = null
let timerCountdown = null

// Initialize input values
if (props.inputs) {
  props.inputs.forEach(input => {
    inputValues.value[input.name] = input.value || ''
  })
}

// Timer remaining in seconds
if (props.timer && props.timerOnButton) {
  timerRemaining.value = Math.ceil(props.timer / 1000)
}

const sanitizedHtml = computed(() => {
  if (!props.html) return ''
  if (props.sanitize) {
    return DOMPurify.sanitize(props.html)
  }
  return props.html
})

const confirmationMatches = computed(() => {
  if (!props.requireConfirmation) return true
  return confirmationInput.value === props.confirmationText
})

const dialogClasses = computed(() => {
  const classes = {
    success: 'border-primary-200 dark:border-primary-700',
    danger: 'border-red-200 dark:border-red-700',
    warning: 'border-yellow-200 dark:border-yellow-700',
    info: 'border-blue-200 dark:border-blue-700',
    error: 'border-red-200 dark:border-red-700',
  }
  return classes[props.type] || classes.info
})

const iconClasses = computed(() => {
  const classes = {
    success: 'text-primary-600 dark:text-primary-400',
    danger: 'text-red-600 dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info: 'text-blue-600 dark:text-blue-400',
    error: 'text-red-600 dark:text-red-400',
  }
  return classes[props.type] || classes.info
})

const titleClasses = computed(() => {
  return 'text-gray-900 dark:text-gray-100'
})

const confirmButtonClasses = computed(() => {
  const classes = {
    success: 'bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600',
    danger: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600',
    warning: 'bg-yellow-600 hover:bg-yellow-700 text-white dark:bg-yellow-500 dark:hover:bg-yellow-600',
    info: 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600',
    error: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600',
  }
  return classes[props.type] || classes.info
})

const progressBarClasses = computed(() => {
  const classes = {
    success: 'bg-primary-500 dark:bg-primary-400',
    danger: 'bg-red-500 dark:bg-red-400',
    warning: 'bg-yellow-500 dark:bg-yellow-400',
    info: 'bg-blue-500 dark:bg-blue-400',
    error: 'bg-red-500 dark:bg-red-400',
  }
  return classes[props.type] || classes.info
})

const iconName = computed(() => {
  if (props.icon) return props.icon

  const icons = {
    success: 'check-circle-filled',
    danger: 'delete',
    error: 'alert-triangle',
    warning: 'alert-circle',
    info: 'info-circle',
  }
  return icons[props.type]
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
  }
  return sizes[props.size] || sizes.md
})

function getButtonClasses(variant, customClass) {
  const variants = {
    primary: 'bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600',
    secondary: 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200',
    danger: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600',
  }
  return [variants[variant] || variants.secondary, customClass].filter(Boolean).join(' ')
}

function handleBackdropClick() {
  if (props.closeOnBackdrop && !props.persistent) {
    handleClose()
  }
}

function handleEscapeKey() {
  if (props.closeOnEscape && !props.persistent) {
    handleClose()
  }
}

function handleClose() {
  store.closeDialog(props.id)
}

function handleCancel() {
  store.dismissDialog(props.id)
}

function handleConfirm() {
  // Validate inputs if present
  if (props.inputs && props.inputs.length > 0) {
    let hasErrors = false
    inputErrors.value = {}

    props.inputs.forEach(input => {
      const value = inputValues.value[input.name]

      // Check required
      if (input.required && !value) {
        inputErrors.value[input.name] = `${input.label} is required`
        hasErrors = true
        return
      }

      // Custom validation
      if (input.validation && value) {
        const error = input.validation(value)
        if (error) {
          inputErrors.value[input.name] = error
          hasErrors = true
        }
      }
    })

    if (hasErrors) return

    // Resolve with input values
    store.resolveDialog(props.id, true, inputValues.value)
  } else {
    store.resolveDialog(props.id, true)
  }
}

function handleCustomButton(button) {
  if (typeof button.action === 'function') {
    button.action()
    store.dismissDialog(props.id)
  } else {
    store.resolveDialog(props.id, true, button.action)
  }
}

onMounted(() => {
  // Show dialog
  show.value = true

  // Auto-focus button
  setTimeout(() => {
    if (props.autoFocusButton === 'confirm') {
      const confirmBtn = document.querySelector(`[aria-labelledby="dialog-title-${props.id}"] button:last-child`)
      confirmBtn?.focus()
    } else if (props.autoFocusButton === 'cancel') {
      const cancelBtn = document.querySelector(`[aria-labelledby="dialog-title-${props.id}"] button:first-child`)
      cancelBtn?.focus()
    }
  }, props.animationDuration + 50)

  // Start timer
  if (props.timer) {
    const startTime = Date.now()
    const interval = 50 // Update every 50ms

    timerInterval = setInterval(() => {
      const elapsed = Date.now() - startTime
      const remaining = Math.max(0, props.timer - elapsed)
      timerProgress.value = (remaining / props.timer) * 100

      if (remaining === 0) {
        clearInterval(timerInterval)
      }
    }, interval)

    // Countdown for button
    if (props.timerOnButton) {
      timerCountdown = setInterval(() => {
        timerRemaining.value = Math.ceil((props.timer - (Date.now() - startTime)) / 1000)
        if (timerRemaining.value <= 0) {
          clearInterval(timerCountdown)
        }
      }, 1000)
    }
  }

  // Prevent body scroll
  document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
  if (timerCountdown) {
    clearInterval(timerCountdown)
  }

  // Restore body scroll
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Fade Animation */
.dialog-fade-enter-active,
.dialog-fade-leave-active {
  transition: opacity v-bind(animationDuration + 'ms');
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
  opacity: 0;
}

/* Scale Animation */
.dialog-scale-enter-active,
.dialog-scale-leave-active {
  transition: all v-bind(animationDuration + 'ms');
}

.dialog-scale-enter-from,
.dialog-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Slide Up Animation */
.dialog-slide-up-enter-active,
.dialog-slide-up-leave-active {
  transition: all v-bind(animationDuration + 'ms');
}

.dialog-slide-up-enter-from,
.dialog-slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Slide Down Animation */
.dialog-slide-down-enter-active,
.dialog-slide-down-leave-active {
  transition: all v-bind(animationDuration + 'ms');
}

.dialog-slide-down-enter-from,
.dialog-slide-down-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
