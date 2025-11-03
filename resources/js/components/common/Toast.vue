<template>
  <div
    :class="toastClasses"
    class="rounded-lg shadow-lg border backdrop-blur-md min-w-[320px] max-w-md p-4 relative overflow-hidden pointer-events-auto"
    role="alert"
    :aria-live="type === 'error' ? 'assertive' : 'polite'"
  >
    <div class="flex items-start space-x-3">
      <!-- Icon -->
      <Icon
        :name="iconName"
        :size="20"
        class="flex-shrink-0 w-5 h-5"
      />

      <!-- Message -->
      <p class="flex-1 text-sm font-medium leading-relaxed">{{ message }}</p>

      <!-- Close Button -->
      <button
        v-if="closable"
        @click="handleClose"
        class="flex-shrink-0 ml-3 text-current/60 hover:text-current transition-colors duration-200 cursor-pointer"
        aria-label="Close notification"
      >
        <Icon name="close" :size="16" />
      </button>
    </div>

    <!-- Progress Bar -->
    <div
      v-if="duration > 0"
      :class="progressBarClasses"
      class="absolute bottom-0 left-0 h-1 transition-all duration-100 ease-linear"
      :style="{ width: `${progress}%` }"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  type: {
    type: String,
    required: true,
    validator: (val) => ['success', 'error', 'warning', 'info'].includes(val)
  },
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 4000
  },
  closable: {
    type: Boolean,
    default: true
  },
  icon: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['close'])

const progress = ref(100)
let progressInterval = null

const toastClasses = computed(() => {
  const classes = {
    success: 'bg-green-50/95 dark:bg-green-900/90 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
    error: 'bg-red-50/95 dark:bg-red-900/90 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200',
    warning: 'bg-yellow-50/95 dark:bg-yellow-900/90 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200',
    info: 'bg-blue-50/95 dark:bg-blue-900/90 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200'
  }
  return classes[props.type] || classes.info
})

const progressBarClasses = computed(() => {
  const classes = {
    success: 'bg-green-500 dark:bg-green-400',
    error: 'bg-red-500 dark:bg-red-400',
    warning: 'bg-yellow-500 dark:bg-yellow-400',
    info: 'bg-blue-500 dark:bg-blue-400'
  }
  return classes[props.type] || classes.info
})

const iconName = computed(() => {
  if (props.icon) return props.icon

  const icons = {
    success: 'check-circle-filled',
    error: 'x-circle',
    warning: 'alert-circle',
    info: 'info-circle'
  }

  return icons[props.type]
})

function handleClose() {
  emit('close', props.id)
}

onMounted(() => {
  if (props.duration > 0) {
    const interval = 50 // Update every 50ms
    const decrement = (interval / props.duration) * 100

    progressInterval = setInterval(() => {
      progress.value -= decrement
      if (progress.value <= 0) {
        progress.value = 0
        clearInterval(progressInterval)
      }
    }, interval)
  }
})

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
})
</script>
