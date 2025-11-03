<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[60] overflow-y-auto"
        @click="handleOverlayClick"
        @keydown.esc="handleEscKey"
      >
        <div class="modal-container">
          <!-- Background overlay -->
          <Transition name="modal-overlay">
            <div v-if="modelValue" class="modal-overlay" />
          </Transition>

          <!-- Modal panel -->
          <Transition name="modal-content">
            <div
              v-if="modelValue"
              :class="modalClasses"
              @click.stop
              role="dialog"
              aria-modal="true"
              :aria-labelledby="title ? 'modal-title' : undefined"
            >
              <!-- Header Slot -->
              <div v-if="$slots.header || title || closable" class="modal-header">
                <slot name="header">
                  <div class="flex items-start justify-between">
                    <div v-if="title" class="flex-1">
                      <h3 id="modal-title" class="text-xl font-semibold text-title">
                        {{ title }}
                      </h3>
                      <p v-if="subtitle" class="mt-1 text-sm text-subtitle">
                        {{ subtitle }}
                      </p>
                    </div>
                    <button
                      v-if="closable && !persistent"
                      @click="close"
                      class="btn-ghost ml-4"
                      type="button"
                      aria-label="Close modal"
                      v-tooltip="'Close'"
                    >
                      <Icon name="close" :size="20" />
                    </button>
                  </div>
                </slot>
              </div>

              <!-- Body/Default Slot -->
              <div :class="bodyClasses">
                <slot>
                  <!-- Default content -->
                </slot>
                <slot name="body" />
              </div>

              <!-- Footer Slot -->
              <div v-if="$slots.footer" class="modal-footer">
                <slot name="footer" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnOverlay: {
    type: Boolean,
    default: true
  },
  closeOnEsc: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  },
  hideHeader: {
    type: Boolean,
    default: false
  },
  hideFooter: {
    type: Boolean,
    default: false
  },
  padding: {
    type: String,
    default: 'default',
    validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'opened', 'closed'])

// Computed Classes
const modalClasses = computed(() => {
  const classes = ['modal-panel']

  // Size classes
  const sizeClasses = {
    sm: 'modal-panel-sm',
    md: '', // default, no extra class needed
    lg: 'modal-panel-lg',
    xl: 'max-w-6xl',
    full: 'max-w-[95vw] h-[95vh]'
  }

  if (sizeClasses[props.size]) {
    classes.push(sizeClasses[props.size])
  }

  // Padding classes
  if (props.padding === 'none') {
    classes.push('p-0')
  } else if (props.padding === 'sm') {
    classes.push('p-4')
  } else if (props.padding === 'lg') {
    classes.push('p-8')
  }
  // default padding is already in modal-panel class (p-6)

  return classes.join(' ')
})

const bodyClasses = computed(() => {
  const classes = []

  // Add spacing between header and body if header exists
  if ((props.title || props.$slots?.header) && !props.hideHeader) {
    classes.push('mt-4')
  }

  // Add spacing between body and footer if footer exists
  if (props.$slots?.footer && !props.hideFooter) {
    classes.push('mb-4')
  }

  return classes.join(' ')
})

// Methods
const close = () => {
  if (!props.persistent) {
    emit('update:modelValue', false)
    emit('close')
  }
}

const handleOverlayClick = () => {
  if (props.closeOnOverlay && !props.persistent) {
    close()
  }
}

const handleEscKey = () => {
  if (props.closeOnEsc && !props.persistent && props.modelValue) {
    close()
  }
}

// Prevent body scroll when modal is open
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
    emit('opened')
  } else {
    document.body.style.overflow = ''
    emit('closed')
  }
})

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = ''
})

// Global ESC key listener
const handleGlobalEsc = (event) => {
  if (event.key === 'Escape' && props.closeOnEsc && !props.persistent && props.modelValue) {
    close()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleGlobalEsc)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalEsc)
})
</script>

<style scoped>
/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 200ms ease-out;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Overlay Transitions */
.modal-overlay-enter-active,
.modal-overlay-leave-active {
  transition: opacity 200ms ease-out;
}

.modal-overlay-enter-from,
.modal-overlay-leave-to {
  opacity: 0;
}

/* Content Transitions */
.modal-content-enter-active {
  transition: all 250ms cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-content-leave-active {
  transition: all 200ms cubic-bezier(0.4, 0, 1, 1);
}

.modal-content-enter-from {
  opacity: 0;
  transform: scale(0.95) translateY(-20px);
}

.modal-content-enter-to {
  opacity: 1;
  transform: scale(1) translateY(0);
}

.modal-content-leave-from {
  opacity: 1;
  transform: scale(1) translateY(0);
}

.modal-content-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-20px);
}
</style>
